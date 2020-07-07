<?php
/**
 * COmanage Registry RCAuth Source Co Petitions Controller
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.1.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

App::uses('CoPetitionsController', 'Controller');

class RcauthSourceCoPetitionsController extends CoPetitionsController
{
  // Class name, used by Cake
  public $name = "RcauthSourceCoPetitions";

  public $uses = array("CoPetition",
                       "OrgIdentitySource",
                       "RcauthSource",
                       "RcauthSource.RcauthSourceBackend");

  /**
   * Enrollment Flow selectOrgIdentity (authenticate mode)
   *
   * @param Integer $id CO Petition ID
   * @param Array $oiscfg Array of configuration data for this plugin
   * @param Array $onFinish URL, in Cake format
   * @param Integer $actorCoPersonId CO Person ID of actor
   * @since  COmanage Registry v3.1.0
   */
  protected function execute_plugin_selectOrgIdentityAuthenticate($id, $oiscfg, $onFinish, $actorCoPersonId)
  {
    // First pull our RCAUTH configuration
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    $args = array();
    $args['conditions']['RcauthSource.org_identity_source_id'] = $oiscfg['OrgIdentitySource']['id'];
    $args['contain'] = false;
    // fixme: Cache this only for one read. Since the plugin goes to RCAuth and then redirects back here
    // as a result we should not read the database again
    $cfg = $this->RcauthSource->find('first', $args);

    if (empty($cfg)) {
      throw new InvalidArgumentException(_txt('er.notfound',
        array(_txt('ct.rcauth_sources.1'),
          $oiscfg['OrgIdentitySource']['id'])));
    }
    $this->RcauthSourceBackend->setConfig($cfg);
    $this->log(__METHOD__ . "::Rcauth Plugin Config => " . print_r($cfg['RcauthSource'], true), LOG_DEBUG);
    try {
      // Get the MP OA2 endpoints
      $this->RcauthSourceBackend->getMPOPA2endpoints($cfg['RcauthSource']['mp_oa2_server']);
      // Check if we have data
      $authEndpoint = $this->RcauthSourceBackend->getMpOA2Server()->getAuthorizationEndpoint();
      if (!isset($authEndpoint)) {
        throw new RuntimeException(_txt('er.rcauthsource.mp_oa2_server.none'));
      }
    } catch (Exception $e) {
      throw new RuntimeException(_txt('er.rcauthsource.mp_oa2_server.none'));
    }

    if (empty($this->request->query['code'])) {
      $this->redirect($this->RcauthSourceBackend->constructAccessTokenRequest($id, $oiscfg['OrgIdentitySource']['id']));
    }

    // Else we're back from an OAuth request, exchange the code for an access token
    try {
      // Exchange the code for an access token
      // Construct the callback URL, needed for both the initial query and
      // exchanging the code for a response
      $redirectUri = Router::url($this->RcauthSourceBackend->callbackUrl(), array('full' => true));
      $response = $this->RcauthSourceBackend->exchangeCode($redirectUri,
                                                           $cfg['RcauthSource']['clientid'],
                                                           $cfg['RcauthSource']['client_secret'],
                                                           $this->request->query['code']);

      // Save the data we retrieved above for the current client id
      // Create th data structure
      $oauth2_data['RcauthSource'] = array(
        'access_token' => $response->access_token,
        'id_token' => $response->id_token,
        'token_type' => $response->token_type
      );
      if (isset($response->refresh_token)) {
        $oauth2_data['RcauthSource']['refresh_token'] = $response->refresh_token;
      }

      $this->RcauthSource->id = $cfg['RcauthSource']['id'];
      // Save the eduPersonUniqueId
      $this->RcauthSource->set($oauth2_data);
      if ($this->RcauthSource->validates()) {
        $this->RcauthSource->save($oauth2_data);
      } else {
        $this->log(__METHOD__ . "::Rcauthsource data failed to validate", LOG_DEBUG);
        throw new RuntimeException(_txt('er.db.save'));
      }


      // XXX find if there is an old Certificate from RCAUTH and remove it. There is no actuall value in updating
      // XXX since the $sourcekey, i.e. accessToken, will always be different.
      // XXX Eventually this should not be an OISPlugin but something else.
      $this->unlinkRCAuthOrg($actorCoPersonId, $cfg['RcauthSource']['issuer']);

      // Create the OrgIdentity
      $OrgId = $this->OrgIdentitySource->createOrgIdentity($oiscfg['OrgIdentitySource']['id'],
                                                           $response->access_token,  // This is what exchange(job) should fetch.
                                                           $actorCoPersonId,
                                                           $this->cur_co['Co']['id'],
                                                           $actorCoPersonId,
                                                           false); // XXX provision is set to false. Pershaps we need to make this configurable

      // Record the RCAUTH into History and Petition History
      $this->CoPetition->EnrolleeOrgIdentity->HistoryRecord->record($actorCoPersonId,
                                                                    null,
                                                                    $OrgId,
                                                                    $actorCoPersonId,
                                                                    ActionEnum::CoPersonOrgIdLinked,
                                                                    _txt('pl.rcauthsource.linked', array($response->access_token)));

      $this->CoPetition->CoPetitionHistoryRecord->record($id,
                                                         $actorCoPersonId,
                                                         PetitionActionEnum::IdentityLinked,
                                                         _txt('pl.rcauthsource.linked', array($response->access_token)));
    } catch (Exception $e) {
      // This might happen if (eg) the Rcauth is already in use
      throw new RuntimeException($e->getMessage());
    }

    // XXX we should revisit if we decide that the plugin should provision the fetched data
    // The step is done
    // redirect to user profile
    $this->redirect(array(
      'plugin'     => null,
      'controller' => 'co_people',
      'action'     => 'canvas',
      $actorCoPersonId
    ));
  }

  /**
   * Unlink the RCAuth OrgIdentity from the CO Person
   * @param $co_person_id
   * @param $crt_issuer
   */
  public function unlinkRCAuthOrg($co_person_id, $crt_issuer) {
    $args = array();
    $args['joins'][0]['table'] = 'co_people';
    $args['joins'][0]['alias'] = 'CoPerson';
    $args['joins'][0]['type'] = 'INNER';
    $args['joins'][0]['conditions'][0] = 'CoOrgIdentityLink.co_person_id=CoPerson.id';
    $args['joins'][1]['table'] = 'certs';
    $args['joins'][1]['alias'] = 'Cert';
    $args['joins'][1]['type'] = 'INNER';
    $args['joins'][1]['conditions'][0] = 'CoOrgIdentityLink.org_identity_id=Cert.org_identity_id';
    $args['conditions']['CoPerson.id'] = $co_person_id;
    $args['conditions']['Cert.issuer'] = $crt_issuer;
    $args['fields'] = array('CoOrgIdentityLink.id');
    $args['contain'] = false;


    $this->CoOrgIdentityLink = ClassRegistry::init('CoOrgIdentityLink');
    $ccoil_ret = $this->CoOrgIdentityLink->find('first', $args);

    // There is no record so go back and continue to create one
    if(empty($ccoil_ret["CoOrgIdentityLink"])) {
      return;
    }
    // Delete the record
    $ccoil_id = $ccoil_ret["CoOrgIdentityLink"]["id"];
    $this->CoOrgIdentityLink->delete($ccoil_id);
  }
}

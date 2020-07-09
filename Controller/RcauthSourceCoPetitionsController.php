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
                       "RcauthSource.RcauthSource",
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
    $cfg = $this->RcauthSource->find('first', $args);

    // XXX Cache the config here

    if (empty($cfg)) {
      throw new InvalidArgumentException(_txt('er.notfound',
        array(_txt('ct.rcauth_sources.1'),
          $oiscfg['OrgIdentitySource']['id'])));
    }
    $this->RcauthSourceBackend->setConfig($cfg);
    $this->log(__METHOD__ . "::Rcauth Plugin Config => " . print_r($cfg['RcauthSource'], true), LOG_DEBUG);
    try {
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


      // XXX find if there is an old Certificate from RCAUTH and remove it. There is no actuall value in updating
      // XXX since the $sourcekey, i.e. accessToken, will always be different.
      // XXX Eventually this should not be an OISPlugin but something else.
      $this->RcauthSource->unlinkRCAuthOrg($actorCoPersonId, $cfg['RcauthSource']['issuer']);

      $provision_status = isset($cfg["RcauthSource"]["provision"]) ? (bool)$cfg["RcauthSource"]["provision"] : false;
      // Create the OrgIdentity
      $OrgId = $this->OrgIdentitySource->createOrgIdentity($oiscfg['OrgIdentitySource']['id'],
                                                           $response->access_token,  // This is what exchange(job) should fetch.
                                                           $actorCoPersonId,
                                                           $this->cur_co['Co']['id'],
                                                           $actorCoPersonId,
                                                           $provision_status);

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
      $this->Flash->set(_txt('er.rcauthsource.add_update'), array('key' => 'error'));
      throw new RuntimeException($e->getMessage());
    }

    // XXX Remove chached cfg here

    $this->Flash->set(_txt('op.rcauthsource.add_update'), array('key' => 'success'));
    // The step is done
    // Redirect to provisioning
    if($provision_status) {
      // redirect to user profile
      $this->redirect(array(
        'plugin'     => null,
        'controller' => 'co_petitions',
        'action'     => 'provision',
        $id
      ));
    }
    // redirect to user profile
    $this->redirect(array(
      'plugin'     => null,
      'controller' => 'co_people',
      'action'     => 'canvas',
      $actorCoPersonId
    ));
  }
}

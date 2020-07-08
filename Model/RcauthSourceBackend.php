<?php
/**
 * COmanage Registry RCAuth OrgIdentitySource Backend Model
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

App::uses("OrgIdentitySourceBackend", "Model");

class RcauthSourceBackend extends OrgIdentitySourceBackend {
  public $name = "RcauthSourceBackend";

  // XXX depricated in Cakephp 3.x version and beyond
  public $useTable = false;

  protected $mpOA2Server = null;

  /**
   * @param $mpOA2Url   the Url of the Masterportal Oauth2 server of the RCAUTH CA
   */
  public function getMPOPA2endpoints($mpOA2Url){
    if($this->mpOA2Server === null) {
      $this->mpOA2Server = new mpCfgUrl($mpOA2Url);
    }
  }

  /**
   * @return MP Oauth2 Server Server Config Object
   */
  public function getMpOA2Server()
  {
    return $this->mpOA2Server;
  }

  /**
   * Generate an RCAUTH callback URL.  *
   * @param null $oisid
   * @return Array URL, in Cake array format
   * @since  COmanage Registry v3.1.0
   */
  public function callbackUrl($oisid=null) {
    /*
     * The plugin uses the following entries and builds the redirect url. For a given server the provided
     * url should look as follows:
     * template url: https://server-address/registry/rcauth_source/rcauth_source_co_petitions/selectOrgIdentityAuthenticate
     * or
     * https://server-address/registry/[plugin]/[controller]/[action]
     * In our case the final redirect url is:
     * https://snf-761236.vm.okeanos.grnet.gr/registry/voms_source/voms_source_co_petitions/selectOrgIdentityAuthenticate
     * */

    return array(
      'plugin'     => 'rcauth_source',
      'controller' => 'rcauth_source_co_petitions',
      'action'     => 'selectOrgIdentityAuthenticate'
    );
  }

  /**
   * @param $redirectUri
   * @param String $clientId
   * @param String $clientSecret
   * @param String $code
   * @return Object json
   */
  public function exchangeCode($redirectUri, $clientId, $clientSecret, $code) {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);

    $params = array(
      'grant_type'    => 'authorization_code',
      'code'          => $code,
      'redirect_uri'  => $redirectUri,
      'client_id'     => $clientId,
      'client_secret' => $clientSecret
    );


    $response = RcauthSourceUtils::do_curl($this->mpOA2Server->getTokenEndpoint(),$params,$error, $info);
    if(!empty($info['http_code'])){
      // The request returned successfully. Dump data into an object, check their validity and return
      // data object from json decode
      // $data->access_token
      // $data->refresh_token
      // $data->token_type
      // $data->expires_in
      $data =json_decode($response);

      // We'll get a 200 response on success or failure
      if(!empty($data->access_token)) {
        return $data;
      }
    }elseif (!empty($error)){
      $this->log('@exchangeCode:curl http post failed: msg => '.$error, LOG_DEBUG);
      // There should be an error in the response
      throw new RuntimeException(_txt('er.rcauthsource.code',$error));
    }
  }

  /**
   * Generate the set of attributes for the IdentitySource that can be used to map
   * to group memberships. The returned array should be of the form key => label,
   * where key is meaningful to the IdentitySource (eg: a number or a field name)
   * and label is the localized string to be displayed to the user. Backends should
   * only return a non-empty array if they wish to take advantage of the automatic
   * group mapping service.
   *
   * @since  COmanage Registry v3.1.0
   * @return Array As specified
   */

  public function groupableAttributes() {
    // Not currently supported
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    return array();
  }

  /**
   * Obtain all available records in the IdentitySource, as a list of unique keys
   * (ie: suitable for passing to retrieve()).
   *
   * @since  COmanage Registry v3.1.0
   * @return Array Array of unique keys
   * @throws DomainException If the backend does not support this type of requests
   */
  public function inventory() {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    throw new DomainException("NOT IMPLEMENTED");
  }


  /**
   * Obtain an access token from an API ID and secret.
   *
   * @since  COmanage Registry v3.1.0
   * @param  String $access_token
   * @return Object Json Object with user info and the Subject DN
   * @throws RuntimeException
   */
  protected function queryRcauthApi($access_token) {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);

    $options = array(
      'access_token' => $access_token
    );

    // The request will return a json with the following format and fields
    //  {
    //     "sub":"025659b401b45793253dbe525111c8e54145569de85e544dd557ef6825d3814c@example.org",
    //     "idp":"https://aai.example.org/proxy/saml2/idp/metadata.php",
    //     "eduPersonTargetedID":"https://aai.example.org/proxy/saml2/idp/metadata.php!7c066f48d4b19621a3c5bd4c7afd5882b20ab30d",
    //     "cert_subject_dn":"CN=John Doe yCKcijJUgi9e8Y4s,O=Example Org,OU=AAI,O=Example",
    //     "idp_display_name":"AAI Example",
    //     "name":"John Doe",
    //     "eduPersonUniqueId":"025659b401b45793253dbe525111c8e54145569de85e544dd557ef6825d3814c@example.org",
    //     "given_name":"John",
    //     "family_name":"Doe",
    //     "email":"jdoe@mail.com"
    //  }
    $response = RcauthSourceUtils::do_curl($this->mpOA2Server->getUserinfoEndpoint(),$options,$error, $info);


    if( (int)$info['http_code'] >= 400
        && (int)$info['http_code'] < 500 ) {
      // Most likely retrieving an invalid rcauth
      throw new InvalidArgumentException(_txt('er.rcauthsource.search', array($info['http_code'])));
    }

    if((int)$info['http_code'] !== 200) {
      // This is probably an RDF blob, which is slightly annoying to parse.
      // Rather than do it properly since we don't parse RDF anywhere else,
      // we return a generic error.
      throw new RuntimeException(_txt('er.rcauthsource.search', array($info['http_code'])));
    }
    return json_decode($response);
  }


  /**
   * Construct the route for the access token request to RCAuth
   *
   * @param $id
   * @param $oiscfg_id
   * @return string route
   * @since  COmanage Registry v3.1.0
   */
  public function constructAccessTokenRequest($id, $oiscfg_id) {
    // Construct the callback route
    $redirectUri = Router::url($this->callbackUrl(), array('full' => true));
    // Construct the scope
    $scope = "";
    foreach ($this->getMpOA2Server()->getScopesSupported() as $key => $value) {
      $scope .= $value . " ";
    }
    $scope = trim($scope);
    // Construct the url
    $url = $this->getMpOA2Server()->getAuthorizationEndpoint() . "?";
    $url .= "response_type=code";
    $url .= "&scope=" . urlencode($scope);
    $url .= "&client_id=" . $this->pluginCfg['RcauthSource']['clientid'];
    $url .= "&state=" . base64_encode("{\"pass\" : {$id}, \"named\" : {$oiscfg_id} }");
    $url .= "&redirect_uri=" . urlencode($redirectUri);
    if (!empty($this->pluginCfg['RcauthSource']['idphint'])) {
      $url .= "&idphint=" . urlencode(trim($this->pluginCfg['RcauthSource']['idphint']));
    }

    return $url;
  }
  /**
   * Convert a raw result, as from eg retrieve(), into an array of attributes that
   * can be used for group mapping.
   *
   * @since  COmanage Registry v3.1.0
   * @param  String $raw Raw record, as obtained via retrieve()
   * @return Array Array, where keys are attribute names and values are lists (arrays) of attributes
   */
  public function resultToGroups($raw) {
    // Not currently supported
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    return array();
  }

  /**
   * Convert a search result into an Org Identity.
   *
   * @since  COmanage Registry v3.1.0
   * @param  Array $result RCAUTH Search Result. This is an object not an array
   * @return Array Org Identity and related models, in the usual format
   */
  protected function resultToOrgIdentity($result) {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    $orgdata = array();
    // XXX what if more than one attribute?
    $orgdata['OrgIdentity'] = array();

    // Until we have some rules, everyone is a member
    $orgdata['OrgIdentity']['affiliation'] = AffiliationEnum::Member;

    // XXX document
    $orgdata['Name'] = array();

    if(!empty($result->name)) {
      $orgdata['Name'][0]['given'] = (string)$result->given_name;
    }
    if(!empty($result->family_name)) {
      $orgdata['Name'][0]['family'] = (string)$result->family_name;
      // Populate primary_name and type in the caller instead of here?
      $orgdata['Name'][0]['primary_name'] = true;
      // XXX this should be configurable
      $orgdata['Name'][0]['type'] = NameEnum::Alternate;
    }

    if(!empty((string)$result->cert_subject_dn)) {
      // XXX for now i will assume that Cert Model is always available
      $orgdata['Cert'][0]['subject'] = (string)$result->cert_subject_dn;
      // Get the issuer from the config
      if (!empty($this->pluginCfg['issuer'])) {
        $orgdata['Cert'][0]['issuer'] = (string)$this->pluginCfg['issuer'];
      }
    }

    // More attributes to add in the future
    return $orgdata;
  }

  /**
   * Retrieve a single record from the IdentitySource. The return array consists
   * of two entries: 'raw', a string containing the raw record as returned by the
   * IdentitySource backend, and 'orgidentity', the data in OrgIdentity format.
   *
   * @param  String $access_token retrieve record
   * @return Array As specified
   * @throws InvalidArgumentException if not found
   * @throws OverflowException if more than one match
   */
  public function retrieve($access_token) {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    try {
      $records = $this->queryrcauthApi($access_token);
    }
    catch(InvalidArgumentException $e) {
      throw new InvalidArgumentException(_txt('er.rcauthsource.token.none'));
    }
    return array(
      'raw' => json_encode($records),
      'orgidentity' => $this->resultToOrgIdentity($records)
    );
  }

  /**
   * Perform a search against the IdentitySource. The returned array should be of
   * the form uniqueId => attributes, where uniqueId is a persistent identifier
   * to obtain the same record and attributes represent an OrgIdentity, including
   * related models.
   *
   * @since  COmanage Registry v3.1.0
   * @param  Array $attributes Array in key/value format, where key is the same as returned by searchAttributes()
   * @return Array Array of search results, as specified
   * @throws InvalidArgumentException
   * @throws RuntimeException
   */
  public function search($attributes) {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    throw new DomainException("NOT IMPLEMENTED");
  }

  /**
   * Generate the set of searchable attributes for the IdentitySource.
   * The returned array should be of the form key => label, where key is meaningful
   * to the IdentitySource (eg: a number or a field name) and label is the localized
   * string to be displayed to the user.
   *
   * @since  COmanage Registry v3.1.0
   * @return Array As specified
   */
  public function searchableAttributes() {
    // By default, rcauth uses a free form search. It is possible to search on
    // specific fields (eg: email), though for the initial implementation we
    // won't support that.
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    return array(
      // XXX This really isn't the right language key, we want an fd.*
      'q' => _txt('op.search')
    );
  }
}
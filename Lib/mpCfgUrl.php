<?php
App::uses('HttpSocket', 'Network/Http');
App::uses('enum.php', 'RcauthSource.Lib');

class mpCfgUrl {
  private $__httpClient = null;
  private $__openIdCfgJson = null;

  // parse json and fill the variables
  function __construct($url){
    // if the url variable has not been defined then do nothing
    if(!empty($url)) {
      $response = $this->httpClient()->get($url, array(), array());
      if($response->isOk()) {
        $this->__openIdCfgJson = json_decode($response, true);
      }
      else {
        $error = json_decode($response->body);
        // There should be an error in the response
        $ret_msg = $error->error . '(' . $response->code . ')';
        throw new RuntimeException(_txt('er.rcauthsource.mpCfgUrl', $ret_msg));
      }
    }
  }

  /**
   * @return HttpSocket
   */
  public function httpClient () {
    if($this->__httpClient === null) {
      $this->__httpClient = new HttpSocket(array(
        'timeout' => 3
     ));
    }
    return $this->__httpClient;
  }

  /**
   * Get the value of authorizationEndpoint
   */
  public function getAuthorizationEndpoint()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::AUTH_ENDPOINT];
  }

  /**
   * Get the value of registrationEndpoint
   */
  public function getRegistrationEndpoint()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::REGISTER_ENDPOINT];
  }

  /**
   * Get the value of jwksUri
   */
  public function getJwksUri()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::JWKS_URI];
  }

  /**
   * Get the value of scopesSupported
   */
  public function getScopesSupported()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::SCOPES_SUP];
  }

  /**
   * Get the value of issuer
   */
  public function getIssuer()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::ISSUER];
  }

  /**
   * Get the value of tokenEndpoint
   */
  public function getTokenEndpoint()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::TOKEN_ENDPOINT];
  }

  /**
   * Get the value of userinfoEndpoint
   */
  public function getUserinfoEndpoint()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::USERINFO_ENDPOINT];
  }

  /**
   * Get the value of tokenEndpointAuthMethodsSupported
   */
  public function getTokenEndpointAuthMethodsSupported()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::TOKEN_ENDPOINT_AM_SUP];
  }

  /**
   * Get the value of subjectTypesSupported
   */
  public function getSubjectTypesSupported()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::SUBJECT_TYP_SUP];
  }

  /**
   * Get the value of responseTypesSupported
   */
  public function getResponseTypesSupported()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::RESPONSE_TYP_SUP];
  }

  /**
   * Get the value of claimsSupported
   */
  public function getClaimsSupported()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::CLAIMS_SUP];
  }

  /**
   * Get the value of idTokenSigningAlgValuesSupported
   */
  public function getIdTokenSigningAlgValuesSupported()
  {
    return $this->__openIdCfgJson[RcauthSourceMPEndpointEnum::ID_TOKEN_SIG_ALG_VAL_SUP];
  }
}

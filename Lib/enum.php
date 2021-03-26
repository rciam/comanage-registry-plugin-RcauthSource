<?php

class RcauthSourceMPEndpointEnum
{
  const AUTH_ENDPOINT = 'authorization_endpoint';
  const REGISTER_ENDPOINT = 'registration_endpoint';
  const JWKS_URI = 'jwks_uri';
  const ISSUER = 'issuer';
  const TOKEN_ENDPOINT = 'token_endpoint';
  const USERINFO_ENDPOINT = 'userinfo_endpoint';
  const TOKEN_ENDPOINT_AM_SUP = 'token_endpoint_auth_methods_supported';
  const SUBJECT_TYP_SUP = 'subject_types_supported';
  const SCOPES_SUP = 'scopes_supported';
  const RESPONSE_TYP_SUP = 'response_types_supported';
  const CLAIMS_SUP = 'claims_supported';
  const ID_TOKEN_SIG_ALG_VAL_SUP = 'id_token_signing_alg_values_supported';
}

class RcauthSourceAssuranceComponentEnum
{
  const IdentifierUniqueness  = 'ID';
  const IdentityAssurance     = 'IAP';
  const AttributeAssurance    = 'ATP';
  const AssuranceProfile      = 'profile';
  const type = array(
    'ID'  => 'Identifier Uniqueness',
    'IAP' => 'Identity Assurance',
    'ATP' => 'Attribute Assurance',
    'profile' => 'Profile Assurance',
  );
}
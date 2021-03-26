<?php

class RcauthSourceMPEndpointEnum
{
 public const AUTH_ENDPOINT = 'authorization_endpoint';
 public const REGISTER_ENDPOINT = 'registration_endpoint';
 public const JWKS_URI = 'jwks_uri';
 public const ISSUER = 'issuer';
 public const TOKEN_ENDPOINT = 'token_endpoint';
 public const USERINFO_ENDPOINT = 'userinfo_endpoint';
 public const TOKEN_ENDPOINT_AM_SUP = 'token_endpoint_auth_methods_supported';
 public const SUBJECT_TYP_SUP = 'subject_types_supported';
 public const SCOPES_SUP = 'scopes_supported';
 public const RESPONSE_TYP_SUP = 'response_types_supported';
 public const CLAIMS_SUP = 'claims_supported';
 public const ID_TOKEN_SIG_ALG_VAL_SUP = 'id_token_signing_alg_values_supported';
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
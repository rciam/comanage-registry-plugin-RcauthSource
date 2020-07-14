# comanage-registry-plugin-RcauthSource
This is an [Organizational Identity Source COmanage Plugin](https://spaces.at.internet2.edu/display/COmanage/Organizational+Identity+Source+Plugins) that will act as an OAuth 2.0 client for https://rcauth.eu/


## Installation

1. Run `git clone https://github.com/rciam/comanage-registry-plugin-RcauthSource.git /path/to/comanage/local/Plugin/RcauthSource`
2. Run `cd /path/to/comanage/app`
3. Run `Console/clearcache`
4. Run `Console/cake schema create --file schema.php --path /path/to/comanage/local/Plugin/RcauthSource/Config/Schema`

## Schema update
Not yet implemented
 
## Configuration

After the installation, you have to configure the plugin before using it. 
1. Navigate to Configuration > [Organizational Identity Sources](https://spaces.at.internet2.edu/display/COmanage/Organizational+Identity+Sources) > Add Organizational Identity Source
2. Add an RcauthSource plugin:
   - Plugin: RcauthSource
   - Status: Active
   - [Sync Mode](https://spaces.at.internet2.edu/display/COmanage/Registry+Pipelines#RegistryPipelines-SyncStrategies): Manual 
3. Configure the RcauthSource Plugin
   - IdpHint parameter
     - Optionally the VO portal can redirect the user to a specific IdP by also sending an idphint parameter
   - MasterPortal OP configuration URL
     - URL of the well known configuration of MasterPortal's OP
   - Client ID
     - Client ID obtained from registering with the RCAUTH Public ID
   - Client Secret
     - Client Secret obtained from registering with the RCAUTH Public ID
   - DN Issuer
     - The DN of the certificate issuer
4. Navicate to Configuration > [Pipelines](https://spaces.at.internet2.edu/display/COmanage/Registry+Pipelines) -> Add Pipeline
   - Status: Active
   - Match Strategy: Do not Match

## License

Licensed under the Apache 2.0 license, for details see [LICENSE](https://github.com/rciam/comanage-registry-plugin-RcauthSource/blob/master/LICENSE).

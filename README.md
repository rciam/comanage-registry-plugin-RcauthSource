# comanage-registry-plugin-RcauthSource
This is an [Organizational Identity Source COmanage Plugin](https://spaces.at.internet2.edu/display/COmanage/Organizational+Identity+Source+Plugins) that will act as an OAuth 2.0 client for https://rcauth.eu/


## Installation

1. Run `git clone https://github.com/rciam/comanage-registry-plugin-RcauthSource.git /path/to/comanage/local/Plugin/RcauthSource`
2. Run `cd /path/to/comanage/app`
3. Run `Console/clearcache`
4. Run `Console/cake schema create --file schema.php --path /path/to/comanage/local/Plugin/RcauthSource/Config/Schema`

## Schema update
1. Run `cd /path/to/comanage/app`
2. Run `Console/cake RcauthSource.upgradeVersion xyz` (e.g To upgrade to version 1.1.0, 'xyz' will take the value 110)
 
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
   - Provisioning
     - Enable or disable provisioning for this plugin
   - Scopes
     - Single space separated list of scopes to request from MasterPortal
   - Assurance
     - Assurance value and type of the RCauth Certificate
4. Navicate to Configuration > [Pipelines](https://spaces.at.internet2.edu/display/COmanage/Registry+Pipelines) > Add Pipeline
   - Status: Active
   - Match Strategy: Do not Match

## Compatibility matrix

This table matches the Plugin version with the supported COmanage version.

| Plugin |  COmanage |    PHP    |
|:------:|:---------:|:---------:|
| v1.x   | v3.1.x    | &gt;=v5.6 |

## License

Licensed under the Apache 2.0 license, for details see [LICENSE](https://github.com/rciam/comanage-registry-plugin-RcauthSource/blob/master/LICENSE).

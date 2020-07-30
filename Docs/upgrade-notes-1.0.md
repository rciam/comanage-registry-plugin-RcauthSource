# Upgrade Notes for RcauthSource 1.0.0
* From COmanage configuration page remove all configured Organizational Identity Sources that use RcauthSource Plugin
  * COmanage > Configure > Organizational Identity Sources
* Update the cm_rcauth_sources table
```sql
-- Changes for Rcauth new plugin version
alter table cm_rcauth_sources add column provision boolean;
alter table cm_rcauth_sources add column mp_oa2_server varchar(256);
alter table cm_rcauth_sources add column idphint varchar(256);

alter table cm_rcauth_sources drop column access_token;
alter table cm_rcauth_sources drop column id_token;
alter table cm_rcauth_sources drop column refresh_token;
```
* Pull the changes from [RCIAM COManage branch](https://github.com/rciam/comanage-registry/tree/rciam-3.1.x)
* [Clone/Install RcauthSource](https://github.com/rciam/comanage-registry-plugin-RcauthSource) plugin in /path/to/comanage/local/Plugin
  * It is VERY IMPORTANT that the plugin gets extracted in a **directory** named **RcauthSource**
* Clear COmanage Cache so that model change take affect.
```bash
su - www-data -s /bin/bash -c "cd /path/to/comanage/app && ./Console/clearcache"
```
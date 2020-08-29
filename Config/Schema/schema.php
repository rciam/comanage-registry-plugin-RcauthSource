<?php 
class RcauthSourceSchema extends CakeSchema {

  public function before($event = array()) {
    // No Database cache clear will be needed afterwards
    $db = ConnectionManager::getDataSource($this->connection);
    $db->cacheSources = false;

    if (isset($event['drop'])) {
      switch ($event['drop']) {
        case 'rcauth_sources':
          $RcauthSource = ClassRegistry::init('RcauthSource.RcauthSource');
          $RcauthSource->useDbConfig = $this->connection;
          $backup_file = __DIR__ . '/rcauth_sources_' . date('y_m_d') . '.csv';
          if(!file_exists($backup_file)) {
            touch($backup_file);
            chmod($backup_file, 0766);
          }
          try {
            $RcauthSource->query("COPY cm_rcauth_sources TO '" . $backup_file . "' DELIMITER ',' CSV HEADER");
          } catch (Exception $e){
            // Ignore the Exception
          }
          break;
      }
    }

    return true;
  }

  public function after($event = array()) {
    if (isset($event['create'])) {
      switch ($event['create']) {
        case 'rcauth_sources':
          $RcauthSource = ClassRegistry::init('RcauthSource.RcauthSource');
          $RcauthSource->useDbConfig = $this->connection;
          // Add the constraints or any other initializations
          $RcauthSource->query("ALTER TABLE ONLY public.cm_rcauth_sources ADD CONSTRAINT cm_rcauth_sources_org_identity_source_id_fkey FOREIGN KEY (org_identity_source_id) REFERENCES public.cm_org_identity_sources(id)");
          break;
      }
    }
  }

  public $rcauth_sources = array(
    'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 11, 'key' => 'primary'),
    'org_identity_source_id' => array('type' => 'integer', 'null' => true, 'default' => null),
    'provision' => array('type' => 'integer', 'null' => true, 'default' => null),
    'clientid' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 80),
    'client_secret' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1024),
    'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
    'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
    'issuer' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256),
    'idphint' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256),
    'mp_oa2_server' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256),
    'scopes' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256),
    'indexes' => array(
      'PRIMARY' => array('unique' => true, 'column' => 'id'),
      'cm_rcauth_sources_i1' => array('unique' => true, 'column' => 'org_identity_source_id')
    ),
    'tableParameters' => array()
  );

}

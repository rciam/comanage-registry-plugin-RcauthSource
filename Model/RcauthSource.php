<?php

class RcauthSource extends AppModel {
  // Required by COmanage Plugins
  public $cmPluginType = "orgidsource";

  public $uses = array("Cert");

  // Document foreign keys
  public $cmPluginHasMany = array();

  // Association rules from this model to other models
  public $belongsTo = array("OrgIdentitySource");

  // Default display field for cake generated views
  public $displayField = "description";

  // Validation rules for table elements
  public $validate = array(
    'org_identity_source_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'An Org Identity Source ID must be provided'
    ),
    'clientid' => array(
      'rule' => 'notBlank',
      'required' => true,
      'allowEmpty' => false
    ),
    'client_secret' => array(
      'rule' => 'notBlank',
      'required' => true,
      'allowEmpty' => false
    ),
    'idphint' => array(
      'rule' => array('url', true),
      'required' => false,
      'allowEmpty' => true
    ),
    'mp_oa2_server' => array(
      'rule' => array('url', true),
      'required' => false,
      'allowEmpty' => true
    ),
    'issuer' => array(
      'content' => array(
        'rule' => array('maxLength', 400),
        'required' => false,
        'allowEmpty' => false,
        'message' => 'Please enter a valid cert issuer DN',
      ),
      'filter' => array(
        'rule' => array('validateInput'),
      ),
    ),
    'provision' => array(
      'rule' => 'notBlank',
      'required' => false,
      'allowEmpty' => true
    )
  );

  /**
   * Actions to take before a save operation is executed.
   *
   * @since  COmanage Registry v3.1.0
   * @return Boolean
   */
  public function beforeSave($options = array()) {
    $this->log(__METHOD__ . '::@',LOG_DEBUG);
    return true;
  }

  /**
   * Expose menu items.
   *
   * @since COmanage Registry v3.1.0
   * @return Array with menu location type as key and array of labels, controllers, actions as values.
   */
  public function cmPluginMenus() {
    $this->log(__METHOD__ . '::@',LOG_DEBUG);
    return array();
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
    try {
      $dbc = $this->getDataSource();
      $dbc->begin();
      $this->CoOrgIdentityLink->delete($ccoil_id);
      $dbc->commit();
    } catch (Exception $e) {
      throw new RuntimeException(_txt('er.delete', array('RCAuth Module')));
      $dbc->rollback();
    }
  }
}

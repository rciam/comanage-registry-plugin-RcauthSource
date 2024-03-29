<!--
/**
 * COmanage Registry RCAUTH Source Plugin Fields
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
-->
<?php
  // Determine if fields are editable
  $e = false;
  
  if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
    $e = true;
    
  // We shouldn't get here if we don't have at least read permission, but check just in case
  
  if(!$e && !$permissions['view'])
    return false;

  // Add breadcrumbs
  print $this->element("coCrumb");

  $args = array();
  $args['plugin'] = null;
  $args['controller'] = 'org_identity_sources';
  $args['action'] = 'index';
  $args['co'] = $cur_co['Co']['id'];
  $this->Html->addCrumb(_txt('ct.org_identity_sources.pl'), $args);

  $args = array();
  $args['plugin'] = null;
  $args['controller'] = 'org_identity_sources';
  $args['action'] = 'edit';
  $args[] = $vv_oisid;
  $this->Html->addCrumb($rcauth_sources[0]['OrgIdentitySource']['description'], $args);

  $crumbTxt = _txt('op.config');
  $this->Html->addCrumb($crumbTxt);

  print $this->Form->hidden('org_identity_source_id', array('default' => $vv_oisid)) . "\n";
  
  $l = 1;
?>
<ul id="<?php print $this->action; ?>_rcauth_source" class="fields form-list">
  <li>
    <div class="field-name">
      <div class="field-title"><?php print _txt('pl.rcauthsource.redirect_url'); ?></div>
    </div>
    <div class="field-info">
      <input type="text" readonly="true" size="60" value="<?php print $this->Html->url($vv_rcauth_redirect_url, true); ?>" />
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title"><?php print _txt('pl.rcauthsource.idphint'); ?></div>
      <div class="field-desc"><?php print _txt('pl.rcauthsource.idphint.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('idphint', array('size' => 50)) : filter_var($rcauth_sources[0]['RcAuthSource']['idphint'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title"><?php print _txt('pl.rcauthsource.mp_oa2_server'); ?>
        <span class="required">*</span>
      </div>     
      <div class="field-desc"><?php print _txt('pl.rcauthsource.mp_oa2_server.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('mp_oa2_server', array('size' => 50)) : filter_var($rcauth_sources[0]['RcAuthSource']['mp_oa2_server'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.rcauthsource.clientid'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.rcauthsource.clientid.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('clientid', array('size' => 50)) : filter_var($rcauth_sources[0]['RcAuthSource']['clientid'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.rcauthsource.secret'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.rcauthsource.secret.desc'); ?></div>
    </div>
    <div class="field-info">
    <?php 
      $value = empty($vv_rcauth_client_secret) ? ''
                    : filter_var($vv_rcauth_client_secret, FILTER_SANITIZE_SPECIAL_CHARS);
      print $this->Form->input('client_secret', array('size' => 50, 'maxlength'=>'1024', 'value' => $value));
    ?>
    </div>
  </li>
  <li>
    <div class="field-name">
        <div class="field-title">
          <?php print _txt('pl.rcauthsource.issuer'); ?>
          <span class="required">*</span>
        </div>
        <div class="field-desc"><?php print _txt('pl.rcauthsource.issuer.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php print ($e ? $this->Form->input('issuer', array('size' => 50, 'maxlength'=>'400')) : filter_var($rcauth_sources[0]['RcauthSource']['issuer'],FILTER_SANITIZE_SPECIAL_CHARS)); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.rcauthsource.scopes'); ?>
        <span class="required">*</span>
      </div>
      <div class="field-desc"><?php print _txt('pl.rcauthsource.scopes.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php
        $value = empty($rcauth_sources[0]['RcauthSource']['scopes']) ? ''
                    : filter_var(str_replace(","," ",$rcauth_sources[0]['RcauthSource']['scopes']), FILTER_SANITIZE_SPECIAL_CHARS);
        print $this->Form->input('scopes', array('placeholder'=>'email edu.uiuc.ncsa.myproxy.getcert openid','size' => 50, 'maxlength'=>'400', 'value' => $value));
      ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.rcauthsource.provision'); ?>
      </div>
      <div class="field-desc"><?php print _txt('pl.rcauthsource.provision.desc'); ?></div>
    </div>
    <div class="field-info">
      <?php
      $checked = isset($rcauth_sources[0]["RcauthSource"]["provision"]) ? (bool)filter_var($rcauth_sources[0]["RcauthSource"]["provision"]) : false;
      print ($e ? $this->Form->checkbox('provision', array('checked' => $checked)) : (bool)$checked);
      ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print _txt('pl.rcauthsource.assurance_req'); ?>
        <span class="required">*</span>
      </div>
    </div>
    <ul class="field-children">
      <li>
        <div class="field-name">
          <div class="field-title">
            <?php print _txt('pl.rcauthsource.assurance_level'); ?>
          </div>
        </div>
        <div class="field-info">
          <?php
          if($e) {
            $default = empty($rcauth_sources[0]["RcauthSource"]['assurance_level'])
              ? ''
              : filter_var($rcauth_sources[0]["RcauthSource"]['assurance_level'],FILTER_SANITIZE_SPECIAL_CHARS);
            print $this->Form->input('assurance_level', array('default' => $default));
            if($this->Form->isFieldError('assurance_level')) {
              print $this->Form->error('assurance_level');
            }
          } else {
            print filter_var($rcauth_sources[0]["RcauthSource"]['assurance_level'],FILTER_SANITIZE_SPECIAL_CHARS);
          }
          ?>
        </div>
      </li>
      <li>
        <div class="field-name">
          <div class="field-title">
            <?php print _txt('pl.rcauthsource.assurance_level_type'); ?>
          </div>
        </div>
        <div class="field-info">
          <?php
          $attrs = array();
          $attrs['value'] = (!empty($rcauth_sources[0]["RcauthSource"]['assurance_level_type'])
            ? $rcauth_sources[0]["RcauthSource"]['assurance_level_type']
            : RcauthSourceAssuranceComponentEnum::AssuranceProfile);
          $attrs['empty'] = true;

          if ($e) {
            print $this->Form->select(
              'assurance_level_type',
              RcauthSourceAssuranceComponentEnum::type,
              $attrs
            );

            if ($this->Form->isFieldError('assurance_level_type')) {
              print $this->Form->error('assurance_level_type');
            }
          } else {
            print RcauthSourceAssuranceComponentEnum::type[$rcauth_sources[0]["RcauthSource"]['assurance_level_type']];
          }
          ?>
        </div>
      </li>
    </ul>
  </li>

  <?php if($e): ?>
    <li class="fields-submit">
      <div class="field-name">
        <span class="required"><?php print _txt('fd.req'); ?></span>
      </div>
      <div class="field-info">
        <?php print $this->Form->submit($submit_label); ?>
      </div>
    </li>
  <?php endif; ?>
</ul>

<?php
class MemberReviewModel extends Model {
  protected $_map = array(
    'org5_name' => 'name',
    'org5_address' => 'address',
    'org5_manproducts' => 'manproducts',
    'org5_companyphone' => 'companyphone',
    'org5_mobilephone' => 'mobilephone',
    'org5_linkman' => 'linkman',
    'org5_email' => 'email',
    'org5_qqcode' => 'qqcode',
    'org5_csname' => 'csname',
    'org5_csaname' => 'csaname',
    'org5_website' => 'website',
    'org5_cc2name' => 'ccname_two',
    'org5_cc1name' => 'ccname_one',
    'org5_new_linkman' => 'new_linkman',
    'org5_new_companyphone' => 'new_companyphone',
    'org5_new_mobilephone' => 'new_mobilephone',
    'org5_new_qqocde' => 'new_qqonline',
    'org5_new_email' => 'new_email',
    'org5_mid' => 'mid',
    'org5_unit' => 'unit',
  );

  protected $_auto = array(
    array('effect', 'checkeffect', 3, 'callback'),
    array('addtime', 'time', 1, 'function'),
  );

  protected $_validate = array(
    array('name','','{%ADD_REVIEW_COMPANY_UNIQUE}',0,'unique',1), 
  );

  public function checkeffect($name){
    $effect = '';
    foreach($name as $value){
      if(!$effect){
	$effect .= $value;
      }else{
	$effect .= ',' . $value;
      }
    }
    return $effect;
  }

}

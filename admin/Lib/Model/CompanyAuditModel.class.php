<?php
class CompanyAuditModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%COMPANY_NAME_EMPTY}'),
    array('address', 'require', '{%COMPANY_ADDRESS_EMPTY}'),
    array('manproducts', 'require', '{%COMPANY_MANPRODUCTS_EMPTY}'),
    array('keyword', 'require', '{%COMPANY_KEYWORD_EMPTY}'),
  );

  protected $_auto = array(
    array('jointime','time',1,'function'),
  );

  protected $_map = array(
    'companyname' => 'name',
    'companyaddress' => 'address',
    'companytypeid' => 'typeid',
    'companycsid' => 'csid',
    'companycsaid' => 'csaid',
    'companylinkman' => 'linkman',
    'companywebsite' => 'website',
    'companyccid' => 'ccid',
    'companykeyword' => 'keyword',
    'companycontent' => 'content',
  );
}


<?php
class CompanyModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%COMPANY_NAME_EMPTY}'),
    array('address', 'require', '{%COMPANY_ADDRESS_EMPTY}'),
    array('companyphone', 'require', '{%COMPANY_COMPANYPHONE_EMPTY}'),
    array('linkman', 'require', '{%COMPANY_LINKMAN_EMPTY}'),
  );

  protected $_auto = array(
    array('addtime','time',1,'function'), 
    array('updatetime','time',2,'function'), 
  );
}

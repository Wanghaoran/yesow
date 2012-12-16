<?php
class AccountModel extends CommonModel {
  protected $_validate = array(
    array('create_time', 'require', '{%CREATE_TIME_EMPTY}'),
    array('type', array(1,2), '{%STATUS_ERROR}', 0, 'in'),
    array('company', 'require', '{%COMPANY_EMPTY}'),
    array('money', 'number', '{%MONEY_ERROR}'),
  );

  protected $_auto = array(
    array('create_time', 'strtotime', 3, 'function'),
  );
}

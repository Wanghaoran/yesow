<?php
class AdminModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'),
    array('name', '', '{%NAME_UNIQUE_ERROR}', 0, 'unique'),
    array('email', 'email', '{%EMAIL_ERROR}'),
    array('status', array(0,1), '{%STATUS_ERROR}', 0, 'in'),
  );

  protected $_auto = array(
    array('last_login_ip', 'get_client_ip', 1, 'function'),
    array('last_login_time', 'time', 1, 'function'),
    array('login_count', 0),
  );
}

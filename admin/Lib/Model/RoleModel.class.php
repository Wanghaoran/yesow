<?php
class RoleModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'),
    array('name', '', '{%NAME_UNIQUE_ERROR}', 0, 'unique'),
  );

  protected $_auto = array(
    array('create_time', 'time', 1, 'function'),
    array('update_time', 'time', 3, 'function'),
  );
}

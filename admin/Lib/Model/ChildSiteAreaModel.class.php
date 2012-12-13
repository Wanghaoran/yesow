<?php
class ChildSiteAreaModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'),
    array('code', 'require', '{%SITECODE_EMPTY}'),
    array('code', '', '{%SITECODE_UNIQUE_ERROR}', 0, 'unique'),
  );

  protected $_auto = array(
    array('create_time', 'time' ,1, 'function'),
  );
}




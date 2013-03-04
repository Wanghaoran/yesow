<?php
class ChildSiteModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'),
    array('name', '', '{%NAME_UNIQUE_ERROR}', 0, 'unique'),
    array('domain', 'require', '{%DOMAIN_EMPTY}'),
    array('domain', '', '{%DOMAIN_UNIQUE_ERROR}', 0, 'unique'),
    array('isshow', array(0,1), '{%ISSHOW_ERROR}', 0, 'in'),
  );

  protected $_auto = array(
    array('create_time', 'time' ,1, 'function'),
  );
}



<?php
class InfoOneColumnModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'), 
    array('isshow', array(1,0), '{%STATUS_ERROR}', 0, 'in'),
    array('isnav', array(1,0), '{%STATUS_ERROR}', 0, 'in'),
  );
}

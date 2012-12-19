<?php
class InfoTwoColumnModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'), 
    array('isoneshow', array(1,0), '{%STATUS_ERROR}', 0, 'in'),
  );
}

<?php
class ChildSiteTemplateModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'),
    array('address', 'require', '{%TEMPLATEADDRESS_EMPTY}'),
    array('address', '', '{%TEMPLATEADDRESS_UNIQUE}', 0, 'unique'),
  );
}


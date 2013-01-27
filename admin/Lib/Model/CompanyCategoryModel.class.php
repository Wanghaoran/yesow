<?php
class CompanyCategoryModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'),
  );
}


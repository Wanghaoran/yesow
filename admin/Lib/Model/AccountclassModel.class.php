<?php
class AccountclassModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'), 
  );
}

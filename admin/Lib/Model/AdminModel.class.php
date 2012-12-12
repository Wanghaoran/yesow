<?php
class AdminModel extends CommonModel {
  protected $_validate = array(
    array('email', 'email', '{%EMAIL_ERROR}'),
  );
}

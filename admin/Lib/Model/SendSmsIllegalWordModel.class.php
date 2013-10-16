<?php
class SendSmsIllegalWordModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'),
  );

  protected $_auto = array(
    array('addtime','time',1,'function') ,
  );
}


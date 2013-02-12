<?php
class AuditSearchKeywordModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'),
    array('name','','{%AUDITKEYWORD_UNIQUE}',0,'unique',3),
  );

  protected $_auto = array(
    array('addtime','time',1,'function') ,
  );
}


<?php
class MemberModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%MEMBER_NAME_EMPTY}'),
    array('name','','{%MEMBER_NAME_UNIQUE}',0,'unique',1), 
    array('password', 'require', '{%MEMBER_PWD_EMPTY}'),
    array('email', 'require', '{%MEMBER_EMAIL_EMPTY}'),
    array('email', 'email', '{%MEMBER_EMAIL_ERROR}'),
  );

  protected $_map = array(
    'province' => 'csid',
    'memdizhi' => 'csaid',
    'memedu' => 'eduid',
    'memzhiye' => 'careerid',
    'memincome' => 'incomeid',
    'username' => 'name',
    'memtishi' => 'passwordquestion',
    'memhueda' => 'passwordanswer',
    'memximin' => 'fullname',
    'memcard' => 'idnumber',
    'memsex' => 'sex',
    'memtel' => 'tel',
    'memqq' => 'qqcode',
    'memmsn' => 'msn',
    'memaddress' => 'address',
    'memzipcode' => 'zipcode',
    'companyname' => 'unit',
    'memhomepage' => 'homepage',
  );

  protected $_auto = array(
    array('password','md5',1,'function'),
    array('status', 1),
    array('ischeck', 0),
    array('join_time', 'time', 1, 'function'),
  );
}

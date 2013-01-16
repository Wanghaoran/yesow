<?php
class MemberModel extends CommonModel {
  protected $_validate = array(
    array('name', 'require', '{%MEMBER_NAME_EMPTY}'),
    array('password', 'require', '{%MEMBER_PWD_EMPTY}'),
    array('passwordquestion', 'require', '{%MEMBER_PWDQUESTION_EMPTY}'),
    array('passwordanswer', 'require', '{%MEMBER_PWDANSWER_EMPTY}'),
    array('nickname', 'require', '{%MEMBER_NICKNAME_EMPTY}'),
    array('tel', 'require', '{%MEMBER_TEL_EMPTY}'),
    array('email', 'require', '{%MEMBER_EMAIL_EMPTY}'),
    array('email', 'email', '{%MEMBER_EMAIL_ERROR}'),
  );
}

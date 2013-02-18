<?php
$common_lang = include './Public/lang.inc.php';

$app_lang = array(
  'USER_REGISTER_SUCCESS' => '恭喜，注册成功，现在跳转到邮箱认证页面',
  'USER_REGISTER_ERROR' => '抱歉，注册失败，请返回检查',
  'USER_FORGET_PASSWORD_USERNAME_ERROR' => '用户名不存在，请返回检查',
  'USER_FORGET_PASSWORD_ANSWER_ERROR' => '密码保护问题不正确，请返回检查',
  'PASSWORD_CHANGE_SUCCESS' => '密码更改成功，请您重新登录',
  'PASSWORD_DATA_UPDATE_ERROR' => '更新失败，新密码与原密码相同，请返回修改',
  'PASSWORD_EMAIL_NAME_ERROR' => '用户名错误，此用户密码不能通过邮件找回',
  'TRADERSPASSWORD_EMPTY_ERROR' => '您还没有设置交易密码,请先设置交易密码',
  'TRADERSPASSWORD_ERROR' => '交易密码错误,请重新输入',
);

return array_merge($common_lang, $app_lang);
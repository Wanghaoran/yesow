<?php
$common_lang = include './Public/lang.inc.php';

$app_lang = array(
  'USER_REGISTER_SUCCESS' => '恭喜，注册成功，现在跳转到邮箱认证页面',
  'USER_REGISTER_ERROR' => '抱歉，注册失败，请返回检查',
);

return array_merge($common_lang, $app_lang);

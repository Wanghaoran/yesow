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
  'MONTHLY_ERROR' => '更新包月信息失败',
  'QQONLINE_ERROR' => '更新在线QQ信息失败',
  'MONTHLY_LEVEL_ERROR' => '您现在已经是包月会员，请重新选择高于您现在会员等级的包月类型',
  'QQONLINE_LIMIT' => '此公司已有在线QQ，您不属于此公司的管理帐号，因此不能添加',
  'QQONLINE_RENEW' => '在线QQ信息更新成功，现在跳转到续费订单页面',
  'QQONLINE_REPETA_ERROR' => '此在线QQ已经添加过，请勿重复添加',
);

return array_merge($common_lang, $app_lang);

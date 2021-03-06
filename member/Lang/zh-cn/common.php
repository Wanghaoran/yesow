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
  'QQONLINE_NUM_ERROR' => '一个企业最多添加8个在线QQ',
  'COMPANYPIC_ERROR' => '更新企业形象信息失败',
  'COMPANYPIC_LIMIT' => '此公司已有企业形象,请勿重复添加',
  'COMPANYPIC_RENEW' => '企业形象信息更新成功，现在跳转到续费订单页面',
  'ADVERT_ERROR' => '更新页面广告信息失败',
  'ADVERT_LIMIT' => '此广告位已售出，请选择其他广告位',
  'ADVERT_RENEW' => '页面广告信息更新成功，现在跳转到续费订单页面',
  'SEARCHRANK_ERROR' => '更新速查排名信息失败',
  'ADDPOSITION_SUCCESS' => '岗位信息添加成功,，审核通过后即可在前台显示',
  'RECOMMENDCOMPANY_ERROR' => '更新推荐商家信息失败',
  'COMPANYSHOW_ERROR' => '更新动感传媒信息失败',
  'COMPANYSHOW_RENEW' => '动感传媒信息更新成功，现在跳转到续费订单页面',
  'SEND_EMAIL_SETTING_EMPTY' => '您还没有设置发送帐号,请先设置发送帐号相关信息!',
);

return array_merge($common_lang, $app_lang);

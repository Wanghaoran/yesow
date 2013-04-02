<?php
$common_lang = include './Public/lang.inc.php';

$app_lang = array(
  'COMPANY_ADD_SUCCESS' => '感谢您对易搜的支持！您所提交的数据我们将在36小时内给予审核后通过！多谢您的合作！',
  'COMPANY_REPORT_LOGIN' => '商家报错需要您登录，请您在新页面完成登录',
  'COMPANY_REPORT_CETID_EMPTY' => '错误类型不能为空',
  'SHOP_ORDER_CREATE_ERROR' => '创建订单失败',
  'TRADERSPASSWORD_EMPTY_ERROR' => '您还没有设置交易密码,请先设置交易密码',
  'TRADERSPASSWORD_ERROR' => '交易密码错误,请重新输入',
  'ORDER_ERROR' => '生成订单错误',
  'RMB_ERROR' => 'RMB操作失败',
  'RMB_CACHE' => '更新RMB缓存失败',
  'RMB_LOG_ERROR' => 'RMB明细写入失败',
  'ORDER_UPDATE_ERROR' => '更新订单状态失败',
);

return array_merge($common_lang, $app_lang);

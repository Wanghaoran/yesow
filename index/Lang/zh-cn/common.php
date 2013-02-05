<?php
$common_lang = include './Public/lang.inc.php';

$app_lang = array(
  'COMPANY_ADD_SUCCESS' => '感谢您对易搜的支持！您所提交的数据我们将在36小时内给予审核后通过！多谢您的合作！',
  'COMPANY_REPORT_LOGIN' => '商家报错需要您登录，请您在新页面完成登录',
  'COMPANY_REPORT_CETID_EMPTY' => '错误类型不能为空',
);

return array_merge($common_lang, $app_lang);

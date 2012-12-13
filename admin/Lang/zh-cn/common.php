<?php
$common_lang = include './Public/lang.inc.php';

$app_lang = array(
  'TEMPLATEADDRESS_EMPTY' => '模板识别码不能为空',
  'TEMPLATEADDRESS_UNIQUE' => '模板识别码已经存在',
  'DOMAIN_EMPTY' => '分站二级域不能为空',
  'DOMAIN_UNIQUE_ERROR' => '分站二级域已存在',
  'SITECODE_EMPTY' => '区号不能为空',
  'SITECODE_UNIQUE_ERROR' => '区号已存在',
  'ISSHOW_ERROR' => '是否显示设置错误',
);

return array_merge($common_lang, $app_lang);

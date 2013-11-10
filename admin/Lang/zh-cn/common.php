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
  'NODE_NAME_EMPTY' => '节点名称不能为空',
  'NODE_TITLE_EMPTY' => '节点显示名称不能为空',
  'CREATE_TIME_EMPTY' => '发生时间不能为空',
  'COMPANY_EMPTY' => '消费单位不能为空',
  'AUDITKEYWORD_UNIQUE' => '关键词已存在，请勿重复添加',
  'SEARCH_WHERE_EMPTY' => '请至少选择一项搜索条件',
  'ADD_REVIEW_COMPANY_UNIQUE' => '此公司已被其它管理员添加，请勿重复添加',
);

return array_merge($common_lang, $app_lang);

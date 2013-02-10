<?php
$common_config = include './Public/config.inc.php';

$app_config = array(
  'USER_AUTH_KEY' => 'user_id',
  'URL_ROUTER_ON' => true,  //开启url路由
  'URL_ROUTE_RULES' => array(
    'info' => 'info/info',//资讯首页
    'infolist/:id' => 'info/infolist', //资讯一级栏目页
    'infodetail/:id' => 'info/infodetail', //资讯一级栏目页
    'article/:id' => 'info/article',//查看文章
    'commit' => 'info/commit',//提交评论
    'noticelist' => 'index/noticelist',//站点公告列表
    'notice/:id' => 'index/notice',//站点公告详情
    'company/:id\d' => 'company/info',//速查详情页
    'report/:id\d' => 'company/report',//速查报错页
    'change/:id\d' => 'company/change',//速查改错页
    'search' => 'company/search',//搜索
  ),
);

return array_merge($common_config, $app_config);
?>

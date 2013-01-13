<?php
$common_config = include './Public/config.inc.php';

$app_config = array(
  'USER_AUTH_KEY' => 'user_id',
  'USER_AUTH_GATEWAY' => '/Public/login',
  'URL_ROUTER_ON' => true,  //开启url路由
  'URL_ROUTE_RULES' => array(
    'info' => 'info/info',//资讯首页
    'infolist/:id' => 'info/infolist', //资讯一级栏目页
    'infodetail/:id' => 'info/infodetail', //资讯一级栏目页
    'article/:id' => 'info/article',//查看文章
    'login' => 'public/login',//登录
  ),
);

return array_merge($common_config, $app_config);
?>

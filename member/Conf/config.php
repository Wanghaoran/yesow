<?php
$common_config = include './Public/config.inc.php';

$app_config = array(
  'USER_AUTH_KEY' => 'user_id',
  'USER_AUTH_GATEWAY' => '/Public/login',
  'URL_ROUTER_ON' => true,  //开启url路由
  'URL_ROUTE_RULES' => array(
    '/^help\/(\d*)\/?(\d*)/' => 'help/index?cid=:1&aid=:2',//帮助中心
  ),
);

return array_merge($common_config, $app_config);
?>

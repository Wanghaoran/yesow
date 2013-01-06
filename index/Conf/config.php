<?php
$common_config = include './Public/config.inc.php';

$app_config = array(
  'USER_AUTH_KEY' => 'user_id',
  'USER_AUTH_GATEWAY' => '/Public/login',
  'URL_ROUTER_ON' => true,  //开启url路由
  'URL_ROUTE_RULES' => array(
    'article/:id' => 'index/article',
  ),
);

return array_merge($common_config, $app_config);
?>

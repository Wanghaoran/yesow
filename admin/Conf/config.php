<?php
$common_config = include './Public/config.inc.php';

$app_config = array(
  'USER_AUTH_ON' => true,//开启验证
  'USER_AUTH_KEY' => 'yesow_uid',
  'NOT_AUTH_MODULE' => 'Public',
  'USER_AUTH_GATEWAY' => '/Public/login', //认证网关
  'ADMIN_AUTH_KEY' => 'administrator',//管理员标识
  
);

return array_merge($common_config, $app_config);
?>

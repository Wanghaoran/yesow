<?php
$common_config = include './Public/config.inc.php';

$app_config = array(
  'USER_AUTH_KEY' => 'user_id',
  'USER_AUTH_GATEWAY' => '/Public/login',
);

return array_merge($common_config, $app_config);
?>

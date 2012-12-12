<?php
$common_config = include './Public/config.inc.php';

$app_config = array(
  'USER_AUTH_ON' => true,//开启验证
  'USER_AUTH_TYPE' => 1,//验证类型
  'USER_AUTH_KEY' => 'yesow_uid',
  'NOT_AUTH_MODULE' => 'Public',
  'USER_AUTH_GATEWAY' => '/Public/login', //认证网关
  'RBAC_ROLE_TABLE' => 'yesow_role', //角色表名称
  'RBAC_USER_TABLE' => 'yesow_role_admin', //用户角色对应表
  'RBAC_ACCESS_TABLE' => 'yesow_access', //权限表名称
  'RBAC_NODE_TABLE' => 'yesow_node', //节点表名称
  'USER_AUTH_MODEL' => 'Admin',//用户表
  'AUTH_PWD_ENCODER' => 'sha1',
  'GUEST_AUTH_ON' => false,
  'ADMIN_AUTH_KEY' => 'administrator',//管理员标识
  
);

return array_merge($common_config, $app_config);
?>

<?php
class RBAC {
  //验证登录信息
  static public function authenticate($map){
    return M('Admin')->where($map)->find();
  }

  //获取当前用户所有权限  并缓存
  static public function saveAccessList(){
    //如果是总管理员，则查出所有节点
    if(!session(C('ADMIN_AUTH_KEY'))){
      $access = M('Access');
      $where_one = array();
      $where_two = array();
      $where_three = array();
      //先读出此用户的组id
      $rid = M('RoleAdmin') -> getFieldByadmin_id(session(C('USER_AUTH_KEY')), 'role_id');
      $where_one['a.role_id'] = $rid;
      $where_two['a.role_id'] = $rid;
      $where_three['a.role_id'] = $rid;
      //先查一级节点   
      $where_one['a.level'] = 1;   
      $result_one = $access -> table('yesow_access as a') -> field('n.id,n.name,n.title') -> order('sort') -> where($where_one) -> join('yesow_node as n ON a.node_id = n.id') -> select();
      //查二级节点    
      $where_two['a.level'] = 2;    
      $result_two = $access -> table('yesow_access as a') -> field('n.id,n.pid,n.name,n.title') -> order('sort') -> where($where_two) -> join('yesow_node as n ON a.node_id = n.id') -> select();
      //查三级节点    
      $where_three['a.level'] = 3;    
      $result_three = $access -> table('yesow_access as a') -> field('n.id,n.pid,n.name,n.title') -> order('sort') -> where($where_three) -> join('yesow_node as n ON a.node_id = n.id') -> select();
    }else{
      $node = M('Node');
      $result_one = $node -> field('id,pid,name,title') -> order('sort') -> where('level=1') -> select();
      $result_two = $node -> field('id,pid,name,title') -> order('sort') -> where('level=2') -> select();
      $result_three = $node -> field('id,pid,name,title') -> order('sort') -> where('level=3') -> select();
    }

    //非总管理员需要
    if(!session(C('ADMIN_AUTH_KEY'))){
    //获取各模块下有权限的操作并缓存
    $result = array();
    foreach($result_one as $value){
      $result[$value['name']] = array();
      foreach($result_two as $value_two){
	foreach($result_three as $value_three){
	  if($value_three['pid'] == $value_two['id'] && $value_two['pid'] == $value['id']){
	    $result[$value['name']][] = $value_three['name'];
	  }
	}
      }
    }
    session('acc_arr', $result);
    }
    //生成并缓存菜单
    $result_menu = array();
    foreach($result_one as $value){
      $result_menu[$value['name']] = array('_name_' => $value['title']);
      foreach($result_two as $value_two){
	if($value_two['pid'] == $value['id']){
	  $result_menu[$value['name']][$value_two['name']] = array('_name_' => $value_two['title']);
	  foreach($result_three as $value_three){
	    if($value_three['pid'] == $value_two['id']){
	      $result_menu[$value['name']][$value_two['name']][$value_three['name']] = $value_three['title'];
	    }
	  }
	}
      }
    }
    session('menu_arr', $result_menu);
  }

  //权限验证
  static public function AccessDecision(){
    if(session(C('ADMIN_AUTH_KEY')) === true){
      return true;
    }
    if(!array_key_exists(strtolower(MODULE_NAME), $_SESSION['acc_arr'])){
      return false;
    }else if(!in_array(strtolower(ACTION_NAME), $_SESSION['acc_arr'][strtolower(MODULE_NAME)])){
      return false;
    }
    return true;
  }
}

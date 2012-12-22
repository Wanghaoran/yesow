<?php
// +-----------------------------------------------------------
// | admin 项目公共 Action
// +-----------------------------------------------------------
// | extends Action
// +-----------------------------------------------------------
// | Last Update Time : 2012-11-19 00:13
// +-----------------------------------------------------------

class CommonAction extends Action {
  /*
   * 前置操作检查用户权限
   */
  public function _initialize(){
    if(C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE'))) && ACTION_NAME != 'menu'){
      import('ORG.Util.RBACI');
      if(!RBAC::AccessDecision()){
	//检查认证识别号
	if(!$_SESSION[C('USER_AUTH_KEY')]){
	  //跳转到默认网关
	  redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
	}
	//提示错误消息
	$this -> error(L('_VALID_ACCESS_'));
      }
    }
  }

    
  //左边栏
  public function menu(){
    $menu_arr = $_SESSION['menu_arr'][strtolower(MODULE_NAME)];
    unset($menu_arr['_name_']);
    foreach($menu_arr as $key => $value){
      foreach($value as $key2 => $value2){
	//排除掉不显示的模块
	if(preg_match('/add|edit|del|audit/i', $key2) || $key2 == 'groupuser' || $key2 == 'app' || $key2 == 'module' || $key2 == 'action' || $key2 == 'infomationtwocolumn'){
	  unset($menu_arr[$key][$key2]);
	}
      }
    }
    $this -> assign('menu_arr', $menu_arr);
    $this -> display();
  }


}

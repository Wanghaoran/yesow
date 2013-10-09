<?php
class CommonAction extends Action {
  public function _initialize(){
    if(C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE'))) && ACTION_NAME != 'menu' && ACTION_NAME != 'uploadfile' && ACTION_NAME != 'setpicisshow' && ACTION_NAME != 'upload' && ACTION_NAME != 'ad_upload'){
      import('ORG.Util.RBACI');
      if(!RBAC::AccessDecision()){
	if(!$_SESSION[C('USER_AUTH_KEY')]){
	  redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
	}
	$this -> error(L('_VALID_ACCESS_'));
      }
    }
  }

    
  public function menu(){
    $menu_arr = $_SESSION['menu_arr'][strtolower(MODULE_NAME)];
    unset($menu_arr['_name_']);
    foreach($menu_arr as $key => $value){
      foreach($value as $key2 => $value2){
	if(preg_match('/add|edit|del|audit/i', $key2) || $key2 == 'groupuser' || $key2 == 'app' || $key2 == 'module' || $key2 == 'action' || $key2 == 'infomationtwocolumn' || $key2 == 'infoimage'){
	  unset($menu_arr[$key][$key2]);
	}
      }
    }
    $this -> assign('menu_arr', $menu_arr);
    $this -> display();
  }

}

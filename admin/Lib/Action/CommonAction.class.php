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


}

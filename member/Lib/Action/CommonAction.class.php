<?php
class CommonAction extends Action {
  //登录验证
  public function _initialize(){
    if(!$_SESSION[C('USER_AUTH_KEY')]){
      redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
    }
  }

  //首页前置操作
  public function _before_index(){
    //获取公告
    if(S('index_yesow_notice')){
      $this -> assign('index_yesow_notice', S('index_yesow_notice'));
    }else{
      $result = M('Notice') -> field('id,title,titleattribute,addtime') -> order('addtime DESC') -> limit(10) -> select();
      S('index_yesow_notice', $result);
      $this -> assign('index_yesow_notice', $result);
    }
  }

  //安全设置前置操作
  public function _before_safe(){
    $this -> _before_index();
  }

  //特权设置前置操作
  public function _before_privilege(){
    $this -> _before_index();
  }

  //特权设置前置操作
  public function _before_organization(){
    $this -> _before_index();
  }
}

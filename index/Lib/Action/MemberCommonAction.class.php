<?php
class MemberCommonAction extends Action {
  //登录验证
  public function _initialize(){
    if(!$_SESSION[C('USER_AUTH_KEY')]){
      redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
    }
  }

  //首页前置操作
  public function _before_index(){
    //生成公告
    if(S('member_background_notice')){
      $this -> assign('member_background_notice', S('member_background_notice'));
    }else{
      $notice = M('MemberBackgroundNotice');
      $result = $notice -> field('title,addtime') -> order('addtime DESC') -> limit(10) -> select();
      S('member_background_notice', $result);
      $this -> assign('member_background_notice', $result);
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

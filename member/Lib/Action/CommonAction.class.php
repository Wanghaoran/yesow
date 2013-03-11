<?php
class CommonAction extends Action {
  //登录验证
  public function _initialize(){
    if(!$_SESSION[C('USER_AUTH_KEY')]){
      redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
    }
    //底部关于我们
    if(S('member_footer_nav')){
      $this -> assign('member_footer_nav', S('member_footer_nav'));
    }else{
      $member_footer_nav = $this -> getfooternav();
      $this -> assign('member_footer_nav', $member_footer_nav);
      S('member_footer_nav', $member_footer_nav);
    }
  }

  //获得底部关于我们
  private function getfooternav(){
    $aboutus =  M('Aboutus');
    return $aboutus -> field('id,title') -> order('sort ASC') -> select();
  }
}

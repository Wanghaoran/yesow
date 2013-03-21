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
    //在线QQ客服
    if(S('member_qqonline')){
      $this -> assign('member_qqonline', S('member_qqonline'));
    }else{
      $member_qqonline = $this -> getqqonline();
      $this -> assign('member_qqonline', $member_qqonline);
      S('member_qqonline', $member_qqonline);
    }

  }

  //获得底部关于我们
  private function getfooternav(){
    $aboutus =  M('Aboutus');
    return $aboutus -> field('id,title') -> order('sort ASC') -> select();
  }

  //获得QQ客服
  private function getqqonline(){
    $qqonline = M('Qqonline');
    $result = M('QqonlineType') -> field('id,name') -> select();
    foreach($result as $key => $value){
      $result[$key]['qq'] = $qqonline -> field('qqcode,nickname') -> where(array('tid' => $value['id'], 'csid' => array('exp', 'is null'))) -> select();
    }
    return $result;
  }
}

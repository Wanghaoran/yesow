<?php
class MoneyAction extends CommonAction {

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

  //资金管理首页(RMB管理)
  public function index(){
    $this -> display();
  }

  //e币管理前置方法
  public function _before_eb(){
    $this -> _before_index();
  }

  //e币管理
  public function eb(){
    $this -> display();
  }

  //rmb充值
  public function rmbrecharge(){
    //RMB充值第二步
    if(!empty($_GET['money'])){
      if($_SESSION['verify'] != $this -> _get('verify', 'md5')){
	R('Register/errorjump',array(L('VERIFY_ERROR')));
      }
      $this -> display('rmbrecharge_two');
      exit();
    }
    //RMB充值第三步
    if(!empty($_POST['paytype'])){
      $this -> display('rmbrecharge_three');
      exit();
    }
    $this -> display('rmbrecharge_one');
  }


}

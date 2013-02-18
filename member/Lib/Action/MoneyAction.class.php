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


}

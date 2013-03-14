<?php
class ShopAction extends CommonAction {
  
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

  //网上购物首页(易搜购物商城)
  public function index(){
    
  }

  //包月订购前置操作
  public function _before_monthly(){
    $this -> _before_index();
  }

  //资金管理 - 包月订购首页
  public function monthly(){
    $this -> display();
  }

  //资金管理 - 包月订购 - 我要包月订购
  public function buymonthly(){
    $member_level = M('MemberLevel');
    //查询会员等级
    $result = $member_level -> field('id,name') -> order('updatemoney ASC') -> select();
    $this -> assign('result', $result);

    $this -> display();
  }
}

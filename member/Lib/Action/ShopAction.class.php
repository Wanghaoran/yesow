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
    $this -> display();
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
    $member_monthly = M('MemberMonthly');
    //查询会员等级
    $result = $member_monthly -> table('yesow_member_monthly as mm') -> field('ml.id,ml.name') -> join('yesow_member_level as ml ON mm.lid = ml.id') -> group('mm.lid') -> order('ml.updatemoney ASC') -> select();
    $this -> assign('result', $result);

    $this -> display();
  }

  //订单支付页
  public function pay(){
    //根据所选价格id，查询订单信息
    $result_monthly = M('MemberMonthly') -> table('yesow_member_monthly as mm') -> field('ml.name as mlname,mm.months,mm.promotionprice') -> join('yesow_member_level as ml ON mm.lid = ml.id') -> where(array('mm.id' => $this -> _get('mid', 'intval'))) -> find();
    //生成订单号
    $result_monthly['orderid'] = date('YmdHis') . mt_rand(100000,999999);
    //总价
    $result_monthly['count'] = $result_monthly['promotionprice'] * $result_monthly['months'];
    //RMB余额是否足够支付
    $result_monthly['rmb_enough'] = $_SESSION['rmb_total'] - $result_monthly['count'] >= 0 ? 1 : 0;
    $this -> assign('result_monthly', $result_monthly);
    //查询接口信息
    $payport = M('Payport');
    $result_pay = $payport -> field('name,enname') -> where(array('status' => 1)) -> select();
    $this -> assign('result_pay', $result_pay);
    $this -> display();
  } 
}

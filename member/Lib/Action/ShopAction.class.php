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

  //网上购物首页(包月订购)
  public function index(){
    $this -> display();
  }

  //包月订购前置操作
  public function _before_shop(){
    $this -> _before_index();
  }

  //资金管理 - 易搜商城首页
  public function shop(){
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
  public function monthly_pay(){
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

  //RMB余额支付
  public function monthly_rmb_pay(){
    //获取交易密码
    $pay_pwd = M('Member') -> getFieldByid(session(C('USER_AUTH_KEY')), 'traderspassword');
    //未设置交易密码的先去设置交易密码
    if(!$pay_pwd){
      R('Register/errorjump',array(L('TRADERSPASSWORD_EMPTY_ERROR'), U('Index/safe')));
    }
    //交易密码错误
    if($pay_pwd != $_GET['pwd']){
      R('Register/errorjump',array(L('TRADERSPASSWORD_ERROR')));
    }
    //查询包月价格、月数
    $monthly_info = M('MemberMonthly') -> field('lid,months,promotionprice') -> find($this -> _get('monid', 'intval'));
    //计算总价格
    $const = $monthly_info['months'] * $monthly_info['promotionprice'];
    //生成订单
    $monthly_order = M('MonthlyOrder');
    $data = array();
    $data['ordernum'] = $this -> _get('orderid');
    $data['mid'] = session(C('USER_AUTH_KEY'));
    $data['monid'] = $this -> _get('monid');
    $data['price'] = $const;
    $data['paytype'] = 'RMB余额';
    $data['addtime'] = time();
    if(!$ordid = $monthly_order -> add($data)){
      R('Register/errorjump',array(L('ORDER_ERROR')));
    }
    //扣费
    $rmb = D('MemberRmb');
    if(!$rmb -> lessrmb($const)){
      R('Register/errorjump',array(L('RMB_ERROR')));
    }
    //更新会员余额和等级
    if(!$rmb -> rmbtotal()){
      R('Register/errorjump',array(L('RMB_CACHE')));
    }
    //扣费成功更新订单状态
    if(!$monthly_order -> save(array('status' => 3, 'id' => $ordid))){
      R('Register/errorjump',array(L('ORDER_UPDATE_ERROR')));
    }
    //写RMB消费记录
    $memberlevel = M('MemberLevel') -> getFieldByid($monthly_info['lid'], 'name');
    $log_content = "您已成功购买 {$memberlevel} 级会员包月 {$monthly_info['months']} 个月,订单号{$data['ordernum']}";
    if(!D('member://MemberRmbDetail') -> writelog($_SESSION[C('USER_AUTH_KEY')], $log_content, '消费', '-' . $const)){
      R('Register/errorjump',array(L('RMB_LOG_ERROR')));
    }
    //写包月主表
    $monthly = M('Monthly');
    $mon_data = array();
    $mon_data['mid'] = session(C('USER_AUTH_KEY'));
    $mon_data['monid'] = $this -> _get('monid', 'intval');
    $mon_data['starttime'] = time();
    $mon_data['endtime'] = $mon_data['starttime'] + ( $monthly_info['months'] * 30 * 24 * 60 * 60);
    if($monthly -> add($mon_data)){
      $info_succ = "您已成功购买 {$memberlevel} 级会员包月 {$monthly_info['months']} 个月";
      R('Register/successjump',array($info_succ, U('Shop/index')));
    }else{
      R('Register/errorjump',array(L('MONTHLY_ERROR')));
    }
  } 
}

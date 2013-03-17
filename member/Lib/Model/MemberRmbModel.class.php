<?php
class MemberRmbModel extends Model {
  //获取并缓存RMB总余额 并 更新会员等级
  public function rmbtotal($mid){
    if(empty($mid)){
      $mid = session(C('USER_AUTH_KEY'));
    }
    $total = $this -> field('rmb_pay+rmb_exchange as total') -> find($mid);
    session('rmb_total', $total['total']);
    D('member://MemberLevel') -> level($total['total']);
    return 1;
  }

  //获取余额
  public function getrmbtotal($mid){
    if(empty($mid)){
      $mid = session(C('USER_AUTH_KEY'));
    }
    return $this -> field('rmb_pay+rmb_exchange as total') -> find($mid);
  }

  //扣费，先扣rmb_exchange再扣rmb_pay
  public function lessrmb($const){
    //先查询两种余额
    $price = $this -> field('rmb_pay,rmb_exchange') -> find(session(C('USER_AUTH_KEY')));
    //如果 兑换RMB余额足够支付 此次费用
    if($price['rmb_exchange'] - $const >= 0){
      $data_rmb = array();
      $data_rmb['mid'] = session(C('USER_AUTH_KEY'));
      $data_rmb['rmb_pay'] = $price['rmb_pay'];
      $data_rmb['rmb_exchange'] = $price['rmb_exchange'] - $const;
      if($this -> save($data_rmb)){
	return 1;
      }else{
	return 0;
      }
    }else{
      //如果兑换RMB不足够支付此次信息，则用充值RMB支付
      //计算差值
      $fee = abs($price['rmb_exchange'] - $const);
      //如果差值不够支付，则退出执行
      if($price['rmb_pay'] < $fee){
	return 0;
      }
      $data_rmb = array();
      $data_rmb['mid'] = session(C('USER_AUTH_KEY'));
      $data_rmb['rmb_pay'] = $price['rmb_pay'] - $fee;
      $data_rmb['rmb_exchange'] = 0;
      if($this -> save($data_rmb)){
	return 1;
      }else{
	return 0;
      }
    }
    return 0;
  }
}

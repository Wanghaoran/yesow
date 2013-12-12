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

  //增加会员RMB
  public function addmoney($field, $money, $mid){
    $mid = !empty($mid) ? $mid : $_SESSION[C('USER_AUTH_KEY')];
    $where = array();
    $where['mid'] = $mid;
    return $this -> where($where) -> setInc($field, $money);
  }

  //减少会员RMB
  public function lessmoney($field, $money, $mid){
    $mid = !empty($mid) ? $mid : $_SESSION[C('USER_AUTH_KEY')];
    $where = array();
    $where['mid'] = $mid;
    return $this -> where($where) -> setDec($field, $money);
  }

  //自动判断并减少会员金额，标准：先减少 rmb_exchange 字段，不够的再减 rmb_pay 字段
  public function autolessmoney($money, $mid){
    $mid = !empty($mid) ? $mid : $_SESSION[C('USER_AUTH_KEY')];
    $money = abs($money);
    //查出两种余额
    $price = $this -> field('rmb_pay,rmb_exchange') -> find($mid);
    if($price['rmb_exchange'] + $price['rmb_pay'] < $money){
      return false;
    }
    //如果 兑换RMB余额足够扣除
    if($price['rmb_exchange'] >= $money){
      $data_rmb = array();
      $data_rmb['mid'] = $mid;
      $data_rmb['rmb_exchange'] = $price['rmb_exchange'] - $money;     
      return $this -> save($data_rmb);
    }else{
      //如果兑换RMB不足够支付此次信息，则用充值RMB支付
      //计算差值
      $fee = $money - $price['rmb_exchange'];
      $data_rmb = array();
      $data_rmb['mid'] = $mid;
      $data_rmb['rmb_pay'] = $price['rmb_pay'] - $fee;
      $data_rmb['rmb_exchange'] = 0;
      return $this -> save($data_rmb);
    }
  }

  //先减少 rmb_exchange 字段，不够的再减 rmb_pay 字段 可以为负
  public function autolessmoney2($money, $mid){
    $mid = !empty($mid) ? $mid : $_SESSION[C('USER_AUTH_KEY')];
    $money = abs($money);
    //查出两种余额
    $price = $this -> field('rmb_pay,rmb_exchange') -> find($mid);
    //如果 兑换RMB余额足够扣除
    if($price['rmb_exchange'] >= $money){
      $data_rmb = array();
      $data_rmb['mid'] = $mid;
      $data_rmb['rmb_exchange'] = $price['rmb_exchange'] - $money;     
      return $this -> save($data_rmb);
    }else{
      //如果兑换RMB不足够支付此次信息，则用充值RMB支付
      //计算差值
      $fee = $money - $price['rmb_exchange'];
      $data_rmb = array();
      $data_rmb['mid'] = $mid;
      $data_rmb['rmb_pay'] = $price['rmb_pay'] - $fee;
      $data_rmb['rmb_exchange'] = 0;
      return $this -> save($data_rmb);
    }
  }

  //只从充值金额中减去金额,可以为负
  public function lessonlypay($money, $mid){
    $data_rmb = array();
    $data_rmb['mid'] = $mid;
    $data_rmb['rmb_pay'] = $price['rmb_pay'] + $money;
    return $this -> save($data_rmb);
  }

  //只从充值金额中减去金额，不可为负
  public function lessonlypayno($money, $mid=''){
    $mid = !empty($mid) ? $mid : $_SESSION[C('USER_AUTH_KEY')];
    $pay = $this -> getFieldBymid($mid, 'rmb_pay');
    if($pay < $money){
      return false;
    }else{
      return $this -> where(array('mid' => $mid)) -> setDec('rmb_pay', $money);
    }
  }
}

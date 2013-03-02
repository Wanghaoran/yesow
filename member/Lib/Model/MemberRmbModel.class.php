<?php
class MemberRmbModel extends Model {
  //获取并缓存RMB总余额 并 更新会员等级
  public function rmbtotal($mid){
    $total = $this -> field('rmb_pay+rmb_exchange as total') -> find($mid);
    session('rmb_total', $total['total']);
    D('member://MemberLevel') -> level($total['total']);
  }

  //获取余额
  public function getrmbtotal($mid){  
    return $this -> field('rmb_pay+rmb_exchange as total') -> find($mid);
  }
}

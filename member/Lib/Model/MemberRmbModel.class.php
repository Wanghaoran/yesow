<?php
class MemberRmbModel extends Model {
  //获取RMB总余额
  public function rmbtotal($mid){
    $total = $this -> field('rmb_pay+rmb_exchange as total') -> find($mid);
    session('rmb_total', $total['total']);
  }
}

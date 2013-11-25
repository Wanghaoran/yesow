<?php
class OutpageAction extends Action {

  public function editvieworder(){
    $mid = $this -> _get('mid', 'intval');
    $where = array();
    $where['mid'] = $mid;
    $mname = M('Member') -> getFieldByid($mid, 'name');
    $this -> assign('mname', $mname);
    //shop
    $shop_order = M('ShopOrder') -> field('ordernum,paytotal,paystatus,ischeck,addtime,paytype') -> where($where) -> order('addtime DESC') -> select();
    $this -> assign('shop_order', $shop_order);
    //rmb
    $rmb_order = M('RmbOrder') -> alias('o') -> field('o.ordernum,o.price,o.status,o.ischeck,o.addtime,p.name as pname') -> join('yesow_payport as p ON o.paytype = p.enname') -> where($where) -> order('o.addtime DESC') -> select();
    $this -> assign('rmb_order', $rmb_order);
    //monthly
    $monthly_order = M('MonthlyOrder') -> alias('o') -> field('o.ordernum,o.price,o.status,o.ischeck,o.addtime,paytype') -> where($where) -> order('o.addtime DESC') -> select();
    $this -> assign('monthly_order', $monthly_order);
    //qqonline
    $qqonline_order = M('QqonlineOrder') -> alias('o') -> field('o.ordernum,o.price,o.status,o.ischeck,o.addtime,paytype') -> where($where) -> order('o.addtime DESC') -> select();
    $this -> assign('qqonline_order', $qqonline_order);
    $this -> display();
  }
}

<?php
class ShopOrderShopModel extends Model {
  //根据订单号查询商品
  public function shopbyordernum($oid){
    return $this -> field('id,shoptitle,price,num,totalmoney') -> where(array('ordernum' => $oid)) -> select();
  }

  //根据单号获取商品总价
  public function totalpaybyordernum($oid){
    return $this -> where(array('ordernum' => $oid)) -> sum('totalmoney');
  }

}

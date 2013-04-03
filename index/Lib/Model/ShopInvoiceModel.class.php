<?php
class ShopInvoiceModel extends Model {
  //获取此金额对应的税率
  public function getradio($money){
    $result = $this -> field('ratio') -> where(array('money' => array('elt', $money))) -> order('money DESC') -> find();
    return $result['ratio'];
  }
  //获取最低金额的费率所对应的值
  public function getlowest(){
    //获取金额和费率
    $result = $this -> field('money,ratio') -> order('money ASC') -> find();
    return $result['money'] * $result['ratio'];
  }
}

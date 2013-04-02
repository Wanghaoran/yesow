<?php
class ShopInvoiceModel extends Model {
  //获取此金额对应的税率
  public function getradio($money){
    $result = $this -> field('ratio') -> where(array('money' => array('elt', $money))) -> order('money DESC') -> find();
    //如果金额小于设置的最低值，则取最低值的税率
    if(!$result){
      $result = $this -> field('ratio') -> order('money ASC') -> find();
    }

    return $result['ratio'];
  }
}

<?php
class ShopCartModel extends Model {


  //向购物车增加商品
  public function addshop($sid, $num, $mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    //商品单价
    $price = M('Shop') -> getFieldByid($sid, 'promotionprice');
    $data = array();
    $data['sid'] = $sid;
    $data['shopnum'] = $num;
    $data['mid'] = $mid;
    $data['totalmoney'] = $price * $num;
    return $this -> add($data);
  }

  //从购物车删除商品
  public function delshop($id, $mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    $del_where = array();
    $del_where['mid'] = $mid;
    if($id != 'all'){
      $del_where['id'] = $id;
    }
    return $this -> where($del_where) -> delete();
  }

  //编辑购物车中商品数量
  public function editshop($id, $shopnum){
    //查询商品id
    $sid = $this -> getFieldByid($id, 'sid');
    //查商品价格
    $price = M('Shop') -> getFieldByid($sid, 'promotionprice');
    $data = array();
    $data['id'] = $id;
    $data['shopnum'] = $shopnum;
    $data['totalmoney'] = $price * $shopnum;
    return $this -> save($data);
  }

  //查询当前用户的购物车
  public function usercart($mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    return $this -> table('yesow_shop_cart as sc') -> field('sc.id as scid,s.id,s.title,s.issend,s.promotionprice,sc.shopnum,sc.totalmoney') -> join('yesow_shop as s ON sc.sid = s.id') -> where(array('sc.mid' => $mid)) -> select();
  }

  //计算当前购物车中应付总额
  public function totalpaymoney($mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    return $this -> where(array('mid' => $mid)) -> sum('totalmoney');
  }

  //将当前用户购物车的商品信息写入订单-商品对应表
  public function writeordershop($oid, $mid){
    $order_shop = M('ShopOrderShop');
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    $shop_result = $this -> usercart($mid);
    foreach($shop_result as $value){
      $data = array();
      $data['ordernum'] = $oid;
      $data['shoptitle'] = $value['title'];
      $data['price'] = $value['promotionprice'];
      $data['num'] = $value['shopnum'];
      $data['totalmoney'] = $value['totalmoney'];
      $order_shop -> add($data);
    }
  }

}

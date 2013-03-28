<?php
class ShopAction extends CommonAction {

  //商城购物首页
  public function index(){
    $shop = M('Shop');
    $where = array();
    if(!empty($_GET['cid'])){
      $where['cid_one'] = $this -> _get('cid', 'intval');
    }

    import("ORG.Util.Page");// 导入分页类
    $count = $shop -> where($where) -> count('id');
    $page = new Page($count, 10);
    $show = $page -> show();
    //查分类下的商品信息，10条
    $result = $shop -> field('id,title,issend,marketprice,promotionprice,small_pic,remark') -> where($where) -> order('addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    //查20个最新商品
    $result_new_shop = $shop -> field('id,title') -> order('addtime DESC') -> limit(20) -> select();
    $this -> assign('result_new_shop', $result_new_shop);

    $this -> display();
  
  }
}

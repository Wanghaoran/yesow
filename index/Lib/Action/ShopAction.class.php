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

  //商品详情也
  public function info(){
    $shop = M('Shop');
    //点击量加1
    //点击量加一
    $shop -> where(array('id' => $this -> _get('id', 'intval'))) -> setInc('clickcount');
    //商品详细信息
    $result = $shop -> field('id,cid_one,issend,marketprice,promotionprice,big_pic,remark,title,content,clickcount') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //如果需要运费，则查询运费信息
    if($result['issend'] == 1){
      $result_send = M('SendType') -> field('name,money') -> order('sort ASC') -> select();
      $this -> assign('result_send', $result_send);
    }
    //查20个最新商品
    $result_new_shop = $shop -> field('id,title') -> order('addtime DESC') -> limit(20) -> select();
    $this -> assign('result_new_shop', $result_new_shop);
    //查询4个同类商品
    $result_like = $shop -> field('id,title,small_pic,marketprice,promotionprice') -> where(array('cid_one' => $result['cid_one'], 'id' => array('neq', $result['id']))) -> order('addtime DESC') -> limit(4) -> select();
    $this -> assign('result_like', $result_like);
    $this -> display();
  }
}

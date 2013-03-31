<?php
class ShopAction extends CommonAction {

  /* --------------------- 商品管理 --------------------- */

  //商品分类管理
  public function shopclass(){
    $shopclass = M('ShopClass');
    $where['pid'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    //处理搜索
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $shopclass -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    //当前页数
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    //结果
    $result = $shopclass -> field('id,name,sort,remark') -> where($where) -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  //添加商品分类
  public function addshopclass(){
    $shopclass = M('ShopClass');
    //处理添加
    if(!empty($_POST['name'])){
      if(!$shopclass -> create()){
	$this -> error($shopclass -> getError());
      }
      if($shopclass -> add()){
	//delete cache
	S('index_shop_nav', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查上级分类
    $pname = $shopclass -> getFieldByid($this -> _get('id', 'intval'), 'name');
    $this -> assign('pname', $pname);
    $this -> display();
  }

  //删除商品分类
  public function delshopclass(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $shopclass = M('ShopClass');
    if($shopclass -> where($where_del) -> delete()){
      //delete cache
      S('index_shop_nav', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑商品分类
  public function editshopclass(){
    $shopclass = M('ShopClass');
    if(!empty($_POST['name'])){
      if(!$shopclass -> create()){
	$this -> error($shopclass -> getError());
      }
      if($shopclass -> save()){
	//delete cache
	S('index_shop_nav', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $shopclass -> field('name,pid,sort,remark') -> find($this -> _get('id', 'intval'));
    $pname = $shopclass -> getFieldByid($result['pid'], 'name');
    $this -> assign('pname', $pname);
    $this -> assign('result', $result);
    $this -> display();
  }

  //派送方式管理
  public function sendtype(){
    $sendtype = M('SendType');
    //处理搜索
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $sendtype -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    //当前页数
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $sendtype -> field('id,name,money,addtime,sort,remark') -> where($where) -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加派送方式
  public function addsendtype(){
    $sendtype = D('SendType');
    //处理添加
    if(!empty($_POST['name'])){
      if(!$sendtype -> create()){
	$this -> error($sendtype -> getError());
      }
      if($sendtype -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除派送方式
  public function delsendtype(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $sendtype = M('SendType');
    if($sendtype -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑派送方式
  public function editsendtype(){
    $sendtype = D('SendType');
    if(!empty($_POST['name'])){
      if(!$sendtype -> create()){
	$this -> error($sendtype -> getError());
      }
      if($sendtype -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $sendtype -> field('name,money,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //商品详情管理
  public function shopinfo(){
    $shop = M('Shop');
    $where = array();
    //处理搜索
    if(!empty($_POST['title'])){
      $where['s.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($_POST['onecid'])){
      $where['s.cid_one'] = $this -> _post('onecid');
    }

    //记录总数
    $count = $shop -> table('yesow_shop as s') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    //当前页数
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $shop -> table('yesow_shop as s') -> field('s.id,sc.name as scname,s.title,s.marketprice,s.promotionprice,s.issend,s.clickcount,s.addtime,s.updatetime') -> where($where) -> join('yesow_shop_class as sc ON s.cid_one = sc.id') -> order('s.addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //查询商品分类
    $result_shopclass = M('ShopClass') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_shopclass', $result_shopclass);

    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加商品详情
  public function addshopinfo(){
    //处理添加
    if(!empty($_POST['title'])){
      $shop = D('Shop');
      if(!$shop -> create()){
	$this -> error($shop -> getError());
      }
      
      //两图都传
      if(!empty($_FILES['small_pic']['name']) && !empty($_FILES['big_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> small_pic = $up_data[0]['savename'];
	$shop -> big_pic = $up_data[1]['savename'];	
      //只传小图	
      }else if(!empty($_FILES['small_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> small_pic = $up_data[0]['savename'];
      //只传大图
      }else if(!empty($_FILES['big_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> big_pic = $up_data[0]['savename'];
      }
      
      if($shop -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询商品分类
    $result_shopclass = M('ShopClass') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_shopclass', $result_shopclass);
    $this -> display();
  
  }

  //删除商品详情
  public function delshopinfo(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $shop = M('Shop');
    if($shop -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑商品详情
  public function editshopinfo(){
    $shop = D('Shop');
    //处理更新
    if(!empty($_POST['title'])){
      if(!$shop -> create()){
	$this -> error($shop -> getError());
      }
      //两图都传
      if(!empty($_FILES['small_pic']['name']) && !empty($_FILES['big_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> small_pic = $up_data[0]['savename'];
	$shop -> big_pic = $up_data[1]['savename'];	
      //只传小图	
      }else if(!empty($_FILES['small_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> small_pic = $up_data[0]['savename'];
      //只传大图
      }else if(!empty($_FILES['big_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> big_pic = $up_data[0]['savename'];
      }

      if($shop -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //结果
    $result = $shop -> field('cid_one,cid_two,issend,marketprice,promotionprice,clickcount,remark,title,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询商品分类
    $result_shopclass = M('ShopClass') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_shopclass', $result_shopclass);
    //查询当前分类下的二级分类
    $result_shopclass_two = M('ShopClass') -> field('id,name') -> where(array('pid' => $result['cid_one'])) -> order('sort ASC') -> select();
    $this -> assign('result_shopclass_two', $result_shopclass_two);
    $this -> display();
  }

  /* --------------------- 商品管理 --------------------- */

  /* --------------------- 订单管理 --------------------- */

  //商城订单管理
  public function shoporder(){
    $order = M('ShopOrder');
    $result = $order -> table('yesow_shop_order as so') -> field('so.id,so.ordernum,m.name as mname,st.name as stname,so.isbull,so.addtime,so.ischeck,so.issend,so.paystatus') -> join('yesow_send_type as st ON so.sendid = st.id') -> join('yesow_member as m ON so.mid = m.id') -> order('so.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> display();
  }

  /* --------------------- 订单管理 --------------------- */
}

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
	S('index_shop', NULL, NULL, '', NULL, 'index');
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
      S('index_shop', NULL, NULL, '', NULL, 'index');
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
	S('index_shop', NULL, NULL, '', NULL, 'index');
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
	//del cache
	S('index_shop', NULL, NULL, '', NULL, 'index');
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
      //del cache
      S('index_shop', NULL, NULL, '', NULL, 'index');
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
	//del cache
	S('index_shop', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //结果
    $result = $shop -> field('cid_one,cid_two,issend,marketprice,promotionprice,big_pic,small_pic,clickcount,remark,title,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询商品分类
    $result_shopclass = M('ShopClass') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_shopclass', $result_shopclass);
    //查询当前分类下的二级分类
    $result_shopclass_two = M('ShopClass') -> field('id,name') -> where(array('pid' => $result['cid_one'])) -> order('sort ASC') -> select();
    $this -> assign('result_shopclass_two', $result_shopclass_two);
    $this -> display();
  }

  //发票税率管理
  public function invoice(){
    $invoice = M('ShopInvoice');
    //记录总数
    $count = $invoice -> count('id');
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

    $result = $invoice -> field('id,money,ratio*100 as ratio,remark') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('money ASC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //增加发票税率
  public function addinvoice(){
    //处理添加
    if(!empty($_POST['money'])){
      $invoice = M('ShopInvoice');
      $_POST['ratio'] = $_POST['ratio'] / 100;
      if(!$invoice -> create()){
	$this -> error($invoice -> getError());
      }
      if($invoice -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除发票税率
  public function delinvoice(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $invoice = M('ShopInvoice');
    if($invoice -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑发票税率
  public function editinvoice(){
    $invoice = M('ShopInvoice');
    //处理更新
    if(!empty($_POST['money'])){
      $_POST['ratio'] = $_POST['ratio'] / 100;
      if(!$invoice -> create()){
	$this -> error($invoice -> getError());
      }
      if($invoice -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $invoice -> field('id,money,ratio*100 as ratio,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  /* --------------------- 商品管理 --------------------- */

  /* --------------------- 订单管理 --------------------- */

  //商城订单管理
  public function shoporder(){
    $order = M('ShopOrder');
    //处理搜索
    $where = array();
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['so.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['so.addtime'][] = array('lt', $endtime);
    }
    //记录总数
    $count = $order -> table('yesow_shop_order as so') -> where($where) -> count('id');
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

    $result = $order -> table('yesow_shop_order as so') -> field('so.id,so.ordernum,so.paytotal,m.name as mname,st.name as stname,so.isbull,so.addtime,so.ischeck,so.issend,so.paystatus,so.paytype') -> join('yesow_send_type as st ON so.sendid = st.id') -> join('yesow_member as m ON so.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('so.addtime DESC') -> where($where) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //删除商品订单
  public function delshoporder(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $order = M('ShopOrder');
    if($order -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //通过审核商品订单
  public function passauditshoporder(){
    $order = M('ShopOrder');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($order -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //不通过审核商品订单
  public function nopassauditshoporder(){
    $order = M('ShopOrder');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($order -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //订单处理
  public function editsendshop(){
    $order = M('ShopOrder');
    //处理更新
    if(!empty($_POST['id'])){
      if(!$order -> create()){
	$this -> error($order -> getError());
      }
      if($order -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $order -> field('ordernum,issend,result') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //订单详情
  public function editshoplist(){
    $order_shop = D('index://ShopOrderShop');
    $order = M('ShopOrder');
    //根据id号查询单号
    $ordernum = $order -> getFieldByid($this -> _get('id', 'intval'), 'ordernum');
    //根据id查询快递方式
    $stname = $order -> table('yesow_shop_order as so') -> field('st.name as stname') -> join('yesow_send_type as st ON so.sendid = st.id') -> where(array('so.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('stname', $stname['stname']);
    //根据单号查询商品信息
    $result = $order_shop -> shopbyordernum($ordernum);
    $this -> assign('result', $result);
    //订单号
    $this -> assign('ordernum', $ordernum);
    //订单状态
    $order_status = $order -> field('ischeck,issend,paystatus') -> where(array('ordernum' => $ordernum)) -> find();
    $this -> assign('order_status', $order_status);
    //商品总价
    $shop_price = $order_shop -> totalpaybyordernum($ordernum);
    $this -> assign('shop_price', $shop_price);
    //快递费用
    $temp_send_price = $order -> table('yesow_shop_order as so') -> field('st.money') -> join('yesow_send_type as st ON so.sendid = st.id') -> where(array('so.ordernum' => $ordernum)) -> find();
    $this -> assign('send_price', $temp_send_price['money']);
    //应付总额
    $total_price = $order -> getFieldByordernum($ordernum, 'paytotal');
    $this -> assign('total_price', $total_price);
    //计算发票税率费用
    $invoice_price = $total_price - $shop_price - $temp_send_price['money'];
    $this -> assign('invoice_price', $invoice_price);
    //查询收货信息
    $goods_info = $order -> field('username,address,zipcode,tel,email,remark') -> where(array('ordernum' => $ordernum)) -> find();
    $this -> assign('goods_info', $goods_info);
    $this -> display();
  }

  /* --------------------- 订单管理 --------------------- */
}

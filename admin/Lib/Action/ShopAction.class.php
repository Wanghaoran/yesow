<?php
class ShopAction extends CommonAction {

  public function shopclass(){
    $shopclass = M('ShopClass');
    $where['pid'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $shopclass -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $shopclass -> field('id,name,sort,remark') -> where($where) -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addshopclass(){
    $shopclass = M('ShopClass');
    if(!empty($_POST['name'])){
      if(!$shopclass -> create()){
	$this -> error($shopclass -> getError());
      }
      if($shopclass -> add()){
	S('index_shop_nav', NULL, NULL, '', NULL, 'index');
	S('index_shop', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $pname = $shopclass -> getFieldByid($this -> _get('id', 'intval'), 'name');
    $this -> assign('pname', $pname);
    $this -> display();
  }

  public function delshopclass(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $shopclass = M('ShopClass');
    if($shopclass -> where($where_del) -> delete()){
      S('index_shop_nav', NULL, NULL, '', NULL, 'index');
      S('index_shop', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editshopclass(){
    $shopclass = M('ShopClass');
    if(!empty($_POST['name'])){
      if(!$shopclass -> create()){
	$this -> error($shopclass -> getError());
      }
      if($shopclass -> save()){
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

  public function sendtype(){
    $sendtype = M('SendType');
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $sendtype -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $sendtype -> field('id,name,money,addtime,sort,remark') -> where($where) -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addsendtype(){
    $sendtype = D('SendType');
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

  public function shopinfo(){
    $shop = M('Shop');
    $where = array();
    if(!empty($_POST['title'])){
      $where['s.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($_POST['onecid'])){
      $where['s.cid_one'] = $this -> _post('onecid');
    }

    $count = $shop -> table('yesow_shop as s') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $shop -> table('yesow_shop as s') -> field('s.id,sc.name as scname,s.title,s.marketprice,s.promotionprice,s.issend,s.clickcount,s.addtime,s.updatetime') -> where($where) -> join('yesow_shop_class as sc ON s.cid_one = sc.id') -> order('s.updatetime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $result_shopclass = M('ShopClass') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_shopclass', $result_shopclass);

    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addshopinfo(){
    if(!empty($_POST['title'])){
      $shop = D('Shop');
      if(!$shop -> create()){
	$this -> error($shop -> getError());
      }
      
      if(!empty($_FILES['small_pic']['name']) && !empty($_FILES['big_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> small_pic = $up_data[0]['savename'];
	$shop -> big_pic = $up_data[1]['savename'];	
      }else if(!empty($_FILES['small_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> small_pic = $up_data[0]['savename'];
      }else if(!empty($_FILES['big_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> big_pic = $up_data[0]['savename'];
      }
      
      if($shop -> add()){
	S('index_shop', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_shopclass = M('ShopClass') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_shopclass', $result_shopclass);
    $this -> display();
  
  }

  public function delshopinfo(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $shop = M('Shop');
    if($shop -> where($where_del) -> delete()){
      S('index_shop', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editshopinfo(){
    $shop = D('Shop');
    if(!empty($_POST['title'])){
      if(!$shop -> create()){
	$this -> error($shop -> getError());
      }
      if(!empty($_FILES['small_pic']['name']) && !empty($_FILES['big_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> small_pic = $up_data[0]['savename'];
	$shop -> big_pic = $up_data[1]['savename'];	
      }else if(!empty($_FILES['small_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> small_pic = $up_data[0]['savename'];
      }else if(!empty($_FILES['big_pic']['name'])){
	$up_data = R('Public/shop_pic_upload');
	$shop -> big_pic = $up_data[0]['savename'];
      }

      if($shop -> save()){
	S('index_shop', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $shop -> field('cid_one,cid_two,issend,marketprice,promotionprice,big_pic,small_pic,clickcount,remark,title,keyword,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $result_shopclass = M('ShopClass') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_shopclass', $result_shopclass);
    $result_shopclass_two = M('ShopClass') -> field('id,name') -> where(array('pid' => $result['cid_one'])) -> order('sort ASC') -> select();
    $this -> assign('result_shopclass_two', $result_shopclass_two);
    $this -> display();
  }

  public function invoice(){
    $invoice = M('ShopInvoice');
    $count = $invoice -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $invoice -> field('id,money,ratio*100 as ratio,remark') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('money ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addinvoice(){
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

  public function editinvoice(){
    $invoice = M('ShopInvoice');
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

  public function shopcomment(){
    $comment = M('ShopComment');
    $where = array();
    if(!empty($_POST['content'])){
      $where['nc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
    }
    if(!empty($_POST['author'])){
      $member = M('Member');
      $authorid = $member -> getFieldByname($this -> _post('author'), 'id');
      $where['nc.mid'] = intval($authorid);
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['nc.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['nc.addtime'][] = array('lt', $endtime);
    }

    $count = $comment -> table('yesow_shop_comment as nc') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $comment -> table('yesow_shop_comment as nc') -> field('nc.id,s.id as sid,m.name,nc.floor,nc.content,s.title,nc.addtime,nc.status,nc.face') -> where($where) -> order('status ASC,nc.addtime DESC') -> join('yesow_shop as s ON nc.sid = s.id') -> join('yesow_member as m ON nc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editshopcomment(){
    $comment = D('index://ShopComment');
    if(!empty($_POST['floor'])){
      if(!$comment -> create()){
	$this -> error($comment -> getError());
      }
      if($comment -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $comment -> table('yesow_shop_comment as cc') -> field('c.title as cname,m.name as mname,cc.floor,cc.content,cc.face') -> join('yesow_shop as c ON cc.sid = c.id') -> join('yesow_member as m ON cc.mid = m.id') -> where(array('cc.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  public function delshopcomment(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $shopcomment = M('ShopComment');
    if($shopcomment -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function passauditshopcomment(){
    $comment = M('ShopComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 2);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditshopcomment(){
    $comment = M('ShopComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 1);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function shoporder(){
    $order = M('ShopOrder');
    $where = array();
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['so.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['so.addtime'][] = array('lt', $endtime);
    }
    $count = $order -> table('yesow_shop_order as so') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $order -> table('yesow_shop_order as so') -> field('so.id,so.ordernum,so.paytotal,m.name as mname,st.name as stname,so.isbull,so.addtime,so.ischeck,so.issend,so.paystatus,so.paytype') -> join('yesow_send_type as st ON so.sendid = st.id') -> join('yesow_member as m ON so.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('so.addtime DESC') -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

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

  public function editsendshop(){
    $order = M('ShopOrder');
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

  public function editshoplist(){
    $order_shop = D('index://ShopOrderShop');
    $order = M('ShopOrder');
    $ordernum = $order -> getFieldByid($this -> _get('id', 'intval'), 'ordernum');
    $stname = $order -> table('yesow_shop_order as so') -> field('st.name as stname') -> join('yesow_send_type as st ON so.sendid = st.id') -> where(array('so.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('stname', $stname['stname']);
    $result = $order_shop -> shopbyordernum($ordernum);
    $this -> assign('result', $result);
    $this -> assign('ordernum', $ordernum);
    $order_status = $order -> field('ischeck,issend,paystatus') -> where(array('ordernum' => $ordernum)) -> find();
    $this -> assign('order_status', $order_status);
    $shop_price = $order_shop -> totalpaybyordernum($ordernum);
    $this -> assign('shop_price', $shop_price);
    $temp_send_price = $order -> table('yesow_shop_order as so') -> field('st.money') -> join('yesow_send_type as st ON so.sendid = st.id') -> where(array('so.ordernum' => $ordernum)) -> find();
    $this -> assign('send_price', $temp_send_price['money']);
    $total_price = $order -> getFieldByordernum($ordernum, 'paytotal');
    $this -> assign('total_price', $total_price);
    $invoice_price = $total_price - $shop_price - $temp_send_price['money'];
    $this -> assign('invoice_price', $invoice_price);
    $goods_info = $order -> field('username,address,zipcode,tel,email,remark') -> where(array('ordernum' => $ordernum)) -> find();
    $this -> assign('goods_info', $goods_info);
    $this -> display();
  }


  /* --------------------- 动感传媒管理 --------------------- */

  //动感传媒管理
  public function mediashow(){
    $mediashow = M('MediaShow');
    //处理搜索
    $where = array();
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['ms.starttime'] = array(array('egt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['ms.endtime'] = array('elt', $endtime);
    }
    if(!empty($_POST['name'])){
      $where['ms.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_POST['csid'])){
      $where['ms.csid'] = $this -> _post('csid', 'intval');
    }
    //记录总数
    $count = $mediashow -> table('yesow_media_show as ms')  -> where($where) -> count('id');
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

    $result = $mediashow -> table('yesow_media_show as ms') -> field('ms.id,cs.name as csname,ms.name,ms.linkman,ms.companyphone,ms.starttime,ms.endtime,cc.name as ccname,ms.sort,ms.ischeck,m.name as mname,ms.type,ms.maketype,ms.image') -> join('yesow_member as m ON ms.mid = m.id') -> join('yesow_company_category as cc ON ms.ccid_one = cc.id') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('ms.updatetime DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  //添加动感传媒
  public function addmediashow(){
    if(!empty($_POST['name'])){
      $mediashow = D('MediaShow');
      if(!$mediashow -> create()){
	$this -> error($mediashow -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	$up_data = R('Public/media_pic_upload');
	$mediashow -> image = $up_data[0]['savename'];
      }
      if(!empty($_POST['org3_id'])){
	$mediashow -> mid = $_POST['org3_id'];
      }
      if($mediashow -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	echo $mediashow -> getLastSql();
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  //删除动感传媒
  public function delmediashow(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $mediashow = D('MediaShow');
    if($mediashow -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑动感传媒
  public function editmediashow(){
    $mediashow = D('MediaShow');
    if(!empty($_POST['name'])){
      if(!$mediashow -> create()){
	$this -> error($mediashow -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	$up_data = R('Public/media_pic_upload');
	$mediashow -> image = $up_data[0]['savename'];
      }
      if($mediashow -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
	$this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $mediashow -> field('csid,ccid_one,ccid_two,name,address,linkman,mobliephone,companyphone,qqcode,keyword,image,imagealt,remark,starttime,endtime,content,sort,maketype,image') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询当前一级类别的二级列表
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result['ccid_one'])) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    $this -> display();
  }

  //查看动感传媒图片
  public function editshowimage(){
    $image = M('MediaShow') -> getFieldByid($this -> _get('id', 'intval'), 'image');
    $this -> assign('image', $image);
    $this -> display();
  }

  //通过审核动感传媒
  public function passauditmediashow(){
    $mediashow = M('MediaShow');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($mediashow -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //不通过审核动感传媒
  public function nopassauditmediashow(){
    $mediashow = M('MediaShow');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($mediashow -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //动感传媒评论管理
  public function mediashowcomment(){
    $mediashowcomment = M('MediaShowComment');
    $where = array();
    //处理搜索
    if(!empty($_POST['content'])){
      $where['msc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
    }
    if(!empty($_POST['author'])){
      $member = M('Member');
      $authorid = $member -> getFieldByname($this -> _post('author'), 'id');
      $where['msc.mid'] = intval($authorid);
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['msc.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['msc.addtime'][] = array('lt', $endtime);
    }

    //记录总数
    $count = $mediashowcomment -> table('yesow_media_show_comment as msc') -> where($where) -> count();
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
    $result = $mediashowcomment -> table('yesow_media_show_comment as msc') -> field('msc.id,msc.msid,ms.name as msname,msc.floor,msc.content,m.name as mname,msc.addtime,msc.status,msc.face') -> where($where) -> order('msc.status ASC,msc.addtime DESC') -> join('yesow_media_show as ms ON msc.msid = ms.id') -> join('yesow_member as m ON msc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //编辑动感传媒评论
  public function editmediashowcomment(){
    $comment = D('index://MediaShowComment');
    //处理更新
    if(!empty($_POST['floor'])){
      if(!$comment -> create()){
	$this -> error($comment -> getError());
      }
      if($comment -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $comment -> table('yesow_media_show_comment as msc') -> field('ms.name as msname,m.name as mname,msc.floor,msc.content,msc.face') -> join('yesow_media_show as ms ON msc.msid = ms.id') -> join('yesow_member as m ON msc.mid = m.id') -> where(array('msc.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  //删除动感传媒评论
  public function delmediashowcomment(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $mediashowcomment = M('MediaShowComment');
    if($mediashowcomment -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //通过审核动感传媒评论
  public function passauditmediashowcomment(){
    $comment = M('MediaShowComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 2);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //不通过审核动感传媒评论
  public function nopassauditmediashowcomment(){
    $comment = M('MediaShowComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 1);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }
  /* --------------------- 动感传媒管理 --------------------- */
}

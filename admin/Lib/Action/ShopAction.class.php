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
    if(!empty($_POST['mname'])){
      $where['m.name'] = array('LIKE', '%' . $this -> _post('mname') . '%');
    }
    $count = $order -> table('yesow_shop_order as so') -> join('yesow_member as m ON so.mid = m.id') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $order -> table('yesow_shop_order as so') -> field('so.id,so.ordernum,so.paytotal,m.name as mname,st.name as stname,so.isbull,so.addtime,so.ischeck,so.issend,so.paystatus,so.paytype,so.mid') -> join('yesow_send_type as st ON so.sendid = st.id') -> join('yesow_member as m ON so.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('so.addtime DESC') -> where($where) -> select();
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
	//sendEmail
	if($_POST['issend'] == 1){
	  $send_email = $order -> alias('o') -> field('m.email') -> join('yesow_member as m ON o.mid = m.id') -> where(array('o.id' => $_POST['id'])) -> find();
	  D('MassEmailSetting') -> sendEmail('shop_send', $send_email['email'], $_POST['id']);
	}
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
    $total_price = $order -> getFieldByordernum($ordernum, 'paytotal');
    $this -> assign('total_price', $total_price);
    $goods_info = $order -> field('username,address,zipcode,tel,email,remark,send_price,invoice_price') -> where(array('ordernum' => $ordernum)) -> find();
    $this -> assign('goods_info', $goods_info);
    $this -> display();
  }

  
  public function editshopordermember(){
    $this -> assign('result', M('Member') -> alias('m') -> field('m.id,m.name,m.nickname,m.tel,cs.name as csname,csa.name as csaname,edu.name as eduname,career.name as careername,m.email,m.sex,m.address,m.unit,m.homepage,m.fullname') -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> join('yesow_member_edu as edu ON m.eduid = edu.id') -> join('yesow_member_career as career ON m.careerid = career.id') -> where(array('m.id' => $this -> _get('mid', 'intval'))) -> find());
    $this -> display();
  }

  public function addreviewformshop(){

    if(!empty($_POST['org5_name'])){
      $MemberReview = M('MemberReview');
      $data_add = array();
      $data_add['aid'] = session(C('USER_AUTH_KEY'));
      $data_add['mid'] = $this -> _post('mid', 'intval');
      $data_add['name'] = $this -> _post('org5_name');
      $data_add['address'] = $this -> _post('org5_address');
      $data_add['manproducts'] = $this -> _post('org5_manproducts');
      $data_add['companyphone'] = $this -> _post('org5_companyphone');
      $data_add['mobilephone'] = $this -> _post('org5_mobilephone');
      $data_add['linkman'] = $this -> _post('org5_linkman');
      $data_add['email'] = $this -> _post('org5_email');
      $data_add['qqcode'] = $this -> _post('org5_qqcode');
      $data_add['csname'] = $this -> _post('org5_csname');
      $data_add['csaname'] = $this -> _post('org5_csaname');
      $data_add['ccname_one'] = $this -> _post('org5_cc1name');
      $data_add['ccname_two'] = $this -> _post('org5_cc2name');
      $data_add['website'] = $this -> _post('org5_website');
      $data_add['new_linkman'] = $this -> _post('new_linkman');
      $data_add['new_companyphone'] = $this -> _post('org5_new_companyphone');
      $data_add['new_mobilephone'] = $this -> _post('new_mobilephone');
      $data_add['new_qqonline'] = $this -> _post('new_qqocde');
      $data_add['new_email'] = $this -> _post('new_email');
      $data_add['unit'] = $this -> _post('unit');
      $effect = '';
      foreach($_POST['effect'] as $value){
	if(!$effect){
	  $effect .= $value;
	}else{
	  $effect .= ',' . $value;
	}
      }
      $data_add['effect'] = $effect;
      $data_add['wanttobuy'] = $this -> _post('wanttobuy');
      $data_add['feedback'] = $this -> _post('feedback');
      $data_add['remark'] = $this -> _post('remark');
      $data_add['addtime'] = time();

      if(!$MemberReview -> create($data_add)){
	$this -> error($MemberReview -> getError());
      }
      if($rid = $MemberReview -> add()){
	if(!empty($_POST['sendsms'])){
	  D('MemberReviewSendSmsRecord') -> sendsms($rid);
	}
	if(!empty($_POST['sendemail'])){
	  $to_email = $MemberReview -> getFieldByid($rid, 'new_email');
	  D('MassEmailSetting') -> sendEmail('review_phone', $to_email, $rid);
	}
	//add record
	$MemberReviewRecord = M('MemberReviewRecord');
	$record_data = array();
	$record_data['rid'] = $rid;
	$record_data['nexttime'] = $this -> _post('nexttime', 'strtotime');
	$record_data['addtime'] = time();
	$record_data['nodeal'] = $this -> _post('nodeal');
	$record_data['status'] = $this -> _post('status');
	$MemberReviewRecord -> add($record_data);

	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }

    $member = M('Member');
    $result = $member -> field('id,name,unit,fullname,tel,qqcode,email') -> find($this -> _get('mid', 'intval'));
    $this -> assign('result', $result);
    $effect_arr = array(
      1 => '比较敷衍',
      2 => '希望了解',
      3 => '比较抗拒 ',
      4 => '抗拒到理解',
      5 => '需要考虑',
      6 => '需要商量',
    );
    $this -> assign('effect_arr', $effect_arr);
    $this -> display();
  }

  public function shoporderremark(){
    $ShopOrderRemark = M('ShopOrderRemark');
    $where = array();
    if(!empty($_POST['ordernum'])){
      $where['o.ordernum'] = $this -> _post('ordernum');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['sor.updatetime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['sor.updatetime'][] = array('lt', $endtime);
    }
    $count = $ShopOrderRemark -> alias('sor') -> join('yesow_shop_order as o ON sor.oid = o.id') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $ShopOrderRemark -> alias('sor') -> field('sor.id,o.ordernum,m.name as mname,sor.updatetime,sor.remark') -> join('yesow_shop_order as o ON sor.oid = o.id') -> join('yesow_member as m ON o.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sor.updatetime DESC') -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delshoporderremark(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $ShopOrderRemark = M('ShopOrderRemark');
    if($ShopOrderRemark -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editshoporderremark(){
    $ShopOrderRemark = M('ShopOrderRemark');
    $result = $ShopOrderRemark -> alias('sor') -> field('sor.remark,sor.updatetime,o.ordernum') -> join('yesow_shop_order as o ON sor.oid = o.id') -> where(array('sor.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }


  public function mediashow(){
    $mediashow = M('MediaShow');
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
    $count = $mediashow -> table('yesow_media_show as ms')  -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $mediashow -> table('yesow_media_show as ms') -> field('ms.id,cs.name as csname,ms.name,ms.linkman,ms.companyphone,ms.starttime,ms.endtime,cc.name as ccname,ms.sort,ms.ischeck,m.name as mname,ms.type,ms.maketype,ms.image') -> join('yesow_member as m ON ms.mid = m.id') -> join('yesow_company_category as cc ON ms.ccid_one = cc.id') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('ms.updatetime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

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
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

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
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result['ccid_one'])) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    $this -> display();
  }

  public function editshowimage(){
    $image = M('MediaShow') -> getFieldByid($this -> _get('id', 'intval'), 'image');
    $this -> assign('image', $image);
    $this -> display();
  }

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

  public function mediashowcomment(){
    $mediashowcomment = M('MediaShowComment');
    $where = array();
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

    $count = $mediashowcomment -> table('yesow_media_show_comment as msc') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $mediashowcomment -> table('yesow_media_show_comment as msc') -> field('msc.id,msc.msid,ms.name as msname,msc.floor,msc.content,m.name as mname,msc.addtime,msc.status,msc.face') -> where($where) -> order('msc.status ASC,msc.addtime DESC') -> join('yesow_media_show as ms ON msc.msid = ms.id') -> join('yesow_member as m ON msc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editmediashowcomment(){
    $comment = D('index://MediaShowComment');
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

  public function questioncategory(){
    $QuestionCategory = M('QuestionCategory');
    $where['pid'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $QuestionCategory -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $QuestionCategory -> field('id,name,sort,remark') -> where($where) -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addquestioncategory(){
    $QuestionCategory = M('QuestionCategory');
    if(!empty($_POST['name'])){
      if(!$QuestionCategory -> create()){
	$this -> error($QuestionCategory -> getError());
      }
      if($QuestionCategory -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $pname = $QuestionCategory -> getFieldByid($this -> _get('id', 'intval'), 'name');
    $this -> assign('pname', $pname);
    $this -> display();
  }

  public function delquestioncategory(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $QuestionCategory = M('QuestionCategory');
    if($QuestionCategory -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editquestioncategory(){
    $type = M('QuestionCategory');
    if(!empty($_POST['name'])){
      if(!$type -> create()){
	$this -> error($type -> getError());
      }
      if($type -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $type -> field('name,pid,sort,remark') -> find($this -> _get('id', 'intval'));
    $pname = $type -> getFieldByid($result['pid'], 'name');
    $this -> assign('pname', $pname);
    $this -> assign('result', $result);
    $this -> display();
  }

  public function question(){
    $Question = M('Question');
    if(!empty($_POST['title'])){
      $where['q.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['q.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['q.addtime'][] = array('lt', $endtime);
    }
    $count = $Question -> alias('q') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $Question -> alias('q') -> field('q.id,q.title,c.name as one_name,c2.name as two_name,m.name as mname,q.addtime,q.ischeck') -> where($where) -> join('yesow_question_category as c ON q.tid_one = c.id') -> join('yesow_question_category as c2 ON q.tid_two = c2.id') -> join('yesow_member as m ON q.mid = m.id') -> order('q.addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delquestion(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $Question = M('Question');
    if($Question -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editquestion(){
    $Question = M('Question');
    $QuestionCategory = M('QuestionCategory');
    if(!empty($_POST['title'])){
      if(!$Question -> create()){
	$this -> error($Question -> getError());
      }
      if($Question -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $Question -> field('id,title,tid_one,tid_two,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);

    $category_one = $QuestionCategory -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('category_one', $category_one);
    $category_two = $QuestionCategory -> field('id,name') -> where(array('pid' => $result['tid_one'])) -> order('sort ASC') -> select();
    $this -> assign('category_two', $category_two);


    $this -> display();
  }

  public function passauditquestion(){
    $Question = M('Question');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($Question -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditquestion(){
    $Question = M('Question');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($Question -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function questioncomments(){
    $QuestionComments = M('QuestionComments');
    $where = array();
    if(!empty($_POST['content'])){
      $where['nc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
    }
    if(!empty($_POST['author'])){
      $where['m.name'] = $this -> _post('author');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['nc.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['nc.addtime'][] = array('lt', $endtime);
    }

    $count = $QuestionComments -> alias('nc') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $QuestionComments -> alias('nc') -> field('nc.id,q.id as qid,m.name,nc.floor,nc.content,q.title,nc.addtime,nc.ischeck') -> where($where) -> order('nc.ischeck ASC,nc.addtime DESC') -> join('yesow_question as q ON nc.qid = q.id') -> join('yesow_member as m ON nc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delquestioncomments(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $QuestionComments = M('QuestionComments');
    if($QuestionComments -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editquestioncomments(){
    $comment = D('index://QuestionComments');
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
    $result = $comment -> alias('cc') -> field('q.title as cname,m.name as mname,cc.floor,cc.content') -> join('yesow_question as q ON cc.qid = q.id') -> join('yesow_member as m ON cc.mid = m.id') -> where(array('cc.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  public function passauditquestioncomments(){
    $comment = M('QuestionComments');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 2);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditquestioncomments(){
    $comment = M('QuestionComments');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }


}

<?php
class ServicesAction extends CommonAction {

  //首页前置操作
  public function _before_index(){
    //获取公告
    if(S('index_yesow_notice')){
      $this -> assign('index_yesow_notice', S('index_yesow_notice'));
    }else{
      $result = M('Notice') -> field('id,title,titleattribute,addtime') -> order('addtime DESC') -> limit(10) -> select();
      S('index_yesow_notice', $result);
      $this -> assign('index_yesow_notice', $result);
    }
  }

  //首页
  public function index(){
    $this -> display();
  
  }

  //在线QQ管理前置操作
  public function _before_qqonline(){
    $this -> _before_index();
  }


  /* ------  在线QQ ------- */

  //在线QQ管理
  public function qqonline(){
    $this -> display();
  }

  //添加在线QQ
  public function addqqonline(){
    //传递cid
    if(!empty($_GET['cid'])){
       $cid = $this -> _get('cid', 'intval');
       //公司信息
       $company_info = M('Company') -> table('yesow_company as c') -> field('c.name,c.address,c.linkman,cs.name as csname,csa.name as csaname') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> where(array('c.id' => $cid)) -> find();
       $this -> assign('company_info', $company_info);
       $CompanyQqonline = M('CompanyQqonline');
       //查询该公司已有QQ所属会员id与此次添加会员id是否相等，不相等则禁止添加
       $where_limit = array();
       $where_limit['cid'] = $cid;
       $where_limit['starttime'] = array('ELT', time());
       $where_limit['endtime'] = array('EGT', time());
       $add_limit = $CompanyQqonline -> field('mid') -> where($where_limit) -> find();
       if($add_limit & $add_limit['mid'] != session(C('USER_AUTH_KEY'))){
	 R('Register/errorjump',array(L('QQONLINE_LIMIT')));
       }
       //查询该公司已有QQ数量
       $where_qqonline = array();
       $where_qqonline['cid'] = $cid;
       $where_qqonline['starttime'] = array('ELT', time());
       $where_qqonline['endtime'] = array('EGT', time());
       $have_qq_num = $CompanyQqonline -> where($where_qqonline) -> count();
       $this -> assign('have_qq_num', $have_qq_num);
       $this -> assign('add_qq_num', 8 - $have_qq_num);
       //查询已有QQ
       $qqonline_list = $CompanyQqonline -> field('qqcode,qqname') -> where($where_qqonline) -> select();
       $this -> assign('qqonline_list', $qqonline_list);
    }
    //后台搜索公司
    if(!empty($_REQUEST['keyword'])){
      $where_company['name'] = array('LIKE', '%' . $_REQUEST['keyword'] . '%');
      import("ORG.Util.Page");// 导入分页类
      $count = M('Company') -> where($where_company) -> count();
      $page = new Page($count, 9);
      $page -> parameter = "keyword=" . $_POST['keyword'];
      $show = $page -> show();
      $company_search = M('Company') -> field('id,name,manproducts,address,website,linkman') -> where($where_company) -> order('updatetime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
      $this -> assign('show', $show);
      $this -> assign('company_search', $company_search);
    }
    //查询价格
    $QqonlineMoney = M('QqonlineMoney');
    $qq_price = $QqonlineMoney -> field('id,months,marketprice,promotionprice') -> order('months ASC') -> select();
    $this -> assign('qq_price', $qq_price);
    $this -> display();
  }

  //在线QQ订单页
  public function qqonline_pay(){
    $result_qqonline = array();
    if(empty($_GET['orderid'])){
       //月份价格
      $result_qqonline = M('QqonlineMoney') -> field('months,promotionprice') -> find($this -> _post('months'));
      //整理QQ和昵称对应数组
      $result_qqonline['qq_name'] = array();
      foreach($_POST['qqlist'] as $key => $value){
	if(!empty($value)){
	  $result_qqonline['qq_name'][$value] = $_POST['namelist'][$key];
	}
      }
    }else{
      //月份价格
      $qid = M('QqonlineOrder') -> getFieldByordernum($_GET['orderid'], 'qid');
      $result_qqonline = M('QqonlineMoney') -> field('months,promotionprice') -> find($qid);
      //整理QQ和昵称对应数组
      $oid = M('QqonlineOrder') -> getFieldByordernum($_GET['orderid'], 'id');
      $qq_list = M('QqonlineOrderList') -> field('qqcode,qqname') -> where(array('oid' => $oid)) -> select();
      $result_qqonline['qq_name'] = array();
      foreach($qq_list as $value){
	$result_qqonline['qq_name'][$value['qqcode']] = $value['qqname'];
      }
    }

    //判断QQ号是否超过8个
    $CompanyQqonline = M('CompanyQqonline');
    $where_qqonline_count = array();
    $where_qqonline_count['cid'] = $this -> _post('cid', 'intval');
    $where_qqonline_count['starttime'] = array('ELT', time());
    $where_qqonline_count['endtime'] = array('EGT', time());
    $qqonline_count = $CompanyQqonline -> where($where_qqonline_count) -> count();
    if($qqonline_count >= 8){
      R('Register/errorjump',array(L('QQONLINE_NUM_ERROR')));
    }

    //判断是否重复添加QQ号
    $where_qqonline = array();
    $where_qqonline['cid'] = $this -> _post('cid', 'intval');
    $where_qqonline['starttime'] = array('ELT', time());
    $where_qqonline['endtime'] = array('EGT', time());
    $result_company_qqonline = $CompanyQqonline -> field('qqcode') -> where($where_qqonline) -> order('starttime ASC') -> limit(8) -> select();
    foreach($result_company_qqonline as $value){
      foreach($result_qqonline['qq_name'] as $keys => $values){
	if(in_array($keys, $value)){
	  R('Register/errorjump',array(L('QQONLINE_REPETA_ERROR')));
	}
      }
    }


    //QQ数量
    $result_qq_num = count($result_qqonline['qq_name']);

    //生成订单号
    $result_qqonline['orderid'] = !empty($_GET['orderid']) ? $_GET['orderid'] : date('YmdHis') . mt_rand(100000,999999);
    //总价
    $result_qqonline['count'] = $result_qqonline['promotionprice'] * $result_qq_num;
    
    if(empty($_GET['orderid'])){
      //生成订单
      $QqonlineOrder = M('QqonlineOrder');
      $data = array();
      $data['ordernum'] = $result_qqonline['orderid'];
      $data['mid'] = session(C('USER_AUTH_KEY'));
      $data['cid'] = $this -> _post('cid', 'intval');
      $data['qid'] = $this -> _post('months', 'intval');
      $data['price'] = $result_qqonline['count'];
      $data['addtime'] = time();
      if(!$oid = $QqonlineOrder -> add($data)){
	R('Register/errorjump',array(L('ORDER_ERROR')));
      }
      //生成订单详情
      $QqonlineOrderList = M('QqonlineOrderList');
      foreach($result_qqonline['qq_name'] as $key => $value){
	$data_list = array();
	$data_list['oid'] = $oid;
	$data_list['qqcode'] = $key;
	$data_list['qqname'] = $value;
	$QqonlineOrderList -> add($data_list);
      }
    }

    //RMB余额是否足够支付
    $result_monthly['rmb_enough'] = $_SESSION['rmb_total'] - $result_qqonline['count'] >= 0 ? 1 : 0;
    $this -> assign('result_monthly', $result_monthly);

    $this -> assign('result_qqonline', $result_qqonline);

    //查询接口信息
    $payport = M('Payport');
    $result_pay = $payport -> field('name,enname') -> where(array('status' => 1)) -> select();
    $this -> assign('result_pay', $result_pay);
    $this -> display();

  }

  //余额支付页
  public function qqonline_rmb_pay(){
    $QqonlineOrder = M('QqonlineOrder');
    //获取交易密码
    $pay_pwd = M('Member') -> getFieldByid($_SESSION[C('USER_AUTH_KEY')], 'traderspassword');
    //未设置交易密码的先去设置交易密码
    if(!$pay_pwd){
      R('Register/errorjump',array(L('TRADERSPASSWORD_EMPTY_ERROR'), '__ROOT__/member.php/index/setsafepwd'));
    }
    //交易密码错误
    if($pay_pwd != $_GET['pwd']){
      R('Register/errorjump',array(L('TRADERSPASSWORD_ERROR'), '__ROOT__/member.php/services/qqonline_pay/orderid/' . $_GET['orderid']));
    }
    //根据订单号查询应付总额
    $const = $QqonlineOrder -> getFieldByordernum($_GET['orderid'], 'price');
    
    //扣费
    $rmb = D('MemberRmb');
    if(!$rmb -> lessrmb($const)){
      R('Register/errorjump',array(L('RMB_ERROR')));
    }

    //扣费成功更新订单状态
    if(!$QqonlineOrder -> where(array('ordernum' => $this -> _get('orderid'))) -> save(array('status' => 3, 'paytype' => 'RMB余额'))){
      R('Register/errorjump',array(L('ORDER_UPDATE_ERROR')));
    }

    //写RMB消费记录
    $log_content = "您已成功购买 在线QQ 服务,订单号{$_GET['orderid']}";
    if(!D('member://MemberRmbDetail') -> writelog($_SESSION[C('USER_AUTH_KEY')], $log_content, '消费', '-' . $const)){
      R('Register/errorjump',array(L('RMB_LOG_ERROR')));
    }

    //写在线QQ主表
      //订单相关信息
    $qqonline_info = $QqonlineOrder -> table('yesow_qqonline_order as qo') -> field('qo.id,qo.cid,qm.months') -> join('yesow_qqonline_money as qm ON qo.qid = qm.id') -> where(array('qo.ordernum' => $_GET['orderid'])) -> find();
      //订单所属QQ信息
    $order_qq_info = M('QqonlineOrderList') -> field('qqcode,qqname') -> where(array('oid' => $qqonline_info['id'])) -> select();
      //写主表
    $CompanyQqonline = M('CompanyQqonline');
    $qq_data = array();
    $qq_data['mid'] = session(C('USER_AUTH_KEY'));
    $qq_data['cid'] = $qqonline_info['cid'];
    $qq_data['starttime'] = time();
    $qq_data['endtime'] = $qq_data['starttime'] + ($qqonline_info['months'] * 30 * 24 * 60 * 60);
    $num = 0;
    foreach($order_qq_info as $value){
      $qq_data['qqcode'] = $value['qqcode'];
      $qq_data['qqname'] = $value['qqname'];
      $res = $CompanyQqonline -> add($qq_data);
      if($res){
	$num++;
      }
    }
    if($num > 0){
      $info_succ = "您已成功购买在线QQ相关服务";
      //更新会员余额和等级
      if(!$rmb -> rmbtotal()){
	R('Register/errorjump',array(L('RMB_CACHE')));
      }
      R('Services/qqonlinesuccess',array($info_succ, 'success', $qqonline_info['cid']));
    }else{
      R('Register/errorjump',array(L('QQONLINE_ERROR')));
    }
  }

  //在线QQ - 快钱支付
  public function qqonline_k99bill_pay(){
    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1') -> where(array('enname' => 'k99bill')) -> find();
    //人民币网关账户号
    $merchantAcctId = $author['account'];
    //人民币网关密钥
    $key = $author['key1'];
    //字符集.固定选择值。可为空。
    /////1代表UTF-8; 2代表GBK; 3代表gb2312
    $inputCharset = "1";
    //同步返回地址
    $pageUrl = C('WEBSITE') . "member.php/pay/qqonline_k99billreturn";
    //网关版本.固定值
    $version = "v2.0";
    //语言种类.固定选择值。
    $language = "1";
    //签名类型.固定值
    $signType = "1";
    //商户订单号
    $orderId = $this -> _get('oid');
    //订单金额
    $rmb_amount = M('QqonlineOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    $orderAmount = $rmb_amount * 100;
    //订单提交时间
    $orderTime = date('YmdHis');
    //商品名称
    $productName = "易搜会员中心在线QQ购买";
    //支付方式.固定选择值
    $payType = "00";
    //同一订单禁止重复提交标志
    $redoFlag = "1";

    //生成加密签名串
    $signMsgVal=appendParam($signMsgVal,"inputCharset",$inputCharset);
	$signMsgVal=appendParam($signMsgVal,"pageUrl",$pageUrl);
	$signMsgVal=appendParam($signMsgVal,"bgUrl",$bgUrl);
	$signMsgVal=appendParam($signMsgVal,"version",$version);
	$signMsgVal=appendParam($signMsgVal,"language",$language);
	$signMsgVal=appendParam($signMsgVal,"signType",$signType);
	$signMsgVal=appendParam($signMsgVal,"merchantAcctId",$merchantAcctId);
	$signMsgVal=appendParam($signMsgVal,"payerName",$payerName);
	$signMsgVal=appendParam($signMsgVal,"payerContactType",$payerContactType);
	$signMsgVal=appendParam($signMsgVal,"payerContact",$payerContact);
	$signMsgVal=appendParam($signMsgVal,"orderId",$orderId);
	$signMsgVal=appendParam($signMsgVal,"orderAmount",$orderAmount);
	$signMsgVal=appendParam($signMsgVal,"orderTime",$orderTime);
	$signMsgVal=appendParam($signMsgVal,"productName",$productName);
	$signMsgVal=appendParam($signMsgVal,"productNum",$productNum);
	$signMsgVal=appendParam($signMsgVal,"productId",$productId);
	$signMsgVal=appendParam($signMsgVal,"productDesc",$productDesc);
	$signMsgVal=appendParam($signMsgVal,"ext1",$ext1);
	$signMsgVal=appendParam($signMsgVal,"ext2",$ext2);
	$signMsgVal=appendParam($signMsgVal,"payType",$payType);	
	$signMsgVal=appendParam($signMsgVal,"bankId",$bankId);
	$signMsgVal=appendParam($signMsgVal,"redoFlag",$redoFlag);
	$signMsgVal=appendParam($signMsgVal,"pid",$pid);
	$signMsgVal=appendParam($signMsgVal,"key",$key);
	$signMsg= strtoupper(md5($signMsgVal));

    $sHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>支付跳转页面-易搜</title>
<style>
  .zhifu_tz{ width:320px; height:150px; border:#e78c55 3px solid; margin:0 auto 50px; position:absolute; top:50%; left:50%; margin-top:-75px; margin-left:-160px;
    /*圆角*/
    -webkit-border-radius: 5px; 
    -moz-border-radius: 5px;
    border-radius: 5px; 
    behavior: url(style/PIE.htc);/*IE6、7、8下支持一些 CSS3 html5 属性*/}
  .zhifu_tz p{ padding:40px 0 0 60px; font-size:14px; line-height:30px; font-weight:bold;}
  .zhifu_tz p img{ vertical-align:middle; margin:0 10px 5px 0;}
  .zhifu_tz p.jishi{padding:20px 0 0 100px; font-size:12px; line-height:30px; font-weight:normal;}
</style>
</head>
<body id="body_user">
      <div class="zhifu_tz">
        <p><img src="' . __ROOT__ .'/Public/member/images/user/loading.gif" width="25" height="25" border="0" />正在跳转至支付页面...</p>
	</div>';
    $sHtml .= '<form name="frm" id="k99billsubmit" method="post" action="https://www.99bill.com/gateway/recvMerchantInfoAction.htm">';
    $sHtml .= '<input type="hidden" name="inputCharset" value="' . $inputCharset . '"/>
			<input type="hidden" name="pageUrl" value="' . $pageUrl . '"/>
			<input type="hidden" name="version" value="' . $version . '"/>
			<input type="hidden" name="language" value="' . $language . '"/>
			<input type="hidden" name="signType" value="' . $signType . '"/>
			<input type="hidden" name="signMsg" value="' . $signMsg . '"/>
			<input type="hidden" name="merchantAcctId" value="' . $merchantAcctId . '"/>
			<input type="hidden" name="payerName" value="' . $payerName . '"/>
			<input type="hidden" name="payerContactType" value="' . $payerContactType . '"/>
			<input type="hidden" name="payerContact" value="' . $payerContact . '"/>
			<input type="hidden" name="orderId" value="' . $orderId . '"/>
			<input type="hidden" name="orderAmount" value="' . $orderAmount . '"/>
			<input type="hidden" name="orderTime" value="' . $orderTime . '"/>
			<input type="hidden" name="productName" value="' . $productName . '"/>
			<input type="hidden" name="productNum" value="' . $productNum . '"/>
			<input type="hidden" name="productId" value="' . $productId . '"/>
			<input type="hidden" name="productDesc" value="' . $productDesc . '"/>
			<input type="hidden" name="ext1" value="' . $ext1 . '"/>
			<input type="hidden" name="ext2" value="' . $ext2 . '"/>
			<input type="hidden" name="payType" value="' . $payType . '"/>
			<input type="hidden" name="bankId" value="' . $bankId . '"/>
			<input type="hidden" name="redoFlag" value="' . $redoFlag . '"/>
			<input type="hidden" name="pid" value="' . $pid . '"/>';
    $sHtml .= '</form>';
    $sHtml .= "<script>document.forms['k99billsubmit'].submit();</script>";
    $sHtml .= "</body></html>";

    echo $sHtml;
  }

  //在线QQ - 支付宝支付
  public function qqonline_alipay_pay(){
    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1,key2') -> where(array('enname' => 'alipay')) -> find();
    Vendor('alipay.alipay_submit','','.class.php');
    $alipay_config = array();
    //合作身份者id，以2088开头的16位纯数字
    $alipay_config['partner'] = $author['key1'];
    //安全检验码，以数字和字母组成的32位字符
    $alipay_config['key'] = $author['key2'];
    //签名方式 不需修改
    $alipay_config['sign_type'] = strtoupper('MD5');
    //字符编码格式 目前支持 gbk 或 utf-8
    $alipay_config['input_charset'] = strtolower('utf-8');
    //ca证书路径地址，用于curl中ssl校验
    ////请保证cacert.pem文件在当前文件夹目录中
    $alipay_config['cacert'] = __ROOT__ . 'Public/cacert.pem';
    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    $alipay_config['transport'] = 'http';

    /**************************请求参数**************************/
    //支付类型
    $payment_type = "1";
    //服务器异步通知页面路径,需http://格式的完整路径，不能加?id=123这类自定义参数
    $notify_url = C('WEBSITE') . "member.php/pay/qqonline_alipaynotify";
    //页面跳转同步通知页面路径,需http://格式的完整路径，不能加?id=123这类自定义参
    $return_url = C('WEBSITE') . "member.php/pay/qqonline_alipayreturn";
    //卖家支付宝帐户
    $seller_email = $author['account'];
    //商户订单号
    $out_trade_no = $this -> _get('oid');
    //订单名称
    $subject = '易搜会员中心在线QQ购买';
    //付款金额，根据订单号查询出来
    $price = M('QqonlineOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    //商品数量,必填，建议默认为1
    $quantity = "1";
    //物流费用,必填，即运费
    $logistics_fee = "0.00";
    //物流类型,必填，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）
    $logistics_type = "EXPRESS";
    //物流支付方式,必填，两个值可选：SELLER_PAY（卖家承担运费）、BUYER_PAY（买家承担运费）
    $logistics_payment = "SELLER_PAY";
    /************************************************************/

    //构造要请求的参数数组，无需改动
    $parameter = array(
      "service" => "create_partner_trade_by_buyer",
      "partner" => trim($alipay_config['partner']),
      "payment_type"	=> $payment_type,
      "notify_url"	=> $notify_url,
      "return_url"	=> $return_url,
      "seller_email"	=> $seller_email,
      "out_trade_no"	=> $out_trade_no,
      "subject"	=> $subject,
      "price"	=> $price,
      "quantity"	=> $quantity,
      "logistics_fee"	=> $logistics_fee,
      "logistics_type"	=> $logistics_type,
      "logistics_payment"	=> $logistics_payment,
      "body"	=> $body,
      "show_url"	=> $show_url,
      "receive_name"	=> $receive_name,
      "receive_address"	=> $receive_address,
      "receive_zip"	=> $receive_zip,
      "receive_phone"	=> $receive_phone,
      "receive_mobile"	=> $receive_mobile,
      "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
    );
    //建立请求
    $alipaySubmit = new AlipaySubmit($alipay_config);
    $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
    echo $html_text;
  
  }

  //在线QQ - 财付通支付
  public function qqonline_tenpay_pay(){
    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1') -> where(array('enname' => 'tenpay')) -> find();
    Vendor('tenpay.RequestHandler','','.class.php');

    $partner = $author['account'];  //财付通商户号
    $key = $author['key1'];  //财付通密钥
    $return_url = C('WEBSITE') . "member.php/pay/qqonline_tenpayreturn";	//同步返回地址
    $notify_url = C('WEBSITE') . "member.php/pay/qqonline_tenpaynotify";  //异步通知地址
    $out_trade_no = $this -> _get('oid'); //订单号
    $desc = '易搜会员中心在线QQ购买';  //商品名称
    $order_price = M('QqonlineOrder') -> getFieldByordernum($this -> _get('oid'), 'price');  //商品价格,根据订单号查寻
    $trade_mode = 1;  //支付方式 1：及时到帐
    $total_fee = $order_price * 100;  //商品价格 以分为单位

    /* 创建支付请求对象 */
    $reqHandler = new RequestHandler();
    $reqHandler->init();
    $reqHandler->setKey($key);
    $reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");

    //----------------------------------------
    ////设置支付参数 
    ////----------------------------------------
    $reqHandler->setParameter("partner", $partner);//商户号
    $reqHandler->setParameter("out_trade_no", $out_trade_no); //订单号
    $reqHandler->setParameter("total_fee", $total_fee);  //总金额
    $reqHandler->setParameter("return_url", $return_url);
    $reqHandler->setParameter("notify_url", $notify_url);
    $reqHandler->setParameter("body", $desc);
    $reqHandler->setParameter("bank_type", "DEFAULT");  	  //银行类型，默认为财付通
    //用户ip
    $reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//客户端IP
    $reqHandler->setParameter("fee_type", "1");               //币种
    $reqHandler->setParameter("subject",$desc);          //商品名称，（中介交易时必填）

    //系统可选参数
    $reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
    $reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
    $reqHandler->setParameter("input_charset", "utf-8");   	  //字符集
    $reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号

    //业务可选参数
    $reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
    $reqHandler->setParameter("product_fee", "");        	  //商品费用
    $reqHandler->setParameter("transport_fee", "0");      	  //物流费用
    $reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
    $reqHandler->setParameter("time_expire", "");             //订单失效时间
    $reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
    $reqHandler->setParameter("goods_tag", "");               //商品标记
    $reqHandler->setParameter("trade_mode",$trade_mode);              //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
    $reqHandler->setParameter("transport_desc","");              //物流说明
    $reqHandler->setParameter("trans_type","1");              //交易类型
    $reqHandler->setParameter("agentid","");                  //平台ID
    $reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
    $reqHandler->setParameter("seller_id","");                //卖家的商户号

    //请求的URL
    $reqUrl = $reqHandler->getRequestURL();
    //提交URL
    //$actionUrl = $reqHandler->getGateUrl();

    $sHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>支付跳转页面-易搜</title>
<style>
  .zhifu_tz{ width:320px; height:150px; border:#e78c55 3px solid; margin:0 auto 50px; position:absolute; top:50%; left:50%; margin-top:-75px; margin-left:-160px;
    /*圆角*/
    -webkit-border-radius: 5px; 
    -moz-border-radius: 5px;
    border-radius: 5px; 
    behavior: url(style/PIE.htc);/*IE6、7、8下支持一些 CSS3 html5 属性*/}
  .zhifu_tz p{ padding:40px 0 0 60px; font-size:14px; line-height:30px; font-weight:bold;}
  .zhifu_tz p img{ vertical-align:middle; margin:0 10px 5px 0;}
  .zhifu_tz p.jishi{padding:20px 0 0 100px; font-size:12px; line-height:30px; font-weight:normal;}
</style>
</head>
<body id="body_user">
      <div class="zhifu_tz">
        <p><img src="' . __ROOT__ .'/Public/member/images/user/loading.gif" width="25" height="25" border="0" />正在跳转至支付页面...</p>
	</div>';
    $sHtml .= "<script>location.href='{$reqUrl}'</script>";
    $sHtml .= "</body></html>";

    echo $sHtml;
  }


  //在线QQ购买成功
  public function qqonlinesuccess($title, $status, $cid){
    $this -> assign('status', $status);
    $this -> assign('cid', $cid);
    $this -> assign('title', $title);
    $this -> display('services:qqonlinesuccess');
    exit();
  }

  //在线QQ订单
  public function qqonlineorder(){
    $QqonlineOrder = M('QqonlineOrder');
    $where = array();
    $where['qo.mid'] = $_SESSION[C('USER_AUTH_KEY')];
    import("ORG.Util.Page");// 导入分页类
    $count = $QqonlineOrder -> table('yesow_qqonline_order as qo') -> where($where) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $QqonlineOrder -> table('yesow_qqonline_order as qo') -> field('qo.id,qo.ordernum,tmp.count,qm.months,qo.price,qo.status,qo.ischeck,qo.paytype,qo.addtime,qo.isrenew') -> join('LEFT JOIN (SELECT oid,COUNT(id) as count FROM yesow_qqonline_order_list GROUP BY oid) as tmp ON tmp.oid = qo.id') -> join('yesow_qqonline_money as qm ON qo.qid = qm.id') -> order('qo.addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //在线QQ订单详情
  public function qqonlineorderlist(){
    $oid = $this -> _get('oid', 'intval');
    $QqonlineOrderList = M('QqonlineOrderList');
    $result_list = $QqonlineOrderList -> field('qqcode,qqname') -> where(array('oid' => $oid)) -> select();
    $this -> assign('result_list', $result_list);
    $QqonlineOrder = M('QqonlineOrder');
    $result_order = $QqonlineOrder -> field('ordernum,status,ischeck,price') -> find($oid);
    $this -> assign('result_order', $result_order);
    $this -> display();
  }

  //在线QQ管理
  public function editqqonline(){
    $CompanyQqonline = M('CompanyQqonline');
    import("ORG.Util.Page");// 导入分页类
    $count = $CompanyQqonline -> where(array('mid' => session(C('USER_AUTH_KEY')))) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $CompanyQqonline -> table('yesow_company_qqonline as cq') -> field('cq.id,c.id as cid,c.name as cname,cq.qqcode,cq.qqname,cq.starttime,cq.endtime') -> join('yesow_company as c ON cq.cid = c.id') -> where(array('cq.mid' => session(C('USER_AUTH_KEY')))) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('cq.starttime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //编辑在线QQ
  public function editeditqqonline(){
    $CompanyQqonline = M('CompanyQqonline');
    if(!empty($_POST['qqcode'])){
      if(!$CompanyQqonline -> create()){
	R('Register/errorjump',array($CompanyQqonline -> getError()));
      }
      if($CompanyQqonline -> save()){
	if(!empty($_POST['months'])){
	  R('Register/successjump',array(L('QQONLINE_RENEW'), U('Services/qqonline_renew_pay') . '/oid/' . $this -> _post('months', 'intval') . '/qid/' . $this -> _post('id', 'intval')));
	}else{
	  R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Services/editqqonline')));
	}
      }else{
	if(!empty($_POST['months'])){
	  R('Register/successjump',array(L('QQONLINE_RENEW'), U('Services/qqonline_renew_pay') . '/oid/' . $this -> _post('months', 'intval') . '/qid/' . $this -> _post('id', 'intval')));
	}else{
	  R('Register/errorjump',array(L('DATA_UPDATE_ERROR')));
	}
      }
    }
    $result = $CompanyQqonline -> table('yesow_company_qqonline as cq') -> field('c.name as cname,cs.name as csname,cq.starttime,cq.endtime,cq.qqcode,cq.qqname') -> join('yesow_company as c ON cq.cid = c.id') -> join('yesow_child_site as cs ON c.csid = cs.id') -> where(array('cq.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    //查询价格
    $QqonlineMoney = M('QqonlineMoney');
    $qq_price = $QqonlineMoney -> field('id,months,marketprice,promotionprice') -> order('months ASC') -> select();
    $this -> assign('qq_price', $qq_price);
    $this -> display();
  }

  //在线QQ续费订单页
  public function qqonline_renew_pay(){
    $result_qqonline = array();
    if(empty($_GET['orderid'])){
       //月份价格
      $result_qqonline = M('QqonlineMoney') -> field('months,promotionprice') -> find($this -> _get('oid', 'intval'));
      //整理QQ和昵称对应数组
      $CompanyQqonline = M('CompanyQqonline');
      $temp_result = $CompanyQqonline -> field('qqcode,qqname') -> select($this -> _get('qid', 'intval'));
      $result_qqonline['qq_name'] = array();
      foreach($temp_result as $key => $value){
	$result_qqonline['qq_name'][$value['qqcode']] = $value['qqname'];
      }
    }else{
      //月份价格
      $qid = M('QqonlineOrder') -> getFieldByordernum($_GET['orderid'], 'qid');
      $result_qqonline = M('QqonlineMoney') -> field('months,promotionprice') -> find($qid);
      //整理QQ和昵称对应数组
      $oid = M('QqonlineOrder') -> getFieldByordernum($_GET['orderid'], 'id');
      $qq_list = M('QqonlineOrderList') -> field('qqcode,qqname') -> where(array('oid' => $oid)) -> select();
      $result_qqonline['qq_name'] = array();
      foreach($qq_list as $value){
	$result_qqonline['qq_name'][$value['qqcode']] = $value['qqname'];
      }
    }
   
    //QQ数量
    $result_qq_num = count($result_qqonline['qq_name']);

    //生成订单号
    $result_qqonline['orderid'] = !empty($_GET['orderid']) ? $_GET['orderid'] : date('YmdHis') . mt_rand(100000,999999);
    //总价
    $result_qqonline['count'] = $result_qqonline['promotionprice'] * $result_qq_num;
    
    if(empty($_GET['orderid'])){
      //生成订单
      $QqonlineOrder = M('QqonlineOrder');
      $data = array();
      $data['ordernum'] = $result_qqonline['orderid'];
      $data['mid'] = session(C('USER_AUTH_KEY'));
      $data['cid'] = $CompanyQqonline -> getFieldByid($this -> _get('qid', 'intval'), 'cid');
      $data['qid'] = $this -> _get('oid', 'intval');
      $data['price'] = $result_qqonline['count'];
      $data['addtime'] = time();
      $data['isrenew'] = 1;
      if(!$oid = $QqonlineOrder -> add($data)){
	R('Register/errorjump',array(L('ORDER_ERROR')));
      }
      //生成订单详情
      $QqonlineOrderList = M('QqonlineOrderList');
      foreach($result_qqonline['qq_name'] as $key => $value){
	$data_list = array();
	$data_list['oid'] = $oid;
	$data_list['qqcode'] = $key;
	$data_list['qqname'] = $value;
	$QqonlineOrderList -> add($data_list);
      }
    }

    //RMB余额是否足够支付
    $result_monthly['rmb_enough'] = $_SESSION['rmb_total'] - $result_qqonline['count'] >= 0 ? 1 : 0;
    $this -> assign('result_monthly', $result_monthly);

    $this -> assign('result_qqonline', $result_qqonline);

    //查询接口信息
    $payport = M('Payport');
    $result_pay = $payport -> field('name,enname') -> where(array('status' => 1)) -> select();
    $this -> assign('result_pay', $result_pay);
    $this -> display();
  }

  //在线QQ续费余额支付
  public function qqonline_renew_rmb_pay(){
    $QqonlineOrder = M('QqonlineOrder');
    //获取交易密码
    $pay_pwd = M('Member') -> getFieldByid($_SESSION[C('USER_AUTH_KEY')], 'traderspassword');
    //未设置交易密码的先去设置交易密码
    if(!$pay_pwd){
      R('Register/errorjump',array(L('TRADERSPASSWORD_EMPTY_ERROR'), '__ROOT__/member.php/index/setsafepwd'));
    }
    //交易密码错误
    if($pay_pwd != $_GET['pwd']){
      R('Register/errorjump',array(L('TRADERSPASSWORD_ERROR'), '__ROOT__/member.php/services/qqonline_pay/orderid/' . $_GET['orderid']));
    }
    //根据订单号查询应付总额
    $const = $QqonlineOrder -> getFieldByordernum($_GET['orderid'], 'price');
    
    //扣费
    $rmb = D('MemberRmb');
    if(!$rmb -> lessrmb($const)){
      R('Register/errorjump',array(L('RMB_ERROR')));
    }

    //扣费成功更新订单状态
    if(!$QqonlineOrder -> where(array('ordernum' => $this -> _get('orderid'))) -> save(array('status' => 3, 'paytype' => 'RMB余额'))){
      R('Register/errorjump',array(L('ORDER_UPDATE_ERROR')));
    }

    //写RMB消费记录
    $log_content = "您已成功续费 在线QQ 服务,订单号{$_GET['orderid']}";
    if(!D('member://MemberRmbDetail') -> writelog($_SESSION[C('USER_AUTH_KEY')], $log_content, '消费', '-' . $const)){
      R('Register/errorjump',array(L('RMB_LOG_ERROR')));
    }

    //写在线QQ主表
      //订单相关信息
    $qqonline_info = $QqonlineOrder -> table('yesow_qqonline_order as qo') -> field('qo.id,qo.cid,qm.months') -> join('yesow_qqonline_money as qm ON qo.qid = qm.id') -> where(array('qo.ordernum' => $_GET['orderid'])) -> find();
      //订单所属QQ信息
    $order_qq_info = M('QqonlineOrderList') -> field('qqcode,qqname') -> where(array('oid' => $qqonline_info['id'])) -> find();
      //更新主表
    $CompanyQqonline = M('CompanyQqonline');
    $where_up = array();
    $where_up['cid'] = $qqonline_info['cid'];
    $where_up['qqcode'] = $order_qq_info['qqcode'];
    $where_up['qqname'] = $order_qq_info['qqname'];
    $num = $CompanyQqonline -> where($where_up) -> setInc('endtime', $qqonline_info['months'] * 30 * 24 * 60 * 60);
    if($num){
      $info_succ = "您已成功续费在线QQ相关服务";
      //更新会员余额和等级
      if(!$rmb -> rmbtotal()){
	R('Register/errorjump',array(L('RMB_CACHE')));
      }
      R('Services/qqonlinesuccess',array($info_succ, 'success', $qqonline_info['cid']));
    }else{
      R('Register/errorjump',array(L('QQONLINE_ERROR')));
    }
  }

  //在线QQ续费 - 快钱支付
  public function qqonline_renew_k99bill_pay(){
    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1') -> where(array('enname' => 'k99bill')) -> find();
    //人民币网关账户号
    $merchantAcctId = $author['account'];
    //人民币网关密钥
    $key = $author['key1'];
    //字符集.固定选择值。可为空。
    /////1代表UTF-8; 2代表GBK; 3代表gb2312
    $inputCharset = "1";
    //同步返回地址
    $pageUrl = C('WEBSITE') . "member.php/pay/qqonline_renew_k99billreturn";
    //网关版本.固定值
    $version = "v2.0";
    //语言种类.固定选择值。
    $language = "1";
    //签名类型.固定值
    $signType = "1";
    //商户订单号
    $orderId = $this -> _get('oid');
    //订单金额
    $rmb_amount = M('QqonlineOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    $orderAmount = $rmb_amount * 100;
    //订单提交时间
    $orderTime = date('YmdHis');
    //商品名称
    $productName = "易搜会员中心在线QQ续费";
    //支付方式.固定选择值
    $payType = "00";
    //同一订单禁止重复提交标志
    $redoFlag = "1";

    //生成加密签名串
    $signMsgVal=appendParam($signMsgVal,"inputCharset",$inputCharset);
	$signMsgVal=appendParam($signMsgVal,"pageUrl",$pageUrl);
	$signMsgVal=appendParam($signMsgVal,"bgUrl",$bgUrl);
	$signMsgVal=appendParam($signMsgVal,"version",$version);
	$signMsgVal=appendParam($signMsgVal,"language",$language);
	$signMsgVal=appendParam($signMsgVal,"signType",$signType);
	$signMsgVal=appendParam($signMsgVal,"merchantAcctId",$merchantAcctId);
	$signMsgVal=appendParam($signMsgVal,"payerName",$payerName);
	$signMsgVal=appendParam($signMsgVal,"payerContactType",$payerContactType);
	$signMsgVal=appendParam($signMsgVal,"payerContact",$payerContact);
	$signMsgVal=appendParam($signMsgVal,"orderId",$orderId);
	$signMsgVal=appendParam($signMsgVal,"orderAmount",$orderAmount);
	$signMsgVal=appendParam($signMsgVal,"orderTime",$orderTime);
	$signMsgVal=appendParam($signMsgVal,"productName",$productName);
	$signMsgVal=appendParam($signMsgVal,"productNum",$productNum);
	$signMsgVal=appendParam($signMsgVal,"productId",$productId);
	$signMsgVal=appendParam($signMsgVal,"productDesc",$productDesc);
	$signMsgVal=appendParam($signMsgVal,"ext1",$ext1);
	$signMsgVal=appendParam($signMsgVal,"ext2",$ext2);
	$signMsgVal=appendParam($signMsgVal,"payType",$payType);	
	$signMsgVal=appendParam($signMsgVal,"bankId",$bankId);
	$signMsgVal=appendParam($signMsgVal,"redoFlag",$redoFlag);
	$signMsgVal=appendParam($signMsgVal,"pid",$pid);
	$signMsgVal=appendParam($signMsgVal,"key",$key);
	$signMsg= strtoupper(md5($signMsgVal));

    $sHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>支付跳转页面-易搜</title>
<style>
  .zhifu_tz{ width:320px; height:150px; border:#e78c55 3px solid; margin:0 auto 50px; position:absolute; top:50%; left:50%; margin-top:-75px; margin-left:-160px;
    /*圆角*/
    -webkit-border-radius: 5px; 
    -moz-border-radius: 5px;
    border-radius: 5px; 
    behavior: url(style/PIE.htc);/*IE6、7、8下支持一些 CSS3 html5 属性*/}
  .zhifu_tz p{ padding:40px 0 0 60px; font-size:14px; line-height:30px; font-weight:bold;}
  .zhifu_tz p img{ vertical-align:middle; margin:0 10px 5px 0;}
  .zhifu_tz p.jishi{padding:20px 0 0 100px; font-size:12px; line-height:30px; font-weight:normal;}
</style>
</head>
<body id="body_user">
      <div class="zhifu_tz">
        <p><img src="' . __ROOT__ .'/Public/member/images/user/loading.gif" width="25" height="25" border="0" />正在跳转至支付页面...</p>
	</div>';
    $sHtml .= '<form name="frm" id="k99billsubmit" method="post" action="https://www.99bill.com/gateway/recvMerchantInfoAction.htm">';
    $sHtml .= '<input type="hidden" name="inputCharset" value="' . $inputCharset . '"/>
			<input type="hidden" name="pageUrl" value="' . $pageUrl . '"/>
			<input type="hidden" name="version" value="' . $version . '"/>
			<input type="hidden" name="language" value="' . $language . '"/>
			<input type="hidden" name="signType" value="' . $signType . '"/>
			<input type="hidden" name="signMsg" value="' . $signMsg . '"/>
			<input type="hidden" name="merchantAcctId" value="' . $merchantAcctId . '"/>
			<input type="hidden" name="payerName" value="' . $payerName . '"/>
			<input type="hidden" name="payerContactType" value="' . $payerContactType . '"/>
			<input type="hidden" name="payerContact" value="' . $payerContact . '"/>
			<input type="hidden" name="orderId" value="' . $orderId . '"/>
			<input type="hidden" name="orderAmount" value="' . $orderAmount . '"/>
			<input type="hidden" name="orderTime" value="' . $orderTime . '"/>
			<input type="hidden" name="productName" value="' . $productName . '"/>
			<input type="hidden" name="productNum" value="' . $productNum . '"/>
			<input type="hidden" name="productId" value="' . $productId . '"/>
			<input type="hidden" name="productDesc" value="' . $productDesc . '"/>
			<input type="hidden" name="ext1" value="' . $ext1 . '"/>
			<input type="hidden" name="ext2" value="' . $ext2 . '"/>
			<input type="hidden" name="payType" value="' . $payType . '"/>
			<input type="hidden" name="bankId" value="' . $bankId . '"/>
			<input type="hidden" name="redoFlag" value="' . $redoFlag . '"/>
			<input type="hidden" name="pid" value="' . $pid . '"/>';
    $sHtml .= '</form>';
    $sHtml .= "<script>document.forms['k99billsubmit'].submit();</script>";
    $sHtml .= "</body></html>";

    echo $sHtml;
  }

  //在线QQ续费 - 支付宝支付
  public function qqonline_renew_alipay_pay(){
    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1,key2') -> where(array('enname' => 'alipay')) -> find();
    Vendor('alipay.alipay_submit','','.class.php');
    $alipay_config = array();
    //合作身份者id，以2088开头的16位纯数字
    $alipay_config['partner'] = $author['key1'];
    //安全检验码，以数字和字母组成的32位字符
    $alipay_config['key'] = $author['key2'];
    //签名方式 不需修改
    $alipay_config['sign_type'] = strtoupper('MD5');
    //字符编码格式 目前支持 gbk 或 utf-8
    $alipay_config['input_charset'] = strtolower('utf-8');
    //ca证书路径地址，用于curl中ssl校验
    ////请保证cacert.pem文件在当前文件夹目录中
    $alipay_config['cacert'] = __ROOT__ . 'Public/cacert.pem';
    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    $alipay_config['transport'] = 'http';

    /**************************请求参数**************************/
    //支付类型
    $payment_type = "1";
    //服务器异步通知页面路径,需http://格式的完整路径，不能加?id=123这类自定义参数
    $notify_url = C('WEBSITE') . "member.php/pay/qqonline_renew_alipaynotify";
    //页面跳转同步通知页面路径,需http://格式的完整路径，不能加?id=123这类自定义参
    $return_url = C('WEBSITE') . "member.php/pay/qqonline_renew_alipayreturn";
    //卖家支付宝帐户
    $seller_email = $author['account'];
    //商户订单号
    $out_trade_no = $this -> _get('oid');
    //订单名称
    $subject = '易搜会员中心在线QQ续费';
    //付款金额，根据订单号查询出来
    $price = M('QqonlineOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    //商品数量,必填，建议默认为1
    $quantity = "1";
    //物流费用,必填，即运费
    $logistics_fee = "0.00";
    //物流类型,必填，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）
    $logistics_type = "EXPRESS";
    //物流支付方式,必填，两个值可选：SELLER_PAY（卖家承担运费）、BUYER_PAY（买家承担运费）
    $logistics_payment = "SELLER_PAY";
    /************************************************************/

    //构造要请求的参数数组，无需改动
    $parameter = array(
      "service" => "create_partner_trade_by_buyer",
      "partner" => trim($alipay_config['partner']),
      "payment_type"	=> $payment_type,
      "notify_url"	=> $notify_url,
      "return_url"	=> $return_url,
      "seller_email"	=> $seller_email,
      "out_trade_no"	=> $out_trade_no,
      "subject"	=> $subject,
      "price"	=> $price,
      "quantity"	=> $quantity,
      "logistics_fee"	=> $logistics_fee,
      "logistics_type"	=> $logistics_type,
      "logistics_payment"	=> $logistics_payment,
      "body"	=> $body,
      "show_url"	=> $show_url,
      "receive_name"	=> $receive_name,
      "receive_address"	=> $receive_address,
      "receive_zip"	=> $receive_zip,
      "receive_phone"	=> $receive_phone,
      "receive_mobile"	=> $receive_mobile,
      "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
    );
    //建立请求
    $alipaySubmit = new AlipaySubmit($alipay_config);
    $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
    echo $html_text;
  
  }

  //在线QQ续费 - 财付通支付
  public function qqonline_renew_tenpay_pay(){
    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1') -> where(array('enname' => 'tenpay')) -> find();
    Vendor('tenpay.RequestHandler','','.class.php');

    $partner = $author['account'];  //财付通商户号
    $key = $author['key1'];  //财付通密钥
    $return_url = C('WEBSITE') . "member.php/pay/qqonline_renew_tenpayreturn";	//同步返回地址
    $notify_url = C('WEBSITE') . "member.php/pay/qqonline_renew_tenpaynotify";  //异步通知地址
    $out_trade_no = $this -> _get('oid'); //订单号
    $desc = '易搜会员中心在线QQ续费';  //商品名称
    $order_price = M('QqonlineOrder') -> getFieldByordernum($this -> _get('oid'), 'price');  //商品价格,根据订单号查寻
    $trade_mode = 1;  //支付方式 1：及时到帐
    $total_fee = $order_price * 100;  //商品价格 以分为单位

    /* 创建支付请求对象 */
    $reqHandler = new RequestHandler();
    $reqHandler->init();
    $reqHandler->setKey($key);
    $reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");

    //----------------------------------------
    ////设置支付参数 
    ////----------------------------------------
    $reqHandler->setParameter("partner", $partner);//商户号
    $reqHandler->setParameter("out_trade_no", $out_trade_no); //订单号
    $reqHandler->setParameter("total_fee", $total_fee);  //总金额
    $reqHandler->setParameter("return_url", $return_url);
    $reqHandler->setParameter("notify_url", $notify_url);
    $reqHandler->setParameter("body", $desc);
    $reqHandler->setParameter("bank_type", "DEFAULT");  	  //银行类型，默认为财付通
    //用户ip
    $reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//客户端IP
    $reqHandler->setParameter("fee_type", "1");               //币种
    $reqHandler->setParameter("subject",$desc);          //商品名称，（中介交易时必填）

    //系统可选参数
    $reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
    $reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
    $reqHandler->setParameter("input_charset", "utf-8");   	  //字符集
    $reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号

    //业务可选参数
    $reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
    $reqHandler->setParameter("product_fee", "");        	  //商品费用
    $reqHandler->setParameter("transport_fee", "0");      	  //物流费用
    $reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
    $reqHandler->setParameter("time_expire", "");             //订单失效时间
    $reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
    $reqHandler->setParameter("goods_tag", "");               //商品标记
    $reqHandler->setParameter("trade_mode",$trade_mode);              //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
    $reqHandler->setParameter("transport_desc","");              //物流说明
    $reqHandler->setParameter("trans_type","1");              //交易类型
    $reqHandler->setParameter("agentid","");                  //平台ID
    $reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
    $reqHandler->setParameter("seller_id","");                //卖家的商户号

    //请求的URL
    $reqUrl = $reqHandler->getRequestURL();
    //提交URL
    //$actionUrl = $reqHandler->getGateUrl();

    $sHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>支付跳转页面-易搜</title>
<style>
  .zhifu_tz{ width:320px; height:150px; border:#e78c55 3px solid; margin:0 auto 50px; position:absolute; top:50%; left:50%; margin-top:-75px; margin-left:-160px;
    /*圆角*/
    -webkit-border-radius: 5px; 
    -moz-border-radius: 5px;
    border-radius: 5px; 
    behavior: url(style/PIE.htc);/*IE6、7、8下支持一些 CSS3 html5 属性*/}
  .zhifu_tz p{ padding:40px 0 0 60px; font-size:14px; line-height:30px; font-weight:bold;}
  .zhifu_tz p img{ vertical-align:middle; margin:0 10px 5px 0;}
  .zhifu_tz p.jishi{padding:20px 0 0 100px; font-size:12px; line-height:30px; font-weight:normal;}
</style>
</head>
<body id="body_user">
      <div class="zhifu_tz">
        <p><img src="' . __ROOT__ .'/Public/member/images/user/loading.gif" width="25" height="25" border="0" />正在跳转至支付页面...</p>
	</div>';
    $sHtml .= "<script>location.href='{$reqUrl}'</script>";
    $sHtml .= "</body></html>";

    echo $sHtml;
  }


  /* ------  在线QQ ------- */


  /* ------  企业形象 ------- */

  //在线QQ管理前置操作
  public function _before_companypic(){
    $this -> _before_index();
  }

  //企业形象管理
  public function companypic(){
    $this -> display();
  }

  //添加企业形象
  public function addcompanypic(){
    //传递cid
    if(!empty($_GET['cid'])){
       $cid = $this -> _get('cid', 'intval');
       //公司信息
       $company_info = M('Company') -> table('yesow_company as c') -> field('c.name,c.address,c.linkman,cs.name as csname,csa.name as csaname') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> where(array('c.id' => $cid)) -> find();
       $this -> assign('company_info', $company_info);

       // 查询该公司是否已有生效的企业形象
       $Companypic = M('Companypic');
       $where_limit['cid'] = $cid;
       $where_limit['starttime'] = array('ELT', time());
       $where_limit['endtime'] = array('EGT', time());
       $add_limit = $Companypic -> where($where_limit) -> find();
       if($add_limit){
	 R('Register/errorjump',array(L('COMPANYPIC_LIMIT')));
       }
    }
    //后台搜索公司
    if(!empty($_REQUEST['keyword'])){
      $where_company['name'] = array('LIKE', '%' . $_REQUEST['keyword'] . '%');
      import("ORG.Util.Page");// 导入分页类
      $count = M('Company') -> where($where_company) -> count();
      $page = new Page($count, 9);
      $page -> parameter = "keyword=" . $_POST['keyword'];
      $show = $page -> show();
      $company_search = M('Company') -> field('id,name,manproducts,address,website,linkman') -> where($where_company) -> order('updatetime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
      $this -> assign('show', $show);
      $this -> assign('company_search', $company_search);
    }
    //查询价格
    $CompanypicMoney = M('CompanypicMoney');
    $companypic_price = $CompanypicMoney -> field('id,months,marketprice,promotionprice') -> order('months ASC') -> select();
    $this -> assign('companypic_price', $companypic_price);
    $this -> display();
  }

  //企业形象订单页
  public function companypic_pay(){

    $result_companypic = array();

    //上传企业形象
    if($_POST['maketype'] == 1){
      import('ORG.Net.UploadFile');
      $upload = new UpLoadFile();
      $upload -> savePath = C('COMPANY_PIC_PATH') ;//设置上传目录
      $upload -> autoSub = false;//设置使用子目录保存上传文件
      $upload -> saveRule = 'uniqid';
      $upload -> allowExts  = array('gif', 'jpg', 'jpeg');// 设置附件上传类型
      //$upload -> maxSize  = 409600 ;// 设置附件上传大小
      if($upload -> upload()){
	$info = $upload -> getUploadFileInfo();
      }else{
	R('Register/errorjump', array('上传企业形象图片失败，请检查文件合理性'));
      }
    }else if($_POST['maketype'] == 2){
      import('ORG.Net.UploadFile');
      $upload = new UpLoadFile();
      $upload -> savePath = C('COMPANY_PIC_DATA_PATH') ;//设置上传目录
      $upload -> autoSub = false;//设置使用子目录保存上传文件
      $upload -> saveRule = 'uniqid';
      $upload -> allowExts  = array('rar', 'zip');// 设置附件上传类型
      //$upload -> maxSize  = 409600 ;// 设置附件上传大小
      if($upload -> upload()){
	$info = $upload -> getUploadFileInfo();
      }else{
	R('Register/errorjump', array('上传企业形象资料失败，请检查文件合理性'));
      }
    }

    if(empty($_GET['orderid'])){
      //月份价格
      $result_companypic = M('CompanypicMoney') -> field('months,promotionprice') -> find($this -> _post('months'));
      //公司id
      $result_companypic['cid'] = $this -> _post('cid', 'intval');
      //网址
      $result_companypic['website'] = $this -> _post('website');
    }else{
      $CompanypicOrder = M('CompanypicOrder');
      $cmid = $CompanypicOrder -> getFieldByordernum($_GET['orderid'], 'cmid');
      $result_companypic = M('CompanypicMoney') -> field('months,promotionprice') -> find($cmid);
      //公司id
      $result_companypic['cid'] =  $CompanypicOrder -> getFieldByordernum($_GET['orderid'], 'cid');
      //网址
      $result_companypic['website'] = $CompanypicOrder -> getFieldByordernum($_GET['orderid'], 'website');
    }

    $result_companypic['filename'] = $info[0]['savename'];
    $result_companypic['maketype'] = $this -> _post('maketype', 'intval');

    //生成订单号
    $result_companypic['orderid'] = !empty($_GET['orderid']) ? $_GET['orderid'] : date('YmdHis') . mt_rand(100000,999999);
    //总价
    $result_companypic['count'] = $result_companypic['promotionprice'];
    
    //公司名称
    $result_companypic['companyname'] = M('Company') -> getFieldByid($result_companypic['cid'], 'name');

    if(empty($_GET['orderid'])){
      //生成订单
      $CompanypicOrder = M('CompanypicOrder');
      $data = array();
      $data['ordernum'] = $result_companypic['orderid'];
      $data['mid'] = session(C('USER_AUTH_KEY'));
      $data['cid'] = $result_companypic['cid'];
      $data['cmid'] = $this -> _post('months', 'intval');
      $data['price'] = $result_companypic['count'];
      $data['maketype'] = $result_companypic['maketype'];
      $data['filename'] = $result_companypic['filename'];
      $data['website'] = $result_companypic['website'];
      $data['addtime'] = time();
      if(!$oid = $CompanypicOrder -> add($data)){
	R('Register/errorjump',array(L('ORDER_ERROR')));
      }
    }
    
    //RMB余额是否足够支付
    $result_companypic['rmb_enough'] = $_SESSION['rmb_total'] - $result_companypic['count'] >= 0 ? 1 : 0;

    $this -> assign('result_companypic', $result_companypic);

    //查询接口信息
    $payport = M('Payport');
    $result_pay = $payport -> field('name,enname') -> where(array('status' => 1)) -> select();
    $this -> assign('result_pay', $result_pay);
    $this -> display();
  }

  //企业形象订单页
  public function companypicorder(){
    $CompanypicOrder = M('CompanypicOrder');
    $where = array();
    $where['co.mid'] = $_SESSION[C('USER_AUTH_KEY')];
    import("ORG.Util.Page");// 导入分页类
    $count = $CompanypicOrder -> table('yesow_companypic_order as co') -> where($where) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $CompanypicOrder -> table('yesow_companypic_order as co') -> field('co.id,co.ordernum,c.name as cname,cm.months,co.price,co.status,co.ischeck,co.paytype,co.addtime,co.isrenew') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> join('yesow_company as c ON co.cid = c.id') -> order('co.addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //企业形象订单详情
  public function companypicorderlist(){
    $oid = $this -> _get('id', 'intval');
    $CompanypicOrder = M('CompanypicOrder');
    $result_order = $CompanypicOrder -> table('yesow_companypic_order as co') -> field('co.maketype,co.ordernum,co.status,co.ischeck,co.price,c.name as cname,cm.months') -> join('yesow_company as c ON co.cid = c.id') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.id' => $oid)) -> find();
    $this -> assign('result_order', $result_order);
    $this -> display();
  }

  //企业形象订单RMB支付页
  public function companypic_rmb_pay(){
    $CompanypicOrder = M('CompanypicOrder');
    //获取交易密码
    $pay_pwd = M('Member') -> getFieldByid($_SESSION[C('USER_AUTH_KEY')], 'traderspassword');
    //未设置交易密码的先去设置交易密码
    if(!$pay_pwd){
      R('Register/errorjump',array(L('TRADERSPASSWORD_EMPTY_ERROR'), '__ROOT__/member.php/index/setsafepwd'));
    }
    //交易密码错误
    if($pay_pwd != $_GET['pwd']){
      R('Register/errorjump',array(L('TRADERSPASSWORD_ERROR'), '__ROOT__/member.php/services/qqonline_pay/orderid/' . $_GET['orderid']));
    }
    //根据订单号查询应付总额
    $const = $CompanypicOrder -> getFieldByordernum($_GET['orderid'], 'price');
    //扣费
    $rmb = D('MemberRmb');
    if(!$rmb -> lessrmb($const)){
      R('Register/errorjump',array(L('RMB_ERROR')));
    }

    //扣费成功更新订单状态
    if(!$CompanypicOrder -> where(array('ordernum' => $this -> _get('orderid'))) -> save(array('status' => 3, 'paytype' => 'RMB余额'))){
      R('Register/errorjump',array(L('ORDER_UPDATE_ERROR')));
    }

    //写RMB消费记录
    $log_content = "您已成功购买 企业形象 服务,订单号{$_GET['orderid']}";
    if(!D('member://MemberRmbDetail') -> writelog($_SESSION[C('USER_AUTH_KEY')], $log_content, '消费', '-' . $const)){
      R('Register/errorjump',array(L('RMB_LOG_ERROR')));
    }

    //订单相关信息
    $companypic_info = $CompanypicOrder -> table('yesow_companypic_order as co') -> field('co.id,co.filename,co.cid,cm.months,co.website') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.ordernum' => $_GET['orderid'])) -> find();

    //写主表
    $Companypic = M('Companypic');
    $pic_data = array();
    $pic_data['mid'] = session(C('USER_AUTH_KEY'));
    $pic_data['cid'] = $companypic_info['cid'];
    $pic_data['filename'] = $companypic_info['filename'];
    $pic_data['website'] = $companypic_info['website'];
    $pic_data['starttime'] = time();
    $pic_data['updatetime'] = time();
    $pic_data['endtime'] = $pic_data['starttime'] + ($companypic_info['months'] * 30 * 24 * 60 * 60);
    if($Companypic -> add($pic_data)){
      $info_succ = "您已成功购买企业形象相关服务";
      //更新会员余额和等级
      if(!$rmb -> rmbtotal()){
	R('Register/errorjump',array(L('RMB_CACHE')));
      }
      R('Services/companypicsuccess',array($info_succ, 'success', $companypic_info['cid']));
    }else{
      R('Register/errorjump',array(L('COMPANYPIC_ERROR')));
    }
  }

  //企业形象快钱支付
  public function companypic_k99bill_pay(){
    $pageUrl = C('WEBSITE') . "member.php/pay/companypic_k99billreturn";;
    $orderId = $this -> _get('oid');
    $rmb_amount = M('CompanypicOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    $productName = '易搜会员中心企业形象购买';
    R('Public/k99bill_pay',array($pageUrl, $orderId, $rmb_amount, $productName));
  }

  //企业形象支付宝支付
  public function companypic_alipay_pay(){
    $notify_url = C('WEBSITE') . "member.php/pay/companypic_alipaynotify";
    $return_url = C('WEBSITE') . "member.php/pay/companypic_alipayreturn";
    $out_trade_no = $this -> _get('oid');
    $subject = '易搜会员中心企业形象购买';
    $price = M('CompanypicOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    R('Public/alipay_pay',array($notify_url, $return_url, $out_trade_no, $subject, $price));
  }

  //企业形象财付通支付
  public function companypic_tenpay_pay(){
    $return_url = C('WEBSITE') . "member.php/pay/companypic_tenpayreturn";
    $notify_url = C('WEBSITE') . "member.php/pay/companypic_tenpaynotify";
    $out_trade_no = $this -> _get('oid');
    $desc = '易搜会员中心企业形象购买';
    $order_price = M('CompanypicOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    R('Public/tenpay_pay',array($return_url, $notify_url, $out_trade_no, $desc, $order_price));
  }

  //企业形象购买成功
  public function companypicsuccess($title, $status, $cid){
    $this -> assign('status', $status);
    $this -> assign('cid', $cid);
    $this -> assign('title', $title);
    $this -> display('services:companypicsuccess');
    exit();
  }

  //企业形象管理
  public function editcompanypic(){
    $Companypic = M('Companypic');
    import("ORG.Util.Page");// 导入分页类
    $count = $Companypic -> where(array('mid' => session(C('USER_AUTH_KEY')))) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $Companypic -> table('yesow_companypic as cp') -> field('cp.id,c.id as cid,c.name as cname,cp.starttime,cp.endtime,cp.filename') -> join('yesow_company as c ON cp.cid = c.id') -> where(array('cp.mid' => session(C('USER_AUTH_KEY')))) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('cp.starttime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //编辑企业形象管理
  public function editeditcompanypic(){
    $Companypic = M('Companypic');

    if(!empty($_POST['id'])){
      //上传企业形象
      if($_POST['updatetype'] == 1){
      import('ORG.Net.UploadFile');
      $upload = new UpLoadFile();
      $upload -> savePath = C('COMPANY_PIC_PATH') ;//设置上传目录
      $upload -> autoSub = false;//设置使用子目录保存上传文件
      $upload -> saveRule = 'uniqid';
      $upload -> allowExts  = array('gif', 'jpg', 'jpeg');// 设置附件上传类型
      //$upload -> maxSize  = 409600 ;// 设置附件上传大小
      if($upload -> upload()){
	$info = $upload -> getUploadFileInfo();
      }else{
	R('Register/errorjump', array('上传企业形象图片失败，请检查文件合理2性'));
      }
    }else if($_POST['updatetype'] == 2){
      import('ORG.Net.UploadFile');
      $upload = new UpLoadFile();
      $upload -> savePath = C('COMPANY_PIC_DATA_PATH') ;//设置上传目录
      $upload -> autoSub = false;//设置使用子目录保存上传文件
      $upload -> saveRule = 'uniqid';
      $upload -> allowExts  = array('rar', 'zip');// 设置附件上传类型
      //$upload -> maxSize  = 409600 ;// 设置附件上传大小
      if($upload -> upload()){
	$info = $upload -> getUploadFileInfo();
      }else{
	R('Register/errorjump', array('上传企业形象资料失败，请检查文件合理3性'));
      }
    }

      $upload_data = array();
      if(!empty($_POST['updatetype'])){
	$upload_data['filename'] = $info[0]['savename'];
      }
      $upload_data['id'] = $_POST['id'];
      $upload_data['website'] = $_POST['website'];
      $upload_data['updatetime'] = time();
      if($Companypic -> save($upload_data)){
	if(!empty($_POST['months'])){
	  R('Register/successjump',array(L('COMPANYPIC_RENEW'), U('Services/companypic_renew_pay') . '/oid/' . $this -> _post('months', 'intval') . '/qid/' . $this -> _post('id', 'intval')));
	}else{
	  R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Services/editcompanypic')));
	}
      }else{
	if(!empty($_POST['months'])){
	  R('Register/successjump',array(L('COMPANYPIC_RENEW'), U('Services/companypic_renew_pay') . '/oid/' . $this -> _post('months', 'intval') . '/qid/' . $this -> _post('id', 'intval')));
	}else{
	  R('Register/errorjump',array(L('DATA_UPDATE_ERROR')));
	}
      }
    }

    $result = $Companypic -> table('yesow_companypic as cp') -> field('cp.starttime,cp.endtime,c.name as cname,cp.filename,cp.website') -> join('yesow_company as c ON cp.cid = c.id') -> where(array('cp.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    //查询价格
    $CompanypicMoney = M('CompanypicMoney');
    $companypic_price = $CompanypicMoney -> field('id,months,marketprice,promotionprice') -> order('months ASC') -> select();
    $this -> assign('companypic_price', $companypic_price);
    $this -> display();
  }

  //企业形象续费订单页
  public function companypic_renew_pay(){
    $result_companypic = array();

    if(empty($_GET['orderid'])){
      //月份价格
      $result_companypic = M('CompanypicMoney') -> field('months,promotionprice') -> find($this -> _get('oid'));
      //公司id
      $result_companypic['cid'] = M('Companypic') -> getFieldByid($this -> _get('qid', 'intval'), 'cid');
    }else{
      $CompanypicOrder = M('CompanypicOrder');
      $cmid = $CompanypicOrder -> getFieldByordernum($_GET['orderid'], 'cmid');
      $result_companypic = M('CompanypicMoney') -> field('months,promotionprice') -> find($cmid);
      //公司id
      $result_companypic['cid'] =  $CompanypicOrder -> getFieldByordernum($_GET['orderid'], 'cid');
    }

    $filename = M('Companypic') -> getFieldByid($this -> _get('qid', 'intval'), 'filename');
    $result_companypic['filename'] = $filename;
    if(strstr($filename, '.') == '.rar' || strstr($filename, '.') == '.zip'){
      $result_companypic['maketype'] = 2;
    }else{
      $result_companypic['maketype'] = 1;
    }

    //生成订单号
    $result_companypic['orderid'] = !empty($_GET['orderid']) ? $_GET['orderid'] : date('YmdHis') . mt_rand(100000,999999);
    //总价
    $result_companypic['count'] = $result_companypic['promotionprice'];
    
    //公司名称
    $result_companypic['companyname'] = M('Company') -> getFieldByid($result_companypic['cid'], 'name');
    
    if(empty($_GET['orderid'])){
      //生成订单
      $CompanypicOrder = M('CompanypicOrder');
      $data = array();
      $data['ordernum'] = $result_companypic['orderid'];
      $data['mid'] = session(C('USER_AUTH_KEY'));
      $data['cid'] = $result_companypic['cid'];
      $data['cmid'] = $this -> _get('oid', 'intval');
      $data['price'] = $result_companypic['count'];
      $data['maketype'] = $result_companypic['maketype'];
      $data['filename'] = $result_companypic['filename'];
      $data['addtime'] = time();
      $data['isrenew'] = 1;
      if(!$oid = $CompanypicOrder -> add($data)){
	R('Register/errorjump',array(L('ORDER_ERROR')));
      }
    }
    
    //RMB余额是否足够支付
    $result_companypic['rmb_enough'] = $_SESSION['rmb_total'] - $result_companypic['count'] >= 0 ? 1 : 0;

    $this -> assign('result_companypic', $result_companypic);

    //查询接口信息
    $payport = M('Payport');
    $result_pay = $payport -> field('name,enname') -> where(array('status' => 1)) -> select();
    $this -> assign('result_pay', $result_pay);
    $this -> display();
  }

  //企业形象 - 续费 - 余额支付
  public function companypic_renew_rmb_pay(){
    $CompanypicOrder = M('CompanypicOrder');
    //获取交易密码
    $pay_pwd = M('Member') -> getFieldByid($_SESSION[C('USER_AUTH_KEY')], 'traderspassword');
    //未设置交易密码的先去设置交易密码
    if(!$pay_pwd){
      R('Register/errorjump',array(L('TRADERSPASSWORD_EMPTY_ERROR'), '__ROOT__/member.php/index/setsafepwd'));
    }
    //交易密码错误
    if($pay_pwd != $_GET['pwd']){
      R('Register/errorjump',array(L('TRADERSPASSWORD_ERROR'), '__ROOT__/member.php/services/qqonline_pay/orderid/' . $_GET['orderid']));
    }
    //根据订单号查询应付总额
    $const = $CompanypicOrder -> getFieldByordernum($_GET['orderid'], 'price');
    //扣费
    $rmb = D('MemberRmb');
    if(!$rmb -> lessrmb($const)){
      R('Register/errorjump',array(L('RMB_ERROR')));
    }

    //扣费成功更新订单状态
    if(!$CompanypicOrder -> where(array('ordernum' => $this -> _get('orderid'))) -> save(array('status' => 3, 'paytype' => 'RMB余额'))){
      R('Register/errorjump',array(L('ORDER_UPDATE_ERROR')));
    }

    //写RMB消费记录
    $log_content = "您已成功续费 企业形象 服务,订单号{$_GET['orderid']}";
    if(!D('member://MemberRmbDetail') -> writelog($_SESSION[C('USER_AUTH_KEY')], $log_content, '消费', '-' . $const)){
      R('Register/errorjump',array(L('RMB_LOG_ERROR')));
    }

    //订单相关信息
    $companypic_info = $CompanypicOrder -> table('yesow_companypic_order as co') -> field('co.id,co.filename,co.cid,cm.months') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.ordernum' => $_GET['orderid'])) -> find();

    //更新主表
    $Companypic = M('Companypic');
    $where_up = array();
    $where_up['cid'] = $companypic_info['cid'];

    if($Companypic -> where($where_up) -> setInc('endtime', $companypic_info['months'] * 30 * 24 * 60 * 60)){
      $info_succ = "您已成功续费企业形象相关服务";
      //更新会员余额和等级
      if(!$rmb -> rmbtotal()){
	R('Register/errorjump',array(L('RMB_CACHE')));
      }
      R('Services/companypicsuccess',array($info_succ, 'success', $companypic_info['cid']));
    }else{
      R('Register/errorjump',array(L('COMPANYPIC_ERROR')));
    }
  }

  //企业形象快钱支付
  public function companypic_renew_k99bill_pay(){
    $pageUrl = C('WEBSITE') . "member.php/pay/companypic_renew_k99billreturn";;
    $orderId = $this -> _get('oid');
    $rmb_amount = M('CompanypicOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    $productName = '易搜会员中心企业形象续费';
    R('Public/k99bill_pay',array($pageUrl, $orderId, $rmb_amount, $productName));
  }

  //企业形象支付宝支付
  public function companypic_renew_alipay_pay(){
    $notify_url = C('WEBSITE') . "member.php/pay/companypic_renew_alipaynotify";
    $return_url = C('WEBSITE') . "member.php/pay/companypic_renew_alipayreturn";
    $out_trade_no = $this -> _get('oid');
    $subject = '易搜会员中心企业形象续费';
    $price = M('CompanypicOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    R('Public/alipay_pay',array($notify_url, $return_url, $out_trade_no, $subject, $price));
  }

  //企业形象财付通支付
  public function companypic_renew_tenpay_pay(){
    $return_url = C('WEBSITE') . "member.php/pay/companypic_renew_tenpayreturn";
    $notify_url = C('WEBSITE') . "member.php/pay/companypic_renew_tenpaynotify";
    $out_trade_no = $this -> _get('oid');
    $desc = '易搜会员中心企业形象续费';
    $order_price = M('CompanypicOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    R('Public/tenpay_pay',array($return_url, $notify_url, $out_trade_no, $desc, $order_price));
  }


  /* ------  企业形象 ------- */

  //短信群发业务
  public function _before_sms(){
    $this -> _before_index();
  }
  public function sms(){
    $this -> display();
  }

  //短信群发管理
  public function sendsms(){
    //发送通道
    $sendtype = M('SmsSendType');
    $result_sendtype = $sendtype -> field('apicode,name') -> select();
    $this -> assign('result_sendtype', $result_sendtype);
    //发送价格
    $setting = M('SmsSetting');
    $send_sms_price = $setting -> getFieldByname('send_sms_price', 'value');
    $this -> assign('send_sms_price', $send_sms_price);
    //个人号码薄
    $MemberSmsGroup = M('MemberSmsGroup');
    $sms_group = $MemberSmsGroup -> field('id,name') -> where(array('mid' => session(C('USER_AUTH_KEY')))) -> select();
    $this -> assign('sms_group', $sms_group);

    //将要发送的号码
    $sendphone = '';
    //后台搜索号码
    if(!empty($_SESSION['member_search_send_list'])){
      foreach($_SESSION['member_search_send_list'] as $value){
	if(empty($sendphone)){
	  $sendphone .= substr_replace($value, '****', 3, 4);
	}else{
	  $sendphone .= ',' . substr_replace($value, '****', 3, 4);
	}
      }
      $this -> assign('issearch', 'true');
    //上传号码
    }else if(!empty($_SESSION['member_upload_send_list'])){
      foreach($_SESSION['member_upload_send_list'] as $value){
	if(empty($sendphone)){
	  $sendphone .= substr_replace($value, '****', 3, 4);
	}else{
	  $sendphone .= ',' . substr_replace($value, '****', 3, 4);
	}
      }
      $this -> assign('isupload', 'true');   
    }
    $this -> assign('sendphone', $sendphone);
    $this -> display();
  }

  //执行发送
  public function tosendsms(){
    set_time_limit(0);
    //文本上传
    if(!empty($_POST['textfield'])){
      import('ORG.Net.UploadFile');
      $upload = new UpLoadFile();
      $upload -> savePath = C('TEMP_UPLOAD_PATH') ;//设置上传目录
      $upload -> autoSub = false;//设置使用子目录保存上传文件
      $upload -> saveRule = 'uniqid';
      $upload -> allowExts  = array('txt');// 设置附件上传类型
      $upload -> maxSize  = 409600 ;// 设置附件上传大小
      if($upload -> upload()){
	$info = $upload -> getUploadFileInfo();
	//读取上传文件
	$string_upload = file_get_contents($info[0]['savepath'] . $info[0]['savename']);
	$arr_upload = explode(',', $string_upload); 
	
	//上传文档号码
	$_SESSION['member_upload_send_list'] = array();
	foreach($arr_upload as $value){
	  $_SESSION['member_upload_send_list'][] = $value;
	}
	R('Register/successjump',array('上传成功,现在跳转到发送页面'));
      }else{
	R('Register/errorjump', array('上传文档失败，请检查上传文件合法性'));
      }
    }
    $sHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>正在发送-易搜</title>
<style>
  .zhifu_tz{ width:320px; height:150px; border:#e78c55 3px solid; margin:0 auto 50px; position:absolute; top:50%; left:50%; margin-top:-75px; margin-left:-160px;
    /*圆角*/
    -webkit-border-radius: 5px; 
    -moz-border-radius: 5px;
    border-radius: 5px; 
    behavior: url(style/PIE.htc);/*IE6、7、8下支持一些 CSS3 html5 属性*/}
  .zhifu_tz p{ padding:40px 0 0 60px; font-size:14px; line-height:30px; font-weight:bold;}
  .zhifu_tz p img{ vertical-align:middle; margin:0 10px 5px 0;}
  .zhifu_tz p.jishi{padding:20px 0 0 100px; font-size:12px; line-height:30px; font-weight:normal;}
</style>
</head>
<body id="body_user">
      <div class="zhifu_tz">
        <p><img src="' . __ROOT__ .'/Public/member/images/user/loading.gif" width="25" height="25" border="0" />正在发送，请耐心等待...</p>
	</div></body></html>';
    echo $sHtml;
    flush();//输出送出的缓冲内容

    //提交来的号码列表
    $sendnumber_arr = explode(',', $_POST['sendnumber']);

    //要发送的号码
    $to_send = array();

    //输入框发送
    if($_POST['phonetype'] == 'list'){

      /* -- 保存通讯录 Start -- */
      if($_POST['savegroup'] == 'true'){
	$MemberSmsGroup = D('MemberSmsGroup');
	$MemberSmsGroupList = M('MemberSmsGroupList');
	$data = array();
	$data['mid'] = session(C('USER_AUTH_KEY'));
	$data['name'] = $this -> _post('savegroupname');
	if(!$MemberSmsGroup -> create($data)){
	  R('Register/errorjump', array('添加通讯录失败'));
	}
	//添加通讯录详情
	if($gid = $MemberSmsGroup -> add()){
	  //搜索得到的记录
	  if(!empty($_POST['issearch'])){
	    foreach($_SESSION['member_search_send_list'] as $value){
	      $list_data = array();
	      $list_data['gid'] = $gid;
	      $list_data['realnumber'] = $value;
	      $list_data['hidenumber'] = substr_replace($value, '****', 3, 4);
	      $MemberSmsGroupList -> add($list_data);
	    }
	  }else if(!empty($_POST['isupload'])){
	    foreach($_SESSION['member_upload_send_list'] as $value){
	      $list_data = array();
	      $list_data['gid'] = $gid;
	      $list_data['realnumber'] = $value;
	      $list_data['hidenumber'] = substr_replace($value, '****', 3, 4);
	      $MemberSmsGroupList -> add($list_data);
	    }
	  }
	  //后添加的
	  foreach($sendnumber_arr as $value){
	    if(!strpos($value, '*')){
	      $list_data = array();
	      $list_data['gid'] = $gid;
	      $list_data['realnumber'] = $value;
	      $list_data['hidenumber'] = substr_replace($value, '****', 3, 4);
	      $MemberSmsGroupList -> add($list_data);
	    }
	  }
	}else{
	  R('Register/errorjump', array('添加通讯录失败'));
	}
      }
      /* -- 保存通讯录 End -- */

      //组合发送号码
      //搜索号码
      if(!empty($_POST['issearch'])){
	$to_send = array_merge($to_send, $_SESSION['member_search_send_list']);
      }else if(!empty($_POST['isupload'])){
	$to_send = array_merge($to_send, $_SESSION['member_upload_send_list']);
      }
      //后添加号码
      foreach($sendnumber_arr as $value){
	if(!strpos($value, '*')){
	  $to_send[] = $value;
	}
      }

    //号码薄发送
    }else if($_POST['phonetype'] == 'group'){
      $MemberSmsGroupList = M('MemberSmsGroupList');
      $group_list = $MemberSmsGroupList -> field('realnumber') -> where(array('gid' => $_POST['phonegroup'])) -> select();
      foreach($group_list as $value){
	$to_send[] = $value['realnumber'];
      }
    }

    /*  ----  执行发送  ----- */

    //计算短信条数
    $content_length = mb_strlen($_POST['content'], 'UTF-8');
    $sms_num = ceil($content_length / 64);

    //扣费
    //搜索价格
    $setting = M('SmsSetting');
    $send_phone_price = $setting -> getFieldByname('send_sms_price', 'value');
    //消费金额
    $cost = count($to_send) * $send_phone_price * $sms_num;
    //扣费
    $MemberRmb = D('member://MemberRmb');
    if($MemberRmb -> autolessmoney($cost)){
      //写消费日志
      $MemberRmbDetail = D('member://MemberRmbDetail');
      $MemberRmbDetail -> writelog($_SESSION[C('USER_AUTH_KEY')], '您在易搜用户中心发送手机短信', '消费', '-' . $cost);
      

      //读取发送配置
      $setting = M('SmsSetting');
      $sms_username = $setting -> getFieldByname('sms_username', 'value');
      $sms_password = $setting -> getFieldByname('sms_password', 'value');

      $MemberSendSmsRecord = M('MemberSendSmsRecord');

      //执行发送
      foreach($to_send as $value){
	$url = "http://www.vip.86aaa.com/api.aspx?SendType={$_POST['sendtype']}&Code=utf-8&UserName={$sms_username}&Pwd={$sms_password}&Mobi={$value}&Content={$_POST['content']}【yesow】";
	$url = iconv('UTF-8', 'GB2312', $url);
	$fp = fopen($url, 'rb');
	$ret= fgetss($fp,255);
	fclose($fp);
	//记录发送信息
	$data_rec = array();
	$data_rec['mid'] = $_SESSION[C('USER_AUTH_KEY')];
	$data_rec['sendtime'] = time();
	$data_rec['content'] = $_POST['content'];
	$data_rec['sendphone'] = $value;
	if($ret === false){
	  $ret = 5;
	}
	//发送失败退费
	if($ret != 0){
	  //增加金额
	  $MemberRmb -> addmoney('rmb_exchange', $send_phone_price);
	  //写日志
	  $MemberRmbDetail -> writelog($_SESSION[C('USER_AUTH_KEY')], '您在易搜用户中心发送手机短信失败的退费', '退费', '+' . $send_phone_price);

	}
	$data_rec['statuscode'] = $ret;
	$MemberSendSmsRecord -> add($data_rec);
      }
      //重新缓存用户余额
      $MemberRmb -> rmbtotal();
      //清空信息
      $_SESSION['member_search_send_list'] = array();
      $_SESSION['member_upload_send_list'] = array();
      echo "<script>location.href='" . __URL__ ."/sendendjump';</script>";
    
    }else{
      R('Register/errorjump', array('用户余额不足，请充值', U('Services/sendsms')));
    }
  }

  //发送完毕跳转
  public function sendendjump(){
    R('Register/successjump',array('发送完毕,现在跳转到发送记录', U('Services/smsendrecord')));
  }

  //搜索号码
  public function searchcompanyphone(){
    if(!empty($_GET['keyword'])){
      $keyword = $this -> _get('keyword');
      $company = M('Company');
      $map['_string'] = "LENGTH(mobilephone) = 11";
      $where = array();
      $where['delaid']  = array('exp', 'is NULL');
      $where['_string'] = "( name LIKE '%{$keyword}%' ) OR ( address LIKE '%{$keyword}%' ) OR ( manproducts LIKE '%{$keyword}%' ) OR ( mobilephone LIKE '%{$keyword}%' ) OR ( email LIKE '%{$keyword}%' ) OR ( linkman LIKE '%{$keyword}%' ) OR ( companyphone LIKE '%{$keyword}%' ) OR ( qqcode LIKE '%{$keyword}%' ) OR ( website LIKE '%{$keyword}%' )";
      if($_GET['searchscope'] == 'city'){
	$where['csid'] = $this -> _get('csid', 'intval');
	if(!empty($_GET['csaid'])){
	  $where['csaid'] = $this -> _get('csaid', 'intval');
	}
      }
      $where['_complex'] = $map;

      import("ORG.Util.Page");// 导入分页类
      $count = $company -> where($where) -> count('id');
      $page = new Page($count, 10);//每页10条
      $show = $page -> show();
      $this -> assign('show', $show);

      $result = $company -> field('id,name,manproducts,mobilephone') -> where($where) -> order('id DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
      $this -> assign('result', $result);
      $this -> assign('count', $count);

      //搜索价格
      $setting = M('SmsSetting');
      $search_phone_price = $setting -> getFieldByname('search_phone_price', 'value');
      $this -> assign('search_phone_price', $search_phone_price);
    }
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  //ajax处理搜索结果添加到待发送列表
  public function ajaxaddsearchsendlist(){
    if(!is_array($_SESSION['member_search_phone_list'])){
      $_SESSION['member_search_phone_list'] = array();
    }
    if(in_array($_GET['cid'], $_SESSION['member_search_phone_list'])){
      unset($_SESSION['member_search_phone_list'][array_search($_GET['cid'], $_SESSION['member_search_phone_list'])]);
    }else{
      $_SESSION['member_search_phone_list'][] = $_GET['cid'];
    } 
    echo count($_SESSION['member_search_phone_list']);
  }

  //提取搜索结果并扣费
  public function searchresult(){
    //搜索价格
    $setting = M('SmsSetting');
    $search_phone_price = $setting -> getFieldByname('search_phone_price', 'value');
    //消费金额
    $cost = count($_SESSION['member_search_phone_list']) * $search_phone_price;
    //扣费
    $MemberRmb = D('member://MemberRmb');
    if($MemberRmb -> autolessmoney($cost)){
      //写消费日志
      $MemberRmbDetail = D('member://MemberRmbDetail');
      $MemberRmbDetail -> writelog($_SESSION[C('USER_AUTH_KEY')], '您在易搜用户中心搜索手机号码', '消费', '-' . $cost);
      //处理待发送数组
      $company = M('Company');
      $_SESSION['member_search_send_list'] = array();
      foreach($_SESSION['member_search_phone_list'] as $value){
	$_SESSION['member_search_send_list'][] = substr($company -> getFieldByid($value, 'mobilephone'), 0, 11);
      }
      $_SESSION['member_search_phone_list'] = array();
      //记录搜索日志
      $MemberSearchSmsRecord = M('MemberSearchSmsRecord');
      $data_rec = array();
      $data_rec['mid'] = session(C('USER_AUTH_KEY'));
      $data_rec['keyword'] = $this -> _get('keyword');
      $data_rec['checknum'] = count($_SESSION['member_search_send_list']);
      $data_rec['ip'] = get_client_ip();
      $data_rec['searchtime'] = time();
      $MemberSearchSmsRecord -> add($data_rec);
      //更新RMB缓存
      if(!$MemberRmb -> rmbtotal()){
	R('Register/errorjump',array(L('RMB_CACHE')));
      }
      R('Register/successjump',array('扣费成功！现在转入待发送页面', U('Services/sendsms')));
    }else{
      R('Register/errorjump', array('用户余额不足，请充值', U('Services/sendsms')));
    }
  }

  //短信发送记录
  public function smsendrecord(){
    $MemberSendSmsRecord = M('MemberSendSmsRecord');
    $where = array();
    $where['mid'] = session(C('USER_AUTH_KEY'));
    import("ORG.Util.Page");// 导入分页类
    $count = $MemberSendSmsRecord -> where($where) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $MemberSendSmsRecord -> field('sendtime,content,sendphone,statuscode') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('sendtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //短信群发薄
  public function sendsmsgroup(){
    $MemberSmsGroup = D('MemberSmsGroup');

    import("ORG.Util.Page");// 导入分页类
    $count = $MemberSmsGroup -> table('yesow_member_sms_group as msg') -> where(array('msg.mid' => session(C('USER_AUTH_KEY')))) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();

    $result = $MemberSmsGroup -> table('yesow_member_sms_group as msg') -> field('msg.id,msg.name,msg.addtime,tmp.count') -> join('LEFT JOIN (SELECT gid,COUNT(id) as count FROM yesow_member_sms_group_list GROUP BY gid) as tmp ON tmp.gid = msg.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where(array('msg.mid' => session(C('USER_AUTH_KEY')))) -> order('msg.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //短信群发薄详情
  public function sendsmsgrouplist(){
    $MemberSmsGroupList = D('MemberSmsGroupList');
    $where = array();
    $where['gid'] = $this -> _get('id', 'intval');

    import("ORG.Util.Page");// 导入分页类
    $count = $MemberSmsGroupList -> where($where) -> count();
    $page = new Page($count, 42);
    $show = $page -> show();

    $result = $MemberSmsGroupList -> field('hidenumber') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //邮件群发业务
  public function _before_email(){
    $this -> _before_index();
  }
  public function email(){
  
  }

  /* ------  页面广告 ------- */
  //页面广告管理
  public function _before_advert(){
    $this -> _before_index();
  }
  public function advert(){
    $this -> display();
  }

  //添加页面广告
  public function addadvert(){
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    if(!empty($_GET['adid'])){
      //已经购买
      $where_have = array();
      $where_have['adid'] = $this -> _get('adid', 'intval');
      $where_have['starttime'] = array('ELT', time());
      $where_have['endtime'] = array('EGT', time());
      if(M('Advert') -> where($where_have) -> find()){
	R('Register/errorjump',array(L('ADVERT_LIMIT')));
      }
      $pid = M('Advertise') -> getFieldByid($this -> _get('adid', 'intval'), 'pid');
      $csid = M('AdvertisePage') -> getFieldByid($pid, 'csid');
      $advert_list = M('Advertise') -> field('id,name,width,height') -> where(array('pid' => $pid, 'isopen' => 1)) -> select();
      $where_limit = array();
      $where_limit['starttime'] = array('ELT', time());
      $where_limit['endtime'] = array('EGT', time());
      $del_adid_tmp = M('Advert') -> field('adid') -> where($where_limit) -> select();
      $del_adid = array();
      foreach($del_adid_tmp as $value){
	$del_adid[] = $value['adid'];
      }
      foreach($advert_list as $key => $value){
	if(in_array($value['id'], $del_adid)){
	  unset($advert_list[$key]);
	}
      }
      $this -> assign('advert_list', $advert_list);
      $advert_page_list = M('AdvertisePage') -> field('id,remark') -> select($pid);
      $this -> assign('advert_page_list', $advert_page_list);

      $this -> assign('pid', $pid);
      $this -> assign('csid', $csid);
    }
    $this -> display();
  }

  //页面广告订单页
  public function advertorder(){
    $AdvertOrder = M('AdvertOrder');
    $where = array();
    $where['ao.mid'] = $_SESSION[C('USER_AUTH_KEY')];
    import("ORG.Util.Page");// 导入分页类
    $count = $AdvertOrder -> table('yesow_advert_order as ao') -> where($where) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $AdvertOrder -> table('yesow_advert_order as ao') -> field('ao.id,ao.ordernum,am.months,ao.price,ao.status,ao.ischeck,ao.paytype,ao.addtime,ao.isrenew,ad.name as adname,adp.remark as adpremark,cs.name as csname') -> join('yesow_advert_money as am ON ao.amid = am.id') -> join('yesow_advertise as ad ON ao.adid = ad.id') -> join('yesow_advertise_page as adp ON ad.pid = adp.id') -> join('yesow_child_site as cs ON adp.csid = cs.id') -> order('ao.addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //页面广告订单详情页
  public function advertorderlist(){
    $oid = $this -> _get('id', 'intval');
    $AdvertOrder = M('AdvertOrder');
    $result_order = $AdvertOrder -> table('yesow_advert_order as ao') -> field('ao.id,ao.ordernum,am.months,ao.price,ao.status,ao.ischeck,ao.paytype,ao.addtime,ao.isrenew,ad.name as adname,adp.remark as adpremark,cs.name as csname,ao.website,ao.maketype') -> join('yesow_advert_money as am ON ao.amid = am.id') -> join('yesow_advertise as ad ON ao.adid = ad.id') -> join('yesow_advertise_page as adp ON ad.pid = adp.id') -> join('yesow_child_site as cs ON adp.csid = cs.id') -> where(array('ao.id' => $oid)) -> find();
    $this -> assign('result_order', $result_order);

    $this -> display();
  }

  //页面广告订单支付页
  public function advert_pay(){

    $result_advert = array();

    //上传页面广告
    if($_POST['maketype'] == 1){
      import('ORG.Net.UploadFile');
      $upload = new UpLoadFile();
      $upload -> savePath = C('ADVERT_PIC_PATH') ;//设置上传目录
      $upload -> autoSub = false;//设置使用子目录保存上传文件
      $upload -> saveRule = 'uniqid';
      $upload -> allowExts  = array('gif', 'jpg', 'jpeg');// 设置附件上传类型
      //$upload -> maxSize  = 409600 ;// 设置附件上传大小
      if($upload -> upload()){
	$info = $upload -> getUploadFileInfo();
      }else{
	R('Register/errorjump', array('上传页面广告图片失败，请检查文件合理性'));
      }
    }else if($_POST['maketype'] == 2){
      import('ORG.Net.UploadFile');
      $upload = new UpLoadFile();
      $upload -> savePath = C('ADVERT_PIC_DATA_PATH') ;//设置上传目录
      $upload -> autoSub = false;//设置使用子目录保存上传文件
      $upload -> saveRule = 'uniqid';
      $upload -> allowExts  = array('rar', 'zip');// 设置附件上传类型
      //$upload -> maxSize  = 409600 ;// 设置附件上传大小
      if($upload -> upload()){
	$info = $upload -> getUploadFileInfo();
      }else{
	R('Register/errorjump', array('上传页面广告资料失败，请检查文件合理性'));
      }
    }

    if(empty($_GET['orderid'])){
      //月份价格
      $result_advert = M('AdvertMoney') -> field('months,promotionprice') -> find($this -> _post('months'));
      //广告位id
      $result_advert['adid'] = $this -> _post('adid', 'intval');
      //网址
      $result_advert['website'] = $this -> _post('website');
    }else{
      $AdvertOrder = M('AdvertOrder');
      $amid = $AdvertOrder -> getFieldByordernum($_GET['orderid'], 'amid');
      $result_advert = M('AdvertMoney') -> field('months,promotionprice') -> find($amid);
      //广告位id
      $result_advert['adid'] =  $AdvertOrder -> getFieldByordernum($_GET['orderid'], 'adid');
      //网址
      $result_advert['website'] = $AdvertOrder -> getFieldByordernum($_GET['orderid'], 'website');
    }

    $result_advert['filename'] = $info[0]['savename'];
    $result_advert['maketype'] = $this -> _post('maketype', 'intval');

    //生成订单号
    $result_advert['orderid'] = !empty($_GET['orderid']) ? $_GET['orderid'] : date('YmdHis') . mt_rand(100000,999999);

    //总价
    $result_advert['count'] = $result_advert['promotionprice'];
    
    //广告位名称
    $temp_name = M('Advertise') -> table('yesow_advertise as ad') -> field('ad.name as adname,adp.remark as adpremark,cs.name as csname') -> join('yesow_advertise_page as adp ON ad.pid = adp.id') -> join('yesow_child_site as cs ON adp.csid = cs.id') -> where(array('ad.id' => $result_advert['adid'])) -> find();
    $result_advert['advertname'] = $temp_name['csname'] . ' - ' . $temp_name['adpremark'] . ' - ' . $temp_name['adname'];

    if(empty($_GET['orderid'])){
      //生成订单
      $AdvertOrder = M('AdvertOrder');
      $data = array();
      $data['ordernum'] = $result_advert['orderid'];
      $data['mid'] = session(C('USER_AUTH_KEY'));
      $data['adid'] = $result_advert['adid'];
      $data['amid'] = $this -> _post('months', 'intval');
      $data['price'] = $result_advert['count'];
      $data['website'] = $result_advert['website'];
      $data['maketype'] = $result_advert['maketype'];
      $data['filename'] = $result_advert['filename'];
      $data['addtime'] = time();
      if(!$oid = $AdvertOrder -> add($data)){
	R('Register/errorjump',array(L('ORDER_ERROR')));
      }
    }

     //RMB余额是否足够支付
    $result_advert['rmb_enough'] = $_SESSION['rmb_total'] - $result_advert['count'] >= 0 ? 1 : 0;
    $this -> assign('result_advert', $result_advert);

    //查询接口信息
    $payport = M('Payport');
    $result_pay = $payport -> field('name,enname') -> where(array('status' => 1)) -> select();
    $this -> assign('result_pay', $result_pay);
    $this -> display();
  }

  //页面广告 - 余额支付
  public function advert_rmb_pay(){
    $AdvertOrder = M('AdvertOrder');
    //获取交易密码
    $pay_pwd = M('Member') -> getFieldByid($_SESSION[C('USER_AUTH_KEY')], 'traderspassword');
    //未设置交易密码的先去设置交易密码
    if(!$pay_pwd){
      R('Register/errorjump',array(L('TRADERSPASSWORD_EMPTY_ERROR'), '__ROOT__/member.php/index/setsafepwd'));
    }
    //交易密码错误
    if($pay_pwd != $_GET['pwd']){
      R('Register/errorjump',array(L('TRADERSPASSWORD_ERROR'), '__ROOT__/member.php/services/qqonline_pay/orderid/' . $_GET['orderid']));
    }

    //根据订单号查询应付总额
    $const = $AdvertOrder -> getFieldByordernum($_GET['orderid'], 'price');
    //扣费
    $rmb = D('MemberRmb');
    if(!$rmb -> lessrmb($const)){
      R('Register/errorjump',array(L('RMB_ERROR')));
    }

    //扣费成功更新订单状态
    if(!$AdvertOrder -> where(array('ordernum' => $this -> _get('orderid'))) -> save(array('status' => 3, 'paytype' => 'RMB余额'))){
      R('Register/errorjump',array(L('ORDER_UPDATE_ERROR')));
    }

    //写RMB消费记录
    $log_content = "您已成功购买 页面广告 服务,订单号{$_GET['orderid']}";
    if(!D('member://MemberRmbDetail') -> writelog($_SESSION[C('USER_AUTH_KEY')], $log_content, '消费', '-' . $const)){
      R('Register/errorjump',array(L('RMB_LOG_ERROR')));
    }

    //订单相关信息
    $advert_info = $AdvertOrder -> table('yesow_advert_order as ao') -> field('ao.id,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.ordernum' => $_GET['orderid'])) -> find();

    //写主表
    $Advert = M('Advert');
    $advert_data = array();
    $advert_data['mid'] = session(C('USER_AUTH_KEY'));
    $advert_data['adid'] = $advert_info['adid'];
    $advert_data['website'] = $advert_info['website'];
    $advert_data['filename'] = $advert_info['filename'];
    $advert_data['starttime'] = time();
    $advert_data['updatetime'] = time();
    $advert_data['endtime'] = $advert_data['starttime'] + ($advert_info['months'] * 30 * 24 * 60 * 60);
    if($Advert -> add($advert_data)){
      $info_succ = "您已成功购买页面广告相关服务";
      //更新会员余额和等级
      if(!$rmb -> rmbtotal()){
	R('Register/errorjump',array(L('RMB_CACHE')));
      }
      R('Services/advertsuccess',array($info_succ, 'success', $advert_info['adid']));
    }else{
      R('Register/errorjump',array(L('ADVERT_ERROR')));
    }
  }

  //页面广告 - 快钱支付
  public function advert_k99bill_pay(){
    $pageUrl = C('WEBSITE') . "member.php/pay/advert_k99billreturn";;
    $orderId = $this -> _get('oid');
    $rmb_amount = M('AdvertOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    $productName = '易搜会员中心页面广告购买';
    R('Public/k99bill_pay',array($pageUrl, $orderId, $rmb_amount, $productName));
  }

  //页面广告 - 支付宝支付
  public function advert_alipay_pay(){
    $notify_url = C('WEBSITE') . "member.php/pay/advert_alipaynotify";
    $return_url = C('WEBSITE') . "member.php/pay/advert_alipayreturn";
    $out_trade_no = $this -> _get('oid');
    $subject = '易搜会员中心页面广告购买';
    $price = M('AdvertOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    R('Public/alipay_pay',array($notify_url, $return_url, $out_trade_no, $subject, $price));
  }

  //页面广告 - 财付通支付
  public function advert_tenpay_pay(){
    $return_url = C('WEBSITE') . "member.php/pay/advert_tenpayreturn";
    $notify_url = C('WEBSITE') . "member.php/pay/advert_tenpaynotify";
    $out_trade_no = $this -> _get('oid');
    $desc = '易搜会员中心页面广告购买';
    $order_price = M('AdvertOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    R('Public/tenpay_pay',array($return_url, $notify_url, $out_trade_no, $desc, $order_price));
  }

  //页面广告管理
  public function editadvert(){
    $Advert = M('Advert');
    $where = array();
    $where['ad.mid'] = session(C('USER_AUTH_KEY'));
    import("ORG.Util.Page");// 导入分页类
    $count = $Advert -> table('yesow_advert as ad') -> where($where) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $Advert -> table('yesow_advert as ad') -> field('ad.id,adt.name as atdname,adp.remark as adpremark,cs.name as csname,ad.starttime,ad.endtime,ad.filename,ad.website') -> join('yesow_advertise as adt ON ad.adid = adt.id') -> join('yesow_advertise_page as adp ON adt.pid = adp.id') -> join('yesow_child_site as cs ON adp.csid = cs.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('ad.starttime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //编辑页面广告管理
  public function editeditadvert(){
    $Advert = M('Advert');

    if(!empty($_POST['id'])){
      //上传页面广告
      if($_POST['updatetype'] == 1){
      import('ORG.Net.UploadFile');
      $upload = new UpLoadFile();
      $upload -> savePath = C('ADVERT_PIC_PATH') ;//设置上传目录
      $upload -> autoSub = false;//设置使用子目录保存上传文件
      $upload -> saveRule = 'uniqid';
      $upload -> allowExts  = array('gif', 'jpg', 'jpeg');// 设置附件上传类型
      //$upload -> maxSize  = 409600 ;// 设置附件上传大小
      if($upload -> upload()){
	$info = $upload -> getUploadFileInfo();
      }else{
	R('Register/errorjump', array('上传页面广告图片失败，请检查文件合理性'));
      }
    }else if($_POST['updatetype'] == 2){
      import('ORG.Net.UploadFile');
      $upload = new UpLoadFile();
      $upload -> savePath = C('ADVERT_PIC_DATA_PATH') ;//设置上传目录
      $upload -> autoSub = false;//设置使用子目录保存上传文件
      $upload -> saveRule = 'uniqid';
      $upload -> allowExts  = array('rar', 'zip');// 设置附件上传类型
      //$upload -> maxSize  = 409600 ;// 设置附件上传大小
      if($upload -> upload()){
	$info = $upload -> getUploadFileInfo();
      }else{
	R('Register/errorjump', array('上传页面广告资料失败，请检查文件合理性'));
      }
    }

      $upload_data = array();
      if(!empty($_POST['updatetype'])){
	$upload_data['filename'] = $info[0]['savename'];
      }
      $upload_data['id'] = $_POST['id'];
      $upload_data['website'] = $_POST['website'];
      $upload_data['updatetime'] = time();
      if($Advert -> save($upload_data)){
	if(!empty($_POST['months'])){
	  R('Register/successjump',array(L('ADVERT_RENEW'), U('Services/advert_renew_pay') . '/oid/' . $this -> _post('months', 'intval') . '/aid/' . $this -> _post('id', 'intval')));
	}else{
	  R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Services/editadvert')));
	}
      }else{
	if(!empty($_POST['months'])){
	  R('Register/successjump',array(L('ADVERT_RENEW'), U('Services/advert_renew_pay') . '/oid/' . $this -> _post('months', 'intval') . '/aid/' . $this -> _post('id', 'intval')));
	}else{
	  R('Register/errorjump',array(L('DATA_UPDATE_ERROR')));
	}
      }
    }

    $result = $Advert -> table('yesow_advert as ad') -> field('ad.id,ad.adid,adt.name as atdname,adp.remark as adpremark,cs.name as csname,ad.starttime,ad.endtime,ad.filename,ad.website,adt.width,adt.height') -> join('yesow_advertise as adt ON ad.adid = adt.id') -> join('yesow_advertise_page as adp ON adt.pid = adp.id') -> join('yesow_child_site as cs ON adp.csid = cs.id') -> where(array('ad.id' => $this -> _get('id', 'intval'))) -> find();
    //查询价格
    $AdvertMoney = M('AdvertMoney');
    $advert_price = $AdvertMoney -> field('id,months,marketprice,promotionprice') -> where(array('adid' => $result['adid'])) -> order('months ASC') -> select();
    $this -> assign('advert_price', $advert_price);
    $this -> assign('result', $result);
    $this -> display();
  }

  //页面广告 - 续费 订单页
  public function advert_renew_pay(){
    $result_advert = array();

    if(empty($_GET['orderid'])){
      //月份价格
      $result_advert = M('AdvertMoney') -> field('months,promotionprice') -> find($this -> _get('oid'));
      //广告位id
      $result_advert['adid'] = M('Advert') -> getFieldByid($this -> _get('aid', 'intval'), 'adid');
      //网址
      $website = M('Advert') -> getFieldByid($this -> _get('aid', 'intval'), 'website');
      $result_advert['website'] = $website;
    }else{
      $AdvertOrder = M('AdvertOrder');
      $amid = $AdvertOrder -> getFieldByordernum($_GET['orderid'], 'amid');
      $result_advert = M('AdvertMoney') -> field('months,promotionprice') -> find($amid);
      //广告位id
      $result_advert['adid'] =  $AdvertOrder -> getFieldByordernum($_GET['orderid'], 'adid');
      $result_advert['website'] = $AdvertOrder -> getFieldByordernum($_GET['orderid'], 'website');
    }

    $filename = M('Advert') -> getFieldByid($this -> _get('aid', 'intval'), 'filename');
    $result_advert['filename'] = $filename;
    if(strstr($filename, '.') == '.rar' || strstr($filename, '.') == '.zip'){
      $result_advert['maketype'] = 2;
    }else{
      $result_advert['maketype'] = 1;
    }

    //生成订单号
    $result_advert['orderid'] = !empty($_GET['orderid']) ? $_GET['orderid'] : date('YmdHis') . mt_rand(100000,999999);
    //总价
    $result_advert['count'] = $result_advert['promotionprice'];
    
    $temp_name = M('Advertise') -> table('yesow_advertise as ad') -> field('ad.name as adname,adp.remark as adpremark,cs.name as csname') -> join('yesow_advertise_page as adp ON ad.pid = adp.id') -> join('yesow_child_site as cs ON adp.csid = cs.id') -> where(array('ad.id' => $result_advert['adid'])) -> find();
    $result_advert['advertname'] = $temp_name['csname'] . ' - ' . $temp_name['adpremark'] . ' - ' . $temp_name['adname'];
    
    if(empty($_GET['orderid'])){
      //生成订单
      $AdvertOrder = M('AdvertOrder');
      $data = array();
      $data['ordernum'] = $result_advert['orderid'];
      $data['mid'] = session(C('USER_AUTH_KEY'));
      $data['adid'] = $result_advert['adid'];
      $data['amid'] = $this -> _get('oid', 'intval');
      $data['price'] = $result_advert['count'];
      $data['maketype'] = $result_advert['maketype'];
      $data['filename'] = $result_advert['filename'];
      $data['website'] = $result_advert['website'];
      $data['addtime'] = time();
      $data['isrenew'] = 1;
      if(!$oid = $AdvertOrder -> add($data)){
	R('Register/errorjump',array(L('ORDER_ERROR')));
      }
    }
    
    //RMB余额是否足够支付
    $result_advert['rmb_enough'] = $_SESSION['rmb_total'] - $result_advert['count'] >= 0 ? 1 : 0;

    $this -> assign('result_advert', $result_advert);

    //查询接口信息
    $payport = M('Payport');
    $result_pay = $payport -> field('name,enname') -> where(array('status' => 1)) -> select();
    $this -> assign('result_pay', $result_pay);
    $this -> display();
  }

  //页面广告 - 续费 - 余额支付
  public function advert_renew_rmb_pay(){
    $AdvertOrder = M('AdvertOrder');
    //获取交易密码
    $pay_pwd = M('Member') -> getFieldByid($_SESSION[C('USER_AUTH_KEY')], 'traderspassword');
    //未设置交易密码的先去设置交易密码
    if(!$pay_pwd){
      R('Register/errorjump',array(L('TRADERSPASSWORD_EMPTY_ERROR'), '__ROOT__/member.php/index/setsafepwd'));
    }
    //交易密码错误
    if($pay_pwd != $_GET['pwd']){
      R('Register/errorjump',array(L('TRADERSPASSWORD_ERROR'), '__ROOT__/member.php/services/qqonline_pay/orderid/' . $_GET['orderid']));
    }

    //根据订单号查询应付总额
    $const = $AdvertOrder -> getFieldByordernum($_GET['orderid'], 'price');
    //扣费
    $rmb = D('MemberRmb');
    if(!$rmb -> lessrmb($const)){
      R('Register/errorjump',array(L('RMB_ERROR')));
    }

    //扣费成功更新订单状态
    if(!$AdvertOrder -> where(array('ordernum' => $this -> _get('orderid'))) -> save(array('status' => 3, 'paytype' => 'RMB余额'))){
      R('Register/errorjump',array(L('ORDER_UPDATE_ERROR')));
    }

    //写RMB消费记录
    $log_content = "您已成功续费 页面广告 服务,订单号{$_GET['orderid']}";
    if(!D('member://MemberRmbDetail') -> writelog($_SESSION[C('USER_AUTH_KEY')], $log_content, '消费', '-' . $const)){
      R('Register/errorjump',array(L('RMB_LOG_ERROR')));
    }

    //订单相关信息
    $advert_info = $AdvertOrder -> table('yesow_advert_order as ao') -> field('ao.id,ao.mid,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.ordernum' => $_GET['orderid'])) -> find();
    //根据adid查主表的id
    $where_id = array();
    $where_id['mid'] = $advert_info['mid'];
    $where_id['adid'] = $advert_info['adid'];
    $where_id['starttime'] = array('ELT', time());
    $where_id['endtime'] = array('EGT', time());
    $id = M('Advert') -> where($where_id) -> getField('id');

    //更新主表
    $Advert = M('Advert');
    $where_up = array();
    $where_up['id'] = $id;

    if($Advert -> where($where_up) -> setInc('endtime', $advert_info['months'] * 30 * 24 * 60 * 60)){
      $info_succ = "您已成功续费页面广告相关服务";
      //更新会员余额和等级
      if(!$rmb -> rmbtotal()){
	R('Register/errorjump',array(L('RMB_CACHE')));
      }
      R('Services/advertsuccess',array($info_succ, 'success', $advert_info['adid']));
    }else{
      R('Register/errorjump',array(L('ADVERT_ERROR')));
    }
  }

  //页面广告 - 续费 - 快钱支付
  public function advert_renew_k99bill_pay(){
    $pageUrl = C('WEBSITE') . "member.php/pay/advert_renew_k99billreturn";;
    $orderId = $this -> _get('oid');
    $rmb_amount = M('AdvertOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    $productName = '易搜会员中心页面广告续费';
    R('Public/k99bill_pay',array($pageUrl, $orderId, $rmb_amount, $productName));
  }

  //页面广告 - 续费 - 支付宝支付
  public function advert_renew_alipay_pay(){
    $notify_url = C('WEBSITE') . "member.php/pay/advert_renew_alipaynotify";
    $return_url = C('WEBSITE') . "member.php/pay/advert_renew_alipayreturn";
    $out_trade_no = $this -> _get('oid');
    $subject = '易搜会员中心页面广告续费';
    $price = M('AdvertOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    R('Public/alipay_pay',array($notify_url, $return_url, $out_trade_no, $subject, $price));
  }

  //页面广告 - 续费 - 财付通支付
  public function advert_renew_tenpay_pay(){
    $return_url = C('WEBSITE') . "member.php/pay/advert_renew_tenpayreturn";
    $notify_url = C('WEBSITE') . "member.php/pay/advert_renew_tenpaynotify";
    $out_trade_no = $this -> _get('oid');
    $desc = '易搜会员中心页面广告续费';
    $order_price = M('AdvertOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    R('Public/tenpay_pay',array($return_url, $notify_url, $out_trade_no, $desc, $order_price));
  }

  //页面广告购买成功
  public function advertsuccess($title, $status, $adid){
    $this -> assign('status', $status);
    $this -> assign('title', $title);
    $page = M('Advertise') -> table('yesow_advertise as ad') -> field('adp.module_name,adp.action_name') -> join('yesow_advertise_page as adp ON ad.pid = adp.id') -> where(array('ad.id' => $adid)) -> find();
    $this -> assign('page', $page);
    $this -> display('services:advertsuccess');
    exit();
  }
  /* ------  页面广告 ------- */

  /* ------  速查排名 ------- */
  //速查排名管理
  public function _before_searchrank(){
    $this -> _before_index();
  }
  public function searchrank(){
    $this -> display();
  }

  //添加速查排名
  public function addsearchrank(){
    //传递cid
    if(!empty($_GET['cid'])){
      $cid = $this -> _get('cid', 'intval');

       //公司信息
       $company_info = M('Company') -> table('yesow_company as c') -> field('c.name,c.address,c.linkman,cs.name as csname,csa.name as csaname') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> where(array('c.id' => $cid)) -> find();
       $this -> assign('company_info', $company_info);
    }
    //后台搜索公司
    if(!empty($_REQUEST['keyword'])){
      $where_company['name'] = array('LIKE', '%' . $_REQUEST['keyword'] . '%');
      import("ORG.Util.Page");// 导入分页类
      $count = M('Company') -> where($where_company) -> count();
      $page = new Page($count, 9);
      $page -> parameter = "f_keyword=" . $_REQUEST['f_keyword'] . "&f_fid=" . $_REQUEST['f_fid'] . "&keyword=" . $_REQUEST['keyword'];
      $show = $page -> show();
      $company_search = M('Company') -> field('id,name,manproducts,address,website,linkman') -> where($where_company) -> order('updatetime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
      $this -> assign('show', $show);
      $this -> assign('company_search', $company_search);
    }
    //热门搜索词
    $SearchHot = M('SearchHot');
    $result_search_hot = $SearchHot -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_search_hot', $result_search_hot);
    //站点类别
    $SearchRankWebsiteType = M('SearchRankWebsiteType');
    $result_website_type = $SearchRankWebsiteType -> field('id,name') -> select();
    $this -> assign('result_website_type', $result_website_type);
    $this -> display();
  }

  //等待排名页 
  public function waitrank(){
    $SearchRank = M('SearchRank');
    $where = array();
    $where['r.fid'] = $this -> _get('fid', 'intval');
    $where['r.keyword'] = $this -> _get('keyword');
    $where['r.rank'] = $this -> _get('rank', 'intval');
    $where['r.endtime'] = array('EGT', time());
    $result = $SearchRank -> alias('r') -> field('c.name as cname,r.endtime-UNIX_TIMESTAMP() as retime') -> join('yesow_company as c ON r.cid = c.id') -> where($where) -> order('r.starttime ASC') -> select();
    $this -> assign('result', $result);
    $this -> display();
  }

  //速查排名订单页
  public function searchrank_pay(){
    $result_searchrank = array();

    if(empty($_GET['orderid'])){
      //月份价格
      $result_searchrank = M('SearchRankMonthsMoney') -> field('months,promotionprice') -> find($this -> _post('months'));      
      //公司id
      $result_searchrank['cid'] = $this -> _post('cid', 'intval');
      //站点类别id
      $result_searchrank['fid'] = $this -> _post('fid', 'intval');
      //排名位置
      $result_searchrank['rank'] = $this -> _post('rank', 'intval');
      //关键词
      $result_searchrank['keyword'] = $this -> _post('keyword');
    }else{
      $order_num_get = $this -> _get('orderid');
      $SearchRankOrder = M('SearchRankOrder');
      //srmid
      $srmid = $SearchRankOrder -> getFieldByordernum($order_num_get, 'srmid');
      //月份价格
      $result_searchrank = M('SearchRankMonthsMoney') -> field('months,promotionprice') -> find($srmid);
      //公司id
      $result_searchrank['cid'] = $SearchRankOrder -> getFieldByordernum($order_num_get, 'cid');
      //站点类别id
      $result_searchrank['fid'] = $SearchRankOrder -> getFieldByordernum($order_num_get, 'fid');
      //排名位置
      $result_searchrank['rank'] = $SearchRankOrder -> getFieldByordernum($order_num_get, 'rank');
      //关键词
      $result_searchrank['keyword'] = $SearchRankOrder -> getFieldByordernum($order_num_get, 'keyword');
    }

    //折扣率
    $where_discount = array();
    $where_discount['fid'] = $result_searchrank['fid'];
    $where_discount['rank'] = array('EGT', $result_searchrank['rank']);
    $result_searchrank['discount'] = M('SearchRankMoney') -> where($where_discount) -> order('rank ASC') -> getField('discount');

    //生成订单号
    $result_searchrank['orderid'] = !empty($_GET['orderid']) ? $_GET['orderid'] : date('YmdHis') . mt_rand(100000,999999);
    //总价
    $result_searchrank['count'] = $result_searchrank['promotionprice'] * (1-$result_searchrank['discount']);
    //公司名称
    $result_searchrank['companyname'] = M('Company') -> getFieldByid($result_searchrank['cid'], 'name');
    //站点类型名称
    $result_searchrank['fname'] = M('SearchRankWebsiteType') -> getFieldByid($result_searchrank['fid'], 'name');

    if(empty($_GET['orderid'])){
      //生成订单
      $SearchRankOrder = M('SearchRankOrder');
      $data = array();
      $data['ordernum'] = $result_searchrank['orderid'];
      $data['cid'] = $result_searchrank['cid'];
      $data['fid'] = $this -> _post('fid', 'intval');
      $data['mid'] = session(C('USER_AUTH_KEY'));
      $data['srmid'] = $this -> _post('months', 'intval');
      $data['rank'] = $result_searchrank['rank'];
      $data['keyword'] = $result_searchrank['keyword'];
      $data['price'] = $result_searchrank['count'];
      $data['addtime'] = time();
      if(!$SearchRankOrder -> add($data)){
	R('Register/errorjump',array(L('ORDER_ERROR')));
      }
    }

    //RMB余额是否足够支付
    $result_searchrank['rmb_enough'] = $_SESSION['rmb_total'] - $result_searchrank['count'] >= 0 ? 1 : 0;
    $this -> assign('result_searchrank', $result_searchrank);
    
    //查询接口信息
    $payport = M('Payport');
    $result_pay = $payport -> field('name,enname') -> where(array('status' => 1)) -> select();
    $this -> assign('result_pay', $result_pay);
    $this -> display();

  }

  //速查排名RMB支付页
  public function searchrank_rmb_pay(){
    $SearchRankOrder = M('SearchRankOrder');
    //获取交易密码
    $pay_pwd = M('Member') -> getFieldByid($_SESSION[C('USER_AUTH_KEY')], 'traderspassword');
    //未设置交易密码的先去设置交易密码
    if(!$pay_pwd){
      R('Register/errorjump',array(L('TRADERSPASSWORD_EMPTY_ERROR'), '__ROOT__/member.php/index/setsafepwd'));
    }
    //交易密码错误
    if($pay_pwd != $_GET['pwd']){
      R('Register/errorjump',array(L('TRADERSPASSWORD_ERROR'), '__ROOT__/member.php/services/qqonline_pay/orderid/' . $_GET['orderid']));
    }

    //根据订单号查询应付总额
    $const = $SearchRankOrder -> getFieldByordernum($_GET['orderid'], 'price');
    //扣费
    $rmb = D('MemberRmb');
    if(!$rmb -> lessrmb($const)){
      R('Register/errorjump',array(L('RMB_ERROR')));
    }

    //扣费成功更新订单状态
    if(!$SearchRankOrder -> where(array('ordernum' => $this -> _get('orderid'))) -> save(array('status' => 3, 'paytype' => 'RMB余额'))){
      R('Register/errorjump',array(L('ORDER_UPDATE_ERROR')));
    }

    //写RMB消费记录
    $log_content = "您已成功购买 速查排名 服务,订单号{$_GET['orderid']}";
    if(!D('member://MemberRmbDetail') -> writelog($_SESSION[C('USER_AUTH_KEY')], $log_content, '消费', '-' . $const)){
      R('Register/errorjump',array(L('RMB_LOG_ERROR')));
    }

    //订单相关信息
    $searchrank_info = $SearchRankOrder -> table('yesow_search_rank_order as sro') -> field('sro.cid,sro.fid,sro.mid,sro.keyword,sro.rank,srm.months') -> join('yesow_search_rank_months_money as srm ON sro.srmid = srm.id') -> where(array('sro.ordernum' => $this -> _get('orderid'))) -> find();

    //开始时间
    $SearchRank = M('SearchRank');
    $where_starttime = array();
    $where_starttime['keyword'] = $searchrank_info['keyword'];
    $where_starttime['fid'] = $searchrank_info['fid'];
    $where_starttime['rank'] = $searchrank_info['rank'];
    $where_starttime['endtime'] = array('EGT', time());
    $endtime = $SearchRank -> where($where_starttime) -> order('endtime DESC') -> getField('endtime');

    //写主表    
    $searchrank_data = array();
    $searchrank_data['cid'] = $searchrank_info['cid'];
    $searchrank_data['mid'] = session(C('USER_AUTH_KEY'));
    $searchrank_data['fid'] = $searchrank_info['fid'];
    $searchrank_data['keyword'] = $searchrank_info['keyword'];
    $searchrank_data['rank'] = $searchrank_info['rank'];
    $searchrank_data['starttime'] = $endtime ? $endtime + 1 : time();
    $searchrank_data['endtime'] = $searchrank_data['starttime'] + ($searchrank_info['months'] * 30 * 24 * 60 * 60);
    if($SearchRank -> add($searchrank_data)){
      $info_succ = "您已成功购买速查排名相关服务";
      //更新会员余额和等级
      if(!$rmb -> rmbtotal()){
	R('Register/errorjump',array(L('RMB_CACHE')));
      }
      R('Register/successjump',array($info_succ, U('Services/searchrank')));
    }else{
      R('Register/errorjump',array(L('SEARCHRANK_ERROR')));
    }

  }

  //速查排名 - 块钱支付
  public function searchrank_k99bill_pay(){
    $pageUrl = C('WEBSITE') . "member.php/pay/searchrank_k99billreturn";;
    $orderId = $this -> _get('oid');
    $rmb_amount = M('SearchRankOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    $productName = '易搜会员中心速查排名购买';
    R('Public/k99bill_pay',array($pageUrl, $orderId, $rmb_amount, $productName));
  }

  //速查排名 - 支付宝支付
  public function searchrank_alipay_pay(){
    $notify_url = C('WEBSITE') . "member.php/pay/searchrank_alipaynotify";
    $return_url = C('WEBSITE') . "member.php/pay/searchrank_alipayreturn";
    $out_trade_no = $this -> _get('oid');
    $subject = '易搜会员中心速查排名购买';
    $price = M('SearchRankOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    R('Public/alipay_pay',array($notify_url, $return_url, $out_trade_no, $subject, $price));
  }

  //速查排名 - 财富通支付
  public function searchrank_tenpay_pay(){
    $return_url = C('WEBSITE') . "member.php/pay/searchrank_tenpayreturn";
    $notify_url = C('WEBSITE') . "member.php/pay/searchrank_tenpaynotify";
    $out_trade_no = $this -> _get('oid');
    $desc = '易搜会员中心速查排名购买';
    $order_price = M('SearchRankOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    R('Public/tenpay_pay',array($return_url, $notify_url, $out_trade_no, $desc, $order_price));
  }

  //速查排名订单页
  public function searchrankorder(){
    $SearchRankOrder = M('SearchRankOrder');
    $where = array();
    $where['sro.mid'] = $_SESSION[C('USER_AUTH_KEY')];
    import("ORG.Util.Page");// 导入分页类
    $count = $SearchRankOrder -> table('yesow_search_rank_order as sro') -> where($where) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();

    $result = $SearchRankOrder -> table('yesow_search_rank_order as sro') -> field('sro.id,sro.ordernum,c.name as cname,sro.keyword,srwt.name as fname,sro.rank,srmm.months,sro.price,sro.status,sro.ischeck,sro.paytype,sro.addtime') -> join('yesow_company as c ON sro.cid = c.id') -> join('yesow_search_rank_website_type as srwt ON sro.fid = srwt.id') -> join('yesow_search_rank_months_money as srmm ON sro.srmid = srmm.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sro.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //速查排名订单详情页
  public function searchrankorderlist(){
    $oid = $this -> _get('id', 'intval');
    $SearchRankOrder = M('SearchRankOrder');
    $result_order = $SearchRankOrder -> table('yesow_search_rank_order as sro') -> field('sro.id,sro.ordernum,c.name as cname,sro.keyword,srwt.name as fname,sro.rank,srmm.months,sro.price,sro.status,sro.ischeck,sro.paytype,sro.addtime') -> join('yesow_company as c ON sro.cid = c.id') -> join('yesow_search_rank_website_type as srwt ON sro.fid = srwt.id') -> join('yesow_search_rank_months_money as srmm ON sro.srmid = srmm.id') -> where(array('sro.id' => $this -> _get('id', 'intval'))) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sro.addtime DESC') -> find();
    $this -> assign('result_order', $result_order);
    $this -> display();
  }

  //速查排名管理
  public function editsearchrank(){
    $SearchRank = M('SearchRank');
    import("ORG.Util.Page");// 导入分页类
    $count = $SearchRank -> table('yesow_search_rank as sr')  -> where(array('sr.mid' => session(C('USER_AUTH_KEY')))) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $SearchRank -> table('yesow_search_rank as sr') -> field('sr.id,sr.cid,c.name as cname,sr.keyword,srwt.name as fname,sr.rank,sr.starttime,sr.endtime') -> join('yesow_company as c ON sr.cid = c.id') -> join('yesow_search_rank_website_type as srwt ON sr.fid = srwt.id') -> where(array('sr.mid' => session(C('USER_AUTH_KEY')))) -> order('sr.starttime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //编辑速查排名管理
  public function editeditsearchrank(){
    $SearchRank = M('SearchRank');
    $result = $SearchRank -> table('yesow_search_rank as sr') -> field('sr.id,sr.cid,c.name as cname,sr.keyword,srwt.name as fname,sr.fid,sr.rank,sr.starttime,sr.endtime') -> join('yesow_company as c ON sr.cid = c.id') -> join('yesow_search_rank_website_type as srwt ON sr.fid = srwt.id') -> where(array('sr.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);

    //查询当前关键词后面有无排队情况
    $where_wait = array();
    $where_wait['r.fid'] = $result['fid'];
    $where_wait['r.keyword'] = $result['keyword'];
    $where_wait['r.rank'] = $result['rank'];
    $where_wait['r.starttime'] = array('GT', $result['endtime']);
    $result_wait = $SearchRank -> alias('r') -> field('c.name as cname,r.starttime,r.endtime') -> join('yesow_company as c ON r.cid = c.id') -> where($where_wait) -> order('r.starttime ASC') -> select();
    $this -> assign('result_wait', $result_wait);

    //查询当前关键词包月价格
    ////折扣率
    $SearchRankMoney = M('SearchRankMoney');
    $where_discount = array();
    $where_discount['fid'] = $result['fid'];
    $where_discount['rank'] = array('EGT', $result['rank']);
    $discount = $SearchRankMoney -> where($where_discount) -> order('rank ASC') -> getField('discount');
    ////包月信息
    $SearchRankMonthsMoney = M('SearchRankMonthsMoney');
    $searchrank_money = $SearchRankMonthsMoney -> field('id,months,ROUND(marketprice*' . (1-$discount) . ',1) as marketprice,ROUND(promotionprice*' . (1-$discount) . ',1) as promotionprice') -> where(array('fid' => $result['fid'])) -> order('months ASC') -> select();
    $this -> assign('searchrank_money', $searchrank_money);
    $this -> display();
  }

  /* ------  速查排名 ------- */
}

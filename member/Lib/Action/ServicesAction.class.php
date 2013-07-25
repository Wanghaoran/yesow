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
      $where_company['name'] = array('LIKE', '%' . $_POST['keyword'] . '%');
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
}

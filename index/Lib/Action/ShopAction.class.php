<?php
class ShopAction extends CommonAction {

  //商城购物首页
  public function index(){
    $shop = M('Shop');
    $where = array();
    if(!empty($_GET['cid'])){
      $where['cid_one'] = $this -> _get('cid', 'intval');
    }
    //读取分站名称
    $child_name = D('admin://ChildSite') -> getname();
    $this -> assign('child_name', $child_name);

    import("ORG.Util.Page");// 导入分页类
    $count = $shop -> where($where) -> count('id');
    $page = new Page($count, 10);
    $show = $page -> show();
    //查分类下的商品信息，10条
    $result = $shop -> field('id,title,issend,marketprice,promotionprice,small_pic,remark') -> where($where) -> order('updatetime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    //查20个最新商品
    $result_new_shop = $shop -> field('id,title') -> order('addtime DESC') -> limit(20) -> select();
    $this -> assign('result_new_shop', $result_new_shop);

    $this -> display();
  
  }

  //商品详情页
  public function info(){
    $shop = M('Shop');
    //点击量加1
    $shop -> where(array('id' => $this -> _get('id', 'intval'))) -> setInc('clickcount');
    //读取分站名称
    $child_name = D('admin://ChildSite') -> getname();
    $this -> assign('child_name', $child_name);
    //商品详细信息
    $result = $shop -> field('id,cid_one,issend,marketprice,promotionprice,big_pic,remark,title,content,keyword,clickcount') -> find($this -> _get('id', 'intval'));
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

  //购物车
  public function shopcart(){
    $shop_cart = D('ShopCart');
    //查询当前用户的订单
    $result = $shop_cart -> usercart();
    //判断购物车中的商品是否全部都是免运费的
    $nosendprice = 0;
    foreach($result as $value){
      $nosendprice += $value['issend'];
    }
    $this -> assign('nosendprice', $nosendprice);
    $this -> assign('result', $result);
    //查询用户相关信息
    $info = M('Member') -> field('fullname,address,zipcode,tel,email') -> find(session(C('USER_AUTH_KEY')));
    $this -> assign('info', $info);
    //查询快递方式
    $send = M('SendType') -> field('id,name,money') -> order('sort ASC') -> select();
    $this -> assign('send', $send);

    $this -> display();
  }

  //向购物车添加商品
  public function addshopcart(){
    $shop_cart = D('ShopCart');
    if($shop_cart -> addshop($this -> _get('sid', 'intval'), $this -> _get('num', 'intval'))){
      echo 1;
    }else{
      echo 0;
    }
  }

  //向购物车删除商品
  public function delshopcart(){
    $shop_cart = D('ShopCart');
    if($shop_cart -> delshop($_GET['ids'])){
      echo 1;
    }else{
      echo 0;
    }
  }

  //更新购物车中商品数量
  public function editshopcare(){
    $shop_cart = D('ShopCart');
    if($shop_cart -> editshop($this -> _get('id', 'intval'), $this -> _get('shopnum', 'intval'))){
      echo 1;
    }else{
      echo 0;
    }
  }

  //生成订单支付页
  public function orderpay(){
    $shopcart = D('ShopCart');
    $order = D('ShopOrder');
    $shop_order = D('ShopOrderShop');
    //生成或获取订单号
    $orderid = !empty($_GET['oid']) ? $this -> _get('oid') : date('YmdHis') . mt_rand(100000,999999);

    //如果是新增订单，则进行生成订单操作
    if(empty($_GET['oid'])){
      //查询购物车中的购物总额
      $totalmoney = $shopcart -> totalpaymoney();
      //加上物流费用
      $totalmoney += M('SendType') -> getFieldByid($this -> _post('sendid', 'intval'), 'money');   
      //如果需要发票，则还需要加上发票费用
      if($_POST['isbull'] == 1){
	$ratio = D('ShopInvoice') -> getradio($totalmoney);
	//如果低于最低金额，则取最低金额所计算出来的值
	if(!$ratio){
	  $totalmoney += D('ShopInvoice') -> getlowest();
	}else{
	  $totalmoney += $totalmoney * $ratio;
	}
      }
      //生成订单    
      if(!$order -> create()){
	R('Public/errorjump',array($order -> getError()));
      }
      $order -> ordernum = $orderid;
      $order -> mid = session(C('USER_AUTH_KEY'));
      $order -> paytotal = $totalmoney;
      if(!$order -> add()){
	R('Public/errorjump',array(L('SHOP_ORDER_CREATE_ERROR')));
      }
      //记录购买商品的标题、数量、单价
      $shopcart -> writeordershop($orderid);
    }
   
    //订单号
    $this -> assign('ordernum', $orderid);
    
    //查询此订单号下的商品
    $shop_result = $shop_order -> shopbyordernum($orderid);
    $this -> assign('shop_result', $shop_result);
    //查询快递费用
    $temp_send_price = $order -> table('yesow_shop_order as so') -> field('st.money') -> join('yesow_send_type as st ON so.sendid = st.id') -> where(array('so.ordernum' => $orderid)) -> find();
    $this -> assign('send_price', $temp_send_price['money']);
    //查询商品总价
    $shop_price = $shop_order -> totalpaybyordernum($orderid);
    $this -> assign('shop_price', $shop_price);
    //查询应付总额
    $total_price = $order -> getFieldByordernum($orderid, 'paytotal');
    $this -> assign('total_price', $total_price);
    //计算发票税率费用
    $invoice_price = $total_price - $shop_price - $temp_send_price['money'];
    $this -> assign('invoice_price', $invoice_price);
    //查询接口信息
    $payport = M('Payport');
    $result_pay = $payport -> field('name,enname') -> where(array('status' => 1)) -> select();
    $this -> assign('result_pay', $result_pay);
    $this -> display();
  }

  //人民币余额支付
  public function shop_rmb_pay(){
    //清空购物车
    D('ShopCart') -> delshop('all');
    //获取交易密码
    $pay_pwd = M('Member') -> getFieldByid(session(C('USER_AUTH_KEY')), 'traderspassword');
    //未设置交易密码的先去设置交易密码
    if(!$pay_pwd){
      R('Register/errorjump',array(L('TRADERSPASSWORD_EMPTY_ERROR')));
    }
    //交易密码错误
    if($pay_pwd != $_GET['pwd']){
      R('Register/errorjump',array(L('TRADERSPASSWORD_ERROR')));
    }
    //根据订单号查询总价格
    $const = M('ShopOrder') -> getFieldbyordernum($_GET['orderid'], 'paytotal');
    //扣费
    $rmb = D('member://MemberRmb');
    if(!$rmb -> lessrmb($const)){
      R('Register/errorjump',array(L('RMB_ERROR')));
    }
    $shop_order = M('ShopOrder');
    //扣费成功更新订单状态
    if(!$shop_order -> where(array('ordernum' => $this -> _get('orderid'))) -> save(array('paystatus' => 3, 'paytype' => 'RMB余额'))){
      R('Register/errorjump',array(L('ORDER_UPDATE_ERROR')));
    }
    //写RMB消费记录
    $log_content = "您已成功在易搜商城购买相关产品,订单号{$_GET['orderid']}";
    if(!D('member://MemberRmbDetail') -> writelog($_SESSION[C('USER_AUTH_KEY')], $log_content, '消费', '-' . $const)){
      R('Register/errorjump',array(L('RMB_LOG_ERROR')));
    }
    //更新会员余额和等级
    if(!$rmb -> rmbtotal()){
      R('Register/errorjump',array(L('RMB_CACHE')));
    }else{
      $this -> assign('img_info', 'gwc_success');
      $this -> display('./index/Tpl/Shop/returnurl.html'); 
    }
  }

  //快钱支付
  public function shop_k99bill_pay(){
    //清空购物车
    D('ShopCart') -> delshop('all');
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
    $pageUrl = C('WEBSITE') . "index.php/pay/shop_k99billreturn";
    //网关版本.固定值
    $version = "v2.0";
    //语言种类.固定选择值。
    $language = "1";
    //签名类型.固定值
    $signType = "1";
    //商户订单号
    $orderId = $this -> _get('oid');
    //订单金额
    $rmb_amount = M('ShopOrder') -> getFieldByordernum($this -> _get('oid'), 'paytotal');
    $orderAmount = $rmb_amount * 100;
    //订单提交时间
    $orderTime = date('YmdHis');
    //商品名称
    $productName = "易搜商城";
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
        <p><img src="' . __ROOT__ .'/Public/index/style/images/user/loading.gif" width="25" height="25" border="0" />正在跳转至支付页面...</p>
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

  //商城 - 财付通支付
  public function shop_tenpay_pay(){
    //清空购物车
    D('ShopCart') -> delshop('all');
    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1') -> where(array('enname' => 'tenpay')) -> find();
    Vendor('tenpay.RequestHandler','','.class.php');

    $partner = $author['account'];  //财付通商户号
    $key = $author['key1'];  //财付通密钥
    $return_url = C('WEBSITE') . "index.php/pay/shop_tenpayreturn";	//同步返回地址
    $notify_url = C('WEBSITE') . "index.php/pay/shop_tenpaynotify";  //异步通知地址
    $out_trade_no = $this -> _get('oid'); //订单号
    $desc = '易搜商城';  //商品名称
    $order_price = M('ShopOrder') -> getFieldByordernum($this -> _get('oid'), 'paytotal');  //商品价格,根据订单号查寻
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
        <p><img src="' . __ROOT__ .'/Public/index/style/images/user/loading.gif" width="25" height="25" border="0" />正在跳转至支付页面...</p>
	</div>';
    $sHtml .= "<script>location.href='{$reqUrl}'</script>";
    $sHtml .= "</body></html>";

    echo $sHtml;
  }

  //商城 - 支付宝支付
  public function shop_alipay_pay(){
    //清空购物车
    D('ShopCart') -> delshop('all');
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
    $notify_url = C('WEBSITE') . "index.php/pay/shop_alipaynotify";
    //页面跳转同步通知页面路径,需http://格式的完整路径，不能加?id=123这类自定义参
    $return_url = C('WEBSITE') . "index.php/pay/shop_alipayreturn";
    //卖家支付宝帐户
    $seller_email = $author['account'];
    //商户订单号
    $out_trade_no = $this -> _get('oid');
    //订单名称
    $subject = '易搜商城';
    //付款金额，根据订单号查询出来
    $price = M('ShopOrder') -> getFieldByordernum($this -> _get('oid'), 'paytotal');
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
}

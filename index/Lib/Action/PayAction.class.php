<?php
class PayAction extends Action {
    /* -------------------------- 商 城 ------------------------ */

  //商城 - 支付宝异步通知页面
  public function shop_alipaynotify(){
    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1,key2') -> where(array('enname' => 'alipay')) -> find();
    Vendor('alipay.alipay_notify','','.class.php');
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
    $alipay_config['cacert'] = __ROOT__ . '/Public/cacert.pem';
    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    $alipay_config['transport'] = 'http';

    //计算得出通知验证结果
    $alipayNotify = new AlipayNotify($alipay_config);
    $verify_result = $alipayNotify -> verifyNotify();

    //如果验证成功
    if($verify_result){
      //商户订单号
      $out_trade_no = $_POST['out_trade_no'];

      $shop_order = M('ShopOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['paystatus'] = 0;
	$shop_order -> where($where) -> save($data);
	echo "success";
      
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//更新订单状态
	$data['paystatus'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功
	if($shop_order -> where($where) -> save($data)){
	  echo "success";
	}
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['paystatus'] = 2;
	$shop_order -> where($where) -> save($data);
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['paystatus'] = 3;
	$shop_order -> where($where) -> save($data);
	echo "success";
      }
    }else{
      echo "fail";
    }
  }

  //商城 - 支付宝同步通知页面
  public function shop_alipayreturn(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);

    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1,key2') -> where(array('enname' => 'alipay')) -> find();
    Vendor('alipay.alipay_notify','','.class.php');
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
    $alipay_config['cacert'] = __ROOT__ . '/Public/cacert.pem';
    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    $alipay_config['transport'] = 'http';

    //删除多余数组，避免验证错误
    unset($_GET['_URL_']);

    //计算得出通知验证结果
    $alipayNotify = new AlipayNotify($alipay_config);
    $verify_result = $alipayNotify->verifyReturn();
    //验证成功
    if($verify_result){
      //支付成功
      if($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//充值成功的图片
	$this -> assign('img_info', 'gwc_success');
      }else{
	$this -> assign('img_info', 'gwc_fail');
      }
    }else{
      $this -> assign('img_info', 'gwc_fail');
    }
    $this -> display('./index/Tpl/Shop/returnurl.html');
  }

  //商城 - 财付通异步页面
  public function shop_tenpaynotify(){
    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1') -> where(array('enname' => 'tenpay')) -> find();
    $partner = $author['account'];  //财付通商户号
    $key = $author['key1'];  //财付通密钥

    //删除多余数组，避免验证错误
    unset($_GET['_URL_']);

    Vendor('tenpay.ResponseHandler','','.class.php');
    Vendor('tenpay.RequestHandler','','.class.php');
    Vendor('tenpay.client.TenpayHttpClient','','.class.php');
    Vendor('tenpay.client.ClientResponseHandler','','.class.php');
    /* 创建支付应答对象 */
    $resHandler = new ResponseHandler();
    $resHandler->setKey($key);

    //判断签名
    if($resHandler->isTenpaySign()){
      //通知id
      $notify_id = $resHandler->getParameter("notify_id");
      //通过通知ID查询，确保通知来至财付通
      //创建查询请求
      $queryReq = new RequestHandler();
      $queryReq->init();
      $queryReq->setKey($key);
      $queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
      $queryReq->setParameter("partner", $partner);
      $queryReq->setParameter("notify_id", $notify_id);
      //通信对象
      $httpClient = new TenpayHttpClient();
      $httpClient->setTimeOut(5);
      //设置请求内容
      $httpClient->setReqContent($queryReq->getRequestURL());

      //后台调用
      if($httpClient->call()){

	//设置结果参数
	$queryRes = new ClientResponseHandler();
	$queryRes->setContent($httpClient->getResContent());
	$queryRes->setKey($key);

	if($resHandler->getParameter("trade_mode") == "1"){
	  //判断签名及结果（即时到帐）
	  //只有签名正确,retcode为0，trade_state为0才是支付成功
	  if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0"){
	    //取得订单号
	    $out_trade_no = $resHandler->getParameter("out_trade_no");
	    //商家业务逻辑
	    $shop_order = M('ShopOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['paystatus'] = 3;
	    $data['paytype'] = '财付通';
	    if($shop_order -> where($where) -> save($data)){
	      echo "success";
	    }else{
	      echo "fail";
	    }
	  }else{
	    echo "fail"; 
	  }
	}else{
	echo "fail";
	}
      }else{
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }

  //财付通同步返回页面
  public function shop_tenpayreturn(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);

    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1') -> where(array('enname' => 'tenpay')) -> find();
    $partner = $author['account'];  //财付通商户号
    $key = $author['key1'];  //财付通密钥
    //删除多余数组，避免验证错误
    unset($_GET['_URL_']);
    Vendor('tenpay.ResponseHandler','','.class.php');
    /* 创建支付应答对象 */
    $resHandler = new ResponseHandler();
    $resHandler->setKey($key);
    //判断签名
    if($resHandler->isTenpaySign()){
	//支付结果
	$trade_state = $resHandler->getParameter("trade_state");
	//交易模式,1即时到账
	$trade_mode = $resHandler->getParameter("trade_mode");
	//金额,以分为单位
	$total_fee = $resHandler->getParameter("total_fee");
	if("1" == $trade_mode ) {
	  if( "0" == $trade_state){
	    $this -> assign('img_info', 'gwc_success');
	  }else{
	    $this -> assign('img_info', 'gwc_fail');
	  }	    
	}else{
	  //当做不成功处理
	  $this -> assign('img_info', 'gwc_fail');
	}
    }else{
      $this -> assign('img_info', 'gwc_fail');
    }
    $this -> display('./index/Tpl/Shop/returnurl.html');
  }

  //快钱同步返回页面
  public function shop_k99billreturn(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);

    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1') -> where(array('enname' => 'k99bill')) -> find();
    //获取人民币网关账户号
    $merchantAcctId=trim($_REQUEST['merchantAcctId']);
    //设置人民币网关密钥
    $key=$author['key1'];
    //获取网关版本.固定值
    $version=trim($_REQUEST['version']);
    //获取语言种类.固定选择值。
    $language=trim($_REQUEST['language']);
    //签名类型.固定值
    $signType=trim($_REQUEST['signType']);
    //获取支付方式
    $payType=trim($_REQUEST['payType']);
    //获取银行代码
    $bankId=trim($_REQUEST['bankId']);
    //获取商户订单号
    $orderId=trim($_REQUEST['orderId']);
    //获取订单提交时间
    $orderTime=trim($_REQUEST['orderTime']);
    //获取原始订单金额
    $orderAmount=trim($_REQUEST['orderAmount']);
    //获取快钱交易号
    $dealId=trim($_REQUEST['dealId']);
    //获取银行交易号   
    //如果使用银行卡支付时，在银行的交易号。如不是通过银行支付，则为空
    $bankDealId=trim($_REQUEST['bankDealId']);
    //获取在快钱交易时间
    $dealTime=trim($_REQUEST['dealTime']);
    //获取实际支付金额
    $payAmount=trim($_REQUEST['payAmount']);
    //获取交易手续费
    $fee=trim($_REQUEST['fee']);
    //获取扩展字段1
    $ext1=trim($_REQUEST['ext1']);
    //获取扩展字段2
    $ext2=trim($_REQUEST['ext2']);
    //获取处理结果
    ///10代表 成功; 11代表 失败
    /////00代表 下订单成功（仅对电话银行支付订单返回）;01代表 下订单失败（仅对电话银行支付订单返回）
    $payResult=trim($_REQUEST['payResult']);
    //获取错误代码
    /////详细见文档错误代码列表
    $errCode=trim($_REQUEST['errCode']);
    //获取加密签名串
    $signMsg=trim($_REQUEST['signMsg']);

    //生成加密串。必须保持如下顺序。 
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"merchantAcctId",$merchantAcctId);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"version",$version);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"language",$language);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"signType",$signType);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"payType",$payType);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"bankId",$bankId);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"orderId",$orderId);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"orderTime",$orderTime);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"orderAmount",$orderAmount);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"dealId",$dealId);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"bankDealId",$bankDealId);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"dealTime",$dealTime);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"payAmount",$payAmount);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"fee",$fee);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"ext1",$ext1);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"ext2",$ext2);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"payResult",$payResult);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"errCode",$errCode);
    $merchantSignMsgVal=appendParam($merchantSignMsgVal,"key",$key);
    $merchantSignMsg= md5($merchantSignMsgVal);

    //初始化结果及地址
    $rtnOk=0;
    $rtnUrl="";
    //商家进行数据处理，并跳转会商家显示支付结果的页面
    $shop_order = M('ShopOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['paystatus'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功
	  if($shop_order -> where($where) -> save($data)){
	    $this -> assign('img_info', 'gwc_success');
	  }else{
	    $this -> assign('img_info', 'gwc_fail');
	  }
	  break;
	default:
	  $data['paystatus'] = 0;
	  $data['paytype'] = '快钱';
	  $shop_order -> where($where) -> save($data);
	  $this -> assign('img_info', 'gwc_fail');
	  break;
      }
    }else{
      $this -> assign('img_info', 'gwc_fail');
    }
    $this -> display('./index/Tpl/Shop/returnurl.html');
  }
  /* -------------------------- 商 城 ------------------------ */
}

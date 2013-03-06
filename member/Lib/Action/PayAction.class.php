<?php
class PayAction extends Action {
  //支付宝异步通知页面
  public function alipaynotify(){
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

      $rmb_order = M('RmbOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$rmb_order -> where($where) -> save($data);
	echo "success";
      
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $rmb_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	//如果更新成功，并且订单状态是从未付款到已付款，则更新会员RMB表
	if($rmb_order -> where($where) -> save($data) && $now_status == 0){
	  //获取支付总额
	  $total_pee = $this -> _post('total_fee');
	  //获取此订单的用户id
	  $mid = $rmb_order -> getFieldByordernum($out_trade_no, 'mid');
	  //更新用户RMB余额
	  M('MemberRmb') -> where(array('mid' => $mid)) -> setInc('rmb_pay', $total_pee);
	}
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$rmb_order -> where($where) -> save($data);
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$rmb_order -> where($where) -> save($data);
	echo "success";
      }
    }else{
      echo "fail";
    }
  }

  //支付宝同步通知页面
  public function alipayreturn(){
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
      //商户订单号
      $out_trade_no = $_GET['out_trade_no'];

      $rmb_order = M('RmbOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();
      $member_rmb = D('MemberRmb');

      if($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $rmb_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	//如果更新成功，并且订单状态是从未付款到已付款，则更新会员RMB表
	if($rmb_order -> where($where) -> save($data) && $now_status == 0){
	  //获取支付总额
	  $total_pee = $this -> _get('price');
	  //获取此订单的用户id
	  $mid = $rmb_order -> getFieldByordernum($out_trade_no, 'mid');
	  //更新用户RMB余额
	  $member_rmb -> where(array('mid' => $mid)) -> setInc('rmb_pay', $total_pee);
	}
      }
      $session_uid = session(C('USER_AUTH_KEY'));
      //写RMB消费日志
      $detail = D('MemberRmbDetail');
      $detail -> writelog($session_uid, '恭喜您,您已通过<span style="color:blue;">支付宝</span>成功在线充值RMB', '充值', $total_pee);
      //计算返送金额
      $gaving_ratio = M('PayGaving') -> field('ratio') -> where(array('money' => array('ELT', $total_pee))) -> order('money DESC') -> find();
      $gaving_ratio['ratio'] = floatval($gaving_ratio['ratio']);
      $gaving_pee = $gaving_ratio['ratio'] * $total_pee;
      //如果存在返送金额，则更新用户余额
      if($gaving_pee > 0){
	//更新用户RMB余额
	$member_rmb -> where(array('mid' => $mid)) -> setInc('rmb_pay', $gaving_pee);
	//写RMB消费日志
	D('MemberRmbDetail') -> writelog($session_uid, "恭喜您,您已成功在线充值<span style='color:blue;'>{$total_pee}元</span>后易搜返还的RMB", '获取', $gaving_pee);
      }
      //重新缓存用户rmb余额
      $member_rmb -> rmbtotal($session_uid);
      //充值成功的图片
      $this -> assign('pic_name', 'success_tishi.gif');
    }else{
      //充值失败的图片
      $this -> assign('pic_name', 'fail_tishi.gif');
    }
    $this -> display('./member/Tpl/Money/rmbrecharge_four.html'); 
  }

  //快钱同步返回页面
  public function k99billreturn(){
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
    $rmb_order = M('RmbOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  //如果更新成功，则写RMB表
	  if($rmb_order -> where($where) -> save($data)){
	    //获取支付总额
	    $total_pee = $payAmount / 100;
	    //获取此订单的用户id
	    $mid = $rmb_order -> getFieldByordernum($orderId, 'mid');
	    //更新用户RMB余额
	    M('MemberRmb') -> where(array('mid' => $mid)) -> setInc('rmb_pay', $total_pee);
	    //写RMB消费日志
	    D('MemberRmbDetail') -> writelog($mid, '恭喜您,您已通过<span style="color:blue;">快钱</span>成功在线充值RMB', '充值', $total_pee);
	    //计算返送金额
	    $gaving_ratio = M('PayGaving') -> field('ratio') -> where(array('money' => array('ELT', $total_pee))) -> order('money DESC') -> find();
	    $gaving_ratio['ratio'] = floatval($gaving_ratio['ratio']);
	    $gaving_pee = $gaving_ratio['ratio'] * $total_pee;
	    //如果存在返送金额，则更新用户余额
	    if($gaving_pee > 0){
	      //更新用户RMB余额
	      $member_rmb -> where(array('mid' => $mid)) -> setInc('rmb_pay', $gaving_pee);
	      //写RMB消费日志
	      D('MemberRmbDetail') -> writelog($mid, "恭喜您,您已成功在线充值<span style='color:blue;'>{$total_pee}元</span>后易搜返还的RMB", '获取', $gaving_pee);
	    }
	    //重新缓存用户rmb余额
	    $member_rmb -> rmbtotal($mid);
	  }
	  $this -> assign('pic_name', 'success_tishi.gif');
	  break;
	default:
	  $data['status'] = 0;
	  $rmb_order -> where($where) -> save($data);
	  $this -> assign('pic_name', 'fail_tishi.gif');
	  break;
      }
    }else{
      $this -> assign('pic_name', 'fail_tishi.gif');
    }
    $this -> display('./member/Tpl/Money/rmbrecharge_four.html');
  }

  //财付通异步页面
  public function tenpaynotify(){
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
	    //财付通订单号
	    $transaction_id = $resHandler->getParameter("transaction_id");
	    //金额,以分为单位
	    $total_fee = $resHandler->getParameter("total_fee");

	    //商家业务逻辑
	    $rmb_order = M('RmbOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    if($rmb_order -> where($where) -> save($data)){
	      //获取支付总额
	      $total_pee = $total_fee / 100;
	      //获取此订单的用户id
	      $mid = $rmb_order -> getFieldByordernum($out_trade_no, 'mid');
	      //更新用户RMB余额
	      M('MemberRmb') -> where(array('mid' => $mid)) -> setInc('rmb_pay', $total_pee);
	    }
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

  //财付通同步返回页面
  public function tenpayreturn(){
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
	    $member_rmb = D('MemberRmb');
	    $session_uid = session(C('USER_AUTH_KEY'));
	    $money = $total_fee / 100;
	    
	    //写RMB消费日志
	    D('MemberRmbDetail') -> writelog($session_uid, '恭喜您,您已通过<span style="color:blue;">财付通</span>成功在线充值RMB', '充值', $money);
	    //计算返送金额
	    $gaving_ratio = M('PayGaving') -> field('ratio') -> where(array('money' => array('ELT', $money))) -> order('money DESC') -> find();
	    $gaving_ratio['ratio'] = floatval($gaving_ratio['ratio']);
	    $gaving_pee = $gaving_ratio['ratio'] * $money;
	    //如果存在返送金额，则更新用户余额
	    if($gaving_pee > 0){
	    //更新用户RMB余额
	    $member_rmb -> where(array('mid' => $session_uid)) -> setInc('rmb_pay', $gaving_pee);
	    //写RMB消费日志
	    D('MemberRmbDetail') -> writelog($session_uid, "恭喜您,您已成功在线充值<span style='color:blue;'>{$money}元</span>后易搜返还的RMB", '获取', $gaving_pee);
	    }

	    //重新缓存用户rmb余额
	    $member_rmb -> rmbtotal($session_uid);
	    $this -> assign('pic_name', 'success_tishi.gif');
	  } else {
	    //当做不成功处理
	    $this -> assign('pic_name', 'fail_tishi.gif');
	  }
	}
    }else{
      $this -> assign('pic_name', 'fail_tishi.gif');
    }
    $this -> display('./member/Tpl/Money/rmbrecharge_four.html');
  
  }


}

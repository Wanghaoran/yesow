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

      if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取此订单是否已经进行过充值操作
	$ispay = $rmb_order -> getFieldByordernum($out_trade_no, 'ispay');
	//再读取此订单目前的订单状态
	$old_status = $rmb_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	//如果订单是未付款的状态，并且未进行过更新RMB表的操作，并且更新成功，则更新会员RMB表
	if($old_status == 0 && $ispay == 0 && $rmb_order -> where($where) -> save($data)){
	  $member_rmb = D('MemberRmb');
	  //获取支付总额
	  $total_pee = $this -> _post('total_fee');
	  //获取此订单的用户id
	  $mid = $rmb_order -> getFieldByordernum($out_trade_no, 'mid');
	  //向用户RMB表增加
	  if($member_rmb -> addmoney('rmb_pay', $total_pee, $mid)){
	    //标记为已充值
	    $rmb_order -> where($where) -> save(array('ispay' => 1));
	    //写RMB消费日志
	    $detail = D('MemberRmbDetail');
	    $detail -> writelog($mid, '恭喜您,您已通过<span style="color:blue;">支付宝</span>成功在线充值RMB', '充值', $total_pee);
	    //计算返送费率
	    $gaving_ratio = M('PayGaving') -> field('ratio') -> where(array('money' => array('ELT', $total_pee))) -> order('money DESC') -> find();
	    $gaving_ratio['ratio'] = floatval($gaving_ratio['ratio']);
	    //返送金额
	    $gaving_pee = $gaving_ratio['ratio'] * $total_pee;
	    //如果存在返送金额，则增加返送的金
	    if($gaving_pee > 0){
	      //更新用户RMB余额
	      if($member_rmb -> addmoney('rmb_pay', $gaving_pee, $mid)){
		//写RMB消费日志
		D('MemberRmbDetail') -> writelog($mid, "恭喜您,您已成功在线充值<span style='color:blue;'>{$total_pee}元</span>后易搜返还的RMB", '获取', $gaving_pee);
	      }	      
	    }
	  }
	}
	ob_end_clean();
	echo "success";
	exit();
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	//读取此订单目前的订单状态
	$old_status = $rmb_order -> getFieldByordernum($out_trade_no, 'status');
	$data['status'] = 2;
	//如果订单是 已付款未发货，才进行此更新
	if($old_status == 1){
	  $rmb_order -> where($where) -> save($data);
	}
	ob_end_clean();
	echo "success";
	exit();
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	//读取此订单目前的订单状态
	$old_status = $rmb_order -> getFieldByordernum($out_trade_no, 'status');
	$data['status'] = 3;
	//如果订单是 已发货未确认收货，才进行此更新
	if($old_status == 2){
	  $rmb_order -> where($where) -> save($data);
	}
	ob_end_clean();
	echo "success";
	exit();
      }
    }else{
      ob_end_clean();
      echo "fail";
      exit();
    }
  }

  //支付宝同步通知页面
  public function alipayreturn(){
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
      //重新缓存用户rmb余额
      D('MemberRmb') -> rmbtotal();
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
	    //标记为已充值
	    $rmb_order -> where($where) -> save(array('ispay' => 1));
	    //获取支付总额
	    $total_pee = $payAmount / 100;
	    //获取此订单的用户id
	    $mid = $rmb_order -> getFieldByordernum($orderId, 'mid');
	    $member_rmb = D('MemberRmb');
	    //更新用户RMB余额
	    $member_rmb -> where(array('mid' => $mid)) -> setInc('rmb_pay', $total_pee);
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
	      //标记为已充值
	      $rmb_order -> where($where) -> save(array('ispay' => 1));
	      //获取支付总额
	      $total_pee = $total_fee / 100;
	      //获取此订单的用户id
	      $mid = $rmb_order -> getFieldByordernum($out_trade_no, 'mid');
	      //更新用户RMB余额
	      $member_rmb = M('MemberRmb');
	      $member_rmb -> where(array('mid' => $mid)) -> setInc('rmb_pay', $total_pee);
	      //写RMB消费日志
	      D('MemberRmbDetail') -> writelog($mid, '恭喜您,您已通过<span style="color:blue;">财付通</span>成功在线充值RMB', '充值', $total_pee);
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
  public function tenpayreturn(){
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
	    $member_rmb = D('MemberRmb');
	    $session_uid = session(C('USER_AUTH_KEY'));
	    //重新缓存用户rmb余额
	    $member_rmb -> rmbtotal($session_uid);
	    $this -> assign('pic_name', 'success_tishi.gif');
	  }else{
	    $this -> assign('pic_name', 'fail_tishi.gif');
	  }	    
	}else{
	  //当做不成功处理
	  $this -> assign('pic_name', 'fail_tishi.gif');
	}
    }else{
      $this -> assign('pic_name', 'fail_tishi.gif');
    }
    $this -> display('./member/Tpl/Money/rmbrecharge_four.html');
  }


  /* -------------------------- 包 月 ------------------------ */

  //包月 - 支付宝异步通知页面
  public function monthly_alipaynotify(){
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

      $monthly_order = M('MonthlyOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$monthly_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $monthly_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写包月主表
	if($monthly_order -> where($where) -> save($data) && $now_status == 0){
	  //获取购买的月份
	  $month = $monthly_order -> table('yesow_monthly_order as mo') -> field('mm.months') -> join('yesow_member_monthly as mm ON mo.monid = mm.id') -> where(array('mo.ordernum' => $out_trade_no)) -> find();
	  $data = array();
	  $data['mid'] = $monthly_order -> getFieldByordernum($out_trade_no, 'mid');
	  $data['monid'] = $monthly_order -> getFieldByordernum($out_trade_no, 'monid');
	  $data['starttime'] = time();
	  $data['endtime'] = $data['starttime'] + ($month['months'] * 30 * 24 * 60 *60);
	  //写用户主表
	  M('Monthly') -> add($data);
	}
	ob_end_clean();
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$monthly_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$monthly_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
  }

  //包月 - 支付宝同步通知页面
  public function monthly_alipayreturn(){
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
	$this -> assign('pic_name', 'success_tishi.gif');
	//重新缓存用户rmb及等级
	D('MemberRmb') -> rmbtotal();
      }else{
	$this -> assign('pic_name', 'fail_tishi.gif');
      }
    }else{
      $this -> assign('pic_name', 'fail_tishi.gif');
    }
    $this -> display('./member/Tpl/Money/rmbrecharge_four.html'); 
  }

  //包月 - 财付通异步页面
  public function monthly_tenpaynotify(){
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
	    $monthly_order = M('MonthlyOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($monthly_order -> where($where) -> save($data)){
	      //获取购买的月份
	      $month = $monthly_order -> table('yesow_monthly_order as mo') -> field('mm.months') -> join('yesow_member_monthly as mm ON mo.monid = mm.id') -> where(array('mo.ordernum' => $out_trade_no)) -> find();
	      $data = array();
	      $data['mid'] = $monthly_order -> getFieldByordernum($out_trade_no, 'mid');
	      $data['monid'] = $monthly_order -> getFieldByordernum($out_trade_no, 'monid');
	      $data['starttime'] = time();
	      $data['endtime'] = $data['starttime'] + ($month['months'] * 30 * 24 * 60 *60);
	      //写包月主表
	      M('Monthly') -> add($data);
	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }

  //财付通同步返回页面
  public function monthly_tenpayreturn(){
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
	    //重新缓存用户rmb及等级
	    D('MemberRmb')-> rmbtotal();
	    $this -> assign('pic_name', 'success_tishi.gif');
	  }else{
	    $this -> assign('pic_name', 'fail_tishi.gif');
	  }	    
	}else{
	  //当做不成功处理
	  $this -> assign('pic_name', 'fail_tishi.gif');
	}
    }else{
      $this -> assign('pic_name', 'fail_tishi.gif');
    }
    $this -> display('./member/Tpl/Money/rmbrecharge_four.html');
  }

  //快钱同步返回页面
  public function monthly_k99billreturn(){
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
    $monthly_order = M('MonthlyOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写RMB表
	  if($monthly_order -> where($where) -> save($data)){
	    //获取购买的月份
	    $month = $monthly_order -> table('yesow_monthly_order as mo') -> field('mm.months') -> join('yesow_member_monthly as mm ON mo.monid = mm.id') -> where(array('mo.ordernum' => $orderId)) -> find();
	      $data = array();
	      $data['mid'] = $monthly_order -> getFieldByordernum($orderId, 'mid');
	      $data['monid'] = $monthly_order -> getFieldByordernum($orderId, 'monid');
	      $data['starttime'] = time();
	      $data['endtime'] = $data['starttime'] + ($month['months'] * 30 * 24 * 60 *60);
	      //写包月主表
	      M('Monthly') -> add($data);
	      //重新缓存用户rmb及等级
	      D('MemberRmb')-> rmbtotal($data['mid']);	   
	  }
	  $this -> assign('pic_name', 'success_tishi.gif');
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $monthly_order -> where($where) -> save($data);
	  $this -> assign('pic_name', 'fail_tishi.gif');
	  break;
      }
    }else{
      $this -> assign('pic_name', 'fail_tishi.gif');
    }
    $this -> display('./member/Tpl/Money/rmbrecharge_four.html');
  }


  /* -------------------------- 包 月 ------------------------ */


  /* -------------------------- 在线QQ ------------------------ */
  //快钱同步返回页面
  public function qqonline_k99billreturn(){
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
    $qqonline_order = M('QqonlineOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写主表
	  if($qqonline_order -> where($where) -> save($data)){
	    //写在线QQ主表
	    ////订单相关信息
	    $qqonline_info = $qqonline_order -> table('yesow_qqonline_order as qo') -> field('qo.id,qo.cid,qm.months,qo.mid') -> join('yesow_qqonline_money as qm ON qo.qid = qm.id') -> where(array('qo.ordernum' => $orderId)) -> find();
	    //订单所属QQ信息
	    $order_qq_info = M('QqonlineOrderList') -> field('qqcode,qqname') -> where(array('oid' => $qqonline_info['id'])) -> select();
	    //写主表
	    $CompanyQqonline = M('CompanyQqonline');
	    $qq_data = array();
	    $qq_data['mid'] = $qqonline_info['mid'];
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
	      R('Services/qqonlinesuccess',array($info_succ, 'success', $qqonline_info['cid']));
	    }else{
	      R('Register/errorjump',array(L('QQONLINE_ERROR')));
	    }
	  }
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $qqonline_order -> where($where) -> save($data);
	  $info_succ = "在线QQ相关服务未购买成功";
	  R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
	  break;
      }
    }else{
      $info_succ = "在线QQ相关服务未购买成功";
      R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
    }
  }

  //支付宝异步通知页面
  public function qqonline_alipaynotify(){
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

      $qqonline_order = M('QqonlineOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$qqonline_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $qqonline_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写主表
	if($qqonline_order -> where($where) -> save($data) && $now_status == 0){
	  //写在线QQ主表
	  //////订单相关信息
	  $qqonline_info = $qqonline_order -> table('yesow_qqonline_order as qo') -> field('qo.id,qo.cid,qm.months,qo.mid') -> join('yesow_qqonline_money as qm ON qo.qid = qm.id') -> where(array('qo.ordernum' => $out_trade_no)) -> find();
	  //订单所属QQ信息
	  $order_qq_info = M('QqonlineOrderList') -> field('qqcode,qqname') -> where(array('oid' => $qqonline_info['id'])) -> select();
	  //写主表
	  $CompanyQqonline = M('CompanyQqonline');
	  $qq_data = array();
	  $qq_data['mid'] = $qqonline_info['mid'];
	  $qq_data['cid'] = $qqonline_info['cid'];
	  $qq_data['starttime'] = time();
	  $qq_data['endtime'] = $qq_data['starttime'] + ($qqonline_info['months'] * 30 * 24 * 60 * 60);
	  foreach($order_qq_info as $value){
	    $qq_data['qqcode'] = $value['qqcode'];
	    $qq_data['qqname'] = $value['qqname'];
	    $CompanyQqonline -> add($qq_data);
	  }
	}
	ob_end_clean();
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$qqonline_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$qqonline_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
  }

  //支付宝同步通知页面
  public function qqonline_alipayreturn(){
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
      $info_succ = "您已成功购买在线QQ相关服务";
      R('Services/qqonlinesuccess',array($info_succ, 'success', $qqonline_info['cid']));
    }else{
      $info_succ = "在线QQ相关服务未购买成功";
      R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
    }
  }

  //财付通异步通知页面
  public function qqonline_tenpaynotify(){
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
	    $qqonline_order = M('QqonlineOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($qqonline_order -> where($where) -> save($data)){
	      //写在线QQ主表
	      ////////订单相关信息
	      $qqonline_info = $qqonline_order -> table('yesow_qqonline_order as qo') -> field('qo.id,qo.cid,qm.months,qo.mid') -> join('yesow_qqonline_money as qm ON qo.qid = qm.id') -> where(array('qo.ordernum' => $out_trade_no)) -> find();
	      //订单所属QQ信息
	      $order_qq_info = M('QqonlineOrderList') -> field('qqcode,qqname') -> where(array('oid' => $qqonline_info['id'])) -> select();
	      //写主表
	      $CompanyQqonline = M('CompanyQqonline');
	      $qq_data = array();
	      $qq_data['mid'] = $qqonline_info['mid'];
	      $qq_data['cid'] = $qqonline_info['cid'];
	      $qq_data['starttime'] = time();
	      $qq_data['endtime'] = $qq_data['starttime'] + ($qqonline_info['months'] * 30 * 24 * 60 * 60);
	      foreach($order_qq_info as $value){
		$qq_data['qqcode'] = $value['qqcode'];
		$qq_data['qqname'] = $value['qqname'];
		$CompanyQqonline -> add($qq_data);
	      }   
	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }

  //财付通同步通知页面
  public function qqonline_tenpayreturn(){
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
	    $info_succ = "您已成功购买在线QQ相关服务";
	    R('Services/qqonlinesuccess',array($info_succ, 'success', $qqonline_info['cid']));
	  }else{
	    $info_succ = "在线QQ相关服务未购买成功";
	    R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
	  }	    
	}else{
	  //当做不成功处理
	  $info_succ = "在线QQ相关服务未购买成功";
	  R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
	}
    }else{
      $info_succ = "在线QQ相关服务未购买成功";
      R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
    }
  }


  //续费 - 快钱同步返回页面
  public function qqonline_renew_k99billreturn(){
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
    $qqonline_order = M('QqonlineOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写主表
	  if($qqonline_order -> where($where) -> save($data)){
	    //写在线QQ主表
	    ////订单相关信息
	    $qqonline_info = $qqonline_order -> table('yesow_qqonline_order as qo') -> field('qo.id,qo.cid,qm.months') -> join('yesow_qqonline_money as qm ON qo.qid = qm.id') -> where(array('qo.ordernum' => $orderId)) -> find();
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
	      R('Services/qqonlinesuccess',array($info_succ, 'success', $qqonline_info['cid']));
	    }else{
	      R('Register/errorjump',array(L('QQONLINE_ERROR')));
	    }
	  }
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $qqonline_order -> where($where) -> save($data);
	  $info_succ = "在线QQ相关服务未续费成功";
	  R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
	  break;
      }
    }else{
      $info_succ = "在线QQ相关服务未续费成功";
      R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
    }
    $this -> display('./member/Tpl/Money/rmbrecharge_four.html');
  }

  //续费 - 支付宝异步通知页面
  public function qqonline_renew_alipaynotify(){
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

      $qqonline_order = M('QqonlineOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$qqonline_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $qqonline_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写主表
	if($qqonline_order -> where($where) -> save($data) && $now_status == 0){
	  //写在线QQ主表
	  ////订单相关信息
	  $qqonline_info = $qqonline_order -> table('yesow_qqonline_order as qo') -> field('qo.id,qo.cid,qm.months') -> join('yesow_qqonline_money as qm ON qo.qid = qm.id') -> where(array('qo.ordernum' => $out_trade_no)) -> find();
	  //订单所属QQ信息
	  $order_qq_info = M('QqonlineOrderList') -> field('qqcode,qqname') -> where(array('oid' => $qqonline_info['id'])) -> find();
	  //更新主表
	  $CompanyQqonline = M('CompanyQqonline');
	  $where_up = array();
	  $where_up['cid'] = $qqonline_info['cid'];
	  $where_up['qqcode'] = $order_qq_info['qqcode'];
	  $where_up['qqname'] = $order_qq_info['qqname'];
	  $CompanyQqonline -> where($where_up) -> setInc('endtime', $qqonline_info['months'] * 30 * 24 * 60 * 60);
	  ob_end_clean();
	  echo "success";
	}
	//该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$qqonline_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$qqonline_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
    }
  }

  //续费 - 支付宝同步通知页面
  public function qqonline_renew_alipayreturn(){
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
      $info_succ = "您已成功续费在线QQ相关服务";
      R('Services/qqonlinesuccess',array($info_succ, 'success', $qqonline_info['cid']));
    }else{
      $info_succ = "在线QQ相关服务未续费成功";
      R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
    }
  }

  //续费 - 财付通异步通知页面
  public function qqonline_renew_tenpaynotify(){
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
	    $qqonline_order = M('QqonlineOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($qqonline_order -> where($where) -> save($data)){
	      //写在线QQ主表
	      //////订单相关信息
	      $qqonline_info = $qqonline_order -> table('yesow_qqonline_order as qo') -> field('qo.id,qo.cid,qm.months') -> join('yesow_qqonline_money as qm ON qo.qid = qm.id') -> where(array('qo.ordernum' => $out_trade_no)) -> find();
	      //订单所属QQ信息
	     $order_qq_info = M('QqonlineOrderList') -> field('qqcode,qqname') -> where(array('oid' => $qqonline_info['id'])) -> find();
	      //更新主表
	      $CompanyQqonline = M('CompanyQqonline');
	      $where_up = array();
	      $where_up['cid'] = $qqonline_info['cid'];
	      $where_up['qqcode'] = $order_qq_info['qqcode'];
	      $where_up['qqname'] = $order_qq_info['qqname'];
	      $CompanyQqonline -> where($where_up) -> setInc('endtime', $qqonline_info['months'] * 30 * 24 * 60 * 60);
	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }

  //续费 - 财付通同步通知页面
  public function qqonline_renew_tenpayreturn(){
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
	    $info_succ = "您已成功续费在线QQ相关服务";
	    R('Services/qqonlinesuccess',array($info_succ, 'success', $qqonline_info['cid']));
	  }else{
	    $info_succ = "在线QQ相关服务未续费成功";
	    R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
	  }	    
	}else{
	  //当做不成功处理
	  $info_succ = "在线QQ相关服务未续费成功";
	  R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
	}
    }else{
      $info_succ = "在线QQ相关服务未续费成功";
      R('Services/qqonlinesuccess',array($info_succ, 'error', $qqonline_info['cid']));
    }
  }

  

  /* -------------------------- 在线QQ ------------------------ */


  /* -------------------------- 企业形象 ------------------------ */
  //企业形象 - 快钱同步返回页面
  public function companypic_k99billreturn(){
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
    $companypic_order = M('CompanypicOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写主表
	  if($companypic_order -> where($where) -> save($data)){
	    //写主表
	    //订单相关信息
	    $companypic_info = $companypic_order -> table('yesow_companypic_order as co') -> field('co.id,co.mid,co.filename,co.cid,cm.months,co.website') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.ordernum' => $orderId)) -> find();
	    //写主表
	    $Companypic = M('Companypic');
	    $pic_data = array();
	    $pic_data['mid'] = $companypic_info['mid'];
	    $pic_data['cid'] = $companypic_info['cid'];
	    $pic_data['filename'] = $companypic_info['filename'];
	    $pic_data['website'] = $companypic_info['website'];
	    $pic_data['starttime'] = time();
	    $pic_data['updatetime'] = time();
	    $pic_data['endtime'] = $pic_data['starttime'] + ($companypic_info['months'] * 30 * 24 * 60 * 60);
	    if($Companypic -> add($pic_data)){
	      $info_succ = "您已成功购买企业形象相关服务";
	      R('Services/companypicsuccess',array($info_succ, 'success', $companypic_info['cid']));
	    }else{
	      R('Register/errorjump',array(L('COMPANYPIC_ERROR')));
	    }
	  }
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $companypic_order -> where($where) -> save($data);
	  $info_succ = "企业形象相关服务未购买成功";
	  R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
	  break;
      }
    }else{
      $info_succ = "企业形象相关服务未购买成功";
      R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
    }
  }

  //企业形象 - 支付宝异步通知页面
  public function companypic_alipaynotify(){
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

      $companypic_order = M('CompanypicOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$companypic_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $companypic_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写主表
	if($companypic_order -> where($where) -> save($data) && $now_status == 0){
	  //订单相关信息
	  $companypic_info = $companypic_order -> table('yesow_companypic_order as co') -> field('co.id,co.mid,co.filename,co.cid,cm.months,co.website') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.ordernum' => $out_trade_no)) -> find();
	  //写主表
	  $Companypic = M('Companypic');
	  $pic_data = array();
	  $pic_data['mid'] = $companypic_info['mid'];
	  $pic_data['cid'] = $companypic_info['cid'];
	  $pic_data['filename'] = $companypic_info['filename'];
	  $pic_data['website'] = $companypic_info['website'];
	  $pic_data['starttime'] = time();
	  $pic_data['updatetime'] = time();
	  $pic_data['endtime'] = $pic_data['starttime'] + ($companypic_info['months'] * 30 * 24 * 60 * 60);
	  $Companypic -> add($pic_data);
	}
	ob_end_clean();
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$companypic_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$companypic_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
  }

  //企业形象 - 支付宝同步通知页面
  public function companypic_alipayreturn(){
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


    $mid = session(C('USER_AUTH_KEY'));

    //订单相关信息
    $companypic_order = M('CompanypicOrder');
    $companypic_info = $companypic_order -> table('yesow_companypic_order as co') -> field('co.id,co.filename,co.cid,cm.months') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.mid' => $mid)) -> order('co.addtime DESC') -> find();

    //验证成功
    if($verify_result){
      $info_succ = "您已成功购买企业形象相关服务";
      R('Services/companypicsuccess',array($info_succ, 'success', $companypic_info['cid']));
    }else{
      $info_succ = "企业形象相关服务未购买成功";
      R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
    }
  }

  //企业形象 - 财付通同步返回页面
  public function companypic_tenpayreturn(){
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
	//取得订单号
	$out_trade_no = $resHandler->getParameter("out_trade_no");
	$mid = session(C('USER_AUTH_KEY'));

	//订单相关信息
	$companypic_order = M('CompanypicOrder');
	$companypic_info = $companypic_order -> table('yesow_companypic_order as co') -> field('co.id,co.filename,co.cid,cm.months') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.mid' => $mid)) -> order('co.addtime DESC') -> find();

	if("1" == $trade_mode ) {
	  if( "0" == $trade_state){
	    $info_succ = "您已成功购买企业形象相关服务";
	    R('Services/companypicsuccess',array($info_succ, 'success', $companypic_info['cid']));
	  }else{
	    $info_succ = "企业形象相关服务未购买成功";
      R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
	  }	    
	}else{
	  //当做不成功处理
	  $info_succ = "企业形象相关服务未购买成功";
      R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
	}
    }else{
      $info_succ = "企业形象相关服务未购买成功";
      R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
    }
  }

  //企业形象 - 财付通异步返回页面
  public function companypic_tenpaynotify(){
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
	    $companypic_order = M('CompanypicOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($companypic_order -> where($where) -> save($data)){
	      //订单相关信息
	      $companypic_info = $companypic_order -> table('yesow_companypic_order as co') -> field('co.id,co.mid,co.filename,co.cid,cm.months,co.website') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.ordernum' => $out_trade_no)) -> find();
	      //写主表
	      $Companypic = M('Companypic');
	      $pic_data = array();
	      $pic_data['mid'] = $companypic_info['mid'];
	      $pic_data['cid'] = $companypic_info['cid'];
	      $pic_data['filename'] = $companypic_info['filename'];
	      $pic_data['website'] = $companypic_info['website'];
	      $pic_data['starttime'] = time();
	      $pic_data['updatetime'] = time();
	      $pic_data['endtime'] = $pic_data['starttime'] + ($companypic_info['months'] * 30 * 24 * 60 * 60);
	      $Companypic -> add($pic_data); 
	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }

  //企业形象 - 续费 - 快钱同步返回页面
  public function companypic_renew_k99billreturn(){
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
    $companypic_order = M('CompanypicOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写主表
	  if($companypic_order -> where($where) -> save($data)){
	    //写主表
	    //订单相关信息
	    $companypic_info = $companypic_order -> table('yesow_companypic_order as co') -> field('co.id,co.filename,co.cid,cm.months') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.ordernum' => $orderId)) -> find();
	    //更新主表
	    $Companypic = M('Companypic');
	    $where_up = array();
	    $where_up['cid'] = $companypic_info['cid'];
	    if($Companypic -> where($where_up) -> setInc('endtime', $companypic_info['months'] * 30 * 24 * 60 * 60)){
	      $info_succ = "您已成功续费企业形象相关服务";
	      R('Services/companypicsuccess',array($info_succ, 'success', $companypic_info['cid']));
	    }else{
	      R('Register/errorjump',array(L('COMPANYPIC_ERROR')));
	    }
	  }
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $companypic_order -> where($where) -> save($data);
	  $info_succ = "企业形象相关服务未续费成功";
	  R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
	  break;
      }
    }else{
      $info_succ = "企业形象相关服务未续费成功";
      R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
    }
  }

  //企业形象 - 续费 - 支付宝异步返回页面
  public function companypic_renew_alipaynotify(){
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

      $companypic_order = M('CompanypicOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$companypic_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $companypic_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写主表
	if($companypic_order -> where($where) -> save($data) && $now_status == 0){
	  //订单相关信息
	  $companypic_info = $companypic_order -> table('yesow_companypic_order as co') -> field('co.id,co.filename,co.cid,cm.months') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.ordernum' => $out_trade_no)) -> find();
	  //更新主表
	  $Companypic = M('Companypic');
	  $where_up = array();
	  $where_up['cid'] = $companypic_info['cid'];
	  $Companypic -> where($where_up) -> setInc('endtime', $companypic_info['months'] * 30 * 24 * 60 * 60);
	}
	ob_end_clean();
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$companypic_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$companypic_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
  }

  //企业形象 - 续费 - 支付宝同步返回页面
  public function companypic_renew_alipayreturn(){
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

    $mid = session(C('USER_AUTH_KEY'));

    //订单相关信息
    $companypic_order = M('CompanypicOrder');
    $companypic_info = $companypic_order -> table('yesow_companypic_order as co') -> field('co.id,co.filename,co.cid,cm.months') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.mid' => $mid)) -> order('co.addtime DESC') -> find();

    //验证成功
    if($verify_result){
      $info_succ = "您已成功续费企业形象相关服务";
      R('Services/companypicsuccess',array($info_succ, 'success', $companypic_info['cid']));
    }else{
      $info_succ = "企业形象相关服务未续费成功";
      R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
    }
  }

  //企业形象 - 续费 - 财付通同步返回页面
  public function companypic_renew_tenpayreturn(){
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
	//取得订单号
	$out_trade_no = $resHandler->getParameter("out_trade_no");
	$mid = session(C('USER_AUTH_KEY'));

	//订单相关信息
	$companypic_order = M('CompanypicOrder');
	$companypic_info = $companypic_order -> table('yesow_companypic_order as co') -> field('co.id,co.filename,co.cid,cm.months') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.mid' => $mid)) -> order('co.addtime DESC') -> find();

	if("1" == $trade_mode ) {
	  if( "0" == $trade_state){
	    $info_succ = "您已成功续费企业形象相关服务";
	    R('Services/companypicsuccess',array($info_succ, 'success', $companypic_info['cid']));
	  }else{
	    $info_succ = "企业形象相关服务未续费成功";
      R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
	  }	    
	}else{
	  //当做不成功处理
	  $info_succ = "企业形象相关服务未续费成功";
      R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
	}
    }else{
      $info_succ = "企业形象相关服务未续费成功";
      R('Services/companypicsuccess',array($info_succ, 'error', $companypic_info['cid']));
    }
  }

  //企业形象 - 续费 - 财付通异步返回页面
  public function companypic_renew_tenpaynotify(){
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
	    $companypic_order = M('CompanypicOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($companypic_order -> where($where) -> save($data)){
	      //订单相关信息
	      $companypic_info = $companypic_order -> table('yesow_companypic_order as co') -> field('co.id,co.filename,co.cid,cm.months') -> join('yesow_companypic_money as cm ON co.cmid = cm.id') -> where(array('co.ordernum' => $out_trade_no)) -> find();
	      //更新主表
	      $Companypic = M('Companypic');
	      $where_up = array();
	      $where_up['cid'] = $companypic_info['cid'];
	      $Companypic -> where($where_up) -> setInc('endtime', $companypic_info['months'] * 30 * 24 * 60 * 60); 
	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }

  /* -------------------------- 企业形象 ------------------------ */

  /* -------------------------- 页面广告 ------------------------ */
  //页面广告 - 快钱同步返回页面
  public function advert_k99billreturn(){
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
    $advert_order = M('AdvertOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写主表
	  if($advert_order -> where($where) -> save($data)){
	    //订单相关信息
	    $advert_info = $advert_order -> table('yesow_advert_order as ao') -> field('ao.id,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.ordernum' => $orderId)) -> find();

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
	      R('Services/advertsuccess',array($info_succ, 'success', $advert_info['adid']));
	    }else{
	      R('Register/errorjump',array(L('COMPANYPIC_ERROR')));
	    }
	  }
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $advert_order -> where($where) -> save($data);
	  $info_succ = "页面广告相关服务未购买成功";
	  R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
	  break;
      }
    }else{
      $info_succ = "页面广告相关服务未购买成功";
      R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
    }
  }

  //页面广告 - 支付宝异步通知页面
  public function advert_alipaynotify(){
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

      $advert_order = M('AdvertOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$advert_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $advert_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写主表
	if($advert_order -> where($where) -> save($data) && $now_status == 0){
	  //订单相关信息
	  $advert_info = $advert_order -> table('yesow_advert_order as ao') -> field('ao.id,ao.mid,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.ordernum' => $out_trade_no)) -> find();
	  //写主表
	  $Advert = M('Advert');
	  $advert_data = array();
	  $advert_data['mid'] = $advert_info['mid'];
	  $advert_data['adid'] = $advert_info['adid'];
	  $advert_data['website'] = $advert_info['website'];
	  $advert_data['filename'] = $advert_info['filename'];
	  $advert_data['starttime'] = time();
	  $advert_data['updatetime'] = time();
	  $advert_data['endtime'] = $advert_data['starttime'] + ($advert_info['months'] * 30 * 24 * 60 * 60);
	  $Advert -> add($advert_data);
	}
	ob_end_clean();
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$advert_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$advert_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
  }

  //页面广告 - 支付宝同步通知页面
  public function advert_alipayreturn(){
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

    $mid = session(C('USER_AUTH_KEY'));

    //订单相关信息
    $advert_order = M('AdvertOrder');
    $advert_info = $advert_order -> table('yesow_advert_order as ao') -> field('ao.id,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.mid' => $mid)) -> order('ao.addtime DESC') -> find();

    //验证成功
    if($verify_result){
      $info_succ = "您已成功购买页面广告相关服务";
      R('Services/advertsuccess',array($info_succ, 'success', $advert_info['adid']));
    }else{
      $info_succ = "页面广告相关服务未购买成功";
      R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
    }
  }

  //页面广告 - 财付通同步通知地址
  public function advert_tenpayreturn(){
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
	//取得订单号
	$out_trade_no = $resHandler->getParameter("out_trade_no");
	$mid = session(C('USER_AUTH_KEY'));

	//订单相关信息
	$advert_order = M('AdvertOrder');
	$advert_info = $advert_order -> table('yesow_advert_order as ao') -> field('ao.id,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.mid' => $mid)) -> order('ao.addtime DESC') -> find();

	if("1" == $trade_mode ) {
	  if( "0" == $trade_state){
	    $info_succ = "您已成功购买页面广告相关服务";
	    R('Services/advertsuccess',array($info_succ, 'success', $advert_info['adid']));
	  }else{
	    $info_succ = "页面广告相关服务未购买成功";
      R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
	  }	    
	}else{
	  //当做不成功处理
	  $info_succ = "页面广告相关服务未购买成功";
      R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
	}
    }else{
      $info_succ = "页面广告相关服务未购买成功";
      R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
    }
  }

  //页面广告 - 财付通异步通知地址
  public function advert_tenpaynotify(){
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
	    $advert_order = M('AdvertOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($advert_order -> where($where) -> save($data)){
	      //订单相关信息
	      $advert_info = $advert_order -> table('yesow_advert_order as ao') -> field('ao.id,ao.mid,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.ordernum' => $out_trade_no)) -> find();
	      //写主表
	      $Advert = M('Advert');
	      $advert_data = array();
	      $advert_data['mid'] = $advert_info['mid'];
	      $advert_data['adid'] = $advert_info['adid'];
	      $advert_data['website'] = $advert_info['website'];
	      $advert_data['filename'] = $advert_info['filename'];
	      $advert_data['starttime'] = time();
	      $advert_data['updatetime'] = time();
	      $advert_data['endtime'] = $advert_data['starttime'] + ($advert_info['months'] * 30 * 24 * 60 * 60);
	      $Advert -> add($advert_data); 
	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }

  //页面广告 - 续费 - 快钱同步返回地址
  public function advert_renew_k99billreturn(){
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
    $advert_order = M('AdvertOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写主表
	  if($advert_order -> where($where) -> save($data)){
	    //订单相关信息
	    $advert_info = $AdvertOrder -> table('yesow_advert_order as ao') -> field('ao.id,ao.mid,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.ordernum' => $orderId)) -> find();
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
	      R('Services/advertsuccess',array($info_succ, 'success', $advert_info['adid']));
	    }else{
	      R('Register/errorjump',array(L('COMPANYPIC_ERROR')));
	    }
	  }
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $advert_order -> where($where) -> save($data);
	  $info_succ = "页面广告相关服务未续费成功";
	  R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
	  break;
      }
    }else{
      $info_succ = "页面广告相关服务未续费成功";
      R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
    }
  }

  //页面广告 - 续费 - 支付宝异步通知地址
  public function advert_renew_alipaynotify(){
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

      $advert_order = M('AdvertOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$advert_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $advert_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写主表
	if($advert_order -> where($where) -> save($data) && $now_status == 0){
	  //订单相关信息
	  $advert_info = $AdvertOrder -> table('yesow_advert_order as ao') -> field('ao.id,ao.mid,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.ordernum' => $out_trade_no)) -> find();
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
	  $Advert -> where($where_up) -> setInc('endtime', $advert_info['months'] * 30 * 24 * 60 * 60);
	}
	ob_end_clean();
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$advert_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$advert_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
  }

  //页面广告 - 续费 - 支付宝同步通知地址
  public function advert_renew_alipayreturn(){
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

    //商户订单号
    $out_trade_no = $_POST['out_trade_no'];

    //订单相关信息
    $advert_order = M('AdvertOrder');
    $advert_info = $advert_order -> table('yesow_advert_order as ao') -> field('ao.id,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.ordernum' => $out_trade_no)) -> find();

    //验证成功
    if($verify_result){
      $info_succ = "您已成功续费页面广告相关服务";
      R('Services/advertsuccess',array($info_succ, 'success', $advert_info['adid']));
    }else{
      $info_succ = "页面广告相关服务未续费成功";
      R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
    }
  }

  //页面广告 - 续费 - 财付通同步返回地址
  public function advert_renew_tenpayreturn(){
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
	//取得订单号
	$out_trade_no = $resHandler->getParameter("out_trade_no");
	//订单相关信息
	$advert_order = M('AdvertOrder');
	$advert_info = $advert_order -> table('yesow_advert_order as ao') -> field('ao.id,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.ordernum' => $out_trade_no)) -> find();

	if("1" == $trade_mode ) {
	  if( "0" == $trade_state){
	    $info_succ = "您已成功续费页面广告相关服务";
	    R('Services/advertsuccess',array($info_succ, 'success', $advert_info['adid']));
	  }else{
	    $info_succ = "页面广告相关服务未续费成功";
      R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
	  }	    
	}else{
	  //当做不成功处理
	  $info_succ = "页面广告相关服务未续费成功";
      R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
	}
    }else{
      $info_succ = "页面广告相关服务未续费成功";
      R('Services/advertsuccess',array($info_succ, 'error', $advert_info['adid']));
    }
  }

  //页面广告 - 续费 - 财付通异步返回地址
  public function advert_renew_tenpaynotify(){
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
	    $advert_order = M('AdvertOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($advert_order -> where($where) -> save($data)){
	      //订单相关信息
	      $advert_info = $AdvertOrder -> table('yesow_advert_order as ao') -> field('ao.id,ao.mid,ao.filename,ao.website,ao.adid,am.months') -> join('yesow_advert_money as am ON ao.amid = am.id') -> where(array('ao.ordernum' => $out_trade_no)) -> find();
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
	      $Advert -> where($where_up) -> setInc('endtime', $advert_info['months'] * 30 * 24 * 60 * 60);
	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }
  /* -------------------------- 页面广告 ------------------------ */

  /* -------------------------- 速查排名 ------------------------ */

  //速查排名 - 快钱同步返回页面
  public function searchrank_k99billreturn(){
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
    $searchrank_order = M('SearchRankOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写主表
	  if($searchrank_order -> where($where) -> save($data)){
	    //订单相关信息
	    $searchrank_info = $searchrank_order -> table('yesow_search_rank_order as sro') -> field('sro.cid,sro.fid,sro.mid,sro.keyword,sro.rank,srm.months') -> join('yesow_search_rank_months_money as srm ON sro.srmid = srm.id') -> where(array('sro.ordernum' => $orderId)) -> find();

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
	    $searchrank_data['mid'] = $searchrank_info['mid'];
	    $searchrank_data['fid'] = $searchrank_info['fid'];
	    $searchrank_data['keyword'] = $searchrank_info['keyword'];
	    $searchrank_data['rank'] = $searchrank_info['rank'];
	    $searchrank_data['starttime'] = $endtime ? $endtime + 1 : time();
	    $searchrank_data['endtime'] = $searchrank_data['starttime'] + ($searchrank_info['months'] * 30 * 24 * 60 * 60);
	    $searchrank_data['updatetime'] = time();
	    if($SearchRank -> add($searchrank_data)){
	      $info_succ = "您已成功购买速查排名相关服务";
	      R('Register/successjump',array($info_succ, U('Services/searchrank')));
	    }else{
	      R('Register/errorjump',array(L('SEARCHRANK_ERROR'), U('Services/searchrank')));
	    }
	  }
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $searchrank_order -> where($where) -> save($data);
	  R('Register/errorjump',array(L('SEARCHRANK_ERROR'), U('Services/searchrank')));
	  break;
      }
    }else{
      R('Register/errorjump',array(L('SEARCHRANK_ERROR'), U('Services/searchrank')));
    }
  }

  //速查排名 - 支付宝异步通知页面
  public function searchrank_alipaynotify(){
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

      $searchrank_order = M('SearchRankOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$searchrank_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $searchrank_order -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写主表
	if($searchrank_order -> where($where) -> save($data) && $now_status == 0){

	  //订单相关信息
	  $searchrank_info = $searchrank_order -> table('yesow_search_rank_order as sro') -> field('sro.cid,sro.fid,sro.mid,sro.keyword,sro.rank,srm.months') -> join('yesow_search_rank_months_money as srm ON sro.srmid = srm.id') -> where(array('sro.ordernum' => $out_trade_no)) -> find();
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
	  $searchrank_data['mid'] = $searchrank_info['mid'];
	  $searchrank_data['fid'] = $searchrank_info['fid'];
	  $searchrank_data['keyword'] = $searchrank_info['keyword'];
	  $searchrank_data['rank'] = $searchrank_info['rank'];
	  $searchrank_data['starttime'] = $endtime ? $endtime + 1 : time();
	  $searchrank_data['endtime'] = $searchrank_data['starttime'] + ($searchrank_info['months'] * 30 * 24 * 60 * 60);
	  $searchrank_data['updatetime'] = time();
	  $SearchRank -> add($searchrank_data);
	}
	ob_end_clean();
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$searchrank_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$searchrank_order -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
  }

  //速查排名 - 支付宝同步通知页面
  public function searchrank_alipayreturn(){
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

    $mid = session(C('USER_AUTH_KEY'));

    //验证成功
    if($verify_result){
      $info_succ = "您已成功购买速查排名相关服务";
      R('Register/successjump',array($info_succ, U('Services/searchrank')));
    }else{
      R('Register/errorjump',array(L('SEARCHRANK_ERROR'), U('Services/searchrank')));
    }
  }

  //速查排名 - 财富通同步返回地址
  public function searchrank_tenpayreturn(){
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
	    $info_succ = "您已成功购买速查排名相关服务";
	    R('Register/successjump',array($info_succ, U('Services/searchrank')));
	  }else{
	    R('Register/errorjump',array(L('SEARCHRANK_ERROR'), U('Services/searchrank')));
	  }	    
	}else{
	  R('Register/errorjump',array(L('SEARCHRANK_ERROR'), U('Services/searchrank')));
	}
    }else{
      R('Register/errorjump',array(L('SEARCHRANK_ERROR'), U('Services/searchrank')));
    }
  }

  //速查排名 - 财富通异步返回地址
  public function searchrank_tenpaynotify(){
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
	    $searchrank_order = M('SearchRankOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($searchrank_order -> where($where) -> save($data)){
	      //订单相关信息
	      $searchrank_info = $searchrank_order -> table('yesow_search_rank_order as sro') -> field('sro.cid,sro.fid,sro.mid,sro.keyword,sro.rank,srm.months') -> join('yesow_search_rank_months_money as srm ON sro.srmid = srm.id') -> where(array('sro.ordernum' => $out_trade_no)) -> find();
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
	      $searchrank_data['mid'] = $searchrank_info['mid'];
	      $searchrank_data['fid'] = $searchrank_info['fid'];
	      $searchrank_data['keyword'] = $searchrank_info['keyword'];
	      $searchrank_data['rank'] = $searchrank_info['rank'];
	      $searchrank_data['starttime'] = $endtime ? $endtime + 1 : time();
	      $searchrank_data['endtime'] = $searchrank_data['starttime'] + ($searchrank_info['months'] * 30 * 24 * 60 * 60);
	      $searchrank_data['updatetime'] = time();
	      $SearchRank -> add($searchrank_data);
	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }
  /* -------------------------- 速查排名 ------------------------ */

  /* -------------------------- 推荐商家 ------------------------ */

  //推荐商家 - 快钱同步返回页面
  public function recommendcompany_k99billreturn(){
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
    $RecommendCompanyOrder = M('RecommendCompanyOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写主表
	  if($RecommendCompanyOrder -> where($where) -> save($data)){
	    //订单相关信息
	    $searchrank_info = $RecommendCompanyOrder -> alias('sro') -> field('sro.cid,sro.fid,sro.mid,sro.rank,srm.months') -> join('yesow_recommend_company_months_money as srm ON sro.srmid = srm.id') -> where(array('sro.ordernum' => $orderId)) -> find();

	    //开始时间
	    $RecommendCompany = M('RecommendCompany');
	    $where_starttime = array();
	    $where_starttime['fid'] = $searchrank_info['fid'];
	    $where_starttime['rank'] = $searchrank_info['rank'];
	    $where_starttime['endtime'] = array('EGT', time());
	    $endtime = $RecommendCompany -> where($where_starttime) -> order('endtime DESC') -> getField('endtime');

	    //写主表    
	    $searchrank_data = array();
	    $searchrank_data['cid'] = $searchrank_info['cid'];
	    $searchrank_data['mid'] = $searchrank_info['mid'];
	    $searchrank_data['fid'] = $searchrank_info['fid'];
	    $searchrank_data['rank'] = $searchrank_info['rank'];
	    $searchrank_data['starttime'] = $endtime ? $endtime + 1 : time();
	    $searchrank_data['endtime'] = $searchrank_data['starttime'] + ($searchrank_info['months'] * 30 * 24 * 60 * 60);
	    $searchrank_data['updatetime'] = time();
	    if($RecommendCompany -> add($searchrank_data)){
	      $info_succ = "您已成功购买推荐商家相关服务";
	      R('Register/successjump',array($info_succ, U('Services/index')));
	    }else{
	      R('Register/errorjump',array(L('RECOMMENDCOMPANY_ERROR'), U('Services/index')));
	    }
	  }
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $RecommendCompanyOrder -> where($where) -> save($data);
	  R('Register/errorjump',array(L('RECOMMENDCOMPANY_ERROR'), U('Services/index')));
	  break;
      }
    }else{
      R('Register/errorjump',array(L('RECOMMENDCOMPANY_ERROR'), U('Services/index')));
    }
  }

  //推荐商家 - 支付宝异步通知页面
  public function recommendcompany_alipaynotify(){
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

      $RecommendCompanyOrder = M('RecommendCompanyOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$RecommendCompanyOrder -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $RecommendCompanyOrder -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写主表
	if($RecommendCompanyOrder -> where($where) -> save($data) && $now_status == 0){

	  //订单相关信息
	  $searchrank_info = $RecommendCompanyOrder -> alias('sro') -> field('sro.cid,sro.fid,sro.mid,sro.rank,srm.months') -> join('yesow_recommend_company_months_money as srm ON sro.srmid = srm.id') -> where(array('sro.ordernum' => $out_trade_no)) -> find();
	  //开始时间
	  $RecommendCompany = M('RecommendCompany');
	  $where_starttime = array();
	  $where_starttime['fid'] = $searchrank_info['fid'];
	  $where_starttime['rank'] = $searchrank_info['rank'];
	  $where_starttime['endtime'] = array('EGT', time());
	  $endtime = $RecommendCompany -> where($where_starttime) -> order('endtime DESC') -> getField('endtime');
	  //写主表    
	  $searchrank_data = array();
	  $searchrank_data['cid'] = $searchrank_info['cid'];
	  $searchrank_data['mid'] = $searchrank_info['mid'];
	  $searchrank_data['fid'] = $searchrank_info['fid'];
	  $searchrank_data['rank'] = $searchrank_info['rank'];
	  $searchrank_data['starttime'] = $endtime ? $endtime + 1 : time();
	  $searchrank_data['endtime'] = $searchrank_data['starttime'] + ($searchrank_info['months'] * 30 * 24 * 60 * 60);
	  $searchrank_data['updatetime'] = time();
	  $RecommendCompany -> add($searchrank_data);
	}
	ob_end_clean();
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$RecommendCompanyOrder -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$RecommendCompanyOrder -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
  }

  //推荐商家 - 支付宝同步通知页面
  public function recommendcompany_alipayreturn(){
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

    $mid = session(C('USER_AUTH_KEY'));

    //验证成功
    if($verify_result){
      $info_succ = "您已成功购买推荐商家相关服务";
      R('Register/successjump',array($info_succ, U('Services/index')));
    }else{
      R('Register/errorjump',array(L('RECOMMENDCOMPANY_ERROR'), U('Services/index')));
    }
  }

  //推荐商家 - 财富通同步返回地址
  public function recommendcompany_tenpayreturn(){
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
	    $info_succ = "您已成功购买推荐商家相关服务";
	    R('Register/successjump',array($info_succ, U('Services/index')));
	  }else{
	    R('Register/errorjump',array(L('RECOMMENDCOMPANY_ERROR'), U('Services/index')));
	  }	    
	}else{
	  R('Register/errorjump',array(L('RECOMMENDCOMPANY_ERROR'), U('Services/index')));
	}
    }else{
      R('Register/errorjump',array(L('RECOMMENDCOMPANY_ERROR'), U('Services/index')));
    }
  }

  //推荐商家 - 财富通异步返回地址
  public function recommendcompany_tenpaynotify(){
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
	    $RecommendCompanyOrder = M('RecommendCompanyOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($RecommendCompanyOrder -> where($where) -> save($data)){
	      //订单相关信息
	      $searchrank_info = $RecommendCompanyOrder -> alias('sro') -> field('sro.cid,sro.fid,sro.mid,sro.rank,srm.months') -> join('yesow_recommend_company_months_money as srm ON sro.srmid = srm.id') -> where(array('sro.ordernum' => $out_trade_no)) -> find();
	      //开始时间
	      $RecommendCompany = M('RecommendCompany');
	      $where_starttime = array();
	      $where_starttime['fid'] = $searchrank_info['fid'];
	      $where_starttime['rank'] = $searchrank_info['rank'];
	      $where_starttime['endtime'] = array('EGT', time());
	      $endtime = $RecommendCompany -> where($where_starttime) -> order('endtime DESC') -> getField('endtime');
	      //写主表    
	      $searchrank_data = array();
	      $searchrank_data['cid'] = $searchrank_info['cid'];
	      $searchrank_data['mid'] = $searchrank_info['mid'];
	      $searchrank_data['fid'] = $searchrank_info['fid'];
	      $searchrank_data['rank'] = $searchrank_info['rank'];
	      $searchrank_data['starttime'] = $endtime ? $endtime + 1 : time();
	      $searchrank_data['endtime'] = $searchrank_data['starttime'] + ($searchrank_info['months'] * 30 * 24 * 60 * 60);
	      $searchrank_data['updatetime'] = time();
	      $RecommendCompany -> add($searchrank_data);
	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }
  /* -------------------------- 推荐商家 ------------------------ */

  //动感传媒 - 快钱同步
  public function companyshow_k99billreturn(){
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
    $MediaShowOrder = M('MediaShowOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写主表
	  if($MediaShowOrder -> where($where) -> save($data)){
	    //订单相关信息
	    $companyshow_info = $MediaShowOrder -> alias('mso') -> field('mso.csid,mso.ccid_one,mso.ccid_two,mso.mid,mso.name,mso.address,mso.linkman,mso.mobliephone,mso.companyphone,mso.qqcode,mso.keyword,mso.smallpic,mso.bigpic,mso.filename,mso.maketype,mso.remark,msm.months,mso.website') -> join('yesow_media_show_money as msm ON mso.smid = msm.id') -> where(array('ordernum' => $orderId)) -> find();

	    //写主表
	    $MediaShow = M('MediaShow');
	    $show_data = array();
	    $show_data['csid'] = $companyshow_info['csid'];
	    $show_data['ccid_one'] = $companyshow_info['ccid_one'];
	    $show_data['ccid_two'] = $companyshow_info['ccid_two'];
	    $show_data['mid'] = $companyshow_info['mid'];
	    $show_data['name'] = $companyshow_info['name'];
	    $show_data['address'] = $companyshow_info['address'];
	    $show_data['linkman'] = $companyshow_info['linkman'];
	    $show_data['mobliephone'] = $companyshow_info['mobliephone'];
	    $show_data['companyphone'] = $companyshow_info['companyphone'];
	    $show_data['qqcode'] = $companyshow_info['qqcode'];
	    $show_data['keyword'] = $companyshow_info['keyword'];
	    $show_data['remark'] = $companyshow_info['remark'];
	    $show_data['website'] = $companyshow_info['website'];
	    $show_data['maketype'] = $companyshow_info['maketype'];
	    if($companyshow_info['maketype'] == 1){
	      $show_data['image'] = $companyshow_info['smallpic'];
	      $url = C('MEDIA_PIC_PATH_SAVE');
	      $show_data['content'] = '<img src="' . $url . $companyshow_info['bigpic'] . '">';
	    }else{
	      $show_data['image'] = $companyshow_info['filename'];
	    }
	    $show_data['starttime'] = time();
	    $show_data['endtime'] = $show_data['starttime'] + ($companyshow_info['months'] * 30 * 24 * 60 * 60);
	    $show_data['addtime'] = time();
	    $show_data['updatetime'] = time();
	    $show_data['type'] = 1;

	    if($MediaShow -> add($show_data)){
	      $info_succ = "您已成功购买动感传媒相关服务";
	      R('Register/successjump',array($info_succ, U('Services/companyshow')));
	    }else{
	      R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
	    }
	  }
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $MediaShowOrder -> where($where) -> save($data);
	  R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
	  break;
      }
    }else{
      R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
    }
  }

  //动感传媒 - 支付宝异步
  public function companyshow_alipaynotify(){
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

      $MediaShowOrder = M('MediaShowOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$MediaShowOrder -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $MediaShowOrder -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写主表
	if($MediaShowOrder -> where($where) -> save($data) && $now_status == 0){

	  //订单相关信息
	    $companyshow_info = $MediaShowOrder -> alias('mso') -> field('mso.csid,mso.ccid_one,mso.ccid_two,mso.mid,mso.name,mso.address,mso.linkman,mso.mobliephone,mso.companyphone,mso.qqcode,mso.keyword,mso.smallpic,mso.bigpic,mso.filename,mso.maketype,mso.remark,msm.months,mso.website') -> join('yesow_media_show_money as msm ON mso.smid = msm.id') -> where(array('ordernum' => $out_trade_no)) -> find();
	  //写主表
	    $MediaShow = M('MediaShow');
	    $show_data = array();
	    $show_data['csid'] = $companyshow_info['csid'];
	    $show_data['ccid_one'] = $companyshow_info['ccid_one'];
	    $show_data['ccid_two'] = $companyshow_info['ccid_two'];
	    $show_data['mid'] = $companyshow_info['mid'];
	    $show_data['name'] = $companyshow_info['name'];
	    $show_data['address'] = $companyshow_info['address'];
	    $show_data['linkman'] = $companyshow_info['linkman'];
	    $show_data['mobliephone'] = $companyshow_info['mobliephone'];
	    $show_data['companyphone'] = $companyshow_info['companyphone'];
	    $show_data['qqcode'] = $companyshow_info['qqcode'];
	    $show_data['keyword'] = $companyshow_info['keyword'];
	    $show_data['remark'] = $companyshow_info['remark'];
	    $show_data['website'] = $companyshow_info['website'];
	    $show_data['maketype'] = $companyshow_info['maketype'];
	    if($companyshow_info['maketype'] == 1){
	      $show_data['image'] = $companyshow_info['smallpic'];
	      $url = C('MEDIA_PIC_PATH_SAVE');
	      $show_data['content'] = '<img src="' . $url . $companyshow_info['bigpic'] . '">';
	    }else{
	      $show_data['image'] = $companyshow_info['filename'];
	    }
	    $show_data['starttime'] = time();
	    $show_data['endtime'] = $show_data['starttime'] + ($companyshow_info['months'] * 30 * 24 * 60 * 60);
	    $show_data['addtime'] = time();
	    $show_data['updatetime'] = time();
	    $show_data['type'] = 1;

	    $MediaShow -> add($show_data);
	}
	ob_end_clean();
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$MediaShowOrder -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$MediaShowOrder -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
  }

  //动感传媒 - 支付宝同步
  public function companyshow_alipayreturn(){
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

    $mid = session(C('USER_AUTH_KEY'));

    //验证成功
    if($verify_result){
      $info_succ = "您已成功购买动感传媒相关服务";
      R('Register/successjump',array($info_succ, U('Services/companyshow')));
    }else{
      R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
    }
  }

  //动感传媒 - 财富通同步返回
  public function companyshow_tenpayreturn(){
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
	    $info_succ = "您已成功购买动感传媒相关服务";
	    R('Register/successjump',array($info_succ, U('Services/companyshow')));
	  }else{
	    R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
	  }	    
	}else{
	  R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
	}
    }else{
      R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/index')));
    }
  }

  //动感传媒 - 财富通异步
  public function companyshow_tenpaynotify(){
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
	    $MediaShowOrder = M('MediaShowOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($MediaShowOrder -> where($where) -> save($data)){

	      //订单相关信息
	      $companyshow_info = $MediaShowOrder -> alias('mso') -> field('mso.csid,mso.ccid_one,mso.ccid_two,mso.mid,mso.name,mso.address,mso.linkman,mso.mobliephone,mso.companyphone,mso.qqcode,mso.keyword,mso.smallpic,mso.bigpic,mso.filename,mso.maketype,mso.remark,msm.months,mso.website') -> join('yesow_media_show_money as msm ON mso.smid = msm.id') -> where(array('ordernum' => $out_trade_no)) -> find();

	      //写主表
	    $MediaShow = M('MediaShow');
	    $show_data = array();
	    $show_data['csid'] = $companyshow_info['csid'];
	    $show_data['ccid_one'] = $companyshow_info['ccid_one'];
	    $show_data['ccid_two'] = $companyshow_info['ccid_two'];
	    $show_data['mid'] = $companyshow_info['mid'];
	    $show_data['name'] = $companyshow_info['name'];
	    $show_data['address'] = $companyshow_info['address'];
	    $show_data['linkman'] = $companyshow_info['linkman'];
	    $show_data['mobliephone'] = $companyshow_info['mobliephone'];
	    $show_data['companyphone'] = $companyshow_info['companyphone'];
	    $show_data['qqcode'] = $companyshow_info['qqcode'];
	    $show_data['keyword'] = $companyshow_info['keyword'];
	    $show_data['remark'] = $companyshow_info['remark'];
	    $show_data['website'] = $companyshow_info['website'];
	    $show_data['maketype'] = $companyshow_info['maketype'];
	    if($companyshow_info['maketype'] == 1){
	      $show_data['image'] = $companyshow_info['smallpic'];
	      $url = C('MEDIA_PIC_PATH_SAVE');
	      $show_data['content'] = '<img src="' . $url . $companyshow_info['bigpic'] . '">';
	    }else{
	      $show_data['image'] = $companyshow_info['filename'];
	    }
	    $show_data['starttime'] = time();
	    $show_data['endtime'] = $show_data['starttime'] + ($companyshow_info['months'] * 30 * 24 * 60 * 60);
	    $show_data['addtime'] = time();
	    $show_data['updatetime'] = time();
	    $show_data['type'] = 1;

	    $MediaShow -> add($show_data);

	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }


  public function companyshow_renew_k99billreturn(){
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
    $MediaShowOrder = M('MediaShowOrder');
    $where = array();
    $where['ordernum'] = $orderId;
    $data = array();
    /////首先进行签名字符串验证
    if(strtoupper($signMsg)==strtoupper($merchantSignMsg)){
      switch($payResult){
	//支付成功
	case "10":
	  $data['status'] = 3;
	  $data['paytype'] = '快钱';
	  //如果更新成功，则写主表
	  if($MediaShowOrder -> where($where) -> save($data)){
	    //订单相关信息
	    $companyshow_info = $MediaShowOrder -> alias('mso') -> field('msm.months,mso.msid') -> join('yesow_media_show_money as msm ON mso.smid = msm.id') -> where(array('ordernum' => $orderId)) -> find();

	     $MediaShow = M('MediaShow');
	    if($MediaShow -> where(array('id' => $companyshow_info['msid'])) -> setInc('endtime', $companyshow_info['months'] * 30 * 24 * 60 * 60)){
	      $info_succ = "您已成功续费动感传媒相关服务";
	      R('Register/successjump',array($info_succ, U('Services/companyshow')));
	    }else{
	      R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
	    }
	  }
	  break;
	default:
	  $data['status'] = 0;
	  $data['paytype'] = '快钱';
	  $MediaShowOrder -> where($where) -> save($data);
	  R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
	  break;
      }
    }else{
      R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
    }
  }

  public function companyshow_renew_alipaynotify(){
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

      $MediaShowOrder = M('MediaShowOrder');
      $where = array();
      $where['ordernum'] = $out_trade_no;
      $data = array();

      //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款  status = 0
      if($_POST['trade_status'] == 'WAIT_BUYER_PAY'){
	$data['status'] = 0;
	$MediaShowOrder -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
      //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货 status = 1
      else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS'){
	//先读取目前的订单状态
	$now_status = $MediaShowOrder -> getFieldByordernum($out_trade_no, 'status');
	//更新订单状态
	$data['status'] = 1;
	$data['paytype'] = '支付宝';
	//如果更新成功，并且订单状态是从未付款到已付款，则写主表
	if($MediaShowOrder -> where($where) -> save($data) && $now_status == 0){

	  $companyshow_info = $MediaShowOrder -> alias('mso') -> field('msm.months,mso.msid') -> join('yesow_media_show_money as msm ON mso.smid = msm.id') -> where(array('ordernum' => $out_trade_no)) -> find();
	  $MediaShow = M('MediaShow');
	  $MediaShow -> where(array('id' => $companyshow_info['msid'])) -> setInc('endtime', $companyshow_info['months'] * 30 * 24 * 60 * 60);
	}
	ob_end_clean();
	echo "success";
      }
      //该判断表示卖家已经发了货，但买家还没有做确认收货的操作 status = 2
      else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS'){
	$data['status'] = 2;
	$MediaShowOrder -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      
      }
      //该判断表示买家已经确认收货，这笔交易完成 status = 3
      else if($_POST['trade_status'] == 'TRADE_FINISHED'){
	$data['status'] = 3;
	$MediaShowOrder -> where($where) -> save($data);
	ob_end_clean();
	echo "success";
      }
    }else{
      ob_end_clean();
      echo "fail";
    }
  }

  public function companyshow_renew_alipayreturn(){
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

    $mid = session(C('USER_AUTH_KEY'));

    //验证成功
    if($verify_result){
      $info_succ = "您已成功续费动感传媒相关服务";
      R('Register/successjump',array($info_succ, U('Services/companyshow')));
    }else{
      R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
    }
  }

  public function companyshow_renew_tenpayreturn(){
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
	    $info_succ = "您已成功续费动感传媒相关服务";
	    R('Register/successjump',array($info_succ, U('Services/companyshow')));
	  }else{
	    R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
	  }	    
	}else{
	  R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/companyshow')));
	}
    }else{
      R('Register/errorjump',array(L('COMPANYSHOW_ERROR'), U('Services/index')));
    }
  }

  public function companyshow_renew_tenpaynotify(){
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
	    $MediaShowOrder = M('MediaShowOrder');
	    $where = array();
	    $where['ordernum'] = $out_trade_no;
	    $data = array();
	    $data['status'] = 3;
	    $data['paytype'] = '财付通';
	    if($MediaShowOrder -> where($where) -> save($data)){

	      $companyshow_info = $MediaShowOrder -> alias('mso') -> field('msm.months,mso.msid') -> join('yesow_media_show_money as msm ON mso.smid = msm.id') -> where(array('ordernum' => $out_trade_no)) -> find();
	      $MediaShow = M('MediaShow');
	      $MediaShow -> where(array('id' => $companyshow_info['msid'])) -> setInc('endtime', $companyshow_info['months'] * 30 * 24 * 60 * 60);
	      ob_end_clean();
	      echo "success";
	    }else{
	      ob_end_clean();
	      echo "fail";
	    }
	  }else{
	    ob_end_clean();
	    echo "fail"; 
	  }
	}else{
	  ob_end_clean();
	echo "fail";
	}
      }else{
	ob_end_clean();
	echo "<br/>" . "认证签名失败" . "<br/>";
	echo $resHandler->getDebugInfo() . "<br>";
      }
    }
  }



}

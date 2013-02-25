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
    $alipay_config['cacert'] = __ROOT__ . 'Public/cacert.pem';
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
	  M('MemberRmb') -> where(array('id' => $mid)) -> setInc('rmb_pay', $total_pee);
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
    $alipay_config['cacert'] = __ROOT__ . 'Public/cacert.pem';
    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    $alipay_config['transport'] = 'http';

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
	  $total_pee = $this -> _get('total_fee');
	  //获取此订单的用户id
	  $mid = $rmb_order -> getFieldByordernum($out_trade_no, 'mid');
	  //更新用户RMB余额
	  $member_rmb -> where(array('id' => $mid)) -> setInc('rmb_pay', $total_pee);
	}
      }
      //重新缓存用户rmb余额
      $member_rmb -> rmbtotal();
      //充值成功的图片
      $this -> assign('pic_name', 'success_tishi.gif');
    }else{
      //充值失败的图片
      $this -> assign('pic_name', 'fail_tishi.gif');
    }
    $this -> display('./member/Tpl/Money/rmbrecharge_four.html'); 
  }
}

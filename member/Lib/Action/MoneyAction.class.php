<?php
class MoneyAction extends CommonAction {

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

  //资金管理首页(RMB管理)
  public function index(){
    $this -> display();
  }

  //e币管理前置方法
  public function _before_eb(){
    $this -> _before_index();
  }

  //e币管理
  public function eb(){
    $this -> display();
  }

  //rmb充值
  public function rmbrecharge(){
    //RMB充值第二步
    if(!empty($_GET['money'])){
      if($_SESSION['verify'] != $this -> _get('verify', 'md5')){
	R('Register/errorjump',array(L('VERIFY_ERROR')));
      }
      if($_GET['money'] <= 0){
	R('Register/errorjump',array(L('MONEY_ERROR')));
      }
      //查询接口信息
      $payport = M('Payport');
      $result_pay = $payport -> field('name,enname') -> where(array('status' => 1)) -> select();
      $this -> assign('result_pay', $result_pay);
      $this -> display('rmbrecharge_two');
      exit();
    }
    //RMB充值第三步
    if(!empty($_POST['paytype'])){
      //查询支付方式中文名称
      $paytype_name = M('Payport') -> getFieldByenname($this -> _post('paytype'), 'name');
      $this -> assign('paytype_name', $paytype_name);
      //根据不同的支付方式，提交到不同的处理方法中
      $this -> assign('payapi', $_POST['paytype'] . 'api');
      //生成订单号
      $orderid = 'new' . time() . mt_rand(1000000,9999999);
      $this -> assign('orderid', $orderid);
      //记录订单信息
      $rmb_order = D('RmbOrder');
      $data = array();
      $data['ordernum'] = $orderid;
      $data['mid'] = session(C('USER_AUTH_KEY'));
      $data['price'] = $this -> _post('money');
      $data['paytype'] = $this -> _post('paytype');
      $data['remark'] = $this -> _post('remark');
      $rmb_order -> create($data);
      $rmb_order -> add();

      $this -> display('rmbrecharge_three');
      exit();
    }
    $this -> display('rmbrecharge_one');
  }

  //处理支付宝充值
  public function alipayapi(){
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
    $notify_url = C('WEBSITE') . "member.php/pay/alipaynotify";
    //页面跳转同步通知页面路径,需http://格式的完整路径，不能加?id=123这类自定义参
    $return_url = C('WEBSITE') . "member.php/pay/alipayreturn";
    //卖家支付宝帐户
    $seller_email = $author['account'];
    //商户订单号
    $out_trade_no = $this -> _post('oid');
    //订单名称
    $subject = '易搜会员中心人民币充值';
    //付款金额
    $price = $this -> _post('price');
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

<?php
class PublicAction extends Action {

  //文件上传，整合kindeditor
  public function uploadfile(){
    //文件保存目录路径
    $save_path = $php_path . C('UPLOAD_PATH') . '/attached/';
    //文件保存目录URL
    $save_url = $php_url . C('SAVE_PATH') . '/attached/';
    //定义允许上传的文件扩展名
    $ext_arr = array(
      'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
      'flash' => array('swf', 'flv'),
      'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
      'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
    );
    //最大文件大小
    $max_size = 1000000;
    $save_path = realpath($save_path) . '/';
    //PHP上传失败
    if (!empty($_FILES['imgFile']['error'])) {
      switch($_FILES['imgFile']['error']){
      case '1':
	$error = '超过php.ini允许的大小。';
	break;
      case '2':
	$error = '超过表单允许的大小。';
	break;
      case '3':
	$error = '图片只有部分被上传。';
	break;
      case '4':
	$error = '请选择图片。';
	break;
      case '6':
	$error = '找不到临时目录。';
	break;
      case '7':
	$error = '写文件到硬盘出错。';
	break;
      case '8':
	$error = 'File upload stopped by extension。';
	break;
      case '999':
      default:
	$error = '未知错误。';
      }
      $this -> alert($error);
    }
    //有上传文件时
    if (empty($_FILES) === false) {
    //原文件名
    $file_name = $_FILES['imgFile']['name'];
    //服务器上临时文件名
    $tmp_name = $_FILES['imgFile']['tmp_name'];
    //文件大小
    $file_size = $_FILES['imgFile']['size'];
    //检查文件名
    if (!$file_name) {
      $this -> alert("请选择文件。");
    }
    //检查目录
    if (@is_dir($save_path) === false) {
      $this -> alert("上传目录不存在。");
    }
    //检查目录写权限
    if (@is_writable($save_path) === false) {
      $this -> alert("上传目录没有写权限。");
    }
    //检查是否已上传
    if (@is_uploaded_file($tmp_name) === false) {
      $this -> alert("上传失败。");
    }
    //检查文件大小
    if ($file_size > $max_size) {
      $this -> alert("上传文件大小超过限制。");
    }
    //检查目录名
    $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
    if (empty($ext_arr[$dir_name])) {
      $this -> alert("目录名不正确。");
    }
    //获得文件扩展名
    $temp_arr = explode(".", $file_name);
    $file_ext = array_pop($temp_arr);
    $file_ext = trim($file_ext);
    $file_ext = strtolower($file_ext);
    //检查扩展名
    if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
      $this -> alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
    }
    //创建文件夹
    if ($dir_name !== '') {
      $save_path .= $dir_name . "/";
      $save_url .= $dir_name . "/";
      if (!file_exists($save_path)) {
	mkdir($save_path);
      }
    }
    $ymd = date("Ymd");
    $save_path .= $ymd . "/";
    $save_url .= $ymd . "/";
    if (!file_exists($save_path)) {
      mkdir($save_path);
    }
    //新文件名
    $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
    //移动文件
    $file_path = $save_path . $new_file_name;
    if (move_uploaded_file($tmp_name, $file_path) === false) {
      $this -> alert("上传文件失败。");
    }
    @chmod($file_path, 0644);
    $file_url = $save_url . $new_file_name;
    header('Content-type: text/html; charset=UTF-8');
    Vendor('JSON');
    $json = new Services_JSON();
    echo $json->encode(array('error' => 0, 'url' => $file_url));
    exit;
    }
  }

    //kindeditor
    public function alert($msg) {
      header('Content-type: text/html; charset=UTF-8');
      Vendor('JSON');
      $json = new Services_JSON();
      echo $json->encode(array('error' => 1, 'message' => $msg));
      exit;
    }

  //登录
  public function login(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);

    //在线QQ客服
    if(S('member_qqonline')){
      $this -> assign('member_qqonline', S('member_qqonline'));
    }else{
      $member_qqonline = $this -> getqqonline();
      $this -> assign('member_qqonline', $member_qqonline);
      S('member_qqonline', $member_qqonline);
    }
    $this -> display();
  }

  //验证登录
  public function checklogin(){
    $member = M('Member');
    $where = array();
    $where['name'] = $this -> _post('name');
    $where['status'] = 1;
    if($result = $member -> field('id,name,password,nickname,name,ischeck,last_login_ip,last_login_time,headico,login_count') -> where($where) -> find()){
      if($result['password'] != $this -> _post('password', 'md5')){
	R('Register/errorjump',array(L('PASSWORD_ERROR')));
      }
      if($result['ischeck'] == 0){
	R('Register/errorjump', array(L('MAIL_CHECK_ERROR'), U('Register/three')));
      }
      session(C('USER_AUTH_KEY'), $result['id']);
      session('name', $result['name']);
      session('username', $result['nickname']);
      session('last_login_ip', $result['last_login_ip']);
      session('last_login_time', $result['last_login_time']);
      session('headico', $result['headico']);
      session('login_count', $result['login_count']);
      //缓存RMB余额 和 会员等级
      D('MemberRmb') -> rmbtotal(session(C('USER_AUTH_KEY')));
      //更新登录信息
      $data['id'] = $result['id'];
      $data['last_login_ip'] = get_client_ip();
      $data['last_login_time'] = time();
      $data['lastest_login_time'] = $result['last_login_time'];
      $data['login_count'] = array('exp', 'login_count+1');
      $member -> save($data);
      if($_POST['type'] == 'notmember'){
	R('Register/successjump',array(L('LOGIN_SUCCESS')));
      }
      if(!empty($_POST['jump_url'])){
	R('Register/successjump',array(L('LOGIN_SUCCESS'), $_POST['jump_url']));
      }else{
	R('Register/successjump',array(L('LOGIN_SUCCESS'), U('Index/index')));
      }
    }else{
      R('Register/errorjump',array(L('NAME_ERROR')));
    }
  }

  //退出登录
  public function logout(){
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      session(C('USER_AUTH_KEY'), null);
      session(null);
      session('[destroy]');
      R('Register/successjump',array(L('LOGOUT_SUCCESS')));
    }else{
      R('Register/errorjump',array(L('LOGOUT_ERROR')));
    }
  }

  //ajax获取包月价格及等级权限
  public function getmonthlymoney(){
    $member_monthly = M('MemberMonthly');
    $where = array();
    $where['mm.type'] = $this -> _get('tid', 'intval');
    $where['mm.lid'] = $this -> _get('lid', 'intval');
    $where['mm.mod'] = $this -> _get('mid', 'intval');
    if($_GET['tid'] == 1){
      //日流量包
      if($_GET['mid'] == 1){
	$result = $member_monthly -> table('yesow_member_monthly as mm') -> field('mm.id,mm.months,mm.marketprice,mm.promotionprice,ml.author_one,ml.author_two,ml.author_three,ml.author_four,ml.author_five,ml.author_six,ml.author_seven,ml.author_eight,ml.author_nine,ml.author_ten,ml.monthly_one_num,ml.monthly_two_num,ml.monthly_three_num') -> join('yesow_member_level as ml ON mm.lid = ml.id') -> where($where) -> order('mm.months ASC') -> select();
      }else{
	//月流量包
	$result = $member_monthly -> table('yesow_member_monthly as mm') -> field('mm.id,mm.months,mm.marketprice,mm.promotionprice,ml.author_one,ml.author_two,ml.author_three,ml.author_four,ml.author_five,ml.author_six,ml.author_seven,ml.author_eight,ml.author_nine,ml.author_ten,ml.monthly_four_num as monthly_one_num,ml.monthly_five_num as monthly_two_num,ml.monthly_six_num as monthly_three_num') -> join('yesow_member_level as ml ON mm.lid = ml.id') -> where($where) -> order('mm.months ASC') -> select();	
      }
    }else{
      //日流量包
      if($_GET['mid'] == 1){
	$result = $member_monthly -> table('yesow_member_monthly as mm') -> field('mm.id,mm.months,mm.marketprice,mm.promotionprice,ml.author_one,ml.author_two,ml.author_three,ml.author_four,ml.author_five,ml.author_six,ml.author_seven,ml.author_eight,ml.author_nine,ml.author_ten,ml.monthly_one_num_area as monthly_one_num,ml.monthly_two_num_area as monthly_two_num,ml.monthly_three_num_area as monthly_three_num') -> join('yesow_member_level as ml ON mm.lid = ml.id') -> where($where) -> order('mm.months ASC') -> select();
      }else{
	$result = $member_monthly -> table('yesow_member_monthly as mm') -> field('mm.id,mm.months,mm.marketprice,mm.promotionprice,ml.author_one,ml.author_two,ml.author_three,ml.author_four,ml.author_five,ml.author_six,ml.author_seven,ml.author_eight,ml.author_nine,ml.author_ten,ml.monthly_four_num_area as monthly_one_num,ml.monthly_five_num_area as monthly_two_num,ml.monthly_six_num_area as monthly_three_num') -> join('yesow_member_level as ml ON mm.lid = ml.id') -> where($where) -> order('mm.months ASC') -> select();
      }
      
    }
    
    echo json_encode($result);
  }

  //ajax获取分站
  public function ajaxgetcsid(){
    $ChildSite = M('ChildSite');
    $result = $ChildSite -> field('id,name') -> order('id ASC') -> select();
    echo json_encode($result);
  }

  //ajax获取分站-没有主站
  public function ajaxgetcsid2(){
    $ChildSite = M('ChildSite');
    $result = $ChildSite -> field('id,name') -> where('id != 18') -> order('id ASC') -> select();
    echo json_encode($result);
  }

  //ajax获取分站页面广告位列表 除去已订购的广告位
  public function ajaxgetchildsiteadvert(){
    $result_temp = M('Advertise') -> field('id,name,width,height') -> where(array('pid' => $this -> _get('id', 'intval'), 'isopen' => 1)) -> select();
    $where_limit = array();
    $where_limit['starttime'] = array('ELT', time());
    $where_limit['endtime'] = array('EGT', time());
    $del_adid_tmp = M('Advert') -> field('adid') -> where($where_limit) -> select();
    $del_adid = array();
    foreach($del_adid_tmp as $value){
      $del_adid[] = $value['adid'];
    }
    foreach($result_temp as $key => $value){
      if(in_array($value['id'], $del_adid)){
	unset($result_temp[$key]);
      }
    }
    $result = array();
    $result[] = array('', '请选择');
    //格式化结果集
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name'] . '(' . $value['width'] . 'x' . $value['height'] . ')');
    }
    echo json_encode($result);
  }

  //ajax获取速查排名情况
  public function ajaxgetsearchrank(){
    //最大排名数
    $RankMoney = M('RankMoney');
    $where_money = array();
    $max_rank = $RankMoney -> where($where_money) -> order('rank DESC') -> getField('rank');
    //已生效排名数
    $SearchRank = M('SearchRank');
    $where_rank = array();
    $where_rank['fid'] = $this -> _post('fid');
    $where_rank['keyword'] = $this -> _post('keyword');
    $where_rank['starttime'] = array('ELT', time());
    $where_rank['endtime'] = array('EGT', time());
    $result_rank_temp = $SearchRank -> field('rank') -> where($where_rank) -> select();
    $result_rank = array();
    foreach($result_rank_temp as $value){
      $result_rank[] = $value['rank'];
    }
    $result = array();
    //创建排名数组
    for($i=1; $i<=$max_rank; $i++){
      if(in_array($i, $result_rank)){
	$result[$i] = true;
      }else{
	$result[$i] = false;
      }
    }
    echo json_encode($result);
  }

  //ajax获取速查排名价格
  public function ajaxgetsearchrankprice(){
    //折扣率
    $RankMoney = M('RankMoney');
    $where = array();
    $where['rank'] = array('EGT', $this -> _post('rank', 'intval'));
    $discount = $RankMoney -> where($where) -> order('rank ASC') -> getField('discount');
    //包月信息
    $SearchRankMonthsMoney = M('SearchRankMonthsMoney');
    $result = $SearchRankMonthsMoney -> field('id,months,ROUND(marketprice*' . (1-$discount) . ',1) as marketprice,ROUND(promotionprice*' . (1-$discount) . ',1) as promotionprice') -> where(array('fid' => $this -> _post('fid', 'intval'))) -> order('months ASC') -> select();
    echo json_encode($result);
  }

  //ajax获取推荐商家排名情况
  public function ajaxgetrecommendcompany(){
    //最大排名数
    $RankMoney = M('RankMoney');
    $where_money = array();
    $max_rank = $RankMoney -> where($where_money) -> order('rank DESC') -> getField('rank');
    $max_rank = $max_rank > 32 ? 32 : $max_rank;
    //已生效排名数
    $RecommendCompany = M('RecommendCompany');
    $where_rank = array();
    $where_rank['fid'] = $this -> _post('fid');
    $where_rank['starttime'] = array('ELT', time());
    $where_rank['endtime'] = array('EGT', time());
    $result_rank_temp = $RecommendCompany -> field('rank') -> where($where_rank) -> select();
    $result_rank = array();
    foreach($result_rank_temp as $value){
      $result_rank[] = $value['rank'];
    }
    $result = array();
    //创建排名数组
    for($i=1; $i<=$max_rank; $i++){
      if(in_array($i, $result_rank)){
	$result[$i] = true;
      }else{
	$result[$i] = false;
      }
    }
    echo json_encode($result);
  }

  //ajax获取推荐商家价格
  public function ajaxgetrecommendcompanyprice(){
    //折扣率
    $RankMoney = M('RankMoney');
    $where = array();
    $where['rank'] = array('EGT', $this -> _post('rank', 'intval'));
    $discount = $RankMoney -> where($where) -> order('rank ASC') -> getField('discount');
    //包月信息
    $RecommendCompanyMonthsMoney = M('RecommendCompanyMonthsMoney');
    $result = $RecommendCompanyMonthsMoney -> field('id,months,ROUND(marketprice*' . (1-$discount) . ',1) as marketprice,ROUND(promotionprice*' . (1-$discount) . ',1) as promotionprice') -> where(array('fid' => $this -> _post('fid', 'intval'))) -> order('months ASC') -> select();
    echo json_encode($result);
  }

  //ajax更改会员发送邮箱设置
  public function ajaxchangeemailsetting(){
    $MemberEmailSetting = M('MemberEmailSetting');
    if(!$MemberEmailSetting -> create()){
      $this -> error($MemberEmailSetting -> getError());
    }
    if($MemberEmailSetting -> save()){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //添加邮箱设置
  public function ajaxaddemailsetting(){
    $MemberEmailSetting = M('MemberEmailSetting');
    if(!$MemberEmailSetting -> create()){
      $this -> error($MemberEmailSetting -> getError());
    }
    $MemberEmailSetting -> mid = session(C('USER_AUTH_KEY'));
    $MemberEmailSetting -> addtime = time();
    if($MemberEmailSetting -> add()){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //删除邮箱设置
  public function ajaxdelemailsetting(){
    $MemberEmailSetting = M('MemberEmailSetting');
    if($MemberEmailSetting -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //获得底部关于我们
  public function getfooternav(){
    $aboutus =  M('Aboutus');
    return $aboutus -> field('id,title') -> order('sort ASC') -> select();
  }

  //获得QQ客服
  public function getqqonline(){
    $qqonline = M('Qqonline');
    $result = M('QqonlineType') -> field('id,name') -> select();
    foreach($result as $key => $value){
      $result[$key]['qq'] = $qqonline -> field('qqcode,nickname') -> where(array('tid' => $value['id'], 'csid' => array('exp', 'is null'))) -> select();
    }
    return $result;
  }

  //快钱支付
  public function k99bill_pay($pageUrl, $orderId, $rmb_amount, $productName){
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
    //$pageUrl = C('WEBSITE') . "member.php/pay/qqonline_k99billreturn";
    //网关版本.固定值
    $version = "v2.0";
    //语言种类.固定选择值。
    $language = "1";
    //签名类型.固定值
    $signType = "1";
    //商户订单号
    //$orderId = $this -> _get('oid');
    //订单金额
    //$rmb_amount = M('QqonlineOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
    $orderAmount = $rmb_amount * 100;
    //订单提交时间
    $orderTime = date('YmdHis');
    //商品名称
    //$productName = "易搜会员中心在线QQ购买";
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
    exit();
  }

  //支付宝支付
  public function alipay_pay($notify_url, $return_url, $out_trade_no, $subject, $price){
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
    //$notify_url = C('WEBSITE') . "member.php/pay/qqonline_alipaynotify";
    //页面跳转同步通知页面路径,需http://格式的完整路径，不能加?id=123这类自定义参
    //$return_url = C('WEBSITE') . "member.php/pay/qqonline_alipayreturn";
    //卖家支付宝帐户
    $seller_email = $author['account'];
    //商户订单号
    //$out_trade_no = $this -> _get('oid');
    //订单名称
    //$subject = '易搜会员中心在线QQ购买';
    //付款金额，根据订单号查询出来
    //$price = M('QqonlineOrder') -> getFieldByordernum($this -> _get('oid'), 'price');
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
    exit();
  }

  //财付通支付
  public function tenpay_pay($return_url, $notify_url, $out_trade_no, $desc, $order_price){
    $payport = M('Payport');
    //查询认证信息
    $author = $payport -> field('account,key1') -> where(array('enname' => 'tenpay')) -> find();
    Vendor('tenpay.RequestHandler','','.class.php');

    $partner = $author['account'];  //财付通商户号
    $key = $author['key1'];  //财付通密钥
    //$return_url = C('WEBSITE') . "member.php/pay/qqonline_tenpayreturn";	//同步返回地址
    //$notify_url = C('WEBSITE') . "member.php/pay/qqonline_tenpaynotify";  //异步通知地址
    //$out_trade_no = $this -> _get('oid'); //订单号
    //$desc = '易搜会员中心在线QQ购买';  //商品名称
    //$order_price = M('QqonlineOrder') -> getFieldByordernum($this -> _get('oid'), 'price');  //商品价格,根据订单号查寻
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
    exit();
  }
}


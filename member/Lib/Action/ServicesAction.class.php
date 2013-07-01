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

  //在线QQ管理
  public function qqonline(){
    $this -> display();
  }

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

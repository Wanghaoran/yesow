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
    dump($_SESSION['member_search_send_list']);
    $this -> display();
  }

  //执行发送
  public function tosendsms(){
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
        <p><img src="' . __ROOT__ .'/Public/member/images/user/loading.gif" width="25" height="25" border="0" />正在发送，请耐心等待...</p>
	</div></body></html>';
    echo $sHtml;
    flush();//输出送出的缓冲内容

    if($_POST['phonetype'] == 'list'){
      dump('发送号码：' . $_POST['sendnumber']);
      dump('发送内容：' . $_POST['content']);
      if($_POST['savegroup'] == 'true'){
	dump('保存通讯录的名称：' . $_POST['savegroupname']);
      }
    
    }else if($_POST['phonetype'] == 'group'){
      dump('发送内容：' . $_POST['content']);
      dump('发送号码簿：' . $_POST['phonegroup']);
      
    }
    dump($_POST); 
  }

  //搜索号码
  public function searchcompanyphone(){
    if(!empty($_GET['keyword'])){
      $keyword = $this -> _get('keyword');
      $company = M('Company');
      $where = array();
      $where['delaid']  = array('exp', 'is NULL');
      $where['mobilephone'] = array('neq', '');
      $where['_string'] = "( name LIKE '%{$keyword}%' ) OR ( address LIKE '%{$keyword}%' ) OR ( manproducts LIKE '%{$keyword}%' ) OR ( mobilephone LIKE '%{$keyword}%' ) OR ( email LIKE '%{$keyword}%' ) OR ( linkman LIKE '%{$keyword}%' ) OR ( companyphone LIKE '%{$keyword}%' ) OR ( qqcode LIKE '%{$keyword}%' ) OR ( website LIKE '%{$keyword}%' )";
      if($_GET['searchscope'] == 'city'){
	$where['csid'] = $this -> _get('csid', 'intval');
	if(!empty($_GET['csaid'])){
	  $where['csaid'] = $this -> _get('csaid', 'intval');
	}
      }

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
    $this -> display();
  }

  //短信群发薄
  public function sendsmsgroup(){
    $this -> display();
  }

  //短信群发薄详情
  public function sendsmsgrouplist(){
    $this -> display();
  }

  //邮件群发业务
  public function _before_email(){
    $this -> _before_index();
  }
  public function email(){
  
  }
}

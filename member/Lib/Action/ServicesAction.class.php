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
    dump($_POST);
    
    
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

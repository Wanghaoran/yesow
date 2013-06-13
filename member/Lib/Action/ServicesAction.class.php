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

  //邮件群发业务
  public function _before_email(){
    $this -> _before_index();
  }
  public function email(){
  
  }
}

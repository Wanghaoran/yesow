<?php
class PublicAction extends Action {

  //ajax获取分站下地区的区号
  public function getchildsitecode(){
    $code = M('ChildSiteArea') -> getFieldByid($this -> _get('id', 'intval'), 'code');
    echo preg_replace('/[a-zA-Z]/i', '', $code);
  }

  //成功跳转
  public function successjump($title, $url="", $time=3){
    $this -> assign('title', $title);
    if(empty($url)){
      $r_url = $_SERVER["HTTP_REFERER"];
      $this -> assign('url', $r_url);
    }else{
      $r_url =  $url;
      $this -> assign('url', $r_url);
    }
    $this -> assign('time', $time);
    $this -> assign('status', 1);
    $this -> display('./index/Tpl/Public/jumpurl.html');
    exit();
  }

  //失败跳转
  public function errorjump($title, $url="", $time=3){
    $this -> assign('title', $title);
    if(empty($url)){
      $r_url = $_SERVER["HTTP_REFERER"];
      $this -> assign('url', $r_url);
    }else{
      $r_url =  $url;
      $this -> assign('url', $r_url);
    }
    $this -> assign('time', $time);
    $this -> assign('status', 0);
    $this -> display('./index/Tpl/Public/jumpurl.html');
    exit();
  }

  //按钮跳转
  public function infojump($title, $url=""){
    $this -> assign('title', $title);
    if(empty($url)){
      $this -> assign('url', $_SERVER["HTTP_REFERER"]);
    }else{
      $this -> assign('url', $url);
    }
    $this -> display('./index/Tpl/Public/infojump.html');
    exit();
  }

  //ajax验证登录
  public function checkajaxlogin(){
    $member = M('Member');
    $where = array();
    $where['name'] = $this -> _post('name');
    $where['status'] = 1;
    if($result = $member -> field('id,name,password,nickname,name,ischeck,last_login_ip,last_login_time,headico,login_count') -> where($where) -> find()){
      if($result['password'] != $this -> _post('password', 'md5')){
	R('Public/errorjump',array(L('PASSWORD_ERROR')));
      }
      if($result['ischeck'] == 0){
	R('Public/errorjump', array(L('MAIL_CHECK_ERROR'), U('Register/three')));
      }
      session(C('USER_AUTH_KEY'), $result['id']);
      session('name', $result['name']);
      session('username', $result['nickname']);
      session('last_login_ip', $result['last_login_ip']);
      session('last_login_time', $result['last_login_time']);
      session('headico', $result['headico']);
      session('login_count', $result['login_count']);
      //更新登录信息
      $data['id'] = $result['id'];
      $data['last_login_ip'] = get_client_ip();
      $data['last_login_time'] = time();
      $data['lastest_login_time'] = $result['last_login_time'];
      $data['login_count'] = array('exp', 'login_count+1');
      $member -> save($data);
      R('Public/successjump',array(L('LOGIN_SUCCESS')));
    }else{
      R('Public/errorjump',array(L('NAME_ERROR')));
    }
  }

  //ajax获取会员查看速查资料所需信息
  public function ajaxmembercompany(){
    echo '您的会员等级为[普通会员]，今天可以查看两条免费信息。目前剩余0条，本 页面将消费0.1元请确认。 <br /><a onclick="quitview();">【取消】</a><a onclick="confirmview();">【确认查看】</a>';
  }

  //ajax确认查看速查资料
  public function ajaxconfirmview(){
    //写会员-速查对应表
    $member_company = M('MemberCompany');
    $data = array();
    $data['cid'] = $this -> _get('cid', 'intval');
    $data['mid'] = session(C('USER_AUTH_KEY'));
    $data['time'] = time();
    if($member_company -> add($data)){
      echo 1;
    }else{
      echo 0;
    }
  }

  //ajax获取搜索关键词返回
  public function ajaxkeyword(){
    $keyword = $this -> _get('keyword');
    $audit_serach = M('AuditSearchKeyword');
    $where = array();
    $where['name'] = array('LIKE', '%' . $keyword . '%');
    $temp_result = $audit_serach -> field('name') -> where($where) -> limit(10) -> order('length(name)') -> select();
    //整理结果数组
    $result = array();
    foreach($temp_result as $value){
      $result[] = $value['name'];
    }
    echo json_encode($result);
  }
}

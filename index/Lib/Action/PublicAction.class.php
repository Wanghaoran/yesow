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
      //缓存RMB余额 和 会员等级
      D('Member://MemberRmb') -> rmbtotal();
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
    $level = M('MemberLevel');
    //查询会员等级免费查看条数 和 查看一条速查信息，扣款数
    $level_info = $level -> field('freecompany,rmb_one') -> find(session('member_level_id'));
    //查询此会员今日免费剩余条数
    $where = array();
    $where['mid'] = session(C('USER_AUTH_KEY'));
    $where['time'] = date('Ymd');
    $free_company = M('MemberFreeCompany') -> where($where) -> count();

    //如果还未达到免费册数，则此次不收费
    if($free_company < $level_info['freecompany']){
      $number = $level_info['freecompany'] - $free_company;
      $const = 0.00;
    }else{
      $number = 0;
      $const = $level_info['rmb_one'];
    }
    echo '您的会员等级为[' . $_SESSION['member_level_name'] . ']，今天可以查看 ' . $level_info['freecompany'] . ' 条免费信息。目前剩余 ' . $number . ' 条，本 页面将消费 ' . $const . ' 元请确认。 <br /><a onclick="quitview();">【取消】</a><a onclick="confirmview();">【确认查看】</a>';
  }

  //ajax确认查看速查资料
  public function ajaxconfirmview(){
    $level = M('MemberLevel');
    $freecompany = M('MemberFreeCompany');
    //查询会员等级免费查看条数 和 查看一条速查信息，扣款数
    $level_info = $level -> field('freecompany,rmb_one') -> find(session('member_level_id'));
    //查询此会员今日免费剩余条数
    $where = array();
    $where['mid'] = session(C('USER_AUTH_KEY'));
    $where['time'] = date('Ymd');
    $free_company = $freecompany -> where($where) -> count();

    //如果还未达到免费册数，则此次不收费
    if($free_company < $level_info['freecompany']){
      //记录这次免费信息
      $data = array();
      $data['mid'] = session(C('USER_AUTH_KEY'));
      $data['cid'] = $this -> _get('cid', 'intval');
      $data['time'] = date('Ymd');
      $freecompany -> add($data);
    }else{
      //否则在会员RMB表中扣除相应余额
      $const = $level_info['rmb_one'];
      //先从 兑换RMB 字段中扣，在从充值RMB字段 中扣
      $rmb = D('Member://MemberRmb');
      $price = $rmb -> field('rmb_pay,rmb_exchange') -> find(session(C('USER_AUTH_KEY')));
      //如果 兑换RMB余额足够支付 此次费用
      if($price['rmb_exchange'] - $const >= 0){
	$data_rmb = array();
	$data_rmb['mid'] = session(C('USER_AUTH_KEY'));
	$data_rmb['rmb_pay'] = $price['rmb_pay'];
	$data_rmb['rmb_exchange'] = $price['rmb_exchange'] - $const;
	$rmb -> save($data_rmb);
      }else{
	//如果兑换RMB不足够支付此次信息，则用充值RMB支付
	//计算差值
	$fee = abs($price['rmb_exchange'] - $const);
	//如果差值不够支付，则退出执行
	if($price['rmb_pay'] < $fee){
	  echo 0;
	  exit();
	}
	$data_rmb = array();
	$data_rmb['mid'] = session(C('USER_AUTH_KEY'));
	$data_rmb['rmb_pay'] = $price['rmb_pay'] - $fee;
	$data_rmb['rmb_exchange'] = 0;
	$rmb -> save($data_rmb);
	//更新会员余额和等级
	$rmb -> rmbtotal(session(C('USER_AUTH_KEY')));
      }
    }

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

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

  //ajax验证登录前置操作
  public function _before_checkajaxlogin(){
    $this -> _before_authorprompt();
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
      D('member://MemberRmb') -> rmbtotal(session(C('USER_AUTH_KEY')));
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
    $isfree = false;//是否是免费查看
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
      $isfree = true;
    }else{
      //否则在会员RMB表中扣除相应余额
      $const = $level_info['rmb_one'];
      //先从 兑换RMB 字段中扣，在从充值RMB字段 中扣
      $rmb = D('member://MemberRmb');
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
      }
      $session_uid = session(C('USER_AUTH_KEY'));
      //更新会员余额和等级
      $rmb -> rmbtotal($session_uid);
      //查询此条速查信息的地域信息，用于记录日志
      $cid = $this -> _get('cid', 'intval');
      $companyname = M('Company') -> getFieldByid($cid, 'name');
      $companyname = msubstr($companyname, 0, 6);
      //写RMB消费日志
      $log_content = "查看速查名片详细内容一次性扣除 [<span style='color:blue'>{$companyname}</span>]";
      D('member://MemberRmbDetail') -> writelog($session_uid, $log_content, '消费', '-' . $const);
    }

    //写会员-速查对应表
    $member_company = M('MemberCompany');
    $data = array();
    $data['cid'] = $this -> _get('cid', 'intval');
    $data['mid'] = session(C('USER_AUTH_KEY'));
    //如果是免费信息，则让时间立刻失效
    if($isfree){
      //查询设置的有效时间
      $viewtime = M('CompanySetup') -> getFieldByname('viewtime', 'value');
      $data['time'] = time() - ($viewtime*60*60 - 6);
    }else{
      $data['time'] = time();
    }
    if($member_company -> add($data)){
      if($isfree){
	echo 2;
      }else{
	echo 1;
      }
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

  //ajax获取单个下载扣费信息
  public function ajaxonedownload(){
    $member_level = M('MemberLevel');
    $result = $member_level -> field('rmb_three,author_seven') -> find(session('member_level_id'));
    //查帐户余额
    $money = D('member://MemberRmb') -> getrmbtotal(session(C('USER_AUTH_KEY')));
    $result['money'] = $money['total'];
    echo json_encode($result);
  }

  //ajax单个下载(记录日志)
  public function doonedownload_log(){
    //查出公司名
    $company = M('Company') -> getFieldByid($this -> _get('cid', 'intval'), 'name');
    //扣费
    $const = M('MemberLevel') -> getFieldByid(session('member_level_id'), 'rmb_three');
    //先从 兑换RMB 字段中扣，在从充值RMB字段 中扣
    $rmb = D('member://MemberRmb');
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
	exit();
      }
      $data_rmb = array();
      $data_rmb['mid'] = session(C('USER_AUTH_KEY'));
      $data_rmb['rmb_pay'] = $price['rmb_pay'] - $fee;
      $data_rmb['rmb_exchange'] = 0;
      $rmb -> save($data_rmb);
    }
    $session_uid = session(C('USER_AUTH_KEY'));
    //更新会员余额和等级
    $rmb -> rmbtotal($session_uid);
    //公司名称，用于记录日志
    $companyname = msubstr($company, 0, 6);
    //写RMB消费日志
    $log_content = "在详细页下载一条名片详细内容 [<span style='color:blue'>{$companyname}</span>]";
    D('member://MemberRmbDetail') -> writelog($session_uid, $log_content, '消费', '-' . $const);
  }

  //ajax单个下载(生成下载)
  public function doonedownload_down(){
    //查出资料
    $result = M('Company') -> table('yesow_company as c') -> field('c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,cs.name as csname,csa.name as csaname,cc.name as ccname,c.updatetime') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> where(array('c.id' => $this -> _get('cid', 'intval'))) -> find();
    //生成下载信息
    header("Content-Type: application/force-download");
    $filename = date('YmdHis');
    header("Content-Disposition: attachment; filename={$filename}.txt");
    $updatetime = date('Y-m-d H:i:s', $result['updatetime']);
    $content_download = "-------------------------------------\r\n商家导出信息\r\n-------------------------------------\r\n{$result['name']}\r\n公司地址:{$result['address']}\r\n主营产品:{$result['manproducts']}\r\n公司电话:{$result['companyphone']}\r\n移动电话:{$result['mobilephone']}\r\n联系人:{$result['linkman']}\r\n电子邮件:{$result['email']}\r\nQQ:{$result['qqcode']}\r\n所在地:{$result['csname']} - {$result['csaname']}\r\n主营类别:{$result['ccname']}\r\n更新时间:{$updatetime}\r\n";
    echo $content_download;
  }

  //ajax获取批量结果下载扣费信息
  public function ajaxindexdownload(){
    $member_level = M('MemberLevel');
    //构造id数组
    $id_arr = explode(',', $_POST['id_str']);
    //首页结果数量
    $num = empty($_GET['cid']) ? 0 : count($id_arr);
    //查询此会员等级有无此权限
    $result = $member_level -> field('rmb_three,author_eight') -> find(session('member_level_id'));
    //查帐户余额
    $money = D('member://MemberRmb') -> getrmbtotal(session(C('USER_AUTH_KEY')));
    $result['money'] = $money['total'];
    //计算本次所需费用
    $const = $num * $result['rmb_three'];
    $result['num'] = $num;
    //如果账户余额足够本次扣费
    if($const <= $result['money']){
      //计算扣费后余额
      $result['balance'] = $result['money'] - $const;
      //余额足够支付费用
      $result['enough'] = 1;
      //需要下载的id字符串
      $result['id_string'] = $this -> _post('id_str');
      $result['consts'] = $const;
    }else{
      //余额不够支付费用
      $result['enough'] = 0;
      //计算实际可下载的条数,最少给用户留2元余额
      $result['listnum'] = floor(($result['money'] - 2) / $result['rmb_three']);
      //计算此次下载后用户余额
      $result['balance'] = $result['money'] - $result['listnum'] * $result['rmb_three'];
      //实际需要费用
      $result['consts'] = $result['listnum'] * $result['rmb_three'];
      //需要下载的id字符串
      $down_arr = array_slice($id_arr, 0, $result['listnum']);
      $result['id_string'] = '';
      foreach($down_arr as $key => $value){
	if($key == 0){
	  $result['id_string'] .= $value;	  
	}else{
	  $result['id_string'] .= ',' . $value;
	}
      }
    }
    echo json_encode($result);
  }

  //ajax批量下载(记录日志)
  public function doindexdownload_log(){
    $member_level = M('MemberLevel');
    //获取每条价格
    $price_ever = $member_level -> getFieldByid(session('member_level_id'), 'rmb_three');
    //构造id数组
    $id_arr = explode(',', $_GET['id_str']);
    //总条数
    $count_arr = count($id_arr);
    //总费用
    $const = $count_arr * $price_ever;

    //先从 兑换RMB 字段中扣，在从充值RMB字段 中扣
    $rmb = D('member://MemberRmb');
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
	echo 1;
	return;
      }
      $data_rmb = array();
      $data_rmb['mid'] = session(C('USER_AUTH_KEY'));
      $data_rmb['rmb_pay'] = $price['rmb_pay'] - $fee;
      $data_rmb['rmb_exchange'] = 0;
      $rmb -> save($data_rmb);
    }
    $session_uid = session(C('USER_AUTH_KEY'));
    //更新会员余额和等级
    $rmb -> rmbtotal($session_uid);
    //写RMB消费日志
    $log_content = "在搜索结果页<span style='color:blue'>批量下载</span>{$count_arr}条名片详细信息 [<span style='color:blue'>{$_GET['keyword']}</span>]";
    D('member://MemberRmbDetail') -> writelog($session_uid, $log_content, '消费', '-' . $const);
    
  }


  //ajax批量下载(生成下载)
  public function doindexdownload_down(){
    //查出资料
    $result = M('Company') -> table('yesow_company as c') -> field('c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,cs.name as csname,csa.name as csaname,cc.name as ccname,c.updatetime') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> where(array('c.id' => array('IN', $_GET['id_str']))) -> select();

    //生成下载信息
    $content_download = '';
    $i = 1;
    foreach($result as $value){
      $updatetime = date('Y-m-d H:i:s', $value['updatetime']);
      $content_download .= "-------------------------------------\r\n({$i})商家导出信息\r\n-------------------------------------\r\n{$value['name']}\r\n公司地址:{$value['address']}\r\n主营产品:{$value['manproducts']}\r\n公司电话:{$value['companyphone']}\r\n移动电话:{$value['mobilephone']}\r\n联系人:{$value['linkman']}\r\n电子邮件:{$value['email']}\r\nQQ:{$value['qqcode']}\r\n所在地:{$value['csname']} - {$value['csaname']}\r\n主营类别:{$value['ccname']}\r\n更新时间:{$updatetime}\r\n\r\n";
      $i++;
    }
    
    header("Content-Type: application/force-download");
    $filename = date('YmdHis');
    header("Content-Disposition: attachment; filename={$filename}.txt");
    echo $content_download;
  
  }

  //ajax获取全部搜索结果下载扣费信息
  public function ajaxalldownload(){
    $member_level = M('MemberLevel');
    //结果数量
    $num = $this -> _get('num', 'intval');
    //查询此会员等级有无此权限
    $result = $member_level -> field('rmb_three,author_nine') -> find(session('member_level_id'));
    //查帐户余额
    $money = D('member://MemberRmb') -> getrmbtotal(session(C('USER_AUTH_KEY')));
    $result['money'] = $money['total'];
    //计算本次所需费用
    $const = $num * $result['rmb_three'];
    //如果账户余额足够本次扣费
    if($const <= $result['money']){
      //计算扣费后余额
      $result['balance'] = $result['money'] - $const;
      //余额足够支付费用
      $result['enough'] = 1;
      $result['consts'] = $const;
      //结果数量
      $result['num'] = $num;
    }else{
      //余额不够支付费用
      $result['enough'] = 0;
      //计算实际可下载的条数,最少给用户留2元余额
      $result['listnum'] = floor(($result['money'] - 2) / $result['rmb_three']);
      //计算此次下载后用户余额
      $result['balance'] = $result['money'] - $result['listnum'] * $result['rmb_three'];
      //支付费用
      $result['consts'] = $result['listnum'] * $result['rmb_three'];
      //结果数量
      $result['num'] = $num;
    }
    echo json_encode($result);
  }

  //ajax全部结果下载(记录日志)
  public function doalldownload_log(){
    //不需要分页查询所有数据
    $result = R('Company/search_company', array($this -> _get('keyword'), false, true));
    
    $member_level = M('MemberLevel');
    //获取每条价格
    $price_ever = $member_level -> getFieldByid(session('member_level_id'), 'rmb_three');
    //总费用
    $const = $result['count'] * $price_ever;
    //查帐户余额
    $money = D('member://MemberRmb') -> getrmbtotal(session(C('USER_AUTH_KEY')));
    //如果账户余额不够支付所有信息，则计算可以下载的数量
    if($money['total'] < $const){
      $result['count'] = floor(($money['total'] - 2) / $price_ever);
      //更新总费用
      $const = $result['count'] * $price_ever;

    }
    //先从 兑换RMB 字段中扣，在从充值RMB字段 中扣
    $rmb = D('member://MemberRmb');
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
	echo 1;
      }
      $data_rmb = array();
      $data_rmb['mid'] = session(C('USER_AUTH_KEY'));
      $data_rmb['rmb_pay'] = $price['rmb_pay'] - $fee;
      $data_rmb['rmb_exchange'] = 0;
      $rmb -> save($data_rmb);
    }
    $session_uid = session(C('USER_AUTH_KEY'));
    //更新会员余额和等级
    $rmb -> rmbtotal($session_uid);
    //写RMB消费日志
    $log_content = "在搜索结果页<span style='color:blue'>全部下载</span>{$result['count']}条名片详细信息 [<span style='color:blue'>{$_GET['keyword']}</span>]";
    D('member://MemberRmbDetail') -> writelog($session_uid, $log_content, '消费', '-' . $const);

    
  }

  //ajax全部结果下载(生成下载)
  public function doalldownload_down(){
    //不需要分页查询所有数据
    $result = R('Company/search_company', array($this -> _get('keyword'), false, true));
    //如果账户余额不够支付所有信息，则删除部分信息
    $result['result'] = array_slice($result['result'], 0, $_GET['num']);
    //生成下载信息
    $content_download = '';
    $i = 1;
    foreach($result['result'] as $value){
      $updatetime = date('Y-m-d H:i:s', $value['updatetime']);
      $content_download .= "-------------------------------------\r\n({$i})商家导出信息\r\n-------------------------------------\r\n{$value['name']}\r\n公司地址:{$value['address']}\r\n主营产品:{$value['manproducts']}\r\n公司电话:{$value['companyphone']}\r\n移动电话:{$value['mobilephone']}\r\n联系人:{$value['linkman']}\r\n电子邮件:{$value['email']}\r\nQQ:{$value['qqcode']}\r\n所在地:{$value['csname']} - {$value['csaname']}\r\n主营类别:{$value['ccname']}\r\n更新时间:{$updatetime}\r\n\r\n";
      $i++;
    }
    
    header("Content-Type: application/force-download");
    $filename = date('YmdHis');
    header("Content-Disposition: attachment; filename={$filename}.txt");
    echo $content_download;
  
  }

  //ajax获取单个复制扣费信息
  public function ajaxonecopy(){
    $member_level = M('MemberLevel');
    $result = $member_level -> field('rmb_two,author_six') -> find(session('member_level_id'));
    //查帐户余额
    $money = D('member://MemberRmb') -> getrmbtotal(session(C('USER_AUTH_KEY')));
    $result['money'] = $money['total'];
    echo json_encode($result);
  }

  //ajax单个复制
  public function doajaxonecopy(){
    //查出资料
    $result = M('Company') -> table('yesow_company as c') -> field('c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,cs.name as csname,csa.name as csaname,cc.name as ccname,c.updatetime') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> where(array('c.id' => $this -> _get('cid', 'intval'))) -> find();
    //扣费
    $const = M('MemberLevel') -> getFieldByid(session('member_level_id'), 'rmb_two');
    //先从 兑换RMB 字段中扣，在从充值RMB字段 中扣
    $rmb = D('member://MemberRmb');
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
	exit();
      }
      $data_rmb = array();
      $data_rmb['mid'] = session(C('USER_AUTH_KEY'));
      $data_rmb['rmb_pay'] = $price['rmb_pay'] - $fee;
      $data_rmb['rmb_exchange'] = 0;
      $rmb -> save($data_rmb);
    }
    $session_uid = session(C('USER_AUTH_KEY'));
    //更新会员余额和等级
    $rmb -> rmbtotal($session_uid);
    //公司名，用于记录消费日志
    $companyname = msubstr($result['name'], 0, 6);
    //写RMB消费日志
    $log_content = "在详细页复制一条名片详细内容 [<span style='color:blue'>{$companyname}</span>]";
    D('member://MemberRmbDetail') -> writelog($session_uid, $log_content, '消费', '-' . $const);
    
    //生成下载信息
    $updatetime = date('Y-m-d H:i:s', $result['updatetime']);
    $content_download = "-------------------------------------\r\n商家导出信息\r\n-------------------------------------\r\n{$result['name']}\r\n公司地址:{$result['address']}\r\n主营产品:{$result['manproducts']}\r\n公司电话:{$result['companyphone']}\r\n移动电话:{$result['mobilephone']}\r\n联系人:{$result['linkman']}\r\n电子邮件:{$result['email']}\r\nQQ:{$result['qqcode']}\r\n所在地:{$result['csname']} - {$result['csaname']}\r\n主营类别:{$result['ccname']}\r\n更新时间:{$updatetime}\r\n";
    echo $content_download;
  
  }

  //批量操作页面
  public function batch(){
    //不需要分页查询所有数据
    $result = R('Company/search_company', array($this -> _get('keyword'), false, true));
    $this -> assign('result_batch', $result['result']);
    //搜索关键词
    $this -> assign('keyword', $result['keyword']);
    //总条数
    $this -> assign('count', $result['count']);
    $this -> display();
  }

  //ajax批量复制确认信息
  public function ajaxbatchcopy(){
    $member_level = M('MemberLevel');
    //构造id数组
    $id_arr = explode(',', $_GET['cid']);
    //首页结果数量
    $num = empty($_GET['cid']) ? 0 : count($id_arr);
    //查询此会员等级有无此权限
    $result = $member_level -> field('rmb_two,author_ten') -> find(session('member_level_id'));
    //查帐户余额
    $money = D('member://MemberRmb') -> getrmbtotal(session(C('USER_AUTH_KEY')));
    $result['money'] = $money['total'];
    //计算本次所需费用
    $const = $num * $result['rmb_two'];
    $result['num'] = $num;
    //如果账户余额足够本次扣费
    if($const <= $result['money']){
      //计算扣费后余额
      $result['balance'] = $result['money'] - $const;
      //余额足够支付费用
      $result['enough'] = 1;
      //需要复制的id字符串
      $result['id_string'] = $this -> _get('cid');
      $result['consts'] = $const;
    }else{
      //余额不够支付费用
      $result['enough'] = 0;
      //计算实际可下载的条数,最少给用户留2元余额
      $result['listnum'] = floor(($result['money'] - 2) / $result['rmb_two']);
      //计算此次下载后用户余额
      $result['balance'] = $result['money'] - $result['listnum'] * $result['rmb_two'];
      //实际需要费用
      $result['consts'] = $result['listnum'] * $result['rmb_two'];
      //需要下载的id字符串
      $down_arr = array_slice($id_arr, 0, $result['listnum']);
      $result['id_string'] = '';
      foreach($down_arr as $key => $value){
	if($key == 0){
	  $result['id_string'] .= $value;	  
	}else{
	  $result['id_string'] .= ',' . $value;
	}
      }
    }
    echo json_encode($result);
  }

  //ajax批量复制
  public function doajaxbatchcopy(){
    $member_level = M('MemberLevel');
    //获取每条价格
    $price_ever = $member_level -> getFieldByid(session('member_level_id'), 'rmb_two');
    //构造id数组
    $id_arr = explode(',', $_GET['cid']);
    //总条数
    $count_arr = count($id_arr);
    //总费用
    $const = $count_arr * $price_ever;

    //先从 兑换RMB 字段中扣，在从充值RMB字段 中扣
    $rmb = D('member://MemberRmb');
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
	exit();
      }
      $data_rmb = array();
      $data_rmb['mid'] = session(C('USER_AUTH_KEY'));
      $data_rmb['rmb_pay'] = $price['rmb_pay'] - $fee;
      $data_rmb['rmb_exchange'] = 0;
      $rmb -> save($data_rmb);
    }
    $session_uid = session(C('USER_AUTH_KEY'));
    //更新会员余额和等级
    $rmb -> rmbtotal($session_uid);
    //写RMB消费日志
    $log_content = "在搜索结果页批量复制{$count_arr}条名片详细信息 [<span style='color:blue'>{$_GET['keyword']}</span>]";
    D('member://MemberRmbDetail') -> writelog($session_uid, $log_content, '消费', '-' . $const);

    //查出资料
    $result = M('Company') -> table('yesow_company as c') -> field('c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,cs.name as csname,csa.name as csaname,cc.name as ccname,c.updatetime') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> where(array('c.id' => array('IN', $_GET['cid']))) -> select();

    //生成复制信息
    $content_download = '';
    $i = 1;
    foreach($result as $value){
      $updatetime = date('Y-m-d H:i:s', $value['updatetime']);
      $content_download .= "-------------------------------------\r\n({$i})商家导出信息\r\n-------------------------------------\r\n{$value['name']}\r\n公司地址:{$value['address']}\r\n主营产品:{$value['manproducts']}\r\n公司电话:{$value['companyphone']}\r\n移动电话:{$value['mobilephone']}\r\n联系人:{$value['linkman']}\r\n电子邮件:{$value['email']}\r\nQQ:{$value['qqcode']}\r\n所在地:{$value['csname']} - {$value['csaname']}\r\n主营类别:{$value['ccname']}\r\n更新时间:{$updatetime}\r\n\r\n";
      $i++;
    }
    echo $content_download;
  }

  //温馨提示页面 前置操作
  public function _before_authorprompt(){
    //分站信息
    if(S('header_child_site')){
      $this -> assign('header_child_site', S('header_child_site'));
    }else{
      $header_child_site = $this -> getchildsite();
      $this -> assign('header_child_site', $header_child_site);
      S('header_child_site', $header_child_site);
    }
  }

  //权限不足 - 温馨提示页面
  public function authorprompt(){
    //计算拥有此权限最低的用户等级
    $member_level = M('MemberLevel');
    $where[$this -> _get('authname')] = 1;
    $min_level = $member_level -> field('name,updatemoney') -> where($where) -> order('updatemoney ASC') -> find();
    $this -> assign('min_level', $min_level);
    //计算RMB差值
    $diff = $min_level['updatemoney'] - $_SESSION['rmb_total'];
    $this -> assign('diff', $diff);
    $this -> display();
  }

  //获得分站信息
  private function getchildsite(){
    $result = M('Area') -> field('id,name') -> where(array('name' => array('neq', '主站'), 'isshow' => '1')) -> select();
    $childsite = M('ChildSite');
    foreach($result as $key => $value){
      $result[$key]['childsite'] = $childsite -> field('domain,name') -> where(array('aid' => $value['id'], 'isshow' => 1)) -> select();
    }
    return $result;
  }

}

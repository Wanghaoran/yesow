<?php
class RegisterAction extends Action {
  //注册
  public function index(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);

    //如果已登录则不能注册
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      $this -> redirect('Index/index');
    }
    //注册第二步
    if(!empty($_POST['steps'])){
      //查询分站
      $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
      $this -> assign('result_childsite', $result_childsite);
      //查询学历
      $result_memberedu = M('MemberEdu') -> field('id,name') -> order('sort ASC') -> select();
      $this -> assign('result_memberedu', $result_memberedu);
      //查询职业
      $result_membercareer = M('MemberCareer') -> field('id,name') -> order('sort ASC') -> select();
      $this -> assign('result_membercareer', $result_membercareer);
      //查询收入
      $result_memberincome = M('MemberIncome') -> field('id,name') -> order('sort ASC') -> select();
      $this -> assign('result_memberincome', $result_memberincome);
      $this -> display('two');
      exit();
    }
    //注册第三步
    if(!empty($_POST['username'])){
      $member = D('admin://Member');
      //如果昵称为空，用用户名当昵称
      if(empty($_POST['nickname'])){
	$_POST['nickname'] = $_POST['username'];
      }
      if(!$member -> create()){
	$this -> errorjump($member -> getError());
      }
      if($mid = $member -> add()){
	
	//向会员reb表插入此会员信息
	$member_rmb = M('MemberRmb');
	$member_rmb -> add(array('mid' => $mid));
	$this -> successjump(L('USER_REGISTER_SUCCESS'), U('register/three'));
      }else{
	$this -> errorjump(L('USER_REGISTER_ERROR'));
      }
      exit();
    }
    $this -> display('one');
  }

  //注册第三步
  public function three(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);
    $this -> display();
  }

  //检测用户名是否重复
  public function checkusername(){
    $result = M('Member') -> getFieldByname($this -> _post('name'), 'id');
    if($result){
      echo 0;
    }else{
      echo 1;
    }
  }

  //检测邮件地址是否重复
  public function checkemail(){
    $result = M('Member') -> getFieldByemail($this -> _post('email'), 'id');
    if($result){
      echo 0;
    }else{
      echo 1;
    }
  }

  //检测验证码
  public function checkverify(){
    if(md5($_POST['name']) == session('verify')){
      echo 1;
    }else{
      echo 0;
    }
  }

  //检测用户邮箱
  public function checkuseremail(){
    $email = M('Member') -> getFieldByname($this -> _post('name'), 'email');
    if(strtolower($email) == strtolower($_POST['email'])){
      echo 1;
    }else{
      echo 0;
    }
  }

  //发送邮箱验证邮件
  public function sendcheckemail(){

    $config = M('MassEmailSetting') -> alias('e') -> field('e.id,e.send_address,e.email_smtp,e.send_account,e.send_pwd,t.title,t.content') -> join('yesow_mass_email_template as t ON t.eid = e.id') -> where(array('e.type_en' => 'member_check')) -> find();

    C('MAIL_ADDRESS', $config['send_address']);
    C('MAIL_SMTP', $config['email_smtp']);
    C('MAIL_LOGINNAME', $config['send_account']);
    C('MAIL_PASSWORD', $config['send_pwd']);
    import('ORG.Util.Mail');


    $mid = M('Member') -> getFieldByname($_POST['username'], 'id');

    $info = M('Member') -> alias('m') -> field('m.id,cs.name as csname,csa.name as csaname,m.name,m.nickname,m.fullname,m.idnumber,m.sex,m.tel,m.qqcode,m.msn,m.email,m.address,m.zipcode,m.unit,m.homepage') -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> where(array('m.id' => $mid)) -> find();
    $info['sex'] = $info['sex'] == 1 ? '男' : '女';
    $search = array('{member_id}', '{member_csid}', '{member_csaid}', '{member_name}', '{member_nickname}', '{member_fullname}', '{member_idnumber}', '{member_sex}', '{member_tel}', '{member_qqcode}', '{member_msn}', '{member_email}', '{member_address}', '{member_zipcode}', '{member_unit}', '{member_homepage}');

    $info['send_time'] = date('Y-m-d H:i:s');
    $search[] = '{send_time}';

    //加密邮箱
    $email = encode_pass($_POST['email'], C('KEY'));
    $name = encode_pass($_POST['username'], C('KEY'));
    $url = C('WEBSITE') . 'member.php/register/checkmail/username/' . $name . '/email/' . $email;

    $info['emailcheck_url'] = $url;
    $search[] = '{emailcheck_url}';

    $email_content = str_replace($search, $info, $config['content']);
    $email_title = str_replace($search, $info, $config['title']);

    if(@SendMail($_POST['email'], $email_title, $email_content, 'yesow管理员')){
      $add_data = array();
      $add_data['eid'] = $config['id'];
      $add_data['send_email'] = $config['send_address'];
      $add_data['accept_email'] = $_POST['email'];
      $add_data['title'] = $email_title;
      $add_data['content'] = $email_content;
      $add_data['sendtime'] = time();
      $add_data['status'] = 1;
      M('MassEmailRecord') -> add($add_data);
    }else{
      $add_data = array();
      $add_data['eid'] = $config['id'];
      $add_data['send_email'] = $config['send_address'];
      $add_data['accept_email'] = $_POST['email'];
      $add_data['title'] = $email_title;
      $add_data['content'] = $email_content;
      $add_data['sendtime'] = time();
      $add_data['status'] = 0;
      M('MassEmailRecord') -> add($add_data);
    }
    $this -> successjump(L('SEND_EMAIL_SUCCESS'), U('Public/login'));
  }

  //邮箱验证
  public function checkmail(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);

    $email = encode_pass($this -> _get('email'), C('KEY'), 'decode');
    $name = encode_pass($this -> _get('username'), C('KEY'), 'decode');
    $user_email = M('Member') -> getFieldByname($name, 'email');
    if($email == $user_email){
      //更新数据
      $where = array();
      $where['name'] = $name;
      $where['email'] = $email;
      M('Member') -> where($where) -> save(array('ischeck' => 1));

      //sendEmail
      $mid = M('Member') -> getFieldByname($name, 'id');
      D('admin://MassEmailSetting') -> sendEmail('member_register', $email, $mid);

      $this -> successjump(L('CHECK_EMAIL_SUCCESS'), U('Public/login'));
    }else{
      $this -> errorjump(L('CHECK_EMAIL_ERROR'), U('Register/three'));
    }
  }

  //成功跳转
  public function successjump($title, $url="", $time=3){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);

    $this -> assign('title', $title);
    if(empty($url)){
      if(!empty($_GET['a_c']) && !empty($_GET['m_d'])){
	$t_url = '__ROOT__/' . $this -> _get('a_c') . '/' . $this -> _get('m_d');
      }
      if(!empty($_GET['id'])){
	$t_url = $t_url . '/id/' . $this -> _get('id');
      }
      $r_url = isset($t_url) ? $t_url : $_SERVER["HTTP_REFERER"];
      $this -> assign('url', $r_url);
    }else{
      if(!empty($_GET['a_c']) && !empty($_GET['m_d'])){
	$t_url = '__ROOT__/' . $this -> _get('a_c') . '/' . $this -> _get('m_d');
      }
      if(!empty($_GET['id'])){
	$t_url = $t_url . '/id/' . $this -> _get('id');
      }
      $r_url = isset($t_url) ? $t_url : $url;
      $this -> assign('url', $r_url);
    }
    $this -> assign('time', $time);
    $this -> assign('status', 1);
    $this -> display('./member/Tpl/Register/jumpurl.html');
    exit();
  }

  //失败跳转
  public function errorjump($title, $url="", $time=3){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);

    $this -> assign('title', $title);
    if(empty($url)){
      if(!empty($_GET['a_c']) && !empty($_GET['m_d'])){
	$t_url = '__ROOT__/' . $this -> _get('a_c') . '/' . $this -> _get('m_d');
      }
      if(!empty($_GET['id'])){
	$t_url = $t_url . '/id/' . $this -> _get('id');
      }
      $r_url = isset($t_url) ? $t_url : $_SERVER["HTTP_REFERER"];
      $this -> assign('url', $r_url);
    }else{
      if(!empty($_GET['a_c']) && !empty($_GET['m_d'])){
	$t_url = '__ROOT__/' . $this -> _get('a_c') . '/' . $this -> _get('m_d');
      }
      if(!empty($_GET['id'])){
	$t_url = $t_url . '/id/' . $this -> _get('id');
      }
      $r_url = isset($t_url) ? $t_url : $url;
      $this -> assign('url', $r_url);
    }
    $this -> assign('time', $time);
    $this -> assign('status', 0);
    $this -> display('./member/Tpl/Register/jumpurl.html');
    exit();
  }

  //密码找回
  public function forgetpassword(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);

    //如果已登录则不能找回密码
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      $this -> redirect('Index/index');
    }
    //验证第二步
    if(!empty($_POST['username'])){
      $member = M('Member');
      $data = $member -> field('name,email,passwordquestion,passwordemail') -> where(array('name' => $this -> _post('username'))) -> find();
      //如果没有设置密保问题，则生产随机密码并发送到邮箱
      if(empty($data['passwordquestion'])){
	//如果可通过密码找回的次数为0，则提示并退出
	if($data['passwordemail'] == 0){
	  $this -> display('noforgetpasswordemail');
	  exit();
	}
	$this -> assign('count', ($data['passwordemail'] - 1));
	$this -> display('forgetpasswordemail');
	exit();
      }
      if(!$data){
	$this -> errorjump(L('USER_FORGET_PASSWORD_USERNAME_ERROR'));
      }else{
	$this -> assign('data', $data);
      }
      $this -> display('forgetpasswordtwo');
      exit();
    }
    //验证第三步
    if(!empty($_POST['answer'])){
      $member = M('Member');
      $answer = $member -> getFieldByname($this -> _post('name'), 'passwordanswer');
      //查用户id
      $id = $member -> getFieldByname($this -> _post('name'), 'id');
      if($answer != $this -> _post('answer')){
	$this -> errorjump(L('USER_FORGET_PASSWORD_ANSWER_ERROR'));
      }else{
	//将用户id写入session
	session('forgetpassword_userid', $id);
	$this -> display('forgetpasswordthree');
      }
      exit();
    }
    //重设密码
    if(!empty($_POST['password'])){
      if($_POST['password'] != $_POST['password1']){
	$this -> errorjump(L('PASSWORD_NEQ'));
      }
      $member = M('Member');
      $data['id'] = session('forgetpassword_userid');
      $data['password'] = $this -> _post('password', 'md5');
      //更改成功清除登录信息重新登录
      if($member -> save($data)){
	session(C('USER_AUTH_KEY'), null);
	session(null);
	session('[destroy]');
	$this -> successjump(L('PASSWORD_CHANGE_SUCCESS'), U('Public/login'));
      }else{
	$this -> errorjump(L('PASSWORD_DATA_UPDATE_ERROR'));
      }
      exit();
    }
    $this -> display('forgetpasswordone');
  }

  //验证密保问题
  public function checkpasswordquestion(){
    $answer = M('Member') -> getFieldByname($this -> _post('name'), 'passwordanswer');
    if($answer == $this -> _post('answer')){
      echo 0;
    }else{
      echo 1;
    }
  }

  //通过邮箱重置密码
  public function passwordemail(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);

    $member = M('Member');
    //先验证
    $question = $member -> getFieldByname($this -> _get('name'), 'passwordquestion');
    if(!empty($question)){
      $this -> errorjump(L('PASSWORD_EMAIL_NAME_ERROR'));
    }
    //重新生成随机密码
    import('ORG.Util.String');
    $password = String::randString(8);
    //更新用户密码
    if($member -> where(array('name' => $this -> _get('name'))) -> save(array('password' => md5($password), 'passwordemail' => array('exp','passwordemail-1')))){
      $email = $member -> getFieldByname($this -> _get('name'), 'email');
      import('ORG.Util.Mail');
      SendMail($email,'yesow用户密码找回邮件',"尊敬的用户{$_GET['name']}您好，您在易搜网站上重置的密码为：{$password} 请使用新密码登录网站，如有疑问，请联系易搜客服:0571-88396114 88396195咨询",'yesow管理员');
      $this -> successjump(L('PASSWORD_EMAIL_SUCCESS'), U('Public/login'));
    }else{
      $this -> errorjump(L('DATA_UPDATE_ERROR'));
    }
    

  }

  
}

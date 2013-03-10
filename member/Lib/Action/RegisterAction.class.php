<?php
class RegisterAction extends Action {
  //注册
  public function index(){
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
    //加密邮箱
    $email = encode_pass($_POST['email'], C('KEY'));
    $name = encode_pass($_POST['username'], C('KEY'));
    $url = C('WEBSITE') . 'member.php/register/checkmail/username/' . $name . '/email/' . $email;
    $content = '尊敬的' . $_POST['username'] . '用户您好：恭喜您已经注册成为易搜会员！感谢您对易搜（ www.yesow.com）的关注和认可，在易搜您将找到全国33个省、市、直辖市的所有电脑、数码等IT商家信息，赶快验证您的邮箱后登陆易搜，查询和发布您的商家信息和产品信息，积累易搜币您可以通过升级来查看客户资料和信息，你也可以直接在“我要充值”处充值人民币（RMB）也可以查看你想要的信息！目前充值有很大优惠！惊喜等着您哦！海量的IT商家信息应有尽有，高品质的服务有求必应！请<a href="' . $url . '">点此链接</a>完成。如果以上连接你无法点击进入，请将以下地址复制在地址栏上访问即可完成验证：' . $url . ' ';
    import('ORG.Util.Mail');
    SendMail($_POST['email'],'yesow注册用户验证邮件',$content,'yesow管理员');
    $this -> successjump(L('SEND_EMAIL_SUCCESS'), U('Public/login'));
  }

  //邮箱验证
  public function checkmail(){
    $email = encode_pass($this -> _get('email'), C('KEY'), 'decode');
    $name = encode_pass($this -> _get('username'), C('KEY'), 'decode');
    $user_email = M('Member') -> getFieldByname($name, 'email');
    if($email == $user_email){
      //更新数据
      $where = array();
      $where['name'] = $name;
      $where['email'] = $email;
      M('Member') -> where($where) -> save(array('ischeck' => 1));
      $this -> successjump(L('CHECK_EMAIL_SUCCESS'), U('Public/login'));
    }else{
      $this -> errorjump(L('CHECK_EMAIL_ERROR'), U('Register/three'));
    }
  }

  //成功跳转
  public function successjump($title, $url="", $time=3){
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

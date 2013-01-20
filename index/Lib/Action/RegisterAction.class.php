<?php
class RegisterAction extends Action {
  //注册
  public function index(){
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
      if($member -> add()){
	$this -> successjump(L('USER_REGISTER_SUCCESS'), '__ROOT__/register/three');
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
    if($email == $_POST['email']){
      echo 1;
    }else{
      echo 0;
    }
  }

  //处理注册
  public function reg(){
    $member = D('admin://Member');
    //如果昵称为空，用用户名当昵称
    if(empty($_POST['nickname'])){
      $_POST['nickname'] = $_POST['username'];
    }
    if(!$member -> create()){
      $this -> error($member -> getError());
    }
    if($member -> add()){
      $this -> successjump(L('USER_REGISTER_SUCCESS'), '__ROOT__/register/three');
    }else{
      $this -> error(L('USER_REGISTER_ERROR'));
    }
  }

  //发送邮箱验证邮件
  public function sendcheckemail(){
    //加密邮箱
    $email = encode_pass($_POST['email'], C('KEY'));
    $name = encode_pass($_POST['username'], C('KEY'));
    $url = C('WEBSITE') . 'register/checkmail/username/' . $name . '/email/' . $email;
    $content = '尊敬的' . $_POST['username'] . '用户您好：恭喜您已经注册成为易搜会员！感谢您对易搜（ www.yesow.com）的关注和认可，在易搜您将找到全国33个省、市、直辖市的所有电脑、数码等IT商家信息，赶快验证您的邮箱后登陆易搜，查询和发布您的商家信息和产品信息，积累易搜币您可以通过升级来查看客户资料和信息，你也可以直接在“我要充值”处充值人民币（RMB）也可以查看你想要的信息！目前充值有很大优惠！惊喜等着您哦！海量的IT商家信息应有尽有，高品质的服务有求必应！请点此链接完成 ' . $url . '如果以上连接你无法点击进入，请将以下地址复制在地址栏上访问即可完成验证：' . $url . ' ';
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
      $this -> assign('url', $_SERVER["HTTP_REFERER"]);
    }else{
      $this -> assign('url', $url);
    }
    $this -> assign('time', $time);
    $this -> assign('status', 1);
    $this -> display('./index/Tpl/Register/jumpurl.html');
    exit();
  }

  //失败跳转
  public function errorjump($title, $url="", $time=3){
    $this -> assign('title', $title);
    if(empty($url)){
      $this -> assign('url', $_SERVER["HTTP_REFERER"]);
    }else{
      $this -> assign('url', $url);
    }
    $this -> assign('time', $time);
    $this -> assign('status', 0);
    $this -> display('./index/Tpl/Register/jumpurl.html');
    exit();
  }

  //密码找回
  public function forgetpassword(){
    //验证第二步
    if(!empty($_POST['username'])){
      $member = M('Member');
      $data = $member -> field('name,passwordquestion') -> where(array('name' => $this -> _post('username'))) -> find();
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
	$this -> errorjump(L('DATA_UPDATE_ERROR'));
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

  
}

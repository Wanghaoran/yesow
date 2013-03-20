<?php
class PublicAction extends Action {

  /*
   * 检测用户是否登录
   */
  public function checkuser(){
    if(!isset($_SESSION[C('USER_AUTH_KEY')])){
      $this -> error(L('LOGIN_NOT'), U(C('USER_AUTH_GATEWAY')));
    }
  }

  /*
   * 用户登录界面
   */
  public function login(){
    $this -> display();
  }

  /*
   * 验证用户登录
   */
  public function checklogin(){
    if(empty($_POST['account'])){
      $this -> error(L('LOGIN_NAME_EMPTY'));
    }else if(empty($_POST['password'])){
      $this -> error(L('LOGIN_PWD_EMPTY'));
    }else if(empty($_POST['verify'])){
      $this -> error(L('LOGIN_VERIFY_EMPTY'));
    }
    if($this -> _session('verify') != md5($this -> _post('verify'))){
      $this -> error(L('VERIFY_ERROR'));
    }
    import('ORG.Util.RBACI');
    //生成认证条件
    $cond = array();
    $cond['name'] = $this -> _post('account');
    $cond['status'] = '1';
    $authInfo = RBAC::authenticate($cond);
    if(false == $authInfo){
      $this -> error(L('NAME_ERROR'));
    }
    if($authInfo['password'] != sha1($this -> _post('password'))){
      $this -> error(L('PASSWORD_ERROR'));
    }
    //生成登录信息
    session(C('USER_AUTH_KEY'), $authInfo['id']);
    session('admin_name', $authInfo['name']);
    session('lastLoginTime', $authInfo['last_login_time']);
    session('loginCount', $authInfo['login_count']);
    session('last_login_ip', $authInfo['last_login_ip']);
    session('csid', $authInfo['csid']);
    if($authInfo['name'] == 'admin'){
      session(C('ADMIN_AUTH_KEY'), true);
    }
    //保存登录信息
    $admin = M('Admin');
    $data = array();
    $data['id'] = $authInfo['id'];
    $data['last_login_ip'] = get_client_ip();
    $data['last_login_time'] = time();
    $data['login_count'] = array('exp', 'login_count+1');
    $admin -> save($data);
    //缓存访问权限
    RBAC::saveAccessList();
    $this -> success(L('LOGIN_SUCCESS'), U('Index/index'));
  }

  /*
   * 退出登录
   */
  public function logout(){
    session(C('USER_AUTH_KEY'), null);
    session(null);
    session('[destroy]');
    $this -> success(L('LOGOUT_SUCCESS'), U(C('USER_AUTH_GATEWAY')));
  }

  /*
   * 生成验证码
   */
  public function verify(){
    import('ORG.Util.Image');
    Image::buildImageVerify();
  }
  /*
   * 后台首页 查看系统信息
   */
  public function main(){
   $info = array(
     '操作系统'=>PHP_OS,
     '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
     'PHP运行方式'=>php_sapi_name(),
     'ThinkPHP版本'=>THINK_VERSION.' [ <a href="http://thinkphp.cn" target="_blank">查看最新版本</a> ]',
     '上传附件限制'=>ini_get('upload_max_filesize'),
     '执行时间限制'=>ini_get('max_execution_time').'秒',
     '服务器时间'=>date("Y年n月j日 H:i:s"),
     '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
     '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
     '剩余空间'=>round((@disk_free_space(".")/(1024*1024)),2).'M',
     'register_globals'=>get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
     'magic_quotes_gpc'=>(1===get_magic_quotes_gpc())?'YES':'NO',
     'magic_quotes_runtime'=>(1===get_magic_quotes_runtime())?'YES':'NO',
   );
   $this->assign('info', $info);
   $this->display();  
  }

  /*
   * 后台首页 修改密码
   */
  public function password(){
    $this -> display(); 
  }

  /*
   * 验证 修改 密码
   */
  public function changepwd(){
    //验证是否登录
    $this -> checkuser();
    //数据完整性检测
    if(empty($_POST['oldpassword'])){
      $this -> error(L('OLDPASSWORD_EMPTY'));
    }else if(empty($_POST['password'])){
      $this -> error(L('NEWPASSWORD_EMPTY'));
    }else if(empty($_POST['repassword'])){
      $this -> error(L('REPASSWORD_EMPTY'));
    }else if($_POST['password'] != $_POST['repassword']){
      $this -> error(L('PASSWORD_NEQ'));
    }else if(md5($_POST['verify']) != $_SESSION['verify']){
      $this -> error(L('VERIFY_ERROR'));
    }
    //构建查询条件
    $cond = array();
    $cond['password'] = sha1($this -> _post('oldpassword'));
    $cond['id'] = session(C('USER_AUTH_KEY'));
    $admin = M('Admin');
    if(!$admin -> field('id') -> where($cond) -> find()){
      $this -> error(L('OLDPASSWORD_ERROR'));
    }
    //构建更新条件
    $cond['password'] = sha1($this -> _post('password'));
    if($admin -> save($cond)){
      $this -> success(L('CHANGE_PWD_SUCCESS'));
    }else{
      $this -> error(L('CHANGE_PWD_ERROR'));
    }
  }

  /*
   * 后台首页 修改资料
   */
  public function profile(){
    $this -> checkuser();
    $admin = M('Admin');
    $result = $admin -> field('remark') -> find(session(C('USER_AUTH_KEY')));
    $this -> assign('result', $result);
    $this -> display();
  }

  /*
   * 更改资料
   */
  public function change(){
    $this -> checkuser();
    $admin = D('Admin');
    //构建更新条件
    $data = array();
    $data['id'] = session(C('USER_AUTH_KEY'));
    $data['email'] = $this -> _post('email');
    $data['remark'] = $this -> _post('remark');
    if(!$admin -> create($data)){
      $this -> error($admin -> getError());
    }
    if($admin -> save()){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //ajax获取资讯二级分类、内容属性、标题属性
  public function ajaxinfotwocolumn(){
    if(!empty($_GET['code'])){
      $where = array();
      $where['oneid'] = $this -> _get('code', 'intval');
      $result = array();
      $result['twocolumn'] = M('InfoTwoColumn') -> field('id,name') -> where($where) -> select();
      $result['titleattribute'] = M('InfoTitleAttribute') -> field('id,name') -> where($where) -> select();
      $result['contentattribute'] = M('InfoContentAttribute') -> field('id,name') -> where(array('oneid' => $this -> _get('code', 'intval'), 'pid' => 0)) -> select();      
      echo json_encode($result);
    }
  }

  //ajax获取二级内容属性
  public function ajaxinfocontentattribute(){
    if(!empty($_GET['code'])){
      $where = array();
      $where['pid'] = $this -> _get('code', 'intval');
      $result = M('InfoContentAttribute') -> field('id,name') -> where($where) -> select();
      if($result){
	echo json_encode($result);
      }else{
      	echo '';
      }
    }
  }

    //ajax获取分站下地区
  public function ajaxgetcsaid(){
    $result_temp = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $this -> _get('id', 'intval'))) -> select();
    $result = array();
    $result[] = array('', '请选择');
    //格式化结果集
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  //ajax获取速查主营类别 - 二级
  public function ajaxgetcompanycategorytwo(){
    $result_temp = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $this -> _get('id', 'intval'))) -> order('sort ASC') -> select();
    $result = array();
    //格式化结果集
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  //ajax获取用户名及id
  public function ajaxgetmemberinfo(){
    $username = $this -> _post('inputValue');
    $member = M('Member');
    $where = array();
    $where['name'] = array('like', '%' . $username . '%');
    $result = $member -> field('id,name') -> where($where) -> limit(10) -> select();
    echo json_encode($result);
  }

  //ajax获取包月会员类型
  public function ajaxgetmonthlytype(){
    $member_monthly = M('MemberMonthly');
    $lid = $this -> _get('id', 'intval');
    $result_tmp = $member_monthly -> field('id,months') -> where(array('lid' => $lid)) -> order('months ASC') -> select();
    $result = array();
    //格式化结果集
    foreach($result_tmp as $key => $value){
      $result[] = array($value['id'], $value['months'] . '个月');
    }
    echo json_encode($result);
  }
}

<?php
class PublicAction extends Action {

  public function checkuser(){
    if(!isset($_SESSION[C('USER_AUTH_KEY')])){
      $this -> error(L('LOGIN_NOT'), U(C('USER_AUTH_GATEWAY')));
    }
  }

  public function login(){
    $this -> display();
  }

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
    session(C('USER_AUTH_KEY'), $authInfo['id']);
    session('admin_name', $authInfo['name']);
    session('lastLoginTime', $authInfo['last_login_time']);
    session('loginCount', $authInfo['login_count']);
    session('last_login_ip', $authInfo['last_login_ip']);
    session('csid', $authInfo['csid']);
    if($authInfo['name'] == 'admin'){
      session(C('ADMIN_AUTH_KEY'), true);
    }
    $admin = M('Admin');
    $data = array();
    $data['id'] = $authInfo['id'];
    $data['last_login_ip'] = get_client_ip();
    $data['last_login_time'] = time();
    $data['login_count'] = array('exp', 'login_count+1');
    $admin -> save($data);
    RBAC::saveAccessList();
    $this -> success(L('LOGIN_SUCCESS'), U('Index/index'));
  }

  public function logout(){
    session(C('USER_AUTH_KEY'), null);
    session(null);
    session('[destroy]');
    $this -> success(L('LOGOUT_SUCCESS'), U(C('USER_AUTH_GATEWAY')));
  }

  public function verify(){
    import('ORG.Util.Image');
    ob_end_clean();
    Image::buildImageVerify();
  }
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

  public function password(){
    $this -> display(); 
  }

  public function changepwd(){
    $this -> checkuser();
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
    $cond = array();
    $cond['password'] = sha1($this -> _post('oldpassword'));
    $cond['id'] = session(C('USER_AUTH_KEY'));
    $admin = M('Admin');
    if(!$admin -> field('id') -> where($cond) -> find()){
      $this -> error(L('OLDPASSWORD_ERROR'));
    }
    $cond['password'] = sha1($this -> _post('password'));
    if($admin -> save($cond)){
      $this -> success(L('CHANGE_PWD_SUCCESS'));
    }else{
      $this -> error(L('CHANGE_PWD_ERROR'));
    }
  }

  public function profile(){
    $this -> checkuser();
    $admin = M('Admin');
    $result = $admin -> field('remark') -> find(session(C('USER_AUTH_KEY')));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function change(){
    $this -> checkuser();
    $admin = D('Admin');
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

  public function ajaxgetcsaid(){
    $result_temp = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $this -> _get('id', 'intval'))) -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  public function ajaxgetcompanycategorytwo(){
    $result_temp = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $this -> _get('id', 'intval'))) -> order('sort ASC') -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  public function ajaxgetmemberinfo(){
    $username = $this -> _post('inputValue');
    $member = M('Member');
    $where = array();
    $where['name'] = array('like', '%' . $username . '%');
    $result = $member -> field('id,name') -> where($where) -> limit(10) -> select();
    if($frist = $member -> field('id,name') -> where(array('name' => $username)) -> find()){
      array_unshift($result, $frist);
    }
    echo json_encode($result);
  }

  public function ajaxgetmemberinfomore(){
    $username = $this -> _post('inputValue');
    $member = M('Member');
    $where = array();
    $where['name'] = array('like', '%' . $username . '%');
    $result = $member -> field('id as mid,name as mname,unit,fullname as new_linkman,tel as new_mobilephone,qqcode as new_qqocde,email as new_email') -> where($where) -> limit(10) -> select();
    echo json_encode($result);
  }

  public function ajaxgetcompanyinfo(){
    $companyname = $this -> _post('inputValue');
    $company = M('Company');
    $where = array();
    $where['name'] = array('like', '%' . $companyname . '%');
    $result = $company -> field('id,name') -> where($where) -> limit(10) -> select();
    echo json_encode($result);
  }

  public function ajaxgetrecruit_companyinfo(){
    $companyname = $this -> _post('inputValue');
    $RecruitCompany = M('RecruitCompany');
    $where = array();
    $where['name'] = array('like', '%' . $companyname . '%');
    $result = $RecruitCompany -> field('id,name') -> where($where) -> limit(10) -> select();
    echo json_encode($result);
  }

  public function ajaxgetmonthlytype(){
    $member_monthly = M('MemberMonthly');
    $lid = $this -> _get('id', 'intval');
    $result_tmp = $member_monthly -> field('id,months,type') -> where(array('lid' => $lid)) -> order('months ASC') -> select();
    $result = array();
    foreach($result_tmp as $key => $value){
      if($value['type'] == 1){
	$result[] = array($value['id'], $value['months'] . '个月(全国)');
      }else{
	$result[] = array($value['id'], $value['months'] . '个月(包省)');
      }
    }
    echo json_encode($result);
  }

  public function ajaxgetshoptwoclass(){
    $pid = $this -> _get('id', 'intval');
    $shop_class = M('ShopClass');
    $result_tmp = $shop_class -> field('id,name') -> where(array('pid' => $pid)) -> order('sort ASC') -> select();
    $result = array();
    foreach($result_tmp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  public function ajaxgetrankstring(){
    $where = array();
    $where['fid'] = $this -> _post('fid', 'intval');
    $where['rank'] = $this -> _post('rank', 'intval');
    $where['keyword'] = $this -> _post('keyword');
    $where['endtime'] = array('EGT', time());
    $result = M('SearchRank') -> field('MAX(endtime) as endtime, COUNT(id) as count') -> where($where) -> select();
    $result[0]['endtime']  = date('Y-m-d', $result[0]['endtime']);
    echo json_encode($result[0]);
  }

  public function ajaxgetrecommendcompanystring(){
    $where = array();
    $where['fid'] = $this -> _post('fid', 'intval');
    $where['rank'] = $this -> _post('rank', 'intval');
    $where['endtime'] = array('EGT', time());
    $result = M('RecommendCompany') -> field('MAX(endtime) as endtime, COUNT(id) as count') -> where($where) -> select();
    $result[0]['endtime']  = date('Y-m-d', $result[0]['endtime']);
    echo json_encode($result[0]);
  }

  public function ajaxgetreviewcompanyinfo(){
    $companyname = $this -> _post('inputValue');
    $company = M('Company');
    $where = array();
    $where['c.name'] = array('like', '%' . $companyname . '%');
    $result = $company -> alias('c') -> field('c.id,c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,c.website,cs.name as csname,csa.name as csaname,cc1.name as cc1name,cc2.name as cc2name,c.linkman as new_linkman,c.companyphone as new_companyphone,c.mobilephone as new_mobilephone,c.qqcode as new_qqocde,c.email as new_email') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_category as cc1 ON c.ccid = cc1.id') -> join('yesow_company_category as cc2 ON cc1.pid = cc2.id') -> where($where) -> limit(20) -> select();
    echo json_encode($result);
  }

  
  public function ajaxgetadmininfo(){
    $adminname = $this -> _post('inputValue');
    $admin = M('Admin');
    $where = array();
    $where['name'] = array('like', '%' . $adminname . '%');
    $result = $admin -> field('id,name') -> where($where) -> limit(10) -> select();
    echo json_encode($result);
  }

  public function shop_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('SHOP_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function media_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('MEDIA_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function store_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('STORE_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function ajaxgetselltid(){
    $result_temp = M('SellUsedType') -> field('id,name') -> where(array('pid' => $this -> _get('id', 'intval'))) -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  public function ajaxgetchildsiteadvertpage(){
    $result_temp = M('AdvertisePage') -> field('id,remark') -> where(array('csid' => $this -> _get('id', 'intval'))) -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['remark']);
    }
    echo json_encode($result);
  }

  public function ajaxgetchildsiteadvert(){
    $result_temp = M('Advertise') -> field('id,name,width,height') -> where(array('pid' => $this -> _get('id', 'intval'), 'isopen' => 1)) -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name'] . '(' . $value['width'] . 'x' . $value['height'] . ')');
    }
    echo json_encode($result);
  }

  public function ajaxgetchildsiteadvertprice(){
    $result = M('AdvertMoney') -> field('id,months,marketprice,promotionprice') -> where(array('adid' => $this -> _get('id', 'intval'))) -> select();
    echo json_encode($result);
  }

  public function ajaxgetchildsiteadvertsize(){
    $result = M('Advertise') -> field('width,height') -> where(array('id' => $this -> _get('id', 'intval'))) -> find();
    echo json_encode($result);
  }

  public function ajaxgetquestioncategorytwoid(){
    $result_temp = M('QuestionCategory') -> field('id,name') -> where(array('pid' => $this -> _get('id', 'intval'))) -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  public function ajaxgetattractinvestmentid(){
    $result_temp = M('AttractinvestmentCategory') -> field('id,name') -> where(array('fid' => $this -> _get('id', 'intval'))) -> order('sort ASC') -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  public function sellused_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('SELLUSED_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function recruit_company_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('RECRUIT_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function ajaxgetemailtemplate(){
    echo M('BackgroundEmailTemplate') -> getFieldByid($this -> _get('id', 'intval'),'content');
  }

  public function companyremind(){
    set_time_limit(0);

    $success_num = 0;
    $error_num = 0;
    $error_send_num = 0;


    $CompanyRemindTime = M('CompanyRemindTime');
    $Company = M('Company');
    $MassEmailSetting = M('MassEmailSetting');
    $CompanyRemindEmail = M('CompanyRemindEmail');

    $start_time_t = mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y'));
    $end_time_t = mktime(date('H'), date('i'), 59, date('m'), date('d'), date('Y'));

    $time_line = $CompanyRemindTime -> field('time') -> order('time ASC') -> select();

    $email_template = $MassEmailSetting -> alias('e') -> field('t.title,t.content') -> join('yesow_mass_email_template as t ON t.eid = e.id') -> where(array('type_en' => 'company_remind')) -> find();

    $send_email = $CompanyRemindEmail -> field('id,send_address as send_address, send_smtp as email_smtp, send_email as send_account, email_pwd as send_pwd') -> where('status=1 AND type=1') -> find();

    $email_template = array_merge($email_template, $send_email);

    foreach($time_line as $value){
      $start_time = $start_time_t - ($value['time'] * 24 * 60 *60);
      $end_time = $end_time_t - ($value['time'] * 24 * 60 *60);

      $company_result = array();

      $company_result = $Company -> alias('c') -> field('c.name,c.address,c.companyphone,c.linkman,c.website,c.email,c.manproducts,c.qqcode,c.mobilephone,c.id,cs.name as csname,csa.name as csaname,cs.domain as domain,c.updatetime') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> where(array('c.updatetime' => array(array('egt',$start_time),array('elt', $end_time)), 'c.delaid' => array('exp', 'IS NULL'))) -> select();


      foreach($company_result as $value2){

	$search = array('{company_name}', '{company_address}', '{company_companyphone}', '{company_linkman}', '{company_website}', '{company_email}', '{company_manproducts}', '{company_qqcode}', '{company_mobilephone}', '{company_id}', '{company_csid}', '{company_csaid}', '{company_domain}', '{company_updatetime}', '{companyremind_time}', '{send_time}');
	$replace = array($value2['name'], $value2['address'], $value2['companyphone'], $value2['linkman'], $value2['website'], $value2['email'], $value2['manproducts'], $value2['qqcode'], $value2['mobilephone'], $value2['id'], $value2['csname'], $value2['csaname'], $value2['domain'], date('Y-m-d H:i:s', $value2['updatetime']), $value['time'], date('Y-m-d H:i:s'));
	$email_title = str_replace($search, $replace, $email_template['title']);
	$email_content = str_replace($search, $replace, $email_template['content']);

	//sendEmail
	C('MAIL_ADDRESS', $email_template['send_address']);
	C('MAIL_SMTP', $email_template['email_smtp']);
	C('MAIL_LOGINNAME', $email_template['send_account']);
	C('MAIL_PASSWORD', $email_template['send_pwd']);
	import('ORG.Util.Mail');

	if(@SendMail($value2['email'], $email_title, $email_content, 'yesow管理员')){
	  $record_data = array();
	  $record_data['cid'] = $value2['id'];
	  $record_data['accept_email'] = $value2['email'];
	  $record_data['send_email'] = $email_template['send_address'];
	  $record_data['title'] = $email_title;
	  $record_data['content'] = $email_content;
	  $record_data['send_time'] = $start_time_t;
	  $record_data['status'] = 1;
	  M('CompanyRemindEmailRecord') -> add($record_data);

	  $Company -> where(array('id' => $value2['id'])) -> setInc('remind_count');

	  $r_data = array();
	  $r_data['cid'] = $value2['id'];
	  $r_data['send_time'] = $start_time_t;
	  $r_data['send_email'] = $email_template['send_address'];
	  $r_data['time'] = $value['time'];
	  $r_data['email'] = $value2['email'];
	  M('CompanyRemindRecord') -> add($r_data);

	  $CompanyRemindEmail -> where(array('id' => $email_template['id'])) -> setInc('sum');

	  $success_num++;
	}else{
	  $record_data = array();
	  $record_data['cid'] = $value2['id'];
	  $record_data['accept_email'] = $value2['email'];
	  $record_data['send_email'] = $email_template['send_address'];
	  $record_data['title'] = $email_title;
	  $record_data['content'] = $email_content;
	  $record_data['send_time'] = $start_time_t;
	  $record_data['status'] = 0;
	  M('CompanyRemindEmailRecord') -> add($record_data);
	  $error_num++;

	  if($value2['email']){
	    $error_send_num++;
	  }
	  
	}
	usleep(10000);
      }
    }

    //失败条数超过5条则发送邮件提醒
    if($error_send_num >= 5){
      D('admin://OrderAcceptEmail') -> sendemail('易搜速查定期提醒系统警告邮件', '亲爱的易搜管理员:<br/>易搜速查定期提醒系统在 <b style="color:red">' . date('Y-m-d H:i:s', $start_time_t) . '</b> 邮件发送区间，共发送了 <b style="color:red">' . ($success_num + $error_num) . ' </b> 条数据，其中，成功 <b style="color:red">' . $success_num . ' </b>条，失败 <b style="color:red">' . $error_num . ' </b>条！<br/> 请注意！！');
    }

    echo '发送完毕,成功' . $success_num . '条，失败' . $error_num . '条！';
    //$this -> success('发送完毕,成功' . $success_num . '条，失败' . $error_num . '条！', __ROOT__);
    return;
  }

  public function memberremind(){
    set_time_limit(0);

    $success_num = 0;
    $error_num = 0;
    $error_send_num = 0;


    $MemberRemindTime = M('MemberRemindTime');
    $Member = M('Member');
    $MassEmailSetting = M('MassEmailSetting');
    $MemberRemindEmail = M('MemberRemindEmail');

    $start_time_t = mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y'));
    $end_time_t = mktime(date('H'), date('i'), 59, date('m'), date('d'), date('Y'));

    $time_line = $MemberRemindTime -> field('time') -> order('time ASC') -> select();


    

    $email_template = $MassEmailSetting -> alias('e') -> field('t.title,t.content') -> join('yesow_mass_email_template as t ON t.eid = e.id') -> where(array('type_en' => 'member_remind')) -> find();

    $send_email = $MemberRemindEmail -> field('id,send_address as send_address, send_smtp as email_smtp, send_email as send_account, email_pwd as send_pwd') -> where('status=1 AND type=2') -> find();

    $email_template = array_merge($email_template, $send_email);

    foreach($time_line as $value){
      $start_time = $start_time_t - ($value['time'] * 24 * 60 *60);
      $end_time = $end_time_t - ($value['time'] * 24 * 60 *60);

      $member_result = array();

      $member_result = $Member -> alias('m') -> field('m.id,cs.name as csname,csa.name as csaname,m.name,m.nickname,m.fullname,m.idnumber,m.sex,m.tel,m.qqcode,m.msn,m.email,m.address,m.zipcode,m.unit,m.homepage,m.last_login_time') -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> where(array('m.last_login_time' => array(array('egt',$start_time),array('elt', $end_time),array('exp', 'IS NOT NULL')))) -> select();


      foreach($member_result as $value2){

	$search = array('{member_id}', '{member_csid}', '{member_csaid}', '{member_name}', '{member_nickname}', '{member_fullname}', '{member_idnumber}', '{member_sex}', '{member_tel}', '{member_qqcode}', '{member_msn}', '{member_email}', '{member_address}', '{member_zipcode}', '{member_unit}', '{member_homepage}', '{member_lastlogintime}', '{memberremind_time}', '{send_time}');

	$value2['sex'] = $value2['sex'] == 1 ? '男' : '女';

	$replace = array($value2['id'], $value2['csname'], $value2['csaname'], $value2['name'], $value2['nickname'], $value2['fullname'], $value2['idnumber'], $value2['sex'], $value2['tel'], $value2['qqcode'], $value2['msn'], $value2['email'], $value2['address'], $value2['zipcode'], $value2['unit'], $value2['homepage'], date('Y-m-d H:i:s', $value2['last_login_time']), $value['time'], date('Y-m-d H:i:s'));
	$email_title = str_replace($search, $replace, $email_template['title']);
	$email_content = str_replace($search, $replace, $email_template['content']);

	//sendEmail
	C('MAIL_ADDRESS', $email_template['send_address']);
	C('MAIL_SMTP', $email_template['email_smtp']);
	C('MAIL_LOGINNAME', $email_template['send_account']);
	C('MAIL_PASSWORD', $email_template['send_pwd']);
	import('ORG.Util.Mail');

	if(@SendMail($value2['email'], $email_title, $email_content, 'yesow管理员')){
	  $record_data = array();
	  $record_data['mid'] = $value2['id'];
	  $record_data['accept_email'] = $value2['email'];
	  $record_data['send_email'] = $email_template['send_address'];
	  $record_data['title'] = $email_title;
	  $record_data['content'] = $email_content;
	  $record_data['send_time'] = $start_time_t;
	  $record_data['status'] = 1;
	  M('MemberRemindEmailRecord') -> add($record_data);

	  $Member -> where(array('id' => $value2['id'])) -> setInc('remind_count');

	  $r_data = array();
	  $r_data['mid'] = $value2['id'];
	  $r_data['send_time'] = $start_time_t;
	  $r_data['send_email'] = $email_template['send_address'];
	  $r_data['time'] = $value['time'];
	  $r_data['email'] = $value2['email'];
	  M('MemberRemindRecord') -> add($r_data);

	  $MemberRemindEmail -> where(array('id' => $email_template['id'])) -> setInc('sum');

	  $success_num++;
	}else{
	  $record_data = array();
	  $record_data['mid'] = $value2['id'];
	  $record_data['accept_email'] = $value2['email'];
	  $record_data['send_email'] = $email_template['send_address'];
	  $record_data['title'] = $email_title;
	  $record_data['content'] = $email_content;
	  $record_data['send_time'] = $start_time_t;
	  $record_data['status'] = 0;
	  M('MemberRemindEmailRecord') -> add($record_data);
	  $error_num++;

	  if($value2['email']){
	    $error_send_num++;
	  }

	}
	usleep(10000);
      }
    }

    //失败条数超过5条则发送邮件提醒
    if($error_send_num >= 5){
      D('admin://OrderAcceptEmail') -> sendemail('易搜会员定期提醒系统警告邮件', '亲爱的易搜管理员:<br/>易搜会员定期提醒系统在 <b style="color:red">' . date('Y-m-d H:i:s', $start_time_t) . '</b> 邮件发送区间，共发送了 <b style="color:red">' . ($success_num + $error_num) . ' </b> 条数据，其中，成功 <b style="color:red">' . $success_num . ' </b>条，失败 <b style="color:red">' . $error_num . ' </b>条！<br/> 请注意！！');
    }

    echo '发送完毕,成功' . $success_num . '条，失败' . $error_num . '条！';
    //$this -> success('发送完毕,成功' . $success_num . '条，失败' . $error_num . '条！', __ROOT__);
    return;
   
  }


  public function setwebsitemap(){
    $time = time();
    $ChildSite = M('ChildSite') -> field('id,domain,name') -> select();
    foreach($ChildSite as $value){
      $content_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<urlset>";
      $content_xml .= '
<url>
<loc>http://' . $value['domain'] .  ' </loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com/company</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com/hire</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com/info</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com/agent</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com/shop</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';

$company_data = M('Company') -> field('id') -> where(array('delaid' => array('exp', 'is NULL'), 'csid' => $value['id'])) -> select();
    foreach($company_data as $value2){
      $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '/company/' . $value2['id'] . '</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }

    $shop_data = M('Shop') -> field('id,title') -> select();
    foreach($shop_data as $value3){
      $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '/shop/' . $value3['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }

    $ChildsiteInfoarticle = M('ChildsiteInfoarticle');
    $iaid_temp = $ChildsiteInfoarticle -> field('iaid') -> where(array('csid' => $value['id'])) -> select();
    $iaid = array();
    foreach($iaid_temp as $iaid_value){
      $iaid[] = $iaid_value['iaid'];
    }
    $article_data = M('InfoArticle') -> field('id') -> where(array('id' => array('IN', $iaid))) -> select();
    foreach($article_data as $value4){
                  $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '/article/' . $value4['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }

    $notice_data = M('Notice') -> field('id,title') -> select();
    foreach($notice_data as $value5){
       $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '/notice/' . $value5['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }

    $storerent_data = M('StoreRent') -> field('id') -> where(array('csid' => $value['id'])) -> select();
    foreach($storerent_data as $value6){
      $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '/storerent/' . $value6['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }

$content_xml .= '
</urlset>';

      $urlname = substr($value['domain'], 0, strpos($value['domain'], '.'));
      $file_xml = fopen('./sitemap/Sitemap_' . $urlname . 'yesow.xml', 'wb');
      fwrite($file_xml, $content_xml);
      fclose($file_xml);

    $sitemap_upload = M('SitemapUpload');
    $data_upload = array();
    $data_upload['csname'] = $value['name'];
    $data_upload['navnum'] = 8;
    $data_upload['companynum'] = count($company_data);
    $data_upload['shopnum'] = count($shop_data);
    $data_upload['articlenum'] = count($article_data);
    $data_upload['noticenum'] = count($notice_data);
    $data_upload['storerentnum'] = count($storerent_data);
    $data_upload['updatetime'] = time();
    $data_upload['xmlsize'] = filesize('./sitemap/Sitemap_' . $urlname . 'yesow.xml');
    $sitemap_upload -> add($data_upload);
    }

    $this -> success('网站地图更新成功', __ROOT__);
    return;
  }

  public function sendbusinessremind(){
    set_time_limit(0);

    $time_line = M('EndtimeAlertTime') -> field('time') -> order('time ASC') -> select();
    $EndtimeAlertEmail = M('EndtimeAlertEmail');
    $business = $EndtimeAlertEmail -> field('model_name,name,send_address,send_smtp,send_email,email_pwd,title,content') -> select();

    $success_num = 0;
    $error_num = 0;

    foreach($time_line as $value){
      $start_time = mktime(0, 0, 0, date('m'), date('d') - $value['time'], date('Y'));
      $end_time = mktime(23, 59, 59, date('m'), date('d') - $value['time'], date('Y'));
      foreach($business as $value2){
	$info = M($value2['model_name']) -> alias('a') -> field('a.id,a.starttime,a.endtime,m.name as mname,m.email as email,m.nickname') -> where(array('endtime' => array('between',array($start_time, $end_time)))) -> join('yesow_member as m ON a.mid = m.id') -> select();

	foreach($info as $value3){

	  if (!$value3['email']) continue;

	  $search = array('{member_name}', '{member_nickname}', '{product_starttime}', '{product_id}', '{product_name}', '{product_endtime}', '{product_remainingtime}', '{send_time}');
	  $replace = array($value3['mname'], $value3['nickname'], date('Y-m-d H:i:s', $value3['starttime']), $value3['id'], $value2['name'], date('Y-m-d H:i:s', $value3['endtime']), floor(abs($value3['endtime'] - time()) / (60 * 60 * 24)), date('Y-m-d H:i:s'));
	  $email_title = str_replace($search, $replace, $value2['title']);
	  $email_content = str_replace($search, $replace, $value2['content']);

	  //sendEmail
	  C('MAIL_ADDRESS', $value2['send_address']);
	  C('MAIL_SMTP', $value2['send_smtp']);
	  C('MAIL_LOGINNAME', $value2['send_email']);
	  C('MAIL_PASSWORD', $value2['email_pwd']);
	  import('ORG.Util.Mail');

	  if(@SendMail($value3['email'], $email_title, $email_content, 'yesow管理员')){
	    $record_data = array();
	    $record_data['mname'] = $value3['mname'];
	    $record_data['send_email'] = $value2['send_address'];
	    $record_data['send_type'] = $value2['name'];
	    $record_data['accept_email'] = $value3['email'];
	    $record_data['title'] = $email_title;
	    $record_data['content'] = $email_content;
	    $record_data['send_time'] = time();
	    $record_data['status'] = 1;
	    M('EndtimeAlertEmailRecord') -> add($record_data);
	    $success_num++;
	  }else{
	    $record_data = array();
	    $record_data['mname'] = $value3['mname'];
	    $record_data['send_email'] = $value2['send_address'];
	    $record_data['send_type'] = $value2['name'];
	    $record_data['accept_email'] = $value3['email'];
	    $record_data['title'] = $email_title;
	    $record_data['content'] = $email_content;
	    $record_data['send_time'] = time();
	    $record_data['status'] = 0;
	    M('EndtimeAlertEmailRecord') -> add($record_data);
	    $error_num++;
	  }
	  
	  usleep(100000);
	}	
      }
    }

    $this -> success('发送完毕,成功' . $success_num . '条，失败' . $error_num . '条！', __ROOT__);
    return;
  }
}

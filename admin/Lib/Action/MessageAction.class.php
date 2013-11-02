<?php
class MessageAction extends CommonAction {

  public function searchemail(){
    if(!empty($_POST['issearch'])){
      $result = array();
      $where = array();
      $company = M('Company');
      $where['email'] = array('neq', '');
      if(!empty($_POST['bgsearch_email_keyword'])){
	$where['_string'] = "( name LIKE '%{$_POST['bgsearch_email_keyword']}%' ) OR ( address LIKE '%{$_POST['bgsearch_email_keyword']}%' ) OR ( manproducts LIKE '%{$_POST['bgsearch_email_keyword']}%' ) OR ( mobilephone LIKE '%{$_POST['bgsearch_email_keyword']}%' ) OR ( email LIKE '%{$_POST['bgsearch_email_keyword']}%' ) OR ( linkman LIKE '%{$_POST['bgsearch_email_keyword']}%' ) OR ( companyphone LIKE '%{$_POST['bgsearch_email_keyword']}%' ) OR ( qqcode LIKE '%{$_POST['bgsearch_email_keyword']}%' ) OR ( website LIKE '%{$_POST['bgsearch_email_keyword']}%' )";
      }
      if(!empty($_POST['bgsearch_email_csid'])){
	$where['csid'] = $this -> _post('bgsearch_email_csid', 'intval');
      }
      if(!empty($_POST['bgsearch_email_csaid'])){
	$where['csaid'] = $this -> _post('bgsearch_email_csaid', 'intval');
      }

      $count_sql = $company -> field('id,email') -> where($where) -> group('email') -> buildSql();
      $count = $company -> table($count_sql . ' T') -> count();
      import('ORG.Util.Page');
      if(! empty ( $_REQUEST ['listRows'] )){
	$listRows = $_REQUEST ['listRows'];
      } else {
	$listRows = 10;
      }
      $page = new Page($count, $listRows);
      $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
      $page -> firstRow = ($pageNum - 1) * $listRows;
      $result['listRows'] = $listRows;
      $result['currentPage'] = $pageNum;
      $result['count'] = $count;

      G('start');
      $result['result'] = $company -> field('id,name,email,addtime,updatetime') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> group('email') -> select();
      $result['time'] = G('start', 'end');
      $this -> assign('result', $result);
    }

    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  
  }

  public function addwaitsendlist($send_arrs){
    $send_arr = array();
    $company = M('Company');
    $count_old = count($_SESSION['admin_send_email_list']);
    if(empty($send_arrs)){
      if(!empty($_GET['issearch'])){
	$where = array();
	$where['email'] = array('neq', '');
	if($_GET['s_csaid'] != 'null'){
	  $where['csid'] = $this -> _get('s_csaid', 'intval');
	}
	if($_GET['s_csaid'] != 'null'){
	  $where['csaid'] = $this -> _get('s_csaid', 'intval');
	}
	if($_GET['s_keyword'] != 'null'){
	  $where['_string'] = "( name LIKE '%{$_GET['s_keyword']}%' ) OR ( address LIKE '%{$_GET['s_keyword']}%' ) OR ( manproducts LIKE '%{$_GET['s_keyword']}%' ) OR ( mobilephone LIKE '%{$_GET['s_keyword']}%' ) OR ( email LIKE '%{$_GET['s_keyword']}%' ) OR ( linkman LIKE '%{$_GET['s_keyword']}%' ) OR ( companyphone LIKE '%{$_GET['s_keyword']}%' ) OR ( qqcode LIKE '%{$_GET['s_keyword']}%' ) OR ( website LIKE '%{$_GET['s_keyword']}%' )";
	}
	$result_temp = $company -> field('id,email') -> where($where) -> group('email') -> select();
	foreach($result_temp as $value){
	  $value['email'] = preg_replace('/\s{2,}|　/U',' ',$value['email']);
	  $temp_arr = explode(' ', $value['email']);
	  foreach($temp_arr as $values){
	    $send_arr[$value['id']] = $values;
	  }
	}
      }else{
	$result_temp = explode(',', $_POST['ids']);
	foreach($result_temp as $key => $value){
	  $temp_string = $company -> getFieldByid($value, 'email');
	  $temp_string = preg_replace('/\s{2,}|　/U',' ',$temp_string);
	  $temp_arr = explode(' ', $temp_string);
	  foreach($temp_arr as $values){
	    $send_arr[$value] = $values;
	  }
	}
      }
    }else{
      $send_arr = $send_arrs;
    }
    if(!is_array($_SESSION['admin_send_email_list'])){
      $_SESSION['admin_send_email_list'] = array();
    }
    $_SESSION['admin_send_email_list'] = array_unique($_SESSION['admin_send_email_list'] + $send_arr);
    $count_total = count($_SESSION['admin_send_email_list']);
    $poor = $count_total - $count_old;
    if($poor){
      $this -> success('添加成功！新增' . $poor .'条记录。目前发送列表里共有 ' . $count_total . ' 条待发送邮件');
    }else{
      $this -> error('添加失败！请不要添加重复记录。目前发送列表里共有 ' . $count_total . ' 条待发送邮件');
    }
  }

  public function editwaitsendlist(){
    $company = M('Company');
    $result = array();
    foreach($_SESSION['admin_send_email_list'] as $key => $value){
      $result[$key]['name'] = $company -> getFieldByid($key, 'name');
      $result[$key]['email'] = $value;
    }
    $this -> assign('result', $result);
    $this -> assign('count', count($_SESSION['admin_send_email_list']));
    $this -> display();
  }

  public function addsendlisttoemailgroup(){
    if(!empty($_POST['name'])){
	$where = array();
	$data = array();
	$data['gid'] = $gid;
	$company = M('Company');
	$where['email'] = array('neq', '');

	if($_POST['keyword'] != 'null'){
	  $where['_string'] = "( name LIKE '%{$_POST['keyword']}%' ) OR ( address LIKE '%{$_POST['keyword']}%' ) OR ( manproducts LIKE '%{$_POST['keyword']}%' ) OR ( mobilephone LIKE '%{$_POST['keyword']}%' ) OR ( email LIKE '%{$_POST['keyword']}%' ) OR ( linkman LIKE '%{$_POST['keyword']}%' ) OR ( companyphone LIKE '%{$_POST['keyword']}%' ) OR ( qqcode LIKE '%{$_POST['keyword']}%' ) OR ( website LIKE '%{$_POST['keyword']}%' )";
	}
	if($_POST['csid'] != 'null'){
	  $where['csid'] = $this -> _post('csid', 'intval');
	}
	if($_POST['csaid'] != 'null'){
	  $where['csaid'] = $this -> _post('csaid', 'intval');
	}
	$result = $company -> field('id,name,email') -> where($where) -> group('email') -> select();

	$group_limit = M('BackgroundSendEmailSetting') -> getFieldByid($_POST['limit_id'], 'group_limit');

	$group_limit = $group_limit ? $group_limit : 100;
	$group_num = (int)ceil(count($result) / $group_limit);
	$result_finish = array();
	for($i = 0; $i < $group_num; $i++){
	  $result_finish[] = array_slice($result, $i*$group_limit, $group_limit);
	}

	$email_group_list = D('BackgroundEmailGroupList');
	$email_group = M('BackgroundEmailGroup');
	foreach($result_finish as $key => $value){
	  $data = array();
	  $data['aid'] = session(C('USER_AUTH_KEY'));
	  $data['remark'] = $this -> _post('remark');
	  $data['addtime'] = time();
	  $data['name'] = $_POST['name'] . '(' . ($key+1) . ')';

	  if($gid = $email_group -> add($data)){
	    foreach($result_finish[$key] as $valuetwo){
	      $valuetwo['email'] = preg_replace('/\s{2,}|　/U',' ',$valuetwo['email']);
	      $temp_arr = explode(' ', $valuetwo['email']);
	      foreach($temp_arr as $values){
		$datas['gid'] = $gid;
		$datas['cid'] = $valuetwo['id'];
		$datas['name'] = $valuetwo['name'];
		$datas['addtime'] = time();
		$datas['email'] = $values;
		$email_group_list -> add($datas);
	      }
	    }
	  }else{
	    $this -> error(L('DATA_ADD_ERROR'));
	  }
	}
	$this -> success(L('DATA_ADD_SUCCESS'));
    }

    $BackgroundSendEmailSetting = M('BackgroundSendEmailSetting');
    $limit = $BackgroundSendEmailSetting -> field('id,email_address,group_limit') -> where(array('aid' => $_SESSION[C('USER_AUTH_KEY')])) -> order('addtime DESC') -> select();
    $this -> assign('limit', $limit);
    $this -> display();
  }

  public function delwaitsendlist(){
    session('admin_send_email_list', null);
    $this -> success(L('DATA_DELETE_SUCCESS'));
  }

  public function delwaitsendemail(){
    unset($_SESSION['admin_send_email_list'][array_search($_GET['email'], $_SESSION['admin_send_email_list'])]);
    $this -> success(L('DATA_DELETE_SUCCESS'));
  }

  public function sendmassemail(){
    $email_list = D('BackgroundSendEmail');
    if(!empty($_POST['recipient'])){
      $company = M('Company');
      set_time_limit(0);
      $recipient_arr = explode(';', $_POST['recipient']);
      if(empty($recipient_arr[count($recipient_arr) - 1])){
	unset($recipient_arr[count($recipient_arr) - 1]);
      }

      $BackgroundSendEmailSetting = M('BackgroundSendEmailSetting');
      $mail_address = $BackgroundSendEmailSetting -> getFieldByid($_POST['send_email'], 'email_address');
      $mail_smtp = $BackgroundSendEmailSetting -> getFieldByid($_POST['send_email'], 'email_SMTP');
      $mail_loginname = $BackgroundSendEmailSetting -> getFieldByid($_POST['send_email'], 'email_account');
      $mail_password = $BackgroundSendEmailSetting -> getFieldByid($_POST['send_email'], 'email_pwd');
      C('MAIL_ADDRESS', $mail_address);
      C('MAIL_SMTP', $mail_smtp);
      C('MAIL_LOGINNAME', $mail_loginname);
      C('MAIL_PASSWORD', $mail_password);
      import('ORG.Util.Mail');
      $success_num = 0;
      $error_num = 0;
      
      foreach($recipient_arr as $value){
	$cid = intval(substr($value, strpos($value, '(') + 1));
	$send_email = substr($value, 0 , strpos($value, '(')) ? substr($value, 0 , strpos($value, '(')) : $value;
	$company_info = $company -> table('yesow_company as c') -> field('c.id,cs.name as csname,csa.name as csaname,c.name,c.address,c.mobilephone,c.companyphone,c.linkman,c.website,c.email,c.manproducts,c.qqcode,cs.domain') -> where(array('c.id' => $cid)) -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> find();
	
	$search = array('{company_id}', '{company_csid}', '{company_csaid}', '{company_name}', '{company_address}', '{company_mobilephone}', '{company_companyphone}', '{company_linkman}', '{company_website}', '{company_email}', '{company_manproducts}', '{company_qqcode}', '{company_domain}');
	$email_content = str_replace($search, $company_info, $_POST['content']);
	$email_title = str_replace($search, $company_info, $_POST['title']);
	
	if(@SendMail($send_email, $email_title, $email_content, 'yesow管理员')){
	  $email_list -> addinfo($send_email, $email_title, $email_content);
	  $success_num++;
	}else{
	  $email_list -> addinfo($send_email, $email_title, $email_content, 0);
	  $error_num++;
	}
	usleep(250000);
      }
      session('admin_send_email_list', null);
      $this -> success('邮件发送完毕！成功：' . $success_num . ' 条。失败：' . $error_num . ' 条。可到邮件发送列表查看相信信息');
    }
    foreach($_SESSION['admin_send_email_list'] as $key => $value){
      $recipientstring .= $value . '(' . $key . ');';
    }
    $this -> assign('recipientstring', $recipientstring);
    $send_template = M('BackgroundEmailTemplate') -> field('id,name') -> select();
    $this -> assign('send_template', $send_template);
    $result_email = M('BackgroundSendEmailSetting') -> field('id,email_address') -> where(array('aid' => $_SESSION[C('USER_AUTH_KEY')])) -> order('addtime DESC') -> select();
    $this -> assign('result_email', $result_email);
    $this -> display();
  }

  public function backgroundsendrecord(){
    $email_list = M('BackgroundSendEmail');
    $where = array();
    if(!empty($_POST['email'])){
      $where['bse.email'] = $this -> _post('email');
    }

    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $dayBegin = mktime(0,0,0,$month,$day,$year);
    $dayEnd = mktime(23,59,59,$month,$day,$year);

    $today_count = $email_list -> where(array('sendtime' => array(array('egt', $dayBegin),array('elt', $dayEnd)))) -> count('id');
    $this -> assign('today_count', $today_count);

    $count = $email_list -> table('yesow_background_send_email as bse') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $email_list -> table('yesow_background_send_email as bse') -> field('bse.id,a.name as aname,bse.email,bse.title,bse.sendtime,bse.status') -> join('yesow_admin as a ON bse.aid = a.id') -> where($where) -> order('sendtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function delbackgroundsendrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $email_list = M('BackgroundSendEmail');
    if($email_list -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function alldelbackgroundsendrecord(){
    $email_list = M('BackgroundSendEmail');
    if($email_list -> where(1) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function intervaldelbackgroundsendrecord(){
    if(!empty($_POST['isdel'])){
      $where_del = array();
      if(!empty($_POST['starttime'])){
	$addtime = $this -> _post('starttime', 'strtotime');
	$where['sendtime'] = array(array('gt', $addtime));
      }
      if(!empty($_POST['endtime'])){
	$endtime = $this -> _post('endtime', 'strtotime');
	$where['sendtime'][] = array('lt', $endtime);
      }
      $email_list = M('BackgroundSendEmail');
      if($email_list -> where($where) -> delete()){
	$this -> success(L('DATA_DELETE_SUCCESS'));
      }else{
	$this -> error(L('DATA_DELETE_ERROR'));
      }
    } 
    $this -> display();
  }

  public function editbackgroundsendrecordinfo(){
    $content = M('BackgroundSendEmail') -> getFieldByid($this -> _get('id', 'intval'), 'content');
    $this -> assign('content', $content);
    $this -> display();
  }

  public function backgroundemailgroup(){
    $email_group = M('BackgroundEmailGroup');
    $where = array();
    $where['g.aid'] = session(C('USER_AUTH_KEY'));
    if(!empty($_POST['name'])){
      $where['g.name'] = array('like', '%' . $this -> _post('name') . '%');
    }

    $count = $email_group -> table('yesow_background_email_group as g') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $email_group -> table('yesow_background_email_group as g') -> field('g.id,g.name,g.remark,g.addtime,tmp.count') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> join('LEFT JOIN (SELECT gid,COUNT(id) as count FROM yesow_background_email_group_list GROUP BY gid) as tmp ON tmp.gid = g.id') -> order('g.id DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addbackgroundemailgroup(){
    if(!empty($_POST['name'])){
      $email_group = D('BackgroundEmailGroup');
      if(!$email_group -> create()){
	$this -> error($email_group -> getError());
      }
      $email_group -> aid = session(C('USER_AUTH_KEY'));
      if($email_group -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function editbackgroundemailgroup(){
    $email_group = M('BackgroundEmailGroup');
    if(!empty($_POST['name'])){
      if(!$email_group -> create()){
	$this -> error($email_group -> getError());
      }
      if($email_group -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $email_group -> field('name,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);

    $this -> display();
  }

  public function delbackgroundemailgroup(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $email_group = M('BackgroundEmailGroup');
    if($email_group -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editemailgrouplist(){
    $email_group_list = M('BackgroundEmailGroupList');
    $where = array();
    $where['gid'] = $this -> _request('gid', 'intval');
    if(!empty($_POST['name'])){
      $where['name'] = array('like', '%' . $this -> _post('name') . '%');
    }

    $count = $email_group_list -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $email_group_list -> field('id,name,email,addtime') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addeditemailgrouplist(){
    if(!empty($_POST['email'])){
      $email_group_list = D('BackgroundEmailGroupList');
      if(!$email_group_list -> create()){
	$this -> error($email_group_list -> getError());
      }
      if($email_group_list -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function deleditemailgrouplist(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $email_group_list = M('BackgroundEmailGroupList');
    if($email_group_list -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editeditemailgrouplist(){
    $email_group_list = M('BackgroundEmailGroupList');
    if(!empty($_POST['email'])){
      if(!$email_group_list -> create()){
	$this -> error($email_group_list -> getError());
      }
      if($email_group_list -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $email_group_list -> field('name,email') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function addgroupwaitsendlist(){
    $email_group_list = M('BackgroundEmailGroupList');
    $where = array();
    $where['gid'] = $this -> _get('id', 'intval');
    $result_temp = $email_group_list -> field('cid,email') -> where($where) -> select();
    $result = array();
    foreach($result_temp as $value){
      $result[$value['cid']] = $value['email'];
    }
    $this -> addwaitsendlist($result);
    
  }

  public function sendemailsetting(){
    $setting = M('BackgroundEmailSetting');
    if(!empty($_POST['search_money'])){
      $where = array();
      $data = array();
      $num = 0;
      foreach($_POST as $key => $value){
	$where['name'] = $key;
	$data['value'] = $value;
	$num += $setting -> where($where) -> save($data);      
      }
      if($num > 0){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $search_money = $setting -> getFieldByname('search_money', 'value');
    $this -> assign('search_money', $search_money);
    $this -> display();
  }

  public function emailtemplate(){
    $template = M('BackgroundEmailTemplate');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('like', '%' . $this -> _post('name') . '%');
    }

    $count = $template -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $template -> field('id,name,addtime') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  
  }

  public function addemailtemplate(){
    if(!empty($_POST['name'])){
      $template = D('BackgroundEmailTemplate');
      if(!$template -> create()){
	$this -> error($template -> getError());
      }
      if($template -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delemailtemplate(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $template = M('BackgroundEmailTemplate');
    if($template -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editemailtemplate(){
    $template = M('BackgroundEmailTemplate');
    if(!empty($_POST['name'])){
      if(!$template -> create()){
	$this -> error($template -> getError());
      }
      if($template -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $template -> field('name,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function memberemailsendrecord(){
    $MemberSendEmailRecord = M('MemberSendEmailRecord');

    $where = array();
    if(!empty($_POST['username'])){
      $mid = M('Member') -> getFieldByname($_POST['username'], 'id');
      $where['mssr.mid'] = $mid;
    }

    $count = $MemberSendEmailRecord -> alias('mssr') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberSendEmailRecord -> alias('mssr') -> field('mssr.id,m.name as mname,mssr.sendtime,mssr.content,mssr.sendemail,mssr.statuscode,mssr.title,mssr.tosendemail') -> join('yesow_member as m ON mssr.mid = m.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sendtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delmemberemailsendrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberSendEmailRecord = M('MemberSendEmailRecord');
    if($MemberSendEmailRecord -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function memberemailsearchrecord(){
    $MemberSearchEmailRecord = M('MemberSearchEmailRecord');
    $where = array();
    if(!empty($_POST['keyword'])){
      $where['mssr.keyword'] = array('LIKE', '%' . $this -> _post('keyword') . '%');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['mssr.searchtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['mssr.searchtime'][] = array('lt', $endtime);
    }

    $count = $MemberSearchEmailRecord -> alias('mssr') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberSearchEmailRecord -> alias('mssr') -> field('mssr.id,mssr.keyword,m.name as mname,tmp.count,mssr.checknum,mssr.ip,mssr.searchtime') -> join('yesow_member as m ON mssr.mid = m.id') -> join('LEFT JOIN (SELECT keyword,count(id) as count FROM yesow_member_search_email_record GROUP BY keyword) as tmp ON mssr.keyword = tmp.keyword') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('mssr.searchtime DESC') -> select();
    $this -> assign('result', $result);

    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function delmemberemailsearchrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberSearchEmailRecord = M('MemberSearchEmailRecord');
    if($MemberSearchEmailRecord -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //

  public function memberemailgroup(){
    $MemberEmailGroup = M('MemberEmailGroup');
    $where = array();
    if(!empty($_POST['keyword'])){
      $where['msg.name'] = array('LIKE', '%' . $this -> _post('keyword') . '%');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['msg.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['msg.addtime'][] = array('lt', $endtime);
    }

    $count = $MemberEmailGroup -> alias('msg') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberEmailGroup -> alias('msg') -> field('msg.id,msg.name,msg.addtime,tmp.count,m.name as mname') -> join('yesow_member as m ON msg.mid = m.id') -> join('LEFT JOIN (SELECT gid,COUNT(id) as count FROM yesow_member_email_group_list GROUP BY gid) as tmp ON tmp.gid = msg.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('msg.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delmemberemailgroup(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberEmailGroup = M('MemberEmailGroup');
    if($MemberEmailGroup -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editmemberemailgroup(){
    $MemberEmailGroupList = M('MemberEmailGroupList');
    $id = $this -> _request('id', 'intval');
    $where = array();
    $where['gid'] = $id;
    if(!empty($_POST['name'])){
      $where['realnumber'] = $this -> _post('name');
    }

    $count = $MemberEmailGroupList -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberEmailGroupList -> field('id,realnumber') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addeditmemberemailgroup(){
    if(!empty($_POST['realnumber'])){
      $MemberEmailGroupList = M('MemberEmailGroupList');
      if(!$MemberEmailGroupList -> create()){
	$this -> error($MemberEmailGroupList -> getError());
      }
      $MemberEmailGroupList -> hidenumber = substr($_POST['realnumber'], 0 ,3) . '****' . strstr($_POST['realnumber'], '@');
      if($MemberEmailGroupList -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display(); 
  }

  public function editeditmemberemailgroup(){
    $MemberEmailGroupList = M('MemberEmailGroupList');

    if(!empty($_POST['realnumber'])){
      if(!$MemberEmailGroupList -> create()){
	$this -> error($MemberEmailGroupList -> getError());
      }
      $MemberEmailGroupList -> hidenumber = substr($_POST['realnumber'], 0 ,3) . '****' . strstr($_POST['realnumber'], '@');
      if($MemberEmailGroupList -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $MemberEmailGroupList -> field('id,realnumber') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  
  }

  public function deleditmemberemailgroup(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberEmailGroupList = M('MemberEmailGroupList');
    if($MemberEmailGroupList -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //

  public function apisendtype(){
    $sendtype = M('SmsSendType');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('like', '%' . $this -> _post('name') . '%');
    }

    $count = $sendtype -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $sendtype -> field('id,name,apicode,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();

    $setting = M('SmsSetting');
    $sms_username = $setting -> getFieldByname('sms_username', 'value');
    $sms_password = $setting -> getFieldByname('sms_password', 'value');
    $balance = file_get_contents('http://www.vip.86aaa.com/api.aspx?SendType=101&Code=utf-8&UserName=' . $sms_username . '&Pwd=' . $sms_password . '');
    preg_match_all('/[^a-z]([0-9]+)/', $balance, $balance_arr);
    foreach($result as $key => $value){
      $result[$key]['balance'] = $balance_arr[1][$value['apicode']];
    }
    $this -> assign('balance_arr', $balance_arr);
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addapisendtype(){
    if(!empty($_POST['name'])){
      $sendtype = M('SmsSendType');
      if(!$sendtype -> create()){
	$this -> error($sendtype -> getError());
      }
      if($sendtype -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delapisendtype(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $sendtype = M('SmsSendType');
    if($sendtype -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editapisendtype(){
    $sendtype = M('SmsSendType');
    if(!empty($_POST['name'])){
      if(!$sendtype -> create()){
	$this -> error($sendtype -> getError());
      }
      if($sendtype -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $sendtype -> field('name,apicode,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function sendsmssetting(){
    $setting = M('SmsSetting');
    if(!empty($_POST['sms_username'])){
      $where = array();
      $data = array();
      $num = 0;
      foreach($_POST as $key => $value){
	$where['name'] = $key;
	$data['value'] = $value;
	$num += $setting -> where($where) -> save($data);      
      }
      if($num > 0){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $sms_username = $setting -> getFieldByname('sms_username', 'value');
    $sms_password = $setting -> getFieldByname('sms_password', 'value');
    $search_phone_price = $setting -> getFieldByname('search_phone_price', 'value');
    $send_sms_price = $setting -> getFieldByname('send_sms_price', 'value');
    $this -> assign('sms_username', $sms_username);
    $this -> assign('sms_password', $sms_password);
    $this -> assign('search_phone_price', $search_phone_price);
    $this -> assign('send_sms_price', $send_sms_price);
    $this -> display();
  }

  public function membersmssendrecord(){
    $MemberSendSmsRecord = M('MemberSendSmsRecord');

    $where = array();
    if(!empty($_POST['username'])){
      $mid = M('Member') -> getFieldByname($_POST['username'], 'id');
      $where['mssr.mid'] = $mid;
    }

    $count = $MemberSendSmsRecord -> table('yesow_member_send_sms_record as mssr') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberSendSmsRecord -> table('yesow_member_send_sms_record as mssr') -> field('mssr.id,m.name as mname,mssr.sendtime,mssr.content,mssr.sendphone,mssr.statuscode,sst.name as sendtype,mssr.price') -> join('yesow_sms_send_type as sst ON mssr.sendtype = sst.id') -> join('yesow_member as m ON mssr.mid = m.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sendtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delmembersmssendrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberSendSmsRecord = M('MemberSendSmsRecord');
    if($MemberSendSmsRecord -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function membersmssearchrecord(){
    $MemberSearchSmsRecord = M('MemberSearchSmsRecord');
    $where = array();
    if(!empty($_POST['keyword'])){
      $where['mssr.keyword'] = array('LIKE', '%' . $this -> _post('keyword') . '%');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['mssr.searchtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['mssr.searchtime'][] = array('lt', $endtime);
    }

    $count = $MemberSearchSmsRecord -> table('yesow_member_search_sms_record as mssr') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberSearchSmsRecord -> table('yesow_member_search_sms_record as mssr') -> field('mssr.id,mssr.keyword,m.name as mname,tmp.count,mssr.checknum,mssr.ip,mssr.searchtime') -> join('yesow_member as m ON mssr.mid = m.id') -> join('LEFT JOIN (SELECT keyword,count(id) as count FROM yesow_member_search_sms_record GROUP BY keyword) as tmp ON mssr.keyword = tmp.keyword') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('mssr.searchtime DESC') -> select();
    $this -> assign('result', $result);

    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function delmembersmssearchrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberSearchSmsRecord = M('MemberSearchSmsRecord');
    if($MemberSearchSmsRecord -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function membersmsgroup(){
    $MemberSmsGroup = M('MemberSmsGroup');
    $where = array();
    if(!empty($_POST['keyword'])){
      $where['msg.name'] = array('LIKE', '%' . $this -> _post('keyword') . '%');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['msg.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['msg.addtime'][] = array('lt', $endtime);
    }

    $count = $MemberSmsGroup -> table('yesow_member_sms_group as msg') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberSmsGroup -> table('yesow_member_sms_group as msg') -> field('msg.id,msg.name,msg.addtime,tmp.count,m.name as mname') -> join('yesow_member as m ON msg.mid = m.id') -> join('LEFT JOIN (SELECT gid,COUNT(id) as count FROM yesow_member_sms_group_list GROUP BY gid) as tmp ON tmp.gid = msg.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('msg.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delmembersmsgroup(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberSmsGroup = M('MemberSmsGroup');
    if($MemberSmsGroup -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editmembersmsgroup(){
    $MemberSmsGroupList = M('MemberSmsGroupList');
    $id = $this -> _request('id', 'intval');
    $where = array();
    $where['gid'] = $id;
    if(!empty($_POST['name'])){
      $where['realnumber'] = $this -> _post('name');
    }

    $count = $MemberSmsGroupList -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberSmsGroupList -> field('id,realnumber') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addeditmembersmsgroup(){
    if(!empty($_POST['realnumber'])){
      $MemberSmsGroupList = M('MemberSmsGroupList');
      if(!$MemberSmsGroupList -> create()){
	$this -> error($MemberSmsGroupList -> getError());
      }
      $MemberSmsGroupList -> hidenumber = substr_replace($_POST['realnumber'], '****', 3, 4);
      if($MemberSmsGroupList -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display(); 
  }

  public function editeditmembersmsgroup(){
    $MemberSmsGroupList = M('MemberSmsGroupList');

    if(!empty($_POST['realnumber'])){
      if(!$MemberSmsGroupList -> create()){
	$this -> error($MemberSmsGroupList -> getError());
      }
      $MemberSmsGroupList -> hidenumber = substr_replace($_POST['realnumber'], '****', 3, 4);
      if($MemberSmsGroupList -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $MemberSmsGroupList -> field('id,realnumber') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  
  }

  public function deleditmembersmsgroup(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberSmsGroupList = M('MemberSmsGroupList');
    if($MemberSmsGroupList -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function membersendemail(){
    $MemberEmailSetting = M('MemberEmailSetting');
    $where = array();
    if(!empty($_POST['mname'])){
      $where['m.name'] = $this -> _post('mname');
    }
    $count = $MemberEmailSetting -> alias('e') -> join('yesow_member as m ON e.mid = m.id') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $MemberEmailSetting -> alias('e') -> field('e.id,e.email_address,e.email_SMTP,e.email_account,e.addtime,m.name as mname,e.group_limit') -> join('yesow_member as m ON e.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('e.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editmembersendemail(){
    $MemberEmailSetting = M('MemberEmailSetting');

    if(!empty($_POST['email_address'])){
      if(!$MemberEmailSetting -> create()){
	$this -> error($MemberEmailSetting -> getError());
      }
      if($MemberEmailSetting -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $MemberEmailSetting -> field('email_address,email_SMTP,email_account,email_pwd,group_limit') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function delmembersendemail(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberEmailSetting = M('MemberEmailSetting');
    if($MemberEmailSetting -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function backgroundsendemailsetting(){
    $BackgroundSendEmailSetting = M('BackgroundSendEmailSetting');
    $where = array();
    if(!empty($_POST['mname'])){
      $where['m.name'] = $this -> _post('mname');
    }
    if($_SESSION[C('USER_AUTH_KEY')] != 1){
      $where['s.aid'] = session(C('USER_AUTH_KEY'));
    }
    $count = $BackgroundSendEmailSetting -> alias('s') -> join('yesow_admin as m ON s.aid = m.id') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $BackgroundSendEmailSetting -> alias('s') -> field('s.id,s.email_address,s.email_SMTP,s.email_account,s.group_limit,m.name as mname,s.addtime') -> join('yesow_admin as m ON s.aid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('s.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addbackgroundsendemailsetting(){
    if(!empty($_POST['email_address'])){
      $BackgroundSendEmailSetting = M('BackgroundSendEmailSetting');
      if(!$BackgroundSendEmailSetting -> create()){
	$this -> error($BackgroundSendEmailSetting -> getError());
      }
      $BackgroundSendEmailSetting -> aid = session(C('USER_AUTH_KEY'));
      $BackgroundSendEmailSetting -> addtime = time();
      if($BackgroundSendEmailSetting -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delbackgroundsendemailsetting(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $BackgroundSendEmailSetting = M('BackgroundSendEmailSetting');
    if($BackgroundSendEmailSetting -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editbackgroundsendemailsetting(){
    $BackgroundSendEmailSetting = M('BackgroundSendEmailSetting');

    if(!empty($_POST['email_address'])){
      if(!$BackgroundSendEmailSetting -> create()){
	$this -> error($BackgroundSendEmailSetting -> getError());
      }
      if($BackgroundSendEmailSetting -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $BackgroundSendEmailSetting -> field('email_address,email_SMTP,email_account,email_pwd,group_limit') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function illegal(){
    $SendSmsIllegalWord = M('SendSmsIllegalWord');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $SendSmsIllegalWord -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $SendSmsIllegalWord -> field('id,name,replace,addtime,remark') -> order('addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addillegal(){
    if(!empty($_POST['name'])){
      $SendSmsIllegalWord = D('SendSmsIllegalWord');
      if(!$SendSmsIllegalWord -> create()){
	$this -> error($SendSmsIllegalWord -> getError());
      }
      if($SendSmsIllegalWord -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delillegal(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $SendSmsIllegalWord = M('SendSmsIllegalWord');
    if($SendSmsIllegalWord -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editillegal(){
    $SendSmsIllegalWord = D('SendSmsIllegalWord');
    if(!empty($_POST['name'])){
      if(!$SendSmsIllegalWord -> create()){
	$this -> error($SendSmsIllegalWord -> getError());
      }
      if($SendSmsIllegalWord -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $SendSmsIllegalWord -> field('name,replace,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function messsendemailsetting(){
    $MassEmailSetting = M('MassEmailSetting');
    $where = array();
    if(!empty($_POST['name'])){
      $where['type_zh'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $MassEmailSetting -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $MassEmailSetting -> field('id,type_en,type_zh,send_address,email_smtp,send_account,send_pwd,addtime') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addmesssendemailsetting(){
    if(!empty($_POST['send_address'])){
      $MassEmailSetting = M('MassEmailSetting');
      if(!$MassEmailSetting -> create()){
	$this -> error($MassEmailSetting -> getError());
      }
      $MassEmailSetting -> addtime = time();
      if($MassEmailSetting -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delmesssendemailsetting(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MassEmailSetting = M('MassEmailSetting');
    if($MassEmailSetting -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editmesssendemailsetting(){
    $MassEmailSetting = M('MassEmailSetting');

    if(!empty($_POST['send_address'])){
      if(!$MassEmailSetting -> create()){
	$this -> error($MassEmailSetting -> getError());
      }
      if($MassEmailSetting -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $MassEmailSetting -> field('type_en,type_zh,send_address,email_smtp,send_account,send_pwd') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function messemailtemplate(){
    $MassEmailTemplate = M('MassEmailTemplate');
    $where = array();
    if(!empty($_POST['eid'])){
      $where['t.eid'] = $this -> _post('eid', 'intval');
    }
    $count = $MassEmailTemplate -> alias('t') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $MassEmailTemplate -> alias('t') -> field('t.id,t.addtime,e.type_zh,e.send_address') -> join('yesow_mass_email_setting as e ON t.eid = e.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('t.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $MassEmailSetting = M('MassEmailSetting');
    $result_email = $MassEmailSetting -> field('id,type_zh,send_address') -> select();
    $this -> assign('result_email', $result_email);
    $this -> display();
  }

  public function addmessemailtemplate(){
    if(!empty($_POST['eid'])){
      $MassEmailTemplate = D('MassEmailTemplate');
      if(!$MassEmailTemplate -> create()){
	$this -> error($MassEmailTemplate -> getError());
      }
      if($MassEmailTemplate -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $MassEmailSetting = M('MassEmailSetting');
    $result_email = $MassEmailSetting -> field('id,type_zh,send_address') -> select();
    $this -> assign('result_email', $result_email);
    $this -> display();
  }

  public function delmessemailtemplate(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MassEmailTemplate = M('MassEmailTemplate');
    if($MassEmailTemplate -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editmessemailtemplate(){
    $MassEmailTemplate = M('MassEmailTemplate');

    if(!empty($_POST['eid'])){
      if(!$MassEmailTemplate -> create()){
	$this -> error($MassEmailTemplate -> getError());
      }
      if($MassEmailTemplate -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $MassEmailTemplate -> field('eid,title,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);

    $MassEmailSetting = M('MassEmailSetting');
    $result_email = $MassEmailSetting -> field('id,type_zh,send_address') -> select();
    $this -> assign('result_email', $result_email);
    $this -> display();
  }

  public function masssendrecord(){
    $MassEmailRecord = M('MassEmailRecord');

    $where = array();
    if(!empty($_POST['accept_email'])){
      $where['e.accept_email'] = $this -> _post('accept_email');
    }

    $count = $MassEmailRecord -> alias('r') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MassEmailRecord -> alias('r') -> field('r.id,r.send_email,r.accept_email,r.title,r.content,r.sendtime,r.status,e.type_zh') -> join('yesow_mass_email_setting as e ON r.eid = e.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('r.sendtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editmasssendrecord(){
    $content = M('MassEmailRecord') -> getFieldByid($this -> _get('id', 'intval'), 'content');
    $this -> assign('content', $content);
    $this -> display();
  }

  public function delmasssendrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MassEmailRecord = M('MassEmailRecord');
    if($MassEmailRecord -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function orderacceptemail(){
    $OrderAcceptEmail = M('OrderAcceptEmail');
    $where = array();
    if(!empty($_POST['email_address'])){
      $where['email_address'] = array('like', '%' . $this -> _post('email_address') . '%');
    }

    $count = $OrderAcceptEmail -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $OrderAcceptEmail -> field('id,email_address,addtime,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();

    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addorderacceptemail(){
    if(!empty($_POST['email_address'])){
      $OrderAcceptEmail = M('OrderAcceptEmail');
      if(!$OrderAcceptEmail -> create()){
	$this -> error($OrderAcceptEmail -> getError());
      }
      $OrderAcceptEmail -> addtime = time();
      if($OrderAcceptEmail -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delorderacceptemail(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $OrderAcceptEmail = M('OrderAcceptEmail');
    if($OrderAcceptEmail -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editorderacceptemail(){
    $OrderAcceptEmail = M('OrderAcceptEmail');
    if(!empty($_POST['email_address'])){
      if(!$OrderAcceptEmail -> create()){
	$this -> error($OrderAcceptEmail -> getError());
      }
      if($OrderAcceptEmail -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $OrderAcceptEmail -> field('email_address,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function orderacceptemailrecord(){
    $OrderAcceptRecord = M('OrderAcceptRecord');

    $where = array();
    if(!empty($_POST['accept_email'])){
      $where['accept_email'] = $this -> _post('accept_email');
    }

    $count = $OrderAcceptRecord -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $OrderAcceptRecord -> field('id,send_type,accept_email,title,content,send_time,status') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('send_time DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delorderacceptemailrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $OrderAcceptRecord = M('OrderAcceptRecord');
    if($OrderAcceptRecord -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editorderacceptemailrecord(){
    $content = M('OrderAcceptRecord') -> getFieldByid($this -> _get('id', 'intval'), 'content');
    $this -> assign('content', $content);
    $this -> display();
  }

  public function endtimealerttime(){
    $EndtimeAlertTime = M('EndtimeAlertTime');

    $where = array();
    if(!empty($_POST['time'])){
      $where['time'] = $this -> _post('time');
    }

    $count = $EndtimeAlertTime -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $EndtimeAlertTime -> field('id,time,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('time ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addendtimealerttime(){
    if(!empty($_POST['time']) || $_POST['time'] == '0'){
      $EndtimeAlertTime = M('EndtimeAlertTime');
      if(!$EndtimeAlertTime -> create()){
	$this -> error($EndtimeAlertTime -> getError());
      }
      if($EndtimeAlertTime -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delendtimealerttime(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $EndtimeAlertTime = M('EndtimeAlertTime');
    if($EndtimeAlertTime -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editendtimealerttime(){
    $EndtimeAlertTime = M('EndtimeAlertTime');
    if(!empty($_POST['time']) || $_POST['time'] == '0'){
      if(!$EndtimeAlertTime -> create()){
	$this -> error($EndtimeAlertTime -> getError());
      }
      if($EndtimeAlertTime -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $EndtimeAlertTime -> field('time,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function endtimealertemail(){
    $EndtimeAlertEmail = M('EndtimeAlertEmail');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = $_POST['name'];
    }
    $count = $EndtimeAlertEmail -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $EndtimeAlertEmail -> field('id,model_name,name,send_address,send_smtp,send_email,addtime') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addendtimealertemail(){
    if(!empty($_POST['name'])){
      $EndtimeAlertEmail = M('EndtimeAlertEmail');
      if(!$EndtimeAlertEmail -> create()){
	$this -> error($EndtimeAlertEmail -> getError());
      }
      $EndtimeAlertEmail -> addtime = time();
      if($EndtimeAlertEmail -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delendtimealertemail(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $EndtimeAlertEmail = M('EndtimeAlertEmail');
    if($EndtimeAlertEmail -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editendtimealertemail(){
    $EndtimeAlertEmail = M('EndtimeAlertEmail');

    if(!empty($_POST['name'])){
      if(!$EndtimeAlertEmail -> create()){
	$this -> error($EndtimeAlertEmail -> getError());
      }
      if($EndtimeAlertEmail -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $EndtimeAlertEmail -> field('model_name,name,send_address,send_smtp,send_email,email_pwd,title,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);

    $this -> display();
  }

  
}

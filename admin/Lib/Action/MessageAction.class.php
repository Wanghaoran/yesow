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

	$group_limit = M('BackgroundEmailGroupLimit') -> getFieldByaid($_SESSION[C('USER_AUTH_KEY')], 'limit_num');

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

      $setting = M('BackgroundEmailSetting');
      $mail_address = $setting -> getFieldByname('mail_address', 'value');
      $mail_smtp = $setting -> getFieldByname('mail_smtp', 'value');
      $mail_loginname = $setting -> getFieldByname('mail_loginname', 'value');
      $mail_password = $setting -> getFieldByname('mail_password', 'value');
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
	
	if(SendMail($send_email, $email_title, $email_content, 'yesow管理员')){
	  $email_list -> addinfo($send_email, $email_title, $email_content);
	  $success_num++;
	}else{
	  $email_list -> addinfo($send_email, $email_title, $email_content, 0);
	  $error_num++;
	}
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
    if(!empty($_POST['mail_address'])){
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
    $mail_address = $setting -> getFieldByname('mail_address', 'value');
    $mail_smtp = $setting -> getFieldByname('mail_smtp', 'value');
    $mail_loginname = $setting -> getFieldByname('mail_loginname', 'value');
    $mail_password = $setting -> getFieldByname('mail_password', 'value');
    $search_money = $setting -> getFieldByname('search_money', 'value');
    $this -> assign('mail_address', $mail_address);
    $this -> assign('mail_smtp', $mail_smtp);
    $this -> assign('mail_loginname', $mail_loginname);
    $this -> assign('mail_password', $mail_password);
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

    $result = $MemberSendEmailRecord -> alias('mssr') -> field('mssr.id,m.name as mname,mssr.sendtime,mssr.content,mssr.sendemail,mssr.statuscode') -> join('yesow_member as m ON mssr.mid = m.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sendtime DESC') -> select();
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

  public function emailgrouplimit(){
    $admin = M('Admin');
    $where = array();
    if(!empty($_POST['aname'])){
      $where['a.name'] = $this -> _post('aname');
    }
    if($_SESSION[C('USER_AUTH_KEY')] != 1){
      $where['a.id'] = session(C('USER_AUTH_KEY'));
    }

    $count = $admin -> alias('a') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $admin -> alias('a') -> field('a.id,a.name,l.limit_num') -> join('yesow_background_email_group_limit as l ON l.aid = a.id') -> order('a.id ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editemailgrouplimit(){
    if(!empty($_POST['limit_num'])){
      $BackgroundEmailGroupLimit = M('BackgroundEmailGroupLimit');
      $result = $BackgroundEmailGroupLimit -> getFieldByaid($_POST['aid'], 'limit_num');
      if($result){
	$num = $BackgroundEmailGroupLimit -> where(array('aid' => $_POST['aid'])) -> save(array('limit_num' => $_POST['limit_num']));    
      }else{
	$num = $BackgroundEmailGroupLimit -> add(array('aid' => $_POST['aid'], 'limit_num' => $_POST['limit_num']));
      }
      if($num){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $admin = M('Admin');
    $result = $admin -> alias('a') -> field('a.id,a.name,l.limit_num') -> join('yesow_background_email_group_limit as l ON l.aid = a.id') -> where(array('a.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
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
    $result = $MemberEmailSetting -> alias('e') -> field('e.id,e.email_address,e.email_SMTP,e.email_account,e.addtime,m.name as mname') -> join('yesow_member as m ON e.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('e.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

}

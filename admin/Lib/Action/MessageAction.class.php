<?php
class MessageAction extends CommonAction {

  /* ------------ 邮件群发管理 ------------ */

  //速查邮件搜索
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

      //page
      $count_sql = $company -> field('id,email') -> where($where) -> group('email') -> buildSql();//去重
      $count = $company -> table($count_sql . ' T') -> count();
      import('ORG.Util.Page');
      if(! empty ( $_REQUEST ['listRows'] )){
	$listRows = $_REQUEST ['listRows'];
      } else {
	$listRows = 10;
      }
      $page = new Page($count, $listRows);
      //当前页数
      $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
      $page -> firstRow = ($pageNum - 1) * $listRows;
      //每页条数
      $result['listRows'] = $listRows;
      //当前页数
      $result['currentPage'] = $pageNum;
      $result['count'] = $count;

      //search time
      G('start');
      //result
      $result['result'] = $company -> field('id,name,email,addtime,updatetime') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> group('email') -> select();
      //将查询时间写入结果数组
      $result['time'] = G('start', 'end');
      $this -> assign('result', $result);
    }

    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  
  }

  //添加到待发送列表
  public function addwaitsendlist($send_arrs){
    $send_arr = array();
    $company = M('Company');
    $count_old = count($_SESSION['admin_send_email_list']);
    if(empty($send_arrs)){
      //全部下载，重新搜索
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
	  $send_arr[$value['id']] = $value['email'];
	}
      }else{
	$result_temp = explode(',', $_POST['ids']);
	foreach($result_temp as $key => $value){
	  $send_arr[$value] = $company -> getFieldByid($value, 'email');
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

  //编辑待发送列表
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

  //添加全部到通讯录
  public function addsendlisttoemailgroup(){
    //处理添加
    if(!empty($_POST['name'])){
      $email_group = D('BackgroundEmailGroup');
      if(!$email_group -> create()){
	$this -> error($email_group -> getError());
      }
      $email_group -> aid = session(C('USER_AUTH_KEY'));
      if($gid = $email_group -> add()){
	$email_group_list = D('BackgroundEmailGroupList');
	//添加组内记录
	//重新搜索
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
	foreach($result as $value){
	  $data['cid'] = $value['id'];
	  $data['name'] = $value['name'];
	  $data['email'] = $value['email'];
	  $data['addtime'] = time();
	  $email_group_list -> add($data);
	}
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //清空待发送列表
  public function delwaitsendlist(){
    session('admin_send_email_list', null);
    $this -> success(L('DATA_DELETE_SUCCESS'));
  }

  //删除待发送邮箱
  public function delwaitsendemail(){
    unset($_SESSION['admin_send_email_list'][array_search($_GET['email'], $_SESSION['admin_send_email_list'])]);
    $this -> success(L('DATA_DELETE_SUCCESS'));
  }

  //速查邮件群发
  public function sendmassemail(){
    $email_list = D('BackgroundSendEmail');
    //执行发送
    if(!empty($_POST['recipient'])){
      $company = M('Company');
      set_time_limit(0);
      $recipient_arr = explode(';', $_POST['recipient']);
      if(empty($recipient_arr[count($recipient_arr) - 1])){
	unset($recipient_arr[count($recipient_arr) - 1]);
      }

      //读取发送邮件配置
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
	$company_info = $company -> table('yesow_company as c') -> field('c.id,cs.name as csname,csa.name as csaname,c.name,c.address,c.mobilephone,c.companyphone,c.linkman,c.website,c.email,c.manproducts,c.qqcode,c.mobilephone') -> where(array('c.id' => $cid)) -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> find();
	
	//模板替换
	$search = array('{company_id}', '{company_csid}', '{company_csaid}', '{company_name}', '{company_address}', '{company_mobilephone}', '{company_companyphone}', '{company_linkman}', '{company_website}', '{company_email}', '{company_manproducts}', '{company_qqcode}', '{company_mobilephine}');
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
      //清除待发送列表
      session('admin_send_email_list', null);
      $this -> success('邮件发送完毕！成功：' . $success_num . ' 条。失败：' . $error_num . ' 条。可到邮件发送列表查看相信信息');
    }
    //读取收件人列表
    foreach($_SESSION['admin_send_email_list'] as $key => $value){
      $recipientstring .= $value . '(' . $key . ');';
    }
    $this -> assign('recipientstring', $recipientstring);
    //读取模板
    $send_template = M('BackgroundEmailTemplate') -> field('id,name') -> select();
    $this -> assign('send_template', $send_template);
    $this -> display();
  }

  //后台发送记录
  public function backgroundsendrecord(){
    $email_list = M('BackgroundSendEmail');
    $where = array();
    if(!empty($_POST['email'])){
      $where['bse.email'] = $this -> _post('email');
    }

    //今日已发送数量
    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $dayBegin = mktime(0,0,0,$month,$day,$year);//当天开始时间戳
    $dayEnd = mktime(23,59,59,$month,$day,$year);//当天结束时间戳

    $today_count = $email_list -> where(array('sendtime' => array(array('egt', $dayBegin),array('elt', $dayEnd)))) -> count('id');
    $this -> assign('today_count', $today_count);

    //记录总数
    $count = $email_list -> table('yesow_background_send_email as bse') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    //当前页数
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $email_list -> table('yesow_background_send_email as bse') -> field('bse.id,a.name as aname,bse.email,bse.title,bse.sendtime,bse.status') -> join('yesow_admin as a ON bse.aid = a.id') -> where($where) -> order('sendtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  //删除后台发送记录
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

  //查看后台已发送记录正文
  public function editbackgroundsendrecordinfo(){
    $content = M('BackgroundSendEmail') -> getFieldByid($this -> _get('id', 'intval'), 'content');
    $this -> assign('content', $content);
    $this -> display();
  }

  //速查邮件通讯录
  public function backgroundemailgroup(){
    $email_group = M('BackgroundEmailGroup');
    $where = array();
    $where['g.aid'] = session(C('USER_AUTH_KEY'));
    if(!empty($_POST['name'])){
      $where['g.name'] = array('like', '%' . $this -> _post('name') . '%');
    }

    //记录总数
    $count = $email_group -> table('yesow_background_email_group as g') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    //当前页数
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $email_group -> table('yesow_background_email_group as g') -> field('g.id,g.name,g.remark,g.addtime,tmp.count') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> join('LEFT JOIN (SELECT gid,COUNT(id) as count FROM yesow_background_email_group_list GROUP BY gid) as tmp ON tmp.gid = g.id') -> order('g.addtime DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加后台邮件通讯录
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

  //编辑后台邮件通讯录
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

  //删除后台邮件通讯录
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

  //查看后台邮件通讯录详情
  public function editemailgrouplist(){
    $email_group_list = M('BackgroundEmailGroupList');
    $where = array();
    $where['gid'] = $this -> _request('gid', 'intval');
    if(!empty($_POST['name'])){
      $where['name'] = array('like', '%' . $this -> _post('name') . '%');
    }

    //记录总数
    $count = $email_group_list -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    //当前页数
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $email_group_list -> field('id,name,email,addtime') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  //添加后台邮件通讯录详情
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

  //删除后台邮件通讯录详情
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

  //编辑后台邮件通讯录详情
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

  //添加到待发送列表
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

  //群发邮件参数设置
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
    $this -> assign('mail_address', $mail_address);
    $this -> assign('mail_smtp', $mail_smtp);
    $this -> assign('mail_loginname', $mail_loginname);
    $this -> assign('mail_password', $mail_password);
    $this -> display();
  }

  //邮件模板管理
  public function emailtemplate(){
    $template = M('BackgroundEmailTemplate');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('like', '%' . $this -> _post('name') . '%');
    }

    //记录总数
    $count = $template -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    //当前页数
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $template -> field('id,name,addtime') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  
  }

  //新增邮件模板
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

  //删除邮件模板
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

  //编辑邮件模板
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


  /* ------------ 邮件群发管理 ------------ */

}

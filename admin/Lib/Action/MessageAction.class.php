<?php
class MessageAction extends CommonAction {

  /* ------------ 邮件群发管理 ------------ */

  //后台邮件搜索
  public function searchemail(){
    if(!empty($_POST['bgsearch_email_csid'])){
      $result = array();
      $where = array();
      $company = M('Company');
      $where['csid'] = $this -> _post('bgsearch_email_csid', 'intval');
      $where['email'] = array('neq', '');
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
  public function addwaitsendlist(){
    $send_arr = array();
    $count_old = count($_SESSION['admin_send_email_list']);
    $send_arr = explode(',', $_POST['ids']);
    if(!is_array($_SESSION['admin_send_email_list'])){
      $_SESSION['admin_send_email_list'] = array();
    }
    $_SESSION['admin_send_email_list'] = array_merge($_SESSION['admin_send_email_list'], $send_arr);
    $_SESSION['admin_send_email_list'] = array_unique($_SESSION['admin_send_email_list']);
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
    $where['id'] = array('in', $_SESSION['admin_send_email_list']);
    $result = M('Company') -> field('id,name,email') -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('count', count($result));
    $this -> display();
  }

  //清空待发送列表
  public function delwaitsendlist(){
    session('admin_send_email_list', null);
    $this -> success(L('DATA_DELETE_SUCCESS'));
  }

  //删除待发送邮箱
  public function delwaitsendemail(){
    unset($_SESSION['admin_send_email_list'][array_search($_GET['id'], $_SESSION['admin_send_email_list'])]);
    $this -> success(L('DATA_DELETE_SUCCESS'));
  }

  //后台邮件群发
  public function sendmassemail(){
    $email_list = D('BackgroundSendEmail');
    //执行发送
    if(!empty($_POST['recipient'])){
      $recipient_arr = explode(';', $_POST['recipient']);
      if(empty($recipient_arr[count($recipient_arr) - 1])){
	unset($recipient_arr[count($recipient_arr) - 1]);
      }

      C('MAIL_ADDRESS', 'yesow@yesow.com');
      C('MAIL_SMTP', 'smtp.exmail.qq.com');
      C('MAIL_LOGINNAME', 'yesow@yesow.com');
      C('MAIL_PASSWORD', 'lyz008');
      import('ORG.Util.Mail');
      $success_num = 0;
      $error_num = 0;
      foreach($recipient_arr as $value){
	if(SendMail($value, $_POST['title'], $_POST['content'], 'yesow管理员')){
	  $email_list -> addinfo($value, $_POST['title'], $_POST['content']);
	  $success_num++;
	}else{
	  $email_list -> addinfo($value, $_POST['title'], $_POST['content'], 0);
	  $error_num++;
	}
      }
      //清除待发送列表
      session('admin_send_email_list', null);
      $this -> success('邮件发送完毕！成功：' . $success_num . ' 条。失败：' . $error_num . ' 条。可到邮件发送列表查看相信信息');
    }
    //读取收件人列表
    $recipientlist = M('Company') -> field('email') -> where(array('id' => array('in', $_SESSION['admin_send_email_list']))) -> select();
    $recipientstring = '';
    foreach($recipientlist as $value){
      $recipientstring .= $value['email'] . ';';
    }
    $this -> assign('recipientstring', $recipientstring);
    $this -> display();
  }

  //后台发送记录
  public function backgroundsendrecord(){
    $email_list = M('BackgroundSendEmail');
    $where = array();
    if(!empty($_POST['email'])){
      $where['bse.email'] = $this -> _post('email');
    }

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

  /* ------------ 邮件群发管理 ------------ */

}

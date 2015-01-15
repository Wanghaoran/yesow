<?php
class MessageAction extends CommonAction {

  //发送邮箱模板
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

  //添加发送邮箱模板
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

  //删除发送邮箱模板
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

  //编辑发送邮箱模板
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
    $result = $template -> field('name,content,title') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function memberemailsendrecord(){
    $MemberSendEmailRecord = M('MemberSendEmailRecord');
    $where = array();
    if(!empty($_POST['mname'])){
      $mid = M('Member') -> getFieldByname($_POST['mname'], 'id');
      $where['mssr.mid'] = $mid;
    }
    if(!empty($_POST['email'])){
      $where['mssr.sendemail'] = $this -> _post('email');
    }

    if(!empty($_POST['status']) || $_POST['status'] === '0'){
      $where['mssr.statuscode'] = $this -> _post('status');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['mssr.sendtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['mssr.sendtime'][] = array('lt', $endtime);
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

  
  //全部删除发送记录
  public function alldelmemberemailsendrecord(){
    $email_list = M('MemberSendEmailRecord');
    if($email_list -> where(1) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //区间删除发送记录
  public function intervaldelmemberemailsendrecord(){
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
      $email_list = M('MemberSendEmailRecord');
      if($email_list -> where($where) -> delete()){
	$this -> success(L('DATA_DELETE_SUCCESS'));
      }else{
	$this -> error(L('DATA_DELETE_ERROR'));
      }
    } 
    $this -> display();
  }

  //邮件补发
  public function editreplacemembersendemail(){
    $email_list = M('MemberSendEmailRecord');
    if(!empty($_POST['accept_email'])){
      $result_record = $email_list -> field('mid,title,content') -> find($this -> _post('id', 'intval'));
      //读取该用户的第一个启用的邮箱配置
      $MemberEmailSetting = M('MemberEmailSetting');
      $result_member = $MemberEmailSetting -> field('email_address,email_SMTP,email_account,email_pwd') -> where(array('mid' => $result_record['mid'], 'status' => 1)) -> order('id ASC') -> find();
      //发送邮件
      C('MAIL_ADDRESS', $result_member['email_address']);
      C('MAIL_SMTP', $result_member['email_SMTP']);
      C('MAIL_LOGINNAME', $result_member['email_account']);
      C('MAIL_PASSWORD', $result_member['email_pwd']);

      import('ORG.Util.Mail');

      if(@SendMail($_POST['accept_email'], $result_record['title'], $result_record['content'], 'yesow管理员')){
	$update_data = array();
	$update_data['id'] = $this -> _post('id', 'intval');
	$update_data['tosendemail'] = $result_member['email_address'];
	$update_data['sendemail'] = $_POST['accept_email'];
	$update_data['sendtime'] = time();	
	if($_POST['check'] == 1){
	  $update_data['statuscode'] = 2;
	}else{
	  $update_data['statuscode'] = 1;
	}

	$email_list -> save($update_data);

	$this -> success('邮件补发成功！');
      }else{
	$this -> error('邮件补发失败！');
      }
    
    }
    $result = $email_list -> field('sendemail') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  /*
   *public function editreplacetimingsendcompanyremindemail(){

    $TimingSendEmail = M('TimingSendEmail');
    $TimingSendEmailSetting = M('TimingSendEmailSetting');

    if(!empty($_POST['accept_email'])){
      //先更新速查数据
      $record_data = $TimingSendEmail -> field('cid,title,content') -> find($this -> _post('id', 'intval'));
      $data = array();
      $data['id'] = $record_data['cid'];
      $data['email'] = $_POST['accept_email'];
      if($_POST['update'] == 1){
	$data['updatetime'] = time();
      }
      M('Company') -> save($data);
      //发送邮件
      $email_template = $TimingSendEmailSetting -> field('id,email_address as send_address, email_SMTP as email_smtp, email_account as send_account, email_pwd as send_pwd') -> where(array('aid' => session(C('USER_AUTH_KEY')), 'status' => 1)) -> order('sendnum ASC') -> find();
      C('MAIL_ADDRESS', $email_template['send_address']);
      C('MAIL_SMTP', $email_template['email_smtp']);
      C('MAIL_LOGINNAME', $email_template['send_account']);
      C('MAIL_PASSWORD', $email_template['send_pwd']);
      import('ORG.Util.Mail');

      if(@SendMail($_POST['accept_email'], $record_data['title'], $record_data['content'], 'yesow管理员')){
	$update_data = array();
	$update_data['id'] = $this -> _post('id', 'intval');
	$update_data['send_email'] = $email_template['send_address'];
	$update_data['email'] = $_POST['accept_email'];
	$update_data['sendtime'] = time();	
	if($_POST['check'] == 1){
	  $update_data['status'] = 2;
	}else{
	  $update_data['status'] = 1;
	}

	$TimingSendEmail -> save($update_data);

	//更新提醒邮件记录
	$TimingSendEmailSetting -> where(array('id' => $email_template['id'])) -> setInc('sendnum');
	$this -> success('邮件补发成功！');
      }else{
	$this -> error('邮件补发失败！');
      }

    }
    $result = $TimingSendEmail -> alias('r') -> field('r.email as accept_email,c.name as company_name,c.updatetime') -> where(array('r.id' => $this -> _get('id', 'intval'))) -> join('yesow_company as c ON r.cid = c.id') -> find();
    $this -> assign('result', $result);
    $this -> display();

  }




   *
   *
   */

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

  public function editsmemberemailgroup(){
    $MemberEmailGroup = M('MemberEmailGroup');
    if(!empty($_POST['name'])){
      if(!$MemberEmailGroup -> create()){
	$this -> error($MemberEmailGroup -> getError());
      }
      $MemberEmailGroup -> mid = $_POST['org4_id'];
      if($MemberEmailGroup -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $MemberEmailGroup -> alias('g') -> field('g.id,g.mid,g.name,m.name as mname') -> join('yesow_member as m ON g.mid = m.id') -> where(array('g.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
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
      $where['t.name'] = array('like', '%' . $this -> _post('name') . '%');
    }

    $count = $sendtype -> alias('t') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $sendtype -> alias('t') -> field('t.id,t.name,t.apicode,t.remark,a.name as aname,a.account,t.aid') -> join('yesow_sms_api as a ON t.aid = a.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();

    $check = false;
    foreach($result as $key => $value){
      if($value['aid'] == 5 && !$check){
	$balance = file_get_contents($value['account']);
	preg_match_all('/[^a-z]([0-9]+)/', $balance, $balance_arr);
	$check = true;
	$result[0]['balance'] = $balance_arr[1][1];
	$result[1]['balance'] = $balance_arr[1][2];
	$result[2]['balance'] = $balance_arr[1][3];
      }
      if($value['aid'] != 5 && $value['aid'] != 7){
	$result[$key]['balance'] = file_get_contents($value['account']);
      }

        if($value['aid'] == 7){


            //河南奇葩接口

            $uri = "http://www.send10086.com/sms.aspx";
            // 参数数组
            $data = array (
                'userid' => '10849',
                'account' => 'yesow',
                'password' => 'yesow123',
                'action' => 'overage',
            );

            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $uri );
            curl_setopt ( $ch, CURLOPT_POST, 1 );
            curl_setopt ( $ch, CURLOPT_HEADER, 0 );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
            $return = curl_exec ( $ch );
            curl_close ( $ch );

            $xmlObj = simplexml_load_string($return, 'SimpleXMLElement', LIBXML_NOCDATA);
            $overage = strval($xmlObj -> overage);

            $result[$key]['balance'] = $overage;

        }
    
    }

    /*
    $setting = M('SmsSetting');
    $sms_username = $setting -> getFieldByname('sms_username', 'value');
    $sms_password = $setting -> getFieldByname('sms_password', 'value');
    $balance = file_get_contents('http://www.vip.86aaa.com/api.aspx?SendType=101&Code=utf-8&UserName=' . $sms_username . '&Pwd=' . $sms_password . '');
    preg_match_all('/[^a-z]([0-9]+)/', $balance, $balance_arr);
    foreach($result as $key => $value){
      $result[$key]['balance'] = $balance_arr[1][$value['apicode']];
    }
    $this -> assign('balance_arr', $balance_arr);
     */
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
    $SmsApi = M('SmsApi');
    $result_api = $SmsApi -> field('id,name') -> select();
    $this -> assign('result_api', $result_api);
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
    $result = $sendtype -> field('name,apicode,remark,aid') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $SmsApi = M('SmsApi');
    $result_api = $SmsApi -> field('id,name') -> select();
    $this -> assign('result_api', $result_api);
    $this -> display();
  }

  public function sendsmssetting(){
    $setting = M('SmsSetting');
    if(!empty($_POST['search_phone_price'])){
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

    $result = $MemberSendSmsRecord -> table('yesow_member_send_sms_record as mssr') -> field('mssr.id,m.name as mname,mssr.sendtime,mssr.content,mssr.sendphone,mssr.statuscode,mssr.sendtype,mssr.price') -> join('yesow_member as m ON mssr.mid = m.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sendtime DESC') -> select();
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
    $result = $MassEmailTemplate -> alias('t') -> field('t.id,t.addtime,e.type_zh,e.send_address,t.title') -> join('yesow_mass_email_setting as e ON t.eid = e.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('t.addtime DESC') -> select();
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
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['r.sendtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['r.sendtime'][] = array('lt', $endtime);
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

  public function intervaldelmasssendrecord(){
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
      $MassEmailRecord = M('MassEmailRecord');
      if($MassEmailRecord -> where($where) -> delete()){
	$this -> success(L('DATA_DELETE_SUCCESS'));
      }else{
	$this -> error(L('DATA_DELETE_ERROR'));
      }
    } 
    $this -> display();
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
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['send_time'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['send_time'][] = array('lt', $endtime);
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

    $result = $OrderAcceptRecord -> field('id,send_type,accept_email,title,content,send_time,status,mname') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('send_time DESC') -> select();
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

  public function intervaldelorderacceptemailrecord(){
    if(!empty($_POST['isdel'])){
      $where_del = array();
      if(!empty($_POST['starttime'])){
	$addtime = $this -> _post('starttime', 'strtotime');
	$where['send_time'] = array(array('gt', $addtime));
      }
      if(!empty($_POST['endtime'])){
	$endtime = $this -> _post('endtime', 'strtotime');
	$where['send_time'][] = array('lt', $endtime);
      }
      $OrderAcceptRecord = M('OrderAcceptRecord');
      if($OrderAcceptRecord -> where($where) -> delete()){
	$this -> success(L('DATA_DELETE_SUCCESS'));
      }else{
	$this -> error(L('DATA_DELETE_ERROR'));
      }
    } 
    $this -> display();
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

  public function endtimeemailrecord(){
    $EndtimeAlertEmailRecord = M('EndtimeAlertEmailRecord');

    $where = array();
    if(!empty($_POST['accept_email'])){
      $where['accept_email'] = $this -> _post('accept_email');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['send_time'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['send_time'][] = array('lt', $endtime);
    }

    $count = $EndtimeAlertEmailRecord -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $EndtimeAlertEmailRecord -> field('id,mname,send_email,send_type,accept_email,title,content,send_time,status') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('send_time DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delendtimeemailrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $EndtimeAlertEmailRecord = M('EndtimeAlertEmailRecord');
    if($EndtimeAlertEmailRecord -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function intervaldelendtimeemailrecord(){
    if(!empty($_POST['isdel'])){
      $where_del = array();
      if(!empty($_POST['starttime'])){
	$addtime = $this -> _post('starttime', 'strtotime');
	$where['send_time'] = array(array('gt', $addtime));
      }
      if(!empty($_POST['endtime'])){
	$endtime = $this -> _post('endtime', 'strtotime');
	$where['send_time'][] = array('lt', $endtime);
      }
      $EndtimeAlertEmailRecord = M('EndtimeAlertEmailRecord');
      if($EndtimeAlertEmailRecord -> where($where) -> delete()){
	$this -> success(L('DATA_DELETE_SUCCESS'));
      }else{
	$this -> error(L('DATA_DELETE_ERROR'));
      }
    } 
    $this -> display();
  }


  public function editendtimeemailrecord(){
    $content = M('EndtimeAlertEmailRecord') -> getFieldByid($this -> _get('id', 'intval'), 'content');
    $this -> assign('content', $content);
    $this -> display();
  }

  public function smsgateway(){
    $SmsApi = M('SmsApi');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('like', '%' . $this -> _post('name') . '%');
    }

    $count = $SmsApi -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $SmsApi -> field('id,name,enable,addtime,remark,account') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();


      foreach($result as $key => $value){
          if($value['id'] != 5 && $value['id'] != 7){
              $result[$key]['accounts'] = file_get_contents($value['account']);
          }else if($value['id'] == 7){

              //河南奇葩接口

              $uri = "http://www.send10086.com/sms.aspx";
              // 参数数组
              $data = array (
                  'userid' => '10849',
                  'account' => 'yesow',
                  'password' => 'yesow123',
                  'action' => 'overage',
              );

              $ch = curl_init ();
              curl_setopt ( $ch, CURLOPT_URL, $uri );
              curl_setopt ( $ch, CURLOPT_POST, 1 );
              curl_setopt ( $ch, CURLOPT_HEADER, 0 );
              curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
              curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
              $return = curl_exec ( $ch );
              curl_close ( $ch );

              $xmlObj = simplexml_load_string($return, 'SimpleXMLElement', LIBXML_NOCDATA);
              $overage = strval($xmlObj -> overage);

              $result[$key]['accounts'] = $overage;

          }else{
              $balance = file_get_contents($value['account']);
              preg_match_all('/[^a-z]([0-9]+)/', $balance, $balance_arr);
              foreach($balance_arr[1] as $key2 => $value2){
                  if($key2 != 0){
                      if(empty($result[$key]['accounts'])){
                          $result[$key]['accounts'] .= $value2;
                      }else{
                          $result[$key]['accounts'] .= ' / ' . $value2;
                      }

                  }

              }
          }

      }

    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addsmsgateway(){
    if(!empty($_POST['name'])){
      $SmsApi = D('SmsApi');
      if(!$SmsApi -> create()){
	$this -> error($SmsApi -> getError());
      }
      if($SmsApi -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delsmsgateway(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $SmsApi = M('SmsApi');
    if($SmsApi -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editsmsgateway(){
    $SmsApi = M('SmsApi');
    if(!empty($_POST['name'])){
      if(!$SmsApi -> create()){
	$this -> error($SmsApi -> getError());
      }
      if($SmsApi -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $SmsApi -> field('name,url,remark,account') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function editsmsgatewayenable(){
    $SmsApi = M('SmsApi');
    $SmsApi -> execute("update yesow_sms_api set enable=0");
    if($SmsApi -> where(array('id' => $this -> _get('id', 'intval'))) -> setField('enable', 1)){
      $this -> success(L('接口启用成功'));
    }else{
      $this -> error(L('接口启用失败'));
    }
  }

  public function editsmsgatewayparameters(){
    $SmsApiParameters = M('SmsApiParameters');
    $where = array();
    $where['aid'] = $this -> _request('aid', 'intval');
    if(!empty($_POST['name'])){
      $where['key'] = array('like', '%' . $this -> _post('name') . '%');
    }

    $count = $SmsApiParameters -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $SmsApiParameters -> field('id,key,value,remark,callback') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();

    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addeditsmsgatewayparameters(){
    if(!empty($_POST['key'])){
      $SmsApiParameters = D('SmsApiParameters');
      if(!$SmsApiParameters -> create()){
	$this -> error($SmsApiParameters -> getError());
      }
      if($SmsApiParameters -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function deleditsmsgatewayparameters(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $SmsApiParameters = M('SmsApiParameters');
    if($SmsApiParameters -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editeditsmsgatewayparameters(){
    $SmsApiParameters = M('SmsApiParameters');
    if(!empty($_POST['key'])){
      if(!$SmsApiParameters -> create()){
	$this -> error($SmsApiParameters -> getError());
      }
      if($SmsApiParameters -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $SmsApiParameters -> field('key,value,remark,callback') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function editsmsgatewaycallback(){
    $SmsApiCallback = M('SmsApiCallback');
    $where = array();
    $where['aid'] = $this -> _request('aid', 'intval');
    if(!empty($_POST['name'])){
      $where['key'] = array('like', '%' . $this -> _post('name') . '%');
    }

    $count = $SmsApiCallback -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $SmsApiCallback -> field('id,key,value,status') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();

    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addeditsmsgatewaycallback(){
    if(!empty($_POST['key'])){
      $SmsApiCallback = D('SmsApiCallback');
      if(!$SmsApiCallback -> create()){
	$this -> error($SmsApiCallback -> getError());
      }
      if($SmsApiCallback -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function editeditsmsgatewaycallback(){
    $SmsApiCallback = M('SmsApiCallback');
    if(!empty($_POST['key'])){
      if(!$SmsApiCallback -> create()){
	$this -> error($SmsApiCallback -> getError());
      }
      if($SmsApiCallback -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $SmsApiCallback -> field('key,value,status') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function deleditsmsgatewaycallback(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $SmsApiCallback = M('SmsApiCallback');
    if($SmsApiCallback -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //切换邮箱管理
  public function companyremindemail(){
    $CompanyRemindEmail = M('CompanyRemindEmail');
    $where = array();
    if(!empty($_POST['send_address'])){
      $where['send_address'] = $_POST['send_address'];
    }
    $count = $CompanyRemindEmail -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $CompanyRemindEmail -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('type ASC,sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  //添加切换邮箱
  public function addcompanyremindemail(){
    if(!empty($_POST['send_address'])){
      $CompanyRemindEmail = M('CompanyRemindEmail');
      if(!$CompanyRemindEmail -> create()){
	$this -> error($CompanyRemindEmail -> getError());
      }
      if($CompanyRemindEmail -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除切换邮箱
  public function delcompanyremindemail(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $CompanyRemindEmail = M('CompanyRemindEmail');
    if($CompanyRemindEmail -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑切换邮箱
  public function editcompanyremindemail(){
    $CompanyRemindEmail = M('CompanyRemindEmail');

    if(!empty($_POST['send_address'])){
      if(!$CompanyRemindEmail -> create()){
	$this -> error($CompanyRemindEmail -> getError());
      }
      if($CompanyRemindEmail -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $CompanyRemindEmail -> field('type,send_address,send_smtp,send_email,email_pwd,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);

    $this -> display();
  }

  //切换邮箱状态
  public function editcompanyremindemailstatus(){
    $CompanyRemindEmail = M('CompanyRemindEmail');
    $data = array();
    $data['id'] = $this -> _get('id', 'intval');
    $data['isallow'] = $this -> _get('status');
    if($CompanyRemindEmail -> save($data)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  
  }

  public function companyremindtime(){
    $CompanyRemindTime = M('CompanyRemindTime');

    $where = array();
    if(!empty($_POST['time'])){
      $where['time'] = $this -> _post('time');
    }

    $count = $CompanyRemindTime -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $CompanyRemindTime -> field('id,time,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('time ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addcompanyremindtime(){
    if(!empty($_POST['time']) || $_POST['time'] == '0'){
      $CompanyRemindTime = M('CompanyRemindTime');
      if(!$CompanyRemindTime -> create()){
	$this -> error($CompanyRemindTime -> getError());
      }
      if($CompanyRemindTime -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delcompanyremindtime(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $CompanyRemindTime = M('CompanyRemindTime');
    if($CompanyRemindTime -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editcompanyremindtime(){
    $CompanyRemindTime = M('CompanyRemindTime');
    if(!empty($_POST['time']) || $_POST['time'] == '0'){
      if(!$CompanyRemindTime -> create()){
	$this -> error($CompanyRemindTime -> getError());
      }
      if($CompanyRemindTime -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $CompanyRemindTime -> field('time,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function companyremindemailrecord(){
    $CompanyRemindEmailRecord = M('CompanyRemindEmailRecord');

    $where = array();
    if(!empty($_POST['accept_email'])){
      $where['r.accept_email'] = $this -> _post('accept_email');
    }
    if(!empty($_POST['mname'])){
      $where['c.name'] = array('LIKE', '%' . $this -> _post('mname') . '%');
    }
    if(!empty($_POST['status']) || $_POST['status'] === '0'){
      $where['r.status'] = $this -> _post('status');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['r.send_time'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['r.send_time'][] = array('lt', $endtime);
    }

    $count = $CompanyRemindEmailRecord -> alias('r') -> join('yesow_company as c ON r.cid = c.id') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $CompanyRemindEmailRecord -> alias('r') -> field('r.cid,r.id,r.accept_email,r.title,r.send_email,r.send_time,r.status,c.name as cname') -> join('yesow_company as c ON r.cid = c.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('r.send_time DESC') -> select();

    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delcompanyremindemailrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $CompanyRemindEmailRecord = M('CompanyRemindEmailRecord');
    if($CompanyRemindEmailRecord -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function intervaldelcompanyremindemailrecord(){
    if(!empty($_POST['isdel'])){
      $where_del = array();
      if(!empty($_POST['starttime'])){
	$addtime = $this -> _post('starttime', 'strtotime');
	$where['send_time'] = array(array('gt', $addtime));
      }
      if(!empty($_POST['endtime'])){
	$endtime = $this -> _post('endtime', 'strtotime');
	$where['send_time'][] = array('lt', $endtime);
      }
      $CompanyRemindEmailRecord = M('CompanyRemindEmailRecord');
      if($CompanyRemindEmailRecord -> where($where) -> delete()){
	$this -> success(L('DATA_DELETE_SUCCESS'));
      }else{
	$this -> error(L('DATA_DELETE_ERROR'));
      }
    } 
    $this -> display();
  }

  public function editcompanyremindemailrecord(){
    $content = M('CompanyRemindEmailRecord') -> getFieldByid($this -> _get('id', 'intval'), 'content');
    $this -> assign('content', $content);
    $this -> display();
  }

  //补发-速查提醒发送记录
  public function editreplacesendcompanyremindemail(){
    $CompanyRemindEmailRecord = M('CompanyRemindEmailRecord');
    $CompanyRemindEmail = M('CompanyRemindEmail');
    if(!empty($_POST['accept_email'])){
      //先更新速查数据
      $record_data = $CompanyRemindEmailRecord -> field('cid,title,content') -> find($this -> _post('id', 'intval'));
      $data = array();
      $data['id'] = $record_data['cid'];
      $data['email'] = $_POST['accept_email'];
      if($_POST['update'] == 1){
	$data['updatetime'] = time();
      }
      M('Company') -> save($data);
      //发送邮件
      $email_template = $CompanyRemindEmail -> field('id,send_address as send_address, send_smtp as email_smtp, send_email as send_account, email_pwd as send_pwd') -> where('status=1 AND type=1') -> find();
      C('MAIL_ADDRESS', $email_template['send_address']);
      C('MAIL_SMTP', $email_template['email_smtp']);
      C('MAIL_LOGINNAME', $email_template['send_account']);
      C('MAIL_PASSWORD', $email_template['send_pwd']);
      import('ORG.Util.Mail');

      if(@SendMail($_POST['accept_email'], $record_data['title'], $record_data['content'], 'yesow管理员')){
	$update_data = array();
	$update_data['id'] = $this -> _post('id', 'intval');
	$update_data['send_email'] = $email_template['send_address'];
	$update_data['accept_email'] = $_POST['accept_email'];
	$update_data['send_time'] = time();	
	if($_POST['check'] == 1){
	  $update_data['status'] = 2;
	}else{
	  $update_data['status'] = 1;
	}

	$CompanyRemindEmailRecord -> save($update_data);
	/*
	//更新速查数据
	$Company -> where(array('id' => $record_data['cid'])) -> setInc('remind_count');
	$r_data = array();
	$r_data['cid'] = $record_data['cid'];
	$r_data['send_time'] = time();
	$r_data['send_email'] = $email_template['send_address'];
	$r_data['time'] = ''; //这里没法计算
	$r_data['email'] = $_POST['accept_email'];
	M('CompanyRemindRecord') -> add($r_data);
	 */

	//更新提醒邮件记录
	$CompanyRemindEmail -> where(array('id' => $email_template['id'])) -> setInc('sum');
	$this -> success('邮件补发成功！');
      }else{
	$this -> error('邮件补发失败！');
      }

    }
    $result = $CompanyRemindEmailRecord -> alias('r') -> field('r.accept_email,c.name as company_name,c.updatetime') -> where(array('r.id' => $this -> _get('id', 'intval'))) -> join('yesow_company as c ON r.cid = c.id') -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  //编辑速查资料
  public function editcompanyinfo(){
    $company = D('Company');
    if(!empty($_POST['name'])){
      if(!$company -> create()){
	$this -> error($company -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	if($pics = $this -> upload()){
	  $company -> pic = $pics;
	}else{
	  $this -> error(L('DATA_UPLOAD_ERROR'));
	}
      }
      $company -> updateaid = session('admin_name');
      $company -> updatetime = time();
      if($company -> save()){
	//sendEmail
	if(!empty($_POST['email'])){
	  D('MassEmailSetting') -> sendEmail('company_change', $_POST['email'], $_POST['id']);
	}
	//更新订单
	M('CompanyRemindEmailRecord') -> where(array('id' => $_POST['oid'])) -> save(array('status' => 2));
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $id = !empty($id) ? $id : $this -> _get('id', 'intval');
    $this -> assign('id', $id);
    $result_edit = $company -> table('yesow_company as c') -> field('c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,c.csid,c.csaid,c.typeid,c.ccid,c.website,c.pic,c.keyword,c.content,c.clickcount,c.addtime,c.updatetime,c.auditaid as auditname,c.updateaid as updatename,c.delaid as delname,c.remind_count as remind_count') -> where(array('c.id' => $id)) -> find();
    $this -> assign('result_edit', $result_edit);
    $result_childsite_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result_edit['csid'])) -> select();
    $this -> assign('result_childsite_area', $result_childsite_area);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_company_type = M('CompanyType') -> field('id,name') -> select();
    $this -> assign('result_company_type', $result_company_type);
    $result_ccid_one = M('CompanyCategory') -> getFieldByid($result_edit['ccid'], 'pid');
    $this -> assign('result_ccid_one', $result_ccid_one);
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result_ccid_one)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    $this -> display();
  }


  public function memberremindtime(){
    $MemberRemindTime = M('MemberRemindTime');

    $where = array();
    if(!empty($_POST['time'])){
      $where['time'] = $this -> _post('time');
    }

    $count = $MemberRemindTime -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberRemindTime -> field('id,time,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('time ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addmemberremindtime(){
    if(!empty($_POST['time']) || $_POST['time'] == '0'){
      $MemberRemindTime = M('MemberRemindTime');
      if(!$MemberRemindTime -> create()){
	$this -> error($MemberRemindTime -> getError());
      }
      if($MemberRemindTime -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delmemberremindtime(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberRemindTime = M('MemberRemindTime');
    if($MemberRemindTime -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editmemberremindtime(){
    $MemberRemindTime = M('MemberRemindTime');
    if(!empty($_POST['time']) || $_POST['time'] == '0'){
      if(!$MemberRemindTime -> create()){
	$this -> error($MemberRemindTime -> getError());
      }
      if($MemberRemindTime -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $MemberRemindTime -> field('time,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function memberremindemailrecord(){
    $MemberRemindEmailRecord = M('MemberRemindEmailRecord');

    $where = array();
    if(!empty($_POST['accept_email'])){
      $where['r.accept_email'] = $this -> _post('accept_email');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['r.send_time'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['r.send_time'][] = array('lt', $endtime);
    }
    if(!empty($_POST['status']) || $_POST['status'] === '0'){
      $where['r.status'] = $this -> _post('status');
    }
    if(!empty($_POST['mname'])){
      $where['m.name'] = array('LIKE', '%' . $this -> _post('mname') . '%');
    }

    $count = $MemberRemindEmailRecord -> alias('r') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberRemindEmailRecord -> alias('r') -> field('r.id,r.send_email,r.accept_email,r.title,r.send_time,r.status,m.name as mname,m.fullname') -> join('yesow_member as m ON r.mid = m.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('r.send_time DESC') -> select();

    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delmemberremindemailrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberRemindEmailRecord = M('MemberRemindEmailRecord');
    if($MemberRemindEmailRecord -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function intervaldelmemberremindemailrecord(){
    if(!empty($_POST['isdel'])){
      $where_del = array();
      if(!empty($_POST['starttime'])){
	$addtime = $this -> _post('starttime', 'strtotime');
	$where['send_time'] = array(array('gt', $addtime));
      }
      if(!empty($_POST['endtime'])){
	$endtime = $this -> _post('endtime', 'strtotime');
	$where['send_time'][] = array('lt', $endtime);
      }
      $MemberRemindEmailRecord = M('MemberRemindEmailRecord');
      if($MemberRemindEmailRecord -> where($where) -> delete()){
	$this -> success(L('DATA_DELETE_SUCCESS'));
      }else{
	$this -> error(L('DATA_DELETE_ERROR'));
      }
    } 
    $this -> display();
  }

  public function editmemberremindemailrecord(){
    $content = M('MemberRemindEmailRecord') -> getFieldByid($this -> _get('id', 'intval'), 'content');
    $this -> assign('content', $content);
    $this -> display();
  }

  /*  ----------    定时邮件群发  --------------    */


  //定时邮件搜索
  public function timingsearchemail(){
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

  //添加到定时发送通讯录
  public function addtimingsendgroup($send_arrs){
    set_time_limit(0);
    $send_arr = array();
    $company = M('Company');
    if(empty($send_arrs)){
      if(!empty($_REQUEST['issearch'])){
	$where = array();
	$where['email'] = array('neq', '');
	if($_REQUEST['s_csid'] != 'null'){
	  $where['csid'] = $this -> _request('s_csid', 'intval');
	}
	if($_REQUEST['s_csaid'] != 'null'){
	  $where['csaid'] = $this -> _request('s_csaid', 'intval');
	}
	if($_REQUEST['s_keyword'] != 'null'){
	  $_REQUEST['s_keyword'] = safeEncoding($_REQUEST['s_keyword']);
	  $where['_string'] = "( name LIKE '%{$_REQUEST['s_keyword']}%' ) OR ( address LIKE '%{$_REQUEST['s_keyword']}%' ) OR ( manproducts LIKE '%{$_REQUEST['s_keyword']}%' ) OR ( mobilephone LIKE '%{$_REQUEST['s_keyword']}%' ) OR ( email LIKE '%{$_REQUEST['s_keyword']}%' ) OR ( linkman LIKE '%{$_REQUEST['s_keyword']}%' ) OR ( companyphone LIKE '%{$_REQUEST['s_keyword']}%' ) OR ( qqcode LIKE '%{$_REQUEST['s_keyword']}%' ) OR ( website LIKE '%{$_REQUEST['s_keyword']}%' )";
	}
	$result_temp = $company -> field('id,email') -> where($where) -> group('email') -> select();

	foreach($result_temp as $value){
	  $send_arr[$value['id']] = $value['email'];
	  /*
	  $value['email'] = preg_replace('/\s{2,}|　/U',' ',$value['email']);
	  $send_arr[$value['id']] = $values;
	  $temp_arr = explode(' ', $value['email']);
	  foreach($temp_arr as $values){
	    if(!empty($values)){
	      $send_arr[$value['id']] = $values;
	    }	    
	  }
	   */
	}
      }else{
	$result_temp = explode(',', $_POST['ids']);
	foreach($result_temp as $key => $value){
	  $temp_string = $company -> getFieldByid($value, 'email');
	  $send_arr[$value] = $temp_string;
	  /*
	  $temp_string = preg_replace('/\s{2,}|　/U',' ',$temp_string);
	  $temp_arr = explode(' ', $temp_string);
	  foreach($temp_arr as $values){
	    if(!empty($values)){
	      $send_arr[$value] = $values;
	    }	    
	  }
	   */
	}
      }
    }else{
      $send_arr = $send_arrs;
    }

    if($_REQUEST['issearch'] == 3){
      $TimingSendGroupList = M('TimingSendGroupList');
      $TimingSendGroup = M('TimingSendGroup');
      //添加通讯录
      $group_add = array();
      $group_add['name'] = $_POST['name'];
      $group_add['tid'] = $_POST['tid'];
      $group_add['aid'] = session(C('USER_AUTH_KEY'));
      $group_add['addtime'] = time();
      $group_add['sendtime'] = strtotime($_POST['send_date_1']  . ' ' . $_POST['send_date_2'] . ':' . $_POST['send_date_3'] . ':' . '00');
      if($gid = $TimingSendGroup -> add($group_add)){
	//添加通讯路详情
	foreach($send_arr as $key => $value){
	  $data_add = array();
	  $data_add['gid'] = $gid;
	  $data_add['cid'] = $key;
	  $data_add['email'] = $value;
	  $TimingSendGroupList -> add($data_add);  
	}
	$this -> success('通讯录添加成功！'); 
      }else{
	$this -> error('添加通讯录失败');
      }
    }
    $this -> assign('num', count($send_arr));

    //发送模板
    $BackgroundEmailTemplate = D('BackgroundEmailTemplate');
    $result_template = $BackgroundEmailTemplate -> field('id,name') -> select();
    $this -> assign('result_template', $result_template);

    $this -> display();
  }

  //定时发送邮箱
  public function timingsendemail(){
    $TimingSendEmailSetting = M('TimingSendEmailSetting');
    $where = array();
    if(!empty($_POST['mname'])){
      $where['m.name'] = $this -> _post('mname');
    }
    if($_SESSION[C('USER_AUTH_KEY')] != 1){
      $where['s.aid'] = session(C('USER_AUTH_KEY'));
    }
    $count = $TimingSendEmailSetting -> alias('s') -> join('yesow_admin as m ON s.aid = m.id') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $TimingSendEmailSetting -> alias('s') -> field('s.id,s.email_address,s.email_SMTP,s.email_account,s.min_limit,s.sendnum,m.name as mname,s.addtime,s.status') -> join('yesow_admin as m ON s.aid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('s.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加定时发送邮箱
  public function addtimingsendemail(){
    if(!empty($_POST['email_address'])){
      $TimingSendEmailSetting = M('TimingSendEmailSetting');
      if(!$TimingSendEmailSetting -> create()){
	$this -> error($TimingSendEmailSetting -> getError());
      }
      $TimingSendEmailSetting -> aid = session(C('USER_AUTH_KEY'));
      $TimingSendEmailSetting -> addtime = time();
      if($TimingSendEmailSetting -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除定时发送邮箱
  public function deltimingsendemail(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $TimingSendEmailSetting = M('TimingSendEmailSetting');
    if($TimingSendEmailSetting -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑定时发送邮箱
  public function edittimingsendemail(){
    $TimingSendEmailSetting = M('TimingSendEmailSetting');

    if(!empty($_POST['email_address'])){
      if(!$TimingSendEmailSetting -> create()){
	$this -> error($TimingSendEmailSetting -> getError());
      }
      if($TimingSendEmailSetting -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $TimingSendEmailSetting -> field('email_address,email_SMTP,email_account,email_pwd,min_limit') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //编辑定时发送邮箱状态
  public function edittimingsendemailstatus(){
    $TimingSendEmailSetting = M('TimingSendEmailSetting');
    $data = array();
    $data['id'] = $this -> _get('id', 'intval');
    $data['status'] = $this -> _get('status');
    if($TimingSendEmailSetting -> save($data)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  
  }

  //定时发送记录
  public function timingsendrecord(){
    $email_list = M('TimingSendEmail');
    $where = array();
    if(!empty($_POST['email'])){
      $where['bse.email'] = $this -> _post('email');
    }
    if(!empty($_POST['mname'])){
      $where['c.name'] = array('LIKE', '%' . $this -> _post('mname') . '%');
    }
    if(!empty($_POST['status']) || $_POST['status'] === '0'){
      $where['bse.status'] = $this -> _post('status');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['bse.sendtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['bse.sendtime'][] = array('lt', $endtime);
    }

    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $dayBegin = mktime(0,0,0,$month,$day,$year);
    $dayEnd = mktime(23,59,59,$month,$day,$year);

    $today_count = $email_list -> where(array('sendtime' => array(array('egt', $dayBegin),array('elt', $dayEnd)))) -> count('id');
    $this -> assign('today_count', $today_count);

    $count = $email_list -> alias('bse') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $email_list -> alias('bse') -> field('bse.cid,bse.id,bse.email,bse.title,bse.sendtime,bse.status,bse.send_email,c.name as cname') -> where($where) -> order('sendtime DESC') -> join('yesow_company as c ON bse.cid = c.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  
  }

  //删除定时发送记录
  public function deltimingsendrecord(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $email_list = M('TimingSendEmail');
    if($email_list -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //全部删除定时发送记录
  public function alldeltimingsendrecord(){
    $email_list = M('TimingSendEmail');
    if($email_list -> where(1) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //区间删除定时发送记录
  public function intervaldeltimingsendrecord(){
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
      $email_list = M('TimingSendEmail');
      if($email_list -> where($where) -> delete()){
	$this -> success(L('DATA_DELETE_SUCCESS'));
      }else{
	$this -> error(L('DATA_DELETE_ERROR'));
      }
    } 
    $this -> display();
  }

  //查看发送正文
  public function edittimingsendrecordinfo(){
    $content = M('TimingSendEmail') -> getFieldByid($this -> _get('id', 'intval'), 'content');
    $this -> assign('content', $content);
    $this -> display();
  }

  //编辑速查信息
  public function edittimingsendrecordcompanyinfo(){
    $company = D('Company');
    if(!empty($_POST['name'])){
      if(!$company -> create()){
	$this -> error($company -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	if($pics = $this -> upload()){
	  $company -> pic = $pics;
	}else{
	  $this -> error(L('DATA_UPLOAD_ERROR'));
	}
      }
      $company -> updateaid = session('admin_name');
      $company -> updatetime = time();
      if($company -> save()){
	//sendEmail
	if(!empty($_POST['email'])){
	  D('MassEmailSetting') -> sendEmail('company_change', $_POST['email'], $_POST['id']);
	}
	//更新订单
	M('TimingSendEmail') -> where(array('id' => $_POST['oid'])) -> save(array('status' => 2));
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $id = !empty($id) ? $id : $this -> _get('id', 'intval');
    $this -> assign('id', $id);
    $result_edit = $company -> table('yesow_company as c') -> field('c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,c.csid,c.csaid,c.typeid,c.ccid,c.website,c.pic,c.keyword,c.content,c.clickcount,c.addtime,c.updatetime,c.auditaid as auditname,c.updateaid as updatename,c.delaid as delname,c.remind_count as remind_count') -> where(array('c.id' => $id)) -> find();
    $this -> assign('result_edit', $result_edit);
    $result_childsite_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result_edit['csid'])) -> select();
    $this -> assign('result_childsite_area', $result_childsite_area);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_company_type = M('CompanyType') -> field('id,name') -> select();
    $this -> assign('result_company_type', $result_company_type);
    $result_ccid_one = M('CompanyCategory') -> getFieldByid($result_edit['ccid'], 'pid');
    $this -> assign('result_ccid_one', $result_ccid_one);
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result_ccid_one)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    $this -> display();
  }

  //定时邮件失败处理
  public function editreplacetimingsendcompanyremindemail(){

    $TimingSendEmail = M('TimingSendEmail');
    $TimingSendEmailSetting = M('TimingSendEmailSetting');

    if(!empty($_POST['accept_email'])){
      //先更新速查数据
      $record_data = $TimingSendEmail -> field('cid,title,content') -> find($this -> _post('id', 'intval'));
      $data = array();
      $data['id'] = $record_data['cid'];
      $data['email'] = $_POST['accept_email'];
      if($_POST['update'] == 1){
	$data['updatetime'] = time();
      }
      M('Company') -> save($data);
      //发送邮件
      $email_template = $TimingSendEmailSetting -> field('id,email_address as send_address, email_SMTP as email_smtp, email_account as send_account, email_pwd as send_pwd') -> where(array('aid' => session(C('USER_AUTH_KEY')), 'status' => 1)) -> order('sendnum ASC') -> find();
      C('MAIL_ADDRESS', $email_template['send_address']);
      C('MAIL_SMTP', $email_template['email_smtp']);
      C('MAIL_LOGINNAME', $email_template['send_account']);
      C('MAIL_PASSWORD', $email_template['send_pwd']);
      import('ORG.Util.Mail');

      if(@SendMail($_POST['accept_email'], $record_data['title'], $record_data['content'], 'yesow管理员')){
	$update_data = array();
	$update_data['id'] = $this -> _post('id', 'intval');
	$update_data['send_email'] = $email_template['send_address'];
	$update_data['email'] = $_POST['accept_email'];
	$update_data['sendtime'] = time();	
	if($_POST['check'] == 1){
	  $update_data['status'] = 2;
	}else{
	  $update_data['status'] = 1;
	}

	$TimingSendEmail -> save($update_data);
	/*
	//更新速查数据
	$Company -> where(array('id' => $record_data['cid'])) -> setInc('remind_count');
	$r_data = array();
	$r_data['cid'] = $record_data['cid'];
	$r_data['send_time'] = time();
	$r_data['send_email'] = $email_template['send_address'];
	$r_data['time'] = ''; //这里没法计算
	$r_data['email'] = $_POST['accept_email'];
	M('CompanyRemindRecord') -> add($r_data);
	 */

	//更新提醒邮件记录
	$TimingSendEmailSetting -> where(array('id' => $email_template['id'])) -> setInc('sendnum');
	$this -> success('邮件补发成功！');
      }else{
	$this -> error('邮件补发失败！');
      }

    }
    $result = $TimingSendEmail -> alias('r') -> field('r.email as accept_email,c.name as company_name,c.updatetime') -> where(array('r.id' => $this -> _get('id', 'intval'))) -> join('yesow_company as c ON r.cid = c.id') -> find();
    $this -> assign('result', $result);
    $this -> display();

  }

  //定时发送通讯录
  public function timingsendemailgroup(){
    $TimingSendGroup = M('TimingSendGroup');
    $where = array();
    $where['g.aid'] = session(C('USER_AUTH_KEY'));
    if(!empty($_POST['name'])){
      $where['g.name'] = array('like', '%' . $this -> _post('name') . '%');
    }

    $count = $TimingSendGroup -> alias('g') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $TimingSendGroup -> alias('g') -> field('g.id,g.name,g.addtime,tmp.count,tt.name as tname,g.sendtime') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> join('LEFT JOIN (SELECT gid,COUNT(id) as count FROM yesow_timing_send_group_list GROUP BY gid) as tmp ON tmp.gid = g.id') -> join('yesow_background_email_template as tt ON g.tid = tt.id') -> order('g.sendtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('now', time());
    $this -> display();
  }

  //删除定时发送通讯录
  public function deltimingsendemailgroup(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $TimingSendGroup = M('TimingSendGroup');
    if($TimingSendGroup -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑定时发送通讯录
  public function edittimingsendemailgroup(){
    $TimingSendGroup = M('TimingSendGroup');

    if(!empty($_POST['name'])){
      if(!$TimingSendGroup -> create()){
	$this -> error($TimingSendGroup -> getError());
      }
      if($TimingSendGroup -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $TimingSendGroup -> field('name,tid') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //发送模板
    $BackgroundEmailTemplate = D('BackgroundEmailTemplate');
    $result_template = $BackgroundEmailTemplate -> field('id,name') -> select();
    $this -> assign('result_template', $result_template);
    $this -> display();
  }

  //查看定时发送通讯录详情
  public function edittimingemailgrouplist(){
    $TimingSendGroupList = M('TimingSendGroupList');
    $where = array();
    $where['l.gid'] = $this -> _request('gid', 'intval');
    if(!empty($_POST['email'])){
      $where['l.email'] = array('like', '%' . $this -> _post('email') . '%');
    }

    $count = $TimingSendGroupList -> alias('l') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $TimingSendGroupList -> alias('l') -> field('l.id,l.email,c.name as cname,l.status') -> join('yesow_company as c ON l.cid = c.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  //添加定时发送通讯录详情
  public function addedittimingemailgrouplist(){
    if(!empty($_POST['email'])){
      $TimingSendGroupList = D('TimingSendGroupList');
      if(!$TimingSendGroupList -> create()){
	$this -> error($TimingSendGroupList -> getError());
      }
      $TimingSendGroupList -> cid = $_POST['org2_id'];
      if($TimingSendGroupList -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除定时发送通讯录详情
  public function deledittimingemailgrouplist(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $TimingSendGroupList = M('TimingSendGroupList');
    if($TimingSendGroupList -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑定时发送通讯录详情
  public function editedittimingemailgrouplist(){
    $TimingSendGroupList = M('TimingSendGroupList');
    if(!empty($_POST['email'])){
      if(!$TimingSendGroupList -> create()){
	$this -> error($TimingSendGroupList -> getError());
      }
      $TimingSendGroupList -> cid = $_POST['org2_id'];
      if($TimingSendGroupList -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $TimingSendGroupList -> alias('l') -> field('l.email,c.name as cname,l.cid') -> join('yesow_company as c ON l.cid = c.id') -> where(array('l.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  //用户模板管理
  public function membersendemailtemplate(){
    $MemberEmailTemplate = M('MemberEmailTemplate');
    $where = array();
    if(!empty($_POST['name'])){
      $where['t.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    if(!empty($_POST['mname'])){
      $where['m.name'] = $this -> _post('mname');
    }

    $count = $MemberEmailTemplate -> alias('t') -> where($where) -> join('yesow_member as m ON t.mid = m.id') -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $MemberEmailTemplate -> alias('t') -> field('t.id,t.name,t.addtime,m.name as mname') -> join('yesow_member as m ON t.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('t.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //编辑用户模板
  public function editmembersendemailtemplate(){
    $MemberEmailTemplate = M('MemberEmailTemplate');
    if(!empty($_POST['name'])){
      if(!$MemberEmailTemplate -> create()){
	$this -> error($MemberEmailTemplate -> getError());
      }
      if($MemberEmailTemplate -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $MemberEmailTemplate -> field('id,name,title,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //删除用户模板
  public function delmembersendemailtemplate(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $MemberEmailTemplate = M('MemberEmailTemplate');
    if($MemberEmailTemplate -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }


  /*


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

  public function editsmemberemailgroup(){
    $MemberEmailGroup = M('MemberEmailGroup');
    if(!empty($_POST['name'])){
      if(!$MemberEmailGroup -> create()){
	$this -> error($MemberEmailGroup -> getError());
      }
      $MemberEmailGroup -> mid = $_POST['org4_id'];
      if($MemberEmailGroup -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $MemberEmailGroup -> alias('g') -> field('g.id,g.mid,g.name,m.name as mname') -> join('yesow_member as m ON g.mid = m.id') -> where(array('g.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
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



   */
 
}

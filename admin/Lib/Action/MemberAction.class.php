<?php
class MemberAction extends CommonAction {

  public function member(){
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $member = M('Member');
    $where = array();
    if(!empty($_POST['name'])){
      $where['m.' . $this -> _post('key')] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_POST['csid'])){
      $where['m.csid'] = $this -> _post('csid', 'intval');
    }
    if(!empty($_POST['csaid'])){
      $where['m.csaid'] = $this -> _post('csaid', 'intval');
    }
    $count = $member -> table('yesow_member as m') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $member -> table('yesow_member as m') -> field('m.id,m.name,m.nickname,m.sex,m.email,cs.name as csname,csa.name as csaname,m.join_time,m.last_login_time,m.status,m.ischeck,m.isservice,ttt.name as tname,ttt.count as tcount') -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> join('LEFT JOIN (SELECT * FROM (select mr.mid,ml.name,mr.rmb_pay+mr.rmb_exchange as count from yesow_member_rmb as mr LEFT JOIN yesow_member_level as ml ON mr.rmb_pay+mr.rmb_exchange >= ml.updatemoney ORDER BY mr.mid,ml.updatemoney DESC) as tmp GROUP BY mid) as ttt ON m.id = ttt.mid') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('m.id DESC') -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editmember(){
    $member = D('Member');
    if(!empty($_POST['name'])){
      if(empty($_POST['password'])){
	unset($_POST['password']);
      }else{
	$_POST['password'] = md5($_POST['password']);
      }
      if(!$member -> create()){
	$this -> error($member -> getError());
      }
      if($member -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $member -> field('csid,csaid,eduid,careerid,incomeid,name,nickname,passwordquestion,passwordanswer,status,ischeck,fullname,idnumber,sex,tel,qqcode,msn,email,address,zipcode,unit,homepage,headico') -> find($this -> _get('id','intval'));
    $this -> assign('result', $result);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    $result_memberedu = M('MemberEdu') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_memberedu', $result_memberedu);
    $result_membercareer = M('MemberCareer') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_membercareer', $result_membercareer);
    $result_memberincome = M('MemberIncome') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_memberincome', $result_memberincome);
    $this -> display();
  }

  public function delmember(){
    $member = M('Member');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($member -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editmemberrmb(){
    if(!empty($_POST['mid'])){
      $member_rmb_detail = D('MemberRmbDetail');
      if(!$member_rmb_detail -> create()){
	$this -> error($member_rmb_detail -> getError());
      }
      if($member_rmb_detail -> add()){
	$mid = $this -> _post('mid');
	$money = $this -> _post('money');
	$member_rmb = D('member://MemberRmb');
	if($money > 0){
	  if($member_rmb -> addmoney('rmb_exchange', $money, $mid)){
	    $this -> success(L('DATA_UPDATE_SUCCESS'));
	  }else{
	    $this -> error(L('DATA_UPDATE_ERROR'));
	  }
	}else if($money < 0){
	  if($member_rmb -> lessonlypay($money, $mid)){
	    $this -> success(L('DATA_UPDATE_SUCCESS'));
	  }else{
	    $this -> error(L('DATA_UPDATE_ERROR'));
	  }
	}else{
	  $this -> error(L('DATA_UPDATE_ERROR'));
	}
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $this -> display();
  }

  public function editmemberservice(){
    $member = M('Member');
    if(!empty($_POST['id'])){
      if(!$member -> create()){
	$this -> error($member -> getError());
      }
      if($member -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $member -> field('name,isservice') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function editmemberdetail(){
    $this -> assign('result', M('Member') -> alias('m') -> field('m.id,m.name,m.nickname,m.tel,cs.name as csname,csa.name as csaname,edu.name as eduname,career.name as careername,m.email,m.sex,m.address,m.unit,m.homepage,m.fullname') -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> join('yesow_member_edu as edu ON m.eduid = edu.id') -> join('yesow_member_career as career ON m.careerid = career.id') -> where(array('m.id' => $this -> _get('mid', 'intval'))) -> find());
    $this -> display();
  }

  public function addreviewformmember(){

    if(!empty($_POST['org5_name'])){
      $MemberReview = M('MemberReview');
      $data_add = array();
      $data_add['aid'] = session(C('USER_AUTH_KEY'));
      $data_add['mid'] = $this -> _post('mid', 'intval');
      $data_add['name'] = $this -> _post('org5_name');
      $data_add['address'] = $this -> _post('org5_address');
      $data_add['manproducts'] = $this -> _post('org5_manproducts');
      $data_add['companyphone'] = $this -> _post('org5_companyphone');
      $data_add['mobilephone'] = $this -> _post('org5_mobilephone');
      $data_add['linkman'] = $this -> _post('org5_linkman');
      $data_add['email'] = $this -> _post('org5_email');
      $data_add['qqcode'] = $this -> _post('org5_qqcode');
      $data_add['csname'] = $this -> _post('org5_csname');
      $data_add['csaname'] = $this -> _post('org5_csaname');
      $data_add['ccname_one'] = $this -> _post('org5_cc1name');
      $data_add['ccname_two'] = $this -> _post('org5_cc2name');
      $data_add['website'] = $this -> _post('org5_website');
      $data_add['new_linkman'] = $this -> _post('new_linkman');
      $data_add['new_companyphone'] = $this -> _post('org5_new_companyphone');
      $data_add['new_mobilephone'] = $this -> _post('new_mobilephone');
      $data_add['new_qqonline'] = $this -> _post('new_qqocde');
      $data_add['new_email'] = $this -> _post('new_email');
      $data_add['unit'] = $this -> _post('unit');
      $effect = '';
      foreach($_POST['effect'] as $value){
	if(!$effect){
	  $effect .= $value;
	}else{
	  $effect .= ',' . $value;
	}
      }
      $data_add['effect'] = $effect;
      $data_add['wanttobuy'] = $this -> _post('wanttobuy');
      $data_add['feedback'] = $this -> _post('feedback');
      $data_add['remark'] = $this -> _post('remark');
      $data_add['addtime'] = time();

      if(!$MemberReview -> create($data_add)){
	$this -> error($MemberReview -> getError());
      }
      if($rid = $MemberReview -> add()){
	if(!empty($_POST['sendsms'])){
	  D('MemberReviewSendSmsRecord') -> sendsms($rid);
	}
	if(!empty($_POST['sendemail'])){
	  $to_email = $MemberReview -> getFieldByid($rid, 'new_email');
	  D('MassEmailSetting') -> sendEmail('review_phone', $to_email, $rid);
	}
	//add record
	$MemberReviewRecord = M('MemberReviewRecord');
	$record_data = array();
	$record_data['rid'] = $rid;
	$record_data['nexttime'] = $this -> _post('nexttime', 'strtotime');
	$record_data['addtime'] = time();
	$record_data['nodeal'] = $this -> _post('nodeal');
	$record_data['status'] = $this -> _post('status');
	$MemberReviewRecord -> add($record_data);

	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }

    $member = M('Member');
    $result = $member -> field('id,name,unit,fullname,tel,qqcode,email') -> find($this -> _get('mid', 'intval'));
    $this -> assign('result', $result);
    $effect_arr = array(
      1 => '比较敷衍',
      2 => '希望了解',
      3 => '比较抗拒 ',
      4 => '抗拒到理解',
      5 => '需要考虑',
      6 => '需要商量',
    );
    $this -> assign('effect_arr', $effect_arr);
    $this -> display();
  }

  public function monthlymember(){
    $monthly = M('Monthly');
    $where = array();
    if(!empty($_POST['name'])){
      $where['tmpt.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $monthly -> table('(SELECT mid FROM yesow_monthly GROUP BY mid) as m') -> join('LEFT JOIN (SELECT m.id,m.name,cs.name as csname,m.nickname FROM yesow_member as m LEFT JOIN yesow_child_site as cs ON m.csid = cs.id) as tmpt ON m.mid = tmpt.id') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $monthly -> table('(SELECT * FROM `yesow_monthly` ORDER BY mid ASC, starttime DESC) as m') -> field('m.mid,tmp.name as tname,tmpt.name as mname,tmpt.csname as csname,tmpt.csaname as csaname,tmpt.mjoin_time,tmpt.mltime,m.starttime,m.endtime,tmpt.nickname,m.ischeck') -> join('LEFT JOIN (SELECT ml.name,mm.id FROM yesow_member_monthly as mm LEFT JOIN yesow_member_level as ml ON mm.lid = ml.id) as tmp ON m.monid = tmp.id') -> join('LEFT JOIN (SELECT m.id,m.name,cs.name as csname,csa.name as csaname,m.nickname,m.join_time as mjoin_time,m.last_login_time as mltime FROM yesow_member as m LEFT JOIN yesow_child_site as cs ON m.csid = cs.id LEFT JOIN yesow_child_site_area as csa ON m.csaid = csa.id) as tmpt ON m.mid = tmpt.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('m.endtime DESC,m.starttime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();

    /*
    $Monthly = M('Monthly');

    $count = $Monthly -> table('(SELECT mid FROM yesow_monthly GROUP BY mid) as m') -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $Monthly -> table('(SELECT * FROM `yesow_monthly` ORDER BY mid ASC, starttime DESC) as my') -> field('my.mid,m.name as mname,cs.name as csname,csa.name as csaname,ttt.name as tname,m.join_time as mjoin_time,m.last_login_time as mltime,my.endtime') -> join('LEFT JOIN (SELECT * FROM (select mr.mid,ml.name,mr.rmb_pay+mr.rmb_exchange as count from yesow_member_rmb as mr LEFT JOIN yesow_member_level as ml ON mr.rmb_pay+mr.rmb_exchange >= ml.updatemoney ORDER BY mr.mid,ml.updatemoney DESC) as tmp GROUP BY mid) as ttt ON my.mid = ttt.mid') -> join('yesow_member as m ON my.mid = m.id') -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> group('my.mid') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
     */
    //$monthlydetail = M('MemberMonthlyDetail');
    //$result = $monthlydetail -> table('yesow_member_monthly_detail as mmd') -> field('tmp.id as mid,tmp.name as mname,tmp.csname,tmp.csaname,ttt.name as tname,tmp.join_time as mjoin_time,tmp.last_login_time as mltime,m.endtime') -> join('LEFT JOIN (SELECT m.id,m.name,cs.name as csname,csa.name as csaname,m.join_time,m.last_login_time FROM yesow_member as m LEFT JOIN yesow_child_site as cs ON m.csid = cs.id LEFT JOIN yesow_child_site_area as csa ON m.csaid = csa.id) as tmp ON mmd.mid = tmp.id') -> join('LEFT JOIN (SELECT * FROM (select mr.mid,ml.name,mr.rmb_pay+mr.rmb_exchange as count from yesow_member_rmb as mr LEFT JOIN yesow_member_level as ml ON mr.rmb_pay+mr.rmb_exchange >= ml.updatemoney ORDER BY mr.mid,ml.updatemoney DESC) as tmp GROUP BY mid) as ttt ON mmd.mid = ttt.mid') -> join('LEFT JOIN (SELECT * FROM (SELECT * FROM yesow_monthly ORDER BY starttime DESC) as ttt GROUP BY mid) as m ON mmd.mid = m.mid') -> group('mmd.mid') -> select();
    
  }

  public function auditmembermonthly(){
    $monthlydetail = M('MemberMonthlyDetail');
    $where = array();
    $where['mid'] = $this -> _request('id', 'intval');
    $count = $monthlydetail -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $monthlydetail -> field('id,type,content,addtime') -> order('addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delmonthlymember(){
    $monthlydetail = M('MemberMonthlyDetail');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($monthlydetail -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function memberedu(){
    $member_edu = M('MemberEdu');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $member_edu -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $member_edu -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addmemberedu(){
    if(!empty($_POST['name'])){
      $member_edu = D('MemberEdu');
      if(!$member_edu -> create()){
	$this -> error($member_edu -> getError());
      }
      if($member_edu -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function editmemberedu(){
    $member_edu = D('MemberEdu');
    if(!empty($_POST['name'])){
      if(!$member_edu -> create()){
	$this -> error($member_edu -> getError());
      }
      if($member_edu -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $member_edu -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function delmemberedu(){
    $member_edu = M('MemberEdu');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($member_edu -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function membercareer(){
    $member_career = M('MemberCareer');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $member_career -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $member_career -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addmembercareer(){
      if(!empty($_POST['name'])){
	$member_career = D('MemberCareer');
      if(!$member_career -> create()){
	$this -> error($member_career -> getError());
      }
      if($member_career -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function editmembercareer(){
    $member_career = D('MemberCareer');
    if(!empty($_POST['name'])){
      if(!$member_career -> create()){
	$this -> error($member_career -> getError());
      }
      if($member_career -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $member_career -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function delmembercareer(){
    $member_career = M('MemberCareer');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($member_career -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function memberincome(){
    $member_income = M('MemberIncome');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $member_income -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $member_income -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addmemberincome(){
    if(!empty($_POST['name'])){
      $member_income = D('MemberIncome');
      if(!$member_income -> create()){
	$this -> error($member_income -> getError());
      }
      if($member_income -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function editmemberincome(){
    $member_income = D('MemberIncome');
    if(!empty($_POST['name'])){
      if(!$member_income -> create()){
	$this -> error($member_income -> getError());
      }
      if($member_income -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $member_income -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function delmemberincome(){
    $member_income = M('MemberIncome');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($member_income -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function memberbasic(){
    $member_setup = M('MemberSetup');
    if(isset($_POST['ms_viewcomment'])){
      $mun = 0;
      foreach($_POST as $key => $value){
	if(substr($key, 0, 3) == 'ms_'){
	  $data = array();
	  $where = array();
	  $where['name'] = substr($key, 3);
	  $data['value'] = $value;
	  $num += $member_setup -> where($where) -> save($data);
	}
      }
      if($num != 0){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
	$this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $this -> assign('viewcomment', $member_setup -> getFieldByname('viewcomment', 'value'));
    $this -> display();
  
  }

  public function memberlevel(){
    $level = M('MemberLevel');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $level -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $level -> field('id,name,updatemoney,freecompany,addtime,remark') -> where($where) -> order('updatemoney ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addmemberlevel(){
    if(!empty($_POST['name'])){
      $level = D('MemberLevel');
      if(!$level -> create()){
	$this -> error($level -> getError());
      }
      if($level -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delmemberlevel(){
    $level = M('MemberLevel');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($level -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editmemberlevel(){
    $level = D('MemberLevel');
    if(!empty($_POST['name'])){
      if(!$level -> create()){
	$this -> error($level -> getError());
      }
      if($level -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $level -> field('name,updatemoney,freecompany,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function editleveleb(){
    $level = D('MemberLevel');
    if(!empty($_POST['id'])){
      if(!$level -> create()){
	$this -> error($level -> getError());
      }
      if($level -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $level -> field('eb_one,eb_two,eb_three,eb_four,eb_five,eb_six,eb_seven,eb_eight') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();  
  }

  public function editlevelauthor(){
    $level = D('MemberLevel');
    if(!empty($_POST['id'])){
      $id = $this -> _post('id', 'intval');
      $data = array();
      $data['author_one'] = 0;
      $data['author_two'] = 0;
      $data['author_three'] = 0;
      $data['author_four'] = 0;
      $data['author_five'] = 0;
      $data['author_six'] = 0;
      $data['author_seven'] = 0;
      $data['author_eight'] = 0;
      $data['author_nine'] = 0;
      $data['author_ten'] = 0;
      $data['id'] = $id;
      $level -> save($data);
      $data = array();
      foreach($_POST['author'] as $value){
	$data[$value] = 1;
      }
      $data['id'] = $id;
      $data['rmb_one'] = $this -> _post('rmb_one');
      $data['rmb_two'] = $this -> _post('rmb_two');
      $data['rmb_three'] = $this -> _post('rmb_three');
      $data['monthly_one_num'] = $this -> _post('monthly_one_num');
      $data['monthly_two_num'] = $this -> _post('monthly_two_num');
      $data['monthly_three_num'] = $this -> _post('monthly_three_num');
      $level -> save($data);
      $this -> success(L('DATA_UPDATE_SUCCESS'));

    }
    $name = $level -> getFieldByid($this -> _get('id', 'intval'), 'name');
    $this -> assign('name', $name);
    $result = $level -> field('rmb_one,rmb_two,rmb_three,author_one,author_two,author_three,author_four,author_five,author_six,author_seven,author_eight,author_nine,author_ten,monthly_one_num,monthly_two_num,monthly_three_num') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function todaylogin(){
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $member = M('Member');
    $where = array();
    if(!empty($_POST['name'])){
      $where['m.' . $this -> _post('key')] = $this -> _post('name');
    }
    if(!empty($_POST['csid'])){
      $where['m.csid'] = $this -> _post('csid', 'intval');
    }
    if(!empty($_POST['csaid'])){
      $where['m.csaid'] = $this -> _post('csaid', 'intval');
    }
    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $dayBegin = mktime(0,0,0,$month,$day,$year);
    $dayEnd = mktime(23,59,59,$month,$day,$year);
    $where['m.last_login_time'] = array(array('gt', $dayBegin),array('lt', $dayEnd));
    $count = $member -> table('yesow_member as m') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $member -> table('yesow_member as m') -> field('m.id,m.name,cs.name as csname,csa.name as csaname,m.lastest_login_time,m.last_login_time,m.login_count,m.last_login_ip,ttt.name as tname,ttt.count as tcount') -> where($where) -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> join('LEFT JOIN (SELECT * FROM (select mr.mid,ml.name,mr.rmb_pay+mr.rmb_exchange as count from yesow_member_rmb as mr LEFT JOIN yesow_member_level as ml ON mr.rmb_pay+mr.rmb_exchange >= ml.updatemoney ORDER BY mr.mid,ml.updatemoney DESC) as tmp GROUP BY mid) as ttt ON m.id = ttt.mid') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('m.last_login_time DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function monthlogin(){
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $member = M('Member');
    $where = array();
    if(!empty($_POST['name'])){
      $where['m.' . $this -> _post('key')] = $this -> _post('name');
    }
    if(!empty($_POST['csid'])){
      $where['m.csid'] = $this -> _post('csid', 'intval');
    }
    if(!empty($_POST['csaid'])){
      $where['m.csaid'] = $this -> _post('csaid', 'intval');
    }
    $year = date("Y");
    $month = date("m");
    $day = date("t");
    $dayBegin = mktime(0,0,0,$month,1,$year);
    $dayEnd = mktime(23,59,59,$month,$day,$year);
    $where['m.last_login_time'] = array(array('gt', $dayBegin),array('lt', $dayEnd));
    $count = $member -> table('yesow_member as m') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $member -> table('yesow_member as m') -> field('m.id,m.name,cs.name as csname,csa.name as csaname,m.lastest_login_time,m.last_login_time,m.login_count,m.last_login_ip,ttt.name as tname,ttt.count as tcount') -> where($where) -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> join('LEFT JOIN (SELECT * FROM (select mr.mid,ml.name,mr.rmb_pay+mr.rmb_exchange as count from yesow_member_rmb as mr LEFT JOIN yesow_member_level as ml ON mr.rmb_pay+mr.rmb_exchange >= ml.updatemoney ORDER BY mr.mid,ml.updatemoney DESC) as tmp GROUP BY mid) as ttt ON m.id = ttt.mid') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('m.login_count DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function yearlogin(){
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $member = M('Member');
    $where = array();
    if(!empty($_POST['name'])){
      $where['m.' . $this -> _post('key')] = $this -> _post('name');
    }
    if(!empty($_POST['csid'])){
      $where['m.csid'] = $this -> _post('csid', 'intval');
    }
    if(!empty($_POST['csaid'])){
      $where['m.csaid'] = $this -> _post('csaid', 'intval');
    }
    $year = date("Y");
    $dayBegin = mktime(0,0,0,1,1,$year);
    $dayEnd = mktime(23,59,59,12,31,$year);
    $where['m.last_login_time'] = array(array('gt', $dayBegin),array('lt', $dayEnd));
    $count = $member -> table('yesow_member as m') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $member -> table('yesow_member as m') -> field('m.id,m.name,cs.name as csname,csa.name as csaname,m.lastest_login_time,m.last_login_time,m.login_count,m.last_login_ip,ttt.name as tname,ttt.count as tcount') -> where($where) -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> join('LEFT JOIN (SELECT * FROM (select mr.mid,ml.name,mr.rmb_pay+mr.rmb_exchange as count from yesow_member_rmb as mr LEFT JOIN yesow_member_level as ml ON mr.rmb_pay+mr.rmb_exchange >= ml.updatemoney ORDER BY mr.mid,ml.updatemoney DESC) as tmp GROUP BY mid) as ttt ON m.id = ttt.mid') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('m.login_count DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function resumepotion(){
    $ResumePotion = M('ResumePotion');
    $where = array();
    if(!empty($_POST['title'])){
      $where['title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    $count = $ResumePotion -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $ResumePotion -> field('id,title,remark,addtime') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addresumepotion(){
    if(!empty($_POST['title'])){
      $ResumePotion = M('ResumePotion');
      if(!$ResumePotion -> create()){
	$this -> error($ResumePotion -> getError());
      }
      $ResumePotion -> addtime = time();
      if($ResumePotion -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delresumepotion(){
    $ResumePotion = M('ResumePotion');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($ResumePotion -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editresumepotion(){
    $ResumePotion = M('ResumePotion');
    if(!empty($_POST['id'])){
      if(!$ResumePotion -> create()){
	$this -> error($ResumePotion -> getError());
      }
      if($ResumePotion -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $ResumePotion -> field('title,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function resume(){
    $Resume = M('Resume');
    $where = array();
    if(!empty($_POST['realname'])){
      $where['r.realname'] = array('LIKE', '%' . $this -> _post('realname') . '%');
    }
    $count = $Resume -> alias('r') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $Resume -> alias('r') -> field('r.id,r.realname,r.addtime,p.title,r.sex,r.mobilephone,r.jobstatus,r.monthlysalary,r.jbotype') -> join('yesow_resume_potion as p ON r.pid = p.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delresume(){
    $Resume = M('Resume');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($Resume -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

}

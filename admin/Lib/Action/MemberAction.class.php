<?php
class MemberAction extends CommonAction {

  /* ----------- 会员管理 ------------ */

  //注册会员管理
  public function member(){
    $childsite = M('ChildSite');
    //查分站信息
    $result_childsite = $childsite -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $member = M('Member');
    $where = array();
    //构建查询条件
    if(!empty($_POST['name'])){
      $where['m.' . $this -> _post('key')] = $this -> _post('name');
    }
    if(!empty($_POST['csid'])){
      $where['m.csid'] = $this -> _post('csid', 'intval');
    }
    if(!empty($_POST['csaid'])){
      $where['m.csaid'] = $this -> _post('csaid', 'intval');
    }
    //记录总数
    $count = $member -> table('yesow_member as m') -> where($where) -> count('id');
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
    //会员数据
    $result = $member -> table('yesow_member as m') -> field('m.id,m.name,m.nickname,m.sex,m.email,cs.name as csname,csa.name as csaname,m.join_time,m.last_login_time,m.status,m.ischeck') -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> order('id DESC') -> where($where) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //编辑会员
  public function editmember(){
    $member = D('Member');
    //处理更新
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
    //会员数据
    $result = $member -> field('csid,csaid,eduid,careerid,incomeid,name,nickname,passwordquestion,passwordanswer,status,ischeck,fullname,idnumber,sex,tel,qqcode,msn,email,address,zipcode,unit,homepage,headico') -> find($this -> _get('id','intval'));
    $this -> assign('result', $result);
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询分站下地区
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    //查询学历
    $result_memberedu = M('MemberEdu') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_memberedu', $result_memberedu);
    //查询职业
    $result_membercareer = M('MemberCareer') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_membercareer', $result_membercareer);
    //查询收入
    $result_memberincome = M('MemberIncome') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_memberincome', $result_memberincome);

    $this -> display();
  }

  //删除会员
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

  //会员学历管理
  public function memberedu(){
    $member_edu = M('MemberEdu');
    $where = array();
    //处理搜索
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $member_edu -> where($where) -> count('id');
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
    //结果
    $result = $member_edu -> field('id,name,sort,remark') -> where($where) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加会员学历
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

  //编辑会员学历
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

  //删除会员学历
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

  //会员职业管理
  public function membercareer(){
    $member_career = M('MemberCareer');
    $where = array();
    //处理搜索
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $member_career -> where($where) -> count('id');
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
    //结果
    $result = $member_career -> field('id,name,sort,remark') -> where($where) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加会员职业
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

  //编辑会员职业
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

  //删除会员职业
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

  //会员收入管理
  public function memberincome(){
    $member_income = M('MemberIncome');
    $where = array();
    //处理搜索
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $member_income -> where($where) -> count('id');
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
    //结果
    $result = $member_income -> field('id,name,sort,remark') -> where($where) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加会员收入
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

  //编辑会员收入
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

  //删除会员收入
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

  /* ----------- 会员管理 ------------ */


  /* ----------- 统计管理 ------------ */

  //今日登陆用户
  public function todaylogin(){
    $childsite = M('ChildSite');
    //查分站信息
    $result_childsite = $childsite -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $member = M('Member');
    $where = array();
    //构建查询条件
    if(!empty($_POST['name'])){
      $where['m.' . $this -> _post('key')] = $this -> _post('name');
    }
    if(!empty($_POST['csid'])){
      $where['m.csid'] = $this -> _post('csid', 'intval');
    }
    if(!empty($_POST['csaid'])){
      $where['m.csaid'] = $this -> _post('csaid', 'intval');
    }
    //计算时间区间
    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $dayBegin = mktime(0,0,0,$month,$day,$year);//当天开始时间戳
    $dayEnd = mktime(23,59,59,$month,$day,$year);//当天结束时间戳
    $where['m.last_login_time'] = array(array('gt', $dayBegin),array('lt', $dayEnd));
    //记录总数
    $count = $member -> table('yesow_member as m') -> where($where) -> count('id');
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
    $result = $member -> table('yesow_member as m') -> field('m.id,m.name,cs.name as csname,csa.name as csaname,m.lastest_login_time,m.last_login_time,m.login_count,m.last_login_ip') -> where($where) -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> order('m.last_login_time DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    

    $this -> display();
  }

  /* ----------- 统计管理 ------------ */

  /* ----------- 公告管理 ------------ */

   //后台公告管理
  public function backgroundnotice(){
    $notice = M('MemberBackgroundNotice');
    $where = array();
    //处理搜索
    if(!empty($_POST['title'])){
      $where['title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    //记录总数
    $count = $notice -> where($where) -> count('id');
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
    //数据
    $result = $notice -> field('id,title,content,addtime') -> where($where) -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();  
  }

  //添加后台公告
  public function addbackgroundnotice(){
    //处理添加
    if(!empty($_POST['title'])){
      $notice = D('MemberBackgroundNotice');
      if(!$notice -> create()){
	$this -> error($notice -> getError());
      }
      if($notice -> add()){
	//删除缓存
	S('member_background_notice', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除后台公告
  public function delbackgroundnotice(){
    $notice = M('MemberBackgroundNotice');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($notice -> where($where_del) -> delete()){
      //删除缓存
      S('member_background_notice', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑后台公告
  public function editbackgroundnotice(){
    $notice = D('MemberBackgroundNotice');
    //处理更新
    if(!empty($_POST['title'])){
      if(!$notice -> create()){
	$this -> error($notice -> getError());
      }
      if($notice -> save()){
	//删除缓存
	S('member_background_notice', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //数据
    $result = $notice -> field('title,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  /* ----------- 公告管理 ------------ */


}

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
    $result = $member -> table('yesow_member as m') -> field('m.id,m.name,m.nickname,m.sex,m.email,cs.name as csname,csa.name as csaname,m.join_time,m.last_login_time,m.status,m.ischeck') -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('id DESC') -> where($where) -> select();
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
    $result = $member_edu -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
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
    $result = $member_career -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
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
    $result = $member_income -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
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

  //会员基本设置
  public function memberbasic(){
  
  }

  /* ----------- 会员管理 ------------ */


  /* ----------- 会员级别管理 ------------ */

  //会员等级管理
  public function memberlevel(){
    $level = M('MemberLevel');
    $where = array();
    //处理搜索
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $level -> where($where) -> count('id');
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
    $result = $level -> field('id,name,updatemoney,freecompany,addtime,remark') -> where($where) -> order('updatemoney ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加会员等级
  public function addmemberlevel(){
    //添加
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

  //删除会员等级
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

  //编辑会员等级
  public function editmemberlevel(){
    $level = D('MemberLevel');
    //处理更新
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

  //会员等级RMB设置
  public function editlevelrmb(){
    $level = D('MemberLevel');
    //更新
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
    $result = $level -> field('rmb_one') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //会员等级EB设置
  public function editleveleb(){
    $level = D('MemberLevel');
    //更新
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

  //会员等级权限设置
  public function editlevelauthor(){
    $level = D('MemberLevel');
    //更新
    if(!empty($_POST['id'])){
      $id = $this -> _post('id', 'intval');
      //先将所有权限设置项归零
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
      $data['id'] = $id;
      $level -> save($data);
      //再将有权限的设置为1
      $data = array();
      foreach($_POST['author'] as $value){
	$data[$value] = 1;
      }
      $data['id'] = $id;
      if($level -> save($data)){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }

    }
    $result = $level -> field('author_one,author_two,author_three,author_four,author_five,author_six,author_seven,author_eight,author_nine') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  /* ----------- 会员级别管理 ------------ */


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

  //本月登录用户
  public function monthlogin(){
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
    $day = date("t");
    $dayBegin = mktime(0,0,0,$month,1,$year);//当月开始时间戳
    $dayEnd = mktime(23,59,59,$month,$day,$year);//当月结束时间戳
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
    $result = $member -> table('yesow_member as m') -> field('m.id,m.name,cs.name as csname,csa.name as csaname,m.lastest_login_time,m.last_login_time,m.login_count,m.last_login_ip') -> where($where) -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> order('m.login_count DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //本年登录用户
  public function yearlogin(){
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
    $dayBegin = mktime(0,0,0,1,1,$year);//当年开始时间戳
    $dayEnd = mktime(23,59,59,12,31,$year);//当年结束时间戳
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
    $result = $member -> table('yesow_member as m') -> field('m.id,m.name,cs.name as csname,csa.name as csaname,m.lastest_login_time,m.last_login_time,m.login_count,m.last_login_ip') -> where($where) -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> order('m.login_count DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  /* ----------- 统计管理 ------------ */


}

<?php
/*
 * 系统设置
 */
class SystemAction extends CommonAction {

  //节点管理
  public function node(){
    $node = M('Node');
    //构建查询条件
    $where = array();
    $where['pid'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    if(!empty($_REQUEST['name'])){
      $where['name'] = array('LIKE', '%' .  $_REQUEST['name'] . '%');
    }
    //记录总数
    $count = $node -> where($where) -> count('id');
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

    
    $result = $node -> field('id,name,title,remark,sort') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) ->  order('sort') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //增加节点
  public function addnode(){
    $node = D('Node');
    //处理新增
    if(!empty($_POST['name'])){
      if(!$node -> create()){
	$this -> error($node -> getError());
      }
      if($node -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }    
    //查询并计算父级名称 和 本次更新的level、pid
    $pid = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    if(!empty($_REQUEST['id'])){
      $result = $node -> field('title,level') -> find($this -> _get('id', 'intval'));
    }
    $title = isset($result['title']) ? $result['title'] : '无';
    $level = isset($result['level']) ? $result['level'] + 1 : 1;
    $this -> assign('title', $title);
    $this -> assign('level', $level);
    $this -> assign('pid', $pid);
    $this -> display();
  }

  //删除节点
  public function delnode(){
    $node = M('Node');
    //待删除的id数组
    $id = array();
    //查出所有要删除的id
    $id[] = $this -> _get('id', 'intval');
    $where = array();
    $where['pid'] = $this -> _get('id', 'intval');
    $result = $node ->field('id') -> where($where) -> select();
    $temp_arr = array();
    if(!empty($result)){
      foreach($result as $value){
	//待删除的二级id
	$temp_arr[] = intval($value['id']);
      }
      //查3级
      $where['pid'] = array('in', $temp_arr);
      $result3 = $node -> field('id') -> where($where) -> select();
      if(!empty($result3)){
	foreach($result3 as $value){
	  $temp_arr[] = intval($value['id']);
	}
      }
    }
    $id = array_merge($temp_arr, $id);
    //构建删除条件
    $del_where = array();
    $del_where['id'] = array('in', $id);
    if($node -> where($del_where) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑节点
  public function editnode(){
    $node = D('Node');
    //处理更新
    if(!empty($_POST['name'])){
      if(!$node -> create()){
	$this -> error($node -> getError());
      }
      if($node -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $node -> field('id,name,title,remark,sort') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //管理员管理
  public function adminer(){
    $admin = M('Admin');
    //处理查询条件
    $where = array();
    $where_page = array();
    if(!empty($_POST['name'])){
      $where['a.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
      $where_page['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $admin -> where($where_page) -> count('id');
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
    //查询结果
    $result = $admin -> table('yesow_admin as a') -> field('a.id,a.name,cs.name as csname,cs.domain as domain,a.status,a.last_login_ip,a.last_login_time,a.login_count,a.remark,r.name as rolename') -> join('yesow_child_site as cs ON cs.id = a.csid') -> join('yesow_role_admin as ra ON a.id = ra.admin_id') -> join('yesow_role as r ON ra.role_id = r.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('id DESC') -> select();
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

  //添加管理员
  public function addadminer(){
    $admin = D('Admin');
    //处理新增
    if(!empty($_POST['name'])){
      $_POST['password'] = sha1($_POST['password']);
      if(!$admin -> create()){
	$this -> error($admin -> getError());
      }
      $uid = $admin -> add();
      //添加管理组信息
      if(!empty($_POST['roleid'])){
	$role_admin = M('RoleAdmin');
	$data['role_id'] = $this -> _post('roleid', 'intval');
	$data['admin_id'] = $uid;
      }
      if(!empty($uid) && $role_admin -> add($data)){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);

    //查所有管理组
    $role = M('Role');
    $result_role = $role -> field('id,name') -> select();
    $this -> assign('result_role', $result_role);

    $this -> display();
  }

  //删除管理员
  public function deladminer(){
    $admin = M('Admin');
    if($admin -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑管理员
  public function editadminer(){
    $admin = D('Admin');
    //处理更新数据
    if(!empty($_POST['name'])){
      if(empty($_POST['password'])){
	unset($_POST['password']);
      }else{
	$_POST['password'] = sha1($_POST['password']);
      }
      if(!$admin -> create()){
	$this -> error($admin -> getError());
      }

      //更新管理组信息
      if($_POST['oldroleid'] != $_POST['roleid']){
	$data = array();
	$where = array();
	$data['role_id'] = $this -> _post('roleid', 'intval');
	$where['admin_id'] = $this -> _post('id', 'intval');
	$res = M('RoleAdmin') -> where($where) -> save($data);
      }

      if($admin -> save() || $res){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $admin -> field('id,csid,name,status,remark') -> find($this -> _get('id'));
    $this -> assign('result', $result);
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查所有管理组
    $role = M('Role');
    $result_role = $role -> field('id,name') -> select();
    $this -> assign('result_role', $result_role);
    //查本管理员的所属组
    $result_myrole = M('RoleAdmin') -> getFieldByadmin_id($this -> _get('id', 'intval'), 'role_id');
    $this -> assign('result_myrole', $result_myrole);
    $this -> display();
  }

  //管理组管理
  public function admingroup(){
    $role = M('Role');
    //构造条件
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $role -> where($where) -> count('id');
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
    //管理组信息
    $result = $role -> table('yesow_role as r') -> field('id,name,remark,create_time,update_time,c') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> join('(SELECT role_id,COUNT(admin_id) as c FROM yesow_role_admin GROUP BY role_id) as tmp ON tmp.role_id = r.id') -> order('id DESC') -> select();
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

  //增加管理组
  public function addadmingroup(){
    $role = D('Role');
    if(!empty($_POST['name'])){
      if(!$role -> create()){
	$this -> error($role -> getError());
      }
      if($role -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除管理组
  public function deladmingroup(){
    $role = M('Role');
    if($role -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑管理组
  public function editadmingroup(){
    $role = D('Role');
    if(!empty($_POST['name'])){
      if(!$role -> create()){
	$this -> error($role -> getError());
      }
      if($role -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $role -> field('id,name,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //成员管理
  public function groupuser(){
    $role_admin = M('RoleAdmin');
    $num = 0;
    if(!empty($_POST['role_id'])){
      //先删除
      $where_del = array();
      $where_del['role_id'] = $this -> _post('role_id', 'intval');
      $num = $role_admin -> where($where_del) -> delete();
      //再添加
      if(!empty($_POST['admin_id'])){
	$num = 0;
	foreach($_POST['admin_id'] as $value){
	  $num += $role_admin -> add(array('role_id' => $_POST['role_id'], 'admin_id' => $value));
	}
      }
      if($num > 0){
	$role = M('Role');
	//更新时间
	$up_id = $this -> _post('role_id', 'intval');
	$up_time = time();
	$role -> save(array('id' => $up_id, 'update_time' => $up_time));
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //查现有组中的成员
    
    $where_role_admin = array();
    $role_id = $this -> _get('id', 'intval');
    $where_role_admin['role_id'] = $role_id;
    $result_role_admin = $role_admin -> field('admin_id') -> where($where_role_admin) -> select();
    $result_ra = array();
    foreach($result_role_admin as $value){
      $result_ra[] = $value['admin_id'];
    }
    $this -> assign('result_ra', $result_ra);
    //查管理员，此管理员不属于任何一个管理组，或者是本组成员
    $admin = M('Admin');
    $result_admin = $admin -> query("SELECT id,name FROM yesow_admin WHERE id NOT IN (select admin_id FROM yesow_role_admin) UNION SELECT id,name FROM yesow_admin WHERE id IN(SELECT admin_id FROM yesow_role_admin WHERE role_id = {$role_id})");
    $this -> assign('result_admin', $result_admin);
    $this -> display();
  }

  //授权管理 - 应用授权
  public function app(){
    $node = M('Node');
    $access = M('Access');
    //处理更新
    if(!empty($_POST['role_id'])){
      $num = 0;
      //先删除
      $where_del = array();
      $where_del['role_id'] = $this -> _post('role_id', 'intval');
      $where_del['node_pid'] = 0;
      $where_del['level'] = 1;
      $num +=$access -> where($where_del) -> delete();
      //再添加
      if(!empty($_POST['node_id'])){
	$num = 0;
	$data = array();
	$data['role_id'] = $this -> _post('role_id', 'intval');
	$data['level'] = $this -> _post('level', 'intval');
	$data['node_pid'] = $this -> _post('node_pid', 'intval');
	foreach($_POST['node_id'] as $value){
	  $data['node_id'] = $value;
	  $num += $access -> add($data);
	}
      }
      if($num > 0){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    //查询已授权 应用
    $where_acc = array();
    $where_acc['role_id'] = $this -> _get('rid', 'intval');
    $where_acc['level'] = 1;
    $result_acc = $access -> field('node_id') -> where($where_acc) -> select();
    $result_access = array();
    foreach($result_acc as $value){
      $result_access[] = $value['node_id'];
    }
    $this -> assign('result_access', $result_access);
    //查询应用
    $result_app = $node -> field('id,title') -> where('level = 1') -> order('sort') -> select();
    $this -> assign('result_app', $result_app);
    $this -> display();
  }

  //授权管理 - 模块授权
  public function module(){
    $access = M('Access');
    $node = M('Node');
    //处理更新
    if(!empty($_POST['role_id'])){
      //先删除
      $num = 0;
      $where_del = array();
      $where_del['role_id'] = $this -> _post('role_id', 'intval');
      $where_del['node_pid'] = $this -> _post('appid', 'intval');
      $where_del['level'] = 2;
      $num += $access -> where($where_del) -> delete();
      //再添加
      if(!empty($_POST['moduleid'])){
	$num = 0;
	$data= array();
	$data['role_id'] = $this -> _post('role_id', 'intval');
	$data['node_pid'] = $this -> _post('appid', 'intval');
	$data['level'] = 2;
	foreach($_POST['moduleid'] as $value){
	  $data['node_id'] = $value;
	  $num += $access -> add($data);
	}
      }
      if($num > 0){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //查此 应用下的 模块
    if(!empty($_GET['appid'])){
      $where_module = array();
      $where_module['pid'] = $this -> _get('appid', 'intval');
      $where_module['level'] = 2;
      $result_module = $node -> where($where_module) -> field('id,title') -> select();
      $this -> assign('result_module', $result_module);
      //查已授权的 模块
      $where_acc = array();
      $where_acc['role_id'] = $this -> _get('rid', 'intval');
      $where_acc['node_pid'] = $this -> _get('appid', 'intval');
      $where_acc['level'] = 2;
      $result_acc = $access -> field('node_id') -> where($where_acc) -> select();
      $result_access2 = array();
      foreach($result_acc as $value){
	$result_access2[] = $value['node_id'];
      }
      $this -> assign('result_access2', $result_access2);
    }
    //先查已授权的应用
    $where_acc = array();
    $where_acc['a.role_id'] = $this -> _get('rid', 'intval');
    $where_acc['a.level'] = 1;
    $result_access = $access -> table('yesow_access as a') -> where($where_acc) -> field('n.id,n.title') -> join('yesow_node as n ON n.id = a.node_id') -> select();
    $this -> assign('result_access', $result_access);
    $this -> display();
  }

  //授权管理 - 操作授权
  public function action(){
    $access = M('Access');
    $node = M('Node');
    //处理更新
    if(!empty($_POST['rid'])){
      //先删除
      $num = 0;
      $where_del = array();
      $where_del['role_id'] = $this -> _post('rid', 'intval');
      $where_del['node_pid'] = $this -> _post('moduleid', 'intval');
      $where_del['level'] = 3;
      $num += $access -> where($where_del) -> delete();
      //再添加
      if(!empty($_POST['action_id'])){
	$num = 0;
	$data= array();
	$data['role_id'] = $this -> _post('rid', 'intval');
	$data['node_pid'] = $this -> _post('moduleid', 'intval');
	$data['level'] = 3;
	foreach($_POST['action_id'] as $value){
	  $data['node_id'] = $value;
	  $num += $access -> add($data);
	}
      }
      if($num > 0){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //查出此 应用 下已授权模块
    if(!empty($_GET['appid'])){
      $where_module = array();
      $where_module['a.node_pid'] = $this -> _get('appid', 'intval');
      $where_module['a.role_id'] = $this -> _get('rid', 'intval');
      $where_module['a.level'] = 2;
      $result_module = $access -> table('yesow_access as a') -> field('n.id,n.title') -> where($where_module) -> join('yesow_node as n ON n.id = a.node_id') -> select();
      $this -> assign('result_module', $result_module);
    }

    //查出此 模块下的 操作
    if(!empty($_GET['appid']) && !empty($_GET['moduleid'])){
      //先查所有操作
      $where_no = array();
      $where_no['pid'] = $this -> _get('moduleid', 'intval');
      $where_no['level'] = 3;
      $result_action = $node -> where($where_no) -> field('id,title') -> select();
      $this -> assign('result_action' ,$result_action);
      //再查已授权的操作
      $where_ac = array();
      $where_ac['role_id'] = $this -> _get('rid', 'intval');
      $where_ac['node_pid'] = $this -> _get('moduleid', 'intval');
      $where_ac['level'] = 3;
      $result_ac = $access -> where($where_ac) -> field('node_id') -> select();
      $result_access = array();
      foreach($result_ac as $value){
	$result_access[] = $value['node_id']; 
      }
      $this -> assign('result_access', $result_access);
    }
    
    //先查已授权的应用
    $where_app = array();
    $where_app['a.role_id'] = $this -> _get('rid', 'intval');
    $where_app['a.level'] = 1;
    $result_app = $access -> table('yesow_access as a') -> where($where_app) -> field('n.id,n.title') -> join('yesow_node as n ON n.id = a.node_id') -> select();
    $this -> assign('result_app', $result_app);
    $this -> display();
  }


  //辖区管理
  public function area(){
    $Area = M('Area');
    $where = array();
    //判断查询条件
    if(!empty($_REQUEST['name'])){
      $where['name'] = array('LIKE', '%' .  $_REQUEST['name'] . '%');
    }
    //记录总数
    $count = $Area -> where($where) -> count('id');
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
    $result = $Area -> field('id,name,remark') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('id DESC') -> select();
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

  //编辑辖区信息
  public function editarea(){
    $area = D('Area');
    //处理编辑辖区
    if(isset($_POST['name'])){
      if(!$area -> create()){
	$this -> error($area -> getError());
      }
      if($area -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }    
    $result = $area -> field('id,name,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //添加辖区信息
  public function addarea(){
    //验证添加信息
    if(isset($_POST['name'])){
      $area = D('Area');
      if(!$area -> create()){
	$this -> error($area -> getError());
      }
      if($area -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除辖区信息
  public function deletearea(){
    $area = M('Area');
    if($area -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //分站模板管理
  public function childsitetemplate(){
    $childsitetemplate = M('ChildSiteTemplate');
    $where = array();
     //判断查询条件
    if(!empty($_REQUEST['name'])){
      $where['name'] = array('LIKE', '%' .  $_REQUEST['name'] . '%');
    }
    //记录总数
    $count = $childsitetemplate -> where($where) -> count('id');
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
    $result = $childsitetemplate -> field('id,name,address') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('id DESC') -> select();
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

  //添加分站模板
  public function addchildsitetemplate(){
    //处理添加信息
    if(isset($_POST['name'])){
      $childsitetemplate = D('ChildSiteTemplate');
      if(!$childsitetemplate -> create()){
	$this -> error($childsitetemplate -> getError());
      }
      if($childsitetemplate -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除分站模板
  public function delchildsitetemplate(){
    $childsitetemplate = M('ChildSiteTemplate');
    if($childsitetemplate -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑分站模板
  public function editchildsitetemplate(){
    $childsitetemplate = D('ChildSiteTemplate');
    //处理编辑地区
    if(isset($_POST['name'])){
      if(!$childsitetemplate -> create()){
	$this -> error($childsitetemplate -> getError());
      }
      if($childsitetemplate -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }    
    $result = $childsitetemplate -> field('id,name,address') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //分站管理
  public function childsite(){
    $childsite = M('ChildSite');
    $where = array();
    $where_page = array(); //分页用查询条件
    //构建查询条件
    if(!empty($_POST['name'])){
      $where['cs.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
      $where_page['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_POST['aid'])){
      $where['cs.aid'] = $this -> _post('aid', 'intval');
      $where_page['aid'] = $this -> _post('aid', 'intval');
    }
    //记录总数
    $count = $childsite -> where($where_page) -> count('id');
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
    //查询地区数据
    $area = M('area');
    $result_area = $area -> field('id,name') -> select();
    $this -> assign('result_area', $result_area);
    //查询分站数据
    $result = $childsite -> table('yesow_child_site as cs') -> field('cs.id as id,cs.name as name,a.name as aname,cst.name as cstname,css.name as pname,cs.domain,cs.code,cs.create_time,cs.isshow') -> join('yesow_area as a ON a.id = cs.aid ') -> join('yesow_child_site_template as cst ON cst.id = cs.tid') -> join('yesow_child_site as css ON css.id = cs.pid') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('id DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加分站
  public function addchildsite(){
    $childsite = D('ChildSite');
    //处理添加数据
    if(isset($_POST['name'])){
      if(!$childsite -> create()){
	$this -> error($childsite -> getError());
      }
      if($childsite -> add()){
      	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询地区数据
    $area = M('area');
    $result_area = $area -> field('id,name') -> select();
    $this -> assign('result_area', $result_area);
    //查询模板
    $childsitetemplate = M('ChildSiteTemplate');
    $result_template = $childsitetemplate -> field('id,name') -> select();
    $this -> assign('result_template', $result_template);
    //查询省级分站
    $result_site = $childsite -> field('id,name') -> where('pid = 0') -> select();
    $this -> assign('result_site', $result_site);
    $this -> display();
  }

  //删除分站
  public function delchildsite(){
    $childsite = M('ChildSite');
    if($childsite -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑分站
  public function editchildsite(){    
    $childsite = D('ChildSite');
    if(isset($_POST['name'])){
      if(!$childsite -> create()){
	$this -> error($childsite -> getError());
      }
      if($childsite -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //查询分站信息
    $result = $childsite -> field('id,aid,tid,pid,name,domain,code,isshow') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询地区数据
    $area = M('area');
    $result_area = $area -> field('id,name') -> select();
    $this -> assign('result_area', $result_area);
    //查询模板
    $childsitetemplate = M('ChildSiteTemplate');
    $result_template = $childsitetemplate -> field('id,name') -> select();
    $this -> assign('result_template', $result_template);
    //查询省级分站
    $result_site = $childsite -> field('id,name') -> where('pid = 0') -> select();
    $this -> assign('result_site', $result_site);
    $this -> display();
  }

  //地区管理
  public function childsitearea(){
    $childsitearea = M('ChildSiteArea');
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $where = array();//连查用条件
    $where_page = array(); //分页用条件
    //构建查询条件
    if(!empty($_POST['name'])){
      $where['csa.name'] = array('LIKE', '%' . $this -> _post('name')  . '%');
      $where_page['name'] = array('LIKE', '%' . $this -> _post('name')  . '%');
    }
    if(!empty($_POST['csid'])){
      $where['csa.csid'] = $this -> _post('csid', 'intval');
      $where_page['csid'] = $this -> _post('csid', 'intval');
    }

    //记录总数
    $count = $childsitearea -> where($where_page) -> count('id');
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

    //查询地区数据
    $result = $childsitearea -> table('yesow_child_site_area as csa') -> field('csa.id,csa.name,cs.name as csname,csa.code,csa.create_time') -> where($where) -> join('yesow_child_site as cs ON cs.id = csa.csid') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('id DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //新增地区
  public function addchildsitearea(){
    //处理新增数据
    if(isset($_POST['name'])){
      $childsitearea = D('ChildSiteArea');
      if(!$childsitearea -> create()){
	$this -> error($childsitearea -> getError());
      }
      if($childsitearea -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  //删除地区
  public function delchildsitearea(){
    $childsitearea = M('ChildSiteArea');
    if($childsitearea -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑地区
  public function editchildsitearea(){
    $childsitearea = D('ChildSiteArea');
    //处理编辑
    if(isset($_POST['name'])){
      if(!$childsitearea -> create()){
	$this -> error($childsitearea -> getError());
      }
      if($childsitearea -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询对应地区数据
    $result = $childsitearea -> field('id,csid,name,code') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //消费产品分类管理
  public function accountclass(){
    $account_class = M('AccountClass');
    $where = array();
    //构建查询条件
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    //记录总数
    $count = $account_class -> where($where) -> count('id');
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
    
    $result = $account_class -> field('id,name,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('id DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //新增消费产品分类
  public function addaccountclass(){
    $account_class = D('AccountClass');
    //处理新增
    if(!empty($_POST['name'])){
      if(!$account_class -> create()){
	$this -> error($account_class -> getError());
      }
      if($account_class -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除消费产品分类
  public function delaccountclass(){
    $account_class = M('AccountClass');
    if($account_class -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑消费产品分类
  public function editaccountclass(){
    $account_class = D('AccountClass');
    //处理更新
    if(!empty($_POST['name'])){
      if(!$account_class -> create()){
	$this -> error($account_class -> getError());
      }
      if($account_class -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $account_class -> field('id,name,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //账目系统管理
  public function account(){
    $account = M('Account');
    $childsite = M('ChildSite');
    $where = array();
    //处理查询条件
    if(!empty($_POST['csid'])){
      $where['a.csid'] = $this -> _post('csid', 'intval');
    }

    //权限验证开始
    //如果是总管理员,可以查看所有账目,否则只能查看自己所属分站的账目
    if(!$_SESSION[C('ADMIN_AUTH_KEY')]){
      $where['a.csid'] = $_SESSION['csid'];
    }
    //权限验证结束
    
    //记录总数
    $count = $account -> table('yesow_account as a') -> where($where) -> count('id');
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

    //如果是总管理员，则查出所有分站，否则只查出管理员所属分站
    if(!$_SESSION[C('ADMIN_AUTH_KEY')]){
      $result_childsite = $childsite -> field('id,name') -> select(session('csid'));
    }else{
      $result_childsite = $childsite -> field('id,name') -> select();
    }
    $this -> assign('result_childsite', $result_childsite);

    //查询数据
    $result = $account -> table('yesow_account as a') -> field('a.id,cs.name as csname,ac.name as acname,a.create_time,a.type,a.company,a.money,a.remark') -> where($where) -> join('yesow_child_site as cs ON a.csid = cs.id') -> join('yesow_account_class as ac ON a.acid = ac.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('id DESC') -> select();
    $this -> assign('result', $result);

    //查询总入款
    $where['a.type'] = 2;
    $inmoney = $account -> table('yesow_account as a') -> field('SUM(money) as sum') -> where($where) -> select();
    $this -> assign('inmoney', $inmoney);

    //查询总扣款
    $where['a.type'] = 1;
    $outmoney = $account -> table('yesow_account as a') -> field('SUM(money) as sum') -> where($where) -> select();
    $this -> assign('outmoney', $outmoney);

    //余额
    $this -> assign('balance', $inmoney[0]['sum'] - $outmoney[0]['sum']);

    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    //管理员位
    $this -> assign('admin', $_SESSION[C('ADMIN_AUTH_KEY')] === true ? 'true' : 'false');
    $this -> display();
  }

  //添加账目
  public function addaccount(){
    $childsite = M('ChildSite');
    $accountclass = M('AccountClass');
    $account = D('Account');
    //处理添加
    if(!empty($_POST['create_time'])){
      if(!$account -> create()){
	$this -> error($account -> getError());
      }
      if($account -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }

    //查出所有站点
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);

    //查出所有产品分类
    $result_accountclass = $accountclass -> field('id,name') -> select();
    $this -> assign('result_accountclass', $result_accountclass);

    $this -> display();
  }

  //批量删除账目
  public function delaccount(){
    $account = M('Account');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($account -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑账目
  public function editaccount(){
    $account = D('Account');
    $accountclass = M('AccountClass');
    $childsite = M('ChildSite');

    //处理更新
    if(!empty($_POST['create_time'])){
      if(!$account -> create()){
	$this -> error($account -> getError());
      }
      if($account -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }

    }

    //查出所有产品分类
    $result_accountclass = $accountclass -> field('id,name') -> select();
    $this -> assign('result_accountclass', $result_accountclass);

    //查出所有站点
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);

    //查编辑数据
    $result = $account -> field('id,acid,csid,create_time,type,company,money,remark') -> find($this -> _get('id', intval));
    $this -> assign('result', $result);
    $this -> display();
  }

  /* ------------  系统设置   -------------- */
  //SEO设置
  public function systemseo(){
    $system = D('System');
    //处理更新
    if(!empty($_POST['sy_title'])){
      foreach($_POST as $key => $value){
	if(substr($key, 0, 3) == 'sy_'){
	  $data = array();
	  $where = array();
	  $where['name'] = substr($key, 3);
	  $data['value'] = $value;
	  $system -> where($where) -> save($data);
	}
      }
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }

    $this -> assign('title', $system -> getFieldByname('title', 'value'));
    $this -> assign('keywords', $system -> getFieldByname('keywords', 'value'));
    $this -> assign('description', $system -> getFieldByname('description', 'value'));
    $this -> display();
  }

  //非法词过滤
  public function illegalword(){
    $illegalword = M('IllegalWord');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    //记录总数
    $count = $illegalword -> where($where) -> count('id');
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

    $result = $illegalword -> field('id,name,addtime,remark') -> order('addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加非法词
  public function addillegalword(){
    //处理添加
    if(!empty($_POST['name'])){
      $illegalword = D('IllegalWord');
      if(!$illegalword -> create()){
	$this -> error($illegalword -> getError());
      }
      if($illegalword -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //编辑非法词
  public function editillegalword(){
    $illegalword = D('IllegalWord');
    if(!empty($_POST['name'])){
      if(!$illegalword -> create()){
	$this -> error($illegalword -> getError());
      }
      if($illegalword -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $illegalword -> field('name,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //删除非法词
  public function delillegalword(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $illegalword = M('IllegalWord');
    if($illegalword -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  /* ------------  系统设置   -------------- */

}

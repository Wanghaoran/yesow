<?php
class SystemAction extends CommonAction {

  public function ad_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('AD_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info[0]['savename'];
    }else{
      return $upload;
    }
  }

  public function node(){
    $node = M('Node');
    $where = array();
    $where['pid'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    if(!empty($_REQUEST['name'])){
      $where['name'] = array('LIKE', '%' .  $_REQUEST['name'] . '%');
    }
    $count = $node -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    
    $result = $node -> field('id,name,title,remark,sort') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) ->  order('sort') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addnode(){
    $node = D('Node');
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

  public function delnode(){
    $node = M('Node');
    $id = array();
    $id[] = $this -> _get('id', 'intval');
    $where = array();
    $where['pid'] = $this -> _get('id', 'intval');
    $result = $node ->field('id') -> where($where) -> select();
    $temp_arr = array();
    if(!empty($result)){
      foreach($result as $value){
	$temp_arr[] = intval($value['id']);
      }
      $where['pid'] = array('in', $temp_arr);
      $result3 = $node -> field('id') -> where($where) -> select();
      if(!empty($result3)){
	foreach($result3 as $value){
	  $temp_arr[] = intval($value['id']);
	}
      }
    }
    $id = array_merge($temp_arr, $id);
    $del_where = array();
    $del_where['id'] = array('in', $id);
    if($node -> where($del_where) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editnode(){
    $node = D('Node');
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

  public function adminer(){
    $admin = M('Admin');
    $where = array();
    $where_page = array();
    if(!empty($_POST['name'])){
      $where['a.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
      $where_page['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $admin -> where($where_page) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $admin -> table('yesow_admin as a') -> field('a.id,a.name,cs.name as csname,cs.domain as domain,a.status,a.last_login_ip,a.last_login_time,a.login_count,a.remark,r.name as rolename') -> join('yesow_child_site as cs ON cs.id = a.csid') -> join('yesow_role_admin as ra ON a.id = ra.admin_id') -> join('yesow_role as r ON ra.role_id = r.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('id DESC') -> select();
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

  public function addadminer(){
    $admin = D('Admin');
    if(!empty($_POST['name'])){
      $_POST['password'] = sha1($_POST['password']);
      if(!$admin -> create()){
	$this -> error($admin -> getError());
      }
      $uid = $admin -> add();
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
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $role = M('Role');
    $result_role = $role -> field('id,name') -> select();
    $this -> assign('result_role', $result_role);

    $this -> display();
  }

  public function deladminer(){
    $admin = M('Admin');
    if($admin -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editadminer(){
    $admin = D('Admin');
    if(!empty($_POST['name'])){
      if(empty($_POST['password'])){
	unset($_POST['password']);
      }else{
	$_POST['password'] = sha1($_POST['password']);
      }
      if(!$admin -> create()){
	$this -> error($admin -> getError());
      }

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
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $role = M('Role');
    $result_role = $role -> field('id,name') -> select();
    $this -> assign('result_role', $result_role);
    $result_myrole = M('RoleAdmin') -> getFieldByadmin_id($this -> _get('id', 'intval'), 'role_id');
    $this -> assign('result_myrole', $result_myrole);
    $this -> display();
  }

  public function admingroup(){
    $role = M('Role');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $role -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $role -> table('yesow_role as r') -> field('id,name,remark,create_time,update_time,c') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> join('(SELECT role_id,COUNT(admin_id) as c FROM yesow_role_admin GROUP BY role_id) as tmp ON tmp.role_id = r.id') -> order('id DESC') -> select();
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

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

  public function deladmingroup(){
    $role = M('Role');
    if($role -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

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

  public function groupuser(){
    $role_admin = M('RoleAdmin');
    $num = 0;
    if(!empty($_POST['role_id'])){
      $where_del = array();
      $where_del['role_id'] = $this -> _post('role_id', 'intval');
      $num = $role_admin -> where($where_del) -> delete();
      if(!empty($_POST['admin_id'])){
	$num = 0;
	foreach($_POST['admin_id'] as $value){
	  $num += $role_admin -> add(array('role_id' => $_POST['role_id'], 'admin_id' => $value));
	}
      }
      if($num > 0){
	$role = M('Role');
	$up_id = $this -> _post('role_id', 'intval');
	$up_time = time();
	$role -> save(array('id' => $up_id, 'update_time' => $up_time));
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    
    $where_role_admin = array();
    $role_id = $this -> _get('id', 'intval');
    $where_role_admin['role_id'] = $role_id;
    $result_role_admin = $role_admin -> field('admin_id') -> where($where_role_admin) -> select();
    $result_ra = array();
    foreach($result_role_admin as $value){
      $result_ra[] = $value['admin_id'];
    }
    $this -> assign('result_ra', $result_ra);
    $admin = M('Admin');
    $result_admin = $admin -> query("SELECT id,name FROM yesow_admin WHERE id NOT IN (select admin_id FROM yesow_role_admin) UNION SELECT id,name FROM yesow_admin WHERE id IN(SELECT admin_id FROM yesow_role_admin WHERE role_id = {$role_id})");
    $this -> assign('result_admin', $result_admin);
    $this -> display();
  }

  public function app(){
    $node = M('Node');
    $access = M('Access');
    if(!empty($_POST['role_id'])){
      $num = 0;
      $where_del = array();
      $where_del['role_id'] = $this -> _post('role_id', 'intval');
      $where_del['node_pid'] = 0;
      $where_del['level'] = 1;
      $num +=$access -> where($where_del) -> delete();
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

    $where_acc = array();
    $where_acc['role_id'] = $this -> _get('rid', 'intval');
    $where_acc['level'] = 1;
    $result_acc = $access -> field('node_id') -> where($where_acc) -> select();
    $result_access = array();
    foreach($result_acc as $value){
      $result_access[] = $value['node_id'];
    }
    $this -> assign('result_access', $result_access);
    $result_app = $node -> field('id,title') -> where('level = 1') -> order('sort') -> select();
    $this -> assign('result_app', $result_app);
    $this -> display();
  }

  public function module(){
    $access = M('Access');
    $node = M('Node');
    if(!empty($_POST['role_id'])){
      $num = 0;
      $where_del = array();
      $where_del['role_id'] = $this -> _post('role_id', 'intval');
      $where_del['node_pid'] = $this -> _post('appid', 'intval');
      $where_del['level'] = 2;
      $num += $access -> where($where_del) -> delete();
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
    if(!empty($_GET['appid'])){
      $where_module = array();
      $where_module['pid'] = $this -> _get('appid', 'intval');
      $where_module['level'] = 2;
      $result_module = $node -> where($where_module) -> field('id,title') -> select();
      $this -> assign('result_module', $result_module);
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
    $where_acc = array();
    $where_acc['a.role_id'] = $this -> _get('rid', 'intval');
    $where_acc['a.level'] = 1;
    $result_access = $access -> table('yesow_access as a') -> where($where_acc) -> field('n.id,n.title') -> join('yesow_node as n ON n.id = a.node_id') -> select();
    $this -> assign('result_access', $result_access);
    $this -> display();
  }

  public function action(){
    $access = M('Access');
    $node = M('Node');
    if(!empty($_POST['rid'])){
      $num = 0;
      $where_del = array();
      $where_del['role_id'] = $this -> _post('rid', 'intval');
      $where_del['node_pid'] = $this -> _post('moduleid', 'intval');
      $where_del['level'] = 3;
      $num += $access -> where($where_del) -> delete();
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
    if(!empty($_GET['appid'])){
      $where_module = array();
      $where_module['a.node_pid'] = $this -> _get('appid', 'intval');
      $where_module['a.role_id'] = $this -> _get('rid', 'intval');
      $where_module['a.level'] = 2;
      $result_module = $access -> table('yesow_access as a') -> field('n.id,n.title') -> where($where_module) -> join('yesow_node as n ON n.id = a.node_id') -> select();
      $this -> assign('result_module', $result_module);
    }

    if(!empty($_GET['appid']) && !empty($_GET['moduleid'])){
      $where_no = array();
      $where_no['pid'] = $this -> _get('moduleid', 'intval');
      $where_no['level'] = 3;
      $result_action = $node -> where($where_no) -> field('id,title') -> select();
      $this -> assign('result_action' ,$result_action);
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
    
    $where_app = array();
    $where_app['a.role_id'] = $this -> _get('rid', 'intval');
    $where_app['a.level'] = 1;
    $result_app = $access -> table('yesow_access as a') -> where($where_app) -> field('n.id,n.title') -> join('yesow_node as n ON n.id = a.node_id') -> select();
    $this -> assign('result_app', $result_app);
    $this -> display();
  }


  public function area(){
    $Area = M('Area');
    $where = array();
    if(!empty($_REQUEST['name'])){
      $where['name'] = array('LIKE', '%' .  $_REQUEST['name'] . '%');
    }
    $count = $Area -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $Area -> field('id,name,remark,isshow') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('id DESC') -> select();
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

  public function editarea(){
    $area = D('Area');
    if(isset($_POST['name'])){
      if(!$area -> create()){
	$this -> error($area -> getError());
      }
      if($area -> save()){
	S('header_child_site', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }    
    $result = $area -> field('id,name,remark,isshow') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function addarea(){
    if(isset($_POST['name'])){
      $area = D('Area');
      if(!$area -> create()){
	$this -> error($area -> getError());
      }
      if($area -> add()){
	S('header_child_site', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function deletearea(){
    $area = M('Area');
    if($area -> delete($this -> _get('id', 'intval'))){
      S('header_child_site', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function childsitetemplate(){
    $childsitetemplate = M('ChildSiteTemplate');
    $where = array();
    if(!empty($_REQUEST['name'])){
      $where['name'] = array('LIKE', '%' .  $_REQUEST['name'] . '%');
    }
    $count = $childsitetemplate -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $childsitetemplate -> field('id,name,address') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('id DESC') -> select();
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

  public function addchildsitetemplate(){
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

  public function delchildsitetemplate(){
    $childsitetemplate = M('ChildSiteTemplate');
    if($childsitetemplate -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editchildsitetemplate(){
    $childsitetemplate = D('ChildSiteTemplate');
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

  public function childsite(){
    $childsite = M('ChildSite');
    $where = array();
    $where_page = array();
    if(!empty($_POST['name'])){
      $where['cs.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
      $where_page['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_POST['aid'])){
      $where['cs.aid'] = $this -> _post('aid', 'intval');
      $where_page['aid'] = $this -> _post('aid', 'intval');
    }
    $count = $childsite -> where($where_page) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $area = M('area');
    $result_area = $area -> field('id,name') -> select();
    $this -> assign('result_area', $result_area);
    $result = $childsite -> table('yesow_child_site as cs') -> field('cs.id as id,cs.name as name,a.name as aname,cst.name as cstname,css.name as pname,cs.domain,cs.code,cs.create_time,cs.isshow') -> join('yesow_area as a ON a.id = cs.aid ') -> join('yesow_child_site_template as cst ON cst.id = cs.tid') -> join('yesow_child_site as css ON css.id = cs.pid') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('id DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addchildsite(){
    $childsite = D('ChildSite');
    if(isset($_POST['name'])){
      if(!$childsite -> create()){
	$this -> error($childsite -> getError());
      }
      if($childsite -> add()){
	S('header_child_site', NULL, NULL, '', NULL, 'index');
      	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $area = M('area');
    $result_area = $area -> field('id,name') -> select();
    $this -> assign('result_area', $result_area);
    $childsitetemplate = M('ChildSiteTemplate');
    $result_template = $childsitetemplate -> field('id,name') -> select();
    $this -> assign('result_template', $result_template);
    $result_site = $childsite -> field('id,name') -> where('pid = 0') -> select();
    $this -> assign('result_site', $result_site);
    $this -> display();
  }

  public function delchildsite(){
    $childsite = M('ChildSite');
    if($childsite -> delete($this -> _get('id', 'intval'))){
      S('header_child_site', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editchildsite(){    
    $childsite = D('ChildSite');
    if(isset($_POST['name'])){
      if(!$childsite -> create()){
	$this -> error($childsite -> getError());
      }
      if($childsite -> save()){
	S('header_child_site', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $childsite -> field('id,aid,tid,pid,name,domain,code,isshow') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $area = M('area');
    $result_area = $area -> field('id,name') -> select();
    $this -> assign('result_area', $result_area);
    $childsitetemplate = M('ChildSiteTemplate');
    $result_template = $childsitetemplate -> field('id,name') -> select();
    $this -> assign('result_template', $result_template);
    $result_site = $childsite -> field('id,name') -> where('pid = 0') -> select();
    $this -> assign('result_site', $result_site);
    $this -> display();
  }

  public function childsitearea(){
    $childsitearea = M('ChildSiteArea');
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $where = array();
    $where_page = array();
    if(!empty($_POST['name'])){
      $where['csa.name'] = array('LIKE', '%' . $this -> _post('name')  . '%');
      $where_page['name'] = array('LIKE', '%' . $this -> _post('name')  . '%');
    }
    if(!empty($_POST['csid'])){
      $where['csa.csid'] = $this -> _post('csid', 'intval');
      $where_page['csid'] = $this -> _post('csid', 'intval');
    }

    $count = $childsitearea -> where($where_page) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $childsitearea -> table('yesow_child_site_area as csa') -> field('csa.id,csa.name,cs.name as csname,csa.code,csa.create_time') -> where($where) -> join('yesow_child_site as cs ON cs.id = csa.csid') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('id DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addchildsitearea(){
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
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  public function delchildsitearea(){
    $childsitearea = M('ChildSiteArea');
    if($childsitearea -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editchildsitearea(){
    $childsitearea = D('ChildSiteArea');
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
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result = $childsitearea -> field('id,csid,name,code') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function accountclass(){
    $account_class = M('AccountClass');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $account_class -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    
    $result = $account_class -> field('id,name,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('id DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addaccountclass(){
    $account_class = D('AccountClass');
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

  public function delaccountclass(){
    $account_class = M('AccountClass');
    if($account_class -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editaccountclass(){
    $account_class = D('AccountClass');
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

  public function account(){
    $account = M('Account');
    $childsite = M('ChildSite');
    $where = array();
    if(!empty($_POST['csid'])){
      $where['a.csid'] = $this -> _post('csid', 'intval');
    }

    if(!$_SESSION[C('ADMIN_AUTH_KEY')]){
      $where['a.csid'] = $_SESSION['csid'];
    }
    $count = $account -> table('yesow_account as a') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    if(!$_SESSION[C('ADMIN_AUTH_KEY')]){
      $result_childsite = $childsite -> field('id,name') -> select(session('csid'));
    }else{
      $result_childsite = $childsite -> field('id,name') -> select();
    }
    $this -> assign('result_childsite', $result_childsite);

    $result = $account -> table('yesow_account as a') -> field('a.id,cs.name as csname,ac.name as acname,a.create_time,a.type,a.company,a.money,a.remark') -> where($where) -> join('yesow_child_site as cs ON a.csid = cs.id') -> join('yesow_account_class as ac ON a.acid = ac.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('id DESC') -> select();
    $this -> assign('result', $result);

    $where['a.type'] = 2;
    $inmoney = $account -> table('yesow_account as a') -> field('SUM(money) as sum') -> where($where) -> select();
    $this -> assign('inmoney', $inmoney);

    $where['a.type'] = 1;
    $outmoney = $account -> table('yesow_account as a') -> field('SUM(money) as sum') -> where($where) -> select();
    $this -> assign('outmoney', $outmoney);

    $this -> assign('balance', $inmoney[0]['sum'] - $outmoney[0]['sum']);

    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('admin', $_SESSION[C('ADMIN_AUTH_KEY')] === true ? 'true' : 'false');
    $this -> display();
  }

  public function addaccount(){
    $childsite = M('ChildSite');
    $accountclass = M('AccountClass');
    $account = D('Account');
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

    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $result_accountclass = $accountclass -> field('id,name') -> select();
    $this -> assign('result_accountclass', $result_accountclass);

    $this -> display();
  }

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

  public function editaccount(){
    $account = D('Account');
    $accountclass = M('AccountClass');
    $childsite = M('ChildSite');

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

    $result_accountclass = $accountclass -> field('id,name') -> select();
    $this -> assign('result_accountclass', $result_accountclass);

    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $result = $account -> field('id,acid,csid,create_time,type,company,money,remark') -> find($this -> _get('id', intval));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function systemseo(){
    $system = D('System');
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
      S('index_seo', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }

    $this -> assign('title', $system -> getFieldByname('title', 'value'));
    $this -> assign('keywords', $system -> getFieldByname('keywords', 'value'));
    $this -> assign('description', $system -> getFieldByname('description', 'value'));
    $this -> display();
  }

  public function illegalword(){
    $illegalword = M('IllegalWord');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $illegalword -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $illegalword -> field('id,name,replace,addtime,remark') -> order('addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addillegalword(){
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
    $result = $illegalword -> field('name,replace,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

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

  public function payport(){
    $payport = M('payport');
    $result = $payport -> field('name,enname,remark,status') -> select();
    $this -> assign('result', $result);
    $this -> display();
  }

  public function editport(){
    $payport = M('payport');
    if(!empty($_POST['id'])){
      if(!$payport -> create()){
	$this -> error($payport -> getError());
      }
      if($payport -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $mod = $this -> _get('mod');
    $result = $payport -> where(array('enname' => $mod)) -> find();
    $this -> assign('result', $result);
    $this -> display('editport_' . $mod);
  }

  public function websitemap(){
    $sitemap_upload = M('SitemapUpload');
    $system = M('System');
    $time = $system -> getFieldByname('updatesitemaptime', 'value');
    $this -> assign('time', $time);
    if(!empty($_POST['time'])){
      $system = M('System');
      $where['name'] = 'updatesitemaptime';
      $data['value'] = $_POST['time'];
      if($system -> where($where) -> save($data)){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
	$this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $count = $sitemap_upload -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $sitemap_upload -> order('updatetime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function delwebsitemap(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $sitemap_upload = M('SitemapUpload');
    if($sitemap_upload -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function aboutus(){
    $aboutus = M('Aboutus');
    $where = array();

    if(!empty($_POST['title'])){
      $where['title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }

    $count = $aboutus -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $aboutus -> field('id,title,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);

    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addaboutus(){
    if(!empty($_POST['title'])){
      $aboutus = M('Aboutus');
      if(!$aboutus -> create()){
	$this -> error($aboutus -> getError());
      }
      if($aboutus -> add()){
	S('index_footer_nav', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delaboutus(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $aboutus = M('Aboutus');
    if($aboutus -> where($where_del) -> delete()){
      S('index_footer_nav', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editaboutus(){
    $aboutus = M('Aboutus');
    if(!empty($_POST['title'])){
      if(!$aboutus -> create()){
	$this -> error($aboutus -> getError());
      }
      if($aboutus -> save()){
	S('index_footer_nav', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $aboutus -> field('title,sort,remark,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  
  }

  public function qqonline(){
    $qqonline = M('Qqonline');

    $where = array();
    if(!empty($_POST['qqcode'])){
      $where['q.qqcode'] = $this -> _post('qqcode');
    }
    if(!empty($_POST['csid'])){
      $where['q.csid'] = $this -> _post('csid');
    }

    $count = $qqonline -> table('yesow_qqonline as q') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $qqonline -> table('yesow_qqonline as q') -> field('q.id,q.qqcode,cs.name as csname,q.nickname,q.sex,qt.name as qtname') -> join('yesow_child_site as cs ON q.csid = cs.id') -> join('yesow_qqonline_type as qt ON q.tid = qt.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('q.id DESC') -> select();
    $this -> assign('result', $result);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addqqonline(){
    if(!empty($_POST['qqcode'])){
      $qqonline = M('Qqonline');
      if(!$qqonline -> create()){
	$this -> error($qqonline -> getError());
      }
      if(empty($_POST['csid'])){
	$qqonline -> csid = null;
      }
      if($qqonline -> add()){
	S('index_qqonline', NULL, NULL, '', NULL, 'index');
	S('member_qqonline', NULL, NULL, '', NULL, 'member');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_type = M('QqonlineType') -> field('id,name') -> select();
    $this -> assign('result_type', $result_type);
    $this -> display();
  
  }

  public function delqqonline(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $qqonline = M('Qqonline');
    if($qqonline -> where($where_del) -> delete()){
      S('index_qqonline', NULL, NULL, '', NULL, 'index');
      S('member_qqonline', NULL, NULL, '', NULL, 'member');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editqqonline(){
    $qqonline = M('Qqonline');

    if(!empty($_POST['qqcode'])){
      if(!$qqonline -> create()){
	$this -> error($qqonline -> getError());
      }
      if(empty($_POST['csid'])){
	$qqonline -> csid = null;
      }
      if($qqonline -> save()){
	S('index_qqonline', NULL, NULL, '', NULL, 'index');
	S('member_qqonline', NULL, NULL, '', NULL, 'member');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $qqonline -> field('id,csid,tid,qqcode,nickname,sex') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_type = M('QqonlineType') -> field('id,name') -> select();
    $this -> assign('result_type', $result_type);

    $this -> display();
  }

  public function servicecontent(){
    $ServiceContent = M('ServiceContent');
    $where['pid'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $ServiceContent -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $ServiceContent -> field('id,name,sort,remark,url') -> where($where) -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addservicecontent(){
    $ServiceContent = M('ServiceContent');
    if(!empty($_POST['name'])){
      if(!$ServiceContent -> create()){
	$this -> error($ServiceContent -> getError());
      }
      if($ServiceContent -> add()){
	S('index_service_content', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $pname = $ServiceContent -> getFieldByid($this -> _get('id', 'intval'), 'name');
    $this -> assign('pname', $pname);
    $this -> display();
  }

  public function delservicecontent(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $ServiceContent = M('ServiceContent');
    if($ServiceContent -> where($where_del) -> delete()){
      S('index_service_content', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editservicecontent(){
    $ServiceContent = M('ServiceContent');
    if(!empty($_POST['name'])){
      if(!$ServiceContent -> create()){
	$this -> error($ServiceContent -> getError());
      }
      if($ServiceContent -> save()){
	S('index_service_content', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $ServiceContent -> field('name,pid,sort,remark,url') -> find($this -> _get('id', 'intval'));
    $pname = $ServiceContent -> getFieldByid($result['pid'], 'name');
    $this -> assign('pname', $pname);
    $this -> assign('result', $result);
    $this -> display();
  }

  public function advertiseset(){
    if(!empty($_REQUEST['csid'])){
      $advertisepage = M('AdvertisePage');
      $where = array();

      $where['csid'] = $this -> _request('csid', 'intval');
      $count = $advertisepage -> where($where) -> count('id');
      import('ORG.Util.Page');
      if(! empty ( $_REQUEST ['listRows'] )){
	$listRows = $_REQUEST ['listRows'];
      } else {
	$listRows = 15;
      }
      $page = new Page($count, $listRows);
      $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
      $page -> firstRow = ($pageNum - 1) * $listRows;

      $result = $advertisepage -> field('id,module_name,action_name,remark') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
      $this -> assign('result', $result);

      $this -> assign('listRows', $listRows);
      $this -> assign('currentPage', $pageNum);
      $this -> assign('count', $count);
    }
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> editadvertiseinfo($_POST['pid']);
  }

  public function addadvertisepage(){
    if(!empty($_POST['csid'])){
      $advertisepage = M('AdvertisePage');
      if(!$advertisepage -> create()){
	$this -> error($advertisepage -> getError());
      }
      if($advertisepage -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  
  }

  public function deladvertisepage(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $advertisepage = M('AdvertisePage');
    if($advertisepage -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editadvertisepage(){
    $advertisepage = M('AdvertisePage');
    if(!empty($_POST['csid'])){
      if(!$advertisepage -> create()){
	$this -> error($advertisepage -> getError());
      }
      if($advertisepage -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $advertisepage -> field('csid,module_name,action_name,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  public function editadvertiseinfo($pid){
    $pid = !empty($pid) ? $pid : $_GET['pid'];
    if(!empty($pid)){
      $advertise = M('Advertise');
      $where_ad = array();
      $where_ad['pid'] = $pid;
      $ad_result = $advertise -> field('id,name,namenote,width,height,isopen,address') -> order('namenote') -> where($where_ad) -> select();
      $this -> assign('ad_result', $ad_result);
    }
    $this -> display();
  }

  public function addeditadvertiseinfo(){
    if(!empty($_POST['name'])){
      $advertise = M('Advertise');
      if(!$advertise -> create()){
	$this -> error($advertise -> getError());
      }
      if(!empty($_FILES['address']['name'])){
	$advertise -> address = $this -> ad_upload();
      }
      if($advertise -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      } 
    }
    $this -> display();
  }

  public function deleditadvertiseinfo(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $advertise = M('Advertise');
    if($advertise -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editeditadvertiseinfo(){
    $advertise = M('Advertise');
    if(!empty($_POST['name'])){
      if(!$advertise -> create()){
	$this -> error($advertise -> getError());
      }
      if(!empty($_FILES['address']['name'])){
	$advertise -> address = $this -> ad_upload();
      }
      if($advertise -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $advertise -> field('name,namenote,width,height,link,isopen') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function editseeadimage(){
    $advertise = M('Advertise');
    $result = $advertise -> field('address,width,height') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function applylink(){
    $applylink = M('LinkApply');
    $where = array();
    if(!empty($_POST['name'])){
      $where['la.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $applylink -> table('yesow_link_apply as la') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $applylink -> table('yesow_link_apply as la') -> field('la.id,la.linktype,cs.name as csname,lwt.name as lwtname,la.name,la.website,la.linkman,la.tel,la.email,la.addtime,la.ischeck') -> join('yesow_child_site as cs ON la.csid = cs.id') -> join('yesow_link_website_type as lwt ON la.tid = lwt.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('la.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editapplylink(){
    $applylink = M('LinkApply');
    if(!empty($_POST['name'])){
      if(!$applylink -> create()){
	$this -> error($applylink -> getError());
      }
      if($applylink -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $applylink -> field('csid,tid,linktype,name,website,logo,linkman,tel,email,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $LinkWebsiteType = M('LinkWebsiteType');
    $result_website_type = $LinkWebsiteType -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_website_type', $result_website_type);
    $this -> display();
  }

  public function delapplylink(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $applylink = M('LinkApply');
    if($applylink -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function passauditapplylink(){
    $applylink = M('LinkApply');
    $link = M('Link');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($applylink -> where($where_audit) -> save($data_audit)){
      $id_arr = explode(',', $this -> _post('ids'));
      foreach($id_arr as $value){
	$insert_data = $applylink -> field('csid,tid,linktype,name,website,logo') -> find($value);
	$link -> add($insert_data);
      }
      S('index_friend_link', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditapplylink(){
    $applylink = M('LinkApply');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($applylink -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function websitetype(){
    $LinkWebsiteType = M('LinkWebsiteType');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $LinkWebsiteType -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $LinkWebsiteType -> field('id,name,sort,remark') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addwebsitetype(){
    if(!empty($_POST['name'])){
      $LinkWebsiteType = M('LinkWebsiteType');
      if(!$LinkWebsiteType -> create()){
	$this -> error($LinkWebsiteType -> getError());
      }
      if($LinkWebsiteType -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function editwebsitetype(){
    $LinkWebsiteType = M('LinkWebsiteType');
    if(!empty($_POST['name'])){
      if(!$LinkWebsiteType -> create()){
	$this -> error($LinkWebsiteType -> getError());
      }
      if($LinkWebsiteType -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $LinkWebsiteType -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function delwebsitetype(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $LinkWebsiteType = M('LinkWebsiteType');
    if($LinkWebsiteType -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function friendlink(){
    $link = M('Link');
    $where = array();
    if(!empty($_POST['name'])){
      $where['l.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $link -> table('yesow_link as l') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $link -> table('yesow_link as l') -> field('l.id,l.name,cs.name as csname,t.name as tname,l.linktype,l.website,l.sort') -> join('yesow_child_site as cs ON l.csid = cs.id') -> join('yesow_link_website_type as t ON l.tid = t.id') -> order('l.csid DESC,l.sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addfriendlink(){
    if(!empty($_POST['name'])){
      $link = M('Link');
      if(!$link -> create()){
	$this -> error($link -> getError());
      }
      if($link -> add()){
	S('index_friend_link', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $LinkWebsiteType = M('LinkWebsiteType');
    $result_website_type = $LinkWebsiteType -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_website_type', $result_website_type);
    $this -> display();
  }

  public function delfriendlink(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $link = M('Link');
    if($link -> where($where_del) -> delete()){
      S('index_friend_link', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editfriendlink(){
    $link = M('Link');
    if(!empty($_POST['name'])){
      if(!$link -> create()){
	$this -> error($link -> getError());
      }
      if($link -> save()){
	S('index_friend_link', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $link -> field('csid,tid,linktype,name,website,logo,sort') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $LinkWebsiteType = M('LinkWebsiteType');
    $result_website_type = $LinkWebsiteType -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_website_type', $result_website_type);

    $this -> display();
  }

  //分站电话管理
  public function childsitephone(){
    $ChildSitePhone = M('ChildSitePhone');
    $where = array();
    if(!empty($_REQUEST['tel'])){
      $where['p.tel'] = array('LIKE', '%' .  $_REQUEST['tel'] . '%');
    }
    $count = $ChildSitePhone -> alias('p') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $ChildSitePhone -> alias('p') -> field('p.id,p.tel,p.addtime,cs.name as csname,p.remark') -> join('yesow_child_site as cs ON p.cid = cs.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('addtime DESC') -> select();
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

  //添加分站电话
  public function addchildsitephone(){
    if(isset($_POST['tel'])){
      $ChildSitePhone = D('ChildSitePhone');
      if(!$ChildSitePhone -> create()){
	$this -> error($ChildSitePhone -> getError());
      }
      $ChildSitePhone -> addtime = time();
      if($ChildSitePhone -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $ChildSite = M('ChildSite');
    $result_childsite = $ChildSite -> field('id,name') -> order('id ASC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  //删除分站电话
  public function delchildsitephone(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $ChildSitePhone = M('ChildSitePhone');
    if($ChildSitePhone -> where($where_del) -> delete()){
      S('index_friend_link', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑分站电话
  public function editchildsitephone(){
    $ChildSitePhone = M('ChildSitePhone');
    if(!empty($_POST['tel'])){
      if(!$ChildSitePhone -> create()){
	$this -> error($ChildSitePhone -> getError());
      }
      if($ChildSitePhone -> save()){
	S('index_friend_link', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $ChildSitePhone -> field('id,cid,tel,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

}

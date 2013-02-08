<?php
class CompanyAction extends CommonAction {

  public function upload(){//文件上传
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('COMPANY_PIC_PATH') ;//设置上传目录
    $upload -> autoSub = false;//设置使用子目录保存上传文件
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info[0]['savename'];
    }else{
      return $upload;
    }
  }

  /* --------------- 速查数据管理 ---------------- */

  //速查主营类别
  public function companycategory(){
    $category = M('CompanyCategory');
    $where['pid'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    //处理搜索
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $category -> where($where) -> count('id');
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
    $result = $category -> field('id,name,sort,remark') -> where($where) -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  //添加速查主营类别
  public function addcompanycategory(){
    $category = D('CompanyCategory');
    //处理添加
    if(!empty($_POST['name'])){
      if(!$category -> create()){
	$this -> error($category -> getError());
      }
      if($category -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查上级分类
    $pname = $category -> getFieldByid($this -> _get('id', 'intval'), 'name');
    $this -> assign('pname', $pname);
    $this -> display();
  }

  //删除速查主营类别
  public function delcompanycategory(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $category = D('CompanyCategory');
    if($category -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑速查主营类别
  public function editcompanycategory(){
    $category = D('CompanyCategory');
    if(!empty($_POST['name'])){
      if(!$category -> create()){
	$this -> error($category -> getError());
      }
      if($category -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $category -> field('name,pid,sort,remark') -> find($this -> _get('id', 'intval'));
    $pname = $category -> getFieldByid($result['pid'], 'name');
    $this -> assign('pname', $pname);
    $this -> assign('result', $result);
    $this -> display();
  }

  //速查未审数据
  public function notcheckcompany(){
    $companyaudit = M('CompanyAudit');
    $where = array();
    $where['type'] = '添加';
    //处理搜索
    if(!empty($_POST['search_name'])){
      if($_POST['search_key'] == 'name'){
	$where['name'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'address'){
	$where['address'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'companyphone'){
	$where['companyphone'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'website'){
	$where['website'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'category'){
	$ccid = M('CompanyCategory') -> getFieldByname($this -> _post('search_name'), 'id');
	$where['ccid'] = $ccid;
      }else if($_POST['search_key'] == 'addname'){
	$where['addname'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }
    }
    if(!empty($_POST['search_starttime'])){
      $addtime = $this -> _post('search_starttime', 'strtotime');
      $where['jointime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['search_endtime'])){
      $endtime = $this -> _post('search_endtime', 'strtotime');
      $where['jointime'][] = array('lt', $endtime);
    }
    if(!empty($_POST['search_csid'])){
      $where['csid'] = $this -> _post('search_csid', 'intval');
    }
    if(!empty($_POST['search_csaid'])){
      $where['csaid'] = $this -> _post('search_csaid', 'intval');
    }

    //记录总数
    $count = $companyaudit -> where($where) -> count('id');
    //未审数据
    $where_noaudit = $where;
    $where_noaudit['ischeck'] = 0;
    $notauditcount = $companyaudit -> where($where_noaudit) -> count('id');
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
    $result = $companyaudit -> field('id,name,ischeck') -> where($where) -> order('ischeck ASC, jointime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('notauditcount', $notauditcount);

    //默认右侧显示第一个公司的信息
    R('Company/editnotcheckcompany', array($result[0]['id']));
  }


  //通过审核速查未审数据
  public function editnotcheckcompany($id=''){
    $companyaudit = D('CompanyAudit');
    //处理更新
    if(!empty($_POST['name'])){
      //更新未审表
      $data['id'] = $_POST['id'];
      $data['ischeck'] = 1;
      if(!empty($_FILES['image']['name'])){
	$data['pic'] = $this -> upload();
      }
      $companyaudit -> save($data);
      unset($_POST['id']);
      //向已审表插入数据
      $company = D('Company');
      $_POST['auditaid'] = session(C('USER_AUTH_KEY'));
      if(!$company -> create()){
	$this -> error($company -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	$company -> pic = $data['pic'];
      }
      if($company -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
        $this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $id = !empty($id) ? $id : $this -> _get('id', 'intval');
    $this -> assign('id', $id);
    //结果
    $result_edit = $companyaudit -> field('name,address,manproducts,companyphone,mobilephone,linkman,email,qqcode,csid,csaid,typeid,ccid,website,keyword,content,addname,addtel,addunit,addaddress,addemail,addnumberid,jointime') -> find($id);
    $this -> assign('result_edit', $result_edit);
    //根据csid 查分站下地区列表
    $result_childsite_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result_edit['csid'])) -> select();
    $this -> assign('result_childsite_area', $result_childsite_area);
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询公司类型
    $result_company_type = M('CompanyType') -> field('id,name') -> select();
    $this -> assign('result_company_type', $result_company_type);
    //查询ccid对应的主营一级类别
    $result_ccid_one = M('CompanyCategory') -> getFieldByid($result_edit['ccid'], 'pid');
    $this -> assign('result_ccid_one', $result_ccid_one);
    //查询对应主营类别 - 二级
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result_ccid_one)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    $this -> display();
  }

  //删除速查未审数据
  public function delnotcheckcompany(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $companyaudit = M('CompanyAudit');
    if($companyaudit -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //改错未审数据
  public function changenotcheckcompany(){
    $companyaudit = M('CompanyAudit');
    $where = array();
    $where['type'] = '改错';
    //处理搜索
    if(!empty($_POST['search_name'])){
      if($_POST['search_key'] == 'name'){
	$where['name'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'address'){
	$where['address'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'companyphone'){
	$where['companyphone'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'website'){
	$where['website'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'category'){
	$ccid = M('CompanyCategory') -> getFieldByname($this -> _post('search_name'), 'id');
	$where['ccid'] = $ccid;
      }else if($_POST['search_key'] == 'addname'){
	$where['addname'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }
    }
    if(!empty($_POST['search_starttime'])){
      $addtime = $this -> _post('search_starttime', 'strtotime');
      $where['jointime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['search_endtime'])){
      $endtime = $this -> _post('search_endtime', 'strtotime');
      $where['jointime'][] = array('lt', $endtime);
    }
    if(!empty($_POST['search_csid'])){
      $where['csid'] = $this -> _post('search_csid', 'intval');
    }
    if(!empty($_POST['search_csaid'])){
      $where['csaid'] = $this -> _post('search_csaid', 'intval');
    }

    //记录总数
    $count = $companyaudit -> where($where) -> count('id');
    //未审数据
    $where_noaudit = $where;
    $where_noaudit['ischeck'] = 0;
    $notauditcount = $companyaudit -> where($where_noaudit) -> count('id');
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
    $result = $companyaudit -> field('id,name,ischeck') -> where($where) -> order('ischeck ASC, jointime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('notauditcount', $notauditcount);

    //默认右侧显示第一个公司的信息
    R('Company/editchangenotcheckcompany', array($result[0]['id']));
  }

  //通过审核改错未审数据
  public function editchangenotcheckcompany($id=''){
    $companyaudit = D('CompanyAudit');
    //处理更新
    if(!empty($_POST['name'])){
      //更新未审表
      $data['id'] = $_POST['id'];
      $data['ischeck'] = 1;
      if(!empty($_FILES['image']['name'])){
	$data['pic'] = $this -> upload();
      }
      $companyaudit -> save($data);
      $_POST['id'] = $companyaudit -> getFieldByid($this -> _post('id', 'intval'), 'cid');
      //更新已审表
      $company = D('Company');
      $_POST['updateaid'] = session(C('USER_AUTH_KEY'));
      if(!$company -> create()){
	$this -> error($company -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	$company -> pic = $data['pic'];
      }
      if($company -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $id = !empty($id) ? $id : $this -> _get('id', 'intval');
    $this -> assign('id', $id);
    //结果
    $result_edit = $companyaudit -> field('name,address,manproducts,companyphone,mobilephone,linkman,email,qqcode,csid,csaid,typeid,ccid,website,keyword,content,addname,addtel,addunit,addaddress,addemail,addnumberid,jointime,cid') -> find($id);
    $this -> assign('result_edit', $result_edit);
    //根据cid，查询原公司信息
    $company = M('Company');
    $result_old_company = $company -> table('yesow_company as c') -> field('c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,c.csid, cs.name as csname, c.csaid, csa.name as csaname, c.typeid, ct.name as ctname, c.ccid, cc.name as ccname,c.website,c.keyword,c.content') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_type as ct ON c.typeid = ct.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> where(array('c.id' => $result_edit['cid'])) -> find();
    $this -> assign('result_old_company',$result_old_company);
    //查原公司的ccid的pid
    $temp_pid = M('CompanyCategory') -> getFieldByid($result_old_company['ccid'], 'pid');
    $this -> assign('result_old_ccid_pid', $temp_pid);
    //pid的name
    $this -> assign('result_old_ccid_pic_name', M('CompanyCategory') -> getFieldByid($temp_pid, 'name'));
    //根据csid 查分站下地区列表
    $result_childsite_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result_edit['csid'])) -> select();
    $this -> assign('result_childsite_area', $result_childsite_area);
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询公司类型
    $result_company_type = M('CompanyType') -> field('id,name') -> select();
    $this -> assign('result_company_type', $result_company_type);
    //查询ccid对应的主营一级类别
    $result_ccid_one = M('CompanyCategory') -> getFieldByid($result_edit['ccid'], 'pid');
    $this -> assign('result_ccid_one', $result_ccid_one);
    //查询对应主营类别 - 二级
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result_ccid_one)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    $this -> display();
  }

  //删除改错未审数据
  public function delchangenotcheckcompany(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $companyaudit = M('CompanyAudit');
    if($companyaudit -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //会员报错数据
  public function reporterrorcompany(){
    $report = M('CompanyReport');
    $where = array();
    //处理搜索
    if(!empty($_POST['name'])){
      if($_POST['key'] == 'name'){
	$where['p.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
      }else if($_POST['key'] == 'address'){
	$where['p.address'] = array('LIKE', '%' . $this -> _post('name') . '%');
      }else if($_POST['key'] == 'companyphone'){
	$where['p.companyphone'] = array('LIKE', '%' . $this -> _post('name') . '%');
      }else if($_POST['key'] == 'website'){
	$where['p.website'] = array('LIKE', '%' . $this -> _post('name') . '%');
      }else if($_POST['key'] == 'category'){
	$ccid = M('CompanyCategory') -> getFieldByname($this -> _post('name'), 'id');
	$where['p.ccid'] = $ccid;
      }else if($_POST['key'] == 'auditname'){
	$auditaid = M('Admin') -> getFieldByname($this -> _post('name'), 'id');
	$where['cr.auditid'] = $auditaid;
      }else if($_POST['key'] == 'reportname'){
	$auditaid = M('Member') -> getFieldByname($this -> _post('name'), 'id');
	$where['cr.mid'] = $auditaid;
      }
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['cr.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['cr.addtime'][] = array('lt', $endtime);
    }
    if(!empty($_POST['csid'])){
      $where['p.csid'] = $this -> _post('csid', 'intval');
    }
    if(!empty($_POST['csaid'])){
      $where['p.csaid'] = $this -> _post('csaid', 'intval');
    }

    //记录总数
    $count = $report -> table('yesow_company_report as cr') -> join('yesow_company as p ON cr.cid = p.id') -> where($where) -> count('cr.id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);

    //数据
    $result = $report -> table('yesow_company_report as cr') -> field('cr.id,cr.cid,p.name as pname,cet.name as cetname,cr.description,m.name as mname,cr.addtime,a.name as aname,cr.audittime,cr.status') -> where($where) -> order('cr.status ASC,cr.addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> join('yesow_company as p ON cr.cid = p.id') -> join('yesow_company_error_type as cet ON cr.cetid = cet.id') -> join('yesow_member as m ON cr.mid = m.id') -> join('yesow_admin as a ON cr.auditid = a.id') -> select();
    $this -> assign('result', $result);
    //查分站信息
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);

    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //编辑会员报错
  public function editreporterrorcompany(){
    $company = D('Company');
    //处理更新
    if(!empty($_POST['name'])){
      if(!$company -> create()){
	$this -> error($company -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	$company -> pic = $this -> upload();
      }
      $company -> updateaid = session(C('USER_AUTH_KEY'));
      if($company -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //结果
    $result = $company -> field('name,address,manproducts,companyphone,mobilephone,linkman,email,qqcode,csid,csaid,typeid,ccid,website,clickcount,keyword,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //根据csid 查分站下地区列表
    $result_childsite_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> select();
    $this -> assign('result_childsite_area', $result_childsite_area);
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询公司类型
    $result_company_type = M('CompanyType') -> field('id,name') -> select();
    $this -> assign('result_company_type', $result_company_type);
    //查询ccid对应的主营一级类别
    $result_ccid_one = M('CompanyCategory') -> getFieldByid($result['ccid'], 'pid');
    $this -> assign('result_ccid_one', $result_ccid_one);
    //查询对应主营类别 - 二级
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result_ccid_one)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    $this -> display();
  }

  //删除会员报错
  public function delreporterrorcompany(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $companyreport = M('CompanyReport');
    if($companyreport -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //通过审核会员报错
  public function passauditreporterrorcompany(){
    $companyreport = M('CompanyReport');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));
    $where_audit['status'] = 0;
    $data_audit = array();
    $data_audit['status'] = 2;
    $data_audit['auditid'] = session(C('USER_AUTH_KEY'));
    $data_audit['audittime'] = time();
    if($companyreport -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //不通过审核会员报错
  public function nopassauditreporterrorcompany(){
    $companyreport = M('CompanyReport');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));
    $where_audit['status'] = 0;  
    $data_audit = array();
    $data_audit['status'] = 1;
    $data_audit['auditid'] = session(C('USER_AUTH_KEY'));
    $data_audit['audittime'] = time();
    if($companyreport -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //速查已审数据
  public function checkcompany(){
    $company = M('Company');
    $where = array();
    if($_SESSION[C('USER_AUTH_KEY')] != 1){
      $where['delaid'] = array('exp', 'IS NULL');
    }
    //处理搜索
    if(!empty($_POST['search_name'])){
      if($_POST['search_key'] == 'name'){
	$where['name'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'address'){
	$where['address'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'companyphone'){
	$where['companyphone'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'website'){
	$where['website'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'category'){
	$ccid = M('CompanyCategory') -> getFieldByname($this -> _post('search_name'), 'id');
	$where['ccid'] = $ccid;
      }else if($_POST['search_key'] == 'auditname'){
	$auditaid = M('Admin') -> getFieldByname($this -> _post('search_name'), 'id');
	$where['auditaid'] = $auditaid;
      }else if($_POST['search_key'] == 'updatename'){
	$auditaid = M('Admin') -> getFieldByname($this -> _post('search_name'), 'id');
	$where['updateaid'] = $auditaid;
      }
    }
    if(!empty($_POST['search_starttime'])){
      $addtime = $this -> _post('search_starttime', 'strtotime');
      $where['addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['search_endtime'])){
      $endtime = $this -> _post('search_endtime', 'strtotime');
      $where['addtime'][] = array('lt', $endtime);
    }
    if(!empty($_POST['search_csid'])){
      $where['csid'] = $this -> _post('search_csid', 'intval');
    }
    if(!empty($_POST['search_csaid'])){
      $where['csaid'] = $this -> _post('search_csaid', 'intval');
    }


    //记录总数
    $count = $company -> where($where) -> count('id');
    $where_del = $where;
    $where_del['delaid'] = array('neq', 0);
    //已删数据
    $count_del = $company -> where($where_del) -> count('id');
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
    $result = $company -> field('id,delaid,name') -> where($where) -> order('delaid DESC,addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    //总页数
    $this -> assign('countNum', ceil($count / $listRows));
    $this -> assign('count', $count);
    $this -> assign('count_del', $count_del);

    //默认右侧显示第一个公司的信息
    R('Company/editcheckcompany', array($result[0]['id']));
  
  }

  //编辑速查已审数据
  public function editcheckcompany($id=''){
    $company = D('Company');
    //处理更新
    if(!empty($_POST['name'])){
      if(!$company -> create()){
	$this -> error($company -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	$company -> pic = $this -> upload();
      }
      $company -> updateaid = session(C('USER_AUTH_KEY'));
      $company -> updatetime = time();
      if($company -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $id = !empty($id) ? $id : $this -> _get('id', 'intval');
    $this -> assign('id', $id);
    //结果
    $result_edit = $company -> table('yesow_company as c') -> field('c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,c.csid,c.csaid,c.typeid,c.ccid,c.website,c.keyword,c.content,c.clickcount,c.addtime,c.updatetime,a.name as auditname,a2.name as updatename,a3.name as delname') -> join('yesow_admin as a ON c.auditaid = a.id') -> join('yesow_admin as a2 ON c.updateaid = a2.id') -> join('yesow_admin as a3 ON c.delaid = a3.id') -> where(array('c.id' => $id)) -> find();
    $this -> assign('result_edit', $result_edit);
    //根据csid 查分站下地区列表
    $result_childsite_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result_edit['csid'])) -> select();
    $this -> assign('result_childsite_area', $result_childsite_area);
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询公司类型
    $result_company_type = M('CompanyType') -> field('id,name') -> select();
    $this -> assign('result_company_type', $result_company_type);
    //查询ccid对应的主营一级类别
    $result_ccid_one = M('CompanyCategory') -> getFieldByid($result_edit['ccid'], 'pid');
    $this -> assign('result_ccid_one', $result_ccid_one);
    //查询对应主营类别 - 二级
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result_ccid_one)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    $this -> display();
  }

  //删除速查已审数据
  public function delcheckcompany(){
    //功能说明：如果是总管理(id=1)则直接删除数据，否则只是将delaid设为删除人的id，实际并不删除
    if($_SESSION[C('USER_AUTH_KEY')] == 1){
      $where_del = array();
      $where_del['id'] = array('in', $_POST['ids']);
      $company = M('Company');
      if($company -> where($where_del) -> delete()){
	$this -> success(L('DATA_DELETE_SUCCESS'));
      }else{
	$this -> error(L('DATA_DELETE_ERROR'));
      }
    }else{
      $where_update = array();
      $where_update['id'] = array('in', $_POST['ids']);
      $data['delaid'] = session(C('USER_AUTH_KEY'));
      $company = D('Company');
      if($company -> where($where_update) -> save($data)){
	$this -> success(L('DATA_DELETE_SUCCESS'));
      }else{
	$this -> error(L('DATA_DELETE_ERROR'));
      }
    }
  }

  //速查评论管理
  public function companycomment(){
    $comapnycomment = M('CompanyComment');
    $where = array();
    //处理搜索
    if(!empty($_POST['content'])){
      $where['cc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
    }
    if(!empty($_POST['author'])){
      $member = M('Member');
      $authorid = $member -> getFieldByname($this -> _post('author'), 'id');
      $where['cc.mid'] = intval($authorid);
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['cc.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['cc.addtime'][] = array('lt', $endtime);
    }

    //记录总数
    $count = $comapnycomment -> table('yesow_company_comment as cc') -> where($where) -> count('id');
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
    $result = $comapnycomment -> table('yesow_company_comment as cc') -> field('cc.id,cc.cid,c.name as cname,cc.floor,cc.score,cc.content,m.name as mname,cc.addtime,cc.status') -> where($where) -> order('cc.status ASC,cc.addtime DESC') -> join('yesow_company as c ON cc.cid = c.id') -> join('yesow_member as m ON cc.mid = m.id') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //编辑速查评论
  public function editcompanycomment(){
    $comment = D('index://CompanyComment');
    //处理更新
    if(!empty($_POST['floor'])){
      if(!$comment -> create()){
	$this -> error($comment -> getError());
      }
      if($comment -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $comment -> table('yesow_company_comment as cc') -> field('c.name as cname,m.name as mname,cc.floor,cc.score,cc.content') -> join('yesow_company as c ON cc.cid = c.id') -> join('yesow_member as m ON cc.mid = m.id') -> where(array('cc.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  //删除速查评论
  public function delcompanycomment(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $comapnycomment = M('CompanyComment');
    if($comapnycomment -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      dump($comapnycomment -> getLastSql());
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //通过审核评论
  public function passauditcompanycomment(){
    $comment = M('CompanyComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 2);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //不通过审核评论
  public function nopassauditcompanycomment(){
    $comment = M('CompanyComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 1);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }
  /* --------------- 速查数据管理 ---------------- */
}

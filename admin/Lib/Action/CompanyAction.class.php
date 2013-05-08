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

  //查看速查形象图片
  public function editcompanypic(){
    if($_GET['type'] == 'notcheck'){
      $image = M('CompanyAudit') -> getFieldByid($this -> _get('id', 'intval'), 'pic');
    }else if($_GET['type'] == 'check'){
      $image = M('Company') -> getFieldByid($this -> _get('id', 'intval'), 'pic');
    }
    $this -> assign('image', $image);
    $this -> display();
  }

  //删除速查形象图片
  public function delcompanypic(){
    $where['id'] = $this -> _get('id', 'intval');
    $data['pic'] = '';
    if($_GET['type'] == 'notcheck'){
      M('CompanyAudit') -> where($where) -> save($data);
    }else if($_GET['type'] == 'check'){
      M('Company') -> where($where) -> save($data);
    }
    $this -> success(L('DATA_DELETE_SUCCESS'));
  }

  //速查未审数据
  public function notcheckcompany(){
    $companyaudit = M('CompanyAudit');
    $where = array();
    //如果不是总管理员，不能查看不是自己所属分站的数据
    if($_SESSION[C('USER_AUTH_KEY')] != 1){
      //查询所属分站的域名
      $domain = M('ChildSite') -> getFieldByid($_SESSION['csid'], 'domain');
      $this -> assign('domain', $domain);
      //如果域名不是主站，则过滤掉其他分站的数据
      if($domain != 'yesow.com'){
	$where['csid'] = $_SESSION['csid'];
	//分配分站名
	$child_name = M('ChildSite') -> getFieldByid($_SESSION['csid'], 'name');
	$this -> assign('child_name', $child_name);
	//查当前分站下的地区
	$noadmin_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $_SESSION['csid'])) -> select();
	$this -> assign('noadmin_area', $noadmin_area);
      }
    }
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
	$where['manproducts'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
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
      $_POST['auditaid'] = session('admin_name');
      if(!$company -> create()){
	$this -> error($company -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	$company -> pic = $data['pic'];
      }
      if($cid = $company -> add()){
	//审核成功,则用户增加相应的RMB金额
	  //先查询应增加的金额
	$add_rmb = M('CompanySetup') -> getFieldByname('addsuccess', 'value');
	if($add_rmb > 0){
	  //查询公司名称
	  $cname = $company -> getFieldByid($cid, 'name');
	  $cname = msubstr($cname, 0, 6);
	  //查询报错用户id
	  $mid = $companyaudit -> getFieldByid($data['id'], 'mid');
	  //再向用户表中增加相应金额	  
	  D('member://MemberRmb') -> where(array('mid' => $mid)) -> setInc('rmb_exchange', $add_rmb);
	  //写RMB消费日志
	  D('member://MemberRmbDetail') -> writelog($mid, "添加一条完整的企业信息审核通过[<span style='color:blue;'>{$cname}</span>]", '获取', $add_rmb);
	}	
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
        $this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $id = !empty($id) ? $id : $this -> _get('id', 'intval');
    $this -> assign('id', $id);
    //结果
    $result_edit = $companyaudit -> field('name,address,manproducts,companyphone,mobilephone,linkman,email,qqcode,csid,csaid,typeid,ccid,website,pic,keyword,content,addname,addtel,addunit,addaddress,addemail,addnumberid,jointime') -> find($id);
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
    //查询将要删除的未审数据，用来下面的会员扣费和消费日志
    $where_del_data = $where_del;
    $where_del_data['ischeck'] = 0;
    $del_data = $companyaudit -> field('name,mid') -> where($where_del_data) -> select();
    if($companyaudit -> where($where_del) -> delete()){
      //未通过审核,则用户减少相应的RMB金额
	  //先查询应增加的金额
	$del_rmb = M('CompanySetup') -> getFieldByname('adderror', 'value');
	if($del_rmb > 0){
	  foreach($del_data as $value){
	    //公司名称
	    $cname = $value['name'];
	    $cname = msubstr($cname, 0, 6);
	    //报错用户id
	    $mid = $value['mid'];
	    //再向用户表中减少相应金额
	    D('member://MemberRmb') -> where(array('mid' => $mid)) -> setDec('rmb_exchange', $del_rmb);
	    //写RMB消费日志
	    D('member://MemberRmbDetail') -> writelog($mid, "添加一条完整的企业信息未通过审核[<span style='color:blue;'>{$cname}</span>]", '扣除', '-' . $del_rmb);
	  }  
	}
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //改错未审数据
  public function changenotcheckcompany(){
    $companyaudit = M('CompanyAudit');
    $where = array();
    //如果不是总管理员，不能查看不是自己所属分站的数据
    if($_SESSION[C('USER_AUTH_KEY')] != 1){
      //查询所属分站的域名
      $domain = M('ChildSite') -> getFieldByid($_SESSION['csid'], 'domain');
      $this -> assign('domain', $domain);
      //如果域名不是主站，则过滤掉其他分站的数据
      if($domain != 'yesow.com'){
	$where['csid'] = $_SESSION['csid'];
	//分配分站名
	$child_name = M('ChildSite') -> getFieldByid($_SESSION['csid'], 'name');
	$this -> assign('child_name', $child_name);
	//查当前分站下的地区
	$noadmin_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $_SESSION['csid'])) -> select();
	$this -> assign('noadmin_area', $noadmin_area);
      }
    }
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
	$where['manproducts'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
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
      $_POST['updateaid'] = session('admin_name');
      if(!$company -> create()){
	$this -> error($company -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	$company -> pic = $data['pic'];
      }
      if($company -> save()){
	//审核成功,则用户增加相应的RMB金额
	  //先查询应增加的金额
	$add_rmb = M('CompanySetup') -> getFieldByname('changesuccess', 'value');
	if($add_rmb > 0){
	  //查询公司名称
	  $cname = $company -> getFieldByid($_POST['id'], 'name');
	  $cname = msubstr($cname, 0, 6);
	  //查询改错用户id
	  $mid = $companyaudit -> getFieldByid($data['id'], 'mid');
	  //再向用户表中增加相应金额	  
	  D('member://MemberRmb') -> where(array('mid' => $mid)) -> setInc('rmb_exchange', $add_rmb);
	  //写RMB消费日志
	  D('member://MemberRmbDetail') -> writelog($mid, "改错一条信息审核通过[<span style='color:blue;'>{$cname}</span>]", '获取', $add_rmb);
	}
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $id = !empty($id) ? $id : $this -> _get('id', 'intval');
    $this -> assign('id', $id);
    //结果
    $result_edit = $companyaudit -> field('name,address,manproducts,companyphone,mobilephone,linkman,email,pic,qqcode,csid,csaid,typeid,ccid,website,keyword,content,addname,addtel,addunit,addaddress,addemail,addnumberid,jointime,cid') -> find($id);
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
    //查询将要删除的未审数据，用来下面的会员扣费和消费日志
    $where_del_data = $where_del;
    $where_del_data['ischeck'] = 0;
    $del_data = $companyaudit -> field('name,mid') -> where($where_del_data) -> select();
    if($companyaudit -> where($where_del) -> delete()){
      //未通过审核,则用户减少相应的RMB金额
	  //先查询应增加的金额
	$del_rmb = M('CompanySetup') -> getFieldByname('changeerror', 'value');
	if($del_rmb > 0){
	  foreach($del_data as $value){
	    //公司名称
	    $cname = $value['name'];
	    $cname = msubstr($cname, 0, 6);
	    //报错用户id
	    $mid = $value['mid'];
	    //再向用户表中减少相应金额
	    D('member://MemberRmb') -> where(array('mid' => $mid)) -> setDec('rmb_exchange', $del_rmb);
	    //写RMB消费日志
	    D('member://MemberRmbDetail') -> writelog($mid, "改错一条信息未审核通过[<span style='color:blue;'>{$cname}</span>]", '扣除', '-' . $del_rmb);
	  }  
	}
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
	$where['p.manproducts'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['key'] == 'auditname'){
	$where['cr.auditid'] = array('LIKE', '%' . $this -> _post('name') . '%');
      }else if($_POST['key'] == 'reportname'){
	$where['cr.mid'] = array('LIKE', '%' . $this -> _post('name') . '%');
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
      //审核成功,则用户增加相应的RMB金额
	  //先查询应增加的金额
	$add_rmb = M('CompanySetup') -> getFieldByname('reportsuccess', 'value');
	if($add_rmb > 0){
	  $id_arr = explode(',', $_POST['ids']);
	  foreach($id_arr as $value){
	    //查询cid , mid
	    $info = $companyreport -> field('cid,mid') -> find($value);
	    //查询公司名称
	    $cname = M('Company') -> getFieldByid($info['cid'], 'name');
	    $cname = msubstr($cname, 0, 6);
	    //再向用户表中增加相应金额	  
	    D('member://MemberRmb') -> where(array('mid' => $info['mid'])) -> setInc('rmb_exchange', $add_rmb);
	    //写RMB消费日志
	    D('member://MemberRmbDetail') -> writelog($info['mid'], "报错一条企业信息审核通过[<span style='color:blue;'>{$cname}</span>]", '获取', $add_rmb);
	  }
	}
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
      //不通过审核,则用户扣除相应的RMB金额
	  //先查询应增加的金额
	$del_rmb = M('CompanySetup') -> getFieldByname('reporterror', 'value');
	if($del_rmb > 0){
	  $id_arr = explode(',', $_POST['ids']);
	  foreach($id_arr as $value){
	    //查询cid , mid
	    $info = $companyreport -> field('cid,mid') -> find($value);
	    //查询公司名称
	    $cname = M('Company') -> getFieldByid($info['cid'], 'name');
	    $cname = msubstr($cname, 0, 6);
	    //再向用户表中扣除相应金额	  
	    D('member://MemberRmb') -> where(array('mid' => $info['mid'])) -> setDec('rmb_exchange', $del_rmb);
	    //写RMB消费日志
	    D('member://MemberRmbDetail') -> writelog($info['mid'], "报错一条企业信息未审核通过[<span style='color:blue;'>{$cname}</span>]", '扣除', '-' . $del_rmb);
	  }
	}
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //速查已审数据
  public function checkcompany(){
    $company = M('Company');
    $where = array();
    //如果不是总管理员，不能查看已删除的数据，和不是自己所属分站的数据
    if($_SESSION[C('USER_AUTH_KEY')] != 1){
      $where['delaid'] = array('exp', 'IS NULL');
      //查询所属分站的域名
      $domain = M('ChildSite') -> getFieldByid($_SESSION['csid'], 'domain');
      $this -> assign('domain', $domain);
      //如果域名不是主站，则过滤掉其他分站的数据
      if($domain != 'yesow.com'){
	$where['csid'] = $_SESSION['csid'];
	//分配分站名
	$child_name = M('ChildSite') -> getFieldByid($_SESSION['csid'], 'name');
	$this -> assign('child_name', $child_name);
	//查当前分站下的地区
	$noadmin_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $_SESSION['csid'])) -> select();
	$this -> assign('noadmin_area', $noadmin_area);
      }
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
	$where['manproducts'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'auditname'){
	$where['auditaid'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'updatename'){
	$where['updateaid'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }
    }
    if(!empty($_POST['search_starttime'])){
      $addtime = $this -> _post('search_starttime', 'strtotime');
      $where['updatetime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['search_endtime'])){
      $endtime = $this -> _post('search_endtime', 'strtotime');
      $where['updatetime'][] = array('lt', $endtime);
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
    $where_del['delaid'] = array('neq', '');
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
    $result = $company -> field('id,delaid,name') -> where($where) -> order('delaid DESC,updatetime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
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
      $company -> updateaid = session('admin_name');
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
    $result_edit = $company -> table('yesow_company as c') -> field('c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,c.csid,c.csaid,c.typeid,c.ccid,c.website,c.pic,c.keyword,c.content,c.clickcount,c.addtime,c.updatetime,c.auditaid as auditname,c.updateaid as updatename,c.delaid as delname') -> where(array('c.id' => $id)) -> find();
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

  //恢复速查已审数据
  public function restorecompany(){
    $company = D('Company');
    $data['delaid'] = NULL;
    if($company -> where(array('id' => $this -> _get('cid', 'intval'))) -> save($data)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
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
      $data['delaid'] = session('admin_name');
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
    $result = $comapnycomment -> table('yesow_company_comment as cc') -> field('cc.id,cc.cid,c.name as cname,cc.floor,cc.score,cc.content,m.name as mname,cc.addtime,cc.status,cc.face') -> where($where) -> order('cc.status ASC,cc.addtime DESC') -> join('yesow_company as c ON cc.cid = c.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> join('yesow_member as m ON cc.mid = m.id') -> select();
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
    $result = $comment -> table('yesow_company_comment as cc') -> field('c.name as cname,m.name as mname,cc.floor,cc.score,cc.content,cc.face') -> join('yesow_company as c ON cc.cid = c.id') -> join('yesow_member as m ON cc.mid = m.id') -> where(array('cc.id' => $this -> _get('id', 'intval'))) -> find();
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


  /* --------------- 速查搜索管理 ---------------- */

  //热门搜索管理
  public function hotsearch(){
    $searchhot = M('SearchHot');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    //记录总数
    $count = $searchhot -> where($where) -> count('id');
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

    $result = $searchhot -> field('id,name,sort,addtime,remark') -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加热门搜索词
  public function addhotsearch(){
    //处理添加
    if(!empty($_POST['name'])){
      $searchhot = D('SearchHot');
      if(!$searchhot -> create()){
	$this -> error($searchhot -> getError());
      }
      if($searchhot -> add()){
	//删除缓存
	S('index_search_hot', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除热门搜索词
  public function delhotsearch(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $searchhot = M('SearchHot');
    if($searchhot -> where($where_del) -> delete()){
      //删除缓存
      S('index_search_hot', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑热门搜索词
  public function edithotsearch(){
    $searchhot = D('SearchHot');
    if(!empty($_POST['name'])){
      if(!$searchhot -> create()){
	$this -> error($searchhot -> getError());
      }
      if($searchhot -> save()){
	//删除缓存
	S('index_search_hot', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $searchhot -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();  
  }

  //已审关键词管理
  public function passkeyword(){
    $passkeyword = M('AuditSearchKeyword');
    $where = array();
    if(!empty($_POST['name'])){
      $where['ask.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    //记录总数
    $count = $passkeyword -> table('yesow_audit_search_keyword as ask') -> where($where) -> count('id');
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

    $result = $passkeyword -> table('yesow_audit_search_keyword as ask') -> field('ask.id,ask.name,ask.addtime,aska.name as askaname') -> join('yesow_audit_search_keyword_attribute as aska ON ask.aid = aska.id') -> order('addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();  
  }

  //添加已审关键词
  public function addpasskeyword(){
    //处理添加
    if(!empty($_POST['name'])){
      $passkeyword = D('AuditSearchKeyword');
      if(!$passkeyword -> create()){
	$this -> error($passkeyword -> getError());
      }
      if(empty($_POST['aid'])){
	$passkeyword -> aid = NULL;
      }
      if($passkeyword -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询关键词属性
    $keyword_attribute = M('AuditSearchKeywordAttribute');
    $result_keyword = $keyword_attribute -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_keyword', $result_keyword);
    $this -> display();  
  }

  //删除已审关键词
  public function delpasskeyword(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $passkeyword = M('AuditSearchKeyword');
    if($passkeyword -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑已审关键词
  public function editpasskeyword(){
    $passkeyword = D('AuditSearchKeyword');
    if(!empty($_POST['name'])){
      if(!$passkeyword -> create()){
	$this -> error($passkeyword -> getError());
      }
      if(empty($_POST['aid'])){
	$passkeyword -> aid = NULL;
      }
      if($passkeyword -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error($passkeyword -> getLastSql());
      }
    }
    $result = $passkeyword -> field('name,aid') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询关键词属性
    $keyword_attribute = M('AuditSearchKeywordAttribute');
    $result_keyword = $keyword_attribute -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_keyword', $result_keyword);
    $this -> display();
  }

  //搜索关键词管理
  public function searchkeyword(){
    $search_keyword = M('SearchKeyword');
    //处理搜索
    $where = array();
    if(!empty($_POST['name'])){
      $where['sk.keyword'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['sk.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['sk.addtime'][] = array('lt', $endtime);
    }

    //记录总数
    $count = $search_keyword -> table('yesow_search_keyword as sk') -> where($where) -> count('id');
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

    $result = $search_keyword -> table('yesow_search_keyword as sk') -> field('sk.id,m.name as mname,sk.ipaddress,sk.sourceaddress,sk.keyword,sk.count,sk.addtime,sk.status') -> where($where) -> join('yesow_member as m ON sk.mid = m.id') -> order('sk.addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();  
  }

  //删除搜索关键词
  public function delsearchkeyword(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $search_keyword = M('SearchKeyword');
    if($search_keyword -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //审核搜索关键词
  public function auditsearchkeyword(){
    $where_save = array();
    $where_save['id'] = array('in', $_POST['ids']);
    $search_keyword = M('SearchKeyword');
    $data['status'] = 1;
    $search_keyword -> where($where_save) -> save($data);
    //获取id数组
    $id_arr = explode(',', $_POST['ids']);
    //更新已审关键词表
    foreach($id_arr as $value){
      $audit_keyword = D('AuditSearchKeyword');
      $keyword = '';
      $keyword = $search_keyword -> getFieldByid($value, 'keyword');
      $audit_data = array();
      $audit_data['name'] = $keyword;
      if($a = $audit_keyword -> create($audit_data)){
	$audit_keyword -> add();
      }    
    }
    $this -> success(L('DATA_UPDATE_SUCCESS'));
  }

  //已审词属性管理
  public function keywordattribute(){
    $keyword_attribute = M('AuditSearchKeywordAttribute');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    //记录总数
    $count = $keyword_attribute -> where($where) -> count('id');
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
    $result = $keyword_attribute -> field('id,name,sort') -> where($where) -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加已审词属性
  public function addkeywordattribute(){
    //处理添加
    if(!empty($_POST['name'])){
      $keyword_attribute = M('AuditSearchKeywordAttribute');
      if(!$keyword_attribute -> create()){
	$this -> error($keyword_attribute -> getError());
      }
      if($keyword_attribute -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除已审词属性
  public function delkeywordattribute(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $keyword_attribute = M('AuditSearchKeywordAttribute');
    if($keyword_attribute -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑已审词属性
  public function editkeywordattribute(){
    $keyword_attribute = M('AuditSearchKeywordAttribute');
    if(!empty($_POST['name'])){
      if(!$keyword_attribute -> create()){
	$this -> error($keyword_attribute -> getError());
      }
      if($keyword_attribute -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $keyword_attribute -> field('name,sort') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //后台数据检索
  public function backgroundsearch(){
    //处理检索
    if(!empty($_POST['issearch'])){
      if(empty($_POST['company_keyword']) && empty($_POST['bgsearch_csid']) && empty($_POST['bgsearch_csaid']) && empty($_POST['bgsearch_ccid_one']) && empty($_POST['bgsearch_ccid'])){
	$this -> error(L('SEARCH_WHERE_EMPTY'));
      }
      $result = $this -> search_company($_POST['company_keyword'], 'updatetime DESC');
      $this -> assign('result', $result);
      //如果搜索的条件中二级地区id，则需要检索出当前以及地区对应的二级地区
      if(!empty($_POST['bgsearch_csaid'])){
	$where_child_site_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $_POST['bgsearch_csid'])) -> select();
	$this -> assign('where_child_site_area', $where_child_site_area);
      }
      //如果搜索条件中有二级主营，则检索出当前的二级主营类别
      if(!empty($_POST['bgsearch_ccid'])){
	$where_category = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $_POST['bgsearch_ccid_one'])) -> select();
	$this -> assign('where_category', $where_category);
      }
    }
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    $this -> display();
  }

  //后台速查搜索算法
  public function search_company($keyword, $order=false, $limit=true){
    //最终输出结果
    $result = array();
    //初始化已审关键词表
    $auditkeyword = M('AuditSearchKeyword');
    //初始化速查主表
    $company = M('Company');
    //删除关键词两边的空格
    $keyword = trim($keyword);
    //将关键词进行转码用于分词
    $keyword = iconv('UTF-8', 'GB2312', $keyword);
    //引入dede分词类库，进行搜索词分词操作
    Vendor('lib_splitword_full');
    $sp = new SplitWord();
    //分词后得到的字符串
    $keyword_str = $sp->SplitRMM($keyword);
    //将字符串转为UTF-8
    $keyword_str = iconv('GB2312', 'UTF-8', $keyword_str);
    //将关键字转回UTF-8
    $keyword = iconv('GB2312', 'UTF-8', $keyword);
    //分割分词字符串
    $keyword_arr = explode(' ', $keyword_str);
    //GC
    $sp->Clear();
    //删除关键字数组最后一个无效空元素
    unset($keyword_arr[count($keyword_arr) - 1]);
    //复制分词数组，用于后面记录搜索关键词
    $search_keyword_arr = $keyword_arr;
    //关键词过滤，剔除没有的关键词
    foreach($keyword_arr as $key => $value){
      if(!$auditkeyword -> getFieldByname($value, 'id')){
	unset($keyword_arr[$key]);      
      }    
    }
    //进行关键字排序
    if(!function_exists('keyword_sort')){
      function keyword_sort($a, $b){
	(int)$sort_a = M() -> table('yesow_audit_search_keyword as ask') -> field('aska.sort') -> join('yesow_audit_search_keyword_attribute as aska ON ask.aid = aska.id') -> where(array('ask.name' => $a)) -> find();
	(int)$sort_b = M() -> table('yesow_audit_search_keyword as ask') -> field('aska.sort') -> join('yesow_audit_search_keyword_attribute as aska ON ask.aid = aska.id') -> where(array('ask.name' => $b)) -> find();
	if($sort_a['sort'] == $sort_b['sort']){
	  return 0;
	}
	return $sort_a['sort'] < $sort_b['sort'] ? -1 : 1;
      }
    }
    
    //生成排序后数组
    usort($keyword_arr, 'keyword_sort');
    //进行分词后关键词的SQL组装
    $keyword_sql = array();
    //如果分词后的结果和关键词相同，则不需要进行子查询
    if($keyword != $keyword_arr[0]){
      foreach($keyword_arr as $value){
	$keyword_sql[] = "SELECT c.id,c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.website,c.linkman,c.email,c.qqcode,cs.name as csname,csa.name as csaname,cc.name as ccname,c.csid,c.csaid,c.ccid,c.clickcount,c.updatetime,c.addtime FROM yesow_company as c LEFT JOIN yesow_child_site as cs ON c.csid = cs.id LEFT JOIN yesow_child_site_area as csa ON c.csaid = csa.id LEFT JOIN yesow_company_category as cc ON c.ccid = cc.id WHERE ( c.name LIKE '%{$value}%' OR c.address LIKE '%{$value}%' OR c.manproducts LIKE '%{$value}%' OR c.linkman LIKE '%{$value}%' ) AND ( c.delaid is NULL )";
      }
    }
    
    //根据组装好的各SQL语句，结合主SQL语句，进行查询
    $where_select = array();
    $where_select['c.name'] = array('LIKE', '%' . $keyword . '%');
    $where_select['c.address'] = array('LIKE', '%' . $keyword . '%');
    $where_select['c.manproducts'] = array('LIKE', '%' . $keyword . '%');
    $where_select['c.mobilephone'] = array('LIKE', '%' . $keyword . '%');
    $where_select['c.email'] = array('LIKE', '%' . $keyword . '%');
    $where_select['c.linkman'] = array('LIKE', '%' . $keyword . '%');
    $where_select['c.companyphone'] = array('LIKE', '%' . $keyword . '%');
    $where_select['c.qqcode'] = array('LIKE', '%' . $keyword . '%');
    $where_select['c.website'] = array('LIKE', '%' . $keyword . '%');
    $where_select['_logic'] = 'OR';
    $map['_complex'] = $where_select;
    $map['c.delaid']  = array('exp','is NULL');
    
    //构建查询SQL
    $sql = $company -> table('yesow_company as c') -> field('c.id,c.name,c.address,c.manproducts,c.website,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,cs.name as csname,csa.name as csaname,cc.name as ccname,c.csid,c.csaid,c.ccid,c.clickcount,c.updatetime,c.addtime') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> where($map) -> union($keyword_sql) -> buildSql();

    //高级查询条件
    $senior_where = array();
    if(!empty($_POST['bgsearch_csid'])){
      $senior_where['a.csid'] = $this -> _post('bgsearch_csid', 'intval');
    }
    if(!empty($_POST['bgsearch_csaid'])){
      $senior_where['a.csaid'] = $this -> _post('bgsearch_csaid', 'intval');
    }
    if(!empty($_POST['bgsearch_ccid_one'])){
      //查询一级分类下的二级id
      $ccid_two = M('CompanyCategory') -> field('id') -> where(array('pid' => $this -> _post('bgsearch_ccid_one'))) -> select();
      $ccid_arr = array();
      //整理结果数组
      foreach($ccid_two as $value){
	$ccid_arr[] = $value['id'];
      }
      $senior_where['a.ccid'] = array('IN', $ccid_arr);
    }
    if(!empty($_POST['bgsearch_ccid'])){
      $senior_where['a.ccid'] = $this -> _post('bgsearch_ccid', 'intval');
    }

    //记录总数
    $count = $company -> table($sql . ' a') -> where($senior_where) -> count();
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
    
    //记录查询时间
    G('start');
    //查询结果
    if($limit){
      $result['result'] = $company -> table($sql . ' a') -> order($order) -> limit($page -> firstRow . ',' . $page -> listRows) -> where($senior_where) -> select();
    }else{
      $result['result'] = $company -> table($sql . ' a') -> order($order) -> where($senior_where) -> select();
    }

    //将查询时间写入结果数组
    $result['time'] = G('start', 'end');
    //将主查询词，写入关键词数组头部
    array_unshift($keyword_arr, $keyword);
    //查询关键词数组写入结果数组
    $result['keyword_arr'] = $keyword_arr;
    //查询主关键词写入结果数组
    $result['keyword'] = $keyword;
    //分词后的关键词字符串写入结果数组
    $result['keyword_str'] = $keyword_str;
    //调试信息
    $result['lastsql'] = $company -> getLastSql();
    return $result;
  }

  //搜索结果下载execl文件
  public function editdownexecl(){
    //查询数据
    $result = $this -> search_company($this -> _post('company_keyword'), 'a.updatetime DESC', false);
    //导入execl操作类
    vendor('PHPExcel/PHPExcel');
    //实例化PHPExcel类
    $objPHPExcel = new PHPExcel();
    //设置保存格式，非2007
    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
    //设置当前的sheet
    $objPHPExcel->setActiveSheetIndex(0);
    //获得当前sheet的对象
    $objActSheet=$objPHPExcel->getActiveSheet();
    //设置当前sheet的名字
    $objActSheet->setTitle('易搜后台数据导出');

    /*合并单元格*/
    $objPHPExcel->getActiveSheet()->mergeCells('A1:K1');

    /*设置表头*/
    $title='后台搜索关键词"' . $result['keyword'] . '"商家导出的数据信息';
    $objActSheet->setCellValue('A1', $title);
    $objActSheet->setCellValue('A2', '公司名称');
    $objActSheet->setCellValue('B2', '主营产品');
    $objActSheet->setCellValue('C2', '公司地址');
    $objActSheet->setCellValue('D2', '公司电话');
    $objActSheet->setCellValue('E2', '移动电话');
    $objActSheet->setCellValue('F2', '联系人');
    $objActSheet->setCellValue('G2', '电子邮件');
    $objActSheet->setCellValue('H2', 'QQ');
    $objActSheet->setCellValue('I2', '所在地');
    $objActSheet->setCellValue('J2', '主营类别');
    $objActSheet->setCellValue('K2', '更新时间');

    
    //设置水平居中
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('C2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('D2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('E2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('F2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('G')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('G2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('H')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('H2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('I')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('I2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('J')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('J2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('K')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('K2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    //设置字体
    $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(14); 
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16); 
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true); 
    $objPHPExcel->getActiveSheet()->getStyle('A2:K2')->getFont()->setBold(true);   
    $objPHPExcel->getActiveSheet()->getStyle('A2:K2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    for($i = 3; $i <= $result['count'] + 2; $i++){//循环设置
      $objActSheet->setCellValue('A'.$i, $result['result'][$i-3]['name']);//写公司名称
      $objActSheet->setCellValue('B'.$i, $result['result'][$i-3]['manproducts']);//写主营产品
      $objActSheet->setCellValue('C'.$i, $result['result'][$i-3]['address']);//写公司地址
      $objActSheet->setCellValue('D'.$i, $result['result'][$i-3]['companyphone']);//写公司电话
      $objActSheet->setCellValue('E'.$i, $result['result'][$i-3]['mobilephone']);//写移动电话
      $objActSheet->setCellValue('F'.$i, $result['result'][$i-3]['linkman']);//写联系人
      $objActSheet->setCellValue('G'.$i, $result['result'][$i-3]['email']);//写电子邮件
      $objActSheet->setCellValue('H'.$i, $result['result'][$i-3]['qqcode']);//写QQ
      $objActSheet->setCellValue('I'.$i, $result['result'][$i-3]['csname'] . '-' . $result['result'][$i-3]['csaname']);//写所在地
      $objActSheet->setCellValue('J'.$i, $result['result'][$i-3]['ccname']);//写主营类别
      $objActSheet->setCellValue('K'.$i, date('Y-m-d H:i:s', $result['result'][$i-3]['updatetime']));//写更新时间
    }
    ob_end_clean();
    //生成下载
    header("Content-Type: application/vnd.ms-excel;charset=UTF-8");
    header("Pragma: no-cache");
    header("Expires: 0");
   // header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
   // header("Content-Type:application/force-download");
    //header("Content-Type:application/vnd.ms-execl");
    //header("Content-Type:application/octet-stream");
    //header("Content-Type:application/download");
    $filename= date('YmdHis');
    header("Content-Disposition:attachment;filename=$filename" . '.xls');
    //header("Content-Transfer-Encoding:binary");
    $objWriter->save('php://output');
  }

  //搜索结果下载txt文件
  public function editdowntxt(){
    //查询数据
    $result = $this -> search_company($this -> _post('company_keyword'), 'a.updatetime DESC', false);
    //生成下载信息
    $content_download = "后台搜索关键词\"{$result['keyword']}\"商家导出的数据信息\r\n";
    $i = 1;
    $company_category = M('CompanyCategory');
    foreach($result['result'] as $value){
      $updatetime = date('Y-m-d H:i:s', $value['updatetime']);
      //主营一级
      $cc_pid = $company_category -> getFieldByid($value['ccid'], 'pid');
      $cc_one = $company_category -> getFieldByid($cc_pid, 'name');
      $content_download .= "({$i}){$value['name']}\r\n主营:{$value['manproducts']}\r\n地址:{$value['address']}\r\n电话:{$value['companyphone']}\r\n联系人:{$value['linkman']}\r\n手机:{$value['mobilephone']}\r\n邮件:{$value['email']}\r\n网址:{$value['website']}\r\nQQ:{$value['qqcode']}\r\n主营类别:{$cc_one} - {$value['ccname']}\r\n更新时间:{$updatetime}\r\n\r\n";
      $i++;
    }
    
    header("Content-Type: application/force-download");
    $filename = date('YmdHis');
    header("Content-Disposition: attachment; filename={$filename}.txt");
    echo $content_download;
  }


  /* --------------- 速查搜索管理 ---------------- */

  /* --------------- 业务订单管理 ---------------- */

  //人民币充值订单
  public function rmborder(){
    $rmb_order = M('RmbOrder');
    $where = array();
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['ro.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['ro.addtime'][] = array('lt', $endtime);
    }

    //记录总数
    $count = $rmb_order -> table('yesow_rmb_order as ro') -> where($where) -> count('id');
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

    $result = $rmb_order -> table('yesow_rmb_order as ro') -> field('ro.id,ro.ordernum,m.name as mname,ro.price,ro.status,ro.ischeck,p.name as pname,ro.addtime,ro.remark,ro.ispay') -> join('yesow_member as m ON ro.mid = m.id') -> join('yesow_payport as p ON ro.paytype = p.enname') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('ro.addtime DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //删除人民币充值订单
  public function delrmborder(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $rmb_order = M('RmbOrder');
    if($rmb_order -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //通过审核人民币充值订单
  public function passauditrmborder(){
    $rmb_order = M('RmbOrder');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($rmb_order -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //不通过审核人民币充值订单
  public function nopassauditrmborder(){
    $rmb_order = M('RmbOrder');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($rmb_order -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //会员包月订单
  public function monthlyorder(){
    $monthlyorder = M('MonthlyOrder');
    //处理搜索
    $where = array();
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['mo.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['mo.addtime'][] = array('lt', $endtime);
    }
    //记录总数
    $count = $monthlyorder -> table('yesow_monthly_order as mo') -> where($where) -> count('id');
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
    $result = $monthlyorder -> table('yesow_monthly_order as mo') -> field('mo.id,mo.ordernum,m.name as mname,tmpmm.name as mlname,tmpmm.months,mo.price,mo.status,mo.ischeck,mo.paytype,mo.addtime') -> join('yesow_member as m ON mo.mid = m.id') -> join('LEFT JOIN (SELECT mm.id,ml.name,mm.months FROM yesow_member_monthly as mm LEFT JOIN yesow_member_level as ml ON mm.lid = ml.id) as tmpmm ON mo.monid = tmpmm.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('mo.addtime DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //删除会员包月订单
  public function delmonthlyorder(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $monthlyorder = M('MonthlyOrder');
    if($monthlyorder -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  
  }

  //通过审核会员包月订单
  public function passauditmonthlyorder(){
    $monthlyorder = M('MonthlyOrder');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($monthlyorder -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //不通过审核会员包月订单
  public function nopassauditmonthlyorder(){
    $monthlyorder = M('MonthlyOrder');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($monthlyorder -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  /* --------------- 业务订单管理 ---------------- */

  /* --------------- 速查业务管理 ---------------- */

  //会员人民币管理
  public function memberrmb(){
    $member_rmb_detail = M('MemberRmbDetail');
    //处理搜索
    $where = array();
    if(!empty($_POST['name'])){
      $where['m.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    //记录总数
    $count = $member_rmb_detail -> table('(SELECT id,mid FROM yesow_member_rmb_detail GROUP BY mid) as tmp') -> join('yesow_member as m ON tmp.mid = m.id') -> where($where) -> count();
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

    $result = $member_rmb_detail -> table('yesow_member_rmb_detail as mrd') -> field('mrd.mid,m.name as mname,m.join_time as mjoin_time,m.last_login_time as mltime,cs.name as csname,csa.name as csaname,ttt.name as tname,ttt.count as tcount') -> join('yesow_member as m ON mrd.mid = m.id') -> join('yesow_child_site as cs ON m.csid = cs.id') -> join('yesow_child_site_area as csa ON m.csaid = csa.id') -> join('LEFT JOIN (SELECT * FROM (select mr.mid,ml.name,mr.rmb_pay+mr.rmb_exchange as count from yesow_member_rmb as mr LEFT JOIN yesow_member_level as ml ON mr.rmb_pay+mr.rmb_exchange >= ml.updatemoney ORDER BY mr.mid,ml.updatemoney DESC) as tmp GROUP BY mid) as ttt ON mrd.mid = ttt.mid') -> group('mrd.mid') -> order('m.join_time DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
    
    
  }

  //新增会员人民币
  public function addmemberrmb(){
    //处理新增
    if(!empty($_POST['mid'])){
      $member_rmb_detail = D('MemberRmbDetail');
      if(!$member_rmb_detail -> create()){
	$this -> error($member_rmb_detail -> getError());
      }
      if($member_rmb_detail -> add()){
	//如果增加成功，则在此会员RMB余额中做相应变化
	$mid = $this -> _post('mid');
	$money = $this -> _post('money');
	$member_rmb = D('member://MemberRmb');
	//如果是增加金额，则调用增加方法
	if($money > 0){
	  if($member_rmb -> addmoney('rmb_exchange', $money, $mid)){
	    $this -> success(L('DATA_UPDATE_SUCCESS'));
	  }else{
	    $this -> error(L('DATA_UPDATE_ERROR'));
	  }
	  //如果是减少金额，则要先减少 rmb_exchange 字段，如果 rmb_exchange 字段不够，再减少 rmb_pay 字段
	}else if($money < 0){
	  if($member_rmb -> autolessmoney($money, $mid)){
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

  //编辑会员人民币
  public function editmemberrmb(){
    $member_rmb_detail = D('MemberRmbDetail');
    if(!empty($_POST['id'])){
      $member_rmb = D('member://MemberRmb');
      $mid = $this -> _post('mid', 'intval');
      //启用事务
      $member_rmb -> startTrans();
      //先从RMB表中扣除原订单的金额
      //大于0则需要删除
      if($_POST['oldmoney'] > 0){
	$member_rmb -> autolessmoney($_POST['oldmoney'], $mid);
	//小于0则是添加
      }else{
	$member_rmb -> addmoney('rmb_exchange', abs($_POST['oldmoney']), $mid);
      }
      //在增加新的金额
      //大于0是增加，
      if($_POST['money'] > 0){
	$member_rmb -> addmoney('rmb_exchange', $_POST['money'], $mid);
	//小于0是删除
      }else{
	$member_rmb -> autolessmoney($_POST['money'], $mid);
      }
      //最后更新订单表
      if(!$member_rmb_detail -> create()){
	$this -> error($member_rmb_detail -> getError());
      }
      if($member_rmb_detail -> save()){
	// 提交事务
	$member_rmb->commit();
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
	// 事务回滚
	$member_rmb->rollback(); 
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $member_rmb_detail -> field('mid,content,type,money') -> find($this -> _get('id'));
    $this -> assign('result', $result);
    $this -> display();
  
  }

  //删除会员人民币
  public function delmemberrmb(){
    $member_rmb_detail = M('MemberRmbDetail');
    $member_rmb = D('member://MemberRmb');
    //启用事务
    $member_rmb -> startTrans();
    //先查询要删除记录的金额
    $del_money_arr = $member_rmb_detail -> field('mid,money') -> where(array('id' => array('in', $_POST['ids']))) -> select();
    //循环从RMB表中删除这些金额
    foreach($del_money_arr as $value){
      //大于0则需要减少
      if($value['money'] > 0){
	$member_rmb -> autolessmoney($value['money'], $value['mid']);
	//小于0则是添加
      }else{
	$member_rmb -> addmoney('rmb_exchange', abs($value['money']), $value['mid']);
      }
    }
    //再删除明细表
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($member_rmb_detail -> where($where_del) -> delete()){
      // 提交事务
      $member_rmb->commit();
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      // 事务回滚
      $member_rmb->rollback();
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //查看会员人民币
  public function auditmemberrmb(){
    $member_rmb_detail = M('MemberRmbDetail');
    $member_rmb = M('MemberRmb');
    $mid = $this -> _request('id', 'intval');
    //记录总数
    $count = $member_rmb_detail -> where(array('mid' => $mid)) -> count('id');
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
    //会员信息
    $info = $member_rmb -> table('yesow_member_rmb as mr') -> Field('mr.rmb_pay+mr.rmb_exchange as count,m.name as mname') -> join('yesow_member as m ON mr.mid = m.id') -> where(array('mr.mid' => $mid)) -> find();
    $this -> assign('info', $info);
    //人民币明细结果
    $result = $member_rmb_detail -> field('id,addtime,content,type,money') -> where(array('mid' => $mid)) -> order('addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display(); 
  }

  //会员包月管理
  public function membermonthly(){
    $monthly = M('Monthly');
    $where = array();
    //记录总数
    $count = $monthly -> table('yesow_monthly as m') -> where($where) -> count('id');
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

    $result = $monthly -> table('yesow_monthly as m') -> field('m.id,tmp.name as tname,tmpt.name as tmpname,tmpt.csname as csname,m.starttime,m.endtime,tmpt.nickname,m.ischeck') -> join('LEFT JOIN (SELECT ml.name,mm.id FROM yesow_member_monthly as mm LEFT JOIN yesow_member_level as ml ON mm.lid = ml.id) as tmp ON m.monid = tmp.id') -> join('LEFT JOIN (SELECT m.id,m.name,cs.name as csname,m.nickname FROM yesow_member as m LEFT JOIN yesow_child_site as cs ON m.csid = cs.id) as tmpt ON m.mid = tmpt.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('m.starttime DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  
  }

  //添加会员包月
  public function addmembermonthly(){
    //处理添加
    if(!empty($_POST['starttime'])){
      $monthly = D('index://Monthly');
      if(!$monthly -> create()){
	$this -> error($monthly -> getError());
      }
      if($monthly -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $member_monthly = M('MemberMonthly');
    //查询包月会员等级
    $result_level = $member_monthly -> table('yesow_member_monthly as mm') -> field('ml.id,ml.name') -> join('yesow_member_level as ml ON mm.lid = ml.id') -> group('mm.lid') -> order('ml.updatemoney ASC') -> select();
    $this -> assign('result_level', $result_level);
    $this -> display();
  
  }

  //删除会员包月
  public function delmembermonthly(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $monthly = M('Monthly');
    if($monthly -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑会员包月
  public function editmembermonthly(){
    $monthly = D('index://Monthly');
    //处理更新
    if(!empty($_POST['monid'])){
      if(!$monthly -> create()){
	$this -> error($monthly -> getError());
      }
      if($monthly -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $monthly -> table('yesow_monthly as m') -> field('m.monid,m.starttime,m.endtime,mr.name as mname') -> join('yesow_member as mr ON m.mid = mr.id') -> where(array('m.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $member_monthly = M('MemberMonthly');
    //查询包月会员等级
    $result_level = $member_monthly -> table('yesow_member_monthly as mm') -> field('ml.id,ml.name') -> join('yesow_member_level as ml ON mm.lid = ml.id') -> group('mm.lid') -> order('ml.updatemoney ASC') -> select();
    $this -> assign('result_level', $result_level);
    //查询包月的会员等级和月数
    $result_monthly = $member_monthly -> field('lid,months') -> find($result['monid']);
    $this -> assign('result_monthly', $result_monthly);
    //查询该会员等级的包月数
    $result_monthlu_month = $member_monthly -> field('id,months') -> where(array('lid' => $result_monthly['lid'])) -> order('months ASC') -> select();
    $this -> assign('result_monthlu_month', $result_monthlu_month);
    $this -> display();
  }

  //通过审核会员包月
  public function passauditmembermonthly(){
    $monthly = M('Monthly');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($monthly -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //不通过审核会员包月
  public function nopassauditmembermonthly(){
    $monthly = M('Monthly');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($monthly -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }
  
  /* --------------- 速查业务管理 ---------------- */


  /* --------------- 速查设置管理 ---------------- */

  //速查基本设置
  public function companysetup(){
    $company_setup = M('CompanySetup');
    //更新
    if(!empty($_POST['cs_viewtime'])){
      $mun = 0;
      foreach($_POST as $key => $value){
	if(substr($key, 0, 3) == 'cs_'){
	  $data = array();
	  $where = array();
	  $where['name'] = substr($key, 3);
	  $data['value'] = $value;
	  $num += $company_setup -> where($where) -> save($data);
	}
      }
      //更新免费分站数据
      $freechildsite = '';
      foreach($_POST['childsite'] as $key => $value){
	if($key == 0){
	  $freechildsite .= $value;
	}else{
	  $freechildsite .= ',' . $value;
	}
      }
      $data = array();
      $where = array();
      $data['value'] = $freechildsite;
      $where['name'] = 'freechildsite';
      $num += $company_setup -> where($where) -> save($data);

      if($num != 0){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
	$this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //读取辖区
    $result_area = M('Area') -> field('id,name') -> select();
    $child_site = M('ChildSite');
    //读取辖区下分站
    foreach($result_area as $key => $value){
      $result_area[$key]['childsite'] = $child_site -> field('id,name') -> where(array('aid' => $value['id'])) -> select();
    }
    $this -> assign('childsite', $result_area);
    //读取免费分站
    $freesite = $company_setup -> getFieldByname('freechildsite', 'value');
    $freesite_arr = explode(',', $freesite);
    $this -> assign('freesite_arr', $freesite_arr);
    //读取设置项
    $this -> assign('viewtime', $company_setup -> getFieldByname('viewtime', 'value'));
    $this -> assign('ebtormb', $company_setup -> getFieldByname('ebtormb', 'value'));
    $this -> assign('addsuccess', $company_setup -> getFieldByname('addsuccess', 'value'));
    $this -> assign('adderror', $company_setup -> getFieldByname('adderror', 'value'));
    $this -> assign('reportsuccess', $company_setup -> getFieldByname('reportsuccess', 'value'));
    $this -> assign('reporterror', $company_setup -> getFieldByname('reporterror', 'value'));
    $this -> assign('changesuccess', $company_setup -> getFieldByname('changesuccess', 'value'));
    $this -> assign('changeerror', $company_setup -> getFieldByname('changeerror', 'value'));
    $this -> display();
  
  }
  
  //速查业务设置
  public function companybusinesssetup(){
    $business = M('CompanyBusinessSetup');
    //处理编辑
    if(!empty($_POST['cbs_qq'])){
      $mun = 0;
      foreach($_POST as $key => $value){
	if(substr($key, 0, 4) == 'cbs_'){
	  $data = array();
	  $where = array();
	  $where['name'] = substr($key, 4);
	  $data['value'] = $value;
	  $num += $business -> where($where) -> save($data);
	}
      }
      if($num != 0){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
	$this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //名片qq
    $this -> assign('qq', $business -> getFieldByname('qq', 'value'));
    $this -> display();
  }

  //充值返送管理
  public function paygiving(){
    $pay_gaving = M('PayGaving');
    //记录总数
    $count = $pay_gaving -> count('id');
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

    $result = $pay_gaving -> field('id,money,ratio*100 as ratio,remark') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('money') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //增加充值返送
  public function addpaygiving(){
    //处理增加
    if(!empty($_POST['money'])){
      $pay_gaving = M('PayGaving');
      $_POST['ratio'] = $_POST['ratio'] / 100;
      if(!$pay_gaving -> create()){
	$this -> error($pay_gaving -> getError());
      }
      if($pay_gaving -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除充值返送
  public function delpaygiving(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $pay_gaving = M('PayGaving');
    if($pay_gaving -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑充值返送
  public function editpaygiving(){
    $pay_gaving = M('PayGaving');
    //处理更新
    if(!empty($_POST['money'])){
      $_POST['ratio'] = $_POST['ratio'] / 100;
      if(!$pay_gaving -> create()){
	$this -> error($pay_gaving -> getError());
      }
      if($pay_gaving -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $pay_gaving -> field('id,money,ratio*100 as ratio,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //包月价格管理
  public function monthlymoney(){
    $member_monthly = M('MemberMonthly');
    $where = array();
    //处理搜索
    if(!empty($_POST['lid'])){
      $where['mm.lid'] = $this -> _post('lid', 'intval');
    }

    //记录总数
    $count = $member_monthly -> table('yesow_member_monthly as mm') -> where($where) -> count('id');
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
    $result = $member_monthly -> table('yesow_member_monthly as mm') -> field('mm.id,ml.name as lname,mm.months,mm.marketprice,mm.promotionprice,mm.remark') -> join('yesow_member_level as ml ON mm.lid = ml.id') -> order('ml.updatemoney ASC,mm.months ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    //查询会员等级
    $result_level = M('MemberLevel') -> field('id,name') -> order('updatemoney ASC') -> select();
    $this -> assign('result_level', $result_level);

    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  
  }

  //增加包月价格
  public function addmonthlymoney(){
    $member_level = M('MemberLevel');
    //处理添加
    if(!empty($_POST['lid'])){
      $level_monthly = M('MemberMonthly');
      if(!$level_monthly -> create()){
	$this -> error($level_monthly -> getError());
      }
      if($level_monthly -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询会员等级
    $result_level = $member_level -> field('id,name') -> order('updatemoney ASC') -> select();
    $this -> assign('result_level', $result_level);
    $this -> display();
  
  }

  //删除包月价格
  public function delmonthlymoney(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $level_monthly = M('MemberMonthly');
    if($level_monthly -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑包月价格
  public function editmonthlymoney(){
    $level_monthly = M('MemberMonthly');
    //处理更新
    if(!empty($_POST['lid'])){
      if(!$level_monthly -> create()){
	$this -> error($level_monthly -> getError());
      }
      if($level_monthly -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //查询数据
    $result = $level_monthly -> field('lid,months,marketprice,promotionprice,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //会员等级
    $result_level = M('MemberLevel') -> field('id,name') -> order('updatemoney ASC') -> select();
    $this -> assign('result_level', $result_level);

    $this -> display();
  
  }

  /* --------------- 速查设置管理 ---------------- */
}

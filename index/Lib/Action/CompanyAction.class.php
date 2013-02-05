<?php
//渠道黄页
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

  //渠道黄页首页
  public function index(){
    $this -> display();
  }

  //添加速查信息
  public function add(){
    //处理添加
    if(!empty($_POST['companyname'])){
      if($this -> _post('verify', 'md5') != $_SESSION['verify']){
	R('Public/errorjump',array(L('VERIFY_ERROR')));
      }
      $companyaudit = D('admin://CompanyAudit');
      if(!$a = $companyaudit -> create()){
	R('Public/errorjump',array($companyaudit -> getError()));
      }
      if(isset($_SESSION[C('USER_AUTH_KEY')])){
	$companyaudit -> mid = session(C('USER_AUTH_KEY'));
      }
      $companyaudit -> type = '添加';
      if(!empty($_FILES['pic']['name'])){
	$companyaudit -> pic = $this -> upload();
      }
      if($companyaudit -> add()){
	R('Public/infojump',array(L('COMPANY_ADD_SUCCESS'), U('Company/add')));
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询公司类型
    $result_company_type = M('CompanyType') -> field('id,name') -> select();
    $this -> assign('result_company_type', $result_company_type);
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    //如果会员已登录，则查出此会员的个人信息
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      $add_info = M('Member') -> field('fullname,tel,unit,address,email,idnumber') -> find(session(C('USER_AUTH_KEY')));
      $this -> assign('add_info', $add_info);
    }
    $this -> display();
  }

  //速查详情页
  public function info(){
    $company = M('Company');
    $id = $this -> _get('id', 'intval');
    //点击量加一
    $company -> where(array('id' => $id)) -> setInc('clickcount');
    //结果
    $result = $company -> table('yesow_company as c') -> field('c.name,c.clickcount,c.pic,ct.name as ctname,cs.name as csname,csa.name as csaname,c.ccid,cc.name as ccname,c.manproducts,c.linkman,c.address,c.content,c.mobilephone,c.qqcode,c.email,c.website') -> where(array('c.id' => $id)) -> join('yesow_company_type as ct ON c.typeid = ct.id') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> find();
    //是否有查看权
    $member_company = M('MemberCompany');
    $where = array();
    $where['mid'] = session(C('USER_AUTH_KEY'));
    $where['cid'] = $id;
    //计算最早购买时间,大于这个购买时间的都有效
    $time = mktime() - 86400;
    $where['time'] = array('EGT', $time);
    //如果未查询到数据，则隐藏数据内容
    if(!$member_company -> where($where) -> find()){
      $result['mobilephone'] = substr_replace($result['mobilephone'], '********', 3);
      $result['qqcode'] = '********';
      $result['email'] = substr_replace($result['email'], '*****', 0, strpos($result['email'], '@'));
      $result['website'] = substr_replace($result['website'], '*******', 7);
    }
    $this -> assign('result', $result);
    //最新更新同行
    $result_counterparts = $company -> table('yesow_company as c') -> field('c.id,c.name,c.updatetime,cs.name as csname') -> where(array('c.ccid' => $result['ccid'])) -> limit(15) -> order('c.updatetime DESC') -> join('yesow_child_site as cs ON c.csid = cs.id') -> select();
    $this -> assign('result_counterparts', $result_counterparts);
    $this -> display();
  }

  //报错页
  public function report(){
    //报错需要登录
    if(!isset($_SESSION[C('USER_AUTH_KEY')])){
      R('Public/errorjump',array(L('COMPANY_REPORT_LOGIN'), '__ROOT__/member.php/public/login/a_c/company/m_d/report/id/' . $this -> _get('id', 'intval')));
    }
    //处理更新
    if(!empty($_POST['submit'])){
      if($_SESSION['verify'] != $this -> _post('verify', 'md5')){
	R('Public/errorjump',array(L('VERIFY_ERROR')));
      }
      $report = D('CompanyReport');
      if(!$a = $report -> create()){
	R('Public/errorjump',array($report -> getError()));
      }
      $report -> mid = $_SESSION[C('USER_AUTH_KEY')];
      if($report -> add()){
	R('Public/infojump',array(L('COMPANY_ADD_SUCCESS')));
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    $company = M('Company');
    //企业信息
    $result = $company -> field('name,address,linkman') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //错误类型
    $result_errortype = M('CompanyErrorType') -> field('id,name') -> select();
    $this -> assign('result_errortype', $result_errortype);
    $this -> display();
  }

  //改错页
  public function change(){
     $company = M('Company');
    //处理提交
    if(!empty($_POST['submit'])){
      if($_SESSION['verify'] != $this -> _post('verify', 'md5')){
	R('Public/errorjump',array(L('VERIFY_ERROR')));
      }
      $cid = $this -> _post('cid', 'intval');
      //如果存在*号，则认为数据没改变
      if(false !== strpos($_POST['mobilephone'], '*')){
	$_POST['mobilephone'] = $company -> getFieldByid($cid, 'mobilephone');
      }
      if(false !== strpos($_POST['companyphone'], '*')){
	$_POST['companyphone'] = $company -> getFieldByid($cid, 'companyphone');
      }
      if(false !== strpos($_POST['qqcode'], '*')){
	$_POST['qqcode'] = $company -> getFieldByid($cid, 'qqcode');
      }
      if(false !== strpos($_POST['email'], '*')){
	$_POST['email'] = $company -> getFieldByid($cid, 'email');
      }
      if(false !== strpos($_POST['companywebsite'], '*')){
	$_POST['companywebsite'] = $company -> getFieldByid($cid, 'website');;
      }
      $companyaudit = D('admin://CompanyAudit');
      if(!$a = $companyaudit -> create()){
	R('Public/errorjump',array($companyaudit -> getError()));
      }
      if(isset($_SESSION[C('USER_AUTH_KEY')])){
	$companyaudit -> mid = session(C('USER_AUTH_KEY'));
      }
      $companyaudit -> type = '改错';
      if(!empty($_FILES['pic']['name'])){
	$companyaudit -> pic = $this -> upload();
      }
      if($companyaudit -> add()){
	R('Public/infojump',array(L('COMPANY_ADD_SUCCESS'), '__ROOT__/company/' . $cid));
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
   
    //结果
    $result = $company -> field('name,address,typeid,csid,csaid,linkman,mobilephone,companyphone,qqcode,email,website,manproducts,ccid,keyword,content') -> find($this -> _get('id', 'intval'));
    //是否有查看权
    $member_company = M('MemberCompany');
    $where = array();
    $where['mid'] = session(C('USER_AUTH_KEY'));
    $where['cid'] = $this -> _get('id', 'intval');
    //计算最早购买时间,大于这个购买时间的都有效
    $time = mktime() - 86400;
    $where['time'] = array('EGT', $time);
    //如果未查询到数据，则隐藏数据内容
    if(!$member_company -> where($where) -> find()){
      $result['mobilephone'] = substr_replace($result['mobilephone'], '********', 3);
      $result['qqcode'] = '********';
      $result['email'] = substr_replace($result['email'], '*****', 0, strpos($result['email'], '@'));
      $result['website'] = substr_replace($result['website'], '*******', 7);
      $result['companyphone'] = substr_replace($result['companyphone'], '*******', 5);
    }
    $this -> assign('result', $result);
    //查询公司类型
    $result_company_type = M('CompanyType') -> field('id,name') -> select();
    $this -> assign('result_company_type', $result_company_type);
    //根据csid 查分站下地区列表
    $result_childsite_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> select();
    $this -> assign('result_childsite_area', $result_childsite_area);
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询ccid对应的主营一级类别
    $result_ccid_one = M('CompanyCategory') -> getFieldByid($result['ccid'], 'pid');
    $this -> assign('result_ccid_one', $result_ccid_one);
    //查询对应主营类别 - 二级
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result_ccid_one)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    //如果会员已登录，则查出此会员的个人信息
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      $add_info = M('Member') -> field('fullname,tel,unit,address,email,idnumber') -> find(session(C('USER_AUTH_KEY')));
      $this -> assign('add_info', $add_info);
    }
    $this -> display();
  }
}

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
    $this -> assign('result', $result);
    //最新更新同行
    $result_counterparts = $company -> table('yesow_company as c') -> field('c.id,c.name,c.updatetime,cs.name as csname') -> where(array('c.ccid' => $result['ccid'])) -> limit(15) -> order('c.updatetime DESC') -> join('yesow_child_site as cs ON c.csid = cs.id') -> select();
    $this -> assign('result_counterparts', $result_counterparts);
    $this -> display();
  }
}

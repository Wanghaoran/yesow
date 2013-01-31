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
  public function addcompany(){
    //处理添加
    if(!empty($_POST['companyname'])){
      if($this -> _post('verify', 'md5') != $_SESSION['verify']){
	$this -> error(L('VERIFY_ERROR'));
      }
      $companyaudit = D('admin://CompanyAudit');
      if(!$a = $companyaudit -> create()){
	$this -> error($companyaudit -> getError());
      }
      if(isset($_SESSION[C('USER_AUTH_KEY')])){
	$companyaudit -> mid = session(C('USER_AUTH_KEY'));
      }
      $companyaudit -> type = '添加';
      if(!empty($_FILES['pic']['name'])){
	$companyaudit -> pic = $this -> upload();
      }
      if($companyaudit -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
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
}

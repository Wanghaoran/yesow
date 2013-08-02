<?php
class PublicAction extends Action {

  /*
   * 检测用户是否登录
   */
  public function checkuser(){
    if(!isset($_SESSION[C('USER_AUTH_KEY')])){
      $this -> error(L('LOGIN_NOT'), U(C('USER_AUTH_GATEWAY')));
    }
  }

  /*
   * 用户登录界面
   */
  public function login(){
    $this -> display();
  }

  /*
   * 验证用户登录
   */
  public function checklogin(){
    if(empty($_POST['account'])){
      $this -> error(L('LOGIN_NAME_EMPTY'));
    }else if(empty($_POST['password'])){
      $this -> error(L('LOGIN_PWD_EMPTY'));
    }else if(empty($_POST['verify'])){
      $this -> error(L('LOGIN_VERIFY_EMPTY'));
    }
    if($this -> _session('verify') != md5($this -> _post('verify'))){
      $this -> error(L('VERIFY_ERROR'));
    }
    import('ORG.Util.RBACI');
    //生成认证条件
    $cond = array();
    $cond['name'] = $this -> _post('account');
    $cond['status'] = '1';
    $authInfo = RBAC::authenticate($cond);
    if(false == $authInfo){
      $this -> error(L('NAME_ERROR'));
    }
    if($authInfo['password'] != sha1($this -> _post('password'))){
      $this -> error(L('PASSWORD_ERROR'));
    }
    //生成登录信息
    session(C('USER_AUTH_KEY'), $authInfo['id']);
    session('admin_name', $authInfo['name']);
    session('lastLoginTime', $authInfo['last_login_time']);
    session('loginCount', $authInfo['login_count']);
    session('last_login_ip', $authInfo['last_login_ip']);
    session('csid', $authInfo['csid']);
    if($authInfo['name'] == 'admin'){
      session(C('ADMIN_AUTH_KEY'), true);
    }
    //保存登录信息
    $admin = M('Admin');
    $data = array();
    $data['id'] = $authInfo['id'];
    $data['last_login_ip'] = get_client_ip();
    $data['last_login_time'] = time();
    $data['login_count'] = array('exp', 'login_count+1');
    $admin -> save($data);
    //缓存访问权限
    RBAC::saveAccessList();
    $this -> success(L('LOGIN_SUCCESS'), U('Index/index'));
  }

  /*
   * 退出登录
   */
  public function logout(){
    session(C('USER_AUTH_KEY'), null);
    session(null);
    session('[destroy]');
    $this -> success(L('LOGOUT_SUCCESS'), U(C('USER_AUTH_GATEWAY')));
  }

  /*
   * 生成验证码
   */
  public function verify(){
    import('ORG.Util.Image');
    ob_end_clean();
    Image::buildImageVerify();
  }
  /*
   * 后台首页 查看系统信息
   */
  public function main(){
   $info = array(
     '操作系统'=>PHP_OS,
     '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
     'PHP运行方式'=>php_sapi_name(),
     'ThinkPHP版本'=>THINK_VERSION.' [ <a href="http://thinkphp.cn" target="_blank">查看最新版本</a> ]',
     '上传附件限制'=>ini_get('upload_max_filesize'),
     '执行时间限制'=>ini_get('max_execution_time').'秒',
     '服务器时间'=>date("Y年n月j日 H:i:s"),
     '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
     '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
     '剩余空间'=>round((@disk_free_space(".")/(1024*1024)),2).'M',
     'register_globals'=>get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
     'magic_quotes_gpc'=>(1===get_magic_quotes_gpc())?'YES':'NO',
     'magic_quotes_runtime'=>(1===get_magic_quotes_runtime())?'YES':'NO',
   );
   $this->assign('info', $info);
   $this->display();  
  }

  /*
   * 后台首页 修改密码
   */
  public function password(){
    $this -> display(); 
  }

  /*
   * 验证 修改 密码
   */
  public function changepwd(){
    //验证是否登录
    $this -> checkuser();
    //数据完整性检测
    if(empty($_POST['oldpassword'])){
      $this -> error(L('OLDPASSWORD_EMPTY'));
    }else if(empty($_POST['password'])){
      $this -> error(L('NEWPASSWORD_EMPTY'));
    }else if(empty($_POST['repassword'])){
      $this -> error(L('REPASSWORD_EMPTY'));
    }else if($_POST['password'] != $_POST['repassword']){
      $this -> error(L('PASSWORD_NEQ'));
    }else if(md5($_POST['verify']) != $_SESSION['verify']){
      $this -> error(L('VERIFY_ERROR'));
    }
    //构建查询条件
    $cond = array();
    $cond['password'] = sha1($this -> _post('oldpassword'));
    $cond['id'] = session(C('USER_AUTH_KEY'));
    $admin = M('Admin');
    if(!$admin -> field('id') -> where($cond) -> find()){
      $this -> error(L('OLDPASSWORD_ERROR'));
    }
    //构建更新条件
    $cond['password'] = sha1($this -> _post('password'));
    if($admin -> save($cond)){
      $this -> success(L('CHANGE_PWD_SUCCESS'));
    }else{
      $this -> error(L('CHANGE_PWD_ERROR'));
    }
  }

  /*
   * 后台首页 修改资料
   */
  public function profile(){
    $this -> checkuser();
    $admin = M('Admin');
    $result = $admin -> field('remark') -> find(session(C('USER_AUTH_KEY')));
    $this -> assign('result', $result);
    $this -> display();
  }

  /*
   * 更改资料
   */
  public function change(){
    $this -> checkuser();
    $admin = D('Admin');
    //构建更新条件
    $data = array();
    $data['id'] = session(C('USER_AUTH_KEY'));
    $data['email'] = $this -> _post('email');
    $data['remark'] = $this -> _post('remark');
    if(!$admin -> create($data)){
      $this -> error($admin -> getError());
    }
    if($admin -> save()){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //ajax获取资讯二级分类、内容属性、标题属性
  public function ajaxinfotwocolumn(){
    if(!empty($_GET['code'])){
      $where = array();
      $where['oneid'] = $this -> _get('code', 'intval');
      $result = array();
      $result['twocolumn'] = M('InfoTwoColumn') -> field('id,name') -> where($where) -> select();
      $result['titleattribute'] = M('InfoTitleAttribute') -> field('id,name') -> where($where) -> select();
      $result['contentattribute'] = M('InfoContentAttribute') -> field('id,name') -> where(array('oneid' => $this -> _get('code', 'intval'), 'pid' => 0)) -> select();      
      echo json_encode($result);
    }
  }

  //ajax获取二级内容属性
  public function ajaxinfocontentattribute(){
    if(!empty($_GET['code'])){
      $where = array();
      $where['pid'] = $this -> _get('code', 'intval');
      $result = M('InfoContentAttribute') -> field('id,name') -> where($where) -> select();
      if($result){
	echo json_encode($result);
      }else{
      	echo '';
      }
    }
  }

    //ajax获取分站下地区
  public function ajaxgetcsaid(){
    $result_temp = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $this -> _get('id', 'intval'))) -> select();
    $result = array();
    $result[] = array('', '请选择');
    //格式化结果集
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  //ajax获取速查主营类别 - 二级
  public function ajaxgetcompanycategorytwo(){
    $result_temp = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $this -> _get('id', 'intval'))) -> order('sort ASC') -> select();
    $result = array();
    $result[] = array('', '请选择');
    //格式化结果集
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  //ajax获取用户名及id
  public function ajaxgetmemberinfo(){
    $username = $this -> _post('inputValue');
    $member = M('Member');
    $where = array();
    $where['name'] = array('like', '%' . $username . '%');
    $result = $member -> field('id,name') -> where($where) -> limit(10) -> select();
    echo json_encode($result);
  }

  //ajax获取速查公司名及id
  public function ajaxgetcompanyinfo(){
    $companyname = $this -> _post('inputValue');
    $company = M('Company');
    $where = array();
    $where['name'] = array('like', '%' . $companyname . '%');
    $result = $company -> field('id,name') -> where($where) -> limit(10) -> select();
    echo json_encode($result);
  }

  //ajax获取包月会员类型
  public function ajaxgetmonthlytype(){
    $member_monthly = M('MemberMonthly');
    $lid = $this -> _get('id', 'intval');
    $result_tmp = $member_monthly -> field('id,months') -> where(array('lid' => $lid)) -> order('months ASC') -> select();
    $result = array();
    //格式化结果集
    foreach($result_tmp as $key => $value){
      $result[] = array($value['id'], $value['months'] . '个月');
    }
    echo json_encode($result);
  }

  //ajax获取商品二级分类
  public function ajaxgetshoptwoclass(){
    $pid = $this -> _get('id', 'intval');
    $shop_class = M('ShopClass');
    $result_tmp = $shop_class -> field('id,name') -> where(array('pid' => $pid)) -> order('sort ASC') -> select();
    $result = array();
    //格式化结果集
    foreach($result_tmp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  //商品图片文件上传
  public function shop_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('SHOP_PIC_PATH') ;//设置上传目录
    $upload -> autoSub = false;//设置使用子目录保存上传文件
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  //动感传媒图片文件上传
  public function media_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('MEDIA_PIC_PATH') ;//设置上传目录
    $upload -> autoSub = false;//设置使用子目录保存上传文件
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  //旺铺出租图片文件上传
  public function store_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('STORE_PIC_PATH') ;//设置上传目录
    $upload -> autoSub = false;//设置使用子目录保存上传文件
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  //ajax获取二手滞销发布类别 - 二级
  public function ajaxgetselltid(){
    $result_temp = M('SellUsedType') -> field('id,name') -> where(array('pid' => $this -> _get('id', 'intval'))) -> select();
    $result = array();
    $result[] = array('', '请选择');
    //格式化结果集
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  //二手滞销图片文件上传
  public function sellused_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('SELLUSED_PIC_PATH') ;//设置上传目录
    $upload -> autoSub = false;//设置使用子目录保存上传文件
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  //ajax获取后台邮件发送模板
  public function ajaxgetemailtemplate(){
    echo M('BackgroundEmailTemplate') -> getFieldByid($this -> _get('id', 'intval'),'content');
  }

  //生成网站地图
  public function setwebsitemap(){
    $time = date('Y-m-d');
    if(is_file('./sitemap.html')){
      unlink('./sitemap.html');
    }
    if(is_file('./Sitemap.xml')){
      unlink('./Sitemap.xml');
    }
    $content_html = '<!doctype html public "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>www.yesow.com SiteMap</title>
<META NAME="description" CONTENT="">
<META NAME="keywords" CONTENT="">
</head>
<body>
<div class="width:100%">
<div style="margin-bottom:30px; width:100%; text-align:center">
<a style="text-decoration:none;" href="http://www.yesow.com"><span style="font-size:20px;color:#3574b1;">www.yesow.com</span><font color=red>SiteMap</font></a>
</div>
</div>
<div class="width:100%">';
    $content_xml = '<?xml version="1.0" encoding="UTF-8"?>
      <urlset>';
    //读取分站
    $data_website = M('ChildSite') -> field('name,domain') -> where('isshow=1') -> select();
    foreach($data_website as $value){
      $content_html .= '<a href="http://' . $value['domain'] .'">' . $value['name'] . '</a><br />';
      $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }
    //导航
    $content_html .= '<a href="http://www.yesow.com">易搜首页</a><br />';
    $content_html .= '<a href="http://www.yesow.com/company">渠道黄页</a><br />';
    $content_html .= '<a href="http://www.yesow.com">企业会员</a><br />';
    $content_html .= '<a href="http://www.yesow.com/hire">出租招聘</a><br />';
    $content_html .= '<a href="http://www.yesow.com/info">资讯文章</a><br />';
    $content_html .= '<a href="http://www.yesow.com">招商引资</a><br />';
    $content_html .= '<a href="http://www.yesow.com/agent">代理加盟</a><br />';
    $content_html .= '<a href="http://www.yesow.com/shop">易搜商城</a><br />';
$content_xml .= '
<url>
<loc>http://www.yesow.com</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com/company</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com/hire</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com/info</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com/agent</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
$content_xml .= '
<url>
<loc>http://www.yesow.com/shop</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';

    //速查
    $company_data = M('Company') -> table('yesow_company as c') -> field('c.name,c.id,cs.domain') -> join('yesow_child_site as cs ON c.csid = cs.id') -> where('c.delaid is NULL') -> select();
    foreach($company_data as $value){
      $content_html .= '<a href="http://' . $value['domain'] .'/company/' . $value['id'] . '">' . $value['name'] . '</a><br />';
      $content_xml .= '
<url>
<loc>http://' . $value['domain'] .'/company/' . $value['id'] . '</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }
    //商城
    $shop_data = M('Shop') -> field('id,title') -> select();
    foreach($shop_data as $value){
      $content_html .= '<a href="http://www.yesow.com/shop/' . $value['id'] .'">' . $value['title'] . '</a><br />';
      $content_xml .= '
<url>
<loc>http://www.yesow.com/shop/' . $value['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }
    //资讯文章
    $article_data = M('InfoArticle') -> field('id,title') -> select();
    foreach($article_data as $value){
      $content_html .= '<a href="http://www.yesow.com/article/' . $value['id'] .'">' . $value['title'] . '</a><br />';
                  $content_xml .= '
<url>
<loc>http://www.yesow.com/article/' . $value['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }
    //公告
    $notice_data = M('Notice') -> field('id,title') -> select();
    foreach($notice_data as $value){
      $content_html .= '<a href="http://www.yesow.com/notice/' . $value['id'] .'">' . $value['title'] . '</a><br />';
       $content_xml .= '
<url>
<loc>http://www.yesow.com/notice/' . $value['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }
    //旺铺出租
    $storerent_data = M('StoreRent') -> field('id,title') -> select();
    foreach($storerent_data as $value){
      $content_html .= '<a href="http://www.yesow.com/storerent/' . $value['id'] .'">' . $value['title'] . '</a><br />';
      $content_xml .= '
<url>
<loc>http://www.yesow.com/storerent/' . $value['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }
    $content_html .= '</div></body></html>';
    $content_xml .= '
</urlset>';
    $file_html = fopen('./sitemap.html', 'wb');
    fwrite($file_html, $content_html);
    fclose($file_html);

    $file_xml = fopen('./Sitemap.xml', 'wb');
    fwrite($file_xml, $content_xml);
    fclose($file_xml);

    $sitemap_upload = M('SitemapUpload');
    $data_upload = array();
    $data_upload['websitenum'] = count($data_website);
    $data_upload['navnum'] = 8;
    $data_upload['companynum'] = count($company_data);
    $data_upload['shopnum'] = count($shop_data);
    $data_upload['articlenum'] = count($article_data);
    $data_upload['noticenum'] = count($notice_data);
    $data_upload['storerentnum'] = count($storerent_data);
    $data_upload['updatetime'] = time();
    $sitemap_upload -> add($data_upload);
    $this -> success('网站地图更新成功', __ROOT__);
    return;
  }
}

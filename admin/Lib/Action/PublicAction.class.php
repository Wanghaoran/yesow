<?php
class PublicAction extends Action {

  public function checkuser(){
    if(!isset($_SESSION[C('USER_AUTH_KEY')])){
      $this -> error(L('LOGIN_NOT'), U(C('USER_AUTH_GATEWAY')));
    }
  }

  public function login(){
    $this -> display();
  }

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
    session(C('USER_AUTH_KEY'), $authInfo['id']);
    session('admin_name', $authInfo['name']);
    session('lastLoginTime', $authInfo['last_login_time']);
    session('loginCount', $authInfo['login_count']);
    session('last_login_ip', $authInfo['last_login_ip']);
    session('csid', $authInfo['csid']);
    if($authInfo['name'] == 'admin'){
      session(C('ADMIN_AUTH_KEY'), true);
    }
    $admin = M('Admin');
    $data = array();
    $data['id'] = $authInfo['id'];
    $data['last_login_ip'] = get_client_ip();
    $data['last_login_time'] = time();
    $data['login_count'] = array('exp', 'login_count+1');
    $admin -> save($data);
    RBAC::saveAccessList();
    $this -> success(L('LOGIN_SUCCESS'), U('Index/index'));
  }

  public function logout(){
    session(C('USER_AUTH_KEY'), null);
    session(null);
    session('[destroy]');
    $this -> success(L('LOGOUT_SUCCESS'), U(C('USER_AUTH_GATEWAY')));
  }

  public function verify(){
    import('ORG.Util.Image');
    ob_end_clean();
    Image::buildImageVerify();
  }
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

  public function password(){
    $this -> display(); 
  }

  public function changepwd(){
    $this -> checkuser();
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
    $cond = array();
    $cond['password'] = sha1($this -> _post('oldpassword'));
    $cond['id'] = session(C('USER_AUTH_KEY'));
    $admin = M('Admin');
    if(!$admin -> field('id') -> where($cond) -> find()){
      $this -> error(L('OLDPASSWORD_ERROR'));
    }
    $cond['password'] = sha1($this -> _post('password'));
    if($admin -> save($cond)){
      $this -> success(L('CHANGE_PWD_SUCCESS'));
    }else{
      $this -> error(L('CHANGE_PWD_ERROR'));
    }
  }

  public function profile(){
    $this -> checkuser();
    $admin = M('Admin');
    $result = $admin -> field('remark') -> find(session(C('USER_AUTH_KEY')));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function change(){
    $this -> checkuser();
    $admin = D('Admin');
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

  public function ajaxgetcsaid(){
    $result_temp = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $this -> _get('id', 'intval'))) -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  public function ajaxgetcompanycategorytwo(){
    $result_temp = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $this -> _get('id', 'intval'))) -> order('sort ASC') -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  public function ajaxgetmemberinfo(){
    $username = $this -> _post('inputValue');
    $member = M('Member');
    $where = array();
    $where['name'] = array('like', '%' . $username . '%');
    $result = $member -> field('id,name') -> where($where) -> limit(10) -> select();
    echo json_encode($result);
  }

  public function ajaxgetcompanyinfo(){
    $companyname = $this -> _post('inputValue');
    $company = M('Company');
    $where = array();
    $where['name'] = array('like', '%' . $companyname . '%');
    $result = $company -> field('id,name') -> where($where) -> limit(10) -> select();
    echo json_encode($result);
  }

  public function ajaxgetrecruit_companyinfo(){
    $companyname = $this -> _post('inputValue');
    $RecruitCompany = M('RecruitCompany');
    $where = array();
    $where['name'] = array('like', '%' . $companyname . '%');
    $result = $RecruitCompany -> field('id,name') -> where($where) -> limit(10) -> select();
    echo json_encode($result);
  }

  public function ajaxgetmonthlytype(){
    $member_monthly = M('MemberMonthly');
    $lid = $this -> _get('id', 'intval');
    $result_tmp = $member_monthly -> field('id,months') -> where(array('lid' => $lid)) -> order('months ASC') -> select();
    $result = array();
    foreach($result_tmp as $key => $value){
      $result[] = array($value['id'], $value['months'] . '个月');
    }
    echo json_encode($result);
  }

  public function ajaxgetshoptwoclass(){
    $pid = $this -> _get('id', 'intval');
    $shop_class = M('ShopClass');
    $result_tmp = $shop_class -> field('id,name') -> where(array('pid' => $pid)) -> order('sort ASC') -> select();
    $result = array();
    foreach($result_tmp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  public function ajaxgetrankstring(){
    $where = array();
    $where['fid'] = $this -> _post('fid', 'intval');
    $where['rank'] = $this -> _post('rank', 'intval');
    $where['keyword'] = $this -> _post('keyword');
    $where['endtime'] = array('EGT', time());
    $result = M('SearchRank') -> field('MAX(endtime) as endtime, COUNT(id) as count') -> where($where) -> select();
    $result[0]['endtime']  = date('Y-m-d', $result[0]['endtime']);
    echo json_encode($result[0]);
  }

  public function ajaxgetrecommendcompanystring(){
    $where = array();
    $where['fid'] = $this -> _post('fid', 'intval');
    $where['rank'] = $this -> _post('rank', 'intval');
    $where['endtime'] = array('EGT', time());
    $result = M('RecommendCompany') -> field('MAX(endtime) as endtime, COUNT(id) as count') -> where($where) -> select();
    $result[0]['endtime']  = date('Y-m-d', $result[0]['endtime']);
    echo json_encode($result[0]);
  }

  public function shop_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('SHOP_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function media_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('MEDIA_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function store_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('STORE_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function ajaxgetselltid(){
    $result_temp = M('SellUsedType') -> field('id,name') -> where(array('pid' => $this -> _get('id', 'intval'))) -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name']);
    }
    echo json_encode($result);
  }

  public function ajaxgetchildsiteadvertpage(){
    $result_temp = M('AdvertisePage') -> field('id,remark') -> where(array('csid' => $this -> _get('id', 'intval'))) -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['remark']);
    }
    echo json_encode($result);
  }

  public function ajaxgetchildsiteadvert(){
    $result_temp = M('Advertise') -> field('id,name,width,height') -> where(array('pid' => $this -> _get('id', 'intval'), 'isopen' => 1)) -> select();
    $result = array();
    $result[] = array('', '请选择');
    foreach($result_temp as $key => $value){
      $result[] = array($value['id'], $value['name'] . '(' . $value['width'] . 'x' . $value['height'] . ')');
    }
    echo json_encode($result);
  }

  public function ajaxgetchildsiteadvertprice(){
    $result = M('AdvertMoney') -> field('id,months,marketprice,promotionprice') -> where(array('adid' => $this -> _get('id', 'intval'))) -> select();
    echo json_encode($result);
  }

  public function ajaxgetchildsiteadvertsize(){
    $result = M('Advertise') -> field('width,height') -> where(array('id' => $this -> _get('id', 'intval'))) -> find();
    echo json_encode($result);
  }

  public function sellused_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('SELLUSED_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function recruit_company_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('RECRUIT_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function ajaxgetemailtemplate(){
    echo M('BackgroundEmailTemplate') -> getFieldByid($this -> _get('id', 'intval'),'content');
  }

  public function setwebsitemap(){
    $time = time();
    $ChildSite = M('ChildSite') -> field('id,domain,name') -> select();
    foreach($ChildSite as $value){
      $content_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<urlset>";
      $content_xml .= '
<url>
<loc>http://' . $value['domain'] .  ' </loc>
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

$company_data = M('Company') -> field('id') -> where(array('delaid' => array('exp', 'is NULL'), 'csid' => $value['id'])) -> select();
    foreach($company_data as $value2){
      $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '/company/' . $value2['id'] . '</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }

    $shop_data = M('Shop') -> field('id,title') -> select();
    foreach($shop_data as $value3){
      $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '/shop/' . $value3['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }

    $ChildsiteInfoarticle = M('ChildsiteInfoarticle');
    $iaid_temp = $ChildsiteInfoarticle -> field('iaid') -> where(array('csid' => $value['id'])) -> select();
    $iaid = array();
    foreach($iaid_temp as $iaid_value){
      $iaid[] = $iaid_value['iaid'];
    }
    $article_data = M('InfoArticle') -> field('id') -> where(array('id' => array('IN', $iaid))) -> select();
    foreach($article_data as $value4){
                  $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '/article/' . $value4['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }

    $notice_data = M('Notice') -> field('id,title') -> select();
    foreach($notice_data as $value5){
       $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '/notice/' . $value5['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }

    $storerent_data = M('StoreRent') -> field('id') -> where(array('csid' => $value['id'])) -> select();
    foreach($storerent_data as $value6){
      $content_xml .= '
<url>
<loc>http://' . $value['domain'] . '/storerent/' . $value6['id'] .'</loc>
<lastmod>' . $time . '</lastmod>
<changefreq>daily</changefreq>
<priority>1.0</priority>
</url>';
    }

$content_xml .= '
</urlset>';

      $urlname = substr($value['domain'], 0, strpos($value['domain'], '.'));
      $file_xml = fopen('./sitemap/Sitemap_' . $urlname . 'yesow.xml', 'wb');
      fwrite($file_xml, $content_xml);
      fclose($file_xml);

    $sitemap_upload = M('SitemapUpload');
    $data_upload = array();
    $data_upload['csname'] = $value['name'];
    $data_upload['navnum'] = 8;
    $data_upload['companynum'] = count($company_data);
    $data_upload['shopnum'] = count($shop_data);
    $data_upload['articlenum'] = count($article_data);
    $data_upload['noticenum'] = count($notice_data);
    $data_upload['storerentnum'] = count($storerent_data);
    $data_upload['updatetime'] = time();
    $data_upload['xmlsize'] = filesize('./sitemap/Sitemap_' . $urlname . 'yesow.xml');
    $sitemap_upload -> add($data_upload);
    }

    $this -> success('网站地图更新成功', __ROOT__);
    return;
  }
}

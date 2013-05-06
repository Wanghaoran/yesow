<?php
// +-----------------------------------------------------------
// | admin 项目首页 Action
// +-----------------------------------------------------------
// | extends CommonAction
// +-----------------------------------------------------------
// | Last Update Time : 2012-11-19 00:14
// +-----------------------------------------------------------
class IndexAction extends CommonAction {

  /**
   +---------------------------------
   | 后台首页
   | @return mixed
   +---------------------------------
   */
  public function index(){
    $this -> display();
  }

  public function menu(){
    $this -> display();
  }

  //快捷操作 - 评论管理
  public function notice(){
    $where_info = array();
    $where_notice = array();
    //处理搜索
    if(!empty($_POST['content'])){
      $where_notice['nc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
      $where_info['iac.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
      $where_company['cc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
      $where_store['src.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
      $where_media['msc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
      $where_used['suc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
      $where_shop['sc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
    }
    if(!empty($_POST['author'])){
      $member = M('Member');
      $authorid = $member -> getFieldByname($this -> _post('author'), 'id');
      $where_notice['nc.mid'] = intval($authorid);
      $where_info['iac.mid'] = intval($authorid);
      $where_company['cc.mid'] = intval($authorid);
      $where_store['src.mid'] = intval($authorid);
      $where_media['msc.mid'] = intval($authorid);
      $where_used['suc.mid'] = intval($authorid);
      $where_shop['sc.mid'] = intval($authorid);
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where_notice['nc.addtime'] = array(array('gt', $addtime));
      $where_info['iac.addtime'] = array(array('gt', $addtime));
      $where_company['cc.addtime'] = array(array('gt', $addtime));
      $where_store['src.addtime'] = array(array('gt', $addtime));
      $where_media['msc.addtime'] = array(array('gt', $addtime));
      $where_used['suc.addtime'] = array(array('gt', $addtime));
      $where_shop['sc.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where_notice['nc.addtime'][] = array('lt', $endtime);
      $where_info['iac.addtime'][] = array('lt', $endtime);
      $where_company['cc.addtime'][] = array('lt', $endtime);
      $where_store['src.addtime'][] = array('lt', $endtime);
      $where_media['msc.addtime'][] = array('lt', $endtime);
      $where_used['suc.addtime'][] = array('lt', $endtime);
      $where_shop['sc.addtime'][] = array('lt', $endtime);
    }

    //资讯评论  type = 1 代表资讯
    $info = M('InfoArticle');
    $sql_info = $info -> table('yesow_info_article_comment as iac') -> field('iac.id,ia.title,iac.aid as fid,iac.floor,iac.content,m.name,iac.addtime,iac.status,0+1 as type') -> where($where_info) -> order('status ASC,iac.addtime DESC') -> join('yesow_info_article as ia ON iac.aid = ia.id') -> join('yesow_member as m ON iac.mid = m.id') -> buildSql();

    //公告评论  type = 2 代表公告
    $notice = M('NoticeComment');
    $sql_notice = $notice -> table('yesow_notice_comment as nc') -> field('nc.id,n.title,nc.nid as fid,nc.floor,nc.content,m.name,nc.addtime,nc.status,1+1 as type') -> where($where_notice) -> order('status ASC,nc.addtime DESC') -> join('yesow_notice as n ON nc.nid = n.id') -> join('yesow_member as m ON nc.mid = m.id') -> buildSql();

    //速查评论 type = 3 代表速查
    $company = M('CompanyComment');
    $sql_company = $company -> table('yesow_company_comment as cc') -> field('cc.id,c.name as title,cc.cid as fid,cc.floor,cc.content,m.name,cc.addtime,cc.status,2+1 as type') -> where($where_company) -> order('status ASC,cc.addtime DESC') -> join('yesow_company as c ON cc.cid = c.id') -> join('yesow_member as m ON cc.mid = m.id') -> buildSql();

    //旺铺租转评论 type = 4 代表旺铺
    $storesent = M('StoreRentComment');
    $sql_store = $storesent -> table('yesow_store_rent_comment as src') -> field('src.id,sr.title as title,src.srid as fid,src.floor,src.content,m.name,src.addtime,src.status,2+2 as type') -> where($where_store) -> order('status ASC,src.addtime DESC') -> join('yesow_store_rent as sr ON src.srid = sr.id') -> join('yesow_member as m ON src.mid = m.id') -> buildSql();

    //动感传媒评论 type = 5 代表传媒
    $mediashow = M('MediaShowComment');
    $sql_media = $mediashow -> table('yesow_media_show_comment as msc') -> field('msc.id,ms.name as title,msc.msid as fid,msc.floor,msc.content,m.name,msc.addtime,msc.status,3+2 as type') -> where($where_media) -> order('status ASC,msc.addtime DESC') -> join('yesow_media_show as ms ON msc.msid = ms.id') -> join('yesow_member as m ON msc.mid = m.id') -> buildSql();

    //二手出租评论 type = 6 代表二手
    $sellused = M('SellUsedComment');
    $sql_used = $sellused -> table('yesow_sell_used_comment as suc') -> field('suc.id,su.title as title,suc.suid as fid,suc.floor,suc.content,m.name,suc.addtime,suc.status,3+3 as type') -> where($where_used) -> order('status ASC,suc.addtime DESC') -> join('yesow_sell_used as su ON suc.suid = su.id') -> join('yesow_member as m ON suc.mid = m.id') -> buildSql();

    //商城评论 type = 7 代表商城
    $shop = M('ShopComment');
    $sql_shop = $shop -> table('yesow_shop_comment as sc') -> field('sc.id,s.title as title,sc.sid as fid,sc.floor,sc.content,m.name,sc.addtime,sc.status,4+3 as type') -> where($where_shop) -> order('status ASC,sc.addtime DESC') -> join('yesow_shop as s ON sc.sid = s.id') -> join('yesow_member as m ON sc.mid = m.id') -> buildSql();

    //合并查询语句
    if(empty($_POST['type'])){
      $sql = '(' . $sql_notice . ' UNION ALL ' . $sql_info . ' UNION ALL ' . $sql_company . ' UNION ALL ' . $sql_store . ' UNION ALL ' . $sql_media . ' UNION ALL ' . $sql_used . ' UNION ALL ' . $sql_shop . ')';
    }else if($_POST['type'] == 'info'){
      $sql = '(' . $sql_info . ')';
    }else if($_POST['type'] == 'notice'){
      $sql = '(' . $sql_notice . ')';
    }else if($_POST['type'] == 'company'){
      $sql = '(' . $sql_company . ')';
    }else if($_POST['type'] == 'storesent'){
      $sql = '(' . $sql_store . ')';
    }else if($_POST['type'] == 'mediashow'){
      $sql = '(' . $sql_media . ')';
    }else if($_POST['type'] == 'sellused'){
      $sql = '(' . $sql_used . ')';
    }else if($_POST['type'] == 'shop'){
      $sql = '(' . $sql_shop . ')';
    }

    //记录总数
    $count = M() -> table($sql . ' a') -> count();
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
    //合并结果集
    $result = M() -> table($sql . ' a') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('status ASC,addtime DESC') -> select();
    $this -> assign('result', $result);

    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //快捷操作 - 编辑评论
  public function editnotice(){
    //处理更新
    if(!empty($_POST['floor'])){
      list($id, $type) = explode('@@@', $_POST['id']);
      if($type == 1){
	$model = D('index://InfoArticleComment');
      }else if($type == 2){
	$model = D('index://NoticeComment');
      }else if($type == 3){
	$model = D('index://CompanyComment');
      }else if($type == 4){
	$model = D('index://StoreRentComment');
      }else if($type == 5){
	$model = D('index://MediaShowComment');
      }else if($type == 6){
	$model = D('index://SellUsedComment');
      }else if($type == 7){
	$model = D('index://ShopComment');
      }

      $_POST['id'] = $id;
      if(!$a = $model -> create()){
	$this -> error($model -> getError());
      }
      if($model -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    list($id, $type) = explode('@@@', $_GET['id']);
    //1代表资讯  2代表公告  3代表速查 4代表旺铺 5代表动感传媒 6代表二手滞销
    if($type == 1){
      $model = D('index://InfoArticle');
      $result = $model -> table('yesow_info_article_comment as iac') -> field('iac.id,ia.title,iac.floor,iac.content,m.name') -> join('yesow_info_article as ia ON iac.aid = ia.id') -> join('yesow_member as m ON iac.mid = m.id') -> where(array('iac.id' => $this -> _get('id', 'intval'))) -> find();
      
    }else if($type == 2){
      $model = D('index://NoticeComment');
      $result = $model -> table('yesow_notice_comment as nc') -> field('nc.id,n.title,nc.floor,nc.content,m.name') -> join('yesow_notice as n ON nc.nid = n.id') -> join('yesow_member as m ON nc.mid = m.id') -> where(array('nc.id' => $this -> _get('id', 'intval'))) -> find();
    }else if($type == 3){
      $model = D('index://CompanyComment');
      $result = $model -> table('yesow_company_comment as cc') -> field('cc.id,c.name as title,cc.floor,cc.content,m.name') -> join('yesow_company as c ON cc.cid = c.id') -> join('yesow_member as m ON cc.mid = m.id') -> where(array('cc.id' => $this -> _get('id', 'intval'))) -> find();
    }else if($type == 4){
      $model = D('index://StoreRentComment');
      $result = $model -> table('yesow_store_rent_comment as src') -> field('src.id,sr.title as title,src.floor,src.content,m.name') -> join('yesow_store_rent as sr ON src.srid = sr.id') -> join('yesow_member as m ON src.mid = m.id') -> where(array('src.id' => $this -> _get('id', 'intval'))) -> find();
    }else if($type == 5){
      $model = D('index://MediaShowComment');
      $result = $model -> table('yesow_media_show_comment as msc') -> field('msc.id,ms.name as title,msc.floor,msc.content,m.name') -> join('yesow_media_show as ms ON msc.msid = ms.id') -> join('yesow_member as m ON msc.mid = m.id') -> where(array('msc.id' => $this -> _get('id', 'intval'))) -> find();
    }else if($type == 6){
      $model = D('index://SellUsedComment');
      $result = $model -> table('yesow_sell_used_comment as suc') -> field('suc.id,su.title as title,suc.floor,suc.content,m.name') -> join('yesow_sell_used as su ON suc.suid = su.id') -> join('yesow_member as m ON suc.mid = m.id') -> where(array('suc.id' => $this -> _get('id', 'intval'))) -> find();
    }else if($type == 7){
      $model = D('index://ShopComment');
      $result = $model -> table('yesow_shop_comment as sc') -> field('sc.id,s.title as title,sc.floor,sc.content,m.name') -> join('yesow_shop as s ON sc.sid = s.id') -> join('yesow_member as m ON sc.mid = m.id') -> where(array('sc.id' => $this -> _get('id', 'intval'))) -> find();
    }

    $this -> assign('result', $result);
    $this -> display();
  }

  //快捷操作 - 删除评论
  public function delnotice(){
    $notice_del = array();
    $info_del = array();
    foreach(explode(',', $_POST['ids']) as $value){
      list($id, $type) = explode('@@@', $value);
      if($type == 1){
	$info_del[] = $id;
      }else if($type == 2){
	$notice_del[] = $id;
      }else if($type == 3){
	$company_del[] = $id;
      }else if($type == 4){
	$store_del[] = $id;
      }else if($type == 5){
	$media_del[] = $id;
      }else if($type == 6){
	$used_del[] = $id;
      }else if($type == 7){
	$shop_del[] = $id;
      }
    }
    $num = 0;
    //分别删除5张表
    $num += M('InfoArticleComment') -> where(array('id' => array('in', $info_del))) -> delete();
    $num += M('NoticeComment') -> where(array('id' => array('in', $notice_del))) -> delete();
    $num += M('CompanyComment') -> where(array('id' => array('in', $company_del))) -> delete();
    $num += M('StoreRentComment') -> where(array('id' => array('in', $store_del))) -> delete();
    $num += M('MediaShowComment') -> where(array('id' => array('in', $media_del))) -> delete();
    $num += M('SellUsedComment') -> where(array('id' => array('in', $used_del))) -> delete();
    $num += M('ShopComment') -> where(array('id' => array('in', $shop_del))) -> delete();
    if($num != 0){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //快捷操作 - 通过审核评论
  public function passauditnotice(){
    $notice_pass = array();
    $info_pass = array();
    foreach(explode(',', $_POST['ids']) as $value){
      list($id, $type) = explode('@@@', $value);
      if($type == 1){
	$info_pass[] = $id;
      }else if($type == 2){
	$notice_pass[] = $id;
      }else if($type == 3){
	$company_pass[] = $id;
      }else if($type == 4){
	$store_pass[] = $id;
      }else if($type == 5){
	$media_pass[] = $id;
      }else if($type == 6){
	$used_pass[] = $id;
      }else if($type == 7){
	$shop_pass[] = $id;
      }
    }
    $data = array('status' => 2);
    $num = 0;
    //分别更新5张表
    $num += M('InfoArticleComment') -> where(array('id' => array('in', $info_pass))) -> save($data);
    $num += M('NoticeComment') -> where(array('id' => array('in', $notice_pass))) -> save($data);
    $num += M('CompanyComment') -> where(array('id' => array('in', $company_pass))) -> save($data);
    $num += M('StoreRentComment') -> where(array('id' => array('in', $store_pass))) -> save($data);
    $num += M('MediaShowComment') -> where(array('id' => array('in', $media_pass))) -> save($data);
    $num += M('SellUsedComment') -> where(array('id' => array('in', $used_pass))) -> save($data);
    $num += M('ShopComment') -> where(array('id' => array('in', $shop_pass))) -> save($data);
    if($num != 0){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //快捷操作 - 不通过审核评论
  public function nopassauditnotice(){
    $notice_pass = array();
    $info_pass = array();
    foreach(explode(',', $_POST['ids']) as $value){
      list($id, $type) = explode('@@@', $value);
      if($type == 1){
	$info_pass[] = $id;
      }else if($type == 2){
	$notice_pass[] = $id;
      }else if($type == 3){
	$company_pass[] = $id;
      }else if($type == 4){
	$store_pass[] = $id;
      }else if($type == 5){
	$media_pass[] = $id;
      }else if($type == 6){
	$used_pass[] = $id;
      }else if($type == 7){
	$shop_pass[] = $id;
      }
    }
    $data = array('status' => 1);
    $num = 0;
    //分别更新5张表
    $num += M('InfoArticleComment') -> where(array('id' => array('in', $info_pass))) -> save($data);
    $num += M('NoticeComment') -> where(array('id' => array('in', $notice_pass))) -> save($data);
    $num += M('CompanyComment') -> where(array('id' => array('in', $company_pass))) -> save($data);
    $num += M('StoreRentComment') -> where(array('id' => array('in', $store_pass))) -> save($data);
    $num += M('MediaShowComment') -> where(array('id' => array('in', $media_pass))) -> save($data);
    $num += M('SellUsedComment') -> where(array('id' => array('in', $used_pass))) -> save($data);
    $num += M('ShopComment') -> where(array('id' => array('in', $shop_pass))) -> save($data);
    if($num != 0){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //文件上传，整合kindeditor
  public function uploadfile(){
    //文件保存目录路径
    $save_path = $php_path . C('UPLOAD_PATH') . '/attached/';
    //文件保存目录URL
    $save_url = $php_url . C('SAVE_PATH') . '/attached/';
    //定义允许上传的文件扩展名
    $ext_arr = array(
      'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
      'flash' => array('swf', 'flv'),
      'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
      'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
    );
    //最大文件大小
    $max_size = 1000000;
    $save_path = realpath($save_path) . '/';
    //PHP上传失败
    if (!empty($_FILES['imgFile']['error'])) {
      switch($_FILES['imgFile']['error']){
      case '1':
	$error = '超过php.ini允许的大小。';
	break;
      case '2':
	$error = '超过表单允许的大小。';
	break;
      case '3':
	$error = '图片只有部分被上传。';
	break;
      case '4':
	$error = '请选择图片。';
	break;
      case '6':
	$error = '找不到临时目录。';
	break;
      case '7':
	$error = '写文件到硬盘出错。';
	break;
      case '8':
	$error = 'File upload stopped by extension。';
	break;
      case '999':
      default:
	$error = '未知错误。';
      }
      $this -> alert($error);
    }
    //有上传文件时
    if (empty($_FILES) === false) {
    //原文件名
    $file_name = $_FILES['imgFile']['name'];
    //服务器上临时文件名
    $tmp_name = $_FILES['imgFile']['tmp_name'];
    //文件大小
    $file_size = $_FILES['imgFile']['size'];
    //检查文件名
    if (!$file_name) {
      $this -> alert("请选择文件。");
    }
    //检查目录
    if (@is_dir($save_path) === false) {
      $this -> alert("上传目录不存在。");
    }
    //检查目录写权限
    if (@is_writable($save_path) === false) {
      $this -> alert("上传目录没有写权限。");
    }
    //检查是否已上传
    if (@is_uploaded_file($tmp_name) === false) {
      $this -> alert("上传失败。");
    }
    //检查文件大小
    if ($file_size > $max_size) {
      $this -> alert("上传文件大小超过限制。");
    }
    //检查目录名
    $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
    if (empty($ext_arr[$dir_name])) {
      $this -> alert("目录名不正确。");
    }
    //获得文件扩展名
    $temp_arr = explode(".", $file_name);
    $file_ext = array_pop($temp_arr);
    $file_ext = trim($file_ext);
    $file_ext = strtolower($file_ext);
    //检查扩展名
    if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
      $this -> alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
    }
    //创建文件夹
    if ($dir_name !== '') {
      $save_path .= $dir_name . "/";
      $save_url .= $dir_name . "/";
      if (!file_exists($save_path)) {
	mkdir($save_path);
      }
    }
    $ymd = date("Ymd");
    $save_path .= $ymd . "/";
    $save_url .= $ymd . "/";
    if (!file_exists($save_path)) {
      mkdir($save_path);
    }
    //新文件名
    $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
    //移动文件
    $file_path = $save_path . $new_file_name;
    if (move_uploaded_file($tmp_name, $file_path) === false) {
      $this -> alert("上传文件失败。");
    }
    @chmod($file_path, 0644);
    $file_url = $save_url . $new_file_name;
    header('Content-type: text/html; charset=UTF-8');
    Vendor('JSON');
    $json = new Services_JSON();
    echo $json->encode(array('error' => 0, 'url' => $file_url));
    exit;
    }
  }

    //kindeditor
    public function alert($msg) {
      header('Content-type: text/html; charset=UTF-8');
      Vendor('JSON');
      $json = new Services_JSON();
      echo $json->encode(array('error' => 1, 'message' => $msg));
      exit;
    }
}

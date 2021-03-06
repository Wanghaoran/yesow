<?php
class IndexAction extends CommonAction {

  //首页
  public function index(){
    $this -> gettitlenotice();
    $this -> getyesownotice();
    $this -> newcompany();
    $this -> recommendcompany();
    $this -> qqonlinecompany();
    $this -> showcompany();
    $this -> mediacompany();
    $this -> companytype();
    $this -> seo();
    $this -> imagetab();
    $this -> shop();
    R('Public/getstorerent_rent');
    R('Public/getstorerent_price');
    R('Public/getsellused_sell');
    R('Public/getsellused_buy');
    R('Public/getnewarticle');
    R('Public/company_recruit');
    $this -> display();
  }

  public function noticelist(){
    R('Index/gettitlenotice');
    $notice = M('Notice');

    import("ORG.Util.Page");
    $count = $notice -> count('id');
    $page = new Page($count,17);
    $show = $page -> show();

    $result = $notice -> field('id,title,titleattribute') -> order('addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);

    $this -> display();
  }

  public function notice(){
    $notice = M('Notice');
    $id = $this -> _get('id', 'intval');
    $notice -> where(array('id' => $id)) -> setInc('clickcount');
    $result_hotnotice = $notice -> field('id,title,titleattribute,addtime') -> order('clickcount DESC') -> limit(15) -> select();
    $this -> assign('result_hotnotice', $result_hotnotice);
    $result = $notice -> field('title,keywords,content,addtime,source,clickcount') -> find($id);
    $this -> assign('result', $result);
    $where_similar = array();
    foreach(explode(' ', $result['keywords']) as $value){
      $where_similar['keywords'][] = array('LIKE', '%' . $value . '%');
    }
    $where_similar['keywords'][] = 'or';
    $result_similarnotice = $notice -> field('id,title,titleattribute,addtime') -> where($where_similar) -> order('addtime DESC') -> limit(15) -> select();
    $this -> assign('result_similarnotice', $result_similarnotice);
    $comment_where = array();
    $comment_where = "nc.nid={$id} and nc.status=2";
    if(M('MemberSetup') -> getFieldByname('viewcomment', 'value') == 1 && isset($_SESSION[C('USER_AUTH_KEY')])){
      $sid = session(C('USER_AUTH_KEY'));
      $where_setup = "nc.nid={$id} AND nc.mid={$sid}";
      $comment_where = '(' . $comment_where . ')' . 'OR' . '(' . $where_setup . ')';
    }
    import("ORG.Util.Page");// 导入分页类
    $count = $notice -> table('yesow_notice_comment as nc') -> where($comment_where) -> count('id');
    $page = new Page($count, 5);//每页5条
    $page->setConfig('header','条评论');
    $show = $page -> show();
    $result_comment = $notice -> table('yesow_notice_comment as nc') -> field('m.name,nc.content,nc.addtime,nc.floor') -> where($comment_where) -> join('yesow_member as m ON nc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('floor ASC') -> select();
    $this -> assign('result_comment', $result_comment);
    $this -> assign('show', $show);

    $this -> display();
  }

  public function gettitlenotice(){
    if(S('index_title_notice')){
      $this -> assign('index_title_notice', S('index_title_notice'));
    }else{
      $result = M('TitleNotice') -> table('yesow_title_notice as tn') -> field('tn.title,tnt.name as tname') -> join('yesow_title_notice_type as tnt ON tn.tid = tnt.id') -> order('tn.addtime DESC') -> limit(10) -> select();
      S('index_title_notice', $result);
      $this -> assign('index_title_notice', $result);
    }
  }

  private function getyesownotice(){
    if(S('index_yesow_notice')){
      $this -> assign('index_yesow_notice', S('index_yesow_notice'));
    }else{
      $result = M('Notice') -> field('id,title,titleattribute,addtime') -> order('addtime DESC') -> limit(10) -> select();
      S('index_yesow_notice', $result);
      $this -> assign('index_yesow_notice', $result);
    }
  }

  private function newcompany(){
    $company = M('Company');
    $where = array();
    if($csid = D('admin://ChildSite') -> getid()){
      $where['csid'] = $csid;
    }
    $where['delaid'] = array('exp', 'is NULL');
    $new_company = $company -> field('id,name,updatetime') -> order('updatetime DESC') -> where($where) -> limit(20) -> select();
    $this -> assign('new_company', $new_company);
  }

  public function commit(){
    if($this -> _post('code', 'md5') != $_SESSION['verify']){
      $this -> error(L('VERIFY_ERROR'));
    }
    $comment = D('NoticeComment');
    $data['nid'] = $this -> _post('nid', 'intval');
    $data['mid'] = isset($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
    $data['content'] = $this -> _post('content');
    if(!$comment -> create($data)){
      $this -> error($comment -> getError());
    }
    if($comment -> add()){
      $this -> success(L('ARTICLE_COMMIT_ADD_SUCCESS'));
    }else{
      $this -> error(L('ARTICLE_COMMIT_ADD_ERROR'));
    }
  }

  public function aboutus(){
    $aboutus = M('Aboutus');
    $result_title = $aboutus -> field('id,title') -> order('sort ASC') -> select();
    $this -> assign('result_title', $result_title);
    $id = isset($_GET['id']) ? $_GET['id'] : $result_title[0]['id'];
    $this -> assign('id', $id);
    $result_content = $aboutus -> field('title,content') -> find($id);
    $this -> assign('result_content', $result_content);
    $this -> display();
  }

  public function childsite(){
    if(S('index_child_site')){
      $this -> assign('index_child_site', S('index_child_site'));
    }else{
      $result = M('Area') -> field('id,name') -> where(array('name' => array('neq', '主站'))) -> select();
      $childsite = M('ChildSite');
      foreach($result as $key => $value){
	$result[$key]['childsite'] = $childsite -> field('domain,name') -> where(array('aid' => $value['id'])) -> select();
      }
      S('index_child_site', $result);
      $this -> assign('index_child_site', $result);
    }
  }

  private function recommendcompany(){
    $website_type_name = D('admin://ChildSite') -> getid() ? '分站' : '主站';
    $csid = D('admin://ChildSite') -> getid();
    $fid = M('RecommendCompanyWebsiteType') -> getFieldByname($website_type_name, 'id');
    $this -> assign('fid', $fid);
    $RecommendCompany = M('RecommendCompany');
    $where_recommendcompany = array();
    $where_recommendcompany['rc.fid'] = $fid;
    if($csid){
      $where_recommendcompany['c.csid'] = $csid;
    }
    $where_recommendcompany['rc.starttime'] = array('ELT', time());
    $where_recommendcompany['rc.endtime'] = array('EGT', time());
    $result_recommendcompany = $RecommendCompany -> alias('rc') -> field('rc.cid,rc.rank,c.name as cname,c.manproducts,c.mobilephone,c.linkman') -> join('yesow_company as c ON rc.cid = c.id') -> where($where_recommendcompany) -> order('rc.rank ASC') -> select();
    $recommend_company = array();
    for($i=1; $i<=32; $i++){
      $recommend_company[$i] = array();
      foreach($result_recommendcompany as $value){
	if($i == $value['rank']){
	  $recommend_company[$i] = array('cid' => $value['cid'], 'cname' => $value['cname'], 'manproducts' => $value['manproducts'], 'mobilephone' => $value['mobilephone'], 'linkman' => $value['linkman']);
	}
      }
    }
    $this -> assign('recommend_company', $recommend_company);
  }

  private function qqonlinecompany(){
    $company = M('Company');
    $qqonline_company = $company -> field('id,name') -> order('updatetime DESC') -> where(array('delaid' => array('exp', 'is NULL'), 'csid' => 22)) -> limit(20) -> select();
    $this -> assign('qqonline_company', $qqonline_company);
  }

  private function showcompany(){
    $company = M('Company');
    $show_company = $company -> field('id,name') -> order('id DESC') -> where(array('delaid' => array('exp', 'is NULL'), 'csid' => 21)) -> limit(20) -> select();
    $this -> assign('show_company', $show_company);
  }

  private function mediacompany(){
    $where = array();
    if($csid = D('admin://ChildSite') -> getid()){
      $where['csid'] = $csid;
    }
    $where['ischeck'] = 1;
    $mediashow = M('MediaShow');
    $media_company = $mediashow -> field('id,name') -> limit(20) -> where($where) -> order('sort ASC') -> select();
    $this -> assign('media_company', $media_company);
  }

  private function companytype(){

    if(S('index_service_content')){
      $this -> assign('service_result', S('index_service_content'));
    }else{
      $ServiceContent = M('ServiceContent');
      $service_result = $ServiceContent -> field('id,name,remark') -> where(array('pid' => 0)) -> limit(15) -> order('sort ASC') -> select();
      foreach($service_result as $key => $value){
	$service_result[$key]['two'] = $ServiceContent -> field('id,name') -> where(array('pid' => $value['id'])) -> order('sort ASC') -> select();
	foreach($service_result[$key]['two'] as $key2 => $value2){
	  $service_result[$key]['two'][$key2]['three'] = $ServiceContent -> field('id,name,url') -> where(array('pid' => $value2['id'])) -> order('sort ASC') -> select();
	}
      }
      $this -> assign('service_result', $service_result);
      S('index_service_content', $service_result);
    }
    
  }

  private function seo(){
    if(S('index_seo')){
      $this -> assign('index_seo', S('index_seo'));
    }else{
      $system = M('System');
      $result = $system -> field('name,value') -> where(array('name' => array('exp', "in ('title','keywords','description')"))) -> select();
      S('index_seo', $index_seo);
      $this -> assign('index_seo', $result);
    }
    $childsite_name = D('admin://ChildSite') -> getname();
    $this -> assign('childsite_name', $childsite_name);
  }

  public function imagetab(){
    $article_pic = M('InfoArticlePic');
    $result_pic = $article_pic -> table('yesow_info_article_pic as iap') -> field('iap.aid,iap.address,ia.title as title') -> where(array('iap.isshow_index' => 1)) -> order('iap.addtime DESC') -> limit(6) -> join('yesow_info_article as ia ON iap.aid = ia.id') -> select();
    $this -> assign('result_pic', $result_pic);
  }

  private function shop(){
    if(S('index_shop')){
      $this -> assign('index_shop', S('index_shop'));
    }else{
      $shopclass = M('ShopClass');
      $shop = M('Shop');
      $index_shop = $shopclass -> field('id,name') -> where('pid=0') -> select();
      foreach($index_shop as $key => $value){
	$index_shop[$key]['shop'] = $shop -> field('id,title,small_pic') -> where(array('cid_one' => $value['id'])) -> order('addtime DESC') -> limit(7) -> select();
      }
      S('index_shop', $index_shop);
      $this -> assign('index_shop', $index_shop);
    }
  }

  public function applylink(){
    if(!empty($_POST['name'])){
      $applylink = D('LinkApply');
      if(!$applylink -> create()){
	R('Public/errorjump',array($applylink -> getError()));
      }
      if($applylink -> add()){
	echo '<script>alert("感谢您对易搜的支持！您所提交的数据我们将在36小时内给予审核后通过！多谢您的合作！");location.href="'.__ACTION__.'";</script>';
	exit();
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
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

  public function dgcm(){
    $where = array();
    if($csid = D('admin://ChildSite') -> getid()){
      $where['csid'] = $csid;
    }
    $where['ischeck'] = 1;
    if(!empty($_POST['keyword'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('keyword') . '%');
    }
    $mediashow = M('MediaShow');

    import("ORG.Util.Page");// 导入分页类
    $count = $mediashow -> where($where) -> count('id');
    $page = new Page($count, 10);
    $show = $page -> show();

    $result = $mediashow -> field('id,name,remark,image,qqcode') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('sort ASC,updatetime DESC') -> select();
    foreach($result as $key => $value){
      $result[$key]['qqarr'] = explode(':', $value['qqcode']);
    }
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    
    $where_hot = array();
    if($csid){
      $where_hot['ms.csid'] = $csid;
      $where_hot['ms.ischeck'] = 1;
    }
    $result_hot = $mediashow -> table('yesow_media_show as ms') -> field('ms.id,cs.name as csname,ms.name,ms.updatetime') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where($where_hot) -> limit(24) -> order('ms.clickcount DESC') -> select();
    $this -> assign('result_hot', $result_hot);
    $mediashow_comment = M('MediaShowComment');
    $result_comment = $mediashow_comment -> table('yesow_media_show_comment as msc') -> field('ms.id,msc.content,msc.addtime,cs.name as csname') -> join('yesow_media_show as ms ON msc.msid = ms.id') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where(array('msc.status' => 2)) -> limit(24) -> order('msc.addtime DESC') -> select();
    $this -> assign('result_comment', $result_comment);
    $this -> display();
  }

  public function dgcminfo(){
    $id = $this -> _get('id', 'intval');
    $mediashow = M('MediaShow');
    $mediashow -> where(array('id' => $id)) -> setInc('clickcount');
    $result = $mediashow -> table('yesow_media_show as ms') -> field('ms.name,ms.content,ms.keyword,cs.name as csname,ms.linkman,ms.mobliephone,ms.companyphone') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where(array('ms.id' => $id)) -> find();
    $this -> assign('result', $result);

    $key_arr = explode(' ', $result['keyword']);
    $about_where = '';
    foreach($key_arr as $value){
      if(empty($about_where)){
	$about_where .="(( ms.keyword LIKE '%{$value}%' )";
      }else{
	$about_where .=" OR ( ms.keyword LIKE '%{$value}%' )";
      }
    }
    $about_where .= ") AND (ms.id != {$id})";
    $about_article = $mediashow -> table('yesow_media_show as ms') -> field('ms.id,cs.name as csname,ms.name,ms.updatetime') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where($about_where) -> order('ms.updatetime DESC') -> limit(10) -> select();
    $this -> assign('about_article', $about_article);

    $today_update = $mediashow -> table('yesow_media_show as ms') -> field('ms.id,cs.name as csname,ms.name,ms.updatetime') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where(array('ms.id' => array('neq', $id))) -> order('ms.updatetime DESC') -> limit(10) -> select();
    $this -> assign('today_update', $today_update);

    $hot_article = $mediashow -> table('yesow_media_show as ms') -> field('ms.id,cs.name as csname,ms.name,ms.updatetime') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where(array('ms.id' => array('neq', $id))) -> order('ms.clickcount DESC') -> limit(10) -> select();
    $this -> assign('hot_article', $hot_article);

    $comment = M('MediaShowComment');
    $comment_where = "msc.msid={$id} AND msc.status=2";
    if(M('MemberSetup') -> getFieldByname('viewcomment', 'value') == 1 && isset($_SESSION[C('USER_AUTH_KEY')])){
      $sid = session(C('USER_AUTH_KEY'));
      $where_setup = "msc.msid={$id} AND msc.mid={$sid}";
      $comment_where = '(' . $comment_where . ')' . 'OR' . '(' . $where_setup . ')';
    }
    import("ORG.Util.Page");// 导入分页类
    $count = $comment -> table('yesow_media_show_comment as msc') -> where($comment_where) -> count();
    $page = new Page($count, 10);//每页10条
    $page->setConfig('header','条评论');
    $show = $page -> show();
    $result_comment = $comment -> table('yesow_media_show_comment as msc') -> field('m.name,msc.content,msc.addtime,msc.floor,msc.face') -> where($comment_where) -> join('yesow_member as m ON msc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('floor ASC') -> select();
    $this -> assign('result_comment', $result_comment);
    $this -> assign('show', $show);

    $this -> display();
  }

  public function companyshowcomment(){
    if($this -> _post('code', 'md5') != $_SESSION['verify']){
      $this -> error(L('VERIFY_ERROR'));
    }
    $commit = D('MediaShowComment');
    $data['msid'] = $this -> _post('msid', 'intval');
    $data['mid'] = isset($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
    $data['content'] = $this -> _post('content');
    $data['face'] = $this -> _post('face');
    if(!$commit -> create($data)){
      $this -> error($commit -> getError());
    }
    if($commit -> add()){
      $this -> success(L('ARTICLE_COMMIT_ADD_SUCCESS'));
    }else{
      $this -> error(L('ARTICLE_COMMIT_ADD_ERROR'));
    }
  }
  
}

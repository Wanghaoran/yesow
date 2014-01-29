<?php
class IndexAction extends CommonAction {

  //首页
  public function index(){
    //数据更新消息
    $this -> gettitlenotice();
    //易搜公告动态
    $this -> getyesownotice();
    //最新IT商家
    $this -> newcompany();
    //推荐商家
    $this -> recommendcompany();
    //在线QQ
    $this -> qqonlinecompany();
    //商家风采
    $this -> showcompany();
    //动感传媒
    $this -> mediacompany();
    //服务内容
    $this -> companytype();
    //SEO信息
    $this -> seo();
    //图片幻灯
    $this -> imagetab();
    //易搜商城
    $this -> shop();
    //旺铺出租
    R('Public/getstorerent_rent');
    //旺铺求租
    R('Public/getstorerent_price');
    //二手出售
    R('Public/getsellused_sell');
    //二手求购
    R('Public/getsellused_buy');
    //最新渠道动态
    R('Public/getnewarticle');
    //企业招聘
    R('Public/company_recruit');
    $this -> display();
  }

  //站点公告列表
  public function noticelist(){
    //数据更新消息
    R('Index/gettitlenotice');
    $notice = M('Notice');

    import("ORG.Util.Page");
    $count = $notice -> count('id');
    $page = new Page($count,17);
    $show = $page -> show();

    //公告列表
    $result = $notice -> field('id,title,titleattribute') -> order('addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);

    $this -> display();
  }

  //站点公告详情
  public function notice(){
    $notice = M('Notice');
    $id = $this -> _get('id', 'intval');
    //点击量加一
    $notice -> where(array('id' => $id)) -> setInc('clickcount');
    //热门公告
    $result_hotnotice = $notice -> field('id,title,titleattribute,addtime') -> order('clickcount DESC') -> limit(15) -> select();
    $this -> assign('result_hotnotice', $result_hotnotice);
    //结果
    $result = $notice -> field('title,keywords,content,addtime,source,clickcount') -> find($id);
    $this -> assign('result', $result);
    //同类公告
    $where_similar = array();
    foreach(explode(' ', $result['keywords']) as $value){
      $where_similar['keywords'][] = array('LIKE', '%' . $value . '%');
    }
    $where_similar['keywords'][] = 'or';
    $result_similarnotice = $notice -> field('id,title,titleattribute,addtime') -> where($where_similar) -> order('addtime DESC') -> limit(15) -> select();
    $this -> assign('result_similarnotice', $result_similarnotice);
    //读取评论
    $comment_where = array();
    $comment_where = "nc.nid={$id} and nc.status=2";
    //如果会员基本设置允许会员看到自己的未经审核的评论，则在这里加上查询条件
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

  //获取标题公告
  public function gettitlenotice(){
    if(S('index_title_notice')){
      $this -> assign('index_title_notice', S('index_title_notice'));
    }else{
      $result = M('TitleNotice') -> table('yesow_title_notice as tn') -> field('tn.title,tnt.name as tname') -> join('yesow_title_notice_type as tnt ON tn.tid = tnt.id') -> order('tn.addtime DESC') -> limit(10) -> select();
      S('index_title_notice', $result);
      $this -> assign('index_title_notice', $result);
    }
  }

  //获取易搜公告
  private function getyesownotice(){
    if(S('index_yesow_notice')){
      $this -> assign('index_yesow_notice', S('index_yesow_notice'));
    }else{
      $result = M('Notice') -> field('id,title,titleattribute,addtime') -> order('addtime DESC') -> limit(10) -> select();
      S('index_yesow_notice', $result);
      $this -> assign('index_yesow_notice', $result);
    }
  }

  //最新IT商家
  private function newcompany(){
    $company = M('Company');
    $where = array();
    //判断是否是分站
    if($csid = D('admin://ChildSite') -> getid()){
      $where['csid'] = $csid;
    }
    $where['delaid'] = array('exp', 'is NULL');
    $new_company = $company -> field('id,name,updatetime') -> order('updatetime DESC') -> where($where) -> limit(20) -> select();
    $this -> assign('new_company', $new_company);
  }

  //提交公告评论
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

  //关于我们
  public function aboutus(){
    $aboutus = M('Aboutus');
    //查询所有标题
    $result_title = $aboutus -> field('id,title') -> order('sort ASC') -> select();
    $this -> assign('result_title', $result_title);
    //如果没传入id,默认取第一个
    $id = isset($_GET['id']) ? $_GET['id'] : $result_title[0]['id'];
    $this -> assign('id', $id);
    //读取该id对应的内容
    $result_content = $aboutus -> field('title,content') -> find($id);
    $this -> assign('result_content', $result_content);
    $this -> display();
  }

  //分站信息
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

  //推荐商家
  private function recommendcompany(){
    //站点类型
    $website_type_name = D('admin://ChildSite') -> getid() ? '分站' : '主站';
    $fid = M('RecommendCompanyWebsiteType') -> getFieldByname($website_type_name, 'id');
    $this -> assign('fid', $fid);
    //查询
    $RecommendCompany = M('RecommendCompany');
    $where_recommendcompany = array();
    $where_recommendcompany['fid'] = $fid;
    $where_recommendcompany['starttime'] = array('ELT', time());
    $where_recommendcompany['endtime'] = array('EGT', time());
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

  //在线QQ
  private function qqonlinecompany(){
    $company = M('Company');
    $qqonline_company = $company -> field('id,name') -> order('updatetime DESC') -> where(array('delaid' => array('exp', 'is NULL'), 'csid' => 22)) -> limit(20) -> select();
    $this -> assign('qqonline_company', $qqonline_company);
  }

  //商家风采
  private function showcompany(){
    $company = M('Company');
    $show_company = $company -> field('id,name') -> order('id DESC') -> where(array('delaid' => array('exp', 'is NULL'), 'csid' => 21)) -> limit(20) -> select();
    $this -> assign('show_company', $show_company);
  }

  //动感传媒
  private function mediacompany(){
    $where = array();
    //先获取分站id
    if($csid = D('admin://ChildSite') -> getid()){
      $where['csid'] = $csid;
    }
    $where['ischeck'] = 1;
    $mediashow = M('MediaShow');
    $media_company = $mediashow -> field('id,name') -> limit(20) -> where($where) -> order('sort ASC') -> select();
    $this -> assign('media_company', $media_company);
  }

  //服务内容分类
  private function companytype(){

    if(S('index_service_content')){
      $this -> assign('service_result', S('index_service_content'));
    }else{
      $ServiceContent = M('ServiceContent');
      //one
      $service_result = $ServiceContent -> field('id,name,remark') -> where(array('pid' => 0)) -> limit(15) -> order('sort ASC') -> select();
      //two
      foreach($service_result as $key => $value){
	$service_result[$key]['two'] = $ServiceContent -> field('id,name') -> where(array('pid' => $value['id'])) -> order('sort ASC') -> select();
	//three
	foreach($service_result[$key]['two'] as $key2 => $value2){
	  $service_result[$key]['two'][$key2]['three'] = $ServiceContent -> field('id,name,url') -> where(array('pid' => $value2['id'])) -> order('sort ASC') -> select();
	}
      }
      $this -> assign('service_result', $service_result);
      S('index_service_content', $service_result);
    }
    
  }

  //SEO
  private function seo(){
    if(S('index_seo')){
      $this -> assign('index_seo', S('index_seo'));
    }else{
      //查询SEO结果
      $system = M('System');
      $result = $system -> field('name,value') -> where(array('name' => array('exp', "in ('title','keywords','description')"))) -> select();
      S('index_seo', $index_seo);
      $this -> assign('index_seo', $result);
    }
    //查询分站名
    $childsite_name = D('admin://ChildSite') -> getname();
    $this -> assign('childsite_name', $childsite_name);
  }

  public function imagetab(){
    //图片幻灯
    $article_pic = M('InfoArticlePic');
    $result_pic = $article_pic -> table('yesow_info_article_pic as iap') -> field('iap.aid,iap.address,ia.title as title') -> where(array('iap.isshow_index' => 1)) -> order('iap.addtime DESC') -> limit(6) -> join('yesow_info_article as ia ON iap.aid = ia.id') -> select();
    $this -> assign('result_pic', $result_pic);
  }

  //易搜商城
  private function shop(){
    if(S('index_shop')){
      $this -> assign('index_shop', S('index_shop'));
    }else{
      //查询SEO结果
      $shopclass = M('ShopClass');
      $shop = M('Shop');
      //查询一级分类
      $index_shop = $shopclass -> field('id,name') -> where('pid=0') -> select();
      foreach($index_shop as $key => $value){
	$index_shop[$key]['shop'] = $shop -> field('id,title,small_pic') -> where(array('cid_one' => $value['id'])) -> order('addtime DESC') -> limit(7) -> select();
      }
      S('index_shop', $index_shop);
      $this -> assign('index_shop', $index_shop);
    }
  }

  //申请友链
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
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询网站类型
    $LinkWebsiteType = M('LinkWebsiteType');
    $result_website_type = $LinkWebsiteType -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_website_type', $result_website_type);
    $this -> display();
  }

  //动感传媒
  public function dgcm(){
    $where = array();
    //先获取分站id
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
    //获取在线qq
    foreach($result as $key => $value){
      $result[$key]['qqarr'] = explode(':', $value['qqcode']);
    }
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    
    //热门商家（读取点击量倒序的24条信息）
    $where_hot = array();
    if($csid){
      $where_hot['ms.csid'] = $csid;
      $where_hot['ms.ischeck'] = 1;
    }
    $result_hot = $mediashow -> table('yesow_media_show as ms') -> field('ms.id,cs.name as csname,ms.name,ms.updatetime') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where($where_hot) -> limit(24) -> order('ms.clickcount DESC') -> select();
    $this -> assign('result_hot', $result_hot);
    //最新评论（24条最新评论）
    $mediashow_comment = M('MediaShowComment');
    $result_comment = $mediashow_comment -> table('yesow_media_show_comment as msc') -> field('ms.id,msc.content,msc.addtime,cs.name as csname') -> join('yesow_media_show as ms ON msc.msid = ms.id') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where(array('msc.status' => 2)) -> limit(24) -> order('msc.addtime DESC') -> select();
    $this -> assign('result_comment', $result_comment);
    $this -> display();
  }

  //动感传媒详细页
  public function dgcminfo(){
    $id = $this -> _get('id', 'intval');
    $mediashow = M('MediaShow');
    //点击量加一
    $mediashow -> where(array('id' => $id)) -> setInc('clickcount');
    $result = $mediashow -> table('yesow_media_show as ms') -> field('ms.name,ms.content,ms.keyword,cs.name as csname,ms.linkman,ms.mobliephone,ms.companyphone') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where(array('ms.id' => $id)) -> find();
    $this -> assign('result', $result);

    //相关文章
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

    //今日更新
    $today_update = $mediashow -> table('yesow_media_show as ms') -> field('ms.id,cs.name as csname,ms.name,ms.updatetime') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where(array('ms.id' => array('neq', $id))) -> order('ms.updatetime DESC') -> limit(10) -> select();
    $this -> assign('today_update', $today_update);

    //热门文章
    $hot_article = $mediashow -> table('yesow_media_show as ms') -> field('ms.id,cs.name as csname,ms.name,ms.updatetime') -> join('yesow_child_site as cs ON ms.csid = cs.id') -> where(array('ms.id' => array('neq', $id))) -> order('ms.clickcount DESC') -> limit(10) -> select();
    $this -> assign('hot_article', $hot_article);

    $comment = M('MediaShowComment');
    //读取评论
    $comment_where = "msc.msid={$id} AND msc.status=2";
    //如果会员基本设置允许会员看到自己的未经审核的评论，则在这里加上查询条件
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

  //动感传媒提交评论
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

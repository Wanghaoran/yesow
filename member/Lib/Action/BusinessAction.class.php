<?php
class BusinessAction extends CommonAction {

  //首页前置操作
  public function _before_index(){
    //获取公告
    if(S('index_yesow_notice')){
      $this -> assign('index_yesow_notice', S('index_yesow_notice'));
    }else{
      $result = M('Notice') -> field('id,title,titleattribute,addtime') -> order('addtime DESC') -> limit(10) -> select();
      S('index_yesow_notice', $result);
      $this -> assign('index_yesow_notice', $result);
    }
  }

  //首页
  public function index(){
    $this -> display(); 
  }

  //资讯文章前置管理
  public function _before_articlemange(){
    $this -> _before_index();
  }

  //资讯文章管理
  public function articlemange(){
    $this -> display();
  }

  //添加文章
  public function addarticle(){
    if(!empty($_POST['title'])){
      $info_article = D('admin://InfoArticle');
      $_POST['authorid'] = $this -> _session(C('USER_AUTH_KEY'));
      $_POST['addtime'] = time();
      if(!empty($_POST['conid2'])){
	$_POST['conid'] = $_POST['conid2'];
	unset($_POST['conid2']);
      }

       if(!$info_article -> create()){
	R('Register/errorjump',array($info_article -> getError()));
       }
           
      if($iaid = $info_article -> add()){
	//使用文章id写文章分站表
	if(!empty($_POST['childsite'])){
	  $childsite_infoatricle = M('ChildsiteInfoarticle');
	  $data = array();
	  $data['iaid'] = $iaid;
	  foreach($_POST['childsite'] as $value){
	    $data['csid'] = $value;
	    $childsite_infoatricle -> add($data);
	  }
	}

	//提取文章图片，写入文章图片表
	if(preg_match_all("/<img.*src\s*=\s*[\"|\']?\s*([^>\"\'\s]*)/i", str_ireplace("\\","",$_POST['content']), $arr)){
	  $infoarticlepic = M('InfoArticlePic');
	  $data = array();
	  $data['aid'] = $iaid;
	  $data['colid'] = $this -> _post('colid', 'intval');
	  foreach($arr[1] as $value){
	    $data['address'] = $value;
	    $data['addtime'] = time();
	    $infoarticlepic -> add($data);
	  }
	}
	R('Register/successjump',array(L('DATA_ADD_SUCCESS'), U('Business/article')));
      }else{
	R('Register/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    //查所有一级分类
    $result_one_col = M('InfoOneColumn') -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one_col', $result_one_col);
    //查所有分站
    $result_site = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_site', $result_site);
    //查此会员的资料
    $result_member = M('Member') -> field('tel,qqcode,email,address,unit') -> find($this -> _session(C('USER_AUTH_KEY')));
    $this -> assign('result_member', $result_member);
    $this -> display();
  }

  //资讯文章首页
  public function article(){
    $where = array();
    //处理搜索
    if(isset($_POST['submit'])){
      if(!empty($_POST['title'])){
	$where['ia.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
      }
      if(!empty($_POST['csid'])){
	$result_childsite_infoarticle_temp = M('ChildsiteInfoarticle') -> field('iaid') -> where(array('csid' => $this -> _post('csid', 'intval'))) -> select();
	$result_childsite_infoarticle = array();
	//格式化
	foreach($result_childsite_infoarticle_temp as $value){
	  $result_childsite_infoarticle[] = intval($value['iaid']);
	}
	$where['ia.id'] = array('in', $result_childsite_infoarticle);
      }
      if(!empty($_POST['classid'])){
	$where['ia.classid'] = $this -> _post('classid', 'intval');
      }
      if(!empty($_POST['colid'])){
	$where['ia.colid'] = $this -> _post('colid', 'intval');
      }
    }
    $infoarticle = M('InfoArticle');
    $where['ia.authorid'] = $this -> _session('user_id', 'intval');
    import("ORG.Util.Page");// 导入分页类
    $count = $infoarticle -> table('yesow_info_article as ia') -> where($where) -> count('id');
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $infoarticle -> table('yesow_info_article as ia') -> field('ia.id,ita.name as tname,ia.title,ica.name as cname,ia.hits,ia.addtime,ia.checktime,ia.status') -> where($where) -> join('yesow_info_title_attribute as ita ON ia.tid = ita.id') -> join('yesow_info_content_attribute as ica ON ia.conid = ica.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('status ASC,addtime DESC') -> where($where) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    //查所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查所有一级分类
    $result_one_col = M('InfoOneColumn') -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one_col', $result_one_col);
    $this -> display();
  }

  //编辑资讯文章
  public function editarticle(){
    $infoarticle = D('InfoArticle');
    //处理更新
    if(!empty($_POST['title'])){
      //先处理分站文章表
      $childsite_infoarticle = M('ChildsiteInfoarticle');
      //先删除
      $childsite_infoarticle -> where(array('iaid' => $this -> _post('id', 'intval'))) -> delete();
      //再添加
      if(!empty($_POST['childsite'])){
	$data = array();
	$data['iaid'] = $this -> _post('id', 'intval');
	foreach($_POST['childsite'] as $value){
	  $data['csid'] = $value;
	  $childsite_infoarticle -> add($data);
	}
      }
      if(!empty($_POST['conid2'])){
	$_POST['conid'] = $_POST['conid2'];
	unset($_POST['conid2']);
      }
      //再处理文章图片表
      //先删除
      $infoarticlepic = M('InfoArticlePic');
      $infoarticlepic -> where(array('aid' => $this -> _post('id', 'intval'))) -> delete();
      //再更新
      if(preg_match_all('/<img.*?src=\"(.*?)\".*?\>/i', $_POST['content'], $arr)){
	  $data = array();
	  $data['aid'] = $this -> _post('id', 'intval');
	  $data['colid'] = $this -> _post('colid', 'intval');
	  foreach($arr[1] as $value){
	    $data['address'] = $value;
	    $data['addtime'] = time();
	    $infoarticlepic -> add($data);
	  }
	}
      //更新其他数据
      //文章状态变为 已审未过
      $_POST['status'] = 1;
      if(!$infoarticle -> create()){
	R('Register/errorjump',array($infoarticle -> getError()));
      }
      $infoarticle -> save();
      R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Business/article')));
    }

    //文章数据
    $result = $infoarticle -> field('id,classid,title,colid,tid,conid,content,source,keyword,tel,qqcode,email,address,unit') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //如果此内容属性id是二级，则读出所有此一级下的所有二级id
    $conattpid = M('InfoContentAttribute') -> getFieldByid($result['conid'], 'pid');
    if($conattpid != 0){
      $result_contwoatt = M('InfoContentAttribute') -> field('id,name') -> where(array('pid' => $conattpid)) -> select();
      $this -> assign('result_contwoatt', $result_contwoatt);
    }
    //查所有一级分类
    $result_one_col = M('InfoOneColumn') -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one_col', $result_one_col);
    //查此文章一级分类下的二级分类
    $result_two_col = M('InfoTwoColumn') -> field('id,name') -> where(array('oneid' => $result['classid'])) -> order('sort') -> select();
    $this -> assign('result_two_col', $result_two_col);
    //查此文章一级分类下的标题属性
    $result_title = M('InfoTitleAttribute') -> field('id,name') -> where(array('oneid' => $result['classid'])) -> order('sort') -> select();
    $this -> assign('result_title', $result_title);
    //查此文章一级分类下的内容属性
    $result_content = M('InfoContentAttribute') -> field('id,name') -> where(array('oneid' => $result['classid'], 'pid' => 0)) -> order('sort') -> select();
    $this -> assign('result_content', $result_content);
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询文章所属分站
    $result_childsite_infoarticle_temp = M('ChildsiteInfoarticle') -> field('csid') -> where(array('iaid' => $this -> _get('id', 'intval'))) -> select();
    $result_childsite_infoarticle = array();
    foreach($result_childsite_infoarticle_temp as $value){
      $result_childsite_infoarticle[] = $value['csid'];
    }
    $this -> assign('result_childsite_infoarticle', $result_childsite_infoarticle);
    $this -> display();
  }

  //商家需求管理前置需求
  public function _before_businessneeds(){
    $this -> _before_index();
  }

  //商家需求管理
  public function businessneeds(){
    $this -> display();
  }

  //旺铺管理
  public function storerent(){
    $storerent = M('StoreRent');
    $where = array();
    $where['sr.mid'] = $_SESSION[C('USER_AUTH_KEY')];
    //处理搜索
    if(!empty($_POST['title'])){
      $where['sr.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }

    import("ORG.Util.Page");// 导入分页类
    $count = $storerent -> table('yesow_store_rent as sr') -> where($where) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();

    $result = $storerent -> table('yesow_store_rent as sr') -> field('cs.name as csname,sr.id,sr.title,srt.name as srtname,sr.addtime,sr.ischeck,sr.clickcount') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sr.updatetime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //添加旺铺
  public function addstorerent(){
    //处理添加
    if(!empty($_POST['title'])){
      $storerent = D('admin://StoreRent');
      if(!$storerent -> create()){
	R('Register/errorjump',array($storerent -> getError()));
      }
      if(!empty($_FILES['image']['name'])){
	$up_data = R('admin://Public/store_pic_upload');
	$storerent -> image = $up_data[0]['savename'];
      }
      $storerent -> mid = !empty($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
      if($storerent -> add()){
	R('Register/successjump',array(L('DATA_ADD_SUCCESS'), U('Business/storerent')));
      }else{
	R('Register/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    //查询店铺类别
    $result_store_type = M('StoreRentType') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_store_type', $result_store_type);
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  //编辑旺铺
  public function editstorerent(){
    $storerent = D('admin://StoreRent');
    //处理编辑
    if(!empty($_POST['title'])){
      if(!$storerent -> create()){
	R('Register/errorjump',array($storerent -> getError()));
      }
      if(!empty($_FILES['image']['name'])){
	$up_data = R('admin://Public/store_pic_upload');
	$storerent -> image = $up_data[0]['savename'];
      }
      $storerent -> mid = !empty($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
      if($storerent -> save()){
	R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Business/storerent')));
      }else{
	R('Register/errorjump',array(L('DATA_UPDATE_ERROR')));
      }
    }
    $result = $storerent -> field('tid,endtime,title,keyword,csid,csaid,systemimage,content,linkman,tel,qqcode,email,address') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询店铺类别
    $result_store_type = M('StoreRentType') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_store_type', $result_store_type);
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询当前分站下地区
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    $this -> display();
  }

  //删除旺铺
  public function delstorerent(){
    echo M('StoreRent') -> delete($this -> _get('id', 'intval'));
  }

  //二手管理
  public function sellused(){
    $sellused = M('SellUsed');
    $where = array();
    $where['su.mid'] = $_SESSION[C('USER_AUTH_KEY')];
    //处理搜索
    if(!empty($_POST['title'])){
      $where['su.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }

    import("ORG.Util.Page");// 导入分页类
    $count = $sellused -> table('yesow_sell_used as su') -> where($where) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();

    $result = $sellused -> table('yesow_sell_used as su') -> field('su.id,cs.name as csname,su.title,sut.name as tname,su.price,su.linkman,su.addtime,su.ischeck,su.clickcount') -> join('yesow_child_site as cs ON su.csid = cs.id') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('su.updatetime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //添加二手滞销
  public function addsellused(){
    //处理添加
    if(!empty($_POST['title'])){
      $sellused = D('admin://SellUsed');
      if(!$sellused -> create()){
	R('Register/errorjump',array($sellused -> getError()));
      }
      if(!empty($_FILES['image']['name'])){
	$up_data = R('admin://Public/sellused_pic_upload');
	$sellused -> image = $up_data[0]['savename'];
      }
      $sellused -> mid = !empty($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
      if($sellused -> add()){
	R('Register/successjump',array(L('DATA_ADD_SUCCESS'), U('Business/sellused')));
      }else{
	R('Register/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    //查询一级发布类别
    $result_type_one = M('SellUsedType') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_type_one', $result_type_one);
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询产品成色
    $result_color = M('SellUsedColor') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_color', $result_color);
    $this -> display();
  }

  //删除二手
  public function delsellused(){
    echo M('SellUsed') -> delete($this -> _get('id', 'intval'));
  }

  //编辑二手
  public function editsellused(){
    $sellused = D('admin://SellUsed');
    //处理编辑
    if(!empty($_POST['title'])){
      if(!$sellused -> create()){
	R('Register/errorjump',array($sellused -> getError()));
      }
      if(!empty($_FILES['image']['name'])){
	$up_data = R('admin://Public/sellused_pic_upload');
	$sellused -> image = $up_data[0]['savename'];
      }
      $sellused -> mid = !empty($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
      if($sellused -> save()){
	R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Business/sellused')));
      }else{
	R('Register/errorjump',array(L('DATA_UPDATE_ERROR')));
      }
    }
    $result = $sellused -> field('tid_one,tid_two,endtime,csid,csaid,cid,title,keyword,image,price,tel,linkman,email,address,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询一级发布类别
    $result_type_one = M('SellUsedType') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_type_one', $result_type_one);
    //查询当前类别下的二级类别
    $result_type_two = M('SellUsedType') -> field('id,name') -> where(array('pid' => $result['tid_one'])) -> order('sort ASC') -> select();
    $this -> assign('result_type_two', $result_type_two);
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询当前分站下地区
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    //查询产品成色
    $result_color = M('SellUsedColor') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_color', $result_color);
    $this -> display();
  }

  //人才交流
  public function recruit(){
    $RecruitCompany = M('RecruitCompany');
    $where = array();
    $where['rc.mid'] = $_SESSION[C('USER_AUTH_KEY')];
    //处理搜索
    if(!empty($_POST['name'])){
      $where['rc.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    import("ORG.Util.Page");// 导入分页类
    $count = $RecruitCompany -> table('yesow_recruit_company as rc') -> where($where) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $RecruitCompany -> table('yesow_recruit_company as rc') -> field('rc.id,cs.name as csname,rc.name,rc.linkman,rc.tel,rc.addtime,rc.ischeck') -> join('yesow_child_site as cs ON rc.csid = cs.id') -> where($where) -> order('rc.addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }

  //发布公司
  public function recruit_addcompany(){
    if(!empty($_POST['name'])){
      $RecruitCompany = M('RecruitCompany');
      if(!$RecruitCompany -> create()){
	R('Register/errorjump',array($RecruitCompany -> getError()));
      }
      if(!empty($_FILES['pic']['name'])){
	$up_data = R('admin://Public/recruit_company_pic_upload');
	$RecruitCompany -> pic = $up_data[0]['savename'];
      }
      $RecruitCompany -> mid = !empty($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
      $RecruitCompany -> addtime = time();
      if($RecruitCompany -> add()){
	R('Register/successjump',array(L('DATA_ADD_SUCCESS'), U('Business/recruit')));
      }else{
	R('Register/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //所属行业
    $result_industry = M('RecruitCompanyIndustry') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_industry', $result_industry);
    //员工人数
    $result_employnum = M('RecruitCompanyEmploynum') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_employnum', $result_employnum);
    //注册资金
    $result_registermoney = M('RecruitCompanyRegistermoney') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_registermoney', $result_registermoney);
    //公司性质
    $result_nature = M('RecruitCompanyNature') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_nature', $result_nature);
    $this -> display();
  }

  //删除公司
  public function recruit_delcompany(){
    echo M('RecruitCompany') -> delete($this -> _get('id', 'intval'));
  }

  //编辑公司
  public function recruit_editcompany(){
    $RecruitCompany = M('RecruitCompany');
    //处理编辑
    if(!empty($_POST['name'])){
      if(!$RecruitCompany -> create()){
	R('Register/errorjump',array($RecruitCompany -> getError()));
      }
      if(!empty($_FILES['pic']['name'])){
	$up_data = R('admin://Public/recruit_company_pic_upload');
	$RecruitCompany -> pic = $up_data[0]['savename'];
      }
      if($RecruitCompany -> save()){
	R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Business/recruit')));
      }else{
	R('Register/errorjump',array(L('DATA_UPDATE_ERROR')));
      }
    }
    $result = $RecruitCompany -> field('csid,csaid,ciid,ceid,crid,cnid,pic,name,address,linkman,website,email,tel,qqcode,abstract') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询当前分站下地区
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    //所属行业
    $result_industry = M('RecruitCompanyIndustry') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_industry', $result_industry);
    //员工人数
    $result_employnum = M('RecruitCompanyEmploynum') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_employnum', $result_employnum);
    //注册资金
    $result_registermoney = M('RecruitCompanyRegistermoney') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_registermoney', $result_registermoney);
    //公司性质
    $result_nature = M('RecruitCompanyNature') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_nature', $result_nature);
    $this -> display();
  }

  //岗位管理
  public function recruit_position(){
    $RecruitJobs = M('RecruitJobs');
    $where = array();
    $where['rj.cid'] = $this -> _get('cid', 'intval');
    import("ORG.Util.Page");// 导入分页类
    $count = $RecruitJobs -> table('yesow_recruit_jobs as rj') -> where($where) -> count();
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $RecruitJobs -> table('yesow_recruit_jobs as rj') -> field('rj.id,cs.name as csname,csa.name as csaname,rj.name,rj.addtime,rj.endtime,rj.ischeck') -> join('yesow_child_site as cs ON rj.jobs_csid = cs.id') -> join('yesow_child_site_area as csa ON rj.jobs_csaid = csa.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('rj.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    //公司名
    $RecruitCompany = M('RecruitCompany');
    $company_name = $RecruitCompany -> getFieldByid($this -> _get('cid', 'intval'), 'name');
    $this -> assign('company_name', $company_name);
    $this -> display();
  }

  //发布岗位
  public function recruit_addposition(){
    if(!empty($_POST['name'])){
      $RecruitJobs = M('RecruitJobs');
      if(!$RecruitJobs -> create()){
	R('Register/errorjump',array($RecruitJobs -> getError()));
      }
      $RecruitJobs -> addtime = time();
      $RecruitJobs -> endtime = $this -> _post('endtime', 'strtotime');
      if($RecruitJobs -> add()){
	R('Register/successjump',array(L('ADDPOSITION_SUCCESS'), U('Business/recruit_position') . '/cid/' . $_POST['cid']));
      }else{
	R('Register/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    //公司名
    $RecruitCompany = M('RecruitCompany');
    $company_name = $RecruitCompany -> getFieldByid($this -> _get('cid', 'intval'), 'name');
    $this -> assign('company_name', $company_name);
    //最低月薪
    $result_monthlypay = M('RecruitJobsMonthlypay') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_monthlypay', $result_monthlypay);
    //学历要求
    $result_degree = M('RecruitJobsDegree') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_degree', $result_degree);
    //工作经验
    $result_experience = M('RecruitJobsExperience') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_experience', $result_experience);
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  //编辑岗位
  public function recruit_editposition(){
    $RecruitJobs = M('RecruitJobs');
    if(!empty($_POST['name'])){
      if(!$RecruitJobs -> create()){
	R('Register/errorjump',array($RecruitJobs -> getError()));
      }
      $RecruitJobs -> endtime = $this -> _post('endtime', 'strtotime');
      if($RecruitJobs -> save()){
	R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Business/recruit_position') . '/cid/' . $_POST['c_cid']));
      }else{
	R('Register/errorjump',array(L('DATA_UPDATE_ERROR')));
      }
    }
    $result = $RecruitJobs -> field('cid,jmid,jdid,jeid,name,keyword,english,major,sex,age,jobstype,num,jobs_csid,jobs_csaid,content,endtime') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //公司名
    $RecruitCompany = M('RecruitCompany');
    $company_name = $RecruitCompany -> getFieldByid($result['cid'], 'name');
    $this -> assign('company_name', $company_name);
    //最低月薪
    $result_monthlypay = M('RecruitJobsMonthlypay') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_monthlypay', $result_monthlypay);
    //学历要求
    $result_degree = M('RecruitJobsDegree') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_degree', $result_degree);
    //工作经验
    $result_experience = M('RecruitJobsExperience') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_experience', $result_experience);
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询当前分站下地区
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['jobs_csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    $this -> display();
  }

  //删除岗位
  public function recruit_delposition(){
    echo M('RecruitJobs') -> delete($this -> _get('id', 'intval'));
  }

}

<?php
class BusinessAction extends MemberCommonAction {
  //首页
  public function index(){
    echo '商家服务'; 
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
	 $this -> error($info_article -> getError());
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
	if(preg_match_all('/<img.*?src=\"(.*?)\".*?\>/i', $_POST['content'], $arr)){
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

	$this -> success(L('DATA_ADD_SUCCESS'), U('member/article'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
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
      if(!empty($_POST['keyword'])){
	$where['ia.keyword'] = array('LIKE', '%' . $this -> _post('keyword') . '%');
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
      if(!empty($_POST['colid'])){
	$colid = M('InfoTwoColumn') -> getFieldByname($this -> _post('colid'), 'id');
	$where['ia.colid'] = intval($colid);
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
	$this -> error($infoarticle -> getError());
      }
      $infoarticle -> save();
      $this -> success(L('DATA_UPDATE_SUCCESS'), U('member/article'));
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
}

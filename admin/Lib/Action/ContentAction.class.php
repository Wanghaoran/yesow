<?php
class ContentAction extends CommonAction {
  
  /* ------------  资讯一级栏目管理   -------------- */
  //资讯一级栏目管理
  public function infomationonecolumn(){
    $infoonecolumn = M('InfoOneColumn');
    $where = array();
    //搜索条件
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $infoonecolumn -> where($where) -> count('id');
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

    $result = $infoonecolumn -> field('id,name,remark,isshow,isnav,sort,hotcommentnum,hotpointnum,slideimgnum,listpagernum') -> order('sort') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加资讯一级栏目
  public function addinfomationonecolumn(){
    //处理添加
    if(!empty($_POST['name'])){
      $infoonecolumn = D('InfoOneColumn');
      if(!$infoonecolumn -> create()){
	$this -> error($infoonecolumn -> getError());
      }
      if($infoonecolumn -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除资讯一级栏目
  public function delinfomationonecolumn(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $infoonecolumn = M('InfoOneColumn');
    if($infoonecolumn -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑资讯一级栏目
  public function editinfomationonecolumn(){
    $infoonecolumn = D('InfoOneColumn');
    //处理更新
    if(!empty($_POST['name'])){
      if(!$infoonecolumn -> create()){
	$this -> error($infoonecolumn -> getError());
      }
      if($infoonecolumn -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $infoonecolumn -> field('id,name,remark,isshow,isnav,sort,hotcommentnum,hotpointnum,slideimgnum,listpagernum') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }
  /* ------------  资讯一级栏目管理   -------------- */

  /* ------------  资讯二级栏目管理   -------------- */
  //资讯二级栏目管理
  public function infomationtwocolumn(){
    $infotwocolumn = M('InfoTwoColumn');
    $infoonecolumn = M('InfoOneColumn');
    $where = array();
    //查询条件
    if(!empty($_POST['name'])){
      $where['itc.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_REQUEST['oneid'])){
      $where['itc.oneid'] = $this -> _request('oneid', 'intval');
    }

    //记录总数
    $count = $infotwocolumn -> table('yesow_info_two_column as itc') -> where($where) -> count('id');
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

    //查一级栏目
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    //查二级栏目
    $result = $infotwocolumn -> table('yesow_info_two_column as itc') -> field('itc.id,itc.name,ioc.name as oname,itc.sort,itc.remark,itc.isoneshow,itc.leftpicnum,itc.hotpointnum,itc.pagernum') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> join('yesow_info_one_column as ioc ON itc.oneid = ioc.id') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加资讯二级栏目
  public function addinfomationtwocolumn(){
    //处理添加
    if(!empty($_POST['name'])){
      $infotwocolumn = D('InfoTwoColumn');
      if(!$infotwocolumn -> create()){
	$this -> error($infotwocolumn -> getError());
      }
      if($infotwocolumn -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查一级分类
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    $this -> display();
  }

  //删除资讯二级栏目
  public function delinfomationtwocolumn(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $infotwocolumn = M('InfoTwoColumn');
    if($infotwocolumn -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑资讯二级栏目
  public function editinfomationtwocolumn(){
    $infotwocolumn = D('InfoTwoColumn');
    if(!empty($_POST['name'])){
      if(!$infotwocolumn -> create()){
	$this -> error($infotwocolumn -> getError());
      }
      if($infotwocolumn -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //查一级分类
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    //查二级栏目
    $result = $infotwocolumn -> field('id,oneid,name,sort,remark,isoneshow,leftpicnum,hotpointnum,pagernum') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }
  /* ------------  资讯二级栏目管理   -------------- */

  /* ------------  标题属性管理   -------------- */

  //标题属性管理
  public function titleattribute(){
    $titleattribute = M('InfoTitleAttribute');
    $where = array();
    //查询条件
     if(!empty($_POST['name'])){
      $where['ita.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_REQUEST['oneid'])){
      $where['ita.oneid'] = $this -> _request('oneid', 'intval');
    }

    //记录总数
    $count = $titleattribute -> table('yesow_info_title_attribute as ita') -> where($where) -> count('id');
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

    //查询属性内容
    $result = $titleattribute -> table('yesow_info_title_attribute as ita') -> field('ita.id,ita.name,ioc.name as oname,ita.sort,ita.remark') -> where($where) -> join('yesow_info_one_column AS ioc ON ita.oneid = ioc.id') -> order('sort') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //查一级分类
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加标题属性
  public function addtitleattribute(){
    //处理添加
    if(!empty($_POST['name'])){
      $titleattribute = D('InfoTitleAttribute');
      if(!$titleattribute -> create()){
	$this -> error($titleattribute -> getError());
      }
      if($titleattribute -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查一级分类
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    $this -> display();
  }

  //删除标题属性
  public function deltitleattribute(){
    $titleattribute = M('InfoTitleAttribute');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($titleattribute -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑标题属性
  public function edittitleattribute(){
    $titleattribute = D('InfoTitleAttribute');
    //处理更新
    if(!empty($_POST['name'])){
      if(!$titleattribute -> create()){
	$this -> error($titleattribute -> getError());
      }
      if($titleattribute -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //查一级分类
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    //查标题属性
    $result = $titleattribute -> field('id,name,oneid,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }
  /* ------------  标题属性管理   -------------- */

  /* ------------  内容属性管理   -------------- */

  //内容属性管理
  public function contentattribute(){
    $contentattribute = M('InfoContentAttribute');
    $where = array();
    $where['ica.pid'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    if(!empty($_REQUEST['name'])){
      $where['ica.name'] = array('LIKE', '%' .  $_REQUEST['name'] . '%');
    }
    if(!empty($_REQUEST['oneid'])){
      $where['ica.oneid'] = $this -> _request('oneid', 'intval');
    }

    //查询上级栏目
    if(!empty($_REQUEST['id'])){
      $result = $contentattribute -> field('name') -> find($this -> _request('id', 'intval'));
    }
    $pidname = isset($result['name']) ? $result['name'] : '无';
    $this -> assign('pidname', $pidname);

    //记录总数
    $count = $contentattribute -> table('yesow_info_content_attribute as ica') -> where($where) -> count('id');
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

    $result = $contentattribute -> table('yesow_info_content_attribute AS ica') -> field('ica.id,ica.name,ioc.name as oname,ica.sort,ica.remark') -> where($where) -> join('yesow_info_one_column AS ioc ON ica.oneid = ioc.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);

    //查一级分类
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);

    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  //添加内容属性
  public function addcontentattribute(){
    $contentattribute = D('InfoContentAttribute');
    //处理更新
    if(!empty($_POST['name'])){
      if(!$contentattribute -> create()){
	$this -> error($contentattribute -> getError());
      }
      if($contentattribute -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }

    //查询并计算父级名称 和 本次更新的level、pid
    $pid = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    if(!empty($_REQUEST['id'])){
      $result = $contentattribute -> field('name') -> find($this -> _request('id', 'intval'));
    }
    $pidname = isset($result['name']) ? $result['name'] : '无';
    $this -> assign('pid', $pid);
    $this -> assign('pidname', $pidname);

    //查一级分类
    $infoonecolumn = M('InfoOneColumn');
    $where_one = array();
    //如果存在id参数，则证明是二级分类，则只查出此一级分类的所属栏目即可
    if(!empty($_REQUEST['id'])){
      $oneid = $contentattribute -> getFieldByid($this -> _request('id', 'intval'), 'oneid');
      $where_one['id'] = $oneid;
    }
    $result_one = $infoonecolumn -> field('id,name') -> where($where_one) -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    $this -> display();
  }

  //删除内容属性
  public function delcontentattribute(){
    $contentattribute = M('InfoContentAttribute');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($contentattribute -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑内容属性
  public function editcontentattribute(){
    $contentattribute = D('InfoContentAttribute');
    //处理更新
    if(!empty($_POST['name'])){
      if(!$contentattribute -> create()){
	$this -> error($contentattribute -> getError());
      }
      if($contentattribute -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $contentattribute -> field('id,oneid,pid,name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);

    //查上级属性
    if($result['pid'] != 0){
      $pname = $contentattribute -> getFieldByid($result['pid'], 'name');
      $this -> assign('pname', $pname);
    }
    //查一级分类
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);

    $this -> display();
  }

  /* ------------  内容属性管理   -------------- */

  /* ------------  文章管理   -------------- */

  //文章管理
  public function article(){
    $infoarticle = M('InfoArticle');
    $where = array();
    //处理查询条件
    if(!empty($_POST['title'])){
      $where['ia.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($_POST['column'])){
      $infotwocolumn = M('InfoTwoColumn');
      $where_col = array();
      $where_col['name'] = array('LIKE', '%' . $this -> _post('column') . '%');
      $colid = $infotwocolumn -> where($where_col) -> field('id') -> select();
      $colid_arr = array();;
      foreach($colid as $value){
	$colid_arr[] = intval($value['id']);
      }
      $where['ia.colid'] = array('IN', $colid_arr);
    }
    if(!empty($_POST['author'])){
      $member = M('Member');
      $authorid = $member -> getFieldByname($this -> _post('author'), 'id');
      $where['ia.authorid'] = intval($authorid);
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['ia.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['ia.addtime'][] = array('lt', $endtime);
    }


    //记录总数
    $count = $infoarticle -> table('yesow_info_article as ia') -> where($where) -> count('id');
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

    $result = $infoarticle -> table('yesow_info_article as ia') -> field('ia.id,ioc.name as classname,itc.name as colname,ita.name as titlename,ica.name as contentname,ia.title,a.name as aname,m.name as mname,ia.hits,ia.addtime,ia.checktime,ia.status,ctc.count') -> where($where) -> join('yesow_info_two_column as itc ON ia.colid = itc.id') -> join('yesow_info_one_column as ioc ON ia.classid = ioc.id') -> join('yesow_info_title_attribute as ita ON ia.tid = ita.id') -> join('yesow_info_content_attribute as ica ON ia.conid = ica.id') -> join('yesow_admin as a ON ia.auditid = a.id') -> join('yesow_member as m ON ia.authorid = m.id') -> join('(SELECT aid,COUNT(id) as count FROM yesow_info_article_comment GROUP BY aid) as ctc ON ia.id = ctc.aid') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('status ASC,addtime DESC') -> select();
    $this -> assign('result', $result);

    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  //删除文章
  public function delarticle(){
    //先删除文章的图片
    $infoarticlepic = M('InfoArticlePic');
    $where_pic['aid'] = array('in', $_POST['ids']);
    $result_pic = $infoarticlepic -> where($where_pic) -> field('address') -> select();
    //删除图片
    foreach($result_pic as $value){
      @unlink($value['address']);
    }
    //删除数据
    $infoarticlepic -> where($where_pic) -> delete();
    //删除文章
    $infoarticle = M('InfoArticle');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($infoarticle -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑文章
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
      if(preg_match_all("/<img.*src\s*=\s*[\"|\']?\s*([^>\"\'\s]*)/i", str_ireplace("\\","",$_POST['content']), $arr)){
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
      if(!$infoarticle -> create()){
	$this -> error($infoarticle -> getError());
      }
      $infoarticle -> save();
      $this -> success(L('DATA_UPDATE_SUCCESS'));

    }
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

  //通过审核文章
  public function passauditarticle(){
    $infoarticle = M('InfoArticle');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 2);
    $data_audit['auditid'] = $this -> _session(c('user_auth_key'), 'intval');
    $data_audit['checktime'] = time();
    if($infoarticle -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //未通过审核文章
  public function nopassauditarticle(){
    $infoarticle = M('InfoArticle');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));
    $data_audit = array('status' => 1);
    $data_audit['auditid'] = $this -> _session(c('user_auth_key'), 'intval');
    $data_audit['checktime'] = time();
    if($infoarticle -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //文章图片管理
  public function infoimage(){
    $infoarticlepic = M('InfoArticlePic');
    $result = $infoarticlepic -> table('yesow_info_article_pic as iap') -> field('iap.id,iap.address,itc.name as cname,isshow_index,isshow_onelist,isshow_twolist') -> where(array('aid' => $this -> _get('id', 'intval'))) -> join('yesow_info_two_column as itc ON iap.colid = itc.id') -> select();
    $this -> assign('result', $result);
    $this -> display();
  }

  //设置文章图片显示
  public function setpicisshow(){
    $infoarticlepic = M('InfoArticlePic');
    $data = array();
    $data['id'] = $this -> _get('id', 'intval');
    $data[$this -> _get('type')] = $this -> _get('value', 'intval');
    if($infoarticlepic -> save($data)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //文章评论管理
  public function infocomment(){
    $comment = M('InfoArticleComment');
    $where = array();
    //处理搜索
    if(!empty($_POST['content'])){
      $where['iac.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
    }
    if(!empty($_POST['author'])){
      $member = M('Member');
      $authorid = $member -> getFieldByname($this -> _post('author'), 'id');
      $where['iac.mid'] = intval($authorid);
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['iac.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['iac.addtime'][] = array('lt', $endtime);
    }

    //记录总数
    $count = $comment -> table('yesow_info_article_comment as iac') -> where($where) -> count('id');
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

    $result = $comment -> table('yesow_info_article_comment as iac') -> field('iac.id,ia.title,ia.id as iaid,iac.floor,iac.content,m.name,iac.addtime,iac.status') -> where($where) -> order('status ASC,iac.addtime DESC') -> join('yesow_info_article as ia ON iac.aid = ia.id') -> join('yesow_member as m ON iac.mid = m.id') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //编辑文章评论
  public function editinfocomment(){
    $comment = D('index://InfoArticleComment');
    //处理更新
    if(!empty($_POST['floor'])){
      if(!$comment -> create()){
	$this -> error($comment -> getError());
      }
      if($comment -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $comment -> table('yesow_info_article_comment as iac') -> field('iac.id,ia.title,iac.floor,iac.content,m.name') -> join('yesow_info_article as ia ON iac.aid = ia.id') -> join('yesow_member as m ON iac.mid = m.id') -> where(array('iac.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  //删除文章评论
  public function delinfocomment(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $comment = M('InfoArticleComment');
    if($comment -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //通过审核评论
  public function passauditcomment(){
    $comment = M('InfoArticleComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 2);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //不通过审核评论
  public function nopassauditcomment(){
    $comment = M('InfoArticleComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 1);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  /* ------------  文章管理   -------------- */

  /* ----------- 公告管理 ------------ */

   //标题公告管理
  public function titlenotice(){
    $notice = M('TitleNotice');
    $where = array();
    //处理搜索
    if(!empty($_POST['title'])){
      $where['tn.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    //记录总数
    $count = $notice -> table('yesow_title_notice as tn') -> where($where) -> count('id');
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
    //数据
    $result = $notice -> table('yesow_title_notice as tn') -> field('tn.id,tnt.name as tname,tn.title,tn.addtime') -> where($where) -> join('yesow_title_notice_type as tnt ON tn.tid = tnt.id') -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();  
  }

  //添加标题公告
  public function addtitlenotice(){
    //处理添加
    if(!empty($_POST['title'])){
      $notice = D('TitleNotice');
      if(!$notice -> create()){
	$this -> error($notice -> getError());
      }
      if($notice -> add()){
	//删除缓存
	S('index_title_notice', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询分类
    $result_type = M('TitleNoticeType') -> field('id,name') -> select();
    $this -> assign('result_type', $result_type);
    $this -> display();
  }

  //删除标题公告
  public function deltitlenotice(){
    $notice = M('TitleNotice');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($notice -> where($where_del) -> delete()){
      //删除缓存
      S('index_title_notice', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑标题公告
  public function edittitlenotice(){
    $notice = D('TitleNotice');
    //处理更新
    if(!empty($_POST['title'])){
      if(!$notice -> create()){
	$this -> error($notice -> getError());
      }
      if($notice -> save()){
	//删除缓存
	S('index_title_notice', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //数据
    $result = $notice -> field('tid,title,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询分类
    $result_type = M('TitleNoticeType') -> field('id,name') -> select();
    $this -> assign('result_type', $result_type);
    $this -> display();
  }

  //易搜公告管理
  public function yesownotice(){
    $notice = M('Notice');
    $where = array();
    //处理搜索
    if(!empty($_POST['title'])){
      $where['title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['addtime'][] = array('lt', $endtime);
    }

    //记录总数
    $count = $notice -> where($where) -> count('id');
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

    $result = $notice -> field('id,titleattribute,title,source,clickcount,addtime') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加易搜公告
  public function addyesownotice(){
    if(!empty($_POST['title'])){
      $notice = D('Notice');
      if(!$notice -> create()){
	$this -> error($notice -> getError());
      }
      if($id = $notice -> add()){
	//添加成功写公告-分站关系表
	if(!empty($_POST['childsite'])){
	  foreach($_POST['childsite'] as $value){
	    $data = array();
	    $data['nid'] = $id;
	    $data['csid'] = $value;
	    M('NoticeChildsite') -> add($data);
	  }
	}
	//删除缓存
	S('index_yesow_notice', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  //编辑易搜公告
  public function edityesownotice(){
    $notice = D('Notice');
    $notice_childsite = M('NoticeChildsite');
    if(!empty($_POST['title'])){
      $id = $this -> _post('id', 'intval');
      //先更新公告分站对应表
        //先删除
      $notice_childsite -> where(array('nid' => $id)) -> delete();
        //再添加
      if(!empty($_POST['childsite'])){
	foreach($_POST['childsite'] as $value){
	  $data = array();
	  $data['nid'] = $id;
	  $data['csid'] = $value;
	  $notice_childsite -> add($data);
	}
      }
      //再更新公告表
      if(!$notice -> create()){
	$this -> error($notice -> getError());
      }
      $notice -> save();
      //删除缓存
      S('index_yesow_notice', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }
    //查询公告数据
    $result = $notice -> field('title,titleattribute,keywords,content,source') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //标题属性
    $this -> assign('titleattribute', array('公告','促销','申明','消息'));
    //公告分站关系
    $temp_notice_childsite = $notice_childsite -> field('csid') -> where(array('nid' => $this -> _get('id', 'intval'))) -> select();
    $result_notice_childsite = array();
    foreach($temp_notice_childsite as $value){
      $result_notice_childsite[] = $value['csid'];
    }
    $this -> assign('result_notice_childsite', $result_notice_childsite);
    $this -> display();
  }

  //删除易搜公告
  public function delyesownotice(){
    //删除文章
    $notice = M('Notice');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($notice -> where($where_del) -> delete()){
      //删除缓存
      S('index_yesow_notice', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //公告评论管理
  public function noticecomment(){
    $comment = M('NoticeComment');
    $where = array();
    //处理搜索
    if(!empty($_POST['content'])){
      $where['nc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
    }
    if(!empty($_POST['author'])){
      $member = M('Member');
      $authorid = $member -> getFieldByname($this -> _post('author'), 'id');
      $where['nc.mid'] = intval($authorid);
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['nc.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['nc.addtime'][] = array('lt', $endtime);
    }

    //记录总数
    $count = $comment -> table('yesow_notice_comment as nc') -> where($where) -> count('id');
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

    $result = $comment -> table('yesow_notice_comment as nc') -> field('nc.id,n.title,n.id as nid,nc.floor,nc.content,m.name,nc.addtime,nc.status') -> where($where) -> order('status ASC,nc.addtime DESC') -> join('yesow_notice as n ON nc.nid = n.id') -> join('yesow_member as m ON nc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //编辑公告评论
  public function editnoticecomment(){
    $comment = D('index://NoticeComment');
    //处理更新
    if(!empty($_POST['floor'])){
      if(!$comment -> create()){
	$this -> error($comment -> getError());
      }
      if($comment -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $comment -> table('yesow_notice_comment as nc') -> field('nc.id,n.title,nc.floor,nc.content,m.name') -> join('yesow_notice as n ON nc.nid = n.id') -> join('yesow_member as m ON nc.mid = m.id') -> where(array('nc.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  //删除公告评论
  public function delnoticecomment(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $comment = M('NoticeComment');
    if($comment -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //通过审核评论
  public function passauditnoticecomment(){
    $comment = M('NoticeComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 2);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  //不通过审核评论
  public function nopassauditnoticecomment(){
    $comment = M('NoticeComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 1);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  /* ----------- 公告管理 ------------ */

  /* ----------- 代理加盟 ------------ */
  //代理加盟管理
  public function agent(){
    $agent = M('AgentJoin');
    $result = $agent -> field('id,title,sort,remark') -> select();
    $this -> assign('result', $result);
    $this -> display();
  }

  //增加代理加盟
  public function addagent(){
    //处理增加
    if(!empty($_POST['title'])){
      $agent = M('AgentJoin');
      if(!$agent -> create()){
	$this -> error($agent -> getError());
      }
      if($agent -> add()){
	//删除缓存
	S('index_agent_join', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除代理加盟
  public function delagent(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $agent = M('AgentJoin');
    if($agent -> where($where_del) -> delete()){
      //删除缓存
      S('index_agent_join', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑代理加盟
  public function editagent(){
    $agent = M('AgentJoin');
    if(!empty($_POST['title'])){
      if(!$agent -> create()){
	$this -> error($agent -> getError());
      }
      if($agent -> save()){
	//删除缓存
	S('index_agent_join', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $agent -> field('title,remark,sort,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

   //加盟申请管理
  public function agentapply(){
    $agent_add = M('AgentAdd');
    $result = $agent_add -> field('id,type,addidea,advice') -> order('id DESC') -> select();
    $this -> assign('result', $result);
    $this -> display();
  }

  //删除加盟申请
  public function delagentapply(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $agent_add = M('AgentAdd');
    if($agent_add -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //查看加盟申请
  public function editagentapply(){
    $agent_add = M('AgentAdd');
    $result = $agent_add -> table('yesow_agent_add as aa') -> field('type,cs.name as csname,csa.name as csaname,aat.name as aatname,aa.name,registeredcapital,address,linkman,tel,qqcode,email,website,businessproject,p_name,p_birthday,p_tel,p_telphone,p_address,p_qqcode,employeesnum,starttime,ownership,sitetpe,businessarea,computernum,printernum,interdevicenum,fexnum,telnum,addidea,advice,other') -> join('yesow_child_site as cs ON aa.csid = cs.id') -> join('yesow_child_site_area as csa ON aa.csaid = csa.id') -> join('yesow_agent_add_type as aat ON aa.tid = aat.id') -> where(array('aa.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  
  }

  /* ----------- 代理加盟 ------------ */

  /* ----------- 帮助中心 ------------ */

  //帮助分类管理
  public function helpclass(){
    $helpclass = M('HelpClass');
    $where = array();

    //处理搜索
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    //记录总数
    $count = $helpclass -> where($where) -> count('id');
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

    $result = $helpclass -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加帮助分类
  public function addhelpclass(){
    //处理添加
    if(!empty($_POST['name'])){
      $helpclass = M('HelpClass');
      if(!$helpclass -> create()){
	$this -> error($helpclass -> getError());
      }
      if($helpclass -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除帮助分类
  public function delhelpclass(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $helpclass = M('HelpClass');
    if($helpclass -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑帮助分类
  public function edithelpclass(){
    $helpclass = M('HelpClass');
    if(!empty($_POST['name'])){
      if(!$helpclass -> create()){
	$this -> error($helpclass -> getError());
      }
      if($helpclass -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $helpclass -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  
  }

  //帮助文章管理
  public function helparticle(){
    $helparticle = M('HelpArticle');
    $where = array();
    //处理搜索
    if(!empty($_POST['title'])){
      $where['ha.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($_POST['cid'])){
      $where['ha.cid'] = $this -> _post('cid');
    }

    //记录总数
    $count = $helparticle -> table('yesow_help_article as ha') -> where($where) -> count('id');
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

    $result = $helparticle -> table('yesow_help_article as ha') -> field('ha.id,hc.name as cname,ha.title,ha.sort,ha.addtime') -> join('yesow_help_class as hc ON ha.cid = hc.id') -> order('ha.addtime DESC') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    //查询分站
    $result_class = M('HelpClass') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_class', $result_class);
    $this -> display();
  }

  //添加帮助文章
  public function addhelparticle(){
    //处理添加
    if(!empty($_POST['title'])){
      $helparticle = D('HelpArticle');
      if(!$helparticle -> create()){
	$this -> error($helparticle -> getError());
      }
      if($helparticle -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询分类
    $result_class = M('HelpClass') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_class', $result_class);
    $this -> display();
  
  }

  //删除帮助文章
  public function delhelparticle(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $helparticle = M('HelpArticle');
    if($helparticle -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑帮助文章
  public function edithelparticle(){
    $helparticle = D('HelpArticle');
    if(!empty($_POST['title'])){
      if(!$helparticle -> create()){
	$this -> error($helparticle -> getError());
      }
      if($helparticle -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $helparticle -> field('cid,title,sort,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询分类
    $result_class = M('HelpClass') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_class', $result_class);
    $this -> display();
  }

  /* ----------- 帮助中心 ------------ */
}

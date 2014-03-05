<?php
class ContentAction extends CommonAction {
  public function infomationonecolumn(){
    $infoonecolumn = M('InfoOneColumn');
    $where = array();
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $infoonecolumn -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $infoonecolumn -> field('id,name,remark,isshow,isnav,sort,hotcommentnum,hotpointnum,slideimgnum,listpagernum') -> order('sort') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addinfomationonecolumn(){
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

  public function editinfomationonecolumn(){
    $infoonecolumn = D('InfoOneColumn');
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

  public function infomationtwocolumn(){
    $infotwocolumn = M('InfoTwoColumn');
    $infoonecolumn = M('InfoOneColumn');
    $where = array();
    if(!empty($_POST['name'])){
      $where['itc.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_REQUEST['oneid'])){
      $where['itc.oneid'] = $this -> _request('oneid', 'intval');
    }

    $count = $infotwocolumn -> table('yesow_info_two_column as itc') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    $result = $infotwocolumn -> table('yesow_info_two_column as itc') -> field('itc.id,itc.name,ioc.name as oname,itc.sort,itc.remark,itc.isoneshow,itc.leftpicnum,itc.hotpointnum,itc.pagernum') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> join('yesow_info_one_column as ioc ON itc.oneid = ioc.id') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addinfomationtwocolumn(){
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
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    $this -> display();
  }

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
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    $result = $infotwocolumn -> field('id,oneid,name,sort,remark,isoneshow,leftpicnum,hotpointnum,pagernum') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function titleattribute(){
    $titleattribute = M('InfoTitleAttribute');
    $where = array();
     if(!empty($_POST['name'])){
      $where['ita.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_REQUEST['oneid'])){
      $where['ita.oneid'] = $this -> _request('oneid', 'intval');
    }

    $count = $titleattribute -> table('yesow_info_title_attribute as ita') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $titleattribute -> table('yesow_info_title_attribute as ita') -> field('ita.id,ita.name,ioc.name as oname,ita.sort,ita.remark') -> where($where) -> join('yesow_info_one_column AS ioc ON ita.oneid = ioc.id') -> order('sort') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addtitleattribute(){
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
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    $this -> display();
  }

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

  public function edittitleattribute(){
    $titleattribute = D('InfoTitleAttribute');
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
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    $result = $titleattribute -> field('id,name,oneid,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

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

    if(!empty($_REQUEST['id'])){
      $result = $contentattribute -> field('name') -> find($this -> _request('id', 'intval'));
    }
    $pidname = isset($result['name']) ? $result['name'] : '无';
    $this -> assign('pidname', $pidname);

    $count = $contentattribute -> table('yesow_info_content_attribute as ica') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $contentattribute -> table('yesow_info_content_attribute AS ica') -> field('ica.id,ica.name,ioc.name as oname,ica.sort,ica.remark') -> where($where) -> join('yesow_info_one_column AS ioc ON ica.oneid = ioc.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);

    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);

    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addcontentattribute(){
    $contentattribute = D('InfoContentAttribute');
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

    $pid = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    if(!empty($_REQUEST['id'])){
      $result = $contentattribute -> field('name') -> find($this -> _request('id', 'intval'));
    }
    $pidname = isset($result['name']) ? $result['name'] : '无';
    $this -> assign('pid', $pid);
    $this -> assign('pidname', $pidname);

    $infoonecolumn = M('InfoOneColumn');
    $where_one = array();
    if(!empty($_REQUEST['id'])){
      $oneid = $contentattribute -> getFieldByid($this -> _request('id', 'intval'), 'oneid');
      $where_one['id'] = $oneid;
    }
    $result_one = $infoonecolumn -> field('id,name') -> where($where_one) -> order('sort') -> select();
    $this -> assign('result_one', $result_one);
    $this -> display();
  }

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

  public function editcontentattribute(){
    $contentattribute = D('InfoContentAttribute');
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

    if($result['pid'] != 0){
      $pname = $contentattribute -> getFieldByid($result['pid'], 'name');
      $this -> assign('pname', $pname);
    }
    $infoonecolumn = M('InfoOneColumn');
    $result_one = $infoonecolumn -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one', $result_one);

    $this -> display();
  }

  public function article(){
    $infoarticle = M('InfoArticle');
    $where = array();
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


    $count = $infoarticle -> table('yesow_info_article as ia') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $infoarticle -> table('yesow_info_article as ia') -> field('ia.id,ioc.name as classname,itc.name as colname,ita.name as titlename,ica.name as contentname,ia.title,a.name as aname,m.name as mname,ia.hits,ia.addtime,ia.checktime,ia.status,ctc.count') -> where($where) -> join('yesow_info_two_column as itc ON ia.colid = itc.id') -> join('yesow_info_one_column as ioc ON ia.classid = ioc.id') -> join('yesow_info_title_attribute as ita ON ia.tid = ita.id') -> join('yesow_info_content_attribute as ica ON ia.conid = ica.id') -> join('yesow_admin as a ON ia.auditid = a.id') -> join('yesow_member as m ON ia.authorid = m.id') -> join('(SELECT aid,COUNT(id) as count FROM yesow_info_article_comment GROUP BY aid) as ctc ON ia.id = ctc.aid') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('status ASC,addtime DESC') -> select();
    $this -> assign('result', $result);

    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function delarticle(){
    $infoarticlepic = M('InfoArticlePic');
    $where_pic['aid'] = array('in', $_POST['ids']);
    $result_pic = $infoarticlepic -> where($where_pic) -> field('address') -> select();
    foreach($result_pic as $value){
      @unlink($value['address']);
    }
    $infoarticlepic -> where($where_pic) -> delete();
    $infoarticle = M('InfoArticle');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($infoarticle -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editarticle(){
    $infoarticle = D('InfoArticle');
    if(!empty($_POST['title'])){
      $childsite_infoarticle = M('ChildsiteInfoarticle');
      $childsite_infoarticle -> where(array('iaid' => $this -> _post('id', 'intval'))) -> delete();
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
      $infoarticlepic = M('InfoArticlePic');
      $infoarticlepic -> where(array('aid' => $this -> _post('id', 'intval'))) -> delete();
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

      if(!$infoarticle -> create()){
	$this -> error($infoarticle -> getError());
      }
      $infoarticle -> save();
      $this -> success(L('DATA_UPDATE_SUCCESS'));

    }
    $result = $infoarticle -> field('id,classid,title,colid,tid,conid,content,source,keyword,tel,qqcode,email,address,unit') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $conattpid = M('InfoContentAttribute') -> getFieldByid($result['conid'], 'pid');
    if($conattpid != 0){
      $result_contwoatt = M('InfoContentAttribute') -> field('id,name') -> where(array('pid' => $conattpid)) -> select();
      $this -> assign('result_contwoatt', $result_contwoatt);
    }
    $result_one_col = M('InfoOneColumn') -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one_col', $result_one_col);
    $result_two_col = M('InfoTwoColumn') -> field('id,name') -> where(array('oneid' => $result['classid'])) -> order('sort') -> select();
    $this -> assign('result_two_col', $result_two_col);
    $result_title = M('InfoTitleAttribute') -> field('id,name') -> where(array('oneid' => $result['classid'])) -> order('sort') -> select();
    $this -> assign('result_title', $result_title);
    $result_content = M('InfoContentAttribute') -> field('id,name') -> where(array('oneid' => $result['classid'], 'pid' => 0)) -> order('sort') -> select();
    $this -> assign('result_content', $result_content);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_childsite_infoarticle_temp = M('ChildsiteInfoarticle') -> field('csid') -> where(array('iaid' => $this -> _get('id', 'intval'))) -> select();
    $result_childsite_infoarticle = array();
    foreach($result_childsite_infoarticle_temp as $value){
      $result_childsite_infoarticle[] = $value['csid'];
    }
    $this -> assign('result_childsite_infoarticle', $result_childsite_infoarticle);
    
    $this -> display();
  }

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

  public function infoimage(){
    $infoarticlepic = M('InfoArticlePic');
    $result = $infoarticlepic -> table('yesow_info_article_pic as iap') -> field('iap.id,iap.address,itc.name as cname,isshow_index,isshow_onelist,isshow_twolist') -> where(array('aid' => $this -> _get('id', 'intval'))) -> join('yesow_info_two_column as itc ON iap.colid = itc.id') -> select();
    $this -> assign('result', $result);
    $this -> display();
  }

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

  public function infocomment(){
    $comment = M('InfoArticleComment');
    $where = array();
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

    $count = $comment -> table('yesow_info_article_comment as iac') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $comment -> table('yesow_info_article_comment as iac') -> field('iac.id,ia.title,ia.id as iaid,iac.floor,iac.content,m.name,iac.addtime,iac.status') -> where($where) -> order('status ASC,iac.addtime DESC') -> join('yesow_info_article as ia ON iac.aid = ia.id') -> join('yesow_member as m ON iac.mid = m.id') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editinfocomment(){
    $comment = D('index://InfoArticleComment');
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

  public function titlenotice(){
    $notice = M('TitleNotice');
    $where = array();
    if(!empty($_POST['title'])){
      $where['tn.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    $count = $notice -> table('yesow_title_notice as tn') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $notice -> table('yesow_title_notice as tn') -> field('tn.id,tnt.name as tname,tn.title,tn.addtime') -> where($where) -> join('yesow_title_notice_type as tnt ON tn.tid = tnt.id') -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();  
  }

  public function addtitlenotice(){
    if(!empty($_POST['title'])){
      $notice = D('TitleNotice');
      if(!$notice -> create()){
	$this -> error($notice -> getError());
      }
      if($notice -> add()){
	S('index_title_notice', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_type = M('TitleNoticeType') -> field('id,name') -> select();
    $this -> assign('result_type', $result_type);
    $this -> display();
  }

  public function deltitlenotice(){
    $notice = M('TitleNotice');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($notice -> where($where_del) -> delete()){
      S('index_title_notice', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function edittitlenotice(){
    $notice = D('TitleNotice');
    if(!empty($_POST['title'])){
      if(!$notice -> create()){
	$this -> error($notice -> getError());
      }
      if($notice -> save()){
	S('index_title_notice', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $notice -> field('tid,title,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $result_type = M('TitleNoticeType') -> field('id,name') -> select();
    $this -> assign('result_type', $result_type);
    $this -> display();
  }

  public function yesownotice(){
    $notice = M('Notice');
    $where = array();
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

    $count = $notice -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $notice -> field('id,titleattribute,title,source,clickcount,addtime') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addyesownotice(){
    if(!empty($_POST['title'])){
      $notice = D('Notice');
      if(!$notice -> create()){
	$this -> error($notice -> getError());
      }
      if($id = $notice -> add()){
	if(!empty($_POST['childsite'])){
	  foreach($_POST['childsite'] as $value){
	    $data = array();
	    $data['nid'] = $id;
	    $data['csid'] = $value;
	    M('NoticeChildsite') -> add($data);
	  }
	}
	S('index_yesow_notice', NULL, NULL, '', NULL, 'index');
	S('index_yesow_notice', NULL, NULL, '', NULL, 'member');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  public function edityesownotice(){
    $notice = D('Notice');
    $notice_childsite = M('NoticeChildsite');
    if(!empty($_POST['title'])){
      $id = $this -> _post('id', 'intval');
      $notice_childsite -> where(array('nid' => $id)) -> delete();
      if(!empty($_POST['childsite'])){
	foreach($_POST['childsite'] as $value){
	  $data = array();
	  $data['nid'] = $id;
	  $data['csid'] = $value;
	  $notice_childsite -> add($data);
	}
      }
      if(!$notice -> create()){
	$this -> error($notice -> getError());
      }
      $notice -> save();
      S('index_yesow_notice', NULL, NULL, '', NULL, 'index');
      S('index_yesow_notice', NULL, NULL, '', NULL, 'member');
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }
    $result = $notice -> field('title,titleattribute,keywords,content,source') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> assign('titleattribute', array('公告','促销','申明','消息'));
    $temp_notice_childsite = $notice_childsite -> field('csid') -> where(array('nid' => $this -> _get('id', 'intval'))) -> select();
    $result_notice_childsite = array();
    foreach($temp_notice_childsite as $value){
      $result_notice_childsite[] = $value['csid'];
    }
    $this -> assign('result_notice_childsite', $result_notice_childsite);
    $this -> display();
  }

  public function delyesownotice(){
    $notice = M('Notice');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($notice -> where($where_del) -> delete()){
      S('index_yesow_notice', NULL, NULL, '', NULL, 'index');
      S('index_yesow_notice', NULL, NULL, '', NULL, 'member');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function noticecomment(){
    $comment = M('NoticeComment');
    $where = array();
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

    $count = $comment -> table('yesow_notice_comment as nc') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $comment -> table('yesow_notice_comment as nc') -> field('nc.id,n.title,n.id as nid,nc.floor,nc.content,m.name,nc.addtime,nc.status') -> where($where) -> order('status ASC,nc.addtime DESC') -> join('yesow_notice as n ON nc.nid = n.id') -> join('yesow_member as m ON nc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editnoticecomment(){
    $comment = D('index://NoticeComment');
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

  public function agent(){
    $agent = M('AgentJoin');
    $result = $agent -> field('id,title,sort,remark') -> select();
    $this -> assign('result', $result);
    $this -> display();
  }

  public function addagent(){
    if(!empty($_POST['title'])){
      $agent = M('AgentJoin');
      if(!$agent -> create()){
	$this -> error($agent -> getError());
      }
      if($agent -> add()){
	S('index_agent_join', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delagent(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $agent = M('AgentJoin');
    if($agent -> where($where_del) -> delete()){
      S('index_agent_join', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editagent(){
    $agent = M('AgentJoin');
    if(!empty($_POST['title'])){
      if(!$agent -> create()){
	$this -> error($agent -> getError());
      }
      if($agent -> save()){
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

  public function agentapply(){
    $agent_add = M('AgentAdd');
    $result = $agent_add -> field('id,type,addidea,advice') -> order('id DESC') -> select();
    $this -> assign('result', $result);
    $this -> display();
  }

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

  public function editagentapply(){
    $agent_add = M('AgentAdd');
    $result = $agent_add -> table('yesow_agent_add as aa') -> field('type,cs.name as csname,csa.name as csaname,aat.name as aatname,aa.name,registeredcapital,address,linkman,tel,qqcode,email,website,businessproject,p_name,p_birthday,p_tel,p_telphone,p_address,p_qqcode,employeesnum,starttime,ownership,sitetpe,businessarea,computernum,printernum,interdevicenum,fexnum,telnum,addidea,advice,other') -> join('yesow_child_site as cs ON aa.csid = cs.id') -> join('yesow_child_site_area as csa ON aa.csaid = csa.id') -> join('yesow_agent_add_type as aat ON aa.tid = aat.id') -> where(array('aa.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  
  }

  public function helpclass(){
    $helpclass = M('HelpClass');
    $where = array();

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $helpclass -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $helpclass -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addhelpclass(){
    if(!empty($_POST['name'])){
      $helpclass = M('HelpClass');
      if(!$helpclass -> create()){
	$this -> error($helpclass -> getError());
      }
      if($helpclass -> add()){
	S('index_bottomhelp', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delhelpclass(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $helpclass = M('HelpClass');
    if($helpclass -> where($where_del) -> delete()){
      S('index_bottomhelp', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function edithelpclass(){
    $helpclass = M('HelpClass');
    if(!empty($_POST['name'])){
      if(!$helpclass -> create()){
	$this -> error($helpclass -> getError());
      }
      if($helpclass -> save()){
	S('index_bottomhelp', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $helpclass -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  
  }

  public function helparticle(){
    $helparticle = M('HelpArticle');
    $where = array();
    if(!empty($_POST['title'])){
      $where['ha.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($_POST['cid'])){
      $where['ha.cid'] = $this -> _post('cid');
    }

    $count = $helparticle -> table('yesow_help_article as ha') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $helparticle -> table('yesow_help_article as ha') -> field('ha.id,hc.name as cname,ha.title,ha.sort,ha.addtime') -> join('yesow_help_class as hc ON ha.cid = hc.id') -> order('ha.addtime DESC') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $result_class = M('HelpClass') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_class', $result_class);
    $this -> display();
  }

  public function addhelparticle(){
    if(!empty($_POST['title'])){
      $helparticle = D('HelpArticle');
      if(!$helparticle -> create()){
	$this -> error($helparticle -> getError());
      }
      if($helparticle -> add()){
	S('index_bottomhelp', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_class = M('HelpClass') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_class', $result_class);
    $this -> display();
  
  }

  public function delhelparticle(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $helparticle = M('HelpArticle');
    if($helparticle -> where($where_del) -> delete()){
      S('index_bottomhelp', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function edithelparticle(){
    $helparticle = D('HelpArticle');
    if(!empty($_POST['title'])){
      if(!$helparticle -> create()){
	$this -> error($helparticle -> getError());
      }
      if($helparticle -> save()){
	S('index_bottomhelp', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $helparticle -> field('cid,title,sort,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $result_class = M('HelpClass') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_class', $result_class);
    $this -> display();
  }

  public function storerenttype(){
    $storetype = M('StoreRentType');
    $where = array();

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $storetype -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $storetype -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addstorerenttype(){
    if(!empty($_POST['name'])){
      $storetype = M('StoreRentType');
      if(!$storetype -> create()){
	$this -> error($storetype -> getError());
      }
      if($storetype -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delstorerenttype(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $storetype = M('StoreRentType');
    if($storetypes -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editstorerenttype(){
    $storetype = M('StoreRentType');
    if(!empty($_POST['name'])){
      if(!$storetype -> create()){
	$this -> error($storetype -> getError());
      }
      if($storetype -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $storetype -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function storerent(){
    $rent = M('StoreRent');

    if(!empty($_POST['search_name'])){
      if($_POST['search_key'] == 'title'){
	$where['sr.title'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'csid'){
	$csid = M('ChildSite') -> getFieldByname($this -> _post('search_name'), 'id');
	$where['sr.csid'] = $csid;
      }else if($_POST['search_key'] == 'mid'){
	$mid = M('Member') -> getFieldByname($this -> _post('search_name'), 'id');
	$where['sr.mid'] = $mid;
      }
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['sr.addtime'] = array(array('egt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['sr.endtime'] = array('elt', $endtime);
    }

    $count = $rent -> table('yesow_store_rent as sr') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $time = time();
    $result = $rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,cs.name as csname,m.name as mname,sr.addtime,sr.updatetime,sr.ischeck,sr.clickcount,tmp.id as tmpid,tmp.endtime,tmp.sort') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> join('yesow_member as m ON sr.mid = m.id') -> join("LEFT JOIN (SELECT * FROM yesow_store_rent_sort WHERE starttime <= {$time} AND endtime >= {$time} ORDER BY id DESC) as tmp ON sr.id = tmp.srid") -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('sr.updatetime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addstorerent(){
    if(!empty($_POST['title'])){
      $rent = D('StoreRent');
      if(!$rent -> create()){
	$this -> error($rent -> getError());
      }
      if(!empty($_FILES['rentimage']['name'])){
	$up_data = R('Public/store_pic_upload');
	$rent -> image = $up_data[0]['savename'];
      }
      if($rent -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_store_type = M('StoreRentType') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_store_type', $result_store_type);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  public function delstorerent(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $rent = M('StoreRent');
    if($rent -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editstorerent(){
    $rent = D('StoreRent');
    if(!empty($_POST['title'])){
      if(!$rent -> create()){
	$this -> error($rent -> getError());
      }
      if(!empty($_FILES['rentimage']['name'])){
	$up_data = R('Public/store_pic_upload');
	$rent -> image = $up_data[0]['savename'];
      }
      if($rent -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $rent -> field('tid,csid,csaid,title,keyword,systemimage,image,content,linkman,qqcode,address,email,tel,endtime') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $result_store_type = M('StoreRentType') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_store_type', $result_store_type);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    $this -> display();
  }

  public function editrecommendstorerent(){
    $rent_sort = D('StoreRentSort');
    if(!empty($_POST['sort'])){
      if($rent_sort -> where(array('srid' => $_POST['srid'])) -> select()){
	$rent_sort -> where(array('srid' => $_POST['srid'])) -> delete();
      }
      if(!$rent_sort -> create()){
	$this -> error($rent_sort -> getError());
      }
      if($rent_sort -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $time = time();
    $where = array();
    $where['srid'] = $this -> _get('id', 'intval');
    $where['starttime'] = array('elt', $time);
    $where['endtime'] = array('egt', $time);
    $result = $rent_sort -> field('starttime,endtime,sort') -> where(array('srid' => $this -> _get('id', 'intval'))) -> order('id DESC') -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  public function passauditstorerent(){
    $rent = M('StoreRent');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($rent -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditstorerent(){
    $rent = M('StoreRent');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($rent -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    } 
  }

  public function editrstoreentimage(){
    $rent = M('StoreRent');
    $image = $rent -> getFieldByid($this -> _get('id', 'intval'), 'image');
    $this -> assign('image', $image);
    $this -> display();
  }

  public function storerentcomment(){
    $comment = M('StoreRentComment');
    $where = array();
    if(!empty($_POST['content'])){
      $where['src.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
    }
    if(!empty($_POST['author'])){
      $member = M('Member');
      $authorid = $member -> getFieldByname($this -> _post('author'), 'id');
      $where['src.mid'] = intval($authorid);
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['src.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['src.addtime'][] = array('lt', $endtime);
    }

    $count = $comment -> table('yesow_store_rent_comment as src') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $comment -> table('yesow_store_rent_comment as src') -> field('src.id,src.srid,sr.title,src.floor,src.content,m.name as mname,src.addtime,src.status,src.face') -> where($where) -> order('src.status ASC,src.addtime DESC') -> join('yesow_store_rent as sr ON src.srid = sr.id') -> join('yesow_member as m ON src.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editstorerentcomment(){
    $comment = D('index://StoreRentComment');
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
    $result = $comment -> table('yesow_store_rent_comment as src') -> field('sr.title,m.name as mname,src.floor,src.content,src.face') -> join('yesow_store_rent as sr ON src.srid = sr.id') -> join('yesow_member as m ON src.mid = m.id') -> where(array('src.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  public function delstorerentcomment(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $comment = M('StoreRentComment');
    if($comment -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function passauditstorerentcomment(){
    $comment = M('StoreRentComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 2);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditstorerentcomment(){
    $comment = M('StoreRentComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 1);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function sellusedtype(){
    $type = M('SellUsedType');
    $where['pid'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    $count = $type -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $type -> field('id,name,sort,remark') -> where($where) -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addsellusedtype(){
     $type = M('SellUsedType');
    if(!empty($_POST['name'])){
      if(!$type -> create()){
	$this -> error($type -> getError());
      }
      if($type -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $pname = $type -> getFieldByid($this -> _get('id', 'intval'), 'name');
    $this -> assign('pname', $pname);
    $this -> display();
  }

  public function delsellusedtype(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $type = M('SellUsedType');
    if($type -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editsellusedtype(){
    $type = M('SellUsedType');
    if(!empty($_POST['name'])){
      if(!$type -> create()){
	$this -> error($type -> getError());
      }
      if($type -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $type -> field('name,pid,sort,remark') -> find($this -> _get('id', 'intval'));
    $pname = $type -> getFieldByid($result['pid'], 'name');
    $this -> assign('pname', $pname);
    $this -> assign('result', $result);
    $this -> display();
  }

  public function sellusedcolor(){
    $color = M('SellUsedColor');
    $where = array();

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $color -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $color -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addsellusedcolor(){
    if(!empty($_POST['name'])){
      $color = M('SellUsedColor');
      if(!$color -> create()){
	$this -> error($color -> getError());
      }
      if($color -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delsellusedcolor(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $color = M('SellUsedColor');
    if($color -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editsellusedcolor(){
    $color = M('SellUsedColor');
    if(!empty($_POST['name'])){
      if(!$color -> create()){
	$this -> error($color -> getError());
      }
      if($color -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $color -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function sellused(){
    $sellused = M('SellUsed');
    $where = array();
    $time = time();

    if(!empty($_POST['search_name'])){
      if($_POST['search_key'] == 'title'){
	$where['su.title'] = array('LIKE', '%' . $this -> _post('search_name') . '%');
      }else if($_POST['search_key'] == 'csid'){
	$csid = M('ChildSite') -> getFieldByname($this -> _post('search_name'), 'id');
	$where['su.csid'] = $csid;
      }else if($_POST['search_key'] == 'mid'){
	$mid = M('Member') -> getFieldByname($this -> _post('search_name'), 'id');
	$where['su.mid'] = $mid;
      }
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['su.addtime'] = array(array('egt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['su.endtime'] = array('elt', $endtime);
    }

    $count = $sellused -> table('yesow_sell_used as su') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $sellused -> table('yesow_sell_used as su') -> field('su.id,cs.name as csname,su.title,sut.name as sutname,su.price,su.linkman,m.name as mname,su.addtime,su.ischeck,su.clickcount,tmp.id as tmpid,tmp.endtime,tmp.sort') -> join('yesow_child_site as cs ON su.csid = cs.id') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_member as m ON su.mid = m.id') -> join("LEFT JOIN (SELECT * FROM yesow_sell_used_sort WHERE starttime <= {$time} AND endtime >= {$time} ORDER BY id DESC) as tmp ON su.id = tmp.suid") -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> order('su.updatetime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addsellused(){
    if(!empty($_POST['title'])){
      $sellused = D('SellUsed');
      if(!$sellused -> create()){
	$this -> error($sellused -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	$up_data = R('Public/sellused_pic_upload');
	$sellused -> image = $up_data[0]['savename'];
      }
      if($sellused -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_type_one = M('SellUsedType') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_type_one', $result_type_one);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_color = M('SellUsedColor') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_color', $result_color);
    $this -> display();
  }

  public function delsellused(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $sellused = M('SellUsed');
    if($sellused -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editsellused(){
    $sellused = D('SellUsed');
    if(!empty($_POST['title'])){
      if(!$sellused -> create()){
	$this -> error($sellused -> getError());
      }
      if(!empty($_FILES['image']['name'])){
	$up_data = R('Public/sellused_pic_upload');
	$sellused -> image = $up_data[0]['savename'];
      }
      if($sellused -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }

    $result = $sellused -> field('tid_one,tid_two,csid,csaid,endtime,cid,title,keyword,image,price,tel,linkman,email,address,content') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $result_type_one = M('SellUsedType') -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_type_one', $result_type_one);
    $result_type_two = M('SellUsedType') -> field('id,name') -> where(array('pid' => $result['tid_one'])) -> order('sort ASC') -> select();
    $this -> assign('result_type_two', $result_type_two);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    $result_color = M('SellUsedColor') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_color', $result_color);
    $this -> display();
  }

  public function editshowsellimage(){
    $sellused = M('SellUsed');
    $image = $sellused -> getFieldByid($this -> _get('id', 'intval'), 'image');
    $this -> assign('image', $image);
    $this -> display();
  }

  public function passauditsellused(){
    $sellused = M('SellUsed');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($sellused -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditsellused(){
    $sellused = M('SellUsed');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($sellused -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function editrecommendsellused(){
    $sell_sort = D('SellUsedSort');
    if(!empty($_POST['sort'])){
      if($sell_sort -> where(array('suid' => $_POST['suid'])) -> select()){
	$sell_sort -> where(array('suid' => $_POST['suid'])) -> delete();
      }
      if(!$sell_sort -> create()){
	$this -> error($sell_sort -> getError());
      }
      if($sell_sort -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $time = time();
    $where = array();
    $where['suid'] = $this -> _get('id', 'intval');
    $where['starttime'] = array('elt', $time);
    $where['endtime'] = array('egt', $time);
    $result = $sell_sort -> field('starttime,endtime,sort') -> where(array('suid' => $this -> _get('id', 'intval'))) -> order('id DESC') -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  public function sellusedcomment(){
    $comment = M('SellUsedComment');
    $where = array();
    if(!empty($_POST['content'])){
      $where['suc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
    }
    if(!empty($_POST['author'])){
      $member = M('Member');
      $authorid = $member -> getFieldByname($this -> _post('author'), 'id');
      $where['suc.mid'] = intval($authorid);
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['suc.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['suc.addtime'][] = array('lt', $endtime);
    }

    $count = $comment -> table('yesow_sell_used_comment as suc') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $comment -> table('yesow_sell_used_comment as suc') -> field('suc.id,suc.suid,su.title,suc.floor,suc.content,m.name as mname,suc.addtime,suc.status,suc.face') -> where($where) -> order('suc.status ASC,suc.addtime DESC') -> join('yesow_sell_used as su ON suc.suid = su.id') -> join('yesow_member as m ON suc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editsellusedcomment(){
    $comment = D('index://SellUsedComment');
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
    $result = $comment -> table('yesow_sell_used_comment as suc') -> field('su.title,m.name as mname,suc.floor,suc.content,suc.face') -> join('yesow_sell_used as su ON suc.suid = su.id') -> join('yesow_member as m ON suc.mid = m.id') -> where(array('suc.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  public function delsellusedcomment(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $comment = M('SellUsedComment');
    if($comment -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function passauditsellusedcomment(){
    $comment = M('SellUsedComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 2);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditsellusedcomment(){
    $comment = M('SellUsedComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 1);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function companyindustry(){
    $RecruitCompanyIndustry = M('RecruitCompanyIndustry');
    $where = array();

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $RecruitCompanyIndustry -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $RecruitCompanyIndustry -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addcompanyindustry(){
    if(!empty($_POST['name'])){
      $RecruitCompanyIndustry = M('RecruitCompanyIndustry');
      if(!$RecruitCompanyIndustry -> create()){
	$this -> error($RecruitCompanyIndustry -> getError());
      }
      if($RecruitCompanyIndustry -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delcompanyindustry(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $RecruitCompanyIndustry = M('RecruitCompanyIndustry');
    if($RecruitCompanyIndustry -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editcompanyindustry(){
    $RecruitCompanyIndustry = M('RecruitCompanyIndustry');
    if(!empty($_POST['name'])){
      if(!$RecruitCompanyIndustry -> create()){
	$this -> error($RecruitCompanyIndustry -> getError());
      }
      if($RecruitCompanyIndustry -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $RecruitCompanyIndustry -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function companyemploynum(){
    $RecruitCompanyEmploynum = M('RecruitCompanyEmploynum');
    $where = array();

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $RecruitCompanyEmploynum -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $RecruitCompanyEmploynum -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addcompanyemploynum(){
    if(!empty($_POST['name'])){
      $RecruitCompanyEmploynum = M('RecruitCompanyEmploynum');
      if(!$RecruitCompanyEmploynum -> create()){
	$this -> error($RecruitCompanyEmploynum -> getError());
      }
      if($RecruitCompanyEmploynum -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delcompanyemploynum(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $RecruitCompanyEmploynum = M('RecruitCompanyEmploynum');
    if($RecruitCompanyEmploynum -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editcompanyemploynum(){
    $RecruitCompanyEmploynum = M('RecruitCompanyEmploynum');
    if(!empty($_POST['name'])){
      if(!$RecruitCompanyEmploynum -> create()){
	$this -> error($RecruitCompanyEmploynum -> getError());
      }
      if($RecruitCompanyEmploynum -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $RecruitCompanyEmploynum -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function companyregistermoney(){
    $RecruitCompanyRegistermoney = M('RecruitCompanyRegistermoney');
    $where = array();

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $RecruitCompanyRegistermoney -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $RecruitCompanyRegistermoney -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addcompanyregistermoney(){
    if(!empty($_POST['name'])){
      $RecruitCompanyRegistermoney = M('RecruitCompanyRegistermoney');
      if(!$RecruitCompanyRegistermoney -> create()){
	$this -> error($RecruitCompanyRegistermoney -> getError());
      }
      if($RecruitCompanyRegistermoney -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delcompanyregistermoney(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $RecruitCompanyRegistermoney = M('RecruitCompanyRegistermoney');
    if($RecruitCompanyRegistermoney -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editcompanyregistermoney(){
    $RecruitCompanyRegistermoney = M('RecruitCompanyRegistermoney');
    if(!empty($_POST['name'])){
      if(!$RecruitCompanyRegistermoney -> create()){
	$this -> error($RecruitCompanyRegistermoney -> getError());
      }
      if($RecruitCompanyRegistermoney -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $RecruitCompanyRegistermoney -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function companynature(){
    $RecruitCompanyNature = M('RecruitCompanyNature');
    $where = array();

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $RecruitCompanyNature -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $RecruitCompanyNature -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addcompanynature(){
    if(!empty($_POST['name'])){
      $RecruitCompanyNature = M('RecruitCompanyNature');
      if(!$RecruitCompanyNature -> create()){
	$this -> error($RecruitCompanyNature -> getError());
      }
      if($RecruitCompanyNature -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function delcompanynature(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $RecruitCompanyNature = M('RecruitCompanyNature');
    if($RecruitCompanyNature -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editcompanynature(){
    $RecruitCompanyNature = M('RecruitCompanyNature');
    if(!empty($_POST['name'])){
      if(!$RecruitCompanyNature -> create()){
	$this -> error($RecruitCompanyNature -> getError());
      }
      if($RecruitCompanyNature -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $RecruitCompanyNature -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }


  public function recruit_company(){
    $RecruitCompany = M('RecruitCompany');
    $where = array();
    if(!empty($_POST['name'])){
      $where['rc.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $RecruitCompany -> table('yesow_recruit_company as rc') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $RecruitCompany -> table('yesow_recruit_company as rc') -> field('rc.id,cs.name as csname,csa.name as csaname,rc.name,m.name as mname,ci.name as ciname,ce.name as cename,cr.name as crname,cn.name as cnname,rc.linkman,rc.tel,rc.addtime,rc.ischeck') -> join('yesow_child_site as cs ON rc.csid = cs.id') -> join('yesow_child_site_area as csa ON rc.csaid = csa.id') -> join('yesow_member as m ON rc.mid = m.id') -> join('yesow_recruit_company_industry as ci ON rc.ciid = ci.id') -> join('yesow_recruit_company_employnum as ce ON rc.ceid = ce.id') -> join('yesow_recruit_company_registermoney as cr ON rc.crid = cr.id') -> join('yesow_recruit_company_nature as cn ON rc.cnid = cn.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('rc.addtime DESC') -> select();

    foreach($result as $key => $value){
      $jid = M('RecruitJobs') -> field('id') -> where(array('cid' => $value['id'])) -> find();
      $result[$key]['jid'] = $jid['id'];
    }

    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  public function addrecruit_company(){
    if(!empty($_POST['name'])){
      $RecruitCompany = M('RecruitCompany');
      if(!$RecruitCompany -> create()){
	$this -> error($RecruitCompany -> getError());
      }
      if(!empty($_FILES['pic']['name'])){
	$up_data = R('Public/recruit_company_pic_upload');
	$RecruitCompany -> pic = $up_data[0]['savename'];
      }
      $RecruitCompany -> addtime = time();
      if($RecruitCompany -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_industry = M('RecruitCompanyIndustry') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_industry', $result_industry);
    $result_employnum = M('RecruitCompanyEmploynum') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_employnum', $result_employnum);
    $result_registermoney = M('RecruitCompanyRegistermoney') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_registermoney', $result_registermoney);
    $result_nature = M('RecruitCompanyNature') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_nature', $result_nature);
    $this -> display();
  }

  public function delrecruit_company(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $RecruitCompany = M('RecruitCompany');
    if($RecruitCompany -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editrecruit_company(){
    $RecruitCompany = M('RecruitCompany');
    if(!empty($_POST['name'])){
      if(!$RecruitCompany -> create()){
	$this -> error($RecruitCompany -> getError());
      }
      if(!empty($_FILES['pic']['name'])){
	$up_data = R('Public/recruit_company_pic_upload');
	$RecruitCompany -> pic = $up_data[0]['savename'];
      }
      if($RecruitCompany -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $RecruitCompany -> field('csid,csaid,ciid,ceid,crid,cnid,pic,name,address,linkman,website,email,tel,qqcode,abstract') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    $result_industry = M('RecruitCompanyIndustry') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_industry', $result_industry);
    $result_employnum = M('RecruitCompanyEmploynum') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_employnum', $result_employnum);
    $result_registermoney = M('RecruitCompanyRegistermoney') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_registermoney', $result_registermoney);
    $result_nature = M('RecruitCompanyNature') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_nature', $result_nature);
    $this -> display();
  }

  public function passauditrecruit_company(){
    $RecruitCompany = M('RecruitCompany');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($RecruitCompany -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditrecruit_company(){
    $RecruitCompany = M('RecruitCompany');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($RecruitCompany -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function jobsmonthlypay(){
    $RecruitJobsMonthlypay = M('RecruitJobsMonthlypay');
    $where = array();

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $RecruitJobsMonthlypay -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $RecruitJobsMonthlypay -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addjobsmonthlypay(){
    if(!empty($_POST['name'])){
      $RecruitJobsMonthlypay = M('RecruitJobsMonthlypay');
      if(!$RecruitJobsMonthlypay -> create()){
	$this -> error($RecruitJobsMonthlypay -> getError());
      }
      if($RecruitJobsMonthlypay -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function deljobsmonthlypay(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $RecruitJobsMonthlypay = M('RecruitJobsMonthlypay');
    if($RecruitJobsMonthlypay -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editjobsmonthlypay(){
    $RecruitJobsMonthlypay = M('RecruitJobsMonthlypay');
    if(!empty($_POST['name'])){
      if(!$RecruitJobsMonthlypay -> create()){
	$this -> error($RecruitJobsMonthlypay -> getError());
      }
      if($RecruitJobsMonthlypay -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $RecruitJobsMonthlypay -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function jobsdegree(){
    $RecruitJobsDegree = M('RecruitJobsDegree');
    $where = array();

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $RecruitJobsDegree -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $RecruitJobsDegree -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addjobsdegree(){
    if(!empty($_POST['name'])){
      $RecruitJobsDegree = M('RecruitJobsDegree');
      if(!$RecruitJobsDegree -> create()){
	$this -> error($RecruitJobsDegree -> getError());
      }
      if($RecruitJobsDegree -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function deljobsdegree(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $RecruitJobsDegree = M('RecruitJobsDegree');
    if($RecruitJobsDegree -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editjobsdegree(){
    $RecruitJobsDegree = M('RecruitJobsDegree');
    if(!empty($_POST['name'])){
      if(!$RecruitJobsDegree -> create()){
	$this -> error($RecruitJobsDegree -> getError());
      }
      if($RecruitJobsDegree -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $RecruitJobsDegree -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function jobsexperience(){
    $RecruitJobsExperience = M('RecruitJobsExperience');
    $where = array();

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $RecruitJobsExperience -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $RecruitJobsExperience -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addjobsexperience(){
    if(!empty($_POST['name'])){
      $RecruitJobsExperience = M('RecruitJobsExperience');
      if(!$RecruitJobsExperience -> create()){
	$this -> error($RecruitJobsExperience -> getError());
      }
      if($RecruitJobsExperience -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  public function deljobsexperience(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $RecruitJobsExperience = M('RecruitJobsExperience');
    if($RecruitJobsExperience -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editjobsexperience(){
    $RecruitJobsExperience = M('RecruitJobsExperience');
    if(!empty($_POST['name'])){
      if(!$RecruitJobsExperience -> create()){
	$this -> error($RecruitJobsExperience -> getError());
      }
      if($RecruitJobsExperience -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $RecruitJobsExperience -> field('name,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  public function recruit_jobs(){
    $RecruitJobs = M('RecruitJobs');
    $time = time();
    $where = array();

    if(!empty($_POST['name'])){
      $where['rj.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['rj.addtime'] = array(array('egt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['rj.endtime'] = array('elt', $endtime);
    }

    $count = $RecruitJobs -> alias('rj') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;
    $result = $RecruitJobs -> alias('rj') -> field('rj.id,cs.name as csname,rj.name,rj.num,rj.jobstype,rc.name as rcname,m.name as mname,rj.addtime,rj.ischeck,tmp.id as tmpid,tmp.endtime,tmp.sort') -> join('yesow_child_site as cs ON rj.jobs_csid = cs.id') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> join('yesow_member as m ON rc.mid = m.id') -> join("LEFT JOIN (SELECT * FROM yesow_recruit_jobs_sort WHERE starttime <= {$time} AND endtime >= {$time} ORDER BY id DESC) as tmp ON rj.id = tmp.rjid") -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('rj.ischeck ASC,rj.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addrecruit_jobs(){
    if(!empty($_POST['name'])){
      $RecruitJobs = M('RecruitJobs');
      if(!$RecruitJobs -> create()){
	$this -> error($RecruitJobs -> getError());
      }
      $RecruitJobs -> endtime = $this -> _post('endtime', 'strtotime');
      $RecruitJobs -> cid = $this -> _post('org2_id');
      $RecruitJobs -> addtime = time();
      if($RecruitJobs -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_monthlypay = M('RecruitJobsMonthlypay') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_monthlypay', $result_monthlypay);
    $result_degree = M('RecruitJobsDegree') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_degree', $result_degree);
    $result_experience = M('RecruitJobsExperience') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_experience', $result_experience);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  public function delrecruit_jobs(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $RecruitJobs = M('RecruitJobs');
    if($RecruitJobs -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editrecruit_jobs(){
    $RecruitJobs = M('RecruitJobs');
    if(!empty($_POST['name'])){
      if(!$RecruitJobs -> create()){
	$this -> error($RecruitJobs -> getError());
      }
      $RecruitJobs -> endtime = $this -> _post('endtime', 'strtotime');
      $RecruitJobs -> cid = $this -> _post('org2_id');
      if($RecruitJobs -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $RecruitJobs -> alias('rj') -> field('rj.cid,rc.name as rcname,rj.jmid,rj.jdid,rj.jeid,rj.name,rj.keyword,rj.english,rj.major,rj.sex,rj.age,rj.jobstype,rj.num,rj.jobs_csid,rj.jobs_csaid,rj.content,rj.endtime') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> where(array('rj.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $result_monthlypay = M('RecruitJobsMonthlypay') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_monthlypay', $result_monthlypay);
    $result_degree = M('RecruitJobsDegree') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_degree', $result_degree);
    $result_experience = M('RecruitJobsExperience') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_experience', $result_experience);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['jobs_csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    $this -> display();
  }

  public function editrecommendrecruit_jobs(){
    $RecruitJobsSort = M('RecruitJobsSort');
    if(!empty($_POST['sort'])){
      if($RecruitJobsSort -> where(array('rjid' => $_POST['rjid'])) -> select()){
	$RecruitJobsSort -> where(array('rjid' => $_POST['rjid'])) -> delete();
      }
      if(!$RecruitJobsSort -> create()){
	$this -> error($RecruitJobsSort -> getError());
      }
      $RecruitJobsSort -> starttime = $this -> _post('starttime', 'strtotime');
      $RecruitJobsSort -> endtime = $this -> _post('endtime', 'strtotime');
      if($RecruitJobsSort -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $time = time();
    $where = array();
    $where['rjid'] = $this -> _get('id', 'intval');
    $where['starttime'] = array('elt', $time);
    $where['endtime'] = array('egt', $time);
    $result = $RecruitJobsSort -> field('starttime,endtime,sort') -> where(array('rjid' => $this -> _get('id', 'intval'))) -> order('id DESC') -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  public function passauditrecruit_jobs(){
    $RecruitJobs = M('RecruitJobs');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 1);
    if($RecruitJobs -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditrecruit_jobs(){
    $RecruitJobs = M('RecruitJobs');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('ischeck' => 0);
    if($RecruitJobs -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function recruit_jobscomment(){
    $comment = M('RecruitJobsComment');
    $where = array();
    if(!empty($_POST['content'])){
      $where['rjc.content'] = array('LIKE', '%' . $this -> _post('content') . '%');
    }
    if(!empty($_POST['author'])){
      $member = M('Member');
      $authorid = $member -> getFieldByname($this -> _post('author'), 'id');
      $where['rjc.mid'] = intval($authorid);
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['rjc.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['rjc.addtime'][] = array('lt', $endtime);
    }

    $count = $comment -> alias('rjc') -> where($where) -> count();
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $comment -> alias('rjc') -> field('rjc.id,rjc.rjid,rj.name,rjc.floor,rjc.content,m.name as mname,rjc.addtime,rjc.status,rjc.face') -> where($where) -> order('rjc.status ASC,rjc.addtime DESC') -> join('yesow_recruit_jobs as rj ON rjc.rjid = rj.id') -> join('yesow_member as m ON rjc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function editrecruit_jobscomment(){
    $comment = D('index://RecruitJobsComment');
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
    $result = $comment -> alias('rjc') -> field('rj.name,m.name as mname,rjc.floor,rjc.content,rjc.face') -> join('yesow_recruit_jobs as rj ON rjc.rjid = rj.id') -> join('yesow_member as m ON rjc.mid = m.id') -> where(array('rjc.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }

  public function delrecruit_jobscomment(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $comment = M('RecruitJobsComment');
    if($comment -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function passauditrecruit_jobscomment(){
    $comment = M('RecruitJobsComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 2);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function nopassauditrecruit_jobscomment(){
    $comment = M('RecruitJobsComment');
    $where_audit = array();
    $where_audit['id'] = array('IN', $this -> _post('ids'));  
    $data_audit = array('status' => 1);
    if($comment -> where($where_audit) -> save($data_audit)){
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }else{
      $this -> error(L('DATA_UPDATE_ERROR'));
    }
  }

  public function attractinvestmentcategory(){
    $AttractinvestmentCategory = M('AttractinvestmentCategory');
    $where = array();
    if(!empty($_REQUEST['fid'])){
      $where['fid'] = $this -> _request('fid', 'intval');
    }else{
      $where['fid'] = 0;
    }

    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }

    $count = $AttractinvestmentCategory -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $AttractinvestmentCategory -> field('id,name,sort,remark') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('sort ASC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addattractinvestmentcategory(){
    $AttractinvestmentCategory = M('AttractinvestmentCategory');
    if(!empty($_POST['name'])){     
      if(!$AttractinvestmentCategory -> create()){
	$this -> error($AttractinvestmentCategory -> getError());
      }
      if($AttractinvestmentCategory -> add()){
	S('index_investment_nav', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> assign('fname', $AttractinvestmentCategory -> getFieldByid($this -> _get('fid', 'intval'), 'name'));
    $this -> display();
  }

  public function delattractinvestmentcategory(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $AttractinvestmentCategory = M('AttractinvestmentCategory');
    if($AttractinvestmentCategory -> where($where_del) -> delete()){
	S('index_investment_nav', NULL, NULL, '', NULL, 'index');
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editattractinvestmentcategory(){
    $AttractinvestmentCategory = M('AttractinvestmentCategory');
    if(!empty($_POST['name'])){
      if(!$AttractinvestmentCategory -> create()){
	$this -> error($AttractinvestmentCategory -> getError());
      }
      if($AttractinvestmentCategory -> save()){
	S('index_investment_nav', NULL, NULL, '', NULL, 'index');
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $AttractinvestmentCategory -> field('name,fid,sort,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> assign('fname', $AttractinvestmentCategory -> getFieldByid($result['fid'], 'name'));
    $this -> display();
  }
  
  public function attractinvestment(){
    $Attractinvestment = M('Attractinvestment');
    $where = array();
    if(!empty($_POST['title'])){
      $where['a.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($_POST['starttime'])){
      $addtime = $this -> _post('starttime', 'strtotime');
      $where['a.addtime'] = array(array('gt', $addtime));
    }
    if(!empty($_POST['endtime'])){
      $endtime = $this -> _post('endtime', 'strtotime');
      $where['a.addtime'][] = array('lt', $endtime);
    }

    $count = $Attractinvestment -> alias('a') -> where($where) -> count('id');
    import('ORG.Util.Page');
    if(! empty ( $_REQUEST ['listRows'] )){
      $listRows = $_REQUEST ['listRows'];
    } else {
      $listRows = 15;
    }
    $page = new Page($count, $listRows);
    $pageNum = !empty($_REQUEST['pageNum']) ? $_REQUEST['pageNum'] : 1;
    $page -> firstRow = ($pageNum - 1) * $listRows;

    $result = $Attractinvestment -> alias('a') -> field('a.id,a.title,a.source,a.clickcount,a.addtime,c1.name as c1name,c2.name as c2name') -> limit($page -> firstRow . ',' . $page -> listRows) -> join('yesow_attractinvestment_category as c1 ON a.cid_one = c1.id') -> join('yesow_attractinvestment_category as c2 ON a.cid_two = c2.id') -> where($where) -> order('a.addtime DESC') -> select();
    $this -> assign('result', $result);
    $this -> assign('listRows', $listRows);
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  public function addattractinvestment(){
    if(!empty($_POST['title'])){
      $Attractinvestment = M('Attractinvestment');
      if(!$Attractinvestment -> create()){
	$this -> error($Attractinvestment -> getError());
      }
      $Attractinvestment -> addtime = time();
      if($id = $Attractinvestment -> add()){
	if(!empty($_POST['childsite'])){
	  foreach($_POST['childsite'] as $value){
	    $data = array();
	    $data['aid'] = $id;
	    $data['csid'] = $value;
	    M('AttractinvestmentChildsite') -> add($data);
	  }
	}
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_category = M('AttractinvestmentCategory') -> field('id,name') -> where('fid=0') -> order('sort ASC') -> select();
    $this -> assign('result_category', $result_category);
    $this -> display();
  }

  public function delattractinvestment(){
    $Attractinvestment = M('Attractinvestment');
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    if($Attractinvestment -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  public function editattractinvestment(){
    $Attractinvestment = D('Attractinvestment');
    $AttractinvestmentChildsite = M('AttractinvestmentChildsite');
    if(!empty($_POST['title'])){
      $id = $this -> _post('id', 'intval');
      $AttractinvestmentChildsite -> where(array('aid' => $id)) -> delete();
      if(!empty($_POST['childsite'])){
	foreach($_POST['childsite'] as $value){
	  $data = array();
	  $data['aid'] = $id;
	  $data['csid'] = $value;
	  $AttractinvestmentChildsite -> add($data);
	}
      }
      if(!$Attractinvestment -> create()){
	$this -> error($Attractinvestment -> getError());
      }
      $Attractinvestment -> save();
      $this -> success(L('DATA_UPDATE_SUCCESS'));
    }
    $result = $Attractinvestment -> field('cid_one,cid_two,title,keywords,content,source') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $result_category = M('AttractinvestmentCategory') -> field('id,name') -> where('fid=0') -> order('sort ASC') -> select();
    $this -> assign('result_category', $result_category);
    $result_category_two = M('AttractinvestmentCategory') -> field('id,name') -> where(array('fid' => $result['cid_one'])) -> order('sort ASC') -> select();
    $this -> assign('result_category_two', $result_category_two);
    $temp_notice_childsite = $AttractinvestmentChildsite -> field('csid') -> where(array('aid' => $this -> _get('id', 'intval'))) -> select();
    $result_notice_childsite = array();
    foreach($temp_notice_childsite as $value){
      $result_notice_childsite[] = $value['csid'];
    }
    $this -> assign('result_notice_childsite', $result_notice_childsite);
    $this -> display();
  }
}

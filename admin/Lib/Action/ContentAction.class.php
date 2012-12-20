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
    $result = $infotwocolumn -> table('yesow_info_two_column as itc') -> field('itc.id,itc.name,ioc.name as oname,itc.sort,itc.remark,itc.isoneshow,itc.hotpointnum,itc.pagernum') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> join('yesow_info_one_column as ioc ON itc.oneid = ioc.id') -> select();
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
    $result = $infotwocolumn -> field('id,oneid,name,sort,remark,isoneshow,hotpointnum,pagernum') -> find($this -> _get('id', 'intval'));
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
}

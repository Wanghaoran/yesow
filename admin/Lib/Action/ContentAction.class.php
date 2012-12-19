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
    $infoonecolumn = M('InfoOneColumn');
    if($infoonecolumn -> delete($this -> _get('id', 'intval'))){
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
    if(!empty($_POST['oneid'])){
      $where['itc.oneid'] = $this -> _post('oneid', 'intval');
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
  
  }

  //编辑资讯二级栏目
  public function editinfomationtwocolumn(){
  
  }
}

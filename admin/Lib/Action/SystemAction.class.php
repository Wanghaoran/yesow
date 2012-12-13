<?php
/*
 * 系统设置
 */
class SystemAction extends CommonAction {
  
  //系统设置左边栏
  public function menu(){
    $this -> display();
  }

  //地区管理
  public function area(){
    $Area = M('Area');
    $where = array();
    //判断查询条件
    if(!empty($_REQUEST['name'])){
      $where['name'] = array('LIKE', '%' .  $_REQUEST['name'] . '%');
    }
    //记录总数
    $count = $Area -> where($where) -> count('id');
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
    $result = $Area -> field('id,name,remark') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

  //编辑地区信息
  public function editarea(){
    $area = D('Area');
    //处理编辑地区
    if(isset($_POST['name'])){
      if(!$area -> create()){
	$this -> error($area -> getError());
      }
      if($area -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }    
    $result = $area -> field('id,name,remark') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //添加地区信息
  public function addarea(){
    //验证添加信息
    if(isset($_POST['name'])){
      $area = D('Area');
      if(!$area -> create()){
	$this -> error($area -> getError());
      }
      if($area -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除地区信息
  public function deletearea(){
    $area = M('Area');
    if($area -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //分站模板管理
  public function childsitetemplate(){
    $childsitetemplate = M('ChildSiteTemplate');
    $where = array();
     //判断查询条件
    if(!empty($_REQUEST['name'])){
      $where['name'] = array('LIKE', '%' .  $_REQUEST['name'] . '%');
    }
    //记录总数
    $count = $childsitetemplate -> where($where) -> count('id');
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
    $result = $childsitetemplate -> field('id,name,address') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> assign('result', $result);
    $this -> display();
  }

  //添加分站模板
  public function addchildsitetemplate(){
    //处理添加信息
    if(isset($_POST['name'])){
      $childsitetemplate = D('ChildSiteTemplate');
      if(!$childsitetemplate -> create()){
	$this -> error($childsitetemplate -> getError());
      }
      if($childsitetemplate -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    $this -> display();
  }

  //删除分站模板
  public function delchildsitetemplate(){
    $childsitetemplate = M('ChildSiteTemplate');
    if($childsitetemplate -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑分站模板
  public function editchildsitetemplate(){
    $childsitetemplate = D('ChildSiteTemplate');
    //处理编辑地区
    if(isset($_POST['name'])){
      if(!$childsitetemplate -> create()){
	$this -> error($childsitetemplate -> getError());
      }
      if($childsitetemplate -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }    
    $result = $childsitetemplate -> field('id,name,address') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }

  //分站管理
  public function childsite(){
    $childsite = M('ChildSite');
    $where = array();
    $where_page = array(); //分页用查询条件
    //构建查询条件
    if(!empty($_POST['name'])){
      $where['cs.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
      $where_page['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_POST['aid'])){
      $where['cs.aid'] = $this -> _post('aid', 'intval');
      $where_page['aid'] = $this -> _post('aid', 'intval');
    }
    //记录总数
    $count = $childsite -> where($where_page) -> count('id');
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
    //查询地区数据
    $area = M('area');
    $result_area = $area -> field('id,name') -> select();
    $this -> assign('result_area', $result_area);
    //查询分站数据
    $result = $childsite -> table('yesow_child_site as cs') -> field('cs.id as id,cs.name as name,a.name as aname,cst.name as cstname,css.name as pname,cs.domain,cs.code,cs.create_time,cs.isshow') -> join('yesow_area as a ON a.id = cs.aid ') -> join('yesow_child_site_template as cst ON cst.id = cs.tid') -> join('yesow_child_site as css ON css.id = cs.pid') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //添加分站
  public function addchildsite(){
    $childsite = D('ChildSite');
    //处理添加数据
    if(isset($_POST['name'])){
      if(!$childsite -> create()){
	$this -> error($childsite -> getError());
      }
      if($childsite -> add()){
      	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询地区数据
    $area = M('area');
    $result_area = $area -> field('id,name') -> select();
    $this -> assign('result_area', $result_area);
    //查询模板
    $childsitetemplate = M('ChildSiteTemplate');
    $result_template = $childsitetemplate -> field('id,name') -> select();
    $this -> assign('result_template', $result_template);
    //查询省级分站
    $result_site = $childsite -> field('id,name') -> where('pid = 0') -> select();
    $this -> assign('result_site', $result_site);
    $this -> display();
  }

  //删除分站
  public function delchildsite(){
    $childsite = M('ChildSite');
    if($childsite -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑分站
  public function editchildsite(){    
    $childsite = D('ChildSite');
    if(isset($_POST['name'])){
      if(!$childsite -> create()){
	$this -> error($childsite -> getError());
      }
      if($childsite -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //查询分站信息
    $result = $childsite -> field('id,aid,tid,pid,name,domain,code,isshow') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //查询地区数据
    $area = M('area');
    $result_area = $area -> field('id,name') -> select();
    $this -> assign('result_area', $result_area);
    //查询模板
    $childsitetemplate = M('ChildSiteTemplate');
    $result_template = $childsitetemplate -> field('id,name') -> select();
    $this -> assign('result_template', $result_template);
    //查询省级分站
    $result_site = $childsite -> field('id,name') -> where('pid = 0') -> select();
    $this -> assign('result_site', $result_site);
    $this -> display();
  }

  //辖区管理
  public function childsitearea(){
    $childsitearea = M('ChildSiteArea');
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);

    $where = array();//连查用条件
    $where_page = array(); //分页用条件
    //构建查询条件
    if(!empty($_POST['name'])){
      $where['csa.name'] = array('LIKE', '%' . $this -> _post('name')  . '%');
      $where_page['name'] = array('LIKE', '%' . $this -> _post('name')  . '%');
    }
    if(!empty($_POST['csid'])){
      $where['csa.csid'] = $this -> _post('csid', 'intval');
      $where_page['csid'] = $this -> _post('csid', 'intval');
    }

    //记录总数
    $count = $childsitearea -> where($where_page) -> count('id');
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

    //查询辖区数据
    $result = $childsitearea -> table('yesow_child_site_area as csa') -> field('csa.id,csa.name,cs.name as csname,csa.code,csa.create_time') -> where($where) -> join('yesow_child_site as cs ON cs.id = csa.csid') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);
    $this -> display();
  }

  //新增辖区
  public function addchildsitearea(){
    //处理新增数据
    if(isset($_POST['name'])){
      $childsitearea = D('ChildSiteArea');
      if(!$childsitearea -> create()){
	$this -> error($childsitearea -> getError());
      }
      if($childsitearea -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $this -> display();
  }

  //删除辖区
  public function delchildsitearea(){
    $childsitearea = M('ChildSiteArea');
    if($childsitearea -> delete($this -> _get('id', 'intval'))){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑辖区
  public function editchildsitearea(){
    $childsitearea = D('ChildSiteArea');
    //处理编辑
    if(isset($_POST['name'])){
      if(!$childsitearea -> create()){
	$this -> error($childsitearea -> getError());
      }
      if($childsitearea -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    //查询所有分站
    $childsite = M('ChildSite');
    $result_childsite = $childsite -> field('id,name') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询对应辖区数据
    $result = $childsitearea -> field('id,csid,name,code') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    $this -> display();
  }
}

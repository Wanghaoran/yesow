<?php
class CompanyAction extends CommonAction {

  /* --------------- 速查数据管理 ---------------- */

  //速查主营类别
  public function companycategory(){
    $category = M('CompanyCategory');
    $where['pid'] = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
    //处理搜索
    if(!empty($_POST['name'])){
      $where['name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    //记录总数
    $count = $category -> where($where) -> count('id');
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
    //结果
    $result = $category -> field('id,name,sort,remark') -> where($where) -> order('sort ASC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    $this -> display();
  }

  //添加速查主营类别
  public function addcompanycategory(){
    $category = D('CompanyCategory');
    //处理添加
    if(!empty($_POST['name'])){
      if(!$category -> create()){
	$this -> error($category -> getError());
      }
      if($category -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }
    }
    //查上级分类
    $pname = $category -> getFieldByid($this -> _get('id', 'intval'), 'name');
    $this -> assign('pname', $pname);
    $this -> display();
  }

  //删除速查主营类别
  public function delcompanycategory(){
    $where_del = array();
    $where_del['id'] = array('in', $_POST['ids']);
    $category = D('CompanyCategory');
    if($category -> where($where_del) -> delete()){
      $this -> success(L('DATA_DELETE_SUCCESS'));
    }else{
      $this -> error(L('DATA_DELETE_ERROR'));
    }
  }

  //编辑速查主营类别
  public function editcompanycategory(){
    $category = D('CompanyCategory');
    if(!empty($_POST['name'])){
      if(!$category -> create()){
	$this -> error($category -> getError());
      }
      if($category -> save()){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $result = $category -> field('name,pid,sort,remark') -> find($this -> _get('id', 'intval'));
    $pname = $category -> getFieldByid($result['pid'], 'name');
    $this -> assign('pname', $pname);
    $this -> assign('result', $result);
    $this -> display();
  }

  //速查未审数据
  public function notcheckcompany(){
    $companyaudit = M('CompanyAudit');
    $where = array();
    $where['type'] = '添加';
    //处理搜索


    //记录总数
    $count = $companyaudit -> where($where) -> count('id');
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
    $result = $companyaudit -> field('id,name,ischeck') -> where($where) -> order('ischeck ASC, jointime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    //每页条数
    $this -> assign('listRows', $listRows);
    //当前页数
    $this -> assign('currentPage', $pageNum);
    $this -> assign('count', $count);

    //默认右侧显示第一个公司的信息
    R('Company/editnotcheckcompany', array($result[0]['id']));
  }

  //编辑速查未审数据
  public function editnotcheckcompany($id=''){
    $companyaudit = D('CompanyAudit');
    //处理更新
    if(!empty($_POST['name'])){
      $data['id'] = $_POST['id'];
      $data['ischeck'] = 1;
      if($companyaudit -> save($data)){
	$this -> success(L('DATA_UPDATE_SUCCESS'));
      }else{
        $this -> error(L('DATA_UPDATE_ERROR'));
      }
    }
    $id = !empty($id) ? $id : $this -> _get('id', 'intval');
    //结果
    $result_edit = $companyaudit -> field('name,address,manproducts,companyphone,mobilephone,linkman,email,qqcode,csid,csaid,typeid,ccid,website,keyword,content,addname,addtel,addunit,addaddress,addemail,addnumberid,jointime') -> find($id);
    $this -> assign('result_edit', $result_edit);
    //根据csid 查分站下地区列表
    $result_childsite_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result_edit['csid'])) -> select();
    $this -> assign('result_childsite_area', $result_childsite_area);
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询公司类型
    $result_company_type = M('CompanyType') -> field('id,name') -> select();
    $this -> assign('result_company_type', $result_company_type);
    //查询ccid对应的主营一级类别
    $result_ccid_one = M('CompanyCategory') -> getFieldByid($result_edit['ccid'], 'pid');
    $this -> assign('result_ccid_one', $result_ccid_one);
    //查询对应主营类别 - 二级
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result_ccid_one)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    $this -> display();
  }

  /* --------------- 速查数据管理 ---------------- */
}

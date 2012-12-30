<?php
class MemberAction extends CommonAction {
  //登录验证
  public function _initialize(){
    if(!$_SESSION[C('USER_AUTH_KEY')]){
      redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
    }
  }

  //会员中心首页
  public function index(){
  
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
      //图片没处理
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
    $result_member = M('Member') -> field('tel,qqcode,e-mail,address,unit') -> find($this -> _session(C('USER_AUTH_KEY')));
    $this -> assign('result_member', $result_member);
    $this -> display();
  }

  //资讯文章首页
  public function article(){
    $infoarticle = M('InfoArticle');
    $where = array();
    $where['authorid'] = $this -> _session('user_id', 'intval');
    import("ORG.Util.Page");// 导入分页类
    $count = $infoarticle -> where($where) -> count('id');
    $page = new Page($count, 10);
    $show = $page -> show();
    $result = $infoarticle -> table('yesow_info_article as ia') -> field('ia.id,ita.name as tname,ia.title,ica.name as cname,ia.hits,ia.addtime,ia.checktime,ia.status') -> where($where) -> join('yesow_info_title_attribute as ita ON ia.tid = ita.id') -> join('yesow_info_content_attribute as ica ON ia.conid = ica.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);
    $this -> assign('show', $show);
    $this -> display();
  }
}

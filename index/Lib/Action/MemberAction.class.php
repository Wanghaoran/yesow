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
      $info_article = D('InfoArticle');//验证没做
      $_POST['authorid'] = $this -> _session(C('USER_AUTH_KEY'));
      $_POST['addtime'] = time();
      $_POST['auditid'] = 1;//有问题
      //写分站表还没做
       if(!$info_article -> create()){
	$this -> error($info_article -> getError());
      }
      if($info_article -> add()){
	$this -> success(L('DATA_ADD_SUCCESS'), U('member/article'));
      }else{
	$this -> error(L('DATA_ADD_ERROR'));
      }  
    }
    //查所有一级分类
    $result_one_col = M('InfoOneColumn') -> field('id,name') -> order('sort') -> select();
    $this -> assign('result_one_col', $result_one_col);
    //查所有分站
    $result_site = M('ChildSite') -> field('id,name') -> select();
    $this -> assign('result_site', $result_site);
    //查此会员的资料
    $result_member = M('Member') -> field('tel,qqcode,e-mail,address,unit') -> find($this -> _session(C('USER_AUTH_KEY')));
    $this -> assign('result_member', $result_member);
    $this -> display();
  }

  //资讯文章首页
  public function article(){
    $this -> display();
  }
}

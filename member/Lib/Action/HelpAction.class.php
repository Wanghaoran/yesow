<?php
class HelpAction extends Action {

  public function _initialize(){
    //根据域名判断分站 及 读取分站模板
    $templatename = D('admin://ChildSite') -> gettemplatename();
    $this -> assign('templatename', $templatename);
    //查询分站名
    $childsite_name = D('admin://ChildSite') -> getname();
    $this -> assign('childsite_name', $childsite_name);
    //在线QQ客服
    if(S('member_qqonline')){
      $this -> assign('member_qqonline', S('member_qqonline'));
    }else{
      $member_qqonline = R('Public/getqqonline');
      $this -> assign('member_qqonline', $member_qqonline);
      S('member_qqonline', $member_qqonline);
    }
  }

  //帮助中心
  public function index(){
    //查询一级分类
    $result_class = M('HelpClass') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_class', $result_class);

    //获取cid，默认第一个
    $cid = isset($_GET['cid']) ? $_GET['cid'] : $result_class[0]['id'];
    $this -> assign('cid', $cid);
    //获取一级标题

    $cid_name = M('HelpClass') -> getFieldByid($cid, 'name');
    $this -> assign('cid_name', $cid_name);

    $where = array();
    $where['cid'] = $cid;
    if(!empty($_GET['aid'])){
      $where['id'] = $this -> _get('aid', 'intval');
    }

    //获取左侧文章标题
    $result_title = M('HelpArticle') -> field('id,title') -> order('sort ASC') -> where(array('cid' => $cid)) -> select();
    $this -> assign('result_title', $result_title);

    //获取文章列表
    $result_article = M('HelpArticle') -> field('id,title,content') -> order('sort ASC') -> where($where) -> select();
    $this -> assign('result_article', $result_article);
    $this -> display();
  }
}

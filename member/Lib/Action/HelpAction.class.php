<?php
class HelpAction extends Action {
  //帮助中心
  public function index(){
    //查询一级分类
    $result_class = M('HelpClass') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_class', $result_class);
    //获取cid，默认第一个
    $cid = isset($_GET['cid']) ? $_GET['cid'] : $result_class[0]['id'];
    $this -> assign('cid', $cid);
    //查询分类下的文章列表
    $result_arcitle = M('HelpArticle') -> field('id,title') -> order('sort ASC') -> where(array('cid' => $cid)) -> select();
    $this -> assign('result_arcitle', $result_arcitle);
    //获取aid，默认第一个
    $aid = isset($_GET['aid']) ? $_GET['aid'] : $result_arcitle[0]['id'];
    $this -> assign('aid', $aid);
    //获取文章内容
    $result_content = M('HelpArticle') -> field('title,content') -> find($aid);
    $this -> assign('result_content', $result_content);
    $this -> display();
  }
}

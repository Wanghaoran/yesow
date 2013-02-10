<?php
class CommonAction extends Action {
  //前置方法，检测变量
  public function _initialize(){
    //资讯导航
    if(S('index_article_nav')){
      $this -> assign('index_article_nav', S('index_article_nav'));
    }else{
      $index_article_nav = $this -> getonearticle();
      $this -> assign('index_article_nav', $index_article_nav);
      S('index_article_nav', $index_article_nav);   
    }
    //热搜关键词
    if(S('index_search_hot')){
      $this -> assign('index_search_hot', S('index_search_hot'));
    }else{
      $index_search_hot = $this -> getsearchhot();
      $this -> assign('index_search_hot', $index_search_hot);
      S('index_search_hot', $index_search_hot);
    }
  }

  //获得一级资讯分类
  private function getonearticle(){
    $articleonecolumn = M('InfoOneColumn');
    return $articleonecolumn -> field('id,name') -> where(array('isnav' => 1)) -> order('sort ASC') -> select();
  }

  //获取热搜关键词
  private function getsearchhot(){
    $searchhot = M('SearchHot');
    return $searchhot -> field('name') -> order('sort ASC') -> select();
  }
}

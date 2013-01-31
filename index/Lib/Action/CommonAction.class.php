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
  }

  //获得一级资讯分类
  private function getonearticle(){
    $articleonecolumn = M('InfoOneColumn');
    return $articleonecolumn -> field('id,name') -> where(array('isnav' => 1)) -> order('sort ASC') -> select();
  }
}

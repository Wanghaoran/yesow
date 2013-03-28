<?php
class CommonAction extends Action {
  //前置方法，检测变量
  public function _initialize(){
    //资讯导航
    if(S('index_article_nav')){
      $this -> assign('index_article_nav', S('index_article_nav'));
    }else{
      $index_article_nav = R('Public/getonearticle');
      $this -> assign('index_article_nav', $index_article_nav);
      S('index_article_nav', $index_article_nav);   
    }
    //热搜关键词
    if(S('index_search_hot')){
      $this -> assign('index_search_hot', S('index_search_hot'));
    }else{
      $index_search_hot =R('Public/getsearchhot');
      $this -> assign('index_search_hot', $index_search_hot);
      S('index_search_hot', $index_search_hot);
    }
    //底部关于我们
    if(S('index_footer_nav')){
      $this -> assign('index_footer_nav', S('index_footer_nav'));
    }else{
      $index_footer_nav = R('Public/getfooternav');
      $this -> assign('index_footer_nav', $index_footer_nav);
      S('index_footer_nav', $index_footer_nav);
    }
    //代理加盟
    if(S('index_agent_join')){
      $this -> assign('index_agent_join', S('index_agent_join'));
    }else{
      $index_agent_join = R('Public/getagentjoin');
      $this -> assign('index_agent_join', $index_agent_join);
      S('index_agent_join', $index_agent_join);
    }
    //分站信息
    if(S('header_child_site')){
      $this -> assign('header_child_site', S('header_child_site'));
    }else{
      $header_child_site = R('Public/getchildsite');
      $this -> assign('header_child_site', $header_child_site);
      S('header_child_site', $header_child_site);
    }
    //QQ客服
    if(S('index_qqonline')){
      $this -> assign('index_qqonline', S('index_qqonline'));
    }else{
      $index_qqonline = R('Public/getqqonline');
      $this -> assign('index_qqonline', $index_qqonline);
      S('index_qqonline', $index_qqonline);
    }
    //底部栏目
    if(S('index_bottomhelp')){
      $this -> assign('index_bottomhelp', S('index_bottomhelp'));
    }else{
      $index_bottomhelp = R('Public/getbottomhelp');
      $this -> assign('index_bottomhelp', $index_bottomhelp);
      S('index_bottomhelp', $index_bottomhelp);
    }
  }
}

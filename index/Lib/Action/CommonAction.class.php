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
    //底部关于我们
    if(S('index_footer_nav')){
      $this -> assign('index_footer_nav', S('index_footer_nav'));
    }else{
      $index_footer_nav = $this -> getfooternav();
      $this -> assign('index_footer_nav', $index_footer_nav);
      S('index_footer_nav', $index_footer_nav);
    }
    //代理加盟
    if(S('index_agent_join')){
      $this -> assign('index_agent_join', S('index_agent_join'));
    }else{
      $index_agent_join = $this -> getagentjoin();
      $this -> assign('index_agent_join', $index_agent_join);
      S('index_agent_join', $index_agent_join);
    }
    //分站信息
    if(S('header_child_site')){
      $this -> assign('header_child_site', S('header_child_site'));
    }else{
      $header_child_site = $this -> getchildsite();
      $this -> assign('header_child_site', $header_child_site);
      S('header_child_site', $header_child_site);
    }
    //QQ客服
    if(S('index_qqonline')){
      $this -> assign('index_qqonline', S('index_qqonline'));
    }else{
      $index_qqonline = $this -> getqqonline();
      $this -> assign('index_qqonline', $index_qqonline);
      S('index_qqonline', $index_qqonline);
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

  //获得底部关于我们
  private function getfooternav(){
    $aboutus =  M('Aboutus');
    return $aboutus -> field('id,title') -> order('sort ASC') -> select();
  }

  //获得代理加盟二级分类
  private function getagentjoin(){
    return M('AgentJoin') -> field('id,title') -> order('sort ASC') -> select();
  }

  //获得分站信息
  private function getchildsite(){
    $result = M('Area') -> field('id,name') -> where(array('name' => array('neq', '主站'), 'isshow' => '1')) -> select();
    $childsite = M('ChildSite');
    foreach($result as $key => $value){
      $result[$key]['childsite'] = $childsite -> field('domain,name') -> where(array('aid' => $value['id'], 'isshow' => 1)) -> select();
    }
    return $result;
  }

  //获得QQ客服
  private function getqqonline(){
    $qqonline = M('Qqonline');
    $result = M('QqonlineType') -> field('id,name') -> select();
    foreach($result as $key => $value){
      $result[$key]['qq'] = $qqonline -> field('qqcode,nickname') -> where(array('tid' => $value['id'], 'csid' => 18)) -> select();
    }
    return $result;
  }
}

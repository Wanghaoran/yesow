<?php
class IndexAction extends CommonAction {

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
  //首页
  public function index(){
    $this -> display();
  }

  //获得一级资讯分类
  private function getonearticle(){
    $articleonecolumn = M('InfoOneColumn');
    return $articleonecolumn -> field('id,name') -> where(array('isnav' => 1)) -> order('sort ASC') -> select();
  }

  //资讯首页
  public function info(){
    dump('资讯首页');
  }

  //资讯一级页
  public function infolist(){
    dump('资讯一级栏目页');
    dump($_GET);
  }

  //资讯二级详情页
  public function infodetail(){
    dump('资讯二级栏目页');
  }

  //查看文章
  public function article(){
    $infoarticle = M('InfoArticle');
    $result = $infoarticle -> table('yesow_info_article as ia') -> field('ia.title,ia.source,m.name as mname,a.name as aname,ia.addtime,content') -> join('yesow_member as m ON ia.authorid = m.id') -> join('yesow_admin as a ON ia.auditid = a.id') -> where(array('ia.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    //点击量加一
    $infoarticle -> where(array('id' => $this -> _get('id', 'intval'))) -> setInc('hits');
    //读取最近更新
    $recent_updates = $infoarticle -> table('yesow_info_article as ia') -> field('ia.id,ia.title,itc.name as cname,ia.addtime') -> join('yesow_info_two_column as itc ON ia.colid = itc.id') -> order('addtime DESC') -> limit(10) -> select();
    $this -> assign('recent_updates', $recent_updates);
    //读取热门点击
    $hot_clicks = $infoarticle -> table('yesow_info_article as ia') -> field('ia.id,ia.title,itc.name as cname,ia.addtime') -> join('yesow_info_two_column as itc ON ia.colid = itc.id') -> order('hits DESC') -> limit(10) -> select();
    $this -> assign('hot_clicks', $hot_clicks);
    $this -> display();
  }
}

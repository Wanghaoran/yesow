<?php
class IndexAction extends CommonAction {
  public function index(){
    $this -> display();
  }

  //查看文章
  public function article(){
    $infoarticle = M('InfoArticle');
    $result = $infoarticle -> table('yesow_info_article as ia') -> field('ia.title,ia.source,m.name as mname,ia.addtime,content') -> join('yesow_member as m ON ia.authorid = m.id') -> where(array('ia.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }
}

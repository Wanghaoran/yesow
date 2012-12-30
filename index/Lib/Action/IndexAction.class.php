<?php
class IndexAction extends CommonAction {
  public function index(){
    $this -> display();
  }

  //查看文章
  public function article(){
    $infoarticle = M('InfoArticle');
    $result = $infoarticle -> table('yesow_info_article as ia') -> field('ia.title,ia.source,m.name as mname,a.name as aname,ia.addtime,content') -> join('yesow_member as m ON ia.authorid = m.id') -> join('yesow_admin as a ON ia.auditid = a.id') -> where(array('ia.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);

    //点击量加一
    $infoarticle -> where(array('id' => $this -> _get('id', 'intval'))) -> setInc('hits');
    $this -> display();
  }
}

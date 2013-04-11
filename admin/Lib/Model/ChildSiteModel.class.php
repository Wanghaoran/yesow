<?php
class ChildSiteModel extends Model {
  protected $_validate = array(
    array('name', 'require', '{%NAME_EMPTY_ERROR}'),
    array('name', '', '{%NAME_UNIQUE_ERROR}', 0, 'unique'),
    array('domain', 'require', '{%DOMAIN_EMPTY}'),
    array('domain', '', '{%DOMAIN_UNIQUE_ERROR}', 0, 'unique'),
    array('isshow', array(0,1), '{%ISSHOW_ERROR}', 0, 'in'),
  );

  protected $_auto = array(
    array('create_time', 'time' ,1, 'function'),
  );

  //根据域名获取分站名
  public function getname(){
    $childsite_name = $this -> getFieldBydomain($_SERVER['HTTP_HOST'], 'name');
    //如果不存在，则读取主站名称
    if(!$childsite_name){
      $childsite_name = $this -> getFieldBydomain('yesow.com', 'name');
    }
    return $childsite_name;
  }

  //根据域名获取分站id 也就是csid
  public function getid(){
    if($_SERVER['HTTP_HOST'] == 'yesow.com' || $_SERVER['HTTP_HOST'] == 'www.yesow.com'){
      return false;
    }
    $cid = $this -> getFieldBydomain($_SERVER['HTTP_HOST'], 'id');
    if(!$cid){
      return false;
    }
    return $cid;
  }

  //根据域名获取分站id，用于判断广告和在线QQ
  public function getidc(){
    if($_SERVER['HTTP_HOST'] == 'yesow.com' || $_SERVER['HTTP_HOST'] == 'www.yesow.com'){
      $cid = $this -> getFieldBydomain('yesow.com', 'id');
    }else{
      $cid = $this -> getFieldBydomain($_SERVER['HTTP_HOST'], 'id');
    }
    return $cid;
  }

  //根据域名获取模板名称
  public function gettemplatename(){
    if($_SERVER['HTTP_HOST'] == 'yesow.com' || $_SERVER['HTTP_HOST'] == 'www.yesow.com'){
      return 'default';
    }
    $templatename = $this -> table('yesow_child_site as cs') -> field('cst.address') -> join('yesow_child_site_template as cst ON cs.tid = cst.id') -> where(array('cs.domain' => $_SERVER['HTTP_HOST'])) -> find();
    if(!$templatename){
      return 'default';
    }
    return $templatename['address'];
  }
}



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
    $childsite_name = !empty($childsite_name) ? $childsite_name : '中国';
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
}



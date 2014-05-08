<?php
class ChildSitePhoneModel extends Model {

  public function getphone(){
    $cid = D('admin://ChildSite') -> getid();
    if($cid){
      $result = array();
      $temp = $this -> field('tel,telphone,email') -> where(array('cid' => $cid)) -> find();
      $result['固定电话'] = $temp['tel'];
      $result['手机'] = $temp['telphone'];
      $result['邮箱'] = $temp['email'];
      return $result;
    }else{
      return $this -> alias('p') -> field('p.tel,cs.name as csname') -> join('yesow_child_site as cs ON p.cid = cs.id') -> select();
    }
  }

}

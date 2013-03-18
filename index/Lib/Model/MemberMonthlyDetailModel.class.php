<?php
class MemberMonthlyDetailModel extends CommonModel {

  //写日志
  public function writelog($type, $content, $mid){
    
    $mid = !empty($mid) ? $mid : $_SESSION[C('USER_AUTH_KEY')];
    $data = array();
    $data['type'] = $type;
    $data['content'] = $content;
    $data['mid'] = $mid;
    $data['addtime'] = time();
    $this -> add($data);
  }

}

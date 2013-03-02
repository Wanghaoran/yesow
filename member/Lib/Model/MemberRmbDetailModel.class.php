<?php
class MemberRmbDetailModel extends Model {
  //记录RMB消费明细
  public function writelog($mid,$content,$type,$money){
    $data = array();
    $data['mid'] = $mid;
    $data['addtime'] = time();
    $data['content'] = $content;
    $data['type'] = $type;
    $data['money'] = $money;
    if($this -> add($data)){
      return 1;
    }else{
      return 0;
    }
  }
}

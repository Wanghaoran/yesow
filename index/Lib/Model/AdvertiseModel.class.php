<?php
class AdvertiseModel extends Model {
  //根据pid返回广告数组
  public function getadimg($pid){
    //先查出namenote数组
    $namenote_arr = $this -> field('namenote') -> where(array('pid' => $pid)) -> group('namenote') -> select();
    $result = array();
    foreach($namenote_arr as $value){
      $result[$value['namenote']] = $this -> field('address,width,height,link') -> where(array('pid' => $pid, 'namenote' => $value['namenote'], 'isopen' => 1)) -> select();
    }
    return $result;
  }
}

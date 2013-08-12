<?php
class AdvertiseModel extends Model {
  //根据pid返回广告数组
  public function getadimg($pid){
    //先查出namenote数组
    $namenote_arr = $this -> field('namenote') -> where(array('pid' => $pid)) -> group('namenote') -> select();
    $result = array();
    $where_limit = array();
    $where_limit['starttime'] = array('ELT', time());
    $where_limit['endtime'] = array('EGT', time());
    foreach($namenote_arr as $value){
      $temp_ad = $this -> field('id,address,width,height,link') -> where(array('pid' => $pid, 'namenote' => $value['namenote'], 'isopen' => 1)) -> select();
      foreach($temp_ad as $key => $value2){
	$where_limit['adid'] = $value2['id'];
	$temp_re = M('Advert') -> field('filename,website') -> where($where_limit) -> find();	
	if($temp_re && strstr($temp_re['filename'], '.') != '.rar' && strstr($temp_re['filename'], '.') != '.zip'){
	  $temp_ad[$key]['address'] = $temp_re['filename'];
	  $temp_ad[$key]['link'] = $temp_re['website'];
	}
      }
      $result[$value['namenote']] = $temp_ad;
    }
    return $result;
  }
}

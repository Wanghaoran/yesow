<?php
class MonthlyLimitDetailModel extends CommonModel {
  //获取相应类型今日已操作的数量
  public function gettypenum($mod, $type, $mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    if($mod == 1){
      $beginToday = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
      $endToday = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")) - 1;
    }else{
      //包月的开始时间先读取他的生效时间
      $Monthly = M('Monthly');
      $where_monthly = array();
      $where_monthly['endtime'] = array('EGT', time());
      $where_monthly['starttime'] = array('ELT', time());
      $where_monthly['mid'] = session(C('USER_AUTH_KEY'));
      $result_time = $Monthly -> field('starttime,endtime') -> where($where_monthly) -> order('starttime DESC') -> find();

      $month = date('m');
      if($month > date('m', $result_time['starttime'])){
	$month += $month - date('m', $result_time['starttime']);
      }

      $beginToday = mktime(date('H', $result_time['starttime']), date('i', $result_time['starttime']), date('s', $result_time['starttime']), $month, date("d", $result_time['starttime']), date("Y", $result_time['starttime']));
      $endToday = mktime(date('H', $result_time['starttime']), date('i', $result_time['starttime']), date('s', $result_time['starttime']), $month+1, date("d", $result_time['starttime']), date("Y", $result_time['starttime']));
    }
    $where = array();
    $where['addtime'] = array(array('gt', $beginToday), array('lt', $endToday));
    $where['type'] = $type;
    $where['mid'] = $mid;
    $num = $this -> where($where) -> count();
    return $num;
  }

}

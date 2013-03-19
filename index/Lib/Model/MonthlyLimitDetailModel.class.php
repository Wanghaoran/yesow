<?php
class MonthlyLimitDetailModel extends CommonModel {
  //获取相应类型今日已操作的数量
  public function gettypenum($type, $mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    $beginToday = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
    $endToday = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")) - 1;
    $where = array();
    $where['addtime'] = array(array('gt', $beginToday), array('lt', $endToday));
    $where['type'] = $type;
    $where['mid'] = $mid;
    return $this -> where($where) -> count();
  }

}

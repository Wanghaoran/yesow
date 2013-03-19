<?php
class MonthlyModel extends CommonModel {

  //判断是否是包月会员
  public function ismonthly($mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    $where_monthly = array();
    $where_monthly['endtime'] = array('EGT', time());
    $where_monthly['mid'] = $mid;
    $result = $this -> where($where_monthly) -> order('starttime DESC') -> find();
    return $result ? true : false;
  }

  //判断是否是包月会员，且相应操作条数没有超过每日限制
  public function ismonthlylimit($type, $authorname){
    if(!$this -> ismonthly()){
      return false;
    }
    //查询每天查看的数量
    $see_num = M('MemberLevel') -> getFieldByid(session('member_level_id'), $authorname);
    //查询今日已经查看了的数量
    $use_num = D('MonthlyLimitDetail') -> gettypenum($type);
    //剩余条数
    $less_num = $see_num - $use_num;
    if($less_num <= 0){
      return false;
    }
    return $less_num;
  }

  //获取包月会员等级
  public function monthlylevel($mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    $where_monthly = array();
    $where_monthly['m.endtime'] = array('EGT', time());
    $where_monthly['m.mid'] = $mid;
    $result = $this -> table('yesow_monthly as m') -> field('tmp.name,tmp.mid') -> join('LEFT JOIN (SELECT mm.id,ml.name,ml.id as mid FROM yesow_member_monthly as mm LEFT JOIN yesow_member_level as ml ON mm.lid = ml.id) as tmp ON m.monid = tmp.id') -> where($where_monthly) -> order('m.starttime DESC') -> find();
    return $result ? $result : false;
  }

}

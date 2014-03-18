<?php
class MonthlyModel extends CommonModel {
  
  protected $_map = array(
    'org2_id' => 'mid', 
  );

  protected $_auto = array(
    array('starttime','strtotime',3,'function'),
    array('endtime','strtotime',3,'function'),
  );

  //判断是否是包月会员
  public function ismonthly($csid, $mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    $where_monthly = array();
    $where_monthly['endtime'] = array('EGT', time());
    $where_monthly['starttime'] = array('ELT', time());
    $where_monthly['mid'] = $mid;
    $result = $this -> field('id,type') -> where($where_monthly) -> order('starttime DESC') -> find();
    if(!$result) return false;
    if($result['type'] == 2){
      $MonthlyChildsite = M('MonthlyChildsite');
      $monthly_childsite_temp = $MonthlyChildsite -> field('csid') -> where(array('monthlyid' => $result['id'])) -> select();
      $monthly_childsite = array();
      foreach($monthly_childsite_temp as $value){
	$monthly_childsite[] = $value['csid'];
      }
      if(in_array($csid, $monthly_childsite)){
	return true;
      }else{
	return false;
      }
    }else{
      return true;
    }
  }

  //判断是否是包月会员，且相应操作条数没有超过每日限制
  public function ismonthlylimit($type, $authorname, $csid){
    if(!$this -> ismonthly($csid)){
      return false;
    }
    //分别判断 全国包月 和 分省包月
    if($this -> isallmonthly()){
      //再判断包月模式
      if($this -> ismonthlymod()){
	//查询每天查看的数量
	$see_num = M('MemberLevel') -> getFieldByid(session('member_level_id'), $authorname);
	//查询今日已经查看了的数量
	$use_num = D('index://MonthlyLimitDetail') -> gettypenum(1, $type);
      }else{
	if($authorname == 'monthly_one_num'){
	  $authorname = 'monthly_four_num';
	}
	if($authorname == 'monthly_two_num'){
	  $authorname = 'monthly_five_num';
	}
	if($authorname == 'monthly_three_num'){
	  $authorname = 'monthly_six_num';
	}
	//查询每月查看的数量
	$see_num = M('MemberLevel') -> getFieldByid(session('member_level_id'), $authorname);
	//查询本月已经查看了的数量
	$use_num = D('index://MonthlyLimitDetail') -> gettypenum(2, $type);
      } 
    }else{
      //再判断包月模式
      if($this -> ismonthlymod()){
	//查询每天查看的数量
	$see_num = M('MemberLevel') -> getFieldByid(session('member_level_id'), $authorname . '_area');
	//查询今日已经查看了的数量
	$use_num = D('index://MonthlyLimitDetail') -> gettypenum(1, $type);
      }else{
	if($authorname == 'monthly_one_num'){
	  $authorname = 'monthly_four_num';
	}
	if($authorname == 'monthly_two_num'){
	  $authorname = 'monthly_five_num';
	}
	if($authorname == 'monthly_three_num'){
	  $authorname = 'monthly_six_num';
	}
	//查询每月查看的数量
	$see_num = M('MemberLevel') -> getFieldByid(session('member_level_id'), $authorname . '_area');
	//查询本月已经查看了的数量
	$use_num = D('index://MonthlyLimitDetail') -> gettypenum(2, $type);
      }
      
    }
    
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
    $result = $this -> table('yesow_monthly as m') -> field('tmp.name,tmp.mid,m.type,m.mod') -> join('LEFT JOIN (SELECT mm.id,ml.name,ml.id as mid FROM yesow_member_monthly as mm LEFT JOIN yesow_member_level as ml ON mm.lid = ml.id) as tmp ON m.monid = tmp.id') -> where($where_monthly) -> order('m.starttime DESC') -> find();
    return $result ? $result : false;
  }

  //判断是否为全国包月会员
  public function isallmonthly($mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    $where_monthly = array();
    $where_monthly['endtime'] = array('EGT', time());
    $where_monthly['starttime'] = array('ELT', time());
    $where_monthly['mid'] = $mid;
    $result = $this -> field('id,type') -> where($where_monthly) -> order('starttime DESC') -> find();
    if($result['type'] == 1){
      return true;
    }else{
      return false;
    }
  }

  //判断是日流量包，还是月流量包
  //true 为 日流量包
  //false 为 月流量包
  public function ismonthlymod($mid){
    $mid = !empty($mid) ? $mid : session(C('USER_AUTH_KEY'));
    $where_monthly = array();
    $where_monthly['endtime'] = array('EGT', time());
    $where_monthly['starttime'] = array('ELT', time());
    $where_monthly['mid'] = $mid;
    $result = $this -> field('id,mod') -> where($where_monthly) -> order('starttime DESC') -> find();
    if($result['type'] == 1){
      return true;
    }else{
      return false;
    }
  }

}

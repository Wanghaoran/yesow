<?php
class MemberLevelModel extends Model {
  //获取并缓存会员等级
  public function level($rmb){
    $rmb = $rmb < 0 ? 0 : $rmb;
    $member_level = $this -> field('id,name') -> where(array('updatemoney' => array('ELT', $rmb))) -> order('updatemoney DESC') -> find();
    session('member_level_id', $member_level['id']);
    session('member_level_name', $member_level['name']);
    //如果存在包月会员，则使用包月会员的等级
    if($level_monthly = D('index://Monthly') -> monthlylevel()){
      session('member_level_id', $level_monthly['mid']);
      session('member_level_name', '包月' . $level_monthly['name']);
    }
  }

}

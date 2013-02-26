<?php
class MemberLevelModel extends Model {
  //获取并缓存会员等级
  public function level($rmb){
    $member_level = $this -> field('id,name') -> where(array('updatemoney' => array('ELT', $rmb))) -> order('updatemoney DESC') -> find();
    session('member_level_id', $member_level['id']);
    session('member_level_name', $member_level['name']);
  }

}

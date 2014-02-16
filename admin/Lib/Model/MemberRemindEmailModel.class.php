<?php
class MemberRemindEmailModel extends Model {

  //切换发送邮箱
  public function cutemail(){
    $last = $this -> field('id,sort') -> where('status=1') -> find();
    $next = $this -> field('id') -> where(array('sort' => array('GT', $last['sort']))) -> order('sort ASC') -> find();
    if($next){
      $this -> where(array('id' => $next['id'])) -> save(array('status' => 1, 'activate_time' => time()));
      $this -> where(array('id' => array('neq', $next['id']))) -> save(array('status' => 0));
    }else{
      $frist = $this -> field('id') -> order('sort ASC') -> find();
      $this -> where(array('id' => $frist['id'])) -> save(array('status' => 1, 'activate_time' => time()));
      $this -> where(array('id' => array('neq', $frist['id']))) -> save(array('status' => 0));
    }
  }
}

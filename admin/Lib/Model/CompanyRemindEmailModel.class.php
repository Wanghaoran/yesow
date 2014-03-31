<?php
class CompanyRemindEmailModel extends Model {

  //切换发送邮箱
  public function cutemail(){
    $last = $this -> field('id,sort') -> where('status=1 AND type=1') -> find();
    $next = $this -> field('id') -> where(array('sort' => array('GT', $last['sort']), 'type' => 1, 'isallow' => 1)) -> order('sort ASC') -> find();
    if($next){
      $this -> where(array('id' => $next['id'])) -> save(array('status' => 1, 'activate_time' => time(), 'typr' => 1));
      $this -> where(array('id' => array('neq', $next['id']), 'type' => 1)) -> save(array('status' => 0));
    }else{
      $frist = $this -> where('type=1 AND isallow=1') -> field('id') -> order('sort ASC') -> find();
      $this -> where(array('id' => $frist['id'], 'type' => 1)) -> save(array('status' => 1, 'activate_time' => time()));
      $this -> where(array('id' => array('neq', $frist['id']), 'type' => 1)) -> save(array('status' => 0));
    }


    $last = $this -> field('id,sort') -> where('status=1 AND type=2') -> find();
    $next = $this -> field('id') -> where(array('sort' => array('GT', $last['sort']), 'type' => 2, 'isallow' => 1)) -> order('sort ASC') -> find();
    if($next){
      $this -> where(array('id' => $next['id'])) -> save(array('status' => 1, 'activate_time' => time(), 'type' => 2));
      $this -> where(array('id' => array('neq', $next['id']), 'type' => 2)) -> save(array('status' => 0));
    }else{
      $frist = $this -> where('type=2 AND isallow=1') -> field('id') -> order('sort ASC') -> find();
      $this -> where(array('id' => $frist['id'], 'type' => 2)) -> save(array('status' => 1, 'activate_time' => time()));
      $this -> where(array('id' => array('neq', $frist['id']), 'type' => 2)) -> save(array('status' => 0));
    }
  }
}


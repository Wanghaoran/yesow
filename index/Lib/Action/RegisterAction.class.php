<?php
class RegisterAction extends Action {
  //注册第一步
  public function one(){
    $this -> display();
  }

  //注册第二步
  public function two(){
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询学历
    $result_memberedu = M('MemberEdu') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_memberedu', $result_memberedu);
    //查询职业
    $result_membercareer = M('MemberCareer') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_membercareer', $result_membercareer);
    //查询收入
    $result_memberincome = M('MemberIncome') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_memberincome', $result_memberincome);
    $this -> display();
  }

  //注册第三步
  public function three(){
  
  }

  //检测用户名是否重复
  public function checkusername(){
    $result = M('Member') -> getFieldByname($this -> _post('name'), 'id');
    if($result){
      echo 0;
    }else{
      echo 1;
    }
  }

  //检测邮件地址是否重复
  public function checkemail(){
    $result = M('Member') -> getFieldByemail($this -> _post('email'), 'id');
    if($result){
      echo 0;
    }else{
      echo 1;
    }
  }

  //处理注册
  public function reg(){
    dump($_POST);
  }
}

<?php
class PublicAction extends Action {
  //登录
  public function login(){
    $this -> display();
  }

  //验证登录
  public function checklogin(){
    $member = M('Member');
    $where = array();
    $where['name'] = $this -> _post('name');
    $where['password'] = $this -> _post('password', 'md5');
    if($result = $member -> field('id,name') -> where($where) -> find()){
      session(C('USER_AUTH_KEY'), $result['id']);
      session('username', $result['name']);
      $this -> success(L('LOGIN_SUCCESS'), U('Member/article'));
    }else{
      $this -> error(L('PASSWORD_ERROR'));
    }
  }

  //退出登录
  public function logout(){
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      session(C('USER_AUTH_KEY'), null);
      session(null);
      session('[destroy]');
      $this -> success(L('LOGOUT_SUCCESS'), U(C('USER_AUTH_GATEWAY')));
    }else{
      $this -> error(L('LOGOUT_ERROR'));
    }
  }
}

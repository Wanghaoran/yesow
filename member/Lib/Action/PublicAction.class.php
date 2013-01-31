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
    $where['status'] = 1;
    if($result = $member -> field('id,password,nickname,name,ischeck,last_login_ip,last_login_time,headico,login_count') -> where($where) -> find()){
      if($result['password'] != $this -> _post('password', 'md5')){
	R('Register/errorjump',array(L('PASSWORD_ERROR')));
      }
      if($result['ischeck'] == 0){
	R('Register/errorjump', array(L('MAIL_CHECK_ERROR'), U('Register/three')));
      }
      session(C('USER_AUTH_KEY'), $result['id']);
      session('username', $result['nickname']);
      session('last_login_ip', $result['last_login_ip']);
      session('last_login_time', $result['last_login_time']);
      session('headico', $result['headico']);
      session('login_count', $result['login_count']);
      //更新登录信息
      $data['id'] = $result['id'];
      $data['last_login_ip'] = get_client_ip();
      $data['last_login_time'] = time();
      $data['lastest_login_time'] = $result['last_login_time'];
      $data['login_count'] = array('exp', 'login_count+1');
      $member -> save($data);
      R('Register/successjump',array(L('LOGIN_SUCCESS'), U('Index/index')));
    }else{
      R('Register/errorjump',array(L('NAME_ERROR')));
    }
  }

  //退出登录
  public function logout(){
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      session(C('USER_AUTH_KEY'), null);
      session(null);
      session('[destroy]');
      R('Register/successjump',array(L('LOGOUT_SUCCESS')));
    }else{
      R('Register/errorjump',array(L('LOGOUT_ERROR')));
    }
  }
}


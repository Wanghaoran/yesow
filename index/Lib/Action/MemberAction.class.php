<?php
class MemberAction extends MemberCommonAction {

  //会员中心首页
  public function index(){
    $this -> display();
  }

  //编辑个人信息
  public function edituserinfo(){
    $member = D('admin://Member');
    if(!empty($_POST['nickname'])){
      //如果更新了email,则需要重新验证
      $oldemail = $member -> getFieldByid(session(C('USER_AUTH_KEY')), 'email');
      if($oldemail != $_POST['email']){
	$_POST['ischeck'] = 0;
      }
      $_POST['id'] = session(C('USER_AUTH_KEY'));
      if(!$member -> create()){
	R('Register/errorjump',array($member -> getError()));
      }
      if($member -> save()){
	//更新成功重新生成缓存信息
	$cachedata = $member -> field('nickname,headico') -> find($_POST['id']);
	session('username', $cachedata['nickname']);
	session('headico', $cachedata['headico']);
	if(isset($_POST['ischeck'])){
	  R('Register/successjump',array(L('UPDETA_USER_DATA_CHENGE_EMAIL'), U('Register/three')));
	}else{
	  R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Member/index')));
	}
      }else{
	R('Register/errorjump',array(L('DATA_UPDATE_ERROR')));
      }
    }
    //个人信息
    $result = $member -> field('id,headico,csid,csaid,eduid,careerid,incomeid,name,nickname,passwordquestion,passwordanswer,email,fullname,idnumber,sex,tel,qqcode,msn,address,zipcode,unit,homepage') -> find(session(C('USER_AUTH_KEY')));
    $this -> assign('result', $result);
    //分站信息
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //分站下地区信息
    $result_childsitearea = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> order('id DESC') -> select();
    $this -> assign('result_childsitearea', $result_childsitearea);
    //教育信息
    $result_memberedu = M('MemberEdu') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_memberedu', $result_memberedu);
    //职业信息
    $result_membercareer = M('MemberCareer') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_membercareer', $result_membercareer);
    //收入信息
    $result_memberincome = M('MemberIncome') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_memberincome', $result_memberincome);
    $this -> display();
  }

  //检测邮件地址是否重复
  public function checkemail(){
    $id = M('Member') -> getFieldByemail($this -> _post('email'), 'id');
    $email = M('Member') -> getFieldByid(session(C('USER_AUTH_KEY')), 'email');
    if($email == $_POST['email']){
      echo 1;
      exit();
    }else if($id){
      echo 0;
      exit();
    }else{
      echo 1;
    }
  }

  //用户升级管理
  public function userupdeta(){
    $this -> display();
  }

  //安全设置首页
  public function safe(){
    $this -> display();
  }

  //安全设置 - 密码设置详情页
  public function setsafepwd(){
    $member = D('admin://Member');
    //处理登录密码更新
    if(!empty($_POST['memtishi'])){
      //处理密码字段
      if(!empty($_POST['password'])){
	$data['password'] = md5($_POST['password']);
      }
      $data['id'] = session(C('USER_AUTH_KEY'));
      $data['passwordquestion'] = $_POST['memtishi'];
      $data['passwordanswer'] = $_POST['memhueda'];
      if(!$member -> create($data)){
	R('Register/errorjump',array($member -> getError()));
      }
      if($member -> save()){
	R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Member/safe')));
      }else{
	R('Register/errorjump',array(L('DATA_UPDATE_ERROR')));
      }
    }
    //处理交易密码更新
    if($_POST['mod'] == 'trader'){
      $pwd = $member -> getFieldByid(session(C('USER_AUTH_KEY')), 'password');
      if($pwd != $this -> _post('password3', 'md5')){
	R('Register/errorjump',array(L('PASSWORD_ERROR')));
      }
      $data = array();
      $data['id'] = session(C('USER_AUTH_KEY'));
      $data['traderspassword'] = $this -> _post('password4', 'md5');
      if($member -> save($data)){
	R('Register/successjump',array(L('DATA_UPDATE_SUCCESS'), U('Member/safe')));
      }else{
	R('Register/errorjump',array(L('DATA_UPDATE_ERROR')));
      }
    }
    $result = $member -> field('passwordquestion,passwordanswer') -> find(session(C('USER_AUTH_KEY')));
    $this -> assign('result', $result);
    $this -> display();
  }

  //安全设置 - 资金设置详情页
  public function setsafemonery(){
    $this -> display();
  }

  
}

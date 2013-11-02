<?php
class QuestionAction extends CommonAction {
  public function index(){
    $this -> display();
  }

  public function add(){
    if(!$_SESSION[C('USER_AUTH_KEY')]){
      redirect(__ROOT__ . '/member.php/public/login');
    }
    if(!empty($_POST['title'])){
      if($this -> _post('verify', 'md5') != $_SESSION['verify']){
	echo '<script>alert("验证码错误");history.go(-1);</script>';
	exit();
      }
      $Question = M('Question');
      if(!$Question -> create()){
	R('Public/errorjump',array($Question -> getError()));
      }
      $Question -> addtime = time();
      $Question -> mid = session(C('USER_AUTH_KEY'));
      if($Question -> add()){
	R('Public/successjump',array(L('DATA_ADD_SUCCESS')));
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }

    }
    $QuestionCategory = M('QuestionCategory');
    $result_category = $QuestionCategory -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    $this -> assign('result_category', $result_category);
    $this -> display();
  }

  public function lists(){
  
  }

  public function info(){
  
  }
}

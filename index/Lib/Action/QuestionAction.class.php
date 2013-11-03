<?php
class QuestionAction extends CommonAction {
  public function index(){
    $QuestionCategory = M('QuestionCategory');
    $Question = M('Question');
    $MemberRmb = M('MemberRmb');
    $result_category = $QuestionCategory -> field('id,name') -> where('pid=0') -> order('sort ASC') -> select();
    foreach($result_category as $key => $value){
      $result_category[$key]['list'] = $Question -> alias('q') -> field('q.id,q.title,m.name as mname,q.addtime') -> join('yesow_member as m ON q.mid = m.id') -> where(array('q.tid_one' => $value['id'])) -> order('q.addtime DESC') -> limit(12) -> select();
    }
    $this -> assign('result_category', $result_category);
    $result_rmb = $MemberRmb -> alias('r') -> field('r.rmb_pay+rmb_exchange as total,m.name as mname') -> join('yesow_member as m ON r.mid = m.id') -> limit('20') -> order('total DESC') -> select();
    $this -> assign('result_rmb', $result_rmb);
    
    $this -> display();
  }

  public function add(){
    if(!$_SESSION[C('USER_AUTH_KEY')]){
      redirect(__ROOT__ . '/member.php/public/login?jump_url=' . __SELF__);
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
    $Question = M('Question');
    $where = array();
    if(!empty($_GET['id'])){
      $where['q.tid_one'] = $this -> _get('id', 'intval');
    }
    import("ORG.Util.Page");
    $count = $Question -> alias('q') -> where($where) -> count();
    $page = new Page($count, 20);
    $page->setConfig('header','条问题');
    $show = $page -> show();
    $result = $Question -> alias('q') -> field('q.id,q.title,m.name as mname,q.click_count,q.addtime,tmp.count') -> join('yesow_member as m ON q.mid = m.id') -> join('LEFT JOIN(SELECT qid,COUNT(qid) as count FROM yesow_question_comments GROUP BY qid) as tmp ON tmp.qid = q.id') -> where($where) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('addtime DESC') -> select();
    $this -> assign('show', $show);
    $this -> assign('result', $result);
    $this -> display();
  }

  public function info(){
    if(!empty($_POST['content'])){
      if($this -> _post('verify', 'md5') != $_SESSION['verify']){
	echo '<script>alert("验证码错误");history.go(-1);</script>';
	exit();
      }
      $QuestionComments = D('QuestionComments');
      $data = array();
      $data['qid'] = $this -> _post('qid', 'intval');
      $data['mid'] = session(C('USER_AUTH_KEY'));
      $data['content'] = $this -> _post('content');
      if(!$QuestionComments -> create($data)){
	R('Public/errorjump',array($QuestionComments -> getError()));
      }
      if($QuestionComments -> add()){
	R('Public/successjump',array(L('DATA_ADD_SUCCESS')));
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    $Question = M('Question');
    $result = $Question -> alias('q') -> field('q.id,q.title,q.content,q.addtime,m.name as mname,m.headico') -> join('yesow_member as m ON q.mid = m.id') -> where(array('q.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $QuestionComments = M('QuestionComments');
    import("ORG.Util.Page");
    $count = $QuestionComments -> alias('c') -> where(array('c.qid' => $this -> _get('id', 'intval'))) -> count();
    $page = new Page($count, 10);
    $page->setConfig('header','条回复');
    $show = $page -> show();
    $result_comments = $QuestionComments -> alias('c') -> field('m.name as mname,m.headico,c.content,c.addtime,c.floor') -> join('yesow_member as m ON c.mid = m.id') -> where(array('c.qid' => $this -> _get('id', 'intval'))) -> limit($page -> firstRow . ',' . $page -> listRows) -> order('c.floor ASC') -> select();
    $this -> assign('show', $show);
    $this -> assign('result_comments', $result_comments);
    $Question -> where(array('id' => $this -> _get('id', 'intval'))) -> setInc('click_count');
    $this -> display();
  }
}

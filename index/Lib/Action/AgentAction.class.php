<?php
//代理加盟
class AgentAction extends CommonAction {
  
  //index 前置方法
  public function _before_index(){
    $where = array();
    //判断是否是分站
    if($csid = D('admin://ChildSite') -> getid()){
      $where['csid'] = $csid;
    }
    $where['delaid'] = array('exp', 'is NULL');
    $new_company = M('Company') -> field('id,name') -> where($where) -> order('updatetime DESC') -> limit(20) -> select();
    $this -> assign('new_company', $new_company);
  }

  public function index(){
    $agent = M('AgentJoin');
    $id = !empty($_GET['id']) ? $this -> _get('id', 'intval') : '';
    //不传id则默认获取第一条
    if(empty($id)){
      $info = $agent -> field('id,title,content') -> find();
      $result = $agent -> field('title,content') -> where(array('id' => array('neq', $info['id']))) -> order('RAND()') -> limit(3) -> select();
    }else{
      $info = $agent -> field('id,title,content') -> find($id);
      $result = $agent -> field('title,content') -> where(array('id' => array('neq', $id))) -> order('RAND()') -> limit(3) -> select();
    }
    $this -> assign('info', $info);
    $this -> assign('result', $result);
    $this -> display();
  }

  //add 前置方法
  public function _before_add(){
    $this -> _before_index();
  }

  //加盟申请
  public function add(){
    $agent = M('AgentJoin');
    //处理增加
    if(!empty($_POST['type'])){
      if($this -> _post('verify', 'md5') != $_SESSION['verify']){
	echo '<script>alert("验证码错误");history.go(-1);</script>';
	exit();
      }
      $agent_add = D('AgentAdd');
      if(!$agent_add -> create()){
	R('Public/errorjump',array($agent_add -> getError()));
      }
      if($agent_add -> add()){
	echo '<script>alert("感谢您对易搜的支持！您所提交的数据我们将在36小时内给予审核后通过！多谢您的合作！");location.href="'.__ACTION__.'";</script>';
	exit();
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    //随机读取3条数据
    $result = $agent -> field('title,content') -> order('RAND()') -> limit(3) -> select();
    $this -> assign('result', $result);
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询经营类型
    $result_add_type = M('AgentAddType') -> field('id,name') -> select();
    $this -> assign('result_add_type', $result_add_type);
    $this -> display();
  }
}

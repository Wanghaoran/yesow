<?php
//代理加盟
class AgentAction extends CommonAction {
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
}

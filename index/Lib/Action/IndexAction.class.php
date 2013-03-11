<?php
class IndexAction extends CommonAction {

  //首页
  public function index(){
    //数据更新消息
    $this -> gettitlenotice();
    //易搜公告动态
    $this -> getyesownotice();
    //最新IT商家
    $this -> newcompany();

    $this -> display();
  }

  //站点公告列表
  public function noticelist(){
  
  }

  //站点公告详情
  public function notice(){
    $notice = M('Notice');
    $id = $this -> _get('id', 'intval');
    //点击量加一
    $notice -> where(array('id' => $id)) -> setInc('clickcount');
    //热门公告
    $result_hotnotice = $notice -> field('id,title,titleattribute,addtime') -> order('clickcount DESC') -> limit(15) -> select();
    $this -> assign('result_hotnotice', $result_hotnotice);
    //结果
    $result = $notice -> field('title,keywords,content,addtime,source,clickcount') -> find($id);
    $this -> assign('result', $result);
    //同类公告
    $where_similar = array();
    foreach(explode(' ', $result['keywords']) as $value){
      $where_similar['keywords'][] = array('LIKE', '%' . $value . '%');
    }
    $where_similar['keywords'][] = 'or';
    $result_similarnotice = $notice -> field('id,title,titleattribute,addtime') -> where($where_similar) -> order('addtime DESC') -> limit(15) -> select();
    $this -> assign('result_similarnotice', $result_similarnotice);
    //读取评论
    $comment_where = array();
    $comment_where['nc.nid'] = $id;
    $comment_where['nc.status'] = 2;
    import("ORG.Util.Page");// 导入分页类
    $count = $notice -> table('yesow_notice_comment as nc') -> where($comment_where) -> count('id');
    $page = new Page($count, 5);//每页5条
    $page->setConfig('header','条评论');
    $show = $page -> show();
    $result_comment = $notice -> table('yesow_notice_comment as nc') -> field('m.name,nc.content,nc.addtime,nc.floor') -> where($comment_where) -> join('yesow_member as m ON nc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('floor ASC') -> select();
    $this -> assign('result_comment', $result_comment);
    $this -> assign('show', $show);

    $this -> display();
  }

  //获取标题公告
  private function gettitlenotice(){
    if(S('index_title_notice')){
      $this -> assign('index_title_notice', S('index_title_notice'));
    }else{
      $result = M('TitleNotice') -> table('yesow_title_notice as tn') -> field('tn.title,tnt.name as tname') -> join('yesow_title_notice_type as tnt ON tn.tid = tnt.id') -> order('tn.addtime DESC') -> limit(10) -> select();
      S('index_title_notice', $result);
      $this -> assign('index_title_notice', $result);
    }
  }

  //获取易搜公告
  private function getyesownotice(){
    if(S('index_yesow_notice')){
      $this -> assign('index_yesow_notice', S('index_yesow_notice'));
    }else{
      $result = M('Notice') -> field('id,title,titleattribute,addtime') -> order('addtime DESC') -> limit(10) -> select();
      S('index_yesow_notice', $result);
      $this -> assign('index_yesow_notice', $result);
    }
  }

  //最新IT商家
  private function newcompany(){
    $company = M('Company');
    $new_company = $company -> field('id,name') -> order('addtime DESC') -> limit(20) -> select();
    $this -> assign('new_company', $new_company);
  }

  //提交公告评论
  public function commit(){
    if($this -> _post('code', 'md5') != $_SESSION['verify']){
      $this -> error(L('VERIFY_ERROR'));
    }
    $comment = D('NoticeComment');
    $data['nid'] = $this -> _post('nid', 'intval');
    $data['mid'] = isset($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
    $data['content'] = $this -> _post('content');
    if(!$comment -> create($data)){
      $this -> error($comment -> getError());
    }
    if($comment -> add()){
      $this -> success(L('ARTICLE_COMMIT_ADD_SUCCESS'));
    }else{
      $this -> error(L('ARTICLE_COMMIT_ADD_ERROR'));
    }
  }

  //关于我们
  public function aboutus(){
    $aboutus = M('Aboutus');
    //查询所有标题
    $result_title = $aboutus -> field('id,title') -> order('sort ASC') -> select();
    $this -> assign('result_title', $result_title);
    //如果没传入id,默认取第一个
    $id = isset($_GET['id']) ? $_GET['id'] : $result_title[0]['id'];
    $this -> assign('id', $id);
    //读取该id对应的内容
    $result_content = $aboutus -> field('title,content') -> find($id);
    $this -> assign('result_content', $result_content);
    $this -> display();
  }

  //分站信息
  public function childsite(){
    if(S('index_child_site')){
      $this -> assign('index_child_site', S('index_child_site'));
    }else{
      $result = M('Area') -> field('id,name') -> where(array('name' => array('neq', '主站'))) -> select();
      $childsite = M('ChildSite');
      foreach($result as $key => $value){
	$result[$key]['childsite'] = $childsite -> field('domain,name') -> where(array('aid' => $value['id'])) -> select();
      }
      S('index_child_site', $result);
      $this -> assign('index_child_site', $result);
    }
  }
  
}

<?php
class InfoAction extends CommonAction {

  //资讯首页
  public function info(){
    //最新更新
    $article = M('InfoArticle');
    $result_newest = $article -> field('id,title') -> where('status=2') -> order('addtime DESC') -> limit(10) -> select();
    $this -> assign('result_newest', $result_newest);
    //热门看点
    $result_hotpoint = $article -> field('id,title') -> where('status=2') -> order('hits DESC') -> limit(10) -> select();
    $this -> assign('result_hotpoint', $result_hotpoint);
    //图片幻灯
    $article_pic = M('InfoArticlePic');
    $result_pic = $article_pic -> table('yesow_info_article_pic as iap') -> field('iap.aid,iap.address,ia.title as title') -> where(array('iap.isshow_index' => 1)) -> order('iap.addtime DESC') -> limit(6) -> join('yesow_info_article as ia ON iap.aid = ia.id') -> select();
    $this -> assign('result_pic', $result_pic);
    //一级分类
    $one_column = M('InfoOneColumn');
    $result_one_column = $one_column -> field('id,name') -> order('sort ASC') -> where(array('isshow' => 1)) -> select();
    $this -> assign('result_one_column_num', count($result_one_column) - 1);
    //查分类下文章
    foreach($result_one_column as $key => $value){
      //分类下6个文章标题列表
      $result_one_column[$key]['articlelist'] = $article -> table('yesow_info_article as ia') -> field('ia.id,ia.title,ita.name as tname') -> where(array('ia.classid' => $value['id'], 'ia.status' => 2)) -> order('addtime DESC') -> limit('1,6') -> join('yesow_info_title_attribute as ita ON ia.tid = ita.id') -> select();
      //分类下第一个文章标题+图片+内容
      $result_one_column[$key]['fristarticle'] = $article -> field('id,title,content') -> where(array('classid' => $value['id'], 'status' => 2)) -> order('addtime DESC') -> limit('1') -> select();
      $result_one_column[$key]['fristarticle'][0]['content'] = msubstr(strip_tags($result_one_column[$key]['fristarticle'][0]['content']), 0, 90);
      //图片
      $result_one_column[$key]['fristarticle'][0]['pic'] = $article_pic -> getFieldByaid($result_one_column[$key]['fristarticle'][0]['id'], 'address');
    }
    $this -> assign('result_one_column', $result_one_column);
    $this -> display();
  }

  //资讯一级页
  public function infolist(){
    $id = $this -> _get('id', 'intval');
    $info_one_column = M('InfoOneColumn');
    $article = M('InfoArticle');
    $article_pic = M('InfoArticlePic');
    //配置项
    $conf = $info_one_column -> field('name,hotcommentnum,hotpointnum,slideimgnum,listpagernum') -> find($id);
    //面包屑
    $this -> assign('title', $conf['name']);
    //热门看点
    $result_hot_point = $article -> table('yesow_info_article as ia') -> field('ia.id,ia.title,itc.name as cname,iap.address as address') -> where(array('ia.classid' => $id, 'ia.status' => 2)) -> join('yesow_info_two_column as itc ON ia.colid = itc.id') -> join('(SELECT aid,address FROM yesow_info_article_pic GROUP BY aid) as iap ON iap.aid = ia.id') -> order('ia.hits DESC') -> limit($conf['hotpointnum']) -> select();
    $this -> assign('result_hot_point', $result_hot_point);
    //幻灯展示,先查此一级分类下的文章id,因为图片表中没有一级分类字段
    $aid_tmp = $article -> field('id') -> where(array('classid' => $id)) -> select();
    foreach($aid_tmp as $value){
      $aid[] = $value['id'];
    }
    $result_pic = $article_pic -> table('yesow_info_article_pic as iap') -> field('iap.aid,iap.address,ia.title as title') -> where(array('iap.isshow_onelist' => 1, 'iap.aid' => array('in', $aid))) -> order('iap.addtime DESC') -> limit(6) -> join('yesow_info_article as ia ON iap.aid = ia.id') -> limit($conf['slideimgnum']) -> select();
    $this -> assign('result_pic', $result_pic);
    //二级分类
    $info_two_column = M('InfoTwoColumn');
    $result_two_column = $info_two_column -> field('id,name') -> where(array('oneid' => $id, 'isoneshow' => 1)) -> select();
    foreach($result_two_column as $key => $value){
      //每个分类下的文章列表
      $result_two_column[$key]['articlelist'] = $article -> table('yesow_info_article as ia') -> field('ia.id,ia.title,ita.name as tname') -> where(array('ia.colid' => $value['id'], 'ia.status' => 2)) -> join('yesow_info_title_attribute as ita ON ia.tid = ita.id') -> limit('1,' . ($conf['listpagernum'] - 1)) -> order('addtime DESC') -> select();
      //每个分类下的头一篇图文文章
      $result_two_column[$key]['firstarticle'] = $article -> table('yesow_info_article as ia') -> field('ia.id,ia.title,content,iap.address') -> where(array('ia.colid' => $value['id'], 'ia.status' => 2)) -> join('yesow_info_article_pic as iap ON ia.id = iap.aid') -> order('ia.addtime DESC') -> find();
      $result_two_column[$key]['firstarticle']['content'] = msubstr(strip_tags($result_two_column[$key]['firstarticle']['content']), 0, 135);
      //分类热评  暂时做成以点击量排序
      $result_two_column[$key]['hotcomments'] = $article -> table('yesow_info_article as ia') -> field('ia.id,ia.title') -> join('(SELECT aid,COUNT(id) as count FROM yesow_info_article_comment GROUP BY aid) as ctc ON ia.id = ctc.aid') -> where(array('ia.colid' => $value['id'], 'ia.status' => 2)) -> order('ctc.count DESC') -> limit($conf['hotcommentnum']) -> select();
    }
    $this -> assign('result_two_column', $result_two_column);
    $this -> display();
  }

  //资讯二级详情页
  public function infodetail(){
    $id = $this -> _get('id', 'intval');
    $info_two_column = M('InfoTwoColumn');
    $article_pic = M('InfoArticlePic');
    $article = M('InfoArticle');
    //配置项
    $conf = $info_two_column -> field('oneid,name,leftpicnum,hotpointnum,pagernum') -> find($id);
    //面包屑
    $this -> assign('twotitle', $conf['name']);
    $this -> assign('onetitle', M('InfoOneColumn') -> field('id,name') -> find($conf['oneid']));
    //左侧图片
    $result_left_pic = $article_pic -> field('aid,address') -> where(array('colid' => $id)) -> order('addtime DESC') -> limit($conf['leftpicnum']) -> group('aid') -> select();
    $this -> assign('result_left_pic', $result_left_pic);
    //热门看点
    $result_hot_point = $article -> table('yesow_info_article as ia') -> field('ia.id,ia.title,ia.addtime,iap.address as address') -> where(array('ia.colid' => $id, 'ia.status' => 2)) -> join('(SELECT aid,address FROM yesow_info_article_pic GROUP BY aid) as iap ON iap.aid = ia.id') -> order('ia.hits DESC') -> limit($conf['hotpointnum']) -> select();
    $this -> assign('result_hot_point', $result_hot_point);
    //中间文章
    $where = array();
    $where['ia.colid'] = $id;
    $where['ia.status'] = 2;
    import("ORG.Util.Page");// 导入分页类
    $count = $article -> table('yesow_info_article as ia') -> where($where) -> count('id');
    $page = new Page($count, $conf['pagernum']);
    $show = $page -> show();
    $result_article = $article -> table('yesow_info_article as ia') -> field('ia.id,ia.title,ita.name as tname') -> where($where) -> order('ia.addtime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> join('yesow_info_title_attribute as ita ON ia.tid = ita.id') -> select();
    $this -> assign('result_article', $result_article);
    $this -> assign('show', $show);
    $this -> display();
  }

  //查看文章
  public function article(){
    $id = $this -> _get('id', 'intval');
    $infoarticle = M('InfoArticle');
    $comment = M('InfoArticleComment');
    //面包屑
    $title = $infoarticle -> field('classid,colid') -> find($id);
    $this -> assign('title1', M('InfoOneColumn') -> field('id,name') -> find($title['classid']));
    $this -> assign('title2', M('InfoTwoColumn') -> field('id,name') -> find($title['colid']));
    //文章
    $result = $infoarticle -> table('yesow_info_article as ia') -> field('ia.title,ia.source,m.name as mname,a.name as aname,ia.addtime,content') -> join('yesow_member as m ON ia.authorid = m.id') -> join('yesow_admin as a ON ia.auditid = a.id') -> where(array('ia.id' => $id)) -> find();
    $this -> assign('result', $result);
    //点击量加一
    $infoarticle -> where(array('id' => $id)) -> setInc('hits');
    //读取最近更新
    $recent_updates = $infoarticle -> table('yesow_info_article as ia') -> field('ia.id,ia.title,itc.name as cname,ia.addtime') -> join('yesow_info_two_column as itc ON ia.colid = itc.id') -> where('ia.status=2') -> order('addtime DESC') -> limit(10) -> select();
    $this -> assign('recent_updates', $recent_updates);
    //读取热门点击
    $hot_clicks = $infoarticle -> table('yesow_info_article as ia') -> field('ia.id,ia.title,itc.name as cname,ia.addtime') -> join('yesow_info_two_column as itc ON ia.colid = itc.id') -> where('ia.status=2') -> order('hits DESC') -> limit(10) -> select();
    $this -> assign('hot_clicks', $hot_clicks);
    //读取评论
    $comment_where = array();
    $comment_where['iac.aid'] = $id;
    $comment_where['iac.status'] = 2;
    import("ORG.Util.Page");// 导入分页类
    $count = $comment -> table('yesow_info_article_comment as iac') -> where($comment_where) -> count('id');
    $page = new Page($count, 10);//每页10条
    $page->setConfig('header','条评论');
    $show = $page -> show();
    $result_comment = $comment -> table('yesow_info_article_comment as iac') -> field('m.name,iac.content,iac.addtime,iac.floor') -> where($comment_where) -> join('yesow_member as m ON iac.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('floor ASC') -> select();
    $this -> assign('result_comment', $result_comment);
    $this -> assign('show', $show);
    $this -> display();
  }

  //提交评论
  public function commit(){
    if($this -> _post('code', 'md5') != $_SESSION['verify']){
      $this -> error(L('VERIFY_ERROR'));
    }
    $commit = D('InfoArticleComment');
    $data['aid'] = $this -> _post('aid', 'intval');
    $data['mid'] = isset($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
    $data['content'] = $this -> _post('content');
    if(!$commit -> create($data)){
      $this -> error($commit -> getError());
    }
    if($commit -> add()){
      $this -> success(L('ARTICLE_COMMIT_ADD_SUCCESS'));
    }else{
      $this -> error(L('ARTICLE_COMMIT_ADD_ERROR'));
    }
  }
}

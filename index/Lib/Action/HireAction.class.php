<?php
class HireAction extends CommonAction {

  //首页
  public function index(){
    //先取得分站id
    $csid = D('admin://ChildSite') -> getid();
    //当前时间
    $time = time();


    /* ---------------- 旺铺出租 ---------------- */
    $store_rent_sort = M('StoreRentSort');
    //先读取推荐商家
    $where_sort = array();
    if($csid){
      $where_sort['sr.csid'] = $csid;
    }
    $where_sort['sr.ischeck'] = 1;
    $where_sort['srs.starttime'] = array('elt', $time);
    $where_sort['srs.endtime'] = array('egt', $time);
    $result_sort = $store_rent_sort -> table('yesow_store_rent_sort as srs') -> field('sr.id,sr.title,srt.name,cs.name as csname') -> join('yesow_store_rent as sr ON srs.srid = sr.id') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> limit(17) -> where($where_sort) -> order('srs.sort DESC') -> select();
    $this -> assign('result_sort', $result_sort);
    //推荐的数量
    $sort_num = count($result_sort);
    $this -> assign('sort_num', $sort_num);
    //如果推荐的数量大于5，则计算超出的数量，否则计算差值
    if($sort_num_up > 5){
      $sort_num_up = $sort_num - 5;
      $this -> assign('sort_num_up', $sort_num_up);
    }else{
      $sort_num_down = 5 - $sort_num;
      $this -> assign('sort_num_down', $sort_num_down);
    }
    
    //非推荐读取的数量
    $not_sort_num = 17 - $sort_num;
    //再读取其他商家
    $store_rent = M('StoreRent');
    //过滤掉推荐中已有的商家
    $del_id_arr = array();
    foreach($result_sort as $value){
      $del_id_arr[] = $value['id'];
    }
    $where = array();
    $where['sr.ischeck'] = 1;
    $where['sr.endtime'] = array('egt', $time);
    if(!empty($del_id_arr)){
      $where['sr.id'] = array('not in', $del_id_arr);
    }
    $result = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,srt.name,cs.name as csname') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> limit($not_sort_num) -> order('updatetime DESC') -> where($where) -> select();
    $this -> assign('result', $result);
    /* ---------------- 旺铺出租 ---------------- */


    $this -> display();
  }

  //旺铺租转列表页
  public function storerentlist(){
    //先取得分站id
    $csid = D('admin://ChildSite') -> getid();
    //当前时间
    $time = time();
    
    //先读取推荐商家
    $store_rent_sort = M('StoreRentSort');
    $where_sort = array();
    if($csid){
      $where_sort['sr.csid'] = $csid;
    }
    $where_sort['sr.ischeck'] = 1;
    $where_sort['srs.starttime'] = array('elt', $time);
    $where_sort['srs.endtime'] = array('egt', $time);

    //先读取一遍，用于确定排除的id
    $id_arr = $store_rent_sort -> table('yesow_store_rent_sort as srs') -> field('srs.srid as id') -> join('yesow_store_rent as sr ON srs.srid = sr.id') -> where($where) -> select();

    //推荐的数量，用于分页
    //$sort_count_temp = count($id_arr);

    //再读取其他商家
    $store_rent = M('StoreRent');
    //过滤掉推荐中已有的商家
    $del_id_arr = array();
    foreach($id_arr as $value){
      $del_id_arr[] = $value['id'];
    }
    $where = array();
    $where['sr.ischeck'] = 1;
    $where['sr.endtime'] = array('egt', $time);
    if(!empty($del_id_arr)){
      $where['sr.id'] = array('not in', $del_id_arr);
    }
    import("ORG.Util.Page");// 导入分页类
    //推荐数量
    $sort_count = $store_rent_sort -> table('yesow_store_rent_sort as srs') -> join('yesow_store_rent as sr ON srs.srid = sr.id') -> where($where_sort) -> count();
    //其余数量
    $other_count = $store_rent ->  table('yesow_store_rent as sr') -> where($where) -> count();
    //总数
    $count = $sort_count + $other_count;
    $page = new Page($count, 20);
    $show = $page -> show();
    $this -> assign('show', $show);

    $result_sort = $store_rent_sort -> table('yesow_store_rent_sort as srs') -> field('sr.id,sr.title,srt.name,cs.name as csname,sr.addtime') -> join('yesow_store_rent as sr ON srs.srid = sr.id') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where_sort) -> order('srs.sort DESC') -> select();
    $this -> assign('result_sort', $result_sort);

    //自定义分页条件
    $limit_row = $page -> firstRow - $sort_count <= 0 ? 0 : $page -> firstRow - $sort_count;
    $limit_lits = count($result_sort) == 0 ? $page -> listRows : $page -> listRows - count($result_sort);

    $result = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,srt.name,cs.name as csname,sr.addtime') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> limit($limit_row . ',' . $limit_lits) -> join('yesow_child_site as cs ON sr.csid = cs.id') -> where($where) -> order('sr.updatetime DESC') -> select();
    $this -> assign('result', $result);

    $this -> display();
  }

  //旺铺租转详情页
  public function storerentinfo(){
    $id = $this -> _get('id', 'intval');
    $store_rent = M('StoreRent');
    //点击量加一
    $store_rent -> where(array('id' => $id)) -> setInc('clickcount');
    $result = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.csid,sr.title,sr.tid,srt.name,cs.name as csname,csa.name as csaname,sr.clickcount,sr.linkman,sr.tel,sr.address,sr.email,sr.updatetime,sr.endtime,sr.content,sr.systemimage,sr.image') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> join('yesow_child_site_area as csa ON sr.csaid = csa.id') -> where(array('sr.id' => $id)) -> find();
    $this -> assign('result', $result);
    //左侧相关链接，读取类别相同的数据，以更新时间倒序排序
    $about_left = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,srt.name,cs.name as csname') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> where(array('sr.tid' => $result['tid'], 'sr.id' => array('neq', $result['id']))) -> order('sr.updatetime DESC') -> limit(10) -> select();
    $this -> assign('about_left', $about_left);
    //右侧相关链接，读取地区相同的数据，以更新时间倒序排序
    $about_right = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,srt.name,cs.name as csname') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> where(array('sr.csid' => $result['csid'], 'sr.id' => array('neq', $result['id']))) -> order('sr.updatetime DESC') -> limit(10) -> select();
    $this -> assign('about_right', $about_right);

    $comment = M('StoreRentComment');
 
    //读取评论
    $comment_where = "src.srid={$id} AND src.status=2";
    //如果会员基本设置允许会员看到自己的未经审核的评论，则在这里加上查询条件
    if(M('MemberSetup') -> getFieldByname('viewcomment', 'value') == 1 && isset($_SESSION[C('USER_AUTH_KEY')])){
      $sid = session(C('USER_AUTH_KEY'));
      $where_setup = "src.srid={$id} AND src.mid={$sid}";
      $comment_where = '(' . $comment_where . ')' . 'OR' . '(' . $where_setup . ')';
    }
    import("ORG.Util.Page");// 导入分页类
    $count = $comment -> table('yesow_store_rent_comment as src') -> where($comment_where) -> count();
    $page = new Page($count, 10);//每页10条
    $page->setConfig('header','条评论');
    $show = $page -> show();
    $result_comment = $comment -> table('yesow_store_rent_comment as src') -> field('m.name,src.content,src.addtime,src.floor,src.face') -> where($comment_where) -> join('yesow_member as m ON src.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('floor ASC') -> select();
    $this -> assign('result_comment', $result_comment);
    $this -> assign('show', $show);

    $this -> display();
  }

  //动感传媒提交评论
  public function storerentcomment(){
    if($this -> _post('code', 'md5') != $_SESSION['verify']){
      $this -> error(L('VERIFY_ERROR'));
    }
    $commit = D('StoreRentComment');
    $data['srid'] = $this -> _post('srid', 'intval');
    $data['mid'] = isset($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
    $data['content'] = $this -> _post('content');
    $data['face'] = $this -> _post('face');
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

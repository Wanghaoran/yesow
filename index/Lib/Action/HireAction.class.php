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
    if($sort_num > 5){
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
    $result = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,srt.name,cs.name as csname,sr.updatetime') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> limit($not_sort_num) -> order('updatetime DESC') -> where($where) -> select();
    $this -> assign('result', $result);
    /* ---------------- 旺铺出租 ---------------- */

    /* ---------------- 二手滞销 ---------------- */
    $sell_used_sort = M('SellUsedSort');
    //先读取推荐商家
    $where_sort = array();
    if($csid){
      $where_sort['su.csid'] = $csid;
    }
    $where_sort['su.ischeck'] = 1;
    $where_sort['sus.starttime'] = array('elt', $time);
    $where_sort['sus.endtime'] = array('egt', $time);
    $result_sellused_sort = $sell_used_sort -> table('yesow_sell_used_sort as sus') -> field('su.id,su.title,sut.name,cs.name as csname') -> join('yesow_sell_used as su ON sus.suid = su.id') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> limit(17) -> where($where_sort) -> order('sus.sort DESC') -> select();
    $this -> assign('result_sellused_sort', $result_sellused_sort);
    //推荐的数量
    $sellused_sort_num = count($result_sellused_sort);
    $this -> assign('sellused_sort_num', $sellused_sort_num);
    //如果推荐的数量大于5，则计算超出的数量，否则计算差值
    if($sellused_sort_num > 5){
      $sellused_sort_num_up = $sellused_sort_num - 5;
      $this -> assign('sellused_sort_num_up', $sellused_sort_num_up);
    }else{
      $sellused_sort_num_down = 5 - $sellused_sort_num;
      $this -> assign('sellused_sort_num_down', $sellused_sort_num_down);
    }
    
    //非推荐读取的数量
    $sellused_not_sort_num = 17 - $sellused_sort_num;
    //再读取其他商家
    $sell_used = M('SellUsed');
    //过滤掉推荐中已有的商家
    $del_id_arr = array();
    foreach($result_sellused_sort as $value){
      $del_id_arr[] = $value['id'];
    }
    $where = array();
    $where['su.ischeck'] = 1;
    $where['su.endtime'] = array('egt', $time);
    if(!empty($del_id_arr)){
      $where['su.id'] = array('not in', $del_id_arr);
    }
    $result_sellused = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,sut.name,cs.name as csname,su.updatetime') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> limit($sellused_not_sort_num) -> order('su.updatetime DESC') -> where($where) -> select();
    $this -> assign('result_sellused', $result_sellused);
    /* ---------------- 二手滞销 ---------------- */


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
    if(!empty($_POST['keyword'])){
      $where_sort['sr.title'] = array('LIKE', '%' . $this -> _post('keyword') . '%');
    }

    //先读取一遍，用于确定排除的id
    $id_arr = $store_rent_sort -> table('yesow_store_rent_sort as srs') -> field('srs.srid as id') -> join('yesow_store_rent as sr ON srs.srid = sr.id') -> where($where_sort) -> select();

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
    if(!empty($_POST['keyword'])){
      $where['sr.title'] = array('LIKE', '%' . $this -> _post('keyword') . '%');
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

  //二手滞销列表页
  public function sellusedlist(){
    //先取得分站id
    $csid = D('admin://ChildSite') -> getid();
    //当前时间
    $time = time();
    
    //先读取推荐商家
    $sell_used_sort = M('SellUsedSort');
    $where_sort = array();
    if($csid){
      $where_sort['su.csid'] = $csid;
    }
    $where_sort['su.ischeck'] = 1;
    $where_sort['sus.starttime'] = array('elt', $time);
    $where_sort['sus.endtime'] = array('egt', $time);
    if(!empty($_POST['keyword'])){
      $where_sort['su.title'] = array('LIKE', '%' . $this -> _post('keyword') . '%');
    }

    //先读取一遍，用于确定排除的id
    $id_arr = $sell_used_sort -> table('yesow_sell_used_sort as sus') -> field('sus.suid as id') -> join('yesow_sell_used as su ON sus.suid = su.id') -> where($where_sort) -> select();

    //再读取其他商家
    $sell_used = M('SellUsed');
    //过滤掉推荐中已有的商家
    $del_id_arr = array();
    foreach($id_arr as $value){
      $del_id_arr[] = $value['id'];
    }
    $where = array();
    $where['su.ischeck'] = 1;
    $where['su.endtime'] = array('egt', $time);
    if(!empty($del_id_arr)){
      $where['su.id'] = array('not in', $del_id_arr);
    }
    if(!empty($_POST['keyword'])){
      $where['su.title'] = array('LIKE', '%' . $this -> _post('keyword') . '%');
    }
    import("ORG.Util.Page");// 导入分页类
    //推荐数量
    $sort_count = $sell_used_sort -> table('yesow_sell_used_sort as sus') -> join('yesow_sell_used as su ON sus.suid = su.id') -> where($where_sort) -> count();
    //其余数量
    $other_count = $sell_used ->  table('yesow_sell_used as su') -> where($where) -> count();
    //总数
    $count = $sort_count + $other_count;
    $page = new Page($count, 20);
    $show = $page -> show();
    $this -> assign('show', $show);

    $result_sort = $sell_used_sort -> table('yesow_sell_used_sort as sus') -> field('su.id,su.title,sut.name,cs.name as csname,su.addtime') -> join('yesow_sell_used as su ON sus.suid = su.id') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where_sort) -> order('sus.sort DESC') -> select();
    $this -> assign('result_sort', $result_sort);

    //自定义分页条件
    $limit_row = $page -> firstRow - $sort_count <= 0 ? 0 : $page -> firstRow - $sort_count;
    $limit_lits = count($result_sort) == 0 ? $page -> listRows : $page -> listRows - count($result_sort);

    $result = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,sut.name,cs.name as csname,su.addtime') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> limit($limit_row . ',' . $limit_lits) -> join('yesow_child_site as cs ON su.csid = cs.id') -> where($where) -> order('su.updatetime DESC') -> select();
    $this -> assign('result', $result);

    $this -> display();
  }

  //二手滞销详情页
  public function sellusedinfo(){
    $id = $this -> _get('id', 'intval');
    $sell_used = M('SellUsed');
    //点击量加一
    $sell_used -> where(array('id' => $id)) -> setInc('clickcount');
    $result = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.csid,su.title,su.tid_one,sut.name,cs.name as csname,csa.name as csaname,su.clickcount,su.linkman,su.tel,su.address,su.email,su.updatetime,su.endtime,su.content,su.image') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> join('yesow_child_site_area as csa ON su.csaid = csa.id') -> where(array('su.id' => $id)) -> find();
    $this -> assign('result', $result);
    //左侧相关链接，读取类别相同的数据，以更新时间倒序排序
    $about_left = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,sut.name,cs.name as csname') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> where(array('su.tid_one' => $result['tid_one'], 'su.id' => array('neq', $result['id']))) -> order('su.updatetime DESC') -> limit(10) -> select();
    $this -> assign('about_left', $about_left);
    //右侧相关链接，读取地区相同的数据，以更新时间倒序排序
    $about_right = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,sut.name,cs.name as csname') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> where(array('su.csid' => $result['csid'], 'su.id' => array('neq', $result['id']))) -> order('su.updatetime DESC') -> limit(10) -> select();
    $this -> assign('about_right', $about_right);

    $comment = M('SellUsedComment');
    //读取评论
    $comment_where = "suc.suid={$id} AND suc.status=2";
    //如果会员基本设置允许会员看到自己的未经审核的评论，则在这里加上查询条件
    if(M('MemberSetup') -> getFieldByname('viewcomment', 'value') == 1 && isset($_SESSION[C('USER_AUTH_KEY')])){
      $sid = session(C('USER_AUTH_KEY'));
      $where_setup = "suc.suid={$id} AND suc.mid={$sid}";
      $comment_where = '(' . $comment_where . ')' . 'OR' . '(' . $where_setup . ')';
    }
    import("ORG.Util.Page");// 导入分页类
    $count = $comment -> table('yesow_sell_used_comment as suc') -> where($comment_where) -> count();
    $page = new Page($count, 10);//每页10条
    $page->setConfig('header','条评论');
    $show = $page -> show();
    $result_comment = $comment -> table('yesow_sell_used_comment as suc') -> field('m.name,suc.content,suc.addtime,suc.floor,suc.face') -> where($comment_where) -> join('yesow_member as m ON suc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('floor ASC') -> select();
    $this -> assign('result_comment', $result_comment);
    $this -> assign('show', $show);

    $this -> display();
  }

  //二手滞销提交评论
  public function sellusedcomment(){
    if($this -> _post('code', 'md5') != $_SESSION['verify']){
      $this -> error(L('VERIFY_ERROR'));
    }
    $commit = D('SellUsedComment');
    $data['suid'] = $this -> _post('suid', 'intval');
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

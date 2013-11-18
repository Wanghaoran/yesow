<?php
class HireAction extends CommonAction {

  //首页
  public function index(){
    $time = time();

    /* ---------------- 人才招聘 ---------------- */
    $RecruitJobsSort = M('RecruitJobsSort');
    //先读取推荐内容
    $where_sort = array();
    //if($csid){
      //$where_sort['sr.csid'] = $csid;
    //}
    if(!empty($_POST['title'])){
      $where_sort['rj.name'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    $where_sort['rj.ischeck'] = 1;
    $where_sort['rjs.starttime'] = array('elt', $time);
    $where_sort['rjs.endtime'] = array('egt', $time);
    $result_recruit_sort = $RecruitJobsSort -> alias('rjs') -> field('rj.id,rj.name,rc.id as rcid,rc.name as rcname,cs.name as csname') -> join('yesow_recruit_jobs as rj ON rjs.rjid = rj.id') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> join('yesow_child_site as cs ON rj.jobs_csid = cs.id') -> limit(16) -> group('rj.cid') -> where($where_sort) -> order('rjs.sort DESC') -> select();
    $this -> assign('result_recruit_sort', $result_recruit_sort);
    //推荐的数量
    $recruit_sort_num = count($result_recruit_sort);
    $this -> assign('recruit_sort_num', $recruit_sort_num);
    //如果推荐的数量大于5，则计算超出的数量，否则计算差值
    if($recruit_sort_num > 5){
      $recruit_sort_num_up = $recruit_sort_num - 5;
      $this -> assign('recruit_sort_num_up', $recruit_sort_num_up);
    }else{
      $recruit_sort_num_down = 5 - $recruit_sort_num;
      $this -> assign('recruit_sort_num_down', $recruit_sort_num_down);
    }
    
    //非推荐读取的数量
    $recruit_not_sort_num = 16 - $recruit_sort_num;
    //再读取其他岗位
    $RecruitJobs = M('RecruitJobs');
    //过滤掉推荐中已有的岗位
    $del_id_arr = array();
    foreach($result_recruit_sort as $value){
      $del_id_arr[] = $value['id'];
    }
    $where = array();
    $where['rj.ischeck'] = 1;
    $where['rj.endtime'] = array('egt', $time);
    if(!empty($_POST['title'])){
      $where['rj.name'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($del_id_arr)){
      $where['rj.id'] = array('not in', $del_id_arr);
    }
    $recruit_result = $RecruitJobs -> alias('rj') -> field('rj.id,rj.name,rc.id as rcid,rc.name as rcname,rj.addtime,cs.name as csname') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> join('yesow_child_site as cs ON rj.jobs_csid = cs.id') -> limit($recruit_not_sort_num) -> order('rj.addtime DESC') -> group('rj.cid') -> where($where) -> select();
    $this -> assign('recruit_result', $recruit_result);
    /* ---------------- 人才招聘 ---------------- */


    /* ---------------- 旺铺出租 ---------------- */
    $store_rent_sort = M('StoreRentSort');
    //先读取推荐商家
    $where_sort = array();
    if($csid){
      $where_sort['sr.csid'] = $csid;
    }
    if(!empty($_POST['title'])){
      $where_sort['sr.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    $where_sort['sr.ischeck'] = 1;
    $where_sort['srs.starttime'] = array('elt', $time);
    $where_sort['srs.endtime'] = array('egt', $time);
    $result_sort = $store_rent_sort -> table('yesow_store_rent_sort as srs') -> field('sr.id,sr.title,srt.name,cs.name as csname') -> join('yesow_store_rent as sr ON srs.srid = sr.id') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> limit(16) -> where($where_sort) -> order('srs.sort DESC') -> select();
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
    $not_sort_num = 16 - $sort_num;
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
    if(!empty($_POST['title'])){
      $where['sr.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($del_id_arr)){
      $where['sr.id'] = array('not in', $del_id_arr);
    }
    $result = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,srt.name,cs.name as csname,sr.updatetime') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> limit($not_sort_num) -> order('updatetime DESC') -> where($where) -> select();
    $this -> assign('result', $result);
    /* ---------------- 旺铺出租 ---------------- */

    /* ---------------- 二手交易 ---------------- */
    $sell_used_sort = M('SellUsedSort');
    //先读取推荐商家
    $where_sort = array();
    if($csid){
      $where_sort['su.csid'] = $csid;
    }
    if(!empty($_POST['title'])){
      $where_sort['su.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    $where_sort['su.ischeck'] = 1;
    $where_sort['sus.starttime'] = array('elt', $time);
    $where_sort['sus.endtime'] = array('egt', $time);
    $where_sort['su.tid_one'] = 1;
    $result_sellused_sort = $sell_used_sort -> table('yesow_sell_used_sort as sus') -> field('su.id,su.title,sut.name,cs.name as csname') -> join('yesow_sell_used as su ON sus.suid = su.id') -> join('yesow_sell_used_type as sut ON su.tid_two = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> limit(16) -> where($where_sort) -> order('sus.sort DESC') -> select();
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
    $sellused_not_sort_num = 16 - $sellused_sort_num;
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
    $where['su.tid_one'] = 1;
    if(!empty($_POST['title'])){
      $where['su.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    if(!empty($del_id_arr)){
      $where['su.id'] = array('not in', $del_id_arr);
    }
    $result_sellused = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,sut.name,cs.name as csname,su.updatetime') -> join('yesow_sell_used_type as sut ON su.tid_two = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> limit($sellused_not_sort_num) -> order('su.updatetime DESC') -> where($where) -> select();
    $this -> assign('result_sellused', $result_sellused);
    /* ---------------- 二手交易 ---------------- */

    /* ---------------- 库存滞销 ---------------- */
    $sell_used_sort = M('SellUsedSort');
    //先读取推荐商家
    $where_sort = array();
    if($csid){
      $where_sort['su.csid'] = $csid;
    }
    $where_sort['su.ischeck'] = 1;
    $where_sort['sus.starttime'] = array('elt', $time);
    $where_sort['sus.endtime'] = array('egt', $time);
    $where_sort['su.tid_one'] = 2;
    if(!empty($_POST['title'])){
      $where_sort['su.title'] = array('LIKE', '%' . $this -> _post('title') . '%');
    }
    $result_old_sort = $sell_used_sort -> table('yesow_sell_used_sort as sus') -> field('su.id,su.title,sut.name,cs.name as csname') -> join('yesow_sell_used as su ON sus.suid = su.id') -> join('yesow_sell_used_type as sut ON su.tid_two = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> limit(16) -> where($where_sort) -> order('sus.sort DESC') -> select();
    $this -> assign('result_old_sort', $result_old_sort);
    //推荐的数量
    $sellused_old_num = count($result_old_sort);
    $this -> assign('sellused_old_num', $sellused_old_num);
    //如果推荐的数量大于5，则计算超出的数量，否则计算差值
    if($sellused_old_num > 5){
      $sellused_old_num_up = $sellused_old_num - 5;
      $this -> assign('sellused_old_num_up', $sellused_old_num_up);
    }else{
      $sellused_old_num_down = 5 - $sellused_old_num;
      $this -> assign('sellused_old_num_down', $sellused_old_num_down);
    }
    
    //非推荐读取的数量
    $sellused_not_old_num = 16 - $sellused_old_num;
    //再读取其他商家
    $sell_used = M('SellUsed');
    //过滤掉推荐中已有的商家
    $del_id_arr = array();
    foreach($result_old_sort as $value){
      $del_id_arr[] = $value['id'];
    }
    $where = array();
    $where['su.ischeck'] = 1;
    $where['su.endtime'] = array('egt', $time);
    $where['su.tid_one'] = 2;
    if(!empty($del_id_arr)){
      $where['su.id'] = array('not in', $del_id_arr);
    }
    $result_old = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,sut.name,cs.name as csname,su.updatetime') -> join('yesow_sell_used_type as sut ON su.tid_two = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> limit($sellused_not_sort_num) -> order('su.updatetime DESC') -> where($where) -> select();
    $this -> assign('result_old', $result_old);
    /* ---------------- 库存滞销 ---------------- */

    /* ---------------- 最新更新 ---------------- */
    //招聘5条
    $where_new_update_recruit = array();
    $where_new_update_recruit['rj.ischeck'] = 1;
    $where_new_update_recruit['rj.endtime'] = array('egt', $time);
    $result_new_update_recruit = $RecruitJobs -> alias('rj') -> field('rj.id,rj.name,rc.id as rcid,rc.name as rcname,rj.addtime,cs.name as csname') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> join('yesow_child_site as cs ON rj.jobs_csid = cs.id') -> limit(7) -> order('rj.addtime DESC') -> group('rj.cid') -> where($where_new_update_recruit) -> select();
    $this -> assign('result_new_update_recruit', $result_new_update_recruit);

    //出租5条
    $where_new_update_store = array();
    $where_new_update_store['sr.ischeck'] = 1;
    $where_new_update_store['sr.endtime'] = array('egt', $time);
    $result_new_update_store = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,cs.name as csname') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> where($where_new_update_store) -> order('sr.updatetime DESC') -> limit(6) -> select();
    $this -> assign('result_new_update_store', $result_new_update_store);
    //二手5条
    $where_new_update_used = array();
    $where_new_update_used['su.ischeck'] = 1;
    $where_new_update_used['su.endtime'] = array('egt', $time);
    $where_new_update_used['su.tid_one'] = 1;
    $result_new_update_used = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,cs.name as csname') -> join('yesow_child_site as cs ON su.csid = cs.id') -> where($where_new_update_used) -> order('su.updatetime DESC') -> limit(6) -> select();
    $this -> assign('result_new_update_used', $result_new_update_used);
    //滞销5条
    $where_new_update_old = array();
    $where_new_update_old['su.ischeck'] = 1;
    $where_new_update_old['su.endtime'] = array('egt', $time);
    $where_new_update_old['su.tid_one'] = 2;
    $result_new_update_old = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,cs.name as csname') -> join('yesow_child_site as cs ON su.csid = cs.id') -> where($where_new_update_old) -> order('su.updatetime DESC') -> limit(6) -> select();
    $this -> assign('result_new_update_old', $result_new_update_old);
    /* ---------------- 最新更新 ---------------- */

    /* ---------------- 热门信息/本月最热 ---------------- */
    //招聘7条
    $where_hot_recruit = array();
    $where_hot_recruit['rj.ischeck'] = 1;
    $where_hot_recruit['rj.endtime'] = array('egt', $time);
    $result_hot_recruit = $RecruitJobs -> alias('rj') -> field('rj.id,rj.name,rc.id as rcid,rc.name as rcname,rj.addtime,cs.name as csname') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> join('yesow_child_site as cs ON rj.jobs_csid = cs.id') -> limit(7) -> order('rc.clickcount DESC') -> group('rj.cid') -> where($where_hot_recruit) -> select();
    $this -> assign('result_hot_recruit', $result_hot_recruit);
    //出租6条
    $where_hot_store = array();
    $where_hot_store['sr.ischeck'] = 1;
    $where_hot_store['sr.endtime'] = array('egt', $time);
    $result_hot_store = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,cs.name as csname') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> where($where_hot_store) -> order('sr.clickcount DESC') -> limit(6) -> select();
    $this -> assign('result_hot_store', $result_hot_store);
    //二手8条
    $where_hot_used = array();
    $where_hot_used['su.ischeck'] = 1;
    $where_hot_used['su.endtime'] = array('egt', $time);
    $where_hot_used['su.tid_one'] = 1;
    $result_hot_used = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,cs.name as csname') -> join('yesow_child_site as cs ON su.csid = cs.id') -> where($where_hot_used) -> order('su.clickcount DESC') -> limit(6) -> select();
    $this -> assign('result_hot_used', $result_hot_used); 
    //滞销6条
    $where_hot_old = array();
    $where_hot_old['su.ischeck'] = 1;
    $where_hot_old['su.endtime'] = array('egt', $time);
    $where_hot_old['su.tid_one'] = 2;
    $result_hot_old = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,cs.name as csname') -> join('yesow_child_site as cs ON su.csid = cs.id') -> where($where_hot_old) -> order('su.clickcount DESC') -> limit(6) -> select();
    $this -> assign('result_hot_old', $result_hot_old);
    /* ---------------- 热门信息/本月最热 ---------------- */


    /* ---------------- 特推商家 ---------------- */
    //招聘7条
    $where_go_sort = array();
    $where_go_sort['rj.ischeck'] = 1;
    $where_go_sort['rjs.starttime'] = array('elt', $time);
    $where_go_sort['rjs.endtime'] = array('egt', $time);
    $result_go_recruit = $RecruitJobsSort -> alias('rjs') -> field('rj.id,rj.name,rc.id as rcid,rc.name as rcname,cs.name as csname') -> join('yesow_recruit_jobs as rj ON rjs.rjid = rj.id') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> join('yesow_child_site as cs ON rj.jobs_csid = cs.id') -> limit(7) -> group('rj.cid') -> where($where_go_sort) -> order('rjs.sort DESC') -> select();
    $this -> assign('result_go_recruit', $result_go_recruit);
    //出租6条
    $where_go_store = array();
    if($csid){
      $where_go_store['sr.csid'] = $csid;
    }
    $where_go_store['sr.ischeck'] = 1;
    $where_go_store['srs.starttime'] = array('elt', $time);
    $where_go_store['srs.endtime'] = array('egt', $time);
    $result_go_store = $store_rent_sort -> table('yesow_store_rent_sort as srs') -> field('sr.id,sr.title,srt.name,cs.name as csname') -> join('yesow_store_rent as sr ON srs.srid = sr.id') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> limit(6) -> where($where_go_store) -> order('srs.sort DESC') -> select();
    $this -> assign('result_go_store', $result_go_store);
    //二手6条
    $where_go_sellused = array();
    if($csid){
      $where_go_sellused['su.csid'] = $csid;
    }
    $where_go_sellused['su.ischeck'] = 1;
    $where_go_sellused['sus.starttime'] = array('elt', $time);
    $where_go_sellused['sus.endtime'] = array('egt', $time);
    $where_go_sellused['su.tid_one'] = 1;
    $result_go_sellused = $sell_used_sort -> table('yesow_sell_used_sort as sus') -> field('su.id,su.title,sut.name,cs.name as csname') -> join('yesow_sell_used as su ON sus.suid = su.id') -> join('yesow_sell_used_type as sut ON su.tid_two = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> limit(6) -> where($where_go_sellused) -> order('sus.sort DESC') -> select();
    $this -> assign('result_go_sellused', $result_go_sellused);
    //滞销6条
    $where_go_old = array();
    if($csid){
      $where_go_old['su.csid'] = $csid;
    }
    $where_go_old['su.ischeck'] = 1;
    $where_go_old['sus.starttime'] = array('elt', $time);
    $where_go_old['sus.endtime'] = array('egt', $time);
    $where_go_old['su.tid_one'] = 2;
    $result_go_old = $sell_used_sort -> table('yesow_sell_used_sort as sus') -> field('su.id,su.title,sut.name,cs.name as csname') -> join('yesow_sell_used as su ON sus.suid = su.id') -> join('yesow_sell_used_type as sut ON su.tid_two = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> limit(6) -> where($where_go_old) -> order('sus.sort DESC') -> select();
    $this -> assign('result_go_old', $result_go_old);
    /* ---------------- 特推商家 ---------------- */
    


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
    if(!empty($_GET['tid'])){
      $where_sort['sr.tid'] = $this -> _get('tid', 'intval');
    }
    if(!empty($_GET['csid'])){
      $where_sort['sr.csid'] = $this -> _get('csid', 'intval');
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
    if(!empty($_GET['tid'])){
      $where['sr.tid'] = $this -> _get('tid', 'intval');
    }
    if(!empty($_GET['csid'])){
      $where['sr.csid'] = $this -> _get('csid', 'intval');
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

    $result_sort = $store_rent_sort -> table('yesow_store_rent_sort as srs') -> field('sr.id,sr.tid,sr.csid,sr.title,srt.name,cs.name as csname,sr.addtime') -> join('yesow_store_rent as sr ON srs.srid = sr.id') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where_sort) -> order('srs.sort DESC') -> select();
    $this -> assign('result_sort', $result_sort);

    //自定义分页条件
    $limit_row = $page -> firstRow - $sort_count <= 0 ? 0 : $page -> firstRow - $sort_count;
    $limit_lits = count($result_sort) == 0 ? $page -> listRows : $page -> listRows - count($result_sort);

    $result = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.tid,sr.csid,sr.title,srt.name,cs.name as csname,sr.addtime,sr.updatetime') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> limit($limit_row . ',' . $limit_lits) -> join('yesow_child_site as cs ON sr.csid = cs.id') -> where($where) -> order('sr.updatetime DESC') -> select();
    $this -> assign('result', $result);

    //推荐信息
    $where_recommended['ischeck'] = 1;
    $where_recommended['endtime'] = array('egt', $time);
    $recommended_result = M('StoreRent') -> field('id,content') -> order('updatetime DESC') -> where($where_recommended) -> limit(11) -> select();
    $this -> assign('recommended_result', $recommended_result);

    $this -> display();
  }

  //旺铺租转详情页
  public function storerentinfo(){
    $id = $this -> _get('id', 'intval');
    $store_rent = M('StoreRent');
    //点击量加一
    $store_rent -> where(array('id' => $id)) -> setInc('clickcount');
    $result = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.csid,sr.title,sr.tid,srt.name,cs.name as csname,csa.name as csaname,sr.clickcount,sr.keyword,sr.linkman,sr.tel,sr.address,sr.email,sr.qqcode,sr.updatetime,sr.endtime,sr.content,sr.systemimage,sr.image') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> join('yesow_child_site_area as csa ON sr.csaid = csa.id') -> where(array('sr.id' => $id)) -> find();
    $this -> assign('result', $result);
    //左侧相关链接，读取类别相同的数据，以更新时间倒序排序
    $about_left = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,srt.name,cs.name as csname') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> where(array('sr.tid' => $result['tid'], 'sr.id' => array('neq', $result['id']), 'ischeck' => 1)) -> order('sr.updatetime DESC') -> limit(10) -> select();
    $this -> assign('about_left', $about_left);
    //右侧相关链接，读取地区相同的数据，以更新时间倒序排序
    $about_right = $store_rent -> table('yesow_store_rent as sr') -> field('sr.id,sr.title,srt.name,cs.name as csname') -> join('yesow_store_rent_type as srt ON sr.tid = srt.id') -> join('yesow_child_site as cs ON sr.csid = cs.id') -> where(array('sr.csid' => $result['csid'], 'sr.id' => array('neq', $result['id']), 'ischeck' => 1)) -> order('sr.updatetime DESC') -> limit(10) -> select();
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

    //推荐信息
    $where_recommended['ischeck'] = 1;
    $where_recommended['endtime'] = array('egt', time());
    $recommended_result = M('StoreRent') -> field('id,content') -> order('updatetime DESC') -> where($where_recommended) -> limit(10) -> select();
    $this -> assign('recommended_result', $recommended_result);

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
    if(!empty($_GET['tid'])){
      $where_sort['su.tid_one'] = $this -> _get('tid', 'intval');
    }
    if(!empty($_GET['csid'])){
      $where_sort['su.csid'] = $this -> _get('csid', 'intval');
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
    if(!empty($_GET['tid'])){
      $where['su.tid_one'] = $this -> _get('tid', 'intval');
    }
    if(!empty($_GET['csid'])){
      $where['su.csid'] = $this -> _get('csid', 'intval');
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

    $result_sort = $sell_used_sort -> table('yesow_sell_used_sort as sus') -> field('su.id,su.tid_one,su.csid,su.title,sut.name,cs.name as csname,su.addtime') -> join('yesow_sell_used as su ON sus.suid = su.id') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where_sort) -> order('sus.sort DESC') -> select();
    $this -> assign('result_sort', $result_sort);

    //自定义分页条件
    $limit_row = $page -> firstRow - $sort_count <= 0 ? 0 : $page -> firstRow - $sort_count;
    $limit_lits = count($result_sort) == 0 ? $page -> listRows : $page -> listRows - count($result_sort);

    $result = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.tid_one,su.csid,su.title,sut.name,cs.name as csname,su.addtime,su.updatetime') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> limit($limit_row . ',' . $limit_lits) -> join('yesow_child_site as cs ON su.csid = cs.id') -> where($where) -> order('su.updatetime DESC') -> select();
    $this -> assign('result', $result);

    //推荐信息
    $where_recommended['ischeck'] = 1;
    $where_recommended['endtime'] = array('egt', $time);
    $recommended_result = M('SellUsed') -> field('id,content') -> order('updatetime DESC') -> where($where_recommended) -> limit(11) -> select();
    $this -> assign('recommended_result', $recommended_result);

    $this -> display();
  }

  //二手滞销详情页
  public function sellusedinfo(){
    $id = $this -> _get('id', 'intval');
    $sell_used = M('SellUsed');
    //点击量加一
    $sell_used -> where(array('id' => $id)) -> setInc('clickcount');
    $result = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.csid,su.title,su.tid_one,sut.name,cs.name as csname,csa.name as csaname,su.price,su.clickcount,su.keyword,su.linkman,su.tel,su.address,su.email,su.updatetime,su.endtime,su.content,su.image') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> join('yesow_child_site_area as csa ON su.csaid = csa.id') -> where(array('su.id' => $id)) -> find();
    $this -> assign('result', $result);
    //左侧相关链接，读取类别相同的数据，以更新时间倒序排序
    $about_left = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,sut.name,cs.name as csname') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> where(array('su.tid_one' => $result['tid_one'], 'su.id' => array('neq', $result['id']), 'su.ischeck' => 1)) -> order('su.updatetime DESC') -> limit(10) -> select();
    $this -> assign('about_left', $about_left);
    //右侧相关链接，读取地区相同的数据，以更新时间倒序排序
    $about_right = $sell_used -> table('yesow_sell_used as su') -> field('su.id,su.title,sut.name,cs.name as csname') -> join('yesow_sell_used_type as sut ON su.tid_one = sut.id') -> join('yesow_child_site as cs ON su.csid = cs.id') -> where(array('su.csid' => $result['csid'], 'su.id' => array('neq', $result['id']), 'su.ischeck' => 1)) -> order('su.updatetime DESC') -> limit(10) -> select();
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

    //推荐信息
    $where_recommended['ischeck'] = 1;
    $where_recommended['endtime'] = array('egt', time());
    $recommended_result = M('SellUsed') -> field('id,content') -> order('updatetime DESC') -> where($where_recommended) -> limit(10) -> select();
    $this -> assign('recommended_result', $recommended_result);

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

  //人才招聘列表页
  public function recruitlist(){
    //先取得分站id
    //$csid = D('admin://ChildSite') -> getid();
    //当前时间
    $time = time();
    
    //先读取推荐岗位
    $RecruitJobsSort = M('RecruitJobsSort');
    $where_sort = array();
    //if($csid){
    //  $where_sort['su.csid'] = $csid;
    //}
    $where_sort['rj.ischeck'] = 1;
    $where_sort['rjs.starttime'] = array('elt', $time);
    $where_sort['rjs.endtime'] = array('egt', $time);

    if(!empty($_POST['name'])){
      $where_sort['rj.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_POST['ciid'])){
      $where_sort['rc.ciid'] = $this -> _post('ciid', 'intval');
    }
    if(!empty($_REQUEST['jobs_csid'])){
      $where_sort['rj.jobs_csid'] = $this -> _request('jobs_csid', 'intval');
    }
    if(!empty($_POST['jobs_csaid'])){
      $where_sort['rj.jobs_csaid'] = $this -> _post('jobs_csaid', 'intval');
    }


    //先读取一遍，用于确定排除的id
    $id_arr = $RecruitJobsSort -> alias('rjs') -> field('rjs.rjid as id') -> join('yesow_recruit_jobs as rj ON rjs.rjid = rj.id') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> where($where_sort) -> select();

    //再读取其他商家
    $RecruitJobs = M('RecruitJobs');
    //过滤掉推荐中已有的商家
    $del_id_arr = array();
    foreach($id_arr as $value){
      $del_id_arr[] = $value['id'];
    }
    $where = array();
    $where['rj.ischeck'] = 1;
    $where['rj.endtime'] = array('egt', $time);

    if(!empty($del_id_arr)){
      $where['rj.id'] = array('not in', $del_id_arr);
    }
    if(!empty($_POST['name'])){
      $where['rj.name'] = array('LIKE', '%' . $this -> _post('name') . '%');
    }
    if(!empty($_POST['ciid'])){
      $where['rc.ciid'] = $this -> _post('ciid', 'intval');
    }
    if(!empty($_REQUEST['jobs_csid'])){
      $where['rj.jobs_csid'] = $this -> _request('jobs_csid', 'intval');
    }
    if(!empty($_POST['jobs_csaid'])){
      $where['rj.jobs_csaid'] = $this -> _post('jobs_csaid', 'intval');
    }

    import("ORG.Util.Page");// 导入分页类
    //推荐数量
    $sort_count = $RecruitJobsSort -> alias('rjs') -> join('yesow_recruit_jobs as rj ON rjs.rjid = rj.id') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> where($where_sort) -> count();
    //其余数量
    $other_count = $RecruitJobs -> alias('rj') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> where($where) -> count();
    //总数
    $count = $sort_count + $other_count;
    $page = new Page($count, 20);
    $show = $page -> show();
    $this -> assign('show', $show);

    $result_sort = $RecruitJobsSort -> alias('rjs') -> field('rj.id,rj.cid,rj.jobs_csid as csid,rj.name,rc.name as rcname,rj.num,rj.addtime,cs.name as csname,rj.num') -> join('yesow_recruit_jobs as rj ON rjs.rjid = rj.id') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> join('yesow_child_site as cs ON rj.jobs_csid = cs.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($where_sort) -> order('rjs.sort DESC') -> select();
    $this -> assign('result_sort', $result_sort);

    //自定义分页条件
    $limit_row = $page -> firstRow - $sort_count <= 0 ? 0 : $page -> firstRow - $sort_count;
    $limit_lits = count($result_sort) == 0 ? $page -> listRows : $page -> listRows - count($result_sort);

    $result = $RecruitJobs -> alias('rj') -> field('rj.id,rj.cid,rj.jobs_csid as csid,rj.name,rc.name as rcname,rj.num,rj.addtime,cs.name as csname,rj.num') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> join('yesow_child_site as cs ON rj.jobs_csid = cs.id') -> limit($limit_row . ',' . $limit_lits) -> where($where) -> order('rj.addtime DESC') -> select();
    $this -> assign('result', $result);

    //所属行业
    $result_industry = M('RecruitCompanyIndustry') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_industry', $result_industry);
    //查询所有分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('id DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);

    //推荐信息
    $where_recommended['rj.ischeck'] = 1;
    $where_recommended['rjs.starttime'] = array('elt', $time);
    $where_recommended['rjs.endtime'] = array('egt', $time);
    $recommended_result = $RecruitJobsSort -> alias('rjs') -> field('rj.id,rj.name') -> join('yesow_recruit_jobs as rj ON rjs.rjid = rj.id') -> order('rjs.sort DESC') -> where($where_recommended) -> limit(11) -> select();
    $this -> assign('recommended_result', $recommended_result);
    $this -> display();
  }

  //人才招聘详情页
  public function recruitinfo(){
    $RecruitJobs = M('RecruitJobs');
    $RecruitCompany = M('RecruitCompany');
    $RecruitJobsSort = M('RecruitJobsSort');
    $time = time();
    //公司信息
    if($_GET['type'] == 'company'){
      $result = $RecruitCompany -> alias('rc') -> field('rc.name as rcname,cs.name as csname,rc.clickcount,rc.address,rc.tel,rc.linkman,rc.email,rc.website,rci.name as rciname,rcn.name as rcnname,rcr.name as rcrname,rce.name as rcename,rc.qqcode,rc.pic,rc.abstract,csa.name as csaname') -> join('yesow_child_site as cs ON rc.csid = cs.id') -> join('yesow_child_site_area as csa ON rc.csaid = csa.id') -> join('yesow_recruit_company_industry as rci ON rc.ciid = rci.id') -> join('yesow_recruit_company_nature as rcn ON rc.cnid = rcn.id') -> join('yesow_recruit_company_registermoney as rcr ON rc.crid = rcr.id') -> join('yesow_recruit_company_employnum as rce ON rc.ceid = rce.id') -> where(array('rc.id' => $this -> _get('id', 'intval'))) -> find();
    }else{
      $result = $RecruitJobs -> alias('rj') -> field('rj.cid,rc.name as rcname,cs.name as csname,rc.clickcount,rj.addtime,rc.address,rc.tel,rc.linkman,rc.email,rc.website,rci.name as rciname,rcn.name as rcnname,rcr.name as rcrname,rce.name as rcename,rc.qqcode,rc.pic,rc.abstract,csa.name as csaname') -> join('yesow_recruit_company as rc ON rj.cid = rc.id') -> join('yesow_child_site as cs ON rc.csid = cs.id') -> join('yesow_child_site_area as csa ON rc.csaid = csa.id') -> join('yesow_recruit_company_industry as rci ON rc.ciid = rci.id') -> join('yesow_recruit_company_nature as rcn ON rc.cnid = rcn.id') -> join('yesow_recruit_company_registermoney as rcr ON rc.crid = rcr.id') -> join('yesow_recruit_company_employnum as rce ON rc.ceid = rce.id') -> where(array('rj.id' => $this -> _get('id', 'intval'))) -> find();
    }
     
    $this -> assign('result', $result);
    //岗位信息
    if($_GET['type'] == 'all' || $_GET['type'] == 'company'){
      $result_jobs = $RecruitJobs -> alias('rj') -> field('rj.id,rj.name,rj.num,jm.name as jmname,jd.name as jdname,je.name as jename,rj.english,rj.major,rj.sex,rj.age,rj.content,rj.keyword') -> join('yesow_recruit_jobs_monthlypay as jm ON rj.jmid = jm.id') -> join('yesow_recruit_jobs_degree as jd ON rj.jdid = jd.id') -> join('yesow_recruit_jobs_experience as je ON rj.jeid = je.id') -> where(array('rj.cid' => $result['cid'])) -> select();    
    }else{
      $result_jobs = $RecruitJobs -> alias('rj') -> field('rj.id,rj.name,rj.num,jm.name as jmname,jd.name as jdname,je.name as jename,rj.english,rj.major,rj.sex,rj.age,rj.content,rj.keyword') -> join('yesow_recruit_jobs_monthlypay as jm ON rj.jmid = jm.id') -> join('yesow_recruit_jobs_degree as jd ON rj.jdid = jd.id') -> join('yesow_recruit_jobs_experience as je ON rj.jeid = je.id') -> where(array('rj.id' => $this -> _get('id', 'intval'))) -> select();
    }
    $this -> assign('result_jobs', $result_jobs);
    //点击量加1
    $RecruitCompany -> where(array('id' => $result['cid'])) -> setInc('clickcount');
    //推荐信息
    $where_recommended['rj.ischeck'] = 1;
    $where_recommended['rjs.starttime'] = array('elt', $time);
    $where_recommended['rjs.endtime'] = array('egt', $time);
    $recommended_result = $RecruitJobsSort -> alias('rjs') -> field('rj.id,rj.name') -> join('yesow_recruit_jobs as rj ON rjs.rjid = rj.id') -> order('rjs.sort DESC') -> where($where_recommended) -> limit(11) -> select();
    $this -> assign('recommended_result', $recommended_result);
    //相关岗位
    $keyword = $result_jobs[0]['keyword'];
    $keyword_arr = explode(' ', $keyword);
    $where_about = array();
    foreach($keyword_arr as $value){
      if(empty($where_about)){
	$where_about['_string'] .="(( rj.keyword LIKE '%{$value}%' )";
      }else{
	$where_about['_string'] .=" OR ( rj.keyword LIKE '%{$value}%' )";
      }
    }
    $where_about['_string'] .= ") AND (rj.id != {$result_jobs[0]['id']}) AND (rj.ischeck = 1) AND (rj.endtime >= {$time})";
    $result_about = $RecruitJobs -> alias('rj') -> field('rj.id,rj.name,cs.name as csname') -> join('yesow_child_site as cs ON rj.jobs_csid = cs.id') -> order('rj.addtime DESC') -> where($where_about) -> limit(10) -> select();
    $this -> assign('result_about', $result_about);

    $comment = M('RecruitJobsComment');
    //读取评论
    $comment_where = "rjc.rjid={$_GET['id']} AND rjc.status=2";
    //如果会员基本设置允许会员看到自己的未经审核的评论，则在这里加上查询条件
    if(M('MemberSetup') -> getFieldByname('viewcomment', 'value') == 1 && isset($_SESSION[C('USER_AUTH_KEY')])){
      $sid = session(C('USER_AUTH_KEY'));
      $where_setup = "rjc.rjid={$_GET['id']} AND rjc.mid={$sid}";
      $comment_where = '(' . $comment_where . ')' . 'OR' . '(' . $where_setup . ')';
    }
    import("ORG.Util.Page");// 导入分页类
    $count = $comment -> alias('rjc') -> where($comment_where) -> count();
    $page = new Page($count, 10);//每页10条
    $page->setConfig('header','条评论');
    $show = $page -> show();
    $result_comment = $comment -> alias('rjc') -> field('m.name,rjc.content,rjc.addtime,rjc.floor,rjc.face') -> where($comment_where) -> join('yesow_member as m ON rjc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('rjc.floor ASC') -> select();
    $this -> assign('result_comment', $result_comment);
    $this -> assign('show', $show);
   
    $this -> display();
  }

  //人才招聘提交评论
  public function recruitcomment(){
    if($this -> _post('code', 'md5') != $_SESSION['verify']){
      $this -> error(L('VERIFY_ERROR'));
    }
    $commit = D('RecruitJobsComment');
    $data['rjid'] = $this -> _post('rjid', 'intval');
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

  public function add(){
    if(!empty($_POST['pid'])){
      $Resume = M('Resume');
      if(!$Resume -> create()){
	R('Public/errorjump',array($Resume -> getError()));
      }
      if(!empty($_FILES['pic']['name'])){
	$up_data = $this -> resume_pic_upload();
	$Resume -> pic = $up_data[0]['savename'];
      }
      if($id = $Resume -> add()){
	redirect(PHP_FILE . '/hire/resume_two/id/' . $id);
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    $ResumePotion = M('ResumePotion');
    $result_potion = $ResumePotion -> field('id,title') -> select();
    $this -> assign('result_potion', $result_potion);
    $this -> display();
  }

  public function resume_pic_upload(){
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('RESUME_PIC_PATH') ;
    $upload -> autoSub = false;
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info;
    }else{
      return $upload;
    }
  }

  public function resume_two(){
    if(!empty($_POST['id'])){
      $Resume = M('Resume');
      if(!$Resume -> create()){
	R('Public/errorjump',array($Resume -> getError()));
      }
      if($Resume -> save()){
	redirect(PHP_FILE . '/hire/resume_three/id/' . $_POST['id']);
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    $this -> display();
  }

  public function resume_three(){
    if(!empty($_POST['id'])){
      $Resume = M('Resume');
      if(!$Resume -> create()){
	R('Public/errorjump',array($Resume -> getError()));
      }
      $Resume -> addtime = time();
      if($Resume -> save()){
	echo '<script>alert("感谢提交招聘信息,我们会在3个工作日内和您联系!");location.href="' . __ROOT__ . '"</script>';
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    $this -> display();
  }

  public function resumeinfo(){
    $Resume = M('Resume');
    $result = $Resume -> alias('r') -> field('r.*,p.title') -> join('yesow_resume_potion as p ON r.pid = p.id') -> where(array('r.id' => $this -> _get('id', 'intval'))) -> find();
    $this -> assign('result', $result);
    $this -> display();
  }
}

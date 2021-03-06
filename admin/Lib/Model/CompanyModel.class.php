<?php
class CompanyModel extends Model {
  protected $_validate = array(
    array('name', 'require', '{%COMPANY_NAME_EMPTY}'),
    array('address', 'require', '{%COMPANY_ADDRESS_EMPTY}'),
    array('companyphone', 'require', '{%COMPANY_COMPANYPHONE_EMPTY}'),
    array('linkman', 'require', '{%COMPANY_LINKMAN_EMPTY}'),
  );

  protected $_auto = array(
    array('addtime','time',1,'function'), 
    array('updatetime','time',3,'function'), 
  );

  //速查搜索算法
  public function search($keyword){
    //最终输出结果
    $result = array();
    //高级搜索,只检索出按更新时间排序的一页数据(20条)
    if(empty($keyword) || $keyword == '请输入您要搜索的内容'){
      $result['result'] = $this -> field('id,name,csid,csaid,manproducts,address,companyphone,linkman,mobilephone,addtime,updatetime') -> where('delaid is NULL') -> order('updatetime DESC') -> limit(20) -> select();
      $result['count'] = 20;
      return $result;
    }

    //引入 API
    Vendor('sphinxapi');
    $cl = new SphinxClient();
    $cl->SetServer('127.0.0.1', 9312);
    $cl->SetConnectTimeout(1);//超时设置
    $cl->SetArrayResult(true);//返回数组
    //匹配模式
    //$cl->SetMatchMode(SPH_MATCH_EXTENDED2);
    //$cl->SetMatchMode(SPH_MATCH_ANY);
    $cl->SetMatchMode(SPH_MATCH_ALL);//匹配所有查询词

    if(!empty($_GET['csid']) && $_GET['csid'] != 'null'){
      $cl -> SetFilter('csid', array($_GET['csid']));
    }
    if(!empty($_GET['csaid']) && $_GET['csaid'] != 'null'){
      $cl -> SetFilter('csaid', array($_GET['csaid']));
    }
    //判断是否为分站数据
    if($csid = D('admin://ChildSite') -> getid()){
      $cl -> SetFilter('csid', array($csid));
    }

    //Count
    $result_sph_tmp_one = $cl->Query($keyword, "*" );

    //分页
    $count = $result_sph_tmp_one['total_found'];
    import("ORG.Util.Page");
    $page = new Page($count, 20);
    $page->setConfig('header','条数据');
    $page -> setConfig('theme', '%totalRow% %header% %nowPage%/%totalPage% 页 %linkPage% &nbsp;&nbsp;&nbsp;跳转到 %inputpage% <br/><br/> %upPage% %downPage% %first%  %prePage%  %nextPage% %end%');
    $show = $page -> show();
    //分页信息
    $result_sph['show'] = $show;
    //总数
    $result_sph['count'] = $count;
    //当前页数
    $result_sph['pagenow'] = $_GET['p'] ? $_GET['p'] : 1;

    //评分模式
    //$cl -> SetRankingMode(SPH_RANK_WORDCOUNT);
    //$cl -> SetRankingMode(SPH_RANK_PROXIMITY_BM25);
    //$cl -> SetRankingMode(SPH_RANK_PROXIMITY);
    //$cl -> SetRankingMode(SPH_RANK_NONE);//禁用评分模式
    
    //字段权重
    //$cl -> SetFieldWeights(array('csaname' => 500, 'name' => 1, 'manproducts' => 2));
    //排序模式
    //$cl -> SetSortMode(SPH_SORT_EXTENDED, 'csaname DESC');
    //$cl -> SetSortMode(SPH_SORT_RELEVANCE);
    //$cl -> SetSortMode ( SPH_SORT_EXPR, "@weight" );
    //s$cl -> SetSortMode(SPH_SORT_ATTR_DESC);

    //result

    $cl -> SetLimits($page -> firstRow, $page -> listRows, 10000);
    $result_sph_tmp = $cl -> Query($keyword, "*" );

    $result_sph['result'] = array();
    foreach($result_sph_tmp['matches'] as $key => $value){
      $result_sph['result'][$key]['id'] = $value['id'];
      $result_sph['result'][$key]['name'] = $value['attrs']['name'];
      $result_sph['result'][$key]['csid'] = $value['attrs']['csid'];
      $result_sph['result'][$key]['csaid'] = $value['attrs']['csaid'];
      $result_sph['result'][$key]['manproducts'] = $value['attrs']['manproducts'];
      $result_sph['result'][$key]['address'] = $value['attrs']['address'];
      $result_sph['result'][$key]['companyphone'] = $value['attrs']['companyphone'];
      $result_sph['result'][$key]['linkman'] = $value['attrs']['linkman'];
      $result_sph['result'][$key]['mobilephone'] = $value['attrs']['mobilephone'];
      $result_sph['result'][$key]['addtime'] = $value['attrs']['addtime'];
      $result_sph['result'][$key]['updatetime'] = $value['attrs']['updatetime'];
      $result_sph['result'][$key]['clickcount'] = $value['attrs']['clickcount'];
    }

    $result_sph['time'] = $result_sph_tmp['time'];

    //非法词过滤，过滤的字段：公司名称、主营、企业介绍
    $illegal = M('IllegalWord');
    //需要过滤的词的数组
    $illegal_word_temp = $illegal -> field('name') -> order('id') -> select();
    //需要替换的词的数组
    $replace_word_temp = $illegal -> field('replace') -> order('id') -> select();
    //整理数组
    $illegal_word = array();
    $replace_word = array();
    foreach($illegal_word_temp as $key => $value){
      $illegal_word[] = $value['name'];
    }
    foreach($replace_word_temp as $key => $value){
      $replace_word[] = $value['replace'];
    }
    //过滤
    foreach($result_sph['result'] as $key => $value){
      $result_sph['result'][$key]['name'] = str_replace($illegal_word, $replace_word, $result_sph['result'][$key]['name']);
      $result_sph['result'][$key]['manproducts'] = str_replace($illegal_word, $replace_word, $result_sph['result'][$key]['manproducts']);
      $result_sph['result'][$key]['content'] = str_replace($illegal_word, $replace_word, $result_sph['result'][$key]['content']);
    }

    //查询关键词
    $keyword_arr = array();
    foreach($result_sph_tmp['words'] as $key => $value){
      $keyword_arr[] = $key;
    }

    //将主查询词，写入关键词数组头部
    array_unshift($keyword_arr, $keyword);
    //关键词数组
    $result_sph['keyword_arr'] = $keyword_arr;
    //查询主关键词
    $result_sph['keyword'] = $keyword;


    //自动切换
    if(!$cl -> IsConnectError()){
      return $result_sph;
    }

    
    //关键词切割
    $keyword_explode =  mbstringtoarray($keyword, 'UTF-8');
    //初始化未过滤关键词词组
    $keyword_noaudit = array();
    //重组未过滤关键词词组
    foreach($keyword_explode as $key => $value){
      foreach($keyword_explode as $key_two => $value_two){
	if($key < $key_two){
	  if(!empty($temp_string)){
	    $keyword_noaudit[] = $temp_string . $value_two;
	    $temp_string = $temp_string . $value_two;
	  }else{
	    $keyword_noaudit[] = $value . $value_two;
	    $temp_string = $value . $value_two;
	  }	  	  
	}else{
	  $temp_string = '';
	}
      }
    }
    //初始化关键词数组
    $keyword_arr = array();
    //关键词词组过滤
    foreach($keyword_noaudit as $key => $value){
      if($aid = M('AuditSearchKeyword') -> getFieldByname($value, 'aid')){
	$keyword_arr[] = $value;
	//地名关键词标记
	if($aid == 7){
	  $place_keyword = $value;
	}
      }  
    }

    //关键字排序
    if(!function_exists('keyword_sort')){
      function keyword_sort($a, $b){
	(int)$sort_a = M() -> table('yesow_audit_search_keyword as ask') -> field('aska.sort') -> join('yesow_audit_search_keyword_attribute as aska ON ask.aid = aska.id') -> where(array('ask.name' => $a)) -> find();
	(int)$sort_b = M() -> table('yesow_audit_search_keyword as ask') -> field('aska.sort') -> join('yesow_audit_search_keyword_attribute as aska ON ask.aid = aska.id') -> where(array('ask.name' => $b)) -> find();
	if($sort_a['sort'] == $sort_b['sort']){
	  return 0;
	}
	return $sort_a['sort'] < $sort_b['sort'] ? -1 : 1;
      }
    }
    
    //生成排序后数组
    usort($keyword_arr, 'keyword_sort');

    //生成关键词SQL
    $keyword_sql = array();
    foreach($keyword_arr as $value){
      $keyword_sql[] = "(SELECT id,name,csid,csaid,manproducts,address,companyphone,linkman,mobilephone,addtime,updatetime,clickcount FROM yesow_company WHERE ( name LIKE '%{$value}%' OR address LIKE '%{$value}%' OR manproducts LIKE '%{$value}%' OR linkman LIKE '%{$value}%' ) AND ( delaid is NULL ) ORDER BY updatetime DESC)";
    }

    //生成主SQL
    $where_select = array();
    $where_select['name'] = array('LIKE', '%' . $keyword . '%');
    $where_select['address'] = array('LIKE', '%' . $keyword . '%');
    $where_select['manproducts'] = array('LIKE', '%' . $keyword . '%');
    $where_select['mobilephone'] = array('LIKE', '%' . $keyword . '%');
    $where_select['email'] = array('LIKE', '%' . $keyword . '%');
    $where_select['linkman'] = array('LIKE', '%' . $keyword . '%');
    $where_select['companyphone'] = array('LIKE', '%' . $keyword . '%');
    $where_select['qqcode'] = array('LIKE', '%' . $keyword . '%');
    $where_select['website'] = array('LIKE', '%' . $keyword . '%');
    $where_select['_logic'] = 'OR';
    $map['_complex'] = $where_select;
    $map['delaid']  = array('exp', 'is NULL');

    //组合SQL
    $sql = $this -> field('id,name,csid,csaid,manproducts,address,companyphone,linkman,mobilephone,addtime,updatetime,clickcount') -> where($map) -> union($keyword_sql) -> buildSql();
    //按更新时间排序
    //$sql = $this -> table($sql . ' tmpt') -> order('updatetime DESC') -> buildSql();

    //高级查询条件
    $senior_where = array();
    if(!empty($_GET['type']) && $_GET['type'] != 'null'){
      $senior_where[$_GET['type']] = array('LIKE', '%' . $keyword . '%');
    }
    if(!empty($_GET['csid']) && $_GET['csid'] != 'null'){
      $senior_where['csid'] = intval($_GET['csid']);
    }
    if(!empty($_GET['csaid']) && $_GET['csaid'] != 'null'){
      $senior_where['csaid'] = intval($_GET['csaid']);
    }
    //判断是否为分站数据
    if($csid = D('admin://ChildSite') -> getid()){
      $senior_where['csid'] = $csid;
    }

    //分页
    $count = $this -> table($sql . ' a') -> where($senior_where) -> count();
    import("ORG.Util.Page");
    $page = new Page($count, 20);
    $page->setConfig('header','条数据');
    $page -> setConfig('theme', '%totalRow% %header% %nowPage%/%totalPage% 页 %linkPage% &nbsp;&nbsp;&nbsp;跳转到 %inputpage% <br/><br/> %upPage% %downPage% %first%  %prePage%  %nextPage% %end%');
    $show = $page -> show();
    //分页信息
    $result['show'] = $show;
    //总数
    $result['count'] = $count;
    //当前页数
    $result['pagenow'] = $_GET['p'] ? $_GET['p'] : 1;

    //将存在地名关键词的数据排到首位
    if($place_keyword){
      $order = 'name LIKE "%' . $place_keyword . '%" OR address LIKE "%' . $place_keyword . '%" OR manproducts LIKE  "%' . $place_keyword . '%" DESC';
    }
    //查询
    G('start');
    $result['result'] = $this -> table($sql . ' a') -> order($order) -> limit($page -> firstRow . ',' . $page -> listRows) -> where($senior_where) -> select();

    //查询时间
    $result['time'] = G('start', 'end');

    //非法词过滤，过滤的字段：公司名称、主营、企业介绍
    $illegal = M('IllegalWord');
    //需要过滤的词的数组
    $illegal_word_temp = $illegal -> field('name') -> order('id') -> select();
    //需要替换的词的数组
    $replace_word_temp = $illegal -> field('replace') -> order('id') -> select();
    //整理数组
    $illegal_word = array();
    $replace_word = array();
    foreach($illegal_word_temp as $key => $value){
      $illegal_word[] = $value['name'];
    }
    foreach($replace_word_temp as $key => $value){
      $replace_word[] = $value['replace'];
    }
    //过滤
    foreach($result['result'] as $key => $value){
      $result['result'][$key]['name'] = str_replace($illegal_word, $replace_word, $result['result'][$key]['name']);
      $result['result'][$key]['manproducts'] = str_replace($illegal_word, $replace_word, $result['result'][$key]['manproducts']);
      $result['result'][$key]['content'] = str_replace($illegal_word, $replace_word, $result['result'][$key]['content']);
    }

    //将主查询词，写入关键词数组头部
    array_unshift($keyword_arr, $keyword);
    //关键词数组
    $result['keyword_arr'] = $keyword_arr;
    //查询主关键词
    $result['keyword'] = $keyword;
    //调试信息
    $result['lastsql'] = $this -> getLastSql();

    return $result;
  
  }



   //速查搜索算法_无分页
  public function search2($keyword){
    //最终输出结果
    $result = array();

    //引入 API
    Vendor('sphinxapi');
    $cl = new SphinxClient();
    $cl->SetServer('127.0.0.1', 9312);
    $cl->SetConnectTimeout(1);//超时设置
    $cl->SetArrayResult(true);//返回数组
    //匹配模式
    //$cl->SetMatchMode(SPH_MATCH_EXTENDED2);
    //$cl->SetMatchMode(SPH_MATCH_ANY);
    $cl->SetMatchMode(SPH_MATCH_ALL);//匹配所有查询词

    if(!empty($_GET['csid']) && $_GET['csid'] != 'null'){
      $cl -> SetFilter('csid', array($_GET['csid']));
    }
    if(!empty($_GET['csaid']) && $_GET['csaid'] != 'null'){
      $cl -> SetFilter('csaid', array($_GET['csaid']));
    }
    //判断是否为分站数据
    if($csid = D('admin://ChildSite') -> getid()){
      $cl -> SetFilter('csid', array($csid));
    }

    //Count
    $result_sph_tmp_one = $cl->Query($keyword, "*" );

    //分页
    $count = $result_sph_tmp_one['total_found'];
    //import("ORG.Util.Page");
    //$page = new Page($count, 20);
    //$page->setConfig('header','条数据');
    //$page -> setConfig('theme', '%totalRow% %header% %nowPage%/%totalPage% 页 %linkPage% &nbsp;&nbsp;&nbsp;跳转到 %inputpage% <br/><br/> %upPage% %downPage% %first%  %prePage%  %nextPage% %end%');
    //$show = $page -> show();
    //分页信息
    //$result_sph['show'] = $show;
    //总数
    $result_sph['count'] = $count;
    //当前页数
    //$result_sph['pagenow'] = $_GET['p'] ? $_GET['p'] : 1;

    //评分模式
    //$cl -> SetRankingMode(SPH_RANK_WORDCOUNT);
    //$cl -> SetRankingMode(SPH_RANK_PROXIMITY_BM25);
    //$cl -> SetRankingMode(SPH_RANK_PROXIMITY);
    //$cl -> SetRankingMode(SPH_RANK_NONE);//禁用评分模式
    
    //字段权重
    //$cl -> SetFieldWeights(array('csaname' => 500, 'name' => 1, 'manproducts' => 2));
    //排序模式
    //$cl -> SetSortMode(SPH_SORT_EXTENDED, 'csaname DESC');
    //$cl -> SetSortMode(SPH_SORT_RELEVANCE);
    //$cl -> SetSortMode ( SPH_SORT_EXPR, "@weight" );
    //s$cl -> SetSortMode(SPH_SORT_ATTR_DESC);

    //result

    $cl -> SetLimits(0, $count, 10000);
    $result_sph_tmp = $cl -> Query($keyword, "*" );

    $result_sph['result'] = array();
    foreach($result_sph_tmp['matches'] as $key => $value){
      $result_sph['result'][$key]['id'] = $value['id'];
      $result_sph['result'][$key]['name'] = $value['attrs']['name'];
      $result_sph['result'][$key]['csid'] = $value['attrs']['csid'];
      $result_sph['result'][$key]['csaid'] = $value['attrs']['csaid'];
      $result_sph['result'][$key]['manproducts'] = $value['attrs']['manproducts'];
      $result_sph['result'][$key]['address'] = $value['attrs']['address'];
      $result_sph['result'][$key]['companyphone'] = $value['attrs']['companyphone'];
      $result_sph['result'][$key]['linkman'] = $value['attrs']['linkman'];
      $result_sph['result'][$key]['mobilephone'] = $value['attrs']['mobilephone'];
      $result_sph['result'][$key]['addtime'] = $value['attrs']['addtime'];
      $result_sph['result'][$key]['updatetime'] = $value['attrs']['updatetime'];
      $result_sph['result'][$key]['clickcount'] = $value['attrs']['clickcount'];
    }

    $result_sph['time'] = $result_sph_tmp['time'];

    //非法词过滤，过滤的字段：公司名称、主营、企业介绍
    $illegal = M('IllegalWord');
    //需要过滤的词的数组
    $illegal_word_temp = $illegal -> field('name') -> order('id') -> select();
    //需要替换的词的数组
    $replace_word_temp = $illegal -> field('replace') -> order('id') -> select();
    //整理数组
    $illegal_word = array();
    $replace_word = array();
    foreach($illegal_word_temp as $key => $value){
      $illegal_word[] = $value['name'];
    }
    foreach($replace_word_temp as $key => $value){
      $replace_word[] = $value['replace'];
    }
    //过滤
    foreach($result_sph['result'] as $key => $value){
      $result_sph['result'][$key]['name'] = str_replace($illegal_word, $replace_word, $result_sph['result'][$key]['name']);
      $result_sph['result'][$key]['manproducts'] = str_replace($illegal_word, $replace_word, $result_sph['result'][$key]['manproducts']);
      $result_sph['result'][$key]['content'] = str_replace($illegal_word, $replace_word, $result_sph['result'][$key]['content']);
    }

    //查询关键词
    $keyword_arr = array();
    foreach($result_sph_tmp['words'] as $key => $value){
      $keyword_arr[] = $key;
    }

    //将主查询词，写入关键词数组头部
    array_unshift($keyword_arr, $keyword);
    //关键词数组
    $result_sph['keyword_arr'] = $keyword_arr;
    //查询主关键词
    $result_sph['keyword'] = $keyword;


    //自动切换
    if(!$cl -> IsConnectError()){
      return $result_sph;
    }

    
    //关键词切割
    $keyword_explode =  mbstringtoarray($keyword, 'UTF-8');
    //初始化未过滤关键词词组
    $keyword_noaudit = array();
    //重组未过滤关键词词组
    foreach($keyword_explode as $key => $value){
      foreach($keyword_explode as $key_two => $value_two){
	if($key < $key_two){
	  if(!empty($temp_string)){
	    $keyword_noaudit[] = $temp_string . $value_two;
	    $temp_string = $temp_string . $value_two;
	  }else{
	    $keyword_noaudit[] = $value . $value_two;
	    $temp_string = $value . $value_two;
	  }	  	  
	}else{
	  $temp_string = '';
	}
      }
    }
    //初始化关键词数组
    $keyword_arr = array();
    //关键词词组过滤
    foreach($keyword_noaudit as $key => $value){
      if($aid = M('AuditSearchKeyword') -> getFieldByname($value, 'aid')){
	$keyword_arr[] = $value;
	//地名关键词标记
	if($aid == 7){
	  $place_keyword = $value;
	}
      }  
    }

    //关键字排序
    if(!function_exists('keyword_sort')){
      function keyword_sort($a, $b){
	(int)$sort_a = M() -> table('yesow_audit_search_keyword as ask') -> field('aska.sort') -> join('yesow_audit_search_keyword_attribute as aska ON ask.aid = aska.id') -> where(array('ask.name' => $a)) -> find();
	(int)$sort_b = M() -> table('yesow_audit_search_keyword as ask') -> field('aska.sort') -> join('yesow_audit_search_keyword_attribute as aska ON ask.aid = aska.id') -> where(array('ask.name' => $b)) -> find();
	if($sort_a['sort'] == $sort_b['sort']){
	  return 0;
	}
	return $sort_a['sort'] < $sort_b['sort'] ? -1 : 1;
      }
    }
    
    //生成排序后数组
    usort($keyword_arr, 'keyword_sort');

    //生成关键词SQL
    $keyword_sql = array();
    foreach($keyword_arr as $value){
      $keyword_sql[] = "(SELECT id,name,csid,csaid,manproducts,address,companyphone,linkman,mobilephone,addtime,updatetime,clickcount FROM yesow_company WHERE ( name LIKE '%{$value}%' OR address LIKE '%{$value}%' OR manproducts LIKE '%{$value}%' OR linkman LIKE '%{$value}%' ) AND ( delaid is NULL ) ORDER BY updatetime DESC)";
    }

    //生成主SQL
    $where_select = array();
    $where_select['name'] = array('LIKE', '%' . $keyword . '%');
    $where_select['address'] = array('LIKE', '%' . $keyword . '%');
    $where_select['manproducts'] = array('LIKE', '%' . $keyword . '%');
    $where_select['mobilephone'] = array('LIKE', '%' . $keyword . '%');
    $where_select['email'] = array('LIKE', '%' . $keyword . '%');
    $where_select['linkman'] = array('LIKE', '%' . $keyword . '%');
    $where_select['companyphone'] = array('LIKE', '%' . $keyword . '%');
    $where_select['qqcode'] = array('LIKE', '%' . $keyword . '%');
    $where_select['website'] = array('LIKE', '%' . $keyword . '%');
    $where_select['_logic'] = 'OR';
    $map['_complex'] = $where_select;
    $map['delaid']  = array('exp', 'is NULL');

    //组合SQL
    $sql = $this -> field('id,name,csid,csaid,manproducts,address,companyphone,linkman,mobilephone,addtime,updatetime,clickcount') -> where($map) -> union($keyword_sql) -> buildSql();
    //按更新时间排序
    //$sql = $this -> table($sql . ' tmpt') -> order('updatetime DESC') -> buildSql();

    //高级查询条件
    $senior_where = array();
    if(!empty($_GET['type']) && $_GET['type'] != 'null'){
      $senior_where[$_GET['type']] = array('LIKE', '%' . $keyword . '%');
    }
    if(!empty($_GET['csid']) && $_GET['csid'] != 'null'){
      $senior_where['csid'] = intval($_GET['csid']);
    }
    if(!empty($_GET['csaid']) && $_GET['csaid'] != 'null'){
      $senior_where['csaid'] = intval($_GET['csaid']);
    }
    //判断是否为分站数据
    if($csid = D('admin://ChildSite') -> getid()){
      $senior_where['csid'] = $csid;
    }

    //分页
    //$count = $this -> table($sql . ' a') -> where($senior_where) -> count();
    //import("ORG.Util.Page");
    //$page = new Page($count, 20);
    //$page->setConfig('header','条数据');
    //$page -> setConfig('theme', '%totalRow% %header% %nowPage%/%totalPage% 页 %linkPage% &nbsp;&nbsp;&nbsp;跳转到 %inputpage% <br/><br/> %upPage% %downPage% %first%  %prePage%  %nextPage% %end%');
    //$show = $page -> show();
    //分页信息
    //$result['show'] = $show;
    //总数
    //$result['count'] = $count;
    //当前页数
    //$result['pagenow'] = $_GET['p'] ? $_GET['p'] : 1;

    //将存在地名关键词的数据排到首位
    if($place_keyword){
      $order = 'name LIKE "%' . $place_keyword . '%" OR address LIKE "%' . $place_keyword . '%" OR manproducts LIKE  "%' . $place_keyword . '%" DESC';
    }
    //查询
    G('start');
    $result['result'] = $this -> table($sql . ' a') -> order($order) -> where($senior_where) -> select();

    //查询时间
    $result['time'] = G('start', 'end');

    //非法词过滤，过滤的字段：公司名称、主营、企业介绍
    $illegal = M('IllegalWord');
    //需要过滤的词的数组
    $illegal_word_temp = $illegal -> field('name') -> order('id') -> select();
    //需要替换的词的数组
    $replace_word_temp = $illegal -> field('replace') -> order('id') -> select();
    //整理数组
    $illegal_word = array();
    $replace_word = array();
    foreach($illegal_word_temp as $key => $value){
      $illegal_word[] = $value['name'];
    }
    foreach($replace_word_temp as $key => $value){
      $replace_word[] = $value['replace'];
    }
    //过滤
    foreach($result['result'] as $key => $value){
      $result['result'][$key]['name'] = str_replace($illegal_word, $replace_word, $result['result'][$key]['name']);
      $result['result'][$key]['manproducts'] = str_replace($illegal_word, $replace_word, $result['result'][$key]['manproducts']);
      $result['result'][$key]['content'] = str_replace($illegal_word, $replace_word, $result['result'][$key]['content']);
    }

    //将主查询词，写入关键词数组头部
    array_unshift($keyword_arr, $keyword);
    //关键词数组
    $result['keyword_arr'] = $keyword_arr;
    //查询主关键词
    $result['keyword'] = $keyword;
    //调试信息
    $result['lastsql'] = $this -> getLastSql();

    return $result;
  
  }


  
}

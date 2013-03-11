<?php
//渠道黄页
class CompanyAction extends CommonAction {

  public function upload(){//文件上传
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('COMPANY_PIC_PATH') ;//设置上传目录
    $upload -> autoSub = false;//设置使用子目录保存上传文件
    $upload -> saveRule = 'uniqid';
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info[0]['savename'];
    }else{
      return $upload;
    }
  }

  //渠道黄页首页
  public function index(){
    $this -> display();
  }

  //添加速查信息
  public function add(){
    //处理添加
    if(!empty($_POST['companyname'])){
      if($this -> _post('verify', 'md5') != $_SESSION['verify']){
	echo '<script>alert("验证码错误");history.go(-1);</script>';
	//R('Public/errorjump',array(L('VERIFY_ERROR')));
      }
      $companyaudit = D('admin://CompanyAudit');
      if(!$a = $companyaudit -> create()){
	R('Public/errorjump',array($companyaudit -> getError()));
      }
      if(isset($_SESSION[C('USER_AUTH_KEY')])){
	$companyaudit -> mid = session(C('USER_AUTH_KEY'));
      }
      $companyaudit -> type = '添加';
      if(!empty($_FILES['pic']['name'])){
	$companyaudit -> pic = $this -> upload();
      }
      if($companyaudit -> add()){
	echo '<script>alert("感谢您对易搜的支持！您所提交的数据我们将在36小时内给予审核后通过！多谢您的合作！");</script>';
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询公司类型
    $result_company_type = M('CompanyType') -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('result_company_type', $result_company_type);
    //查询主营类别 - 二级
    $result_company_category_two = M('CompanyCategory') -> table('yesow_company_category as cc') -> field('cc.id,cc.name,cct.name as cctname') -> where(array('cc.pid' => array('neq', 0))) -> join('yesow_company_category as cct ON cc.pid = cct.id') -> order('cct.sort ASC,cc.sort ASC') -> select();

    $this -> assign('result_company_category_two', $result_company_category_two);
    //如果会员已登录，则查出此会员的个人信息
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      $add_info = M('Member') -> field('name,tel,unit,address,email,idnumber') -> find(session(C('USER_AUTH_KEY')));
      $this -> assign('add_info', $add_info);
    }
    $this -> display();
  }

  //速查详情页
  public function info(){
    $company = M('Company');
    $comment = M('CompanyComment');
    $id = $this -> _get('id', 'intval');
    //点击量加一
    $company -> where(array('id' => $id)) -> setInc('clickcount');
    //结果
    $result = $company -> table('yesow_company as c') -> field('c.name,c.clickcount,c.pic,ct.name as ctname,cs.name as csname,csa.name as csaname,c.ccid,cc.name as ccname,c.manproducts,c.linkman,c.address,c.content,c.mobilephone,c.companyphone,c.qqcode,c.email,c.website,c.updatetime,c.addtime') -> where(array('c.id' => $id)) -> join('yesow_company_type as ct ON c.typeid = ct.id') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> find();
    //单独读取一下主营类别的一级分类，因为数据库中没有记录
    $one_pid = M('CompanyCategory') -> getFieldByid($result['ccid'], 'pid');
    $result['one_ccname'] = M('CompanyCategory') -> getFieldByid($one_pid, 'name');
    //是否有查看权
    $member_company = M('MemberCompany');
    $where = array();
    $where['mid'] = session(C('USER_AUTH_KEY'));
    $where['cid'] = $id;
    //查询设置的有效时间
    $viewtime = M('CompanySetup') -> getFieldByname('viewtime', 'value');
    $this -> assign('viewtime', $viewtime);
    //计算最早购买时间,大于这个购买时间的都有效
    $time = mktime() - $viewtime*60*60;
    $where['time'] = array('EGT', $time);
    //如果未查询到数据，则隐藏数据内容
    if(!$member_company -> where($where) -> find()){
      $result['mobilephone'] = substr_replace($result['mobilephone'], '********', 3);
      $result['companyphone'] = substr_replace($result['companyphone'], '********', 8);
      $result['qqcode'] = substr_replace($result['qqcode'], '*****', 4);
      $result['email'] = substr_replace($result['email'], '*****', 0, strpos($result['email'], '@'));
      $result['website'] = preg_replace('/\..*?\./i', '.*****.', $result['website']);
      //是否有查看权
      $this -> assign('isview', 0);
    //否则 根据会员等级 将没有权限的信息隐藏
    }else{
      $level = M('MemberLevel');
      //获取权限位
      $author = $level -> field('author_one,author_two,author_three,author_four,author_five') -> find(session('member_level_id'));
      //公司电话
      if($author['author_one'] == 0){
	$result['companyphone'] = substr_replace($result['companyphone'], '********', 8);
      }
      //手机
      if($author['author_two'] == 0){
	$result['mobilephone'] = substr_replace($result['mobilephone'], '********', 3);
      }
      //QQ
      if($author['author_three'] == 0){
	$result['qqcode'] = substr_replace($result['qqcode'], '*****', 4);
      }
      //邮件
      if($author['author_four'] == 0){
	$result['email'] = substr_replace($result['email'], '*****', 0, strpos($result['email'], '@'));
      }
      //网址
      if($author['author_five'] == 0){
	$result['website'] = preg_replace('/\..*?\./i', '.*****.', $result['website']);
      }
      //是否有查看权
      $this -> assign('isview', 1);
    }
    //如果没有登录，加一张提示图片到后面
    if(!isset($_SESSION[C('USER_AUTH_KEY')])){
	$result['companyphone'] .= ' <img src="__PUBLIC__/index/style/images/dd.gif" />';
	$result['mobilephone'] .= ' <img src="__PUBLIC__/index/style/images/dd.gif" />';
	$result['qqcode'] .= ' <img src="__PUBLIC__/index/style/images/dd.gif" />';
	$result['email'] .= ' <img src="__PUBLIC__/index/style/images/dd.gif" />';
	$result['website'] .= ' <img src="__PUBLIC__/index/style/images/dd.gif" />';

    }
    $this -> assign('result', $result);
    //最新更新同行
    $result_counterparts = $company -> table('yesow_company as c') -> field('c.id,c.name,c.updatetime,cs.name as csname') -> where(array('c.ccid' => $result['ccid'])) -> limit(15) -> order('c.updatetime DESC') -> join('yesow_child_site as cs ON c.csid = cs.id') -> select();
    $this -> assign('result_counterparts', $result_counterparts);
    //读取评论
    $comment_where = "cc.cid={$id} AND cc.status=2";
    //如果会员基本设置允许会员看到自己的未经审核的评论，则在这里加上查询条件
    if(M('MemberSetup') -> getFieldByname('viewcomment', 'value') == 1 && isset($_SESSION[C('USER_AUTH_KEY')])){
      $sid = session(C('USER_AUTH_KEY'));
      $where_setup = "cc.cid={$id} AND cc.mid={$sid}";
      $comment_where = '(' . $comment_where . ')' . 'OR' . '(' . $where_setup . ')';
    }
    import("ORG.Util.Page");// 导入分页类
    $count = $comment -> table('yesow_company_comment as cc') -> where($comment_where) -> count('id');
    $page = new Page($count, 10);//每页10条
    $page->setConfig('header','条评论');
    $show = $page -> show();
    $result_comment = $comment -> table('yesow_company_comment as cc') -> field('m.name,cc.content,cc.addtime,cc.floor,cc.score') -> where($comment_where) -> join('yesow_member as m ON cc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('floor ASC') -> select();
    $this -> assign('result_comment', $result_comment);
    $this -> assign('show', $show);

    $this -> display();
  }

  //报错页
  public function report(){
    //报错需要登录
    if(!isset($_SESSION[C('USER_AUTH_KEY')])){
      R('Public/errorjump',array(L('COMPANY_REPORT_LOGIN'), '__ROOT__/member.php/public/login/a_c/company/m_d/report/id/' . $this -> _get('id', 'intval')));
    }
    //处理更新
    if(!empty($_POST['submit'])){
      if($_SESSION['verify'] != $this -> _post('verify', 'md5')){
	R('Public/errorjump',array(L('VERIFY_ERROR')));
      }
      $report = D('CompanyReport');
      if(!$a = $report -> create()){
	R('Public/errorjump',array($report -> getError()));
      }
      $report -> mid = $_SESSION[C('USER_AUTH_KEY')];
      if($report -> add()){
	R('Public/infojump',array(L('COMPANY_ADD_SUCCESS')));
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
    $company = M('Company');
    //企业信息
    $result = $company -> field('name,address,linkman') -> find($this -> _get('id', 'intval'));
    $this -> assign('result', $result);
    //错误类型
    $result_errortype = M('CompanyErrorType') -> field('id,name') -> select();
    $this -> assign('result_errortype', $result_errortype);
    $this -> display();
  }

  //改错页
  public function change(){
     $company = M('Company');
    //处理提交
    if(!empty($_POST['submit'])){
      if($_SESSION['verify'] != $this -> _post('verify', 'md5')){
	R('Public/errorjump',array(L('VERIFY_ERROR')));
      }
      $cid = $this -> _post('cid', 'intval');
      //如果存在*号，则认为数据没改变
      if(false !== strpos($_POST['mobilephone'], '*')){
	$_POST['mobilephone'] = $company -> getFieldByid($cid, 'mobilephone');
      }
      if(false !== strpos($_POST['companyphone'], '*')){
	$_POST['companyphone'] = $company -> getFieldByid($cid, 'companyphone');
      }
      if(false !== strpos($_POST['qqcode'], '*')){
	$_POST['qqcode'] = $company -> getFieldByid($cid, 'qqcode');
      }
      if(false !== strpos($_POST['email'], '*')){
	$_POST['email'] = $company -> getFieldByid($cid, 'email');
      }
      if(false !== strpos($_POST['companywebsite'], '*')){
	$_POST['companywebsite'] = $company -> getFieldByid($cid, 'website');;
      }
      $companyaudit = D('admin://CompanyAudit');
      if(!$a = $companyaudit -> create()){
	R('Public/errorjump',array($companyaudit -> getError()));
      }
      if(isset($_SESSION[C('USER_AUTH_KEY')])){
	$companyaudit -> mid = session(C('USER_AUTH_KEY'));
      }
      $companyaudit -> type = '改错';
      if(!empty($_FILES['pic']['name'])){
	$companyaudit -> pic = $this -> upload();
      }
      if($companyaudit -> add()){
	R('Public/infojump',array(L('COMPANY_ADD_SUCCESS'), '__ROOT__/company/' . $cid));
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
   
    //结果
    $result = $company -> field('name,address,typeid,csid,csaid,linkman,mobilephone,companyphone,qqcode,email,website,manproducts,ccid,keyword,content') -> find($this -> _get('id', 'intval'));
    //是否有查看权
    $member_company = M('MemberCompany');
    $where = array();
    $where['mid'] = session(C('USER_AUTH_KEY'));
    $where['cid'] = $this -> _get('id', 'intval');
    //计算最早购买时间,大于这个购买时间的都有效
    $time = mktime() - 86400;
    $where['time'] = array('EGT', $time);
    //如果未查询到数据，则隐藏数据内容
    if(!$member_company -> where($where) -> find()){
      $result['mobilephone'] = substr_replace($result['mobilephone'], '********', 3);
      $result['qqcode'] = '********';
      $result['email'] = substr_replace($result['email'], '*****', 0, strpos($result['email'], '@'));
      $result['website'] = substr_replace($result['website'], '*******', 7);
      $result['companyphone'] = substr_replace($result['companyphone'], '*******', 5);
    }
    $this -> assign('result', $result);
    //查询公司类型
    $result_company_type = M('CompanyType') -> field('id,name') -> select();
    $this -> assign('result_company_type', $result_company_type);
    //根据csid 查分站下地区列表
    $result_childsite_area = M('ChildSiteArea') -> field('id,name') -> where(array('csid' => $result['csid'])) -> select();
    $this -> assign('result_childsite_area', $result_childsite_area);
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //查询ccid对应的主营一级类别
    $result_ccid_one = M('CompanyCategory') -> getFieldByid($result['ccid'], 'pid');
    $this -> assign('result_ccid_one', $result_ccid_one);
    //查询对应主营类别 - 二级
    $result_company_category_two = M('CompanyCategory') -> field('id,name') -> where(array('pid' => $result_ccid_one)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_two', $result_company_category_two);
    //查询主营类别 - 一级
    $result_company_category_one = M('CompanyCategory') -> field('id,name') -> where(array('pid' => 0)) -> order('sort ASC') -> select();
    $this -> assign('result_company_category_one', $result_company_category_one);
    //如果会员已登录，则查出此会员的个人信息
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      $add_info = M('Member') -> field('fullname,tel,unit,address,email,idnumber') -> find(session(C('USER_AUTH_KEY')));
      $this -> assign('add_info', $add_info);
    }
    $this -> display();
  }

  //提交评论
  public function commit(){
    if($this -> _post('code', 'md5') != $_SESSION['verify']){
      $this -> error(L('VERIFY_ERROR'));
    }
    $commit = D('CompanyComment');
    $data['cid'] = $this -> _post('cid', 'intval');
    $data['mid'] = isset($_SESSION[C('USER_AUTH_KEY')]) ? $_SESSION[C('USER_AUTH_KEY')] : NULL;
    $data['content'] = $this -> _post('content');
    $data['score'] = $this -> _post('score');
    if(!$commit -> create($data)){
      $this -> error($commit -> getError());
    }
    if($commit -> add()){
      $this -> success(L('ARTICLE_COMMIT_ADD_SUCCESS'));
    }else{
      $this -> error(L('ARTICLE_COMMIT_ADD_ERROR'));
    }
  }

  //搜索
  public function search(){
    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    //高级搜索,只检索出按更新时间排序的一页数据
    if(empty($_GET['keyword']) || $_GET['keyword'] == '请输入您要搜索的内容'){
      $result = M('Company') -> field('id,name,csid,csaid,manproducts,address,companyphone,linkman,mobilephone,addtime,updatetime') -> order('updatetime DESC') -> limit(10) -> select();
      $this -> assign('result', $result);
      $this -> display();
      exit();
    }
    $auditkeyword = M('AuditSearchKeyword');
    $company = M('Company');
    $keyword = $this -> _get('keyword', 'trim');
    $keyword = iconv('UTF-8', 'GB2312', $keyword);//转化编码
    //引入dede分词类库，进行搜索词分词操作
    Vendor('lib_splitword_full');
    $sp = new SplitWord();
    $keyword_str = $sp->SplitRMM($keyword);
    $keyword_str = iconv('GB2312', 'UTF-8', $keyword_str);//再转回来
    $keyword = iconv('GB2312', 'UTF-8', $keyword);//再转回来
    $keyword_arr = explode(' ', $keyword_str);
    $sp->Clear();
    //删除关键字数组最后一个无效空元素
    unset($keyword_arr[count($keyword_arr) - 1]);
    //复制分词数组，用于后面记录搜索关键词
    $search_keyword_arr = $keyword_arr;
    //关键词过滤，剔除没有的关键词
    foreach($keyword_arr as $key => $value){
      if(!$auditkeyword -> getFieldByname($value, 'id')){
	unset($keyword_arr[$key]);      
      }    
    }
    //进行分词后关键词的SQL组装
    $keyword_sql = array();
    foreach($keyword_arr as $value){
      $keyword_sql[] = "SELECT id,name,csid,csaid,manproducts,address,companyphone,linkman,mobilephone,addtime,updatetime FROM yesow_company WHERE ( name LIKE '%{$value}%' OR address LIKE '%{$value}%' OR manproducts LIKE '%{$value}%' OR linkman LIKE '%{$value}%' ) AND ( delaid is NULL )";
    }

    //根据组装好的各SQL语句，结合主SQL语句，进行查询
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
    $map['delaid']  = array('exp','is NULL');
    

    //构建查询SQL
    $sql = $company -> field('id,name,csid,csaid,manproducts,address,companyphone,linkman,mobilephone,addtime,updatetime') -> where($map) -> union($keyword_sql) -> buildSql();

    //高级查询条件
    $senior_where = array();
    if(!empty($_GET['type'])){
      $senior_where[$this -> _get('type')] = array('LIKE', '%' . $keyword . '%');
    }
    if(!empty($_GET['csid'])){
      $senior_where['csid'] = $this -> _get('csid', 'intval');
    }
    if(!empty($_GET['csaid'])){
      $senior_where['csaid'] = $this -> _get('csaid', 'intval');
    }

    import("ORG.Util.Page");// 导入分页类
    $count = $company -> table($sql . ' a') -> where($senior_where) -> count('id');
    $page = new Page($count, 20);//每页10条
    $page->setConfig('header','条数据');
    $show = $page -> show();
    $this -> assign('count', $count);
    $this -> assign('show', $show);

    //记录查询时间
    G('start');

    //查询结果
    $result = $company -> table($sql . ' a') -> limit($page -> firstRow . ',' . $page -> listRows) -> where($senior_where) -> select();
    //获得查询时间
    $this -> assign('time', G('start', 'end'));

    //将主查询词，写入关键词数组头部
    array_unshift($keyword_arr, $keyword);
    //制作复制替换数组
    $replace_arr = array();
    foreach($keyword_arr as $value){
      $replace_arr[] = '<font color="#FF0000"><b>' . $value . '</b></font>';
    }
    //执行关键字高亮替换
    foreach($result as $key => $value){
      $result[$key] = str_replace($keyword_arr, $replace_arr, $result[$key]);
    }
    $this -> assign('result', $result);

    //记录搜索词及搜索信息
    $search_keyword = D('SearchKeyword');
    $search_data = array();
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      $search_data['mid'] = session(C('USER_AUTH_KEY'));
    }
    $search_data['sourceaddress'] = $_SERVER["HTTP_REFERER"];
    $search_data['keyword'] = $keyword_str;
    //如果这个词已经存在，直接设置为已审核
    if(M('AuditSearchKeyword') -> getFieldByname($keyword_str, 'id')){
      $search_data['status'] = 1;    
    }
    $search_keyword -> create($search_data);
    $search_keyword -> add(); 

    $this -> display();
  }

  //点击排名
  public function clickrank(){
    $company = M('Company');
    //TOP100
    $result_top100 = $company -> table('yesow_company as c') -> field('c.id,c.name,cs.name as csname,c.clickcount') -> join('yesow_child_site as cs ON c.csid = cs.id') -> order('c.clickcount DESC') -> limit(100) -> select();
    $this -> assign('result_top100', $result_top100);
    //LAST100
    $result_last100 = $company -> table('yesow_company as c') -> field('c.id,c.name,cs.name as csname,c.clickcount') -> join('yesow_child_site as cs ON c.csid = cs.id') -> order('c.clickcount ASC') -> limit(100) -> select();
    $this -> assign('result_last100', $result_last100);
    $this -> display();
  }

  //正负排名
  public function scorerank(){
    $companycomment = M('CompanyComment');
    //TOP100
    $result_top100 = $companycomment -> table('yesow_company_comment as cc') -> field('c.name as cname,cs.name as csname,cc.cid,SUM(cc.score) as count') -> join('yesow_company as c ON cc.cid = c.id') -> join('yesow_child_site as cs ON c.csid = cs.id') -> group('cc.cid') -> where('cc.status = 2') -> order('count DESC') -> limit(100) -> select();
    $this -> assign('result_top100', $result_top100);
    //LAST100
    $result_last100 = $companycomment -> table('yesow_company_comment as cc') -> field('c.name as cname,cs.name as csname,cc.cid,SUM(cc.score) as count') -> join('yesow_company as c ON cc.cid = c.id') -> join('yesow_child_site as cs ON c.csid = cs.id') -> group('cc.cid') -> where('cc.status = 2') -> order('count ASC') -> limit(100) -> select();
    $this -> assign('result_last100', $result_last100);
    $this -> display();
  }

  //添加在线QQ
  public function addqqonline(){
    $cid = $this -> _get('cid', 'intval');
    //公司信息
    $company_info = M('Company') -> table('yesow_company as c') -> field('c.name,c.address,c.linkman,cs.name as csname,csa.name as csaname') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> where(array('c.id' => $cid)) -> find();
    $this -> assign('company_info', $company_info);
    $this -> display();
  }
}

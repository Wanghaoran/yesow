<?php
//渠道黄页
class CompanyAction extends CommonAction {

  public function upload(){//文件上传
    import('ORG.Net.UploadFile');
    $upload = new UpLoadFile();
    $upload -> savePath = C('COMPANY_PIC_PATH') ;//设置上传目录
    $upload -> autoSub = false;//设置使用子目录保存上传文件
    $upload -> saveRule = 'uniqid';
    $upload -> allowExts  = array('jpg', 'gif', 'swf', 'jpeg');// 设置附件上传类型
    $upload -> maxSize  = 409600 ;// 设置附件上传大小

    
    if($upload -> upload()){
      $info = $upload -> getUploadFileInfo();
      return $info[0]['savename'];
    }else{
      return false;
    }
  }

  //渠道黄页首页
  public function index(){
    //速查表通用查询条件
    $where = array();
    $where['c.delaid']  = array('exp', 'is NULL');
    //判断是否为分站数据
    if($csid = D('admin://ChildSite') -> getid()){
      $where['c.csid'] = $csid;
    }
    $category = M('CompanyCategory');
    //读取主营一级类别
    $result_cate = $category -> field('id,name') -> order('sort ASC') -> where('pid=0') -> select();
    //读取主营二级类别（8个）
    foreach($result_cate as $key => $value){
      $result_cate[$key]['two'] = $category -> field('id,name') -> order('sort ASC') -> where(array('pid' => $value['id'])) -> limit(11) -> select();
    }
    $this -> assign('result_cate', $result_cate);
    //读取最新商家（17条最新添加的速查信息）
    $company = M('Company');
    $new_company = $company -> table('yesow_company as c') -> field('c.id,c.name,cc.pid,ccc.name as cname,cs.name as csname,c.addtime,c.manproducts') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> join('yesow_company_category as ccc ON cc.pid = ccc.id') -> where($where) -> order('c.addtime DESC') -> limit(17) -> select();
    $this -> assign('new_company', $new_company);
    //读取最高人气
    $hot_company = $company -> table('yesow_company as c') -> field('c.id,c.name,cc.pid,ccc.name as cname,cs.name as csname,c.addtime,c.manproducts') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> join('yesow_company_category as ccc ON cc.pid = ccc.id') -> where($where) -> order('c.clickcount DESC') -> limit(17) -> select();
    $this -> assign('hot_company', $hot_company);
    //读取最新评论
    $new_comment = M('CompanyComment') -> table('yesow_company_comment as cct') -> field('cct.cid,cct.content,cct.addtime,ccc.name as cname,c.name') -> join('yesow_company as c ON cct.cid = c.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> join('yesow_company_category as ccc ON cc.pid = ccc.id') -> order('addtime DESC') -> limit(17) -> where(array('status' => 2)) -> select();
    $this -> assign('new_comment', $new_comment);
    //正分排行
    $score_top5 = M('CompanyComment') -> table('yesow_company_comment as cc') -> field('c.name as cname,cs.name as csname,cc.cid,SUM(cc.score) as count,c.addtime') -> join('yesow_company as c ON cc.cid = c.id') -> join('yesow_child_site as cs ON c.csid = cs.id') -> group('cc.cid') -> where('cc.status = 2') -> order('count DESC') -> limit(10) -> select();
    $this -> assign('score_top5', $score_top5);
    //负分排行
    $score_last5 = M('CompanyComment') -> table('yesow_company_comment as cc') -> field('c.name as cname,cs.name as csname,cc.cid,SUM(cc.score) as count,c.addtime') -> join('yesow_company as c ON cc.cid = c.id') -> join('yesow_child_site as cs ON c.csid = cs.id') -> group('cc.cid') -> where('cc.status = 2') -> order('count ASC') -> limit(10) -> select();
    $this -> assign('score_last5', $score_last5);

    //图片幻灯
    R('Index/imagetab');
    //系统推荐 - 最新公告
    $new_notice = M('Notice') -> field('id,title,titleattribute,addtime') -> order('addtime DESC') -> limit(10) -> select();
    $this -> assign('new_notice', $new_notice);
    //系统推荐 - 最新更新
    $new_title = M('TitleNotice') -> table('yesow_title_notice as tn') -> field('tn.title,tnt.name as tname,tn.addtime') -> join('yesow_title_notice_type as tnt ON tn.tid = tnt.id') -> order('tn.addtime DESC') -> limit(10) -> select();
    $this -> assign('new_title', $new_title);
    $this -> display();
  }

  //添加速查信息
  public function add(){
    header("Content-Type:text/html;charset=utf-8");
    //处理添加
    if(!empty($_POST['companyname'])){
      if($this -> _post('verify', 'md5') != $_SESSION['verify']){
	echo '<script>alert("验证码错误");history.go(-1);</script>';
	exit();
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
	if($pics = $this -> upload()){
	  $companyaudit -> pic = $pics;
	}else{
	  R('Public/errorjump',array(L('DATA_UPLOAD_ERROR')));
	}
      }
      if($companyaudit -> add()){
	echo '<script>alert("感谢您对易搜的支持！您所提交的数据我们将在36小时内给予审核后通过！多谢您的合作！");location.href="'.__ACTION__.'";</script>';
	exit();
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
    $result_company_category_two = M('CompanyCategory') -> table('yesow_company_category as cc') -> field('cc.id,cc.name,cct.name as cctname,cc.remark') -> where(array('cc.pid' => array('neq', 0))) -> join('yesow_company_category as cct ON cc.pid = cct.id') -> order('cct.sort ASC,cc.sort ASC') -> select();

    $this -> assign('result_company_category_two', $result_company_category_two);
    //如果会员已登录，则查出此会员的个人信息
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      $add_info = M('Member') -> field('name,tel,unit,address,email,idnumber') -> find(session(C('USER_AUTH_KEY')));
      $this -> assign('add_info', $add_info);
    }
    //添加RMB金额变化
    $company_setup = M('CompanySetup');
    $success_rmb = $company_setup -> getFieldByname('addsuccess', 'value');
    $error_rmb = $company_setup -> getFieldByname('adderror', 'value');
    $this -> assign('success_rmb', $success_rmb);
    $this -> assign('error_rmb', $error_rmb);
    $this -> display();
  }

  //速查详情页
  public function info(){
    //如果已登录，并且资料填写不完全，则需要去填写资料，才能查看
    if(session(C('USER_AUTH_KEY'))){
      //如果以下几项有没有填的，则跳到资料填写页
      $info = M('Member') -> field('nickname,tel,password,passwordquestion,passwordanswer,email,fullname,idnumber,sex,qqcode,address,unit') -> find(session(C('USER_AUTH_KEY')));
      foreach($info as $value){
	if(!(bool)$value){
	  echo '<script>alert("您的会员资料还未填写完整,请先填写会员资料");location.href="'. __ROOT__ . '/member.php/index/edituserinfo"</script>';
	  exit();
	}
      }
    }
    $company = M('Company');
    $comment = M('CompanyComment');
    $id = $this -> _get('id', 'intval');
    //点击量加一
    $company -> where(array('id' => $id)) -> setInc('clickcount');
    //结果
    $result = $company -> table('yesow_company as c') -> field('c.name,c.clickcount,c.pic,ct.name as ctname,cs.name as csname,csa.name as csaname,c.ccid,cc.name as ccname,c.manproducts,c.linkman,c.address,c.content,c.mobilephone,c.companyphone,c.qqcode,c.email,c.website,c.updatetime,c.addtime,c.keyword') -> where(array('c.id' => $id)) -> join('yesow_company_type as ct ON c.typeid = ct.id') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> find();
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
	$result['companyphone'] .= ' <a href="javascript:noauthor(\'author_one\');"><img src="__PUBLIC__/index/default/style/images/dd2.gif" /></a>';
      }
      //手机
      if($author['author_two'] == 0){
	$result['mobilephone'] = substr_replace($result['mobilephone'], '********', 3);
	$result['mobilephone'] .= ' <a href="javascript:noauthor(\'author_two\');"><img src="__PUBLIC__/index/default/style/images/dd2.gif" /></a>';
      }
      //QQ
      if($author['author_three'] == 0){
	$result['qqcode'] = substr_replace($result['qqcode'], '*****', 4);
	$result['qqcode'] .= ' <a href="javascript:noauthor(\'author_three\');"><img src="__PUBLIC__/index/default/style/images/dd2.gif" /></a>';
      }
      //邮件
      if($author['author_four'] == 0){
	$result['email'] = substr_replace($result['email'], '*****', 0, strpos($result['email'], '@'));
	$result['email'] .= ' <a href="javascript:noauthor(\'author_four\');"><img src="__PUBLIC__/index/default/style/images/dd2.gif" /></a>';
      }
      //网址
      if($author['author_five'] == 0){
	$result['website'] = preg_replace('/\..*?\./i', '.*****.', $result['website']);
	$result['website'] .= ' <a href="javascript:noauthor(\'author_five\');"><img src="__PUBLIC__/index/default/style/images/dd2.gif" /></a>';
      }
      //是否有查看权
      $this -> assign('isview', 1);
    }
    //如果没有登录，加一张提示图片到后面
    if(!isset($_SESSION[C('USER_AUTH_KEY')])){
      $result['companyphone'] .= ' <a onclick="poplogin();"><img src="__PUBLIC__/index/default/style/images/dd.gif" /></a>';
	$result['mobilephone'] .= ' <a onclick="poplogin();"><img src="__PUBLIC__/index/default/style/images/dd.gif" /></a>';
	$result['qqcode'] .= ' <a onclick="poplogin();"><img src="__PUBLIC__/index/default/style/images/dd.gif" /></a>';
	$result['email'] .= ' <a onclick="poplogin();"><img src="__PUBLIC__/index/default/style/images/dd.gif" /></a>';
	$result['website'] .= ' <a onclick="poplogin();"><img src="__PUBLIC__/index/default/style/images/dd.gif" /></a>';
    }
    //非法词过滤，过滤的字段：公司名称、主营、企业介绍
    $illegal = M('IllegalWord');
    //需要过滤的词的数组
    $illegal_word_temp = $illegal -> field('name') -> order('id') -> select();
    //需要替换的词的数组
    $replace_word_temp = $illegal -> field('replace') -> order('id') -> select();
    //整理这两个数组
    $illegal_word = array();
    $replace_word = array();
    foreach($illegal_word_temp as $key => $value){
      $illegal_word[] = $value['name'];
    }
    foreach($replace_word_temp as $key => $value){
      $replace_word[] = $value['replace'];
    }
    //进行过滤
    $result['name'] = str_replace($illegal_word, $replace_word, $result['name']);
    $result['manproducts'] = str_replace($illegal_word, $replace_word, $result['manproducts']);
    $result['content'] = str_replace($illegal_word, $replace_word, $result['content']);

    $this -> assign('result', $result);
    //最新同类更新
    $where_counterparts = array();
    //判断是否是分站
    if($counterparts_csid = D('admin://ChildSite') -> getid()){
      $where_counterparts['c.csid'] = $counterparts_csid;
    }
    $where_counterparts['c.ccid'] = $result['ccid'];
    $result_counterparts = $company -> table('yesow_company as c') -> field('c.id,c.name,c.updatetime,cs.name as csname') -> where($where_counterparts) -> limit(15) -> order('c.updatetime DESC') -> join('yesow_child_site_area as cs ON c.csaid = cs.id') -> select();
    $this -> assign('result_counterparts', $result_counterparts);
    //相关更新同行
    $key_arr = explode(' ', $result['keyword']);
    $where_about = array();
    foreach($key_arr as $value){
      if(empty($where_about)){
	$where_about['_string'] .="(( c.keyword LIKE '%{$value}%' )";
      }else{
	$where_about['_string'] .=" OR ( c.keyword LIKE '%{$value}%' )";
      }
    }
    $where_about['_string'] .= ") AND (c.id != {$id})";
    //判断是否是分站
    if($about_csid = D('admin://ChildSite') -> getid()){
      $where_about['c.csid'] = $about_csid;
    }
    $result_about_company = $company -> table('yesow_company as c') -> field('c.id,c.name,c.updatetime,cs.name as csname') -> where($where_about) -> limit(15) -> order('c.updatetime DESC') -> join('yesow_child_site_area as cs ON c.csaid = cs.id') -> select();
    $this -> assign('result_about_company', $result_about_company);
    //读取评论
    $comment_where = "cc.cid={$id} and cc.status=2";
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
    $result_comment = $comment -> table('yesow_company_comment as cc') -> field('m.name,cc.content,cc.addtime,cc.floor,cc.score,cc.face') -> where($comment_where) -> join('yesow_member as m ON cc.mid = m.id') -> limit($page -> firstRow . ',' . $page -> listRows) -> order('floor ASC') -> select();
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
	echo '<script>alert("验证码错误");history.go(-1);</script>';
	exit();
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
    //报错RMB金额变化
    $company_setup = M('CompanySetup');
    $success_rmb = $company_setup -> getFieldByname('reportsuccess', 'value');
    $error_rmb = $company_setup -> getFieldByname('reporterror', 'value');
    $this -> assign('success_rmb', $success_rmb);
    $this -> assign('error_rmb', $error_rmb);
    $this -> display();
  }

  //改错页
  public function change(){
     $company = M('Company');
    //处理提交
    if(!empty($_POST['submit'])){
      if($_SESSION['verify'] != $this -> _post('verify', 'md5')){
	echo '<script>alert("验证码错误");history.go(-1);</script>';
	exit();
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
	if($pics = $this -> upload()){
	  $companyaudit -> pic = $pics;
	}else{
	  R('Public/errorjump',array(L('DATA_UPLOAD_ERROR')));
	}
      }
      if($companyaudit -> add()){
	echo '<script>alert("感谢您对易搜的支持！您所提交的数据我们将在36小时内给予审核后通过！多谢您的合作！");history.go(-1);</script>';
	exit();
      }else{
	R('Public/errorjump',array(L('DATA_ADD_ERROR')));
      }
    }
   
    //结果
    $result = $company -> field('name,address,typeid,csid,csaid,linkman,mobilephone,companyphone,qqcode,email,website,manproducts,ccid,keyword,content') -> find($this -> _get('id', 'intval'));
    //如果未查询到数据，则隐藏数据内容
    $result['mobilephone'] = substr_replace($result['mobilephone'], '********', 3);
    $result['qqcode'] = '********';
    $result['email'] = substr_replace($result['email'], '*****', 0, strpos($result['email'], '@'));
    $result['website'] = substr_replace($result['website'], '*******', 7);
    $result['companyphone'] = substr_replace($result['companyphone'], '*******', 5);

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
    //查询主营类别 - 二级
    $result_company_category_two = M('CompanyCategory') -> table('yesow_company_category as cc') -> field('cc.id,cc.name,cct.name as cctname,cc.remark') -> where(array('cc.pid' => array('neq', 0))) -> join('yesow_company_category as cct ON cc.pid = cct.id') -> order('cct.sort ASC,cc.sort ASC') -> select();

    $this -> assign('result_company_category_two', $result_company_category_two);
    //如果会员已登录，则查出此会员的个人信息
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      $add_info = M('Member') -> field('name,tel,unit,address,email,idnumber') -> find(session(C('USER_AUTH_KEY')));
      $this -> assign('add_info', $add_info);
    }
    //改错RMB金额变化
    $company_setup = M('CompanySetup');
    $success_rmb = $company_setup -> getFieldByname('changesuccess', 'value');
    $error_rmb = $company_setup -> getFieldByname('changeerror', 'value');
    $this -> assign('success_rmb', $success_rmb);
    $this -> assign('error_rmb', $error_rmb);
    $this -> display();
  }

  //手动复制页
  public function manualcopy(){
    $where = array();
    $where['c.id'] = array('in', $this -> _get('cid'));
    //查出资料
    $result = M('Company') -> table('yesow_company as c') -> field('c.name,c.address,c.manproducts,c.companyphone,c.mobilephone,c.linkman,c.email,c.qqcode,cs.name as csname,csa.name as csaname,cc.name as ccname,c.updatetime') -> join('yesow_child_site as cs ON c.csid = cs.id') -> join('yesow_child_site_area as csa ON c.csaid = csa.id') -> join('yesow_company_category as cc ON c.ccid = cc.id') -> where($where) -> select();
    $this -> assign('result', $result);
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

  //搜索
  public function search(){

    $company = D('admin://Company');

    //查询分站
    $result_childsite = M('ChildSite') -> field('id,name') -> order('create_time DESC') -> select();
    $this -> assign('result_childsite', $result_childsite);
    $_GET['keyword'] = safeEncoding($_GET['keyword']);

    //右侧固定排名 和 右侧热点排名 读取数据更新消息
    $fixed_result = M('TiteNotice') -> table('yesow_title_notice as tn') -> field('tn.title,tnt.name') -> join('yesow_title_notice_type as tnt ON tn.tid = tnt.id') -> order('tn.addtime DESC') -> limit(30) -> select();
    $this -> assign('fixed_result', $fixed_result);
   
    //高级搜索,只检索出按更新时间排序的一页数据
    if(empty($_GET['keyword']) || $_GET['keyword'] == '请输入您要搜索的内容'){
      $result = $company -> search($_GET['keyword']);
      $this -> assign('result', $result['result']);
      $this -> assign('count', $result['count']);
      $this -> display();
      exit();
    }
    $result =  $company -> search($_GET['keyword']);
    //总条数
    $this -> assign('count', $result['count']);
    //分页
    $this -> assign('show', $result['show']);
    //查询时间
    $this -> assign('time', $result['time']);

    //制作复制替换数组
    $replace_arr = array();
    foreach($result['keyword_arr'] as $value){
      $replace_arr[] = '<font color="#FF0000"><b>' . $value . '</b></font>';
    }
    //执行关键字高亮替换
    foreach($result['result'] as $key => $value){
      $result['result'][$key] = str_replace($result['keyword_arr'], $replace_arr, $result['result'][$key]);
    }
    $this -> assign('result', $result['result']);

    //记录搜索词及搜索信息
    $search_keyword = D('SearchKeyword');
    $search_data = array();
    if(isset($_SESSION[C('USER_AUTH_KEY')])){
      $search_data['mid'] = session(C('USER_AUTH_KEY'));
    }
    $search_data['sourceaddress'] = $_SERVER["HTTP_REFERER"];
    $search_data['keyword'] = $result['keyword'];
    //如果这个词已经存在，直接设置为已审核
    if(M('AuditSearchKeyword') -> getFieldByname($result['keyword'], 'id')){
      $search_data['status'] = 1;    
    }
    //如果这个词已经记录，则更新这条数据，而不是新增
    if($skid = $search_keyword -> getFieldBykeyword($result['keyword'], 'id')){
      $search_data['id'] = $skid;
      $search_data['count'] = array('exp','count+1');
      $search_keyword -> create($search_data);
      $search_keyword -> save();
    }else{
      $search_data['count'] = 1;
      $search_keyword -> create($search_data);
      $search_keyword -> add();
    }

    //如果是包月会员，则记录此次搜索
    if(D('Monthly') -> ismonthly()){
      D('MemberMonthlyDetail') -> writelog('搜索', '在主站搜索一次[<span style="color:blue">' . $_GET['keyword'] . '</span>]');
    }

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

  //商家名录
  public function companylist(){
    $category = M('CompanyCategory');
    //读取主营一级类别
    $result_cate = $category -> field('id,name') -> order('sort ASC') -> where('pid=0') -> select();
    //读取主营二级类别（8个）
    foreach($result_cate as $key => $value){
      $result_cate[$key]['two'] = $category -> field('id,name') -> order('sort ASC') -> where(array('pid' => $value['id'])) -> limit(8) -> select();
    }
    $this -> assign('result_cate', $result_cate);

    //类别id
    $cid = !empty($_GET['cid']) ? $_GET['cid'] : $result_cate[0]['two'][0]['id'];
    //获得此类别名称
    $cid_name = $category -> getFieldByid($cid, 'name');
    $this -> assign('cid_name', $cid_name);
    
    //读取此分类的最新12条速查数据
    $result = M('Company') -> table('yesow_company as c') -> field('c.id,c.name,c.linkman,c.address,c.companyphone,c.manproducts,c.updatetime,c.addtime') -> where(array('c.ccid' => $cid)) -> order('c.addtime DESC') -> limit(12) -> select();
    //隐藏电话
    foreach($result as $key => $value){
      $result[$key]['companyphone'] = substr_replace($value['companyphone'], '*****', 8);
    }
    $this -> assign('result', $result);

    //数据更新消息
    R('Index/gettitlenotice');
    $this -> display();
  }

  //商家在线
  public function companyonline(){
    $this -> display();
  }

  //关键排名
  public function keywordrank(){
    $company = M('Company');
    //热门商家
    $where_hot = array();
    $where_hot['c.delaid']  = array('exp', 'is NULL');
    if($csid = D('admin://ChildSite') -> getid()){
      $where_hot['c.csid'] = $csid;
    }
    $hot_result = $company -> table('yesow_company as c') -> field('c.id,c.name,cs.name as csname,c.addtime') -> join('yesow_child_site as cs ON c.csid = cs.id') -> order('clickcount DESC') -> where($wher_hot) -> limit(10) -> select();
    $this -> assign('hot_result', $hot_result);
    $this -> display();
  }

  //商家风采
  public function companyshow(){
    $companyType = M('CompanyType');
    $company = M('Company');
    //读取公司类型
    $company_type = $companyType -> field('id,name') -> order('sort ASC') -> select();
    $this -> assign('company_type', $company_type);
    //确定typeid
    $tid = !empty($_GET['tid']) ? $_GET['tid'] : $company_type[0]['id'];
    $this -> assign('tid', $tid);

    //结果
    $where = array();
    $where['typeid'] = $tid;
    $where['delaid']  = array('exp', 'is NULL');
    $where['pic'] = array('neq', '');
    //判断是否为分站数据
    if($csid = D('admin://ChildSite') -> getid()){
      $where['csid'] = $csid;
    }

    import("ORG.Util.Page");// 导入分页类
    $count = $company -> where($where) -> count();
    $page = new Page($count, 10);//每页10条
    $page->setConfig('header','条数据');
    $show = $page -> show();
    $this -> assign('show', $show);

    $result = $company -> field('id,name,manproducts,pic') -> where($where) -> order('updatetime DESC') -> limit($page -> firstRow . ',' . $page -> listRows) -> select();
    $this -> assign('result', $result);

    //热门商家
    $where_hot = array();
    $where_hot['c.delaid']  = array('exp', 'is NULL');
    $where_hot['c.typeid'] = $tid;
    if($csid = D('admin://ChildSite') -> getid()){
      $where_hot['c.csid'] = $csid;
    }
    $hot_result = $company -> table('yesow_company as c') -> field('c.id,c.name,cs.name as csname,c.addtime') -> join('yesow_child_site as cs ON c.csid = cs.id') -> order('clickcount DESC') -> where($wher_hot) -> limit(10) -> select();
    $this -> assign('hot_result', $hot_result);

    
    $this -> display();
  }
}

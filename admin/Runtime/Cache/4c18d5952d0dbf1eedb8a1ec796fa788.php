<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head> 
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
  <meta http-equiv="X-UA-Compatible" content="IE=7" /> 
  <title>易搜网站管理后台</title> 
  <link href="__PUBLIC__/dwz/themes/default/style.css" rel="stylesheet" type="text/css" /> 
  <link href="__PUBLIC__/dwz/themes/css/core.css" rel="stylesheet" type="text/css" /> 
  <!--[if IE]>
<link href="__PUBLIC__/dwz/themes/css/ieHack.css" rel="stylesheet" type="text/css" />
<![endif]--> 
  <script src="__PUBLIC__/dwz/js/speedup.js" type="text/javascript"></script> 
  <script src="__PUBLIC__/dwz/js/jquery-1.7.1.min.js" type="text/javascript"></script> 
  <script src="__PUBLIC__/dwz/js/jquery.cookie.js" type="text/javascript"></script> 
  <script src="__PUBLIC__/dwz/js/jquery.validate.js" type="text/javascript"></script> 
  <script src="__PUBLIC__/dwz/js/jquery.bgiframe.js" type="text/javascript"></script> 
  <script src="__PUBLIC__/xheditor/xheditor-1.1.9-zh-cn.min.js" type="text/javascript"></script> 
  <script src="__PUBLIC__/dwz/js/dwz.min.js" type="text/javascript"></script> 
  <script src="__PUBLIC__/dwz/js/dwz.regional.zh.js" type="text/javascript"></script>
  <script src="__PUBLIC__/dwz/js/dwz.ajax.js" type="text/javascript"></script>  
  <script type="text/javascript">
function fleshVerify(){
	//重载验证码
	$('#verifyImg').attr("src", '__APP__/Public/verify/'+new Date().getTime());
}
function dialogAjaxMenu(json){
	dialogAjaxDone(json);
	if (json.statusCode == DWZ.statusCode.ok){
		$("#sidebar").loadUrl("__APP__/Index/menu");
	}
}
function navTabAjaxMenu(json){
	navTabAjaxDone(json);
	if (json.statusCode == DWZ.statusCode.ok){
		$("#sidebar").loadUrl("__APP__/Index/menu");
	}
}
$(function(){
	DWZ.init("__PUBLIC__/dwz/dwz.frag.xml", {
		loginUrl:"__APP__/Public/login_dialog", loginTitle:"登录",	// 弹出登录对话框
//		loginUrl:"__APP__/Public/login",	//跳到登录页面
		statusCode:{ok:1,error:0},
		pageInfo:{pageNum:"pageNum", numPerPage:"numPerPage", orderField:"_order", orderDirection:"_sort"}, //【可选】
		debug:false,	// 调试模式 【true|false】
		callback:function(){
			initEnv();
			$("#themeList").theme({themeBase:"__PUBLIC__/dwz/themes"});
		}
	});
});
</script> 
  <style>
#header {
    height: 85px;
}
#leftside, #container, #splitBar, #splitBarProxy {
    top: 90px;
}


</style> 
 </head> 
 <body scroll="no"> 
  <div id="layout"> 
   <div id="header"> 
    <div class="headerNav"> 
     <a class="logo" href="__APP__">Logo</a> 
     <ul class="nav"> 
      <li><a href="__APP__/Public/main" target="dialog" width="580" height="360" rel="sysInfo">系统信息</a></li> 
      <li><a href="__APP__/Public/password/" target="dialog" mask="true">修改密码</a></li> 
      <li><a href="__APP__/Public/profile/" target="dialog" mask="true">修改资料</a></li> 
      <li><a href="__APP__/Public/logout/">退出</a></li> 
     </ul> 
     <ul class="themeList" id="themeList"> 
      <li theme="default">
       <div class="selected">
        蓝色
       </div></li> 
      <li theme="green">
       <div>
        绿色
       </div></li> 
      <li theme="purple">
       <div>
        紫色
       </div></li> 
      <li theme="silver">
       <div>
        银色
       </div></li> 
      <li theme="azure">
       <div>
        天蓝
       </div></li> 
     </ul> 
    </div> 
    <div id="navMenu"> 
     <ul> 
      <li class=""><a href="sidebar_1.html"><span>速查管理</span></a></li> 
      <li class=""><a href="sidebar_2.html"><span>订单管理</span></a></li> 
      <li class="selected"><a href="sidebar_1.html"><span>产品管理</span></a></li> 
      <li class=""><a href="sidebar_2.html"><span>会员管理</span></a></li> 
      <li class=""><a href="sidebar_1.html"><span>服务管理</span></a></li> 
      <li class=""><a href="__APP__/system/menu"><span>系统设置</span></a></li> 
     </ul> 
    </div> 
   </div> 
   <div id="leftside"> 
    <div id="sidebar_s"> 
     <div class="collapse"> 
      <div class="toggleCollapse">
       <div></div>
      </div> 
     </div> 
    </div> 
    <div id="sidebar"> 
     	
<div class="accordion" fillSpace="sideBar">
	<div class="accordionHeader">
		<h2><span>Folder</span>应用</h2>
	</div>
	<div class="accordionContent">
	
	  <ul class="tree treeFolder">
	    <li><a href="http://www.baidu.com" target="navTab">asasasas</a></li>
			<?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><li>
			<?php if((strtolower($item['name'])) != "public"): if((strtolower($item['name'])) != "index"): if(($item['access']) == "1"): ?><li><a href="__APP__/{$item['name']}/index/" target="navTab" rel="{$item['name']}">{$item['title']}</a></li><?php endif; endif; endif; endforeach; endif; else: echo "" ;endif; ?>

		      </ul>


		    </div>
		    <div class="accordionHeader">
		<h2><span>Folder</span>应用2</h2>
	</div>
	<div class="accordionContent">
	
	  <ul class="tree treeFolder">
	    <li><a href="http://www.baidu.com" target="navTab">asasasas</a></li>
			<?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><li>
			<?php if((strtolower($item['name'])) != "public"): if((strtolower($item['name'])) != "index"): if(($item['access']) == "1"): ?><li><a href="__APP__/{$item['name']}/index/" target="navTab" rel="{$item['name']}">{$item['title']}</a></li><?php endif; endif; endif; endforeach; endif; else: echo "" ;endif; ?>

		      </ul>


	</div>
</div>
 
    </div> 
   </div> 
   <div id="container"> 
    <div id="navTab" class="tabsPage"> 
     <div class="tabsPageHeader"> 
      <div class="tabsPageHeaderContent">
       <!-- 显示左右控制时添加 class="tabsPageHeaderMargin" --> 
       <ul class="navTab-tab"> 
        <li tabid="main" class="main"><a href="javascript:void(0)"><span><span class="home_icon">我的主页</span></span></a></li> 
       </ul> 
      </div> 
      <div class="tabsLeft">
       left
      </div>
      <!-- 禁用只需要添加一个样式 class="tabsLeft tabsLeftDisabled" --> 
      <div class="tabsRight">
       right
      </div>
      <!-- 禁用只需要添加一个样式 class="tabsRight tabsRightDisabled" --> 
      <div class="tabsMore">
       more
      </div> 
     </div> 
     <ul class="tabsMoreList"> 
      <li><a href="javascript:void(0)">我的主页</a></li> 
     </ul> 
     <div class="navTab-panel tabsPageContent layoutBox"> 
      <div class="page unitBox"> 
       <div class="accountInfo"> 
        <div class="alertInfo"> 
        </div> 
        <div class="right"> 
	 <p><?php echo (date('Y-m-d g:i a',time())); ?></p> 
        </div> 
	<p><span>尊敬的 <?php echo ($_SESSION['name']); ?> 欢迎回来！</span></p> 
	<p>您已登录本系统 <span><?php echo ($_SESSION['loginCount']); ?></span> 次  &nbsp;&nbsp;&nbsp;&nbsp;本次登录IP为：<span style="color:red"><?php echo get_client_ip();?></span>  &nbsp;&nbsp;&nbsp;&nbsp;上次登录时间为： <span style="color:red"><?php echo (date("y-m-d H:i:s",$_SESSION['lastLoginTime'])); ?></span> </p> 
       </div> 
       <div class="pageFormContent" layouth="80"> 
	<br>
        <h1>易搜管理员后台使用须知：</h1> 
        <pre style="margin: 6px; line-height: 2.4em;">
	  一、凡登陆此后台的管理用户均属于我们中心正式授权的后台管理帐号，如果你是非法获取的帐号不小心登陆进来，我们已经记录下您的IP，请您的遵守
《中国互联网络安全法》的相关规定立即退出，如果您有任何恶意的破坏行为，我们将采用法律手段维护自己的合法权益；
          二、凡是合法授权的管理人员，请严格遵守我中心的相关规定进行规范操作，有任何疑问请直接联系技术或客户服务人员咨询后做相关操作；
          三、管理员对自己所管理的后台有独立管理维护的义务，若发生任何意外或错误的操作，请及时向有关部门反应，并协助立即解决，否则视为非法破坏行为，
我们将追究责其法律责任； 
          四、管理人员对我中心给予授权的帐号和密码请务必妥善保管，未经我中心书面许可，任何人不得将自己的帐号密码转让、转借、出售给无关的第三方使用，
如因此造成的后果将有授权管理员自己承担； 
          五、管理人员尽量避免在公共场所（网吧或客户私人电脑等）使用该中心的管理帐号和密码，以免被一些不法分子利用此帐号做些非法的诈骗等；以免给您或
客户造成不必要的麻烦或经济损失，如果因此造成的直接经济损失由该管理人员自己个人承担； 
          六、本管理后台的任何数据均属于我中心的合法版权所有，任何人未经我中心书面授权，不得随意传播、盗取、冒用我中心数据，如有违者，我们将追究其法
律责任；													

</pre> 
       </div> 
      </div> 
     </div> 
    </div> 
   </div> 
  </div> 
  <div id="footer">
   Copyright &copy; 2010 
   <a href="http://www.cnhtk.cn" target="_blank">cnhtk.cn</a>
  </div>   
 </body>
</html>
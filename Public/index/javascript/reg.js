
//会员表单校验

$(document).ready(function(){

	//初始提示
	$('#username').after('<span id="chkUser" class="msgdiv">登录账号由5-20个英文字母或数字组成</span>');
	$('#nickname').after('<span id="chknickname" class="msgdiv">取个你喜欢的网络名字,由6位以上数字或字母组合而成</span>');
	$('#password').after('<span id="chkPass" class="msgdiv">登录密码由5-20个英文字母或数字组成</span>');
	$('#password1').after('<span id="chkRepass" class="msgdiv">请重复输入和上面相同的密码</span>');
	$('#memtishi').after('<span id="chkmemtishi" class="msgdiv">密码丢掉后可以找回的问题提示</span>');
	$('#memhueda').after('<span id="chkmemhueda" class="msgdiv">找回密码必须回答的答案</span>');
    $('#email').after('<span id="chkEmail" class="msgdiv">请正确填写您的邮件地址，邮箱件验证后方可使用帐号</span>');
	$('#memtel').after('<span id="chkmemtel" class="msgdiv">您目前的联系电话</span>');
	$('#companyname').after('<span id="chkcompanyname" class="msgdiv">您目前的工作单位</span>');
    $('#tuijian').after('<span id="chktuijian" class="msgdiv">请填写推荐您来本站注册的人的帐号，没有可以为空</span>');
	$('#code').after('<span id="chkverify" class="msgdiv">请输入和图片上一致的验证码</span>');
	$('#memdizhi').after('<span id="chkmemdizhi" class="msgdiv">所在地区</span>');

	$('#username').focus(function(){ 
		$('#chkUser').remove();
		$('#username').after('<span id="chkUser" class="msgdiv">登录账号由5-20个英文字母或数字组成</span>');
	}); 
	
	$('#username').blur(function(){ 
		var p=$("#username")[0].value;
		var patrn=/^(\w){5,20}$/;
		if(!patrn.exec(p)){
			$('#chkUser').remove();
			$('#username').after('<span id="chkUser" class="errdiv">登录账号必须由5-20个英文字母或数字组成</span>');
		}else{

			$.ajax({
					type: "POST",
					url: "checkusername",
					data: "name="+p,
					success: function(msg){
						
						if(msg=="1"){
							$('#chkUser').remove();
							$('#username').after('<span id="chkUser" class="rightdiv">该登录账号可以使用</span>');
						}else{
							$('#chkUser').remove();
							$('#username').after('<span id="chkUser" class="errdiv">该登录账号已经被使用，请更换一个</span>');
						}
					}
				
			 });
			
		}
	}); 

	$('#nickname').focus(function(){ 
		$('#chknickname').remove();
		$('#nickname').after('<span id="chknickname" class="msgdiv">登录账号由5-20个英文字母或数字组成</span>');
	}); 
	
	$('#nickname').blur(function(){ 
		var p=$("#nickname")[0].value;
		//var patrn=/^(\w){5,20}$/;
		if(p.length<1){
			$('#chknickname').remove();
			$('#nickname').after('<span id="chknickname" class="errdiv">昵称必须填写</span>');
		}else{

							$('#chknickname').remove();
							$('#nickname').after('<span id="chknickname" class="rightdiv">该昵称可以使用</span>');
			
		}
	}); 


	$('#password').focus(function(){ 
		$('#chkPass').remove();
		$('#password').after('<span id="chkPass" class="msgdiv">登录密码由5-20个英文字母或数字组成</span>');
	}); 


	$('#password').blur(function(){ 
		var p=$("#password")[0].value;
		var patrn=/^(\w){5,20}$/;
		if(!patrn.exec(p)){
			$('#chkPass').remove();
			$('#password').after('<span id="chkPass" class="errdiv">登录密码必须由5-20个英文字母或数字组成</span>');
		}else{
			$('#chkPass').remove();
			$('#password').after('<span id="chkPass" class="rightdiv">该登录密码可以使用</span>');
		}
	}); 

	$('#password1').focus(function(){ 
		$('#chkRepass').remove();
		$('#password1').after('<span id="chkRepass" class="msgdiv">请重复输入和上面相同的密码</span>');
	}); 

	$('#password1').blur(function(){ 
		var p=$("#password1")[0].value;
		var w=$("#password")[0].value;
		var patrn=/^(\w){5,20}$/;
		if(!patrn.exec(p)){
			$('#chkRepass').remove();
			$('#password1').after('<span id="chkRepass" class="errdiv">登录密码必须由5-20个英文字母或数字组成</span>');
		}else if(p!=w){
			$('#chkRepass').remove();
			$('#password1').after('<span id="chkRepass" class="errdiv">两次输入的密码不一致，请输入和上面相同的密码</span>');
		}else{
			$('#chkRepass').remove();
			$('#password1').after('<span id="chkRepass" class="rightdiv">输入正确</span>');
		}
	}); 

	$('#email').focus(function(){ 
		$('#chkEmail').remove();
		$('#email').after('<span id="chkEmail" class="msgdiv">请输入正确的电子邮件</span>');
	}); 

	$('#email').blur(function(){ 
		var p=$("#email")[0].value;
		var patrn=/^[_.0-9a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,3}$/;
		if(!patrn.exec(p)){
			$('#chkEmail').remove();
			$('#email').after('<span id="chkEmail" class="errdiv">电子邮件格式不正确，请输入正确的电子邮件</span>');
		}else{

			$.ajax({
					type: "POST",
					url: "checkemail",
					data: "email="+p,
					success: function(msg){
						
						if(msg=="1"){
							$('#chkEmail').remove();
							$('#email').after('<span id="chkEmail" class="rightdiv">输入正确</span>');
						}else{
							$('#chkEmail').remove();
							$('#email').after('<span id="chkEmail" class="errdiv">该电子邮件已经被使用，请更换一个</span>');
						}
					}
				
			 });
		}
	}); 

//问题提示
	$('#memtishi').focus(function(){ 
		$('#chkmemtishi').remove();
		$('#memtishi').after('<span id="chkmemtishi" class="msgdiv">密码丢失后可以找回的问题提示</span>');
	}); 

	$('#memtishi').blur(function(){
		var p=$("#memtishi")[0].value;
		if(p.length<2){
			$('#chkmemtishi').remove();
			$('#memtishi').after('<span id="chkmemtishi" class="errdiv">请输入问题提示</span>');
		}else{
			$('#chkmemtishi').remove();
			$('#memtishi').after('<span id="chkmemtishi" class="rightdiv">输入正确</span>');
		}

	}); 
//问题答案
	$('#memhueda').focus(function(){ 
		$('#chkmemhueda').remove();
		$('#memhueda').after('<span id="chkmemhueda" class="msgdiv">密码丢失后可以找回的问题提示</span>');
	}); 

	$('#memhueda').blur(function(){
		var p=$("#memhueda")[0].value;
		if(p.length<2){
			$('#chkmemhueda').remove();
			$('#memhueda').after('<span id="chkmemhueda" class="errdiv">请输入问题答案</span>');
		}else{
			$('#chkmemhueda').remove();
			$('#memhueda').after('<span id="chkmemhueda" class="rightdiv">输入正确</span>');
		}

	}); 
//问题提示

	$('#code').focus(function(){ 
		$('#chkCode').remove();
		$('#getcode').after('<span id="chkCode" class="msgdiv">请输入和图片上一致的验证码</span>');
	}); 

	$('#code').blur(function(){
		var p=$("#code")[0].value;
		if(p==''){
			$('#chkCode').remove();
			$('#getcode').after('<span id="chkCode" class="errdiv">请输入和图片上一致的验证码</span>');
		}else{

			$.ajax({
					type: "POST",
					url: PDV_RP+"index.php?controller=adminp&action=post",
//					url: PDV_RP+"post.php",
					data: "act=imgcode&code="+p,
					success: function(msg){
						if(msg=="1"){
							$('#chkCode').remove();
							$('#getcode').after('<span id="chkCode" class="rightdiv">输入正确</span>');
						}else{
							$('#chkCode').remove();
							$('#getcode').after('<span id="chkCode" class="errdiv">请输入和图片上一致的验证码</span>');
						}
					}
				
			 });

		}
	}); 


	$('#memdizhi').blur(function(){
		var p=$("#memdizhi")[0].value;
		//var p=document.form1.memdizhi.value;
		if(p==''){
			$('#chkmemdizhi').remove();
			$('#memdizhi').after('<span id="chkmemdizhi" class="errdiv">请输入所在地</span>');
		}else{
			$('#chkmemdizhi').remove();
			$('#memdizhi').after('<span id="chkmemdizhi" class="rightdiv">输入正确</span>');
		}

	}); 

	//姓名
	$('#name').focus(function(){ 
		$('#chkName').remove();
		$('#name').after('<span id="chkName" class="msgdiv">请输入您的姓名</span>');
	}); 

	$('#name').blur(function(){
		var p=$("#name")[0].value;
		if(p.length<2){
			$('#chkName').remove();
			$('#name').after('<span id="chkName" class="errdiv">请输入您的姓名</span>');
		}else{
			$('#chkName').remove();
			$('#name').after('<span id="chkName" class="rightdiv">输入正确</span>');
		}

	}); 


	//公司
	$('#companyname').focus(function(){ 
		$('#chkcompanyname').remove();
		$('#companyname').after('<span id="chkcompanyname" class="msgdiv">请填写公司名称,个人用户请填姓名</span>');
	}); 

	$('#companyname').blur(function(){
		var p=$("#companyname")[0].value;
		if(p.length<2){
			$('#chkcompanyname').remove();
			$('#companyname').after('<span id="chkcompanyname" class="errdiv">请填写公司名称,个人用户请填姓名</span>');
		}else{
			$('#chkcompanyname').remove();
			$('#companyname').after('<span id="chkcompanyname" class="rightdiv">输入正确</span>');
		}

	}); 



///固话
/*	$('#memtel').focus(function(){ 
		$('#chkmemtel').remove();
		$('#memtel').after('<span id="chkmemtel" class="msgdiv">请输入固定电话号码，格式如：010-12345678</span>');
	}); 

	$('#memtel').blur(function(){
		var p=$("#memtel")[0].value;
		if(p==''){
			$('#chkmemtel').remove();
		}else{
			var patrn=/^[_.0-9a-z-]+-([0-9a-z][0-9a-z-])+[0-9]{4,8}$/;
			if(!patrn.exec(p)){
				$('#chkmemtel').remove();
				$('#memtel').after('<span id="chkmemtel" class="errdiv">请输入正确的固定电话号码，格式如：010-12345678</span>');
			}else{
				$('#chkmemtel').remove();
				$('#memtel').after('<span id="chkmemtel" class="rightdiv">输入正确</span>');
				
			}
		}

	}); */
	
   $('#memtel').focus(function(){ 
		$('#chkmemtel').remove();
		$('#memtel').after('<span id="chkmemtel" class="msgdiv">请输入手机号码，如：13912345678</span>');
	}); 

	$('#memtel').blur(function(){
		var p=$("#memtel")[0].value;
		if(p==''){
			$('#chkmemtel').remove();
			$('#memtel').after('<span id="chkmemtel" class="errdiv">请输入正确的手机号码，如：13912345678</span>');
		}else if(p.length<10){
			$('#chkmemtel').remove();
			$('#memtel').after('<span id="chkmemtel" class="errdiv">请输入正确的手机号码，如：13912345678</span>');
		}else{
			$('#chkmemtel').remove();
			$('#memtel').after('<span id="chkmemtel" class="rightdiv">输入正确</span>');
		}

	});

	//验证码
	$('#verify').focus(function(){ 
		$('#chkverify').remove();
		$('#code').after('<span id="chkverify" class="msgdiv">请输入和图片上一致的验证码</span>');
	});
	$('#verify').blur(function(){
	  var p = $('#verify')[0].value;
	  $.ajax({
	    type: "POST",
	    url: "checkverify",
	    data: "name="+p,
	    success: function(msg){
	      if(msg=="1"){
		$('#chkverify').remove();
		$('#code').after('<span id="chkverify" class="rightdiv">验证码输入正确</span>');
	      }else{
		$('#chkverify').remove();
		$('#code').after('<span id="chkverify" class="errdiv">验证码输入错误</span>');
	      }
	    }
	  });
	});
  
});






//会员注册表单提交
$(document).ready(function(){
	
	$('#form12').submit(function(){ 
		$('#form12').ajaxSubmit({
			target: 'div#notice',
			url: PDV_RP+"index.php/adminp/reg2",
			//url: PDV_RP+'post.php',
			success: function(msg) {
				
				switch(msg){
					
					case "OK":
						$('div#notice').hide();
						if($("#nextstep")[0].value=="enter"){
							window.location='index.php';
						}else{
							window.location='reg.php?step='+$("#nextstep")[0].value;
						}

					break;

					case "CHECK":
						$('div#notice')[0].className='okdiv';
						$('div#notice').html("会员注册成功！您注册的会员类型需要审核后才能登录，感谢您的注册");
						$('div#notice').show();
						$().setBg();
					break;

					default :
						$('div#notice')[0].className='noticediv';
						$('div#notice').show();
						$('div#notice').html("会员注册成功！您注册的会员类型需要审核后才能登录，感谢您的注册");
						//$().setBg();
					break;
				}
				
			}
		}); 
       return false; 

   }); 
});
//头像设置
$(document).ready(function(){
	$(".selface").click(function(){
		$("input#nowface")[0].value=this.id.substr(8);
		$("img#nowfacepic")[0].src=this.src;
	});
});





	function cheakform()
	{
		

		if(document.form1.username.value=="")
		{
			document.form1.username.focus();
			document.form1.username.blur();
		return false;
		}		
		else if($('#chkUser')[0].className!='rightdiv')
		{
			document.form1.username.focus();
			document.form1.username.blur();
		return false;
		}
		else if (document.form1.nickname.value=="")
		{
		
			document.form1.nickname.focus();
			document.form1.nickname.blur();

		return false;
		}
		
		else if(document.form1.password.value=="")
		{
			document.form1.password.focus();
			document.form1.password.blur();
		return false;

		}
		 else if(document.form1.password1.value=="")		
		{
			document.form1.password1.focus();
			document.form1.password1.blur();
		return false;
		}
		else if(document.form1.password.value!=document.form1.password1.value)
		{
		
			document.form1.password1.focus();
			document.form1.password1.blur();

		return false;
		}
		else if(document.form1.memtishi.value=="")
		{
			document.form1.memtishi.focus();
			document.form1.memtishi.blur();		
		return false;
		}
		else if(document.form1.memhueda.value=="")
		{
			document.form1.memhueda.focus();
			document.form1.memhueda.blur();	
		return false;
		}
		else if(document.form1.email.value=="")
		{
			document.form1.email.focus();
			document.form1.email.blur();		
		return false;
		}
		else if(document.form1.email.value.indexOf("@")=="-1")
		{
			document.form1.email.focus();
			document.form1.email.blur();
		return false;
		}
		else if(document.form1.email.value.indexOf(".")=="-1")
		{
			document.form1.email.focus();
			document.form1.email.blur();

		
		return false;
		}
		
		else if(document.form1.memtel.value=="")
		{
		
			document.form1.memtel.focus();
			document.form1.memtel.blur();

		return false;
		}
		else if(document.form1.companyname.value=="")
		{
		
			document.form1.companyname.focus();
			document.form1.companyname.blur();

		return false;
		}
		
		else if(document.form1.memdizhi.value=="")
		{
		  
			document.form1.memdizhi.focus();
			document.form1.memdizhi.blur();
		return false;
		}
		else if(document.form1.verify.value=="")
		{
		    document.form1.verify.focus();
			document.form1.verify.blur();
			//$('#chkCode').remove();
			//$('#getcode').after('<span id="chkCode" class="errdiv">请输入的验证码</span>');
		return false;
		}
		else if($('#chkverify')[0].className!='rightdiv')
		{
			document.form1.verify.focus();
			document.form1.verify.blur();
		return false;
		}

	return true;
	}

function deal(){
var someValue;
someValue=window.showModalDialog('/index.php?controller=adminp&action=headbrowse','','dialogWidth=550px;\
dialogHeight=430px;status=no;help=no;scrollbars=no');
if (someValue == undefined){  //当用户在弹出的网页对话框中没有选择头像时
	someValue="0";
} 
document.images.img.src="style/images/user/"+someValue+".gif";

document.form1.ICO.value=someValue+".gif";
}

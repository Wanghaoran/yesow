
//会员表单校验

$(document).ready(function(){

	//初始提示
	$('#username').after('<span id="chkUser" class="msgdiv">登录账号由5-20个英文字母或数字组成</span>');
	$('#password').after('<span id="chkPass" class="msgdiv">登录密码由5-20个英文字母或数字组成</span>');
	$('#password1').after('<span id="chkRepass" class="msgdiv">请重复输入和上面相同的密码</span>');
    $('#email').after('<span id="chkEmail" class="msgdiv">请正确填写您的邮件地址，邮箱件验证后方可使用帐号</span>');
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
		var patrn=/^[_.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z]+.)+[a-zA-Z]{2,3}$/;
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
		else if($('#chkEmail')[0].className!='rightdiv')
		{
			document.form1.email.focus();
			document.form1.email.blur();
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

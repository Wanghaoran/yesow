﻿
//会员表单校验

$(document).ready(function(){

	//初始提示
	$('#nickname').after('<span id="chknickname" class="msgdiv">取个你喜欢的名字,由6位以上数字或字母组合而成</span>');
	$('#memtishi').after('<span id="chkmemtishi" class="msgdiv">密码丢掉后可以找回的问题提示</span>');
	$('#memhueda').after('<span id="chkmemhueda" class="msgdiv">找回密码必须回答的答案</span>');
    $('#email').after('<span id="chkEmail" class="msgdiv">请正确填写您的邮件地址，邮箱件验证后方可使用帐号</span>');
	$('#memdizhi').after('<span id="chkmemdizhi" class="msgdiv">所在地区</span>');


	$('#nickname').focus(function(){ 
		$('#chknickname').remove();
		$('#nickname').after('<span id="chknickname" class="msgdiv">取个你喜欢的名字,由6位以上数字或字母组合而成</span>');
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

	function cheakform()
	{
	 if (document.form1.nickname.value=="")
		{
		
			document.form1.nickname.focus();
			document.form1.nickname.blur();

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
		else if($('#chkEmail')[0].className == 'errdiv')
		{
			document.form1.email.focus();
			document.form1.email.blur();
		return false;
		}
		else if($('#chkmemtel')[0].className == 'errdiv')
		{
			document.form1.memtel.focus();
			document.form1.memtel.blur();
		return false;
		}
		else if(document.form1.memdizhi.value=="")
		{
			document.form1.memdizhi.focus();
			document.form1.memdizhi.blur();
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

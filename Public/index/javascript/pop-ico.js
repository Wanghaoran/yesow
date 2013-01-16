/**
 * 弹出头像JS
 */
var ICO = new  Object();
ICO.show = function(){
	$(".pop-ico").fadeIn();
}

ICO.hide = function(){
	$(".pop-ico").fadeOut();
}

$(function(){
 	var $box = $(".pop-ico");
 	var $showbtn =$(".show-ico-btn");
 	var $headico = $("input[name='headico']");
 	var $hidebtn =$box.find(".hide-ico-btn");
 	var $icolist = $box.find(".ico-list");
 	$showbtn.bind("click",ICO.show);
 	$hidebtn.bind("click",ICO.hide);
 	//点击选择头像
 	$("> a", $icolist).each(function(){
 		$(this).bind("click",function(){
 			$(this).addClass("cur").siblings().removeClass("cur");
 			var src =$(this).find("img").attr("src");
 			$showbtn.find("img").attr("src",src);
 			$headico.val($(this).attr("data-src"));
 			ICO.hide();
 		});
 	});
});
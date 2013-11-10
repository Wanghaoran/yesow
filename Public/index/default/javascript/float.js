$(function(){
	var oFloat=$('.helpCenter'); 
	var oBtn=$('.helpButton');
	var startTop=oFloat.offset().top;
	var bool=false;
	var bStop=true;
	
	$(window).scroll(function(){
		if(bStop)
		{
			var offsetTop=startTop+$(window).scrollTop();
			oFloat.animate({ top: offsetTop}, { duration:500, queue:false});	
		}
	});
	
	oFloat.hover(function(){
		if(bool)
		{
			oFloat.animate({ right:-123}, 500, function(){
				bool=false;
			});
		}
		else
		{
			oFloat.animate({ right:0}, 500, function(){
				bool=true;
			});
		}
	});
});
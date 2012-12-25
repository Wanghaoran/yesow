$(function(){
	//获取焦点	
	var $k = '';
	$('.input').on('focus',function(){
		$k = $(this).val();
		$(this).val('');	
	}).on('blur',function(){
		if($(this).val() == ''){
			$(this).val($k);	
	}	
	})
	//一级菜单
	$('ul#nav01_ul').on('mouseover', 'li', function(){
		$(this).addClass('hover').siblings('li').removeClass('hover');
		var ind = $('ul#nav01_ul li').index($(this));
		$('ul#nav02_ul li').eq(ind).removeClass('none').siblings('li').addClass('none');
	})
	//顶部切换
	$('ul#main01_r_tit_ul').on('mouseover', 'li.btn', function(){
		$(this).addClass('hover').siblings('li.btn').removeClass('hover');
		var ind = $('ul#main01_r_tit_ul li.btn').index($(this));
		$('div#main01_r_bod ul').eq(ind).removeClass('none').siblings('ul').addClass('none');
	})

	//弹出菜单
	$('ul#main02_ul>li:not(.exclude)').on('mouseover', function(){
		$(this).addClass('hover').siblings('li').removeClass('hover');
	}).on('mouseout',function(){
		$(this).removeClass('hover');
	})
	//tab切换
	$('div.tab').on('mouseover',function(){
		var that = $(this);
		that.on('mouseover', 'ul.tab_btn li',function(){
			var ind = that.find('ul.tab_btn li').index($(this));
			$(this).addClass('hover').siblings('li').removeClass('hover');
			that.find('div.tab_bod>ul').eq(ind).removeClass('none').siblings('ul').addClass('none');
			return false;
		})
		return false;
	})
	$('#minglu_nav').on('mouseover', 'li', function(){
		$(this).addClass('hover').siblings('li').removeClass('hover');
	}).on('mouseout', 'li', function(){
		$(this).removeClass('hover');
	})
})

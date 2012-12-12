<?php if (!defined('THINK_PATH')) exit();?>	
<div class="accordion" fillSpace="sideBar">
	<div class="accordionHeader">
		<h2><span>Folder</span>管理员管理</h2>
	</div>
	<div class="accordionContent">
	
	  <ul class="tree treeFolder">
	    <li><a>节点管理</a></li>
	    <li><a>用户管理</a></li>
	    <li><a>用户组管理</a></li>
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
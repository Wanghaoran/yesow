<?php if (!defined('THINK_PATH')) exit();?><div class="page">
	<div class="pageContent">
	
	<form method="post" action="__URL__/change/callbackType/closeCurrent" class="pageForm required-validate" onsubmit="return validateCallback(this, dialogAjaxDone)">
		<div class="pageFormContent" layoutH="58">
		
	<div class="unit">
		<label>用户名：</label>
		<input type="text" class="required" readonly="readonly" value="<?php echo ($_SESSION['name']); ?>"/>
	</div>
	
	<div class="unit">
		<label>电子邮件：</label>
		<input type="text" class="required email"  name="email" value="<?php echo ($result["email"]); ?>"/>
	</div>
	
	<div class="unit">
		<label>备注：</label>
		<textarea class="required"  name="remark"  rows="5" cols="57" ><?php echo ($result["remark"]); ?></textarea>
	</div>

</div>
		<div class="formBar">
			<ul>
				<li><div class="buttonActive"><div class="buttonContent"><button type="submit">提交</button></div></div></li>
				<li><div class="button"><div class="buttonContent"><button type="button" class="close">取消</button></div></div></li>
			</ul>
		</div>
	</form>
	
	</div>
</div>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
	<title><?php echo e(__('admin.admin/public/jump/title')); ?> - <?php echo e(__('admin.admin/public/head/title')); ?></title>
	<style type="text/css">
		*{ padding: 0; margin: 0; }
		body{ background: #fff; font-family: "Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif; color: #333; font-size: 16px; }
		.system-message{ padding: 24px 48px; }
		.system-message h1{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
		.system-message .jump{ padding-top: 10px; }
		.system-message .jump a{ color: #333; }
		.system-message .success,.system-message .error{ line-height: 1.8em; font-size: 36px; }
		.system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display: none; }
	</style>
</head>
<body>
<div class="system-message">
	<?phpswitch ($code) {?>
	<?phpcase 1:?>
	<h1>:)</h1>
	<p class="success"><?phpecho(strip_tags($msg));?></p>
	<?phpbreak;?>
	<?phpcase 0:?>
	<h1>:(</h1>
	<p class="error"><?phpecho(strip_tags($msg));?></p>
	<?phpbreak;?>
	<?php} ?>
	<p class="detail"></p>
</div>
<script type="text/javascript">

</script>
</body>
</html>
<?php /**PATH D:\phpstudy_pro\WWW\mac\resources\views\admin\public\msg.blade.php ENDPATH**/ ?>
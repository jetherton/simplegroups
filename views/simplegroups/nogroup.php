
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
<title><?php echo Kohana::language('simplegroups.no');?></title> 
<link rel="stylesheet" type="text/css" href="<?php echo url::site(); ?>media/css/error.css" /> 
</head> 
 
<body> 
<div id="error"> 
	<h1><?php echo Kohana::language('simplegroups.error');?></h1><?php echo Kohana::language('simplegroups.sorry');?>
	 <a href="<?php echo url::site();?>logout"><?php echo Kohana::lang('ui_admin.logout');?></a><?php echo Kohana::language('simplegroups.you');?> 
</div> 
</body> 
</html>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
<title>No Group</title> 
<link rel="stylesheet" type="text/css" href="<?php echo url::site(); ?>media/css/error.css" /> 
</head> 
 
<body> 
<div id="error"> 
	<h1>Error: No Group Assigned</h1> 
	We are sorry, but it seems that your user account has not been assigned to a group. <br/><br/> Please contact this web site's administrator to correct this. 
	<br/><br/>
	If you have another use account you can <a href="<?php echo url::site();?>logout"><?php echo Kohana::lang('ui_admin.logout');?></a> and then log back in using that user account.
	<br/><br/> Thank you.
</div> 
</body> 
</html>
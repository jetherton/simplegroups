<?php $group_page = url::site()."simplegroups/index/".$group_id; ?>

	<br/>
	<br/>
	<h5 style="font-size:14px;border-top:1px dotted #c0c2b8;">Group</h5>
	
	This report created by: <a style="float:none;font-size:14px;" href="<?php echo $group_page; ?>"> <?php echo $group_name; ?></a>
	<br/>
	<?php
		$thumb = $logo_file."_t.jpg";
		$prefix = url::base().Kohana::config('upload.relative_directory')."/groups";
			print "<a href='$group_page'>";
			print "<img src=\"$prefix/$thumb\" >";
			print "</a>";
	?>

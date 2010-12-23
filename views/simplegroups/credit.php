<?php $group_page = url::site()."simplegroups/index/".$group_id; ?>
<li>
<small>Group</small>
<br/>
This report created by: <strong><a href="<?php echo $group_page; ?>"> <?php echo $group_name; ?></a></strong>
<br/>
<?php
                        $thumb = $logo_file."_t.jpg";
                        $prefix = url::base().Kohana::config('upload.relative_directory')."/groups";
	                        print "<a href='$group_page'>";
	                        print "<img src=\"$prefix/$thumb\" >";
	                        print "</a>";
	?>
</li>
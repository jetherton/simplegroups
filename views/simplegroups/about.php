<div id="content">
	<div class="content-bg">
		<div class="big-block">
			<h1><?php echo $group_name ?></h1>
			<?php
				$thumb = $group_logo.".jpg";
					$prefix = url::base().Kohana::config('upload.relative_directory')."/groups";
					print "<img src=\"$prefix/$thumb\" >";
			?>
			<div class="page_text"><?php echo $group_description ?></div>
		</div>
	</div>
</div>
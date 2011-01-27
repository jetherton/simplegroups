<div class="content-bg">
	<div class="big-block"> 
		<h1> Groups: </h1>
		<h2>
			<table style="padding-left: 20px;">
				<?php
					foreach($groups as $group)
					{
						$thumb = $group->logo."_t.jpg";
						$prefix = url::base().Kohana::config('upload.relative_directory')."/groups";						
						echo "<tr>";
						echo "<td style=\"padding-right: 10px;\"><a href=\"".url::site()."simplegroups/index/".$group->id."\">".$group->name."</a></td>";
						echo "<td style=\"padding-bottom: 20px;\"> <a href=\"".url::site()."simplegroups/index/".$group->id."\"><img src=\"$prefix/$thumb\" ></a></td>";
						echo "</tr>";
					}
				?>
			</table>
		</h2>
	</div>
</div>
<div class="content-bg">
	<div class="big-block"> 
		<h1><?php echo Kohana::lang('simplegroups.grouplist_groups');?></h1>
			<table style="padding-left: 20px;">
				<?php
					foreach($groups as $group)
					{
						$thumb = $group->logo."_t.jpg";
						$prefix = url::base().Kohana::config('upload.relative_directory')."/groups";						
						echo "<tr>";
						echo "<td style=\"padding-right: 10px;\"><h2><a href=\"".url::site()."simplegroups/groupmap/".$group->id."\">".$group->name."</a></h2></td>";
						if($group->logo != null)
						{					
							echo "<td style=\"padding-bottom: 20px;\"> <a href=\"".url::site()."simplegroups/groupmap/".$group->id."\"><img src=\"$prefix/$thumb\" ></a></td>";
						}
						echo "<td style=\"padding-bottom: 20px;\">"; 
							if($group->contact_person != null)
							{
								echo $group->contact_person . "<br/>";
							}
						echo "</td>";
						echo "</tr>";
					}
				?>
			</table>
	</div>
</div>
<div class="info-container">
	<div class="i-c-head">
		<h3>
			<?php echo Kohana::lang('simplegroups.groups');?>
		</h3>
	</div>
	<br/>

		<table class="table">
			<thead>
				<tr>
					<th><?php echo Kohana::lang('simplegroups.name');?></th>
					<th><?php echo Kohana::lang('simplegroups.#_member');?></th>
					<th><?php echo Kohana::lang('simplegroups.#_report');?></th>
				</tr>
			</thead>
			<tfoot>		
			</tfoot>
			<tbody>
					<?php 
						foreach($groups as $group)
						{
							$group_name = $group->name;
							$group_id = $group->id;
							
							echo "<tr>";
							echo "<td><a href=\"".url::site()."admin/simplegroups_settings/edit/".$group_id."\">".$group_name."</td>";
							$value = isset($user_counts[$group_id]) ? $user_counts[$group_id] : "0";
							echo "<td><a href=\"".url::site()."admin/simplegroups_settings/edit/".$group_id."\">".$value."</td>";
							$value = isset($report_counts[$group_id]) ? $report_counts[$group_id] : "0";
							$link = url::site()."admin/simplegroups/reportssuper?sg=".$group_id;
							echo "<td><a href=\"$link\">$value</a></td>";
							echo "</tr>";
						}
					?>
			</tbody>
		</table>
</div>
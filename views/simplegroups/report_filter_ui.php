<h3>
	<a href="#" class="small-link-button f-clear reset" onclick="simpleGroupsRemoveParameterKey('sg', 'fl-simpleGroups');">
		<?php echo Kohana::lang('ui_main.clear'); ?>
	</a>
	<a class="f-title" href="#"><?php echo Kohana::lang('simplegroups.groups_plain'); ?></a>
</h3>
<div class="f-simpleGroups-box">
	<ul class="filter-list fl-simpleGroups">
		<li>
			<?php echo $group_name . " "; print form::checkbox('simple_group_filter_checkbox', $group_id, TRUE, "onchange='simpleGroupsFilterToggle(".$group_id.");'");?>
		</li>
		<li>
			<?php echo $group_name. " ". Kohana::lang('ui_main.categories');?>:
			<br/>
			<?php echo groups_category::tree($group_categories, $selected_group_categories, 'simple_group_category', 2, true); ?>
		</li>
	</ul>
	
	
</div>
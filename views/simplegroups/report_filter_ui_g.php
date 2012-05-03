<h3>
	<a href="#" class="small-link-button f-clear reset" onclick="simpleGroupsRemoveParameterKey('sg', 'fl-simpleGroups');">
		<?php echo Kohana::lang('ui_main.clear'); ?>
	</a>
	<a class="f-title" href="#"><?php echo Kohana::lang('simplegroups.groups_plain'); ?></a>
</h3>
<div class="f-simpleGroups-box">
	<ul class="filter-list fl-simpleGroups">
	<?php foreach($groups as $group) { ?>
		<li>
			<?php echo $group->name . " "; print form::radio('simple_group_filter_radio', $group->id, FALSE, "onchange='simpleGroupsFilterToggle(".$group->id.");'");?>
			<br/>
				<?php /*
				$categories = ORM::factory('simplegroups_category')			
				->where('parent_id', '0')
				->where('applies_to_report', 1)
				->where('category_visible', 1)
				->where('simplegroups_groups_id', $group->id)
				->orderby('category_title', 'ASC')
				->find_all();
				
				echo groups_category::tree($categories, array(), 'simple_group_category', 2, true); 
				*/
				?>
			
		</li>		
	<?php } ?>
		<li>
			<?php echo "No Group "; print form::radio('simple_group_filter_radio', 0, TRUE, "onchange='simpleGroupsFilterToggle(0);'");?>
		</li>
	</ul>
	
	
</div>
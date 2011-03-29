<div >

	<h2>Categories For Message:</h2>
	<h3><?php echo $message->message ?></h3>	
	<div class="row" style="text-align:left;">
		<h4>
			<br/>
			<?php echo $group_name ." ".  Kohana::lang('simplegroups.group')." ".Kohana::lang('ui_main.categories');?> : 
		</h4>
		<?php print $new_category_toggle_js; ?>
		<!--category_add form goes here-->
		<div class="message_category">
			<?php 
				print form::open(NULL, array('id' => 'message_categories', 'name' => 'message_categories'));
			
				$selected_categories = array();
				$selected_categories = $message_category;
				$columns = 2;
				echo groups_category::tree($categories, $selected_categories, 'incident_category', $columns);
				
				print form::close();
			?>
			 
		</div>		
	</div>
	<div class="btns">
		<ul>
			<li>
				<a href="#" onclick="saveCategories(<?php echo $message->id; ?>); return false; " class="btn_save">Save Categories</a>
			</li>	
		</ul>
	</div>
</div>
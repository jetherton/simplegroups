<div class="bg">
	<h2>
		<?php groups::settings_subtabs("edit group"); ?>
	</h2>
</div>
	<?php print form::open(NULL, array('enctype' => 'multipart/form-data', 'id' => 'reportForm', 'name' => 'reportForm')); ?>
		<input type="hidden" name="save" id="save" value="">
		<!-- report-form -->
		<div class="report-form">
			<?php
			if ($form_error) {
			?>
				<!-- red-box -->
				<div class="red-box">
					<h3><?php echo Kohana::lang('ui_main.error');?></h3>
					<ul>
					<?php
					foreach ($errors as $error_item => $error_description)
					{
						print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
					}
					?>
					</ul>
				</div>
			<?php
			}

			if ($form_saved) {
			?>
				<!-- green-box -->
				<div class="green-box">
					<h3><?php echo Kohana::lang('ui_main.report_saved');?></h3>
				</div>
			<?php
			}
			?>
			<div class="head">
				<h3><?php echo $id ? "Edit Group" : "New Group"; ?></h3>
				<div class="btns" style="float:right;">
					<ul>
						<li><a href="#" class="btn_save"><?php echo strtoupper("Save Settings");?></a></li>						
					</ul>
				</div>
			</div>
			<!-- f-col -->
			<div>

				
				<div class="row">
					<h4>Group Name</h4>
					<?php print form::input('name', $form['name'], ' class="text title"'); ?>
				</div>
				<div class="row">
					<h4>Group Description<span></span></h4>
					<?php print form::textarea('description', $form['description'], ' rows="17" cols="80" style="width:800px; height:300px;"') ?>
				</div>
				<div class="row">
					<h4>Group's Own Ushahidi FrontlineSMS URL<span><br/>Optional. Must be in the format http://myhost/frontlinesms/?key=*MY_KEY*&s=${sender_number}&m=${message_content}</span></h4>
					<?php print form::input('own_instance', $form['own_instance'], ' class="text title"'); ?>
				</div>
				<!-- Photo Fields -->
				<div class="row link-row">
					<h4>Logo</h4>
					<?php								
					if ($logo_file)
					{
						print "<div class=\"report_thumbs\" id=\"photo_1\">";

						$thumb = $logo_file."_t.jpg";
						$logo_link = $logo_file.".jpg";
						$prefix = url::base().Kohana::config('upload.relative_directory')."/groups";
						print "<a class='photothumb' rel='lightbox-group1' href='$prefix/$logo_link'>";
						print "<img src=\"$prefix/$thumb\" >";
						print "</a>";
						print "</div>";
					}?>
				</div>
				<div id="divPhoto">
					<?php
				
					print "<div class=\"row link-row\">";
					print form::upload('logo');
					print "</div>";
					?>
				</div>
			</div>
			
			<div class="row">
				<h4>Group Users</h4>
				<table class="table">
					<thead>
						<tr><th>User</th><th colspan="<?php //echo count($group_roles); ?>">User's Roles</th></tr>
					</thead>
				<?php
					foreach($users as $user)
					{
						/*
						$checked = false;
						$value = "false";
						$role_enabled = "DISABLED";
						$role_style = "style=\"background:#eee; color:#888;\"";
						if($user->simplegroups_groups_id != null)
						{
							$checked = true;
							$value = "true";
							$role_enabled = "";
							$role_style = "";
						}
						*/
						echo "<tr>";
						echo "<td><span style=\"font-size:150%;\">";
						echo "<a href=\"".url::base()."admin/simplegroups/users/edit/".$user->id."\">".$user->name. "</a>";
						echo "</span>";						
						echo "</td><td id=\"role_row_".$user->id."\" >";
						
						
						foreach($group_roles as $role)
						{	
							//should this be checked?
							$role_checked = false;
							$role_value = "false";
							if(isset($group_users_roles[$user->id]) && isset($group_users_roles[$user->id][$role->id]))
							{
								$role_checked = true;
								$role_value = "true";
							}
							print form::label('role_id_'.$user->id.'_'.$role->id, $role->name);
							print form::checkbox('role_id_'.$user->id.'_'.$role->id, $role_value, $role_checked);	
							echo "&nbsp;&nbsp;&nbsp;";
						}
						
						echo "</td>";
						echo "</tr>";
						
					}
				?>
				</table>
				<br/>
			</div>
			
			<div class="row">
				<h4>White Listed Phone Numbers<span><br/>Enter phone numbers that are allowed to send in SMSs to this group.  
				<br/>Numbers must be in the exact same format as when they're recieved.</span></h4>
				<table style="width:500px;" border="1" id="white_list_table">
				<tr><th>Number</th><th>Name</th><th>Organization</th><th></th></tr>
				<?php
					$last_id = 1;
					foreach($whitelist as $item)
					{
						echo "<tr id=\"white_list_item_".$last_id."\">";
						echo '<td style="width:150px;"><input type="text" id="white_list_number_'.$last_id.'" name="white_list_number_'.$last_id.'" value="'.$item->number.'"></td>';
						echo '<td style="width:150px;"><input type="text" id="white_list_name_'.$last_id.'" name="white_list_name_'.$last_id.'" value="'.$item->name.'"></td>';
						echo '<td style="width:150px;"><input type="text" id="white_list_org_'.$last_id.'" name="white_list_org_'.$last_id.'" value="'.$item->org.'"></td>';
						echo '<td style="width:50px;"><a href="#" id="whitelistdelete_'.$last_id.'">delete</a></tr>';
						$last_id++;
					}
				?>
				</table>
				
				<?php echo '<input type="hidden" name="white_list_id" value="'.$last_id.'" id="white_list_id">'; ?>
				
				<div class="btns">
				<ul>
					<li><a href="#" class="btn_addNumber"><?php echo strtoupper("Add New Number");?></a></li>		
				</ul>
			</div>
			</div>
			
			<hr/>
			
			
			
			<div class="btns">
				<ul>
					<li><a href="#" class="btn_save"><?php echo strtoupper("Save Settings");?></a></li>
				</ul>
			</div>						
		</div>
	<?php print form::close(); ?>
	
</div>
</div>

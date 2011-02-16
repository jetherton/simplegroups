<?php 

?>
	<script type="text/javascript">
		
		function checkRole()
		{
			var roleName = $('#role').val();
			
			var groupId = $('#group').val();
			
			if(roleName != "simplegroups" && groupId != "NONE")
			{
				alert("To assign a group to a user they must have the role of 'SIMPLEGROUPS'");
				$('#group').val("NONE");
				return false;
			}
		}
		
		
		$('#role').change(function() {
				OnRoleChange();
			});
			
		
		//check to make sure the simple user stuff can't be accessed unless it should be
		function OnRoleChange()
		{
			if( $('#role').val() == "simplegroups")
			{
				$('#group').removeAttr("disabled");
				$('.group_role').removeAttr("disabled");
				
				$('.group_settings').css("background", "#fff");
				$('.group_settings').css("color", "#404040");
			}
			else
			{
				$('#group').val("NONE");
				$('#group').attr("disabled", true);
				
				$('.group_role').attr("disabled", true);
				$('.group_role').attr("checked", false);
				
				
				$('.group_settings').css("background", "#eee");
				$('.group_settings').css("color", "#888");
			}
			return false;
		}
		
		$(document).ready(function (){
			OnRoleChange();
		});
		
	</script>

	
	<div class="row group_settings">
			<h4>Group User should belong to:<span> <br/>For user to be assigned to a group, the user must have the "SIMPLEGROUPS" role.</span></h4>
			<?php print form::dropdown('group', $groups, $user_group_id, ' onchange="checkRole(); return false;"'); ?>

	</div>
	
	<div class="row group_settings">
			<h4>Group Roles:<span> <br/>If the user is a group user, these are the group roles the user could have.</span></h4>
			<?php 
				foreach($roles as $role)
				{
					//is the checkbox checked?
					$role_checked = false;
					$role_value = "false";
					if(isset($users_roles[$role->id]))
					{
						$role_checked = true;
						$role_value = "true";
					}
					print form::label('group_role_id_'.$role->id, $role->name);
					print form::checkbox('group_role_id_'.$role->id, $role_value, $role_checked, "class=\"group_role\"");	
					echo "&nbsp;&nbsp;&nbsp;";
				}
			?>

	</div>
	
		
		
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
		
	</script>

	
	<div class="row">
			<h4>Group User should belong to:<span> <br/>For user to be assigned to a group, the user must have the "SIMPLEGROUPS" role.</span></h4>
			<?php print form::dropdown('group', $groups, $user_group_id, ' onchange="checkRole(); return false;"'); ?>

		</div>
		
		
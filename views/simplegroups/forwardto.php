<script type="text/javascript"> 
function forwardMessage<?php echo $message_id; ?>()
{
	var groupId = $("#input_forwardto_<?php echo $message_id; ?>").val();
	
	$.get("<?php echo url::site()."admin/simplegroups/forwardto/index/".$message_id."/"; ?>" + groupId);
	var forwarded = document.getElementById("forwarded_<?php echo $message_id; ?>");
	forwarded.style.display = "block";
	return false;
}

</script>
<?php
	$assigned_groups_text = "";
	$count = 0;
	foreach($assigned_groups as $assigned_group)
	{
		$count++;
		if($count > 1)
		{
			$assigned_groups_text = $assigned_groups_text.", ";
		}
		$assigned_groups_text = $assigned_groups_text.  "<a href=\"".url::site()."admin/simplegroups_settings/edit/".$assigned_group->id."\">".$assigned_group->name."</a>";
	}
	
	if($assigned_groups_text != "")
	{
		echo "Assigned to group(s): ". $assigned_groups_text. "<br/>";
	}
?>
Forward To:
<?php print form::dropdown("input_forwardto_".$message_id, $groups_array, 'standard'); ?>
<a href="javascript:forwardMessage<?php echo $message_id; ?>()"  style="border: #d1d1d1 1px solid; background-color:#F2F7Fa; color: #5c5c5c; padding: 0px 9px; line-height:24px; text-decoration:none;">Forward</a>
<span id="forwarded_<?php echo $message_id; ?>"style="width: 70px; display:none; color:#555; background-color:#d8f1d8; border: 2px solid #a7d1a7;">Forwarded</span>
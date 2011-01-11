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
Forward To:
<?php print form::dropdown("input_forwardto_".$message_id, $groups_array, 'standard'); ?>
<a href="javascript:forwardMessage<?php echo $message_id; ?>()"  style="border: #d1d1d1 1px solid; background-color:#F2F7Fa; color: #5c5c5c; padding: 0px 9px; line-height:24px; text-decoration:none;">Forward</a>
<span id="forwarded_<?php echo $message_id; ?>"style="width: 70px; display:none; color:#555; background-color:#d8f1d8; border: 2px solid #a7d1a7;">Forwarded</span>
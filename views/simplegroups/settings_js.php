<?php
/**
 * Edit reports js file.
 *
 * Handles javascript stuff related to edit report function.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     API Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>
		
			
		/* Form Actions */
		
		// Action on Save Only
		$('.btn_addNumber').live('click', function () {
			var id = $("#white_list_id").val();
			$("#white_list_table").append("<tr id=\"white_list_item_"+id+"\">"
				+"<td style=\"width:150px;\"><input type=\"text\" id=\"white_list_number_"+id+"\" name=\"white_list_number_"+id+"\" value=\"\"></td>"
				+"<td style=\"width:150px;\"><input type=\"text\" id=\"white_list_name_"+id+"\" name=\"white_list_name_"+id+"\" value=\"\"></td>"
				+"<td style=\"width:150px;\"><input type=\"text\" id=\"white_list_org_"+id+"\" name=\"white_list_org_"+id+"\" value=\"\"></td>"
				+"<td style=\"width:50px;\"><a href=\"#\" id=\"whitelistdelete_"+id+"\">delete</a></td></tr>");
			id = (id - 1) + 2;
			$("#white_list_id").val(id);
			return false;
		});
	
		//removes things from the list of white listed numbers
		$("a[id^='whitelistdelete_']").live('click', function()
		{
			var ID = this.id.substring(16);
			$("#white_list_item_" + ID).remove();
			return false;
		});
		
		// Action on Save Only
		$('.btn_save').live('click', function () {
			$("#save").attr("value", "1");
			$(this).parents("form").submit();
			return false;
		});
		
		//Save and Close
		$('.btn_save_close').live('click', function () {
			$(this).parents("form").submit();
			return false;
		});
		
		// Delete Action
		$('.btn_delete').live('click', function () {
			var agree=confirm("Are You Sure You Want To DELETE item?");
			if (agree){
				$('#reportMain').submit();
			}
			return false;
		});
		
		
		// User Clicked action
		function userClicked(id)
		{
			if($('#user_id_'+id).attr('checked'))
			{
				$('#role_row_'+id).css("background", "#fff");
				$('#role_row_'+id).css("color", "#555");
				var kids = $('#role_row_'+id).find(':checkbox');
				kids.each(function()
				{
					$(this).removeAttr("disabled");
				});
			}
			else
			{
				$('#role_row_'+id).css("background", "#eee");
				$('#role_row_'+id).css("color", "#888");
				var kids = $('#role_row_'+id).find(':checkbox');
				kids.each(function()
				{
					$(this).attr("disabled", true);
					$(this).attr("checked", false);
				});
			}
			
			return false;
		}
		
			
		
		// Initialize tinyMCE Wysiwyg Editor
		tinyMCE.init({
		convert_urls : false,
		relative_urls : false,
		mode : "exact",
		elements : "description",
		theme : "advanced",
		plugins : "pagebreak,advhr,advimage,advlink,iespell,inlinepopups,contextmenu,paste,directionality,noneditable,advlist",
		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "outdent,indent,blockquote,|,undo,redo,|,link,unlink,image,code,|,forecolor,backcolor",
		theme_advanced_buttons3 : "cut,copy,paste,pastetext,pasteword,|,hr,removeformat,visualaid,|,sub,sup,|,advhr,|,ltr,rtl",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left"
		});
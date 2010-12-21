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
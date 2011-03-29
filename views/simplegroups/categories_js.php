/**
 * Categories js file.
 * 
 * Handles javascript stuff related to category function.
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
 
             $(function() {
                $('span[title]').hovertip();
            });

// Categories JS
function fillFields(id, parent_id, category_title, category_description, category_color, locale<?php foreach($locale_array as $lang_key => $lang_name) echo ', '.$lang_key; ?>, category_visible, applies_to_report, applies_to_message, selected_by_default)
{
	show_addedit();
	$("#category_id").attr("value", unescape(id));
	$("#parent_id").attr("value", unescape(parent_id));
	$("#category_title").attr("value", unescape(category_title));
	$("#category_description").attr("value", unescape(category_description));
	$("#category_color").attr("value", unescape(category_color));
	$("#locale").attr("value", unescape(locale));
	<?php
		foreach($locale_array as $lang_key => $lang_name) {
			echo '$("#category_title_'.$lang_key.'").attr("value", unescape('.$lang_key.'));'."\n";
		}
	?>
	
	//visible
	if(category_visible == 1)
	{
		$("#category_visible").attr("checked", true);
	}	
	else
	{
		$("#category_visible").attr("checked", false);
	}
	
	//Applies to Report
	if(applies_to_report == 1)
	{
		$("#applies_to_report").attr("checked", true);
	}	
	else
	{
		$("#applies_to_report").attr("checked", false);
	}
	
	//Applies to Message
	if(applies_to_report == 1)
	{
		$("#applies_to_message").attr("checked", true);
	}	
	else
	{
		$("#applies_to_message").attr("checked", false);
	}
	
	//Selected by Default
	if(selected_by_default == 1)
	{
		$("#selected_by_default").attr("checked", true);
	}	
	else
	{
		$("#selected_by_default").attr("checked", false);
	}
} //end method



// Ajax Submission
function catAction ( action, confirmAction, id )
{
	var statusMessage;
	var answer = confirm('Are You Sure You Want To ' 
		+ confirmAction )
	if (answer){
		// Set Category ID
		$("#category_id_action").attr("value", id);
		// Set Submit Type
		$("#category_action").attr("value", action);
		// Submit Form
		$("#catListing").submit();
	}
}
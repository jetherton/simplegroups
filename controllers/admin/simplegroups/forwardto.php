<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This controller is used for the main Admin panel
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Admin Dashboard Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class Forwardto_Controller extends Admin_Controller
{
    function __construct()
    {
        parent::__construct();
    }



	function index($message_id, $group_id)
	{
		//if the person is a group user don't show them this:
		$user = new User_Model($_SESSION['auth_user']->id);
		//figure out what group this user is
		//if they're not a groupie, then quit
		$users_group_id = groups::get_user_group($user);
		if($users_group_id)
		{
			return;
		}   

	
	$this->auto_render = FALSE;
	$this->template = "";
	
	$dont_add = false;
	//check if this link already exists
	$group_messages = ORM::factory("simplegroups_groups_message")
		->where("message_id", $message_id)
		->where("simplegroups_groups_id", $group_id)
		->find_all();
	foreach($group_messages as $group_message)
	{
		$dont_add = true;
		break;
	}
	
	if(!$dont_add)
	{
		$group_message = ORM::factory("simplegroups_groups_message");
		$group_message->message_id = $message_id;
		$group_message->simplegroups_groups_id = $group_id;
		$group_message->save();
		
		//get the message so we can forward it to the groups own site
		$message = ORM::factory("message", $message_id);
		
		groups::forward_message_to_own_instance($message->message, $message->message_from, $group_id);
		
		
		//check and see if we need to assign some categories to this
		$group_categories = ORM::factory("simplegroups_category")
			->where("simplegroups_groups_id", $group_id)
			->where("applies_to_message", "1")
			->where("selected_by_default", "1")
			->find_all();
		foreach($group_categories as $group_category)
		{
			$category_instance = ORM::factory("simplegroups_message_category");
			$category_instance->simplegroups_category_id = $group_category->id;
			$category_instance->message_id = $message_id;
			$category_instance->save();
		}
	}
	//figure out which groups now are associated with this messgage


	$assigned_groups = ORM::factory("simplegroups_groups")
		->join("simplegroups_groups_message", "simplegroups_groups_message.simplegroups_groups_id", "simplegroups_groups.id")
		->where("simplegroups_groups_message.message_id", $message_id)
		->find_all();

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
		echo " Assigned to group(s): ". $assigned_groups_text;
	}


	}//end index()

}
?>

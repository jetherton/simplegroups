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



	function index($id, $item_type, $group_id)
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
	
	//check if this link bewteen item and group already exists
	$group_messages = ORM::factory("simplegroups_groups_".$item_type)
		->where($item_type."_id", $id)
		->where("simplegroups_groups_id", $group_id)
		->find_all();
	foreach($group_messages as $group_message)
	{
		$dont_add = true;
		break;
	}

	if(!$dont_add)
	{
		$group_item = ORM::factory("simplegroups_groups_".$item_type);
		if($item_type == "message")
		{
			$group_item->message_id = $id;
		}
		elseif($item_type == "incident")
		{
			$group_item->incident_id = $id;
		}
		$group_item->simplegroups_groups_id = $group_id;
		$group_item->save();
		
		if($item_type == "message") //send the message to the groups site, if they have their own site
		{
			//get the message so we can forward it to the groups own site
			$message = ORM::factory("message", $id);		
			groups::forward_message_to_own_instance($message->message, $message->message_from, $group_id);
		}
		
		//check and see if we need to assign some categories to this
		$group_categories = ORM::factory("simplegroups_category")
			->where("simplegroups_groups_id", $group_id);
		if($item_type == "message")
		{
			$group_categories = $group_categories->where("applies_to_message", "1");
		}
		elseif($item_type == "incident")
		{
			$group_categories = $group_categories->where("applies_to_report", "1");
		}
		$group_categories->where("selected_by_default", "1")->find_all();
		
		foreach($group_categories as $group_category)
		{
			$category_instance = ORM::factory("simplegroups_".$item_type."_category");
			$category_instance->simplegroups_category_id = $group_category->id;
			if($item_type == "message")
			{
				$category_instance->message_id = $id;
			}
			elseif($item_type == "incident")
			{
				$category_instance->incident_id = $id;
			}
			$category_instance->save();
		}
	}
	//figure out which groups now are associated with this messgage


	$assigned_groups = ORM::factory("simplegroups_groups")
		->join("simplegroups_groups_".$item_type, "simplegroups_groups_".$item_type.".simplegroups_groups_id", "simplegroups_groups.id")
		->where("simplegroups_groups_".$item_type.".".$item_type."_id", $id)
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

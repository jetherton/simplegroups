<?php defined('SYSPATH') or die('No direct script access.');
/**
 * smsautomate Hook - Load All Events
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class simplegroups {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
	
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
		$this->table_prefix = Kohana::config('database.default.table_prefix');
		$this->post_data = null; //initialize this for later use
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		//sends group users to the group only part of the site
		Event::add('ushahidi_action.admin_header_top_left', array($this, '_check_for_group'));	

		//checks incoming SMSs for whitelist numbers and then updates the groups messages accordingly
		Event::add('ushahidi_action.message_sms_add', array($this, '_incoming_sms'));
		
		
		//whenever a report detail is being drawn add this for some credit
		Event::add('ushahidi_action.report_meta', array($this, '_give_credit_front_end'));
		Event::add('ushahidi_action.report_form_admin', array($this, '_give_credit'));
		Event::add('ushahidi_action.report_form_admin', array($this, '_get_number_info_for_report'));		
		
		//add a link to a page that shows which groups are using this site.
		Event::add('ushahidi_action.nav_main_top', array($this, '_public_group_list_link'));
		
		//creates dashboard view of stuff
		Event::add('ushahidi_action.dashboard_content', array($this, '_groups_dashboard'));		
		
		//adds info about the person that sent in messages
		Event::add('ushahidi_action.message_extra_admin',array($this, '_get_number_info_for_message'));
		
		//hook into the message action hook so we can add the "forward too" action
		Event::add('ushahidi_action.message_extra_admin',array($this, '_add_forward_to'));
		
		//hook into the generation of the list of reports and add a "forward too" button, just like what messages has
		Event::add('ushahidi_action.report_extra_admin',array($this, '_add_forward_to')); 
		
		//hook into the user edit page so we can choose which group a user is in when they are created
		Event::add('ushahidi_action.users_form_admin',array($this, '_edit_user_form'));
		
		//hook into the user edit page post being tested
		Event::add('ushahidi_action.user_submit_admin', array($this, '_edit_user_submit'));
		
		//hook into the user being saved so we can save our change of user
		Event::add('ushahidi_action.user_edit', array($this, '_edit_user'));
	}
	
	/*************************************
	 * Saves changes to the group assignment of a
	 * user that's being edited
	 ************************************/
	 public function _edit_user()
	 {
		$user = Event::$data;

		//clear any group assocations they may already have
		$group_users = ORM::factory("simplegroups_groups_users")
			->where("users_id", $user->id)
			->find_all();
		foreach($group_users as $group_user)
		{
			$group_user->delete();
		}
		
		//check and see if the person doing the editing is a group user
		$current_user = new User_Model($_SESSION['auth_user']->id);
		$group_id = groups::get_user_group($current_user);
		if($group_id)
		{
			$group_user = ORM::factory("simplegroups_groups_users");
			$group_user->simplegroups_groups_id = $group_id;
			$group_user->users_id = $user->id;
			$group_user->save();
		}
		else
		{
			//check and see if they selected a group
			if(isset($this->post_data->group) && $this->post_data->group != "NONE")
			{
				$group_user = ORM::factory("simplegroups_groups_users");
				$group_user->simplegroups_groups_id = $this->post_data->group;
				$group_user->users_id = $user->id;
				$group_user->save();
			}
		}
		
		//clear out any group roles for this user
		$users_group_roles = ORM::factory("simplegroups_users_roles")
			->where("users_id", $user->id)
			->find_all();
		foreach($users_group_roles as $users_group_role)
		{
			$users_group_role->delete();
		}
		
		//if any new group roles have been added add them
		foreach($this->post_data as $key=>$item)
		{			
			if (strpos($key, "group_role_id_") !== false)
			{
				$group_role_id = substr($key, 14);
				$users_group_role = ORM::factory("simplegroups_users_roles");
				$users_group_role->users_id = $user->id;
				$users_group_role->roles_id = $group_role_id;
				$users_group_role->save();
			}
		}
		
	 }//end of _edit_user()
	
	/*************************************
	 * Saves changes to the group assignment of a
	 * user that's being edited
	 ************************************/
	public function _edit_user_submit()
	{
		$this->post_data = Event::$data;		
	}//end of _edit_user_submit
	
	/*************************************
	 * Adds a drop down box to pick which
	 * group a  user is a part of when you're
	 * editing a user.
	 ************************************/
	 public function _edit_user_form()
	 {
		$groups_array = null;
		$user_group_id = "NONE";
		$user_id = Event::$data;
		
		//check and see if the person doing the editing is a group user
		$current_user = new User_Model($_SESSION['auth_user']->id);
		$group_id = groups::get_user_group($current_user);
		if($group_id)
		{
			$group = ORM::factory("simplegroups_groups")
				->where("id", $group_id)
				->find();
			$groups_array = array($group->id => $group->name);
		}
		else
		{
			//get a list of all the groups
			$groups = ORM::factory("simplegroups_groups")
				->find_all();
			$groups_array = array("NONE"=>"--No Group--");
			foreach($groups as $group)
			{
				$groups_array[$group->id] = $group->name;
			}
		
			//find out if our user in question is a group members
			$users_groups = ORM::factory("simplegroups_groups")
				->join('simplegroups_groups_users', 'simplegroups_groups_users.simplegroups_groups_id', 'simplegroups_groups.id')
				->where('simplegroups_groups_users.users_id', $user_id)
				->find_all();
			
			
			foreach($users_groups as $users_group)
			{
				$user_group_id = $users_group->id;
			}
		}
		
		//get list of group roles
		$roles = groups::get_group_roles();
		
		//get list of roles for this particular user
		$users_roles = groups::get_roles_for_user($user_id);

		$view = new View('simplegroups/edit_user');
		$view->groups = $groups_array;
		$view->roles = $roles;
		$view->users_roles = $users_roles;
		$view->user_group_id = $user_group_id;
		$view->render(TRUE);

}//end _edit_user()
	
	
	/**************************************
	* Makes a by line for the sender
	* of a message sent by a group memeber that became a report
	***************************************/
	public function _add_forward_to()
	{
		//if the person is a group user don't show them this:
		$user = new User_Model($_SESSION['auth_user']->id);
		//figure out what group this user is
		//if they're not a groupie, then quit
		$group_id = groups::get_user_group($user);
		if($group_id)
		{
			return;
		}
		
		//get a list of groups that this item could be sent to:
		
		$groups = ORM::factory("simplegroups_groups")->find_all();
			$groups_array = array();
			foreach($groups as $group)
			{
				$groups_array[$group->id] = $group->name;
			}
		
		//get the id of the item in question
		$id = Event::$data;
		
		//if we're looking at a message...
		if (Router::$controller == 'messages')
		{
			$item_type = "message";
		}
		elseif (Router::$controller == 'reports')
		{
			$item_type = "incident";
		}
		else //something when wrong so get out of here
		{
			return; 
		}
			
		//figure out if the current message/incident has already been assigned to a group
		
		$assigned_groups = ORM::factory("simplegroups_groups")
			->join("simplegroups_groups_$item_type", "simplegroups_groups_$item_type.simplegroups_groups_id", "simplegroups_groups.id")
			->where("simplegroups_groups_$item_type.".$item_type."_id", $id)
			->find_all();
		
	
		$view = new View('simplegroups/forwardto');
		$view->message_id = $id;
		$view->assigned_groups = $assigned_groups;
		$view->groups_array = $groups_array;
		$view->item_type = $item_type;
		$view->render(TRUE);
	}

	
	
	/**************************************
	* Makes a by line for the sender
	* of a message sent by a group memeber that became a report
	***************************************/
	public function _get_number_info_for_report()
	{
		$report_id = Event::$data;
		
		$number_items = ORM::factory("simplegroups_groups_number")
			->join("simplegroups_groups_incident", "simplegroups_groups_incident.number_id", "simplegroups_groups_numbers.id")
			->where("simplegroups_groups_incident.incident_id", $report_id)
			->find_all();
		foreach($number_items as $number_item)
		{
			$view = new View('simplegroups/number_info_report');
			$view->name = $number_item->name;
			$view->org = $number_item->org;
			$view->render(TRUE);
		}
	}
	
	
	
	/**************************************
	* Makes a by line for the sender
	* of a message sent by a group memeber
	***************************************/
	public function _get_number_info_for_message()
	{
		$message_id = Event::$data;
		
		$number_items = ORM::factory("simplegroups_groups_number")
			->join("simplegroups_groups_message", "simplegroups_groups_message.number_id", "simplegroups_groups_numbers.id")
			->where("simplegroups_groups_message.message_id", $message_id)
			->find_all();
		foreach($number_items as $number_item)
		{
			$view = new View('simplegroups/number_info_message');
			$view->name = $number_item->name;
			$view->org = $number_item->org;
			$view->render(TRUE);
		}
	}
	
	/**************************************
	* Creates a dashboard widget with info
	* on groups
	**************************************/
	public function _groups_dashboard
	()
	{
		$user_counts_array = array();
		$report_counts_array = array();
		
		$view = new View('simplegroups/simplegroups_dashboard');
		
		//get a list of groups
		$groups = ORM::factory('simplegroups_groups')
			->orderby("name", "ASC")
			->find_all();
		
		
		$users_counts = ORM::factory('simplegroups_groups_users')
			->select("simplegroups_groups_users.*, COUNT(simplegroups_groups_users.simplegroups_groups_id) as user_count")
			->groupby("simplegroups_groups_users.simplegroups_groups_id")
			->find_all();
			
		foreach($users_counts as $user_count)
		{
			$user_counts_array[$user_count->simplegroups_groups_id] = $user_count->user_count;
		}
			
		$reports_counts = ORM::factory('simplegroups_groups_incident')
			->select("simplegroups_groups_incident.*, COUNT(simplegroups_groups_incident.simplegroups_groups_id) as report_count")
			->groupby("simplegroups_groups_incident.simplegroups_groups_id")
			->find_all();
			
		foreach($reports_counts as $reports_count)
		{
			$report_counts_array[$reports_count->simplegroups_groups_id] = $reports_count->report_count;
		}
	

		$view->groups = $groups;
		$view->user_counts = $user_counts_array;
		$view->report_counts = $report_counts_array;
		
		$view->render(TRUE);
	}//end method
	
	/**************************************
	* Puts a link to the list of groups on the 
	* front end
	***************************************/
	public function _public_group_list_link()
	{
		$this_page = url::current();
		$menu = "<li><a href=\"".url::site()."simplegroups/groups\" ";
		$menu .= ( strpos($this_page, "simplegroups") !== false) ? " class=\"active\"" : "";
	 	$menu .= ">GROUPS</a></li>";
		echo $menu;
	}
	
	
	/**************************************
	* Puts a little by line in for our estemeed
	* groups
	**************************************/
	public function _give_credit($is_front_end = false)
	{
		$report_id = Event::$data;
		//check and see if this is a group report
		$group_reports = ORM::factory("simplegroups_groups")
			->join($this->table_prefix."simplegroups_groups_incident", $this->table_prefix."simplegroups_groups.id", $this->table_prefix."simplegroups_groups_incident.simplegroups_groups_id")
			->where($this->table_prefix."simplegroups_groups_incident.incident_id", $report_id)
			->find_all();
			
		foreach($group_reports as $group_report)
		{
						
			$credit = View::factory('simplegroups/credit');
			if(!$is_front_end)
			{
				$credit->categories = array();	
			}
			else
			{
				$categories = ORM::factory("simplegroups_category")
					->join($this->table_prefix."simplegroups_incident_category", $this->table_prefix."simplegroups_category.id", $this->table_prefix."simplegroups_incident_category.simplegroups_category_id")
					->where($this->table_prefix."simplegroups_incident_category.incident_id", $report_id)
					->find_all();
				$credit->categories = $categories;
			}
			$credit->group_name = $group_report->name;
			$credit->group_id = $group_report->id;
			$credit->logo_file = $group_report->logo;
			$credit->render(TRUE);
		}
	}//end of _give_credit()
	
	
	/**
	 * Used to run give credit, but formated with categories
	 */
	public function _give_credit_front_end()
	{
		$this->_give_credit(true);
	}
	
	/**************************************
	* Checks to see if the user is allowed
	* to viewed the admin map
	**************************************/
	public function _admin_map_let_view()
	{
		$user = new User_Model($_SESSION['auth_user']->id);
		//figure out what group this user is
		//if they're not a groupie, then quit
		$group_id = groups::get_user_group($user);
		if($group_id)
		{
			Event::$data = true;
		}
	}//end method _admin_map_let_view()
	
	
	
	
	/***************************************
	* Tries to match the sender of incoming SMSs
	* with a whitelisted number from a group
	***************************************/
	public function _incoming_sms()
	{
		$sms = Event::$data;
		
		//get all of the whitelisted numbers and see if there's a match
		$numbers = ORM::factory("simplegroups_groups_number")->find_all();
		foreach($numbers as $number)
		{
			if($number->number)
			{
				//makes it a fuzzy search
				if( !(strpos($sms->message_from, $number->number) === false) ||
					($number->number == $sms->message_from))
				{
					$group_message = ORM::factory("simplegroups_groups_message");
					$group_message->simplegroups_groups_id = $number->simplegroups_groups_id;
					$group_message->message_id = $sms->id;
					$group_message->number_id = $number->id;
					$group_message->save();
					
			
					
					//check and see if it needs to be forwarded
					groups::forward_message_to_own_instance($sms->message, $sms->message_from, $number->simplegroups_groups_id);
					
					//check and see if we need to assign some categories to this
					$group_categories = ORM::factory("simplegroups_category")
						->where("simplegroups_groups_id", $number->simplegroups_groups_id)
						->where("applies_to_message", "1")
						->where("selected_by_default", "1")
						->find_all();
					foreach($group_categories as $group_category)
					{
						$category_instance = ORM::factory("simplegroups_message_category");
						$category_instance->simplegroups_category_id = $group_category->id;
						$category_instance->message_id = $sms->id;
						$category_instance->save();
					}
					
					//break; //more than one number can match? good or bad?
				}
			}
		}
	}//end method
	

	/**
	 * Here we check and see if the user logged in is part of a group
	 * If they are we re direct them to only the content they can see
	 */
	public function _check_for_group()
	{
		$user = new User_Model($_SESSION['auth_user']->id);
		$group_id = groups::get_user_group($user);
		$role = ORM::factory("role")
			->join("roles_users", "roles_users.role_id", "roles.id")
			->where("roles_users.user_id", $user->id)
			->where("name", "simplegroups")
			->find();
					
		if (!$group_id) //don't belong to a group
		{
			//but do they have the role of a groupie and they're just not assigned to a group yet?
			if($role->name == "simplegroups")
			{
				url::redirect(url::site().'admin/simplegroups/nogroup');
			}
			
			return;
		}
		
		//the person is a member of a group so redirect them to the group dashboard
		url::redirect(url::site().'admin/simplegroups/dashboard');
			
	}//end method _check_for_group
	

}//end class

new simplegroups;
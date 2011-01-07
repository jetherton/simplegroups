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
		
		//intercept lists reports going to the admin map plugin and make sure they take into acocunt our groupieness
		Event::add('ushahidi_filter.admin_map_get_reports', array($this, '_admin_map_reports'));
		
		//intercept the counts of reports going to the admin map plugin and make sure they take into acocunt our groupieness
		Event::add('ushahidi_filter.admin_map_get_reports_count', array($this, '_admin_map_reports_count'));
		
		//whenever a report detail is being drawn add this for some credit
		Event::add('ushahidi_action.report_meta', array($this, '_give_credit'));
		Event::add('ushahidi_action.report_form_admin', array($this, '_give_credit'));
		Event::add('ushahidi_action.report_form_admin', array($this, '_get_number_info_for_report'));		
		
		//add a link to a page that shows which groups are using this site.
		Event::add('ushahidi_action.nav_main_top', array($this, '_public_group_list_link'));
		
		//creates dashboard view of stuff
		Event::add('ushahidi_action.dashboard_content', array($this, '_groups_dashboard'));		
		
		//adds info about the person that sent in messages
		Event::add('ushahidi_action.message_extra_admin',array($this, '_get_number_info_for_message'));
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
		$menu .= ($this_page == 'simplegroups/groups') ? " class=\"active\"" : "";
	 	$menu .= ">GROUPS</a></li>";
		echo $menu;
	}
	
	
	/**************************************
	* Puts a little by line in for our estemeed
	* groups
	**************************************/
	public function _give_credit()
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
			$credit->group_name = $group_report->name;
			$credit->group_id = $group_report->id;
			$credit->logo_file = $group_report->logo;
			$credit->render(TRUE);
		}
	}//end of _give_credit()
	
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
	* Runs our own fancy database queries 
	* to get the reports that should go to the 
	* admin map
	****************************************/
	public function _admin_map_reports()
	{
		//check if the $_SESSION variable is even set. May not be.
		if(!isset($_SESSION))
		{
			return;
		}
		$user = new User_Model($_SESSION['auth_user']->id);
		//figure out what group this user is
		//if they're not a groupie, then quit
		$group_id = groups::get_user_group($user);
		if(!$group_id)
		{
			return;
		}
		else //they are a groupie
		{
			$group_where = " AND ( ".$this->table_prefix."simplegroups_groups_incident.simplegroups_groups_id = ".$group_id.") ";
			$reports = groups::get_reports(
				Event::$data['category_ids'],
				Event::$data['approved_text'], 
				Event::$data['where_text']. $group_where, 
				Event::$data['logical_operator'],
				Event::$data['order_by'],
				Event::$data['order_by_direction'],
				Event::$data['limit'], 
				Event::$data['offset']
				);
				
			Event::$data['incidents'] = $reports;
		}
	} //end of method
	
	
	
	/***************************************
	* Runs our own fancy database queries 
	* to get the reports count that should go to the 
	* admin map
	****************************************/
	public function _admin_map_reports_count()
	{
		$user = new User_Model($_SESSION['auth_user']->id);
		//figure out what group this user is
		//if they're not a groupie, then quit
		$group_id = groups::get_user_group($user);
		if(!$group_id)
		{
			return;
		}
		else //they are a groupie
		{
			$group_where = " AND ( ".$this->table_prefix."simplegroups_groups_incident.simplegroups_groups_id = ".$group_id.") ";
			$reports_count = groups::get_reports_count(
				Event::$data['category_ids'],
				Event::$data['approved_text'], 
				Event::$data['where_text']. $group_where, 
				Event::$data['logical_operator']
				);
				
			Event::$data['incidents_count'] = $reports_count;
		}
	} //end of method
	
	
	
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
			//makes it a fuzzy search
			if( !(strpos($sms->message_from, $number->number) === false) ||
				($number->number == $sms->message_from))
			{
				$group_message = ORM::factory("simplegroups_groups_message");
				$group_message->simplegroups_groups_id = $number->simplegroups_groups_id;
				$group_message->message_id = $sms->id;
				$group_message->number_id = $number->id;
				$group_message->save();
				break;
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
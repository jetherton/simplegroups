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
		$this->incident_to_groups = null;
		$this->incident_to_group_categories = null;
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
	
		//if we're using v2.2 the makers of Ushahidi were nice enough to give a event that lets us know
		//we have a new login
		Event::add('ushahidi_action.user_login', array($this, '_check_for_group_login'));
		
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
		
		//hook into reports being added via the API so we can associate them with a group
		//if appropriate.
		if(Router::$controller == "api")
		{
			Event::add('ushahidi_action.report_submit', array($this, '_edit_api_post_incident'));
			Event::add('ushahidi_action.report_add', array($this, '_edit_api_saved_incident'));
		}
		
		//hooks for writing out CSV data
		Event::add('ushahidi_filter.report_download_csv_header', array($this, '_add_csv_headers'));
		Event::add('ushahidi_filter.report_download_csv_incident', array($this, '_add_csv_incident_info'));
		
		
		
		//if dealing with the
		if(Router::$controller == "reports")
		{
			Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_simple_group_filter'));
			
			Event::add('ushahidi_action.report_filters_ui', array($this,'_add_report_filter_ui'));
			
			Event::add('ushahidi_action.header_scripts', array($this, '_add_report_filter_js'));
		}
		if(Router::$controller == "json" || Router::$controller == "densitymap" ||
		   Router::$controller == "bigmap_json" || Router::$controller == "iframemap_json" ||
		   Router::$controller == "adminmap_json") //any time the map is brought up
		{
			Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_simple_group_filter'));
		}
		
		//changing the color of dots and lines when SG categories are in play
		Event::add('adminmap_filter.features_color', array($this, '_set_colors'));
	}
	
	
	/**
	 * Adds incident specific data to an CSV
	 */
	public function _add_csv_incident_info()
	{
		$incident = Event::$data['incident'];
		$report_csv = "";
		
		//get group name incident		
		if(isset($this->incident_to_groups[$incident->id]))
		{
			$report_csv .= ",\"". $this->incident_to_groups[$incident->id] . "\",\"";
		}
		else
		{
			$report_csv .= ",,\"";
			//if they aren't part of a group then don't bother
			//looking for group categories
			$report_csv .= "\"";
			Event::$data['report_csv'] = $report_csv;	
			return;
		}
		
		//get group categories/////////////////////////////////////////////////////////////
		if(isset($this->incident_to_group_categories[$incident->id]))
		{
			$i = 0;
			foreach($this->incident_to_group_categories[$incident->id] as $title)
			{
				$i++;
				if($i > 1){$report_csv .= ",";}
				$report_csv .= $title;
			}
		}

		$report_csv .= "\"";
		Event::$data['report_csv'] = $report_csv;
		
	}
	
	/**
	 * event for adding CSV headers to the CSV output
	 */
	public function _add_csv_headers()
	{
		$csv = Event::$data;
		$csv .= ",SIMPLE GROUP NAME,SIMPLE GROUP CATEGORIES";
		Event::$data = $csv; 
		
		/////////////////////////////////////////////////
		//preload all the incident and their groups:		
		$this->incident_to_groups = array();
		// Get the table prefix
		$table_prefix = Kohana::config('database.default.table_prefix');
		$sql = 'SELECT sc.name, sgi.incident_id FROM `'.$table_prefix.'simplegroups_groups` as sc 
					RIGHT JOIN  '.$table_prefix.'simplegroups_groups_incident as sgi
					ON sc.id = sgi.simplegroups_groups_id';
		$db = new Database();
		$rows = $db->query($sql);
		foreach($rows as $row)
		{
			$this->incident_to_groups[$row->incident_id] = $row->name;	
		}
		
		///////////////////////////////////////////////
		//preload group category info
		$this->incident_to_group_categories = array();
		$sql = 'SELECT sc.category_title, incident_id FROM '.$table_prefix.'simplegroups_category as sc
					RIGHT JOIN '.$table_prefix.'simplegroups_incident_category as sic ON sic.simplegroups_category_id = sc.id';
		$db = new Database();
		$rows = $db->query($sql);
		foreach($rows as $row)
		{
			if(isset($this->incident_to_group_categories[$row->incident_id]))
			{
				$this->incident_to_group_categories[$row->incident_id][] = $row->category_title;
			}	
			else
			{
				$this->incident_to_group_categories[$row->incident_id] = array($row->category_title);
			}
		}
	}
	
	
	/**
	 * when a report is submitted via the API this will check and see if it should be assigned to a
	 * group.
	 */
	public function _edit_api_saved_incident()
	{
		
		$this->post_data = $_POST;
		
		$incident = Event::$data;
		$group = null;
		//is there any simple groups stuff set?
		if( isset($this->post_data["sgn"]))
		{
			//they are using a simple group name
			//find the group
			$groups = ORM::factory("simplegroups_groups")
				->find_all();
			foreach($groups as $g)
			{
				$name = strtolower($g->name);
				if($name == strtolower($this->post_data["sgn"]))
				{
					$group = $g;
					break;
				}
			}
			
		}
		elseif(isset($this->post_data["sg"]))
		{
			//they are using a simple group ID
			$group = ORM::factory("simplegroups_groups")
				->where("id", $this->post_data["sg"])
				->find();
			if(!$group->loaded)
			{
				return; //no group found with that name
			}
		}
		
		if($group == null)
		{
			return;
		}
		//now link up the group and the report
		$group_incident = ORM::factory("simplegroups_groups_incident");
		$group_incident->incident_id = $incident->id;
		$group_incident->simplegroups_groups_id = $group->id;
		$group_incident->save();
	}
	
	/**
	* Grabs the post data when a report is added via the API
	 */
	public function _edit_api_post_incident()
	{
		
		$this->post_data = Event::$data;	

	}
	
	/**
	 * Creates the JS for the reports page so we can turn off and on filtering by group
	 */
	public function _add_report_filter_js()
	{
		if (isset($_GET['sgid']) AND !is_array($_GET['sgid']) AND intval($_GET['sgid']) >= 0)
		{

			$view = new View('simplegroups/report_filter_js');
			$view->selected_group_categories = implode(",", $this->_get_group_categories());
			$view->render(true);
		}
		else
		{		
			$view = new View('simplegroups/report_filter_js_g');
			$view = new View('simplegroups/report_filter_js_g');
			$view->selected_group_categories = array();
			$view->render(true);
		}
	}
	
	
	/**
	 * Creates a custom UI for filtering things in the reports page
	 * 
	 */
	public function _add_report_filter_ui()
	{
		if (isset($_GET['sgid']) AND !is_array($_GET['sgid']) AND intval($_GET['sgid']) >= 0)
		{
			$group = ORM::factory("simplegroups_groups")->where("id", $_GET['sgid'])->find();
			$view = new View('simplegroups/report_filter_ui');
			$view->group_name = $group->name;
			$view->group_id = $group->id;
			
			
			//now the categories
			$categories = ORM::factory('simplegroups_category')			
				->where('parent_id', '0')
				->where('applies_to_report', 1)
				->where('category_visible', 1)
				->where('simplegroups_groups_id', $group->id)
				->orderby('category_title', 'ASC')
				->find_all();
			$view->group_categories = $categories;
			$view->selected_group_categories = $this->_get_group_categories();
			
			$view->render(true);
		}
		else
		{
			//get a list of groups
			$groups = ORM::factory("simplegroups_groups")->find_all();
			$view = new View('simplegroups/report_filter_ui_g');
			$view->groups = $groups;
			$view->render(true);
		}
		
	}
	
	/**
	 * This little zinger does all the HTTP GET parsing to figure out what categories are in play
	 * Enter description here ...
	 */
	private function _get_group_categories()
	{
		$category_ids = array();
			
			//the case when it's just one category
			//add sql for any simplegroup categories
			if ( isset($_GET['c']) AND !is_array($_GET['c']) AND strpos($_GET['c'],"sg_") === 0)
			{
				// Get the category ID
				$category_ids[] = intval(substr($_GET['c'],3));			
			}
			elseif (isset($_GET['c']) AND is_array($_GET['c']))
			{
				// Sanitize each of the category ids
				foreach ($_GET['c'] as $c_id)
				{
					if (strpos($c_id,"sg_") === 0)
					{
						$category_ids[] = intval(substr($c_id,3));
					}
					else
					{
						$non_group_categories[] = $c_id;
					}
				}
			}
			
			
			
			return $category_ids;
	}
	
	
	/**
	 * figures out what the logical operator is
	 * defaults to OR
	 */
	private function _get_logical_operator()
	{
		$lo = "or";
		if ( isset($_GET['lo']) AND !is_array($_GET['lo']) AND strtolower($_GET['lo']) == "and" )
		{
			$lo = "and";
		}
		return $lo;
	}
	
	/********************************************
	 * Sets the reports filters to handle simple group stuff
	 */
	public function _add_simple_group_filter()
	{
		//check for the "sgid" get parameter
		if (isset($_GET['sgid']) AND !is_array($_GET['sgid']) AND intval($_GET['sgid']) >= 0)
		{
			//get the table prefix
			$table_prefix = Kohana::config('database.default.table_prefix');
			
			//get the params
			$sg_id = intval($_GET['sgid']);
			$params = Event::$data;
			array_push($params,	'i.id IN (SELECT DISTINCT incident_id FROM '.$table_prefix.'simplegroups_groups_incident WHERE simplegroups_groups_id = '. $sg_id. ')');
			
			//figure out if we're on the backend or not, and if we're not hide private categories
			$only_public = (strpos(url::current(), "admin/") === 0) ? "" : " AND sgc.category_visible = 1 "; 
			
			$category_ids = $this->_get_group_categories();			
			// Check if there are any category ids
			if (count($category_ids) > 0)
			{
				
				//what's the logical operator:
				if($this->_get_logical_operator() == "or")
				{
					//first we need to find out what the original SQL for categories looked like:
					$category_sql = $this->_create_default_category_sql();
					$i = 0;
					$found_it = false;
					foreach($params as $key=>$value)
					{
						if(strcmp($value, $category_sql) == 0)
						{
							$i = $key;
							$found_it = true;
							break;					
						}
						$i++;
					}
					//if we found it, lets remove it.
					if($found_it)
					{
						unset($params[$i]);
					}
					if(strlen($category_sql) > 0)
					{
						$category_sql = ' OR ('.$category_sql.')' ;
					}
					$category_ids = implode(",", $category_ids);
					array_push($params,
						'(i.id IN (SELECT DISTINCT incident_id FROM '.$table_prefix.'simplegroups_incident_category sgic '.
							'INNER JOIN '.$table_prefix.'simplegroups_category sgc ON (sgc.id = sgic.simplegroups_category_id) '.
							'WHERE (sgc.id IN ('. $category_ids . ') OR sgc.parent_id IN ('.$category_ids.'))'.$only_public.' ) '.$category_sql. ')');
					
					
				}
				else
				{
					foreach($category_ids as $c)
					{
						array_push($params,
						'i.id IN (SELECT DISTINCT incident_id FROM '.$table_prefix.'simplegroups_incident_category sgic '.
							'INNER JOIN '.$table_prefix.'simplegroups_category sgc ON (sgc.id = sgic.simplegroups_category_id) '.
							'WHERE ((sgc.id = '. $c . ') OR sgc.parent_id = (' . $c . '))'.$only_public.' ) ');
					}
				}
			}	
			Event::$data = $params;
		}
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
		
		
		//it seems that newer versions of Ushahidi now pass around the entier report DB object
		//and not just a string of the id
		if(!is_string($id) AND $item_type == 'incident')
		{
			$id = $id->incident_id;
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
	
	
		/**
	 * Here we check and see if the user logged in is part of a group
	 * If they are we re direct them to only the content they can see
	 * This differs from the above in that it's called right when the
	 * user is logged in and the user object is passed in by the event
	 */
	public function _check_for_group_login()
	{
		$user = Event::$data;
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
	
	
	//creates the same SQL as the reports helper, so we can then remove it later on
	private function _create_default_category_sql()
	{
		// 
		// Check for the category parameter
		//

		$category_sql = "";
		if ( isset($_GET['c']) AND !is_array($_GET['c']) AND intval($_GET['c']) > 0)
		{
			//just one category, so AND has no effect, so just return an empty string
			return "";
		}
		elseif (isset($_GET['c']) AND is_array($_GET['c']))
		{
			// Sanitize each of the category ids
			$category_ids = array();
			foreach ($_GET['c'] as $c_id)
			{
				if (intval($c_id) > 0)
				{
					$category_ids[] = intval($c_id);
				}
			}
			// Check if there are any category ids
			if (count($category_ids) > 0)
			{
				$category_ids = implode(",", $category_ids);
			
				$category_sql = '(c.id IN ('.$category_ids.') OR c.parent_id IN ('.$category_ids.'))';
			}
		}
		return $category_sql;
	}
	
	
	/************************************************************************************************
	* Function, this'll merge colors. Given an array of category IDs it'll return a hex string
	* of all the colors merged together
	*/
	public function _set_colors()
	{
		$orginal_colors = Event::$data;
		//Group categories
		$sg_cats = $this->_get_group_categories();
		$category_id = $this->_get_categories();

		
		
		//get color
  		if(((count($sg_cats) == 1 AND intval($sg_cats[0]) == 0) AND (count($category_id) == 1 AND intval($category_id[0]) == 0 )) OR 
  		(count($sg_cats) == 0 AND count($category_id) == 0))
		{
			$colors = array(Kohana::config('settings.default_map_all'));
		}		
		else 
		{	
			//more than one color
			$colors = array();
			foreach($sg_cats as $cat)
			{
				$colors[] = ORM::factory('simplegroups_category', $cat)->category_color;
			}
			foreach($category_id as $cat)
			{
				$colors[] = ORM::factory('category', $cat)->category_color;
			}			
		}
		
		//check if we're dealing with just one color
		if(count($colors)==1)
		{
			foreach($colors as $color)
			{
				Event::$data = $color;	
				return;
			}
		}
		
		//now for each color break it into RGB, add them up, then normalize
		$red = 0;
		$green = 0;
		$blue = 0;
		foreach($colors as $color)
		{
			$numeric_colors = $this->_hex2RGB($color);
			$red = $red + $numeric_colors['red'];
			$green = $green + $numeric_colors['green'];
			$blue = $blue + $numeric_colors['blue'];
		}
		//now normalize
		$color_length = sqrt( ($red*$red) + ($green*$green) + ($blue*$blue));
	
		//make sure there's no divide by zero
		if($color_length == 0)
		{
			$color_length = 255;
		}
		$red = ($red / $color_length) * 255;
		$green = ($green / $color_length) * 255;
		$blue = ($blue / $color_length) * 255;
	
		
		//pad with zeros if there's too much space
		$red = dechex($red);
		if(strlen($red) < 2)
		{
			$red = "0".$red;
		}
		$green = dechex($green);
		if(strlen($green) < 2)
		{
			$green = "0".$green;
		}
		$blue = dechex($blue);
		if(strlen($blue) < 2)
		{
			$blue = "0".$blue;
		}
		//now put the color back together and return it
		$color_str = $red.$green.$blue;
		//in case other plugins have something to say about this
		Event::$data = $color_str;		
	}//end method merge colors
	
	
	private function _hex2RGB($hexStr, $returnAsString = false, $seperator = ',') 
	{
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		$rgbArray = array();
		if (strlen($hexStr) == 6) 
		{ //If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['blue'] = 0xFF & $colorVal;
		} 
		elseif (strlen($hexStr) == 3) 
		{ //if shorthand notation, need some string manipulations
			$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		} 
		else 
		{
			return false; //Invalid hex color code
		}
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	}
	
	
	/**
	 * This little zinger does all the HTTP GET parsing to figure out what categories are in play
	 * Enter description here ...
	 */
	private function _get_categories()
	{
		$category_ids = array();
			
			if ( isset($_GET['c']) AND !is_array($_GET['c']) AND intval($_GET['c']) > 0)
			{
				// Get the category ID
				$category_ids[] = intval($_GET['c']);			
			}
			elseif (isset($_GET['c']) AND is_array($_GET['c']))
			{
				// Sanitize each of the category ids
				
				foreach ($_GET['c'] as $c_id)
				{
					if (intval($c_id) > 0)
					{
						$category_ids[] = intval($c_id);
					}
				}
			}
			
			return $category_ids;
	}
	

}//end class

new simplegroups;
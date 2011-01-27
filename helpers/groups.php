<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * simplegroups helper class.
 */
class groups_Core {


	// Table Prefix
	protected static $table_prefix;

	static function init()
	{
		// Set Table Prefix
		self::$table_prefix = Kohana::config('database.default.table_prefix');
	}
	
	
	//This will take the given message and forward it, via the FrontlineSMS URL, to 
	//another ushahidi site
	public static function forward_message_to_own_instance($message, $sender, $group_id)
	{
		//get the own_instance url
		$group_info = ORM::factory('simplegroups_groups', $group_id);
		$own_instance = $group_info->own_instance;
		
		//if $own_instance hasn't been filled out then quit
		if(strlen($own_instance) < 8)
		{
			return;
		}
		
		//url encode the parameters
		$message = urlencode($message);
		$sender = urlencode($sender);
		
		//parse out the url variables, also making sure that the format is correct
		//check for the sender's number
		if(strpos($own_instance, '${sender_number}') !== false)
		{
			$own_instance = str_replace('${sender_number}', $sender, $own_instance);
		}
		else //can't find it, it's not formatted correctly
		{
			return;
		}
		//check for the message
		if(strpos($own_instance, '${message_content}') !== false)
		{
			$own_instance = str_replace('${message_content}', $message, $own_instance);
		}
		else //can't find it, it's not formatted correctly
		{
			return;
		}
		//now make the HTTP get
		$curl_handle = curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$own_instance);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,15); // Timeout set to 15 seconds. This is somewhat arbitrary and can be changed.
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1); // Set cURL to store data in variable instead of print
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
		
	}
	
	/**
	 * Generate Messages Sub Tab Menus
     * @param int $service_id
	 * @return string $menu
     */
	public static function messages_subtabs($service_id = FALSE)
	{
		$menu = "";
		foreach (ORM::factory('service')->find_all() as $service)
		{
			if ($service->id == $service_id)
			{
				$menu .= $service->service_name;
			}
			else
			{
				$menu .= "<a href=\"" . url::site() . "admin/simplegroups/messages/index/".$service->id."\">".$service->service_name."</a>";
			}
		}
		
		echo $menu;
	}


	/**
	 * Generate Report Sub Tab Menus
     * @param string $this_sub_page
	 * @return string $menu
     */
	public static function reports_subtabs($this_sub_page = FALSE)
	{
		$menu = "";

		$menu .= ($this_sub_page == "view") ? Kohana::lang('ui_main.view_reports') : "<a href=\"".url::base()."admin/simplegroups/reports\">".Kohana::lang('ui_main.view_reports')."</a>";

		$menu .= ($this_sub_page == "edit") ? Kohana::lang('ui_main.create_report') : "<a href=\"".url::base()."admin/simplegroups/reports/edit\">".Kohana::lang('ui_main.create_report')."</a>";

		$menu .= ($this_sub_page == "comments") ? Kohana::lang('ui_main.comments') : "<a href=\"".url::base()."admin/simplegroups/comments\">".Kohana::lang('ui_main.comments')."</a>";

		echo $menu;
	}





	public static function manage_subtabs($this_sub_page = FALSE)
	{
		$menu = "";

		$menu .= ($this_sub_page == "view") ? "View Groups" : "<a href=\"".url::base()."admin/simplegroups_settings\">View Groups</a>";

		$menu .= ($this_sub_page == "edit") ? "Add/Edit Group" : "<a href=\"".url::base()."admin/simplegroups_settings/edit\">Add/Edit Groups</a>";

		echo $menu;
	}//end method


	
	/***************************************************
	* figures out what group, if any, a user is apart of.
	* if the user isn't part of a group return FALSE, otherwise 
	* return the group id number
	***************************************************/
	public static function get_user_group($user = FALSE)
	{
		If($user)
		{
			$groups = ORM::factory("simplegroups_groups_users")->where("users_id", $user->id)->find_all();
			foreach($groups as $group)
			{
				return $group->simplegroups_groups_id;
			}
		}
		return FALSE;
	}

	
	public static function permissions($user = FALSE, $section = FALSE)
	{
		if ($user AND $section)
		{
			$access = FALSE;
			foreach ($user->roles as $user_role)
			{
				if ($user_role->$section == 1)
				{
					$access = TRUE;
				}
			}
			
			return $access;
		}
		else
		{
			return false;
		}
	}//end method










	/**************************************************************************************************************
      * Given all the parameters returns a list of incidents that meet the search criteria
      */
	public static function get_reports($category_ids, $approved_text, $where_text, $logical_operator, 
		$order_by = "incident.incident_date",
		$order_by_direction = "asc",
		$limit = -1, $offset = -1)
	{
		$incidents = null;
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			
			    if($limit != -1 && $offset != -1)
			    {
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*')
					->with('location')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
					->where($approved_text.$where_text)
					->orderby($order_by, $order_by_direction)
					->find_all($limit, $offset);
			    }
			    else
			    {
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*')
					->with('location')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
					->where($approved_text.$where_text)
					->orderby($order_by, $order_by_direction)
					->find_all();
			    }
			    
			return $incidents;
		}
		
		// or up allthe categories we're interested in
		$where_category = "";
		$i = 0;
		foreach($category_ids as $id)
		{
			$i++;
			$where_category = ($i > 1) ? $where_category . " OR " : $where_category;
			$where_category = $where_category . groups_Core::$table_prefix.'incident_category.category_id = ' . $id;
		}

		
		//if we're using OR
		if($logical_operator == "or")
		{
			
			// Retrieve incidents by category			
			if($limit != -1 && $offset != -1)
			{
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
					->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->orderby($order_by, $order_by_direction)
					->find_all($limit, $offset);
			}
			else
			{
				$incidents = ORM::factory('incident')
					->select('DISTINCT incident.*')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
					->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->orderby($order_by, $order_by_direction)
					->find_all();
			}
				
			return $incidents;
		}
		else //if we're using AND
		{
		
			if($limit != -1 && $offset != -1)
			{
				// Retrieve incidents by category			
				$incidents = ORM::factory('incident')
					->select('incident.*, COUNT(incident.id) as category_count')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
					->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->groupby('incident.id')
					->having('category_count', count($category_ids))
					->orderby($order_by, $order_by_direction)
					->find_all($limit, $offset);
			}
			else
			{
				// Retrieve incidents by category			
				$incidents = ORM::factory('incident')
					->select('incident.*, COUNT(incident.id) as category_count')
					->with('location')
					->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
					->join('media', 'incident.id', 'media.incident_id','LEFT')
					->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
					->where($approved_text.' AND ('.$where_category. ')' . $where_text)
					->groupby('incident.id')
					->having('category_count', count($category_ids))
					->orderby($order_by, $order_by_direction)
					->find_all();
			}
					
			return $incidents;
		}

	}//end method	
	
	
	
	
	/**************************************************************************************************************
      * Given all the parameters returns the count of incidents that meet the search criteria
      */
	public static function get_reports_count($category_ids, $approved_text, $where_text, $logical_operator)
	{
		$incidents_count = -1;
		
		//check if we're showing all categories, or if no category info was selected then return everything
		If(count($category_ids) == 0 || $category_ids[0] == '0')
		{
			// Retrieve all markers
			
			$incidents_count = ORM::factory('incident')
				->select('DISTINCT incident.*')
				->with('location')
				->join('media', 'incident.id', 'media.incident_id','LEFT')
				->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
				->where($approved_text.$where_text)
				->count_all();
			    
			return $incidents_count;
		}
		
		// or up allthe categories we're interested in
		$where_category = "";
		$i = 0;
		foreach($category_ids as $id)
		{
			$i++;
			$where_category = ($i > 1) ? $where_category . " OR " : $where_category;
			$where_category = $where_category . groups_Core::$table_prefix.'incident_category.category_id = ' . $id;
		}

		
		//if we're using OR
		if($logical_operator == "or")
		{
			$incidents_count = ORM::factory('incident')
				->select('DISTINCT incident.*')
				->with('location')
				->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
				->join('media', 'incident.id', 'media.incident_id','LEFT')
				->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
				->where($approved_text.' AND ('.$where_category. ')' . $where_text)
				->count_all();
			return $incidents_count;
		}
		else //if we're using AND
		{
			// Retrieve incidents by category			
			$incidents_count = ORM::factory('incident')
				->select('incident.*, COUNT(incident.id) as category_count')
				->with('location')
				->join('incident_category', 'incident.id', 'incident_category.incident_id','LEFT')
				->join('media', 'incident.id', 'media.incident_id','LEFT')
				->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
				->where($approved_text.' AND ('.$where_category. ')' . $where_text)
				->groupby('incident.id')
				->having('category_count', count($category_ids))
				->count_all();
			return $incidents_count;
		}

	}//end method	
	
}//end class



	groups_Core::init();


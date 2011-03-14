<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Simple Groups controller
 *
 * PHP version 5
 * @author     John Etherton<john.etherton@gmail.com>
 */

class Simplegroups_Controller extends Main_Controller {

    function __construct()
    {
        parent::__construct();
    }

	public function index($group_id = 1)
	{
		$this->template->header->this_page = "Simple Groups";
		$this->template->content = new View('simplegroups/about');

		//There's no group ID so send these jokers back to the home page.
		if ( ! $group_id)
		{
		    url::redirect('main');
		}

		$group = ORM::factory('simplegroups_groups',$group_id)->find($group_id);
		if ($group->loaded)
		{
			$this->template->content->group_name = $group->name;
			$this->template->content->group_description = $group->description;
			$this->template->content->group_id = $group->id;
			$this->template->content->group_logo = $group->logo;

		}
		else //couldn't load the group so send these jokers back to, ya ya ya you get it.
		{
		    url::redirect('main');
		}

		$this->template->header->header_block = $this->themes->header_block();
	}//end method


	//test group map
	public function groupmap($group_id)
	{
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Make sure we have a group
		if ( ! $group_id)
		{
			url::redirect('main');
		}
		
		$group = ORM::factory('simplegroups_groups',$group_id)->find($group_id);
		
		if (!$group->loaded)
		{
			url::redirect('main');
		}
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Setup the map
		adminmap_helper::setup_adminmap($this, "simplegroups/about_map", "simplegroups/css/simplegroupmap");
		
		//get the categories
		adminmap_helper::set_categories($this, false, $group);
		
		//setup the map
		$clustering = Kohana::config('settings.allow_clustering');
		$json_url = ($clustering == 1) ? "simplegroupmap_json/cluster/$group_id" : "simplegroupmap_json/index/$group_id";
		$json_timeline_url = "simplegroupmap_json/timeline/$group_id/";
		adminmap_helper::set_map($this->template, $this->themes, $json_url, $json_timeline_url, 'simplegroups/mapview_js',
								'simplegroups/frontend_map/main_map', 'simplegroups/frontend_map/main_timeline');
		
		//setup the overlays and shares
		adminmap_helper::set_overlays_shares($this);
	
		
		
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//stuff to setup the group 
		
		$this->template->content->group_name = $group->name;
		$this->template->content->group_description = $group->description;
		$this->template->content->group_id = $group->id;
		$this->template->content->group_logo = $group->logo;

		$this->template->header->header_block = $this->themes->header_block();

	}//end method
	
	

	
	/*******************************************
	* Creates a list of groups on this instance
	*******************************************/
	public function groups()
	{
		 $this->template->content = new View('simplegroups/grouplist');
		 
		 $groups = ORM::factory('simplegroups_groups')->find_all();
		 $this->template->content->groups=$groups;
		 
		 $this->template->header->header_block = $this->themes->header_block();
	}//end method

}//end class
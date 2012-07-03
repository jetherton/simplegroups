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
		enhancedmap_helper::setup_enhancedmap($this, "simplegroups/about_map", "simplegroups/css/simplegroupmap");
		
		
		
		//setup the map
		$clustering = Kohana::config('settings.allow_clustering');
		
		$json_url = ($clustering == 1) ? "bigmap_json/cluster" : "bigmap_json";
		$json_timeline_url = "bigmap_json/timeline/";
		
		$urlParams = array('sgid'=>$group_id);
		
		
		
			
		enhancedmap_helper::set_map($this->template, $this->themes, $json_url, $json_timeline_url, 'enhancedmap/adminmap_js',
								'simplegroups/frontend_map/main_map', 'simplegroups/frontend_map/main_timeline', $urlParams);
		
		
		//boolean filter
		$this->template->content->div_boolean_filter = enhancedmap_helper::get_boolean_filter();
		
		//category filter
		$this->template->content->div_category_filter = enhancedmap_helper::set_categories(false, $group, 'simplegroups/categories_filter');
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//setup the overlays and shares
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		
		//layers
		$this->template->content->div_layers_filter = enhancedmap_helper::set_layers();
		
		//shares
		$this->template->content->div_shares_filter = enhancedmap_helper::set_shares(false, false);
		
		
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
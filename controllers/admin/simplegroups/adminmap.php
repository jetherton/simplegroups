<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Simple Groups - Administrative Controller
 *
 * @author	   John Etherton
 * @package	   Simple Groups
 */

class adminmap_Controller extends Admin_simplegroup_Controller
{

	function __construct()
	{
		parent::__construct();
		
		$this->template->this_page = 'adminmap';
		
		
	}
	

	public function index()
	{
		
		
		enhancedmap_helper::setup_enhancedmap($this);
		
		$urlParams = array('sgid'=>$this->group->id);
		
		//get the categories
		//boolean filter
		$this->template->content->div_boolean_filter = enhancedmap_helper::get_boolean_filter();		
		//category filter
		$this->template->content->div_categories_filter = enhancedmap_helper::set_categories(true, $this->group, 'simplegroups/categories_filter');
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//setup the overlays and shares
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		//set the status filter
		$this->template->content->div_status_filter = enhancedmap_helper::get_status_filter($on_backend = true,
				$status_filter_view = 'enhancedmap/status_filter', $status_filter_id = "status_filter",
				$show_unapproved = true);
		
		//layers
		$this->template->content->div_layers_filter = enhancedmap_helper::set_layers();
		
		//shares
		$this->template->content->div_shares_filter = enhancedmap_helper::set_shares(false, false);
		
		//setup the map
		$clustering = Kohana::config('settings.allow_clustering');
		$json_url = ($clustering == 1) ? "admin/simplegroups/adminmap_json/cluster" : "admin/simplegroups/adminmap_json";
		$json_timeline_url = "admin/simplegroups/adminmap_json/timeline/";		
		enhancedmap_helper::set_map($this->template, $this->template, $json_url, $json_timeline_url, 'enhancedmap/adminmap_js',
				'enhancedmap/main_map', 'enhancedmap/main_timeline', $urlParams);
				
	}//end index method

} //end class
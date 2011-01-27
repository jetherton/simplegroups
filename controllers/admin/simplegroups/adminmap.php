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
		
		adminmap_helper::setup_adminmap($this);
		
		//get the categories
		adminmap_helper::set_categories($this);
		
		//setup the map
		$clustering = Kohana::config('settings.allow_clustering');
		$json_url = ($clustering == 1) ? "admin/simplegroups/adminmap_json/cluster" : "admin/simplegroups/adminmap_json";
		adminmap_helper::set_map($this, $json_url, 'simplegroups/mapview_js');
		
		//setup the overlays and shares
		adminmap_helper::set_overlays_shares($this);
		
	}//end index method

} //end class
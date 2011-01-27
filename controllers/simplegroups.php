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
        $this->template->header->this_page = "page_".$group_id;
        $this->template->content = new View('simplegroups/about');

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
        else
        {
            url::redirect('main');
        }
        
        $this->template->header->header_block = $this->themes->header_block();
    }


	
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
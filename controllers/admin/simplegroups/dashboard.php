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

class Dashboard_Controller extends Admin_simplegroup_Controller
{
    function __construct()
    {
        parent::__construct();
    }



    function index()
    {
        $this->template->content = new View('simplegroups/dashboard');
        $this->template->content->title = Kohana::lang('ui_admin.dashboard');
        $this->template->this_page = 'dashboard';

        // Retrieve Dashboard Count...

        // Total Reports
        $this->template->content->reports_total = ORM::factory('incident')
		->join("simplegroups_groups_incident", "simplegroups_groups_incident.incident_id", "incident.id")
		->where("simplegroups_groups_incident.simplegroups_groups_id", $this->group->id)
		->count_all();
    

        // Total Categories
        $this->template->content->categories = ORM::factory('category')->count_all();

        // Messages By Service
	$this->template->content->message_count = ORM::factory('simplegroups_groups_message')
		->where('simplegroups_groups_id', $this->group->id)
		->count_all();


        // Get reports for display
        $incidents = ORM::factory('incident')
		->join("simplegroups_groups_incident", "simplegroups_groups_incident.incident_id", "incident.id")
		->where("simplegroups_groups_incident.simplegroups_groups_id", $this->group->id)
		->limit(5)->orderby('incident_dateadd', 'desc')->find_all();
        $this->template->content->incidents = $incidents;

        // Get Incoming Media (We'll Use NewsFeeds for now)
        $this->template->content->feeds = ORM::factory('feed_item')
                                                    ->limit('3')
                                                    ->orderby('item_date', 'desc')
                                                    ->find_all();



        $this->template->content->failure = '';


    }//end index()

}
?>

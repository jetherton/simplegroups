<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Reports Controller.
 * This controller will take care of adding and editing reports in the Admin section.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Admin Reports Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class Reportssuper_Controller extends Admin_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->template->this_page = 'reports';
    }


    /**
    * Lists the reports.
    * @param int $page
    */
    function index($page = 1)
    {
	$auth = new auth();
	if (!($auth AND $auth->logged_in('superadmin')))
	{
		url::redirect('admin/dashboard');
	}

        $this->template->content = new View('simplegroups/reportssuper');
        $this->template->content->title = Kohana::lang('ui_admin.reports');


        if (!empty($_GET['status']))
        {
            $status = $_GET['status'];

            if (strtolower($status) == 'a')
            {
                $filter = 'incident_active = 0';
            }
            elseif (strtolower($status) == 'v')
            {
                $filter = 'incident_verified = 0';
            }
            else
            {
                $status = "0";
                $filter = '1=1';
            }
        }
        else
        {
            $status = "0";
            $filter = "1=1";
        }

        // Get Search Keywords (If Any)
        if (isset($_GET['k']))
        {
            //  Brute force input sanitization
            
            // Phase 1 - Strip the search string of all non-word characters 
            $keyword_raw = preg_replace('/[^\w+]\w*/', '', $_GET['k']);
            
            // Strip any HTML tags that may have been missed in Phase 1
            $keyword_raw = strip_tags($keyword_raw);
            
            // Phase 3 - Invoke Kohana's XSS cleaning mechanism just incase an outlier wasn't caught
            // in the first 2 steps
            $keyword_raw = $this->input->xss_clean($keyword_raw);
            
            $filter .= " AND (".$this->_get_searchstring($keyword_raw).")";
        }
        else
        {
            $keyword_raw = "";
        }

        // check, has the form been submitted?
        $form_error = FALSE;
        $form_saved = FALSE;
        $form_action = "";
        
        if ($_POST)
        {
            $post = Validation::factory($_POST);

             //  Add some filters
            $post->pre_filter('trim', TRUE);

            // Add some rules, the input field, followed by a list of checks, carried out in order
            $post->add_rules('action','required', 'alpha', 'length[1,1]');
            $post->add_rules('incident_id.*','required','numeric');

            if ($post->validate())
            {
                if ($post->action == 'a')       // Approve Action
                {
                    foreach($post->incident_id as $item)
                    {
                        $update = new Incident_Model($item);
                        if ($update->loaded == true) 
                        {
                            if( $update->incident_active == 0 ) 
                            {
                                $update->incident_active = '1';
                            } 
                            else {
                                $update->incident_active = '0';
                            }

                            // Tag this as a report that needs to be sent out as an alert
                            if ($update->incident_alert_status != '2')
                            { // 2 = report that has had an alert sent
                                $update->incident_alert_status = '1';
                            }

                            $update->save();

                            $verify = new Verify_Model();
                            $verify->incident_id = $item;
                            $verify->verified_status = '1';
                            $verify->user_id = $_SESSION['auth_user']->id;          // Record 'Verified By' Action
                            $verify->verified_date = date("Y-m-d H:i:s",time());
                            $verify->save();

                            // Action::report_approve - Approve a Report
                            Event::run('ushahidi_action.report_approve', $update);
                        }
                    }
                    $form_action = strtoupper(Kohana::lang('ui_admin.approved'));
                }
                elseif ($post->action == 'u')   // Unapprove Action
                {
                    foreach($post->incident_id as $item)
                    {
                        $update = new Incident_Model($item);
                        if ($update->loaded == true) {
                            $update->incident_active = '0';

                            // If Alert hasn't been sent yet, disable it
                            if ($update->incident_alert_status == '1')
                            {
                                $update->incident_alert_status = '0';
                            }

                            $update->save();

                            $verify = new Verify_Model();
                            $verify->incident_id = $item;
                            $verify->verified_status = '0';
                            $verify->user_id = $_SESSION['auth_user']->id;          // Record 'Verified By' Action
                            $verify->verified_date = date("Y-m-d H:i:s",time());
                            $verify->save();

                            // Action::report_unapprove - Unapprove a Report
                            Event::run('ushahidi_action.report_unapprove', $update);
                        }
                    }
                    $form_action = strtoupper(Kohana::lang('ui_admin.unapproved'));
                }
                elseif ($post->action == 'v')   // Verify Action
                {
                    foreach($post->incident_id as $item)
                    {
                        $update = new Incident_Model($item);
                        $verify = new Verify_Model();
                        if ($update->loaded == true) {
                            if ($update->incident_verified == '1')
                            {
                                $update->incident_verified = '0';
                                $verify->verified_status = '0';
                            }
                            else {
                                $update->incident_verified = '1';
                                $verify->verified_status = '2';
                            }
                            $update->save();

                            $verify->incident_id = $item;
                            $verify->user_id = $_SESSION['auth_user']->id;          // Record 'Verified By' Action
                            $verify->verified_date = date("Y-m-d H:i:s",time());
                            $verify->save();
                        }
                    }
                    $form_action = "VERIFIED";
                }
                elseif ($post->action == 'd')   //Delete Action
                {
                    foreach($post->incident_id as $item)
                    {
                        $update = new Incident_Model($item);
                        if ($update->loaded == true)
                        {
                            $incident_id = $update->id;
                            $location_id = $update->location_id;
                            $update->delete();

                            // Delete Location
                            ORM::factory('location')->where('id',$location_id)->delete_all();

                            // Delete Categories
                            ORM::factory('incident_category')->where('incident_id',$incident_id)->delete_all();

                            // Delete Translations
                            ORM::factory('incident_lang')->where('incident_id',$incident_id)->delete_all();

                            // Delete Photos From Directory
                            foreach (ORM::factory('media')->where('incident_id',$incident_id)->where('media_type', 1) as $photo) {
                                deletePhoto($photo->id);
                            }

                            // Delete Media
                            ORM::factory('media')->where('incident_id',$incident_id)->delete_all();

                            // Delete Sender
                            ORM::factory('incident_person')->where('incident_id',$incident_id)->delete_all();

                            // Delete relationship to SMS message
                            $updatemessage = ORM::factory('message')->where('incident_id',$incident_id)->find();
                            if ($updatemessage->loaded == true) {
                                $updatemessage->incident_id = 0;
                                $updatemessage->save();
                            }

                            // Delete Comments
                            ORM::factory('comment')->where('incident_id',$incident_id)->delete_all();
			    
			    //Delete Group
			    ORM::factory("simplegroups_groups_incident")->where('incident_id',$incident_id)->delete_all();

                        }
                    }
                    $form_action = strtoupper(Kohana::lang('ui_admin.deleted'));
                }
                $form_saved = TRUE;
            }
            else
            {
                $form_error = TRUE;
            }

        }

	$db = new Database;


	// Category ID
	$category_ids=array();
        if( isset($_GET['c']) AND ! empty($_GET['c']) )
	{
		$category_ids = explode(",", $_GET['c']); //get rid of that trailing ","
	}
	else
	{
		$category_ids = array("0");
	}
	
	// logical operator
	$logical_operator = "or";
        if( isset($_GET['lo']) AND ! empty($_GET['lo']) )
	{
		$logical_operator = $_GET['lo'];
	}

	$show_unapproved="3"; //1 show only approved, 2 show only unapproved, 3 show all
	//figure out if we're showing unapproved stuff or what.
        if (isset($_GET['u']) AND !empty($_GET['u']))
        {
            $show_unapproved = (int) $_GET['u'];
        }
	$approved_text = "";
	if($show_unapproved == 1)
	{
		$approved_text = "incident.incident_active = 1 ";
	}
	else if ($show_unapproved == 2)
	{
		$approved_text = "incident.incident_active = 0 ";
	}
	else if ($show_unapproved == 3)
	{
		$approved_text = " (incident.incident_active = 0 OR incident.incident_active = 1) ";
	}
	
	
	
	$location_where = "";
	// Break apart location variables, if necessary
	$southwest = array();
	if (isset($_GET['sw']))
	{
		$southwest = explode(",",$_GET['sw']);
	}

	$northeast = array();
	if (isset($_GET['ne']))
	{
		$northeast = explode(",",$_GET['ne']);
	}
	

	if ( count($southwest) == 2 AND count($northeast) == 2 )
	{
		$lon_min = (float) $southwest[0];
		$lon_max = (float) $northeast[0];
		$lat_min = (float) $southwest[1];
		$lat_max = (float) $northeast[1];

		$location_where = ' AND (location.latitude >='.$lat_min.' AND location.latitude <='.$lat_max.' AND location.longitude >='.$lon_min.' AND location.longitude <='.$lon_max.') ';

	}
	
	$group=0;
	//figure out if we're showing unapproved stuff or what.
        if (isset($_GET['sg']) AND !empty($_GET['sg']))
        {
            $group = (int) $_GET['sg'];
        }
	
	$group_where = " (1=1) ";
	if($group != 0)
	{
		$group_where = " (simplegroups_groups_incident.simplegroups_groups_id = ".$group.") ";
	}
	
	$reports_count = groups::get_reports_count($category_ids, $approved_text, $location_where. " AND ". $filter. " AND ". $group_where
		, $logical_operator);

	
	// Pagination
	$pagination = new Pagination(array(
			'query_string' => 'page',
			'items_per_page' => (int) Kohana::config('settings.items_per_page'),
			'total_items' => $reports_count
			));

	$incidents = groups::get_reports($category_ids,  $approved_text, $location_where. " AND ". $filter. " AND ". $group_where, 
		$logical_operator, 
		"incident.incident_date", "asc",
		(int) Kohana::config('settings.items_per_page_admin'), $pagination->sql_offset );

        $location_ids = array();
        foreach ($incidents as $incident)
        {
            $location_ids[] = $incident->location_id;
        }

	//check if location_ids is not empty
        if( count($location_ids ) > 0 ) 
        {
            $locations_result = ORM::factory('location')->in('id',implode(',',$location_ids))->find_all();
            $locations = array();
            foreach ($locations_result as $loc)
            {
                $locations[$loc->id] = $loc->location_name;
            }
        }
        else
        {
            $locations = array();
        }

        $this->template->content->locations = $locations;



        //GET countries
        $countries = array();
        foreach (ORM::factory('country')->orderby('country')->find_all() as $country)
        {
            // Create a list of all categories
            $this_country = $country->country;
            if (strlen($this_country) > 35)
            {
                $this_country = substr($this_country, 0, 35) . "...";
            }
            $countries[$country->id] = $this_country;
        }

        $this->template->content->countries = $countries;
        $this->template->content->incidents = $incidents;
        $this->template->content->pagination = $pagination;
        $this->template->content->form_error = $form_error;
        $this->template->content->form_saved = $form_saved;
        $this->template->content->form_action = $form_action;

        // Total Reports
        $this->template->content->total_items = $pagination->total_items;

        // Status Tab
        $this->template->content->status = $status;

        // Javascript Header
        $this->template->js = new View('simplegroups/reports_js');
    }//end of index()

    

}//end of class

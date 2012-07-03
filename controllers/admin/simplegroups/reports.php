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

class Reports_Controller extends Admin_simplegroup_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->template->this_page = 'reports';
        $this->params = array('all_reports' => TRUE);
        
    }


    /**
    * Lists the reports.
    * @param int $page
    */
    function index($page = 1)
    {

        $this->template->content = new View('simplegroups/reports');
        $this->template->content->title = Kohana::lang('ui_admin.reports');
        
        

        // check, has the form been submitted?
        $form_error = FALSE;
        $form_saved = FALSE;
        $form_action = "";
        
		$this->handle_post_variables();
	
	
		$db = new Database;
	
	
		$this->template->content = $this->setup_report_table($this->template->content);
			
	    $this->template->content->form_error = $form_error;
	    $this->template->content->form_saved = $form_saved;
	    $this->template->content->form_action = $form_action;
		$this->template->content->category_array = $this->setup_category_dropdown_filter();
	        
		// Status Tab
		if (!empty($_GET['status']))
		{
			$status = $_GET['status'];
		}
		else
		{
			$status = "0";
		}
		$this->template->content->status = $status;
	
	        // Javascript Header
	    $this->template->js = new View('simplegroups/reports_js');
    }//end of index()




	//creates the table of messages
	private function setup_report_table($view)
	{
		//hook into the event for the reports::fetch_incidents() method
		Event::add('ushahidi_filter.fetch_incidents_set_params', array($this,'_add_incident_filters'));
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Setup the filters and such.
		if (!empty($_GET['u']))
		{
			$status = $_GET['u'];

			if (strtolower($status) == 'v')
			{
				array_push($this->params, 'i.incident_verified = 0');
			}			
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
			
			array_push($this->params, $filter);
		}



		//make sure they always get reports from their group
		$_GET['sgid'] = $this->group->id;
	
		
		// Fetch all incidents
		$all_incidents = reports::fetch_incidents();
		
		// Pagination
		$pagination = new Pagination(array(
				'directory' => 'simplegroups/pagination',
				'style' => 'ajax_classic',
				'query_string' => 'page',
				'items_per_page' => (int) Kohana::config('settings.items_per_page_admin'),
				'total_items' => $all_incidents->count()
				));
				
		Event::run('ushahidi_filter.pagination',$pagination);				

		// Reports
		$incidents = Incident_Model::get_incidents(reports::$params, $pagination, "i.incident_date", "DESC");
		
		
			
	
		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//use this to make a mapping of simplegroup categories to reports	
		$incidents_ids = array();
		foreach($incidents as $incident)
		{
			$incidents_ids[] = $incident->incident_id;
		}
		
		$category_mapping = array();
		//make sure there are some messages
		if(count($incidents_ids) > 0)
		{
		$incident_categories = ORM::factory('simplegroups_category')
					->select("simplegroups_category.*, simplegroups_incident_category.incident_id AS incident_id")
					->join('simplegroups_incident_category', 'simplegroups_category.id', 'simplegroups_incident_category.simplegroups_category_id')
					->in("simplegroups_incident_category.incident_id", implode(',', $incidents_ids))
					->where('simplegroups_category.simplegroups_groups_id', $this->group->id)
					->find_all();

			foreach($incident_categories as $incident_category)
			{
				$category_mapping[$incident_category->incident_id][] = $incident_category;
			}
		}
		
	
		if(count($incidents) > 0)
		{
			$incidents = ORM::factory("incident")
				->in("incident.id", implode(',', $incidents_ids))
				->orderby('incident_date', 'desc')
				->find_all();
		}	
		
		
			
		$view->category_mapping = $category_mapping;
		$view->incidents = $incidents;
		$view->pagination = $pagination;
		$view->total_items = $pagination->total_items;		
		
		return $view;
		
	}//end of setup_report_table



	
	private function setup_category_dropdown_filter()
	{
		//create category array for drop down filter list
		$category_array = array(0=>"Show All");
		$category_array[strtoupper("---".$this->group->name." Categories---")] = array();

		$categories = ORM::factory('simplegroups_category')
						->where('simplegroups_groups_id', $this->group->id)
						->where('applies_to_report', 1)
						->where('parent_id', 0)
						->find_all();
		foreach($categories as $category)
		{
			//first, check and see if we're dealing with a kid category
			if ($category->children->count() > 0)
			{
				$parent_array = array();
				foreach ($category->children as $child)
				{
					$parent_array["sg_".$child->id] = $child->category_title;
				}
				$category_array[$category->category_title] = $parent_array;
			}
			else
			{
				$category_array["sg_".$category->id] = $category->category_title;
			}		
		}//end loop

		$category_array["---GLOBAL CATEGORIES---"] = array();
		$categories = ORM::factory('category')->find_all();
		foreach($categories as $category)
		{
			//first, check and see if we're dealing with a kid category
			if ($category->children->count() > 0)
			{
				$parent_array = array();
				foreach ($category->children as $child)
				{
					$parent_array[$child->id] = $child->category_title;
				}
				$category_array[$category->category_title] = $parent_array;
			}
			else
			{
				$category_array[$category->id] = $category->category_title;
			}		
		}//end loop
		
		return $category_array;
	}












    


    
	
	
	
	//ajax calls to updated the report list come in here
	function get_table()
	{	
		$this->template = "";
		$this->auto_render = FALSE;
		
		$this->handle_post_variables();
		
		$table_view = View::factory('simplegroups/reports/reports_table');
		$table_view = $this->setup_report_table($table_view);
		
		$table_view->render(TRUE);
	}//end get_table()
    

    
    
    private function handle_post_variables()
    {
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
                            
						    //Just delete the association with the group and the incident, don't delete the
						    //incident itself. You never know when you might need it.
						    ORM::factory("simplegroups_groups_incident")
						    	->where('incident_id',$incident_id)
						    	->where('simplegroups_groups_id', $this->group->id)
						    	->delete_all();

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
    }//end method
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
    * Edit a report
    * @param bool|int $id The id no. of the report
    * @param bool|string $saved
    */
    function edit( $id = false, $saved = false )
    {

	$db = new Database();
	
        //$this->template->content = new View('admin/reports_edit');
	$this->template->content = View::factory('simplegroups/reports_edit');	
        $this->template->content->title = Kohana::lang('ui_admin.create_report');

        // setup and initialize form field names
        $form = array
        (
            'location_id'      => '',
            'form_id'      => '',
            'locale'           => '',
            'incident_title'      => '',
            'incident_description'    => '',
            'incident_date'  => '',
            'incident_hour'      => '',
            'incident_minute'      => '',
            'incident_ampm' => '',
            'latitude' => '',
            'longitude' => '',
	    	'geometry' => array(),
            'location_name' => '',
            'country_id' => '',
            'incident_category' => array(),
	    	'incident_group_category' => array(),
            'incident_news' => array(),
            'incident_video' => array(),
            'incident_photo' => array(),
            'person_first' => '',
            'person_last' => '',
            'person_email' => '',
            'custom_field' => array(),
            'incident_active' => '',
            'incident_verified' => '',
            //'incident_source' => '',
            //'incident_information' => '',
	    	'incident_zoom' => ''
        );

        //  copy the form as errors, so the errors will be stored with keys corresponding to the form field names
        $errors = $form;
        $form_error = FALSE;
        if ($saved == 'saved')
        {
            $form_saved = TRUE;
        }
        else
        {
            $form_saved = FALSE;
        }

        // Initialize Default Values
        $form['locale'] = Kohana::config('locale.language');
        //$form['latitude'] = Kohana::config('settings.default_lat');
        //$form['longitude'] = Kohana::config('settings.default_lon');
        $form['country_id'] = Kohana::config('settings.default_country');
        $form['incident_date'] = date("m/d/Y",time());
        $form['incident_hour'] = date('h');
        $form['incident_minute'] = date('i');
        $form['incident_ampm'] = date('a');
        // initialize custom field array
        $form['custom_field'] = $this->_get_custom_form_fields($id,'',true);
		$number_of_message_sender = null;

        // Locale (Language) Array
        $this->template->content->locale_array = Kohana::config('locale.all_languages');

        // Create Categories
        $this->template->content->categories = $this->_get_categories();
		$this->template->content->group_categories = $this->_get_group_categories();
        $this->template->content->new_categories_form = $this->_new_categories_form_arr();
	
		$this->template->content->group_name = $this->group->name;

        // Time formatting
        $this->template->content->hour_array = $this->_hour_array();
        $this->template->content->minute_array = $this->_minute_array();
        $this->template->content->ampm_array = $this->_ampm_array();
	
		$this->template->content->stroke_width_array = $this->_stroke_width_array();

        // Get Countries
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

        //GET custom forms
        $forms = array();
        foreach (ORM::factory('form')->where('form_active',1)->find_all() as $custom_forms)
        {
            $forms[$custom_forms->id] = $custom_forms->form_title;
        }
        
        $this->template->content->forms = $forms;

        // Retrieve thumbnail photos (if edit);
        //XXX: fix _get_thumbnails
        $this->template->content->incident = $this->_get_thumbnails($id);

        // Are we creating this report from SMS/Email/Twitter?
        // If so retrieve message
        if ( isset($_GET['mid']) && !empty($_GET['mid']) ) {

            $message_id = $_GET['mid'];
            $service_id = "";
            $message = ORM::factory('message', $message_id);
	    
	    
	    //figure out the group number that sent the message
	    $number_items = ORM::factory("simplegroups_groups_number")
			->join("simplegroups_groups_message", "simplegroups_groups_message.number_id", "simplegroups_groups_numbers.id")
			->where("simplegroups_groups_message.message_id", $message_id)
			->find_all();
		foreach($number_items as $number_item)
		{
			$number_of_message_sender = $number_item;
		}

            if ($message->loaded == true && $message->message_type == 1)
            {
                $service_id = $message->reporter->service_id;

                // Has a report already been created for this Message?
                if ($message->incident_id != 0) {
                    // Redirect to report
                    url::redirect('admin/simplegroups/reports/edit/'. $message->incident_id);
                }

                $this->template->content->show_messages = true;
                $incident_description = $message->message;
                if (!empty($message->message_detail))
                {
                    $incident_description .= "\n\n~~~~~~~~~~~~~~~~~~~~~~~~~\n\n"
                        . $message->message_detail;
                }
                $form['incident_description'] = $incident_description;
                $form['incident_date'] = date('m/d/Y', strtotime($message->message_date));
                $form['incident_hour'] = date('h', strtotime($message->message_date));
                $form['incident_minute'] = date('i', strtotime($message->message_date));
                $form['incident_ampm'] = date('a', strtotime($message->message_date));
                $form['person_first'] = $message->reporter->reporter_first;
                $form['person_last'] = $message->reporter->reporter_last;

                // Does the sender of this message have a location?
                if ($message->reporter->location->loaded)
                {
                    $form['latitude'] = $message->reporter->location->latitude;
                    $form['longitude'] = $message->reporter->location->longitude;
                    $form['location_name'] = $message->reporter->location->location_name;
                }

                // Retrieve Last 5 Messages From this account
                $this->template->content->all_messages = ORM::factory('message')
                    ->where('reporter_id', $message->reporter_id)
                    ->orderby('message_date', 'desc')
                    ->limit(5)
                    ->find_all();
            }
            else
            {
                $message_id = "";
                $this->template->content->show_messages = false;
            }
        }
        else
        {
            $this->template->content->show_messages = false;
        }

        // Are we creating this report from a Newsfeed?
        if ( isset($_GET['fid']) && !empty($_GET['fid']) )
        {
            $feed_item_id = $_GET['fid'];
            $feed_item = ORM::factory('feed_item', $feed_item_id);

            if ($feed_item->loaded == true)
            {
                // Has a report already been created for this Feed item?
                if ($feed_item->incident_id != 0)
                {
                    // Redirect to report
                    url::redirect('admin/simplegroups/reports/edit/'. $feed_item->incident_id);
                }

                $form['incident_title'] = $feed_item->item_title;
                $form['incident_description'] = $feed_item->item_description;
                $form['incident_date'] = date('m/d/Y', strtotime($feed_item->item_date));
                $form['incident_hour'] = date('h', strtotime($feed_item->item_date));
                $form['incident_minute'] = date('i', strtotime($feed_item->item_date));
                $form['incident_ampm'] = date('a', strtotime($feed_item->item_date));

                // News Link
                $form['incident_news'][0] = $feed_item->item_link;

                // Does this newsfeed have a geolocation?
                if ($feed_item->location_id)
                {
                    $form['location_id'] = $feed_item->location_id;
                    $form['latitude'] = $feed_item->location->latitude;
                    $form['longitude'] = $feed_item->location->longitude;
                    $form['location_name'] = $feed_item->location->location_name;
                }
            }
            else
            {
                $feed_item_id = "";
            }
        }

        // check, has the form been submitted, if so, setup validation
        if ($_POST)
        {
            // Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
            $post = Validation::factory(array_merge($_POST,$_FILES));

             //  Add some filters
            $post->pre_filter('trim', TRUE);

            // Add some rules, the input field, followed by a list of checks, carried out in order
            // $post->add_rules('locale','required','alpha_dash','length[5]');
            $post->add_rules('location_id','numeric');
            $post->add_rules('message_id','numeric');
            $post->add_rules('incident_title','required', 'length[3,200]');
            $post->add_rules('incident_description','required');
            $post->add_rules('incident_date','required','date_mmddyyyy');
            $post->add_rules('incident_hour','required','between[1,12]');
            $post->add_rules('incident_minute','required','between[0,59]');
            
            if ($_POST['incident_ampm'] != "am" && $_POST['incident_ampm'] != "pm")
            {
                $post->add_error('incident_ampm','values');
            }
            
            $post->add_rules('latitude','required','between[-90,90]');      // Validate for maximum and minimum latitude values
            $post->add_rules('longitude','required','between[-180,180]');   // Validate for maximum and minimum longitude values
            $post->add_rules('location_name','required', 'length[3,200]');

            //XXX: Hack to validate for no checkboxes checked
            if (!isset($_POST['incident_category'])) {
                $post->incident_category = "";
                $post->add_error('incident_category','required');
            }
            else
            {
                $post->add_rules('incident_category.*','required','numeric');
            }

            // Validate only the fields that are filled in
            if (!empty($_POST['incident_news']))
            {
                foreach ($_POST['incident_news'] as $key => $url) {
                    if (!empty($url) AND !(bool) filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED))
                    {
                        $post->add_error('incident_news','url');
                    }
                }
            }

            // Validate only the fields that are filled in
            if (!empty($_POST['incident_video']))
            {
                foreach ($_POST['incident_video'] as $key => $url) {
                    if (!empty($url) AND !(bool) filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED))
                    {
                        $post->add_error('incident_video','url');
                    }
                }
            }

            // Validate photo uploads
            $post->add_rules('incident_photo', 'upload::valid', 'upload::type[gif,jpg,png]', 'upload::size[2M]');


            // Validate Personal Information
            if (!empty($_POST['person_first']))
            {
                $post->add_rules('person_first', 'length[3,100]');
            }

            if (!empty($_POST['person_last']))
            {
                $post->add_rules('person_last', 'length[3,100]');
            }

            if (!empty($_POST['person_email']))
            {
                $post->add_rules('person_email', 'email', 'length[3,100]');
            }

            // Validate Custom Fields
            if (isset($post->custom_field) && !$this->_validate_custom_form_fields($post->custom_field))
            {
                $post->add_error('custom_field', 'values');
            }

            $post->add_rules('incident_active','required', 'between[0,1]');
            $post->add_rules('incident_verified','required', 'length[0,1]');
            //$post->add_rules('incident_source','numeric', 'length[1,1]');
            //$post->add_rules('incident_information','numeric', 'length[1,1]');


            // Action::report_submit_admin - Report Posted
            Event::run('ushahidi_action.report_submit_admin', $post);


            // Test to see if things passed the rule checks
            if ($post->validate())
            {
                // Yes! everything is valid
                $location_id = $post->location_id;
                // STEP 1: SAVE LOCATION
                $location = new Location_Model($location_id);
                $location->location_name = $post->location_name;
                $location->latitude = $post->latitude;
                $location->longitude = $post->longitude;
                $location->location_date = date("Y-m-d H:i:s",time());
                $location->save();

                // STEP 2: SAVE INCIDENT
                $incident = new Incident_Model($id);
                $incident->location_id = $location->id;
                //$incident->locale = $post->locale;
                $incident->form_id = $post->form_id;
                $incident->user_id = $_SESSION['auth_user']->id;
                $incident->incident_title = $post->incident_title;
                $incident->incident_description = $post->incident_description;

                $incident_date=explode("/",$post->incident_date);
                // where the $_POST['date'] is a value posted by form in mm/dd/yyyy format
                $incident_date=$incident_date[2]."-".$incident_date[0]."-".$incident_date[1];

                $incident_time = $post->incident_hour . ":" . $post->incident_minute . ":00 " . $post->incident_ampm;
                $incident->incident_date = date( "Y-m-d H:i:s", strtotime($incident_date . " " . $incident_time) );
                
				$is_new = false;
                // Is this new or edit?
                if ($id)    // edit
                {
                    $incident->incident_datemodify = date("Y-m-d H:i:s",time());
                }
                else        // new
                {
                    $incident->incident_dateadd = date("Y-m-d H:i:s",time());
		    		$is_new = true;
		    		
                }

                // Is this an Email, SMS, Twitter submitted report?
                //XXX: We may get rid of incident_mode altogether... ???
                //$_POST
                if(!empty($service_id))
                {
                    if ($service_id == 1)
                    { // SMS
                        $incident->incident_mode = 2;
                    }
                    elseif ($service_id == 2)
                    { // Email
                        $incident->incident_mode = 3;
                    }
                    elseif ($service_id == 3)
                    { // Twitter
                        $incident->incident_mode = 4;
                    }
                    elseif ($service_id == 4)
                    { // Laconica
                        $incident->incident_mode = 5;
                    }
                }
                // Incident Evaluation Info
                $incident->incident_active = $post->incident_active;
                $incident->incident_verified = $post->incident_verified;
                //$incident->incident_source = $post->incident_source;
                //$incident->incident_information = $post->incident_information;
				//$incident->incident_zoom = (int) $post->incident_zoom;
                //Save
                $incident->save();

                // Tag this as a report that needs to be sent out as an alert
                if ($incident->incident_active == '1' AND $incident->incident_alert_status != '2')
                { // 2 = report that has had an alert sent
                    $incident->incident_alert_status = '1';
                    $incident->save();
                }
                // Remove alert if report is unactivated and alert hasn't yet been sent
                if ($incident->incident_active == '0' AND $incident->incident_alert_status == '1')
                {
                    $incident->incident_alert_status = '0';
                    $incident->save();
                }

                // Record Approval/Verification Action
                $verify = new Verify_Model();
                $verify->incident_id = $incident->id;
                $verify->user_id = $_SESSION['auth_user']->id;          // Record 'Verified By' Action
                $verify->verified_date = date("Y-m-d H:i:s",time());
                
                if ($post->incident_active == 1)
                {
                    $verify->verified_status = '1';
                }
                elseif ($post->incident_verified == 1)
                {
                    $verify->verified_status = '2';
                }
                elseif ($post->incident_active == 1 && $post->incident_verified == 1)
                {
                    $verify->verified_status = '3';
                }
                else
                {
                    $verify->verified_status = '0';
                }
                $verify->save();


		//STEP 2.5: SAVE THE GROUP ASSOCIATION
		if($is_new)
		{
			$group_incident = ORM::factory("simplegroups_groups_incident");
			$group_incident->incident_id = $incident->id;
			$group_incident->simplegroups_groups_id = $this->group->id;
			if($number_of_message_sender)
			{
				$group_incident->number_id = $number_of_message_sender->id;
			}
			$group_incident->save();
		}
		
		
		// STEP 2b: SAVE INCIDENT GEOMETRIES
		ORM::factory('geometry')->where('incident_id',$incident->id)->delete_all();
		if (isset($post->geometry)) 
		{
			foreach($post->geometry as $item)
			{
				if(!empty($item))
				{
					//Decode JSON
					$item = json_decode($item);
					//++ TODO - validate geometry
					$geometry = (isset($item->geometry)) ? mysql_escape_string($item->geometry) : "";
					$label = (isset($item->label)) ? mysql_escape_string(substr($item->label, 0, 150)) : "";
					$comment = (isset($item->comment)) ? mysql_escape_string(substr($item->comment, 0, 255)) : "";
					$color = (isset($item->color)) ? mysql_escape_string(substr($item->color, 0, 6)) : "";
					$strokewidth = (isset($item->strokewidth) AND (float) $item->strokewidth) ? (float) $item->strokewidth : "2.5";
					if ($geometry)
					{
						//++ Can't Use ORM for this
						$sql = "INSERT INTO ".Kohana::config('database.default.table_prefix')."geometry (
							incident_id, geometry, geometry_label, geometry_comment, geometry_color, geometry_strokewidth ) 
							VALUES( ".$incident->id.",
							GeomFromText( '".$geometry."' ),'".$label."','".$comment."','".$color."','".$strokewidth."')";
						$db->query($sql);
					}
				}
			}
		}
		

                // STEP 3: SAVE CATEGORIES
                ORM::factory('Incident_Category')->where('incident_id',$incident->id)->delete_all();        // Delete Previous Entries
                foreach($post->incident_category as $item)
                {
                    $incident_category = new Incident_Category_Model();
                    $incident_category->incident_id = $incident->id;
                    $incident_category->category_id = $item;
                    $incident_category->save();
                }
		
                // STEP 3.1: SAVE GROUP CATEGORIES
                ORM::factory('simplegroups_incident_category')->where('incident_id',$incident->id)->delete_all();        // Delete Previous Entries
				if(isset($post->incident_group_category))
				{
					foreach($post->incident_group_category as $item)
					{
					    $incident_group_category = ORM::factory('simplegroups_incident_category');
					    $incident_group_category->incident_id = $incident->id;
					    $incident_group_category->simplegroups_category_id = $item;
					    $incident_group_category->save();
					}
				}


                // STEP 4: SAVE MEDIA
                ORM::factory('Media')->where('incident_id',$incident->id)->where('media_type <> 1')->delete_all();      // Delete Previous Entries
                // a. News
                foreach($post->incident_news as $item)
                {
                    if(!empty($item))
                    {
                        $news = new Media_Model();
                        $news->location_id = $location->id;
                        $news->incident_id = $incident->id;
                        $news->media_type = 4;      // News
                        $news->media_link = $item;
                        $news->media_date = date("Y-m-d H:i:s",time());
                        $news->save();
                    }
                }

                // b. Video
                foreach($post->incident_video as $item)
                {
                    if(!empty($item))
                    {
                        $video = new Media_Model();
                        $video->location_id = $location->id;
                        $video->incident_id = $incident->id;
                        $video->media_type = 2;     // Video
                        $video->media_link = $item;
                        $video->media_date = date("Y-m-d H:i:s",time());
                        $video->save();
                    }
                }

                // c. Photos
                $filenames = upload::save('incident_photo');
                $i = 1;
                foreach ($filenames as $filename) {
                    $new_filename = $incident->id . "_" . $i . "_" . time();

                    // Resize original file... make sure its max 408px wide
                    Image::factory($filename)->save(Kohana::config('upload.directory', TRUE) . $new_filename . ".jpg");

                    // Create thumbnail
                    Image::factory($filename)->resize(70,41,Image::HEIGHT)
                        ->save(Kohana::config('upload.directory', TRUE) . $new_filename . "_t.jpg");

                    // Remove the temporary file
                    unlink($filename);

                    // Save to DB
                    $photo = new Media_Model();
                    $photo->location_id = $location->id;
                    $photo->incident_id = $incident->id;
                    $photo->media_type = 1; // Images
                    $photo->media_link = $new_filename . ".jpg";
                    $photo->media_thumb = $new_filename . "_t.jpg";
                    $photo->media_date = date("Y-m-d H:i:s",time());
                    $photo->save();
                    $i++;
                }


                // STEP 5: SAVE PERSONAL INFORMATION
                ORM::factory('Incident_Person')->where('incident_id',$incident->id)->delete_all();      // Delete Previous Entries
                $person = new Incident_Person_Model();
                $person->incident_id = $incident->id;
                $person->person_first = $post->person_first;
                $person->person_last = $post->person_last;
                $person->person_email = $post->person_email;
                $person->person_date = date("Y-m-d H:i:s",time());
                $person->save();
                
                if($is_new) //if it's new forward this to the groups main site, if they have one
                {
                	groups::forward_incident_to_own_instance($incident->id, $this->group->id);
                }


                // STEP 6a: SAVE LINK TO REPORTER MESSAGE
                // We're creating a report from a message with this option
                if(isset($message_id) && $message_id != "")
                {
                    $savemessage = ORM::factory('message', $message_id);
                    if ($savemessage->loaded == true)
                    {
                        $savemessage->incident_id = $incident->id;
                        $savemessage->save();
                    }
                }

                // STEP 6b: SAVE LINK TO NEWS FEED
                // We're creating a report from a newsfeed with this option
                if(isset($feed_item_id) && $feed_item_id != "")
                {
                    $savefeed = ORM::factory('feed_item', $feed_item_id);
                    if ($savefeed->loaded == true)
                    {
                        $savefeed->incident_id = $incident->id;
                        $savefeed->location_id = $location->id;
                        $savefeed->save();
                    }
                }

                // STEP 7: SAVE CUSTOM FORM FIELDS
                if(isset($post->custom_field))
                {
                    foreach($post->custom_field as $key => $value)
                    {
                        $form_response = ORM::factory('form_response')
                                                     ->where('form_field_id', $key)
                                                     ->where('incident_id', $incident->id)
                                                     ->find();
                                                     
                        if ($form_response->loaded == true)
                        {
                            $form_response->form_field_id = $key;
                            $form_response->form_response = $value;
                            $form_response->save();
                        }
                        else
                        {
                            $form_response = new Form_Response_Model();
                            $form_response->form_field_id = $key;
                            $form_response->incident_id = $incident->id;
                            $form_response->form_response = $value;
                            $form_response->save();
                        }
                    }
                }

                // Action::report_edit - Edited a Report
                Event::run('ushahidi_action.report_edit', $incident);


                // SAVE AND CLOSE?
                if ($post->save == 1)       // Save but don't close
                {
                    url::redirect('admin/simplegroups/reports/edit/'. $incident->id .'/saved');
                }
                else                        // Save and close
                {
                    url::redirect('admin/simplegroups/reports/');
                }
            }

            // No! We have validation errors, we need to show the form again, with the errors
            else
            {
                // repopulate the form fields
                $form = arr::overwrite($form, $post->as_array());

                // populate the error fields, if any
                $errors = arr::overwrite($errors, $post->errors('report'));
                $form_error = TRUE;
            }
        }
        else
        {
            if ( $id )
            {
		//make sure the group user is allowed to see this report		
		$count = ORM::factory("simplegroups_groups_incident")
			->where(array("incident_id"=> $id, "simplegroups_groups_id"=>$this->group->id))
			->count_all();

		if($count == 0)
		{
			url::redirect(url::site().'admin/simplegroups/reports');
		}
	    
                // Retrieve Current Incident
                $incident = ORM::factory('incident', $id);
                if ($incident->loaded == true)
                {
                    // Retrieve Categories
                    $incident_category = array();
                    foreach($incident->incident_category as $category)
                    {
                        $incident_category[] = $category->category_id;
                    }
		    
		    // Retrieve Group Categories
                    $incident_group_category = array();
		    $incident_group_categories = ORM::factory("simplegroups_category")
			->join("simplegroups_incident_category", "simplegroups_category.id", "simplegroups_incident_category.simplegroups_category_id")
			->where("simplegroups_incident_category.incident_id", $id)
			->find_all();
                    foreach($incident_group_categories as $category)
                    {
                        $incident_group_category[] = $category->id;
                    }

                    // Retrieve Media
                    $incident_news = array();
                    $incident_video = array();
                    $incident_photo = array();
                    foreach($incident->media as $media)
                    {
                        if ($media->media_type == 4)
                        {
                            $incident_news[] = $media->media_link;
                        }
                        elseif ($media->media_type == 2)
                        {
                            $incident_video[] = $media->media_link;
                        }
                        elseif ($media->media_type == 1)
                        {
                            $incident_photo[] = $media->media_link;
                        }
                    }
		    
		    
		    // Get Geometries via SQL query as ORM can't handle Spatial Data
			$sql = "SELECT AsText(geometry) as geometry, geometry_label, 
				geometry_comment, geometry_color, geometry_strokewidth 
				FROM ".Kohana::config('database.default.table_prefix')."geometry 
				WHERE incident_id=".$id;
			$query = $db->query($sql);
			foreach ( $query as $item )
			{
				$form['geometry'][] = $item;
			}

                    // Combine Everything
                    $incident_arr = array
                    (
                        'location_id' => $incident->location->id,
                        'form_id' => $incident->form_id,
                        'locale' => $incident->locale,
                        'incident_title' => $incident->incident_title,
                        'incident_description' => $incident->incident_description,
                        'incident_date' => date('m/d/Y', strtotime($incident->incident_date)),
                        'incident_hour' => date('h', strtotime($incident->incident_date)),
                        'incident_minute' => date('i', strtotime($incident->incident_date)),
                        'incident_ampm' => date('a', strtotime($incident->incident_date)),
                        'latitude' => $incident->location->latitude,
                        'longitude' => $incident->location->longitude,
                        'location_name' => $incident->location->location_name,
                        'country_id' => $incident->location->country_id,
                        'incident_category' => $incident_category,
						'incident_group_category' => $incident_group_category,
                        'incident_news' => $incident_news,
                        'incident_video' => $incident_video,
                        'incident_photo' => $incident_photo,
                        'person_first' => $incident->incident_person->person_first,
                        'person_last' => $incident->incident_person->person_last,
                        'person_email' => $incident->incident_person->person_email,
                        'custom_field' => $this->_get_custom_form_fields($id,$incident->form_id,true),
                        'incident_active' => $incident->incident_active,
                        'incident_verified' => $incident->incident_verified,
                        //'incident_source' => $incident->incident_source,
                        //'incident_information' => $incident->incident_information,
						//'incident_zoom' => Kohana::config('settings.default_zoom')
                    );

                    // Merge To Form Array For Display
                    $form = arr::overwrite($form, $incident_arr);
                }
                else
                {
                    // Redirect
                    url::redirect('admin/simplegroups/reports/');
                }

            }//end of if($id)
	    else
	    { //this is a new report with no id
		//check to see if we need to add some group categories that default on
		//first find out what's out there.
		//check and see if we need to tag this with a catgory
		//find all the categories for this group with tag by default turned on
		$categories = ORM::factory("simplegroups_category")
			->where("simplegroups_groups_id", $this->group->id)
			->where("selected_by_default", "1")
			->where("applies_to_report", "1")
			->find_all();
		
		$default_categories = array();
		foreach($categories as $category)
		{			
			$default_categories[$category->id] = $category->id;			
		}
		
		
		if(isset($message) && $message->loaded)
		{
			//if a messge was used in the creation of this report we're gonna copy the appropriate categories over
			//figure out what categories this has
			$message_cats = ORM::factory("simplegroups_category")
				->join("simplegroups_message_category", "simplegroups_message_category.simplegroups_category_id", "simplegroups_category.id")				
				->where('simplegroups_message_category.message_id', $message->id)
				->where("simplegroups_category.applies_to_report", "1")
				->find_all();
			
			foreach($message_cats as $message_cat)
			{
				$default_categories[$message_cat->id] = $message_cat->id;
			}
		}
		
		$form['incident_group_category'] = $default_categories;
		
	    }
        }

        $this->template->content->id = $id;
        $this->template->content->form = $form;
        $this->template->content->errors = $errors;
        $this->template->content->form_error = $form_error;
        $this->template->content->form_saved = $form_saved;

        // Retrieve Custom Form Fields Structure
        $disp_custom_fields = $this->_get_custom_form_fields($id,$form['form_id'],false);
        $this->template->content->disp_custom_fields = $disp_custom_fields;

        // Retrieve Previous & Next Records
	$incident_date  = date("c");
	if(isset($incident))
	{
		$incident_date = $incident->incident_date;
	}
        $previous = ORM::factory('incident')
		->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
		->where('incident.incident_date < ', $incident_date)
		->where("simplegroups_groups_incident.simplegroups_groups_id", $this->group->id)
		->orderby('incident.incident_date','desc')
		->find();
        $previous_url = ($previous->loaded ?
                url::base().'admin/simplegroups/reports/edit/'.$previous->id :
                url::base().'admin/simplegroups/reports/');
        
	$next = ORM::factory('incident')
		->join("simplegroups_groups_incident", "incident.id", "simplegroups_groups_incident.incident_id")
		->where("simplegroups_groups_incident.simplegroups_groups_id", $this->group->id)
		->where('incident.incident_date > ', $incident_date)
		->orderby('incident.incident_date','asc')
		->find();
        $next_url = ($next->loaded ?
                url::base().'admin/simplegroups/reports/edit/'.$next->id :
                url::base().'admin/simplegroups/reports/');
        $this->template->content->previous_url = $previous_url;
        $this->template->content->next_url = $next_url;

        // Javascript Header
        $this->template->map_enabled = TRUE;
        $this->template->colorpicker_enabled = TRUE;
        $this->template->treeview_enabled = TRUE;
		$this->template->editor_enabled = TRUE;
		$this->template->js = new View('reports_submit_edit_js');
		$this->template->js->edit_mode = TRUE;
        $this->template->js->default_map = Kohana::config('settings.default_map');
        $this->template->js->default_zoom = Kohana::config('settings.default_zoom');

        if (!$form['latitude'] || !$form['latitude'])
        {
            $this->template->js->latitude = Kohana::config('settings.default_lat');
            $this->template->js->longitude = Kohana::config('settings.default_lon');
        }
        else
        {
            $this->template->js->latitude = $form['latitude'];
            $this->template->js->longitude = $form['longitude'];
        }
	
		$this->template->js->incident_zoom = Kohana::config('settings.default_zoom');
		$this->template->js->geometries = $form['geometry'];

        // Inline Javascript
        $this->template->content->date_picker_js = $this->_date_picker_js();
        $this->template->content->color_picker_js = $this->_color_picker_js();
        $this->template->content->new_category_toggle_js = $this->_new_category_toggle_js();
    }

   /**
    * Delete Photo
    * @param int $id The unique id of the photo to be deleted
    */
    function deletePhoto ( $id )
    {
        $this->auto_render = FALSE;
        $this->template = "";

        if ( $id )
        {
            $photo = ORM::factory('media', $id);
            $photo_large = $photo->media_link;
            $photo_thumb = $photo->media_thumb;

            // Delete Files from Directory
            if ( ! empty($photo_large))
            {
                unlink(Kohana::config('upload.directory', TRUE) . $photo_large);
            }
            
            if ( ! empty($photo_thumb))
            {
                unlink(Kohana::config('upload.directory', TRUE) . $photo_thumb);
            }

            // Finally Remove from DB
            $photo->delete();
        }
    }

    /* private functions */

    // Return thumbnail photos
    //XXX: This needs to be fixed, it's probably ok to return an empty iterable instead of "0"
    private function _get_thumbnails( $id )
    {
        $incident = ORM::factory('incident', $id);

        if ( $id )
        {
            $incident = ORM::factory('incident', $id);

            return $incident;

        }
        return "0";
    }

    private function _get_categories()
    {
        $categories = ORM::factory('category')
            //->where('category_visible', '1') //when we're on the backend show all categories.
            ->where('parent_id', '0')
			->where('category_trusted != 1')
            ->orderby('category_title', 'ASC')
            ->find_all();

        return $categories;
    }
    
    
	private function _get_group_categories()
	{
		$categories = ORM::factory('simplegroups_category')			
			->where('parent_id', '0')
			->where('applies_to_report', 1)
			->where('simplegroups_groups_id', $this->group->id)
			->orderby('category_title', 'ASC')
			->find_all();

		return $categories;
	}

    // Dynamic categories form fields
    private function _new_categories_form_arr()
    {
        return array
        (
            'category_name' => '',
            'category_description' => '',
            'category_color' => '',
        );
    }

    // Time functions
    private function _hour_array()
    {
        for ($i=1; $i <= 12 ; $i++)
        {
            $hour_array[sprintf("%02d", $i)] = sprintf("%02d", $i);     // Add Leading Zero
        }
        return $hour_array;
    }

    private function _minute_array()
    {
        for ($j=0; $j <= 59 ; $j++)
        {
            $minute_array[sprintf("%02d", $j)] = sprintf("%02d", $j);   // Add Leading Zero
        }

        return $minute_array;
    }

    private function _ampm_array()
    {
        return $ampm_array = array('pm'=>Kohana::lang('ui_admin.pm'),'am'=>Kohana::lang('ui_admin.am'));
    }

    // Javascript functions
     private function _color_picker_js()
    {
     return "<script type=\"text/javascript\">
                $(document).ready(function() {
                $('#category_color').ColorPicker({
                        onSubmit: function(hsb, hex, rgb) {
                            $('#category_color').val(hex);
                        },
                        onChange: function(hsb, hex, rgb) {
                            $('#category_color').val(hex);
                        },
                        onBeforeShow: function () {
                            $(this).ColorPickerSetColor(this.value);
                        }
                    })
                .bind('keyup', function(){
                    $(this).ColorPickerSetColor(this.value);
                });
                });
            </script>";
    }

    private function _date_picker_js()
    {
        return "<script type=\"text/javascript\">
                $(document).ready(function() {
                $(\"#incident_date\").datepicker({
                showOn: \"both\",
                buttonImage: \"" . url::base() . "media/img/icon-calendar.gif\",
                buttonImageOnly: true
                });
                });
            </script>";
    }


    private function _new_category_toggle_js()
    {
        return "<script type=\"text/javascript\">
                $(document).ready(function() {
                $('a#category_toggle').click(function() {
                $('#category_add').toggle(400);
                return false;
                });
                });
            </script>";
    }


    /**
     * Checks if translation for this report & locale exists
     * @param Validation $post $_POST variable with validation rules
     * @param int $iid The unique incident_id of the original report
     */
    public function translate_exists_chk(Validation $post)
    {
        // If add->rules validation found any errors, get me out of here!
        if (array_key_exists('locale', $post->errors()))
            return;

        $iid = $_GET['iid'];
        if (empty($iid)) {
            $iid = 0;
        }
        $translate = ORM::factory('incident_lang')->where('incident_id',$iid)->where('locale',$post->locale)->find();
        if ($translate->loaded == true) {
            $post->add_error( 'locale', 'exists');
        // Not found
        } else {
            return;
        }
    }


    /**
     * Retrieve Custom Form Fields
     * @param bool|int $incident_id The unique incident_id of the original report
     * @param int $form_id The unique form_id. Uses default form (1), if none selected
     * @param bool $field_names_only Whether or not to include just fields names, or field names + data
     * @param bool $data_only Whether or not to include just data
     */
    private function _get_custom_form_fields($incident_id = false, $form_id = 1, $data_only = false)
    {
        $fields_array = array();

        if (!$form_id)
        {
            $form_id = 1;
        }
        $custom_form = ORM::factory('form', $form_id)->orderby('field_position','asc');
        foreach ($custom_form->form_field as $custom_formfield)
        {
            if ($data_only)
            { // Return Data Only
                $fields_array[$custom_formfield->id] = '';

                foreach ($custom_formfield->form_response as $form_response)
                {
                    if ($form_response->incident_id == $incident_id)
                    {
                        $fields_array[$custom_formfield->id] = $form_response->form_response;
                    }
                }
            }
            else
            { // Return Field Structure
                $fields_array[$custom_formfield->id] = array(
                    'field_id' => $custom_formfield->id,
                    'field_name' => $custom_formfield->field_name,
                    'field_type' => $custom_formfield->field_type,
                    'field_required' => $custom_formfield->field_required,
                    'field_maxlength' => $custom_formfield->field_maxlength,
                    'field_height' => $custom_formfield->field_height,
                    'field_width' => $custom_formfield->field_width,
                    'field_isdate' => $custom_formfield->field_isdate,
                    'field_response' => ''
                    );
            }
        }

        return $fields_array;
    }


    /**
     * Validate Custom Form Fields
     * @param array $custom_fields Array
     */
    private function _validate_custom_form_fields($custom_fields = array())
    {
        $custom_fields_error = "";

        foreach ($custom_fields as $field_id => $field_response)
        {
            // Get the parameters for this field
            $field_param = ORM::factory('form_field', $field_id);
            if ($field_param->loaded == true)
            {
                // Validate for required
                if ($field_param->field_required == 1 && $field_response == "")
                {
                    return false;
                }

                // Validate for date
                if ($field_param->field_isdate == 1 && $field_response != "")
                {
                    $myvalid = new Valid();
                    return $myvalid->date_mmddyyyy($field_response);
                }
            }
        }
        return true;
    }


    /**
     * Ajax call to update Incident Reporting Form
     */
    public function switch_form()
    {
        $this->template = "";
        $this->auto_render = FALSE;

        isset($_POST['form_id']) ? $form_id = $_POST['form_id'] : $form_id = "1";
        isset($_POST['incident_id']) ? $incident_id = $_POST['incident_id'] : $incident_id = "";

        $html = "";
        $fields_array = array();
        $custom_form = ORM::factory('form', $form_id)->orderby('field_position','asc');

        foreach ($custom_form->form_field as $custom_formfield)
        {
            $fields_array[$custom_formfield->id] = array(
                'field_id' => $custom_formfield->id,
                'field_name' => $custom_formfield->field_name,
                'field_type' => $custom_formfield->field_type,
                'field_required' => $custom_formfield->field_required,
                'field_maxlength' => $custom_formfield->field_maxlength,
                'field_height' => $custom_formfield->field_height,
                'field_width' => $custom_formfield->field_width,
                'field_isdate' => $custom_formfield->field_isdate,
                'field_response' => ''
                );

            // Load Data, if Any
            foreach ($custom_formfield->form_response as $form_response)
            {
                if ($form_response->incident_id = $incident_id)
                {
                    $fields_array[$custom_formfield->id]['field_response'] = $form_response->form_response;
                }
            }
        }

        foreach ($fields_array as $field_property)
        {
            $html .= "<div class=\"row\">";
            $html .= "<h4>" . $field_property['field_name'] . "</h4>";
            if ($field_property['field_type'] == 1)
            { // Text Field
                // Is this a date field?
                if ($field_property['field_isdate'] == 1)
                {
                    $html .= form::input('custom_field['.$field_property['field_id'].']', $field_property['field_response'],
                        ' id="custom_field_'.$field_property['field_id'].'" class="text"');
                    $html .= "<script type=\"text/javascript\">
                            $(document).ready(function() {
                            $(\"#custom_field_".$field_property['field_id']."\").datepicker({
                            showOn: \"both\",
                            buttonImage: \"" . url::base() . "media/img/icon-calendar.gif\",
                            buttonImageOnly: true
                            });
                            });
                        </script>";
                }
                else
                {
                    $html .= form::input('custom_field['.$field_property['field_id'].']', $field_property['field_response'],
                        ' id="custom_field_'.$field_property['field_id'].'" class="text custom_text"');
                }
            }
            elseif ($field_property['field_type'] == 2)
            { // TextArea Field
                $html .= form::textarea('custom_field['.$field_property['field_id'].']',
                    $field_property['field_response'], ' class="custom_text" rows="3"');
            }
            $html .= "</div>";
        }

        echo json_encode(array("status"=>"success", "response"=>$html));
    }

    /**
     * Creates a SQL string from search keywords
     */
    private function _get_searchstring($keyword_raw)
    {
        $or = '';
        $where_string = '';


        // Stop words that we won't search for
        // Add words as needed!!
        $stop_words = array('the', 'and', 'a', 'to', 'of', 'in', 'i', 'is', 'that', 'it',
        'on', 'you', 'this', 'for', 'but', 'with', 'are', 'have', 'be',
        'at', 'or', 'as', 'was', 'so', 'if', 'out', 'not');

        $keywords = explode(' ', $keyword_raw);
        
        if (is_array($keywords) && !empty($keywords))
        {
            array_change_key_case($keywords, CASE_LOWER);
            $i = 0;
            
            foreach($keywords as $value)
            {
                if (!in_array($value,$stop_words) && !empty($value))
                {
                    $chunk = mysql_real_escape_string($value);
                    if ($i > 0) {
                        $or = ' OR ';
                    }
                    $where_string = $where_string.$or."incident_title LIKE '%$chunk%' OR incident_description LIKE '%$chunk%'  OR location_name LIKE '%$chunk%'";
                    $i++;
                }
            }
        }

        if ($where_string)
        {
            return $where_string;
        }
        else
        {
            return "1=1";
        }
    }
    
    
        /**
    * Download Reports in CSV format
    */

    function download()
    {
        $cat_array = array();
        $group_cat_array = array();
	$permissions = groups::get_permissions_for_user($this->user->id);
	if(!$permissions["edit_group_settings"] )
	{
		url::redirect(url::site().'admin/simplegroups/dashboard');
	}

        $this->template->content = new View('simplegroups/reports_download');
        $this->template->content->title = Kohana::lang('ui_admin.download_reports');

        $form = array(
            'data_point'   => '',
            'data_include' => '',
            'from_date'    => '',
            'to_date'      => ''
        );
        
        $errors = $form;
        $form_error = FALSE;

        // check, has the form been submitted, if so, setup validation
        if ($_POST)
        {
            // Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
            $post = Validation::factory($_POST);

             //  Add some filters
            $post->pre_filter('trim', TRUE);

            // Add some rules, the input field, followed by a list of checks, carried out in order
            $post->add_rules('data_point.*','required','numeric','between[1,4]');
            $post->add_rules('data_include.*','numeric','between[1,6]');
            $post->add_rules('from_date','date_mmddyyyy');
            $post->add_rules('to_date','date_mmddyyyy');

            // Validate the report dates, if included in report filter
            if (!empty($_POST['from_date']) || !empty($_POST['to_date']))
            {
                // Valid FROM Date?
                if (empty($_POST['from_date']) || (strtotime($_POST['from_date']) > strtotime("today"))) {
                    $post->add_error('from_date','range');
                }

                // Valid TO date?
                if (empty($_POST['to_date']) || (strtotime($_POST['to_date']) > strtotime("today"))) {
                    $post->add_error('to_date','range');
                }

                // TO Date not greater than FROM Date?
                if (strtotime($_POST['from_date']) > strtotime($_POST['to_date'])) {
                    $post->add_error('to_date','range_greater');
                }
            }

            // Test to see if things passed the rule checks
            if ($post->validate())
            {
                // Add Filters
                $filter = " ( 1=1";
                // Report Type Filter
                foreach($post->data_point as $item)
                {
                    if ($item == 1) {
                        $filter .= " OR incident_active = 1 ";
                    }
                    if ($item == 2) {
                        $filter .= " OR incident_verified = 1 ";
                    }
                    if ($item == 3) {
                        $filter .= " OR incident_active = 0 ";
                    }
                    if ($item == 4) {
                        $filter .= " OR incident_verified = 0 ";
                    }
                }
                $filter .= ") ";
		
		//are we stripping out HTML from the description
		$strip_html = false;
		if(isset($post->strip_html))
		{
			$strip_html = true;
		}

                // Report Date Filter
                if (!empty($post->from_date) && !empty($post->to_date))
                {
                    $filter .= " AND ( incident_date >= '" . date("Y-m-d H:i:s",strtotime($post->from_date)) . "' AND incident_date <= '" . date("Y-m-d H:i:s",strtotime($post->to_date)) . "' ) ";
                }

		//make sure only reports from this group are downloaded
		
		$filter .= " AND (simplegroups_groups_incident.simplegroups_groups_id = '".$this->group->id."') ";

                // Retrieve reports
                $incidents = ORM::factory('incident')
			->join("simplegroups_groups_incident", "simplegroups_groups_incident.incident_id", "incident.id")
			->where($filter)
			->orderby('incident_dateadd', 'desc')
			->find_all();

                // Column Titles
                $report_csv = "#,INCIDENT TITLE,INCIDENT DATE";
                foreach($post->data_include as $item)
                {
                    if ($item == 1) {
                        $report_csv .= ",LOCATION";
                    }
                    
                    if ($item == 2) {
                        $report_csv .= ",DESCRIPTION";
                    }
                    
                    if ($item == 3) {
                        $cats = ORM::factory('category')->where('category_visible','1')->find_all();
                        foreach($cats as $cat)
                        {
                        	$report_csv .= ',"'.$this->_csv_text($cat->category_title).'"';
                        	$cat_array[$cat->id] = $cat->category_title;
                        }			
                        
                    }
                    
                    if ($item == 4) {
                        $report_csv .= ",LATITUDE";
                    }
                    
                    if($item == 5) {
                        $report_csv .= ",LONGITUDE";
                    }
		    if($item == 6)
		    {
				
				$cats = ORM::factory('simplegroups_category')->where('category_visible','1')->find_all();
				foreach($cats as $cat)
				{
					$report_csv .= ',"'.$this->_csv_text($cat->category_title).'"';
					$group_cat_array[$cat->id] = $cat->category_title;
				}
		    }
                }
                $report_csv .= ",APPROVED,VERIFIED";
                $report_csv .= "\n";

                foreach ($incidents as $incident)
                {
                    $report_csv .= '"'.$incident->id.'",';
                    $report_csv .= '"'.$this->_csv_text($incident->incident_title).'",';
                    $report_csv .= '"'.$incident->incident_date.'"';

                    foreach($post->data_include as $item)
                    {
                        switch ($item)
                        {
                            case 1:
                                $report_csv .= ',"'.$this->_csv_text($incident->location->location_name).'"';
                            break;

                            case 2:
				if($strip_html)
				{
					$report_csv .= ',"'.$this->_csv_text(strip_tags($incident->incident_description)).'"';
				}
				else
				{
					$report_csv .= ',"'.$this->_csv_text($incident->incident_description).'"';
				}
                            break;

                            case 3:
				//first do the site wide categories
                                                            	
                                foreach($cat_array as $cat_id=>$cat_title)
                                {
                                	$found = false;
                                	foreach($incident->incident_category as $category)
                                	{
                                		if($category->category->id == $cat_id)
                                		{
                                			$found = true;
                                			break;
                                		}
                                	}
                                	
                                	if($found)
                                	{
                                		$report_csv .= ', "'.$this->_csv_text($cat_title).'"';
                                	}
                                	else
                                	{
                                		$report_csv .= ', ""';
                                	}
                                }
                                                                
				
                            break;
                        
                            case 4:
                                $report_csv .= ',"'.$this->_csv_text($incident->location->latitude).'"';
                            break;
                        
                            case 5:
                                $report_csv .= ',"'.$this->_csv_text($incident->location->longitude).'"';
                            break;
			    
			    case 6:
				//the do the group specific categories
				
				//get the categories
				$group_categories = ORM::factory("simplegroups_category")
					->join("simplegroups_incident_category", "simplegroups_incident_category.simplegroups_category_id", "simplegroups_category.id")
					->where("simplegroups_incident_category.incident_id", $incident->id)
					->find_all();
				
                                      
                                foreach($group_cat_array as $cat_id=>$cat_title)
                                {
                                	$found = false;
                                	foreach($group_categories as $category)
                                	{
                                		if($category->id == $cat_id)
                                		{
                                			$found = true;
                                			break;
                                		}
                                	}
                                	 
                                	if($found)
                                	{
                                		$report_csv .= ', "'.$this->_csv_text($cat_title).'"';
                                	}
                                	else
                                	{
                                		$report_csv .= ', ""';
                                	}
                                }
                
			    break;
                        }
                    }
                    
                    if ($incident->incident_active)
                    {
                        $report_csv .= ",YES";
                    }
                    else
                    {
                        $report_csv .= ",NO";
                    }
                    
                    if ($incident->incident_verified)
                    {
                        $report_csv .= ",YES";
                    }
                    else
                    {
                        $report_csv .= ",NO";
                    }
                    
                    $report_csv .= "\n";
                }

                // Output to browser
                header("Content-type: text/x-csv");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Disposition: attachment; filename=" . time() . ".csv");
                header("Content-Length: " . strlen($report_csv));
                echo $report_csv;
                exit;

            }
            // No! We have validation errors, we need to show the form again, with the errors
            else
            {
                // repopulate the form fields
                $form = arr::overwrite($form, $post->as_array());

                // populate the error fields, if any
                $errors = arr::overwrite($errors, $post->errors('report'));
                $form_error = TRUE;
            }
        }

        $this->template->content->form = $form;
        $this->template->content->errors = $errors;
        $this->template->content->form_error = $form_error;

        // Javascript Header
        $this->template->js = new View('simplegroups/reports_download_js');
        $this->template->js->calendar_img = url::base() . "media/img/icon-calendar.gif";
    }//end method


    private function _csv_text($text)
    {
        $text = stripslashes(htmlspecialchars($text));
        return $text;
    }
    
    private function _stroke_width_array()
	{
		for ($i = 0.5; $i <= 8 ; $i += 0.5)
		{
			$stroke_width_array["$i"] = $i;
		}
		
		return $stroke_width_array;
	}
	
	
	
	public function upload()
	{

		$permissions = groups::get_permissions_for_user($this->user->id);
		if(!$permissions["edit_group_settings"] )
		{
			url::redirect(url::site().'admin/simplegroups/dashboard');
		}

		if($_SERVER['REQUEST_METHOD'] == 'GET') {
			$this->template->content = new View('simplegroups/reports_upload');
			$this->template->content->title = 'Upload Reports';
			$this->template->content->form_error = false;
		}
		if ($_SERVER['REQUEST_METHOD']=='POST')
		{
			$errors = array();
			$notices = array();
			
			if (!$_FILES['csvfile']['error']) 
			{
				if (file_exists($_FILES['csvfile']['tmp_name']))
				{
					if($filehandle = fopen($_FILES['csvfile']['tmp_name'], 'r'))
					{
						$importer = new SimpleGroupsReportsImporter;
						
						if ($importer->import($filehandle, $this->group))
						{
							$this->template->content = new View('simplegroups/reports_upload_success');
							$this->template->content->title = 'Upload Reports';
							$this->template->content->rowcount = $importer->totalrows;
							$this->template->content->imported = $importer->importedrows;
							$this->template->content->notices = $importer->notices;
						}
						else
						{
							$errors = $importer->errors;
						}
					}
					else
					{
						$errors[] = Kohana::lang('ui_admin.file_open_error');
					}
				} // file exists?
				else
				{
					$errors[] = Kohana::lang('ui_admin.file_not_found_upload');
				}
			} // upload errors?
			else
			{
				$errors[] = $_FILES['csvfile']['error'];
			}

			if(count($errors))
			{
				$this->template->content = new View('simplegroups/reports_upload');
				$this->template->content->title = Kohana::lang('ui_admin.upload_reports');
				$this->template->content->errors = $errors;
				$this->template->content->form_error = 1;
			}
		} // _POST
	}
	
	
	/**
	 * Adds extra filter paramters to the reports::fetch_incidents()
	 * method. This way we can add 'all_reports=>true and other filters
	 * that don't come standard sinc we are on the backend. 
	 * Works by simply adding in SQL conditions to the params
	 * array of the reprots::fetch_incidents() method
	 * @return none
	 */
	public function _add_incident_filters()
	{
		$params = Event::$data;
		$params = array_merge($params, $this->params);
		Event::$data = $params;
	}
	
	/**
	* Adds extra filter paramters to the reports::fetch_incidents()
	* method. This way we can add filters for non-verified incidents
	* @return none
	*/
	public function _add_incident_verified_filters()
	{
		$params = Event::$data;
		array_push($params, 'i.incident_verified = 0');
		Event::$data = $params;
	}
	
    
}//end class

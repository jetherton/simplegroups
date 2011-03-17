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

class Reports_Controller extends Main_Controller
{

     var $logged_in;

    function __construct()
    {
		parent::__construct();

		$this->themes->validator_enabled = TRUE;

		// Is the Admin Logged In?

		$this->logged_in = Auth::instance()->logged_in()
			? TRUE
			: FALSE;
    }


    /**
    * Lists the reports.
    * @param int $page
    */
    function index($page = 1,$group_id = false)
    {
		// Cacheable Controller
		$this->is_cachable = TRUE;
		
		$this->template->header->this_page = 'reports';
		$this->template->content = new View('reports');
		$this->themes->js = new View('reports_js');

		// Get locale
		$l = Kohana::config('locale.language.0');

		$db = new Database;


        if (!empty($_GET['status']))
        {
            $status = $_GET['status'];

            if (strtolower($status) == 'a')
            {
                $filter = 'incident.incident_active = 0';
            }
            elseif (strtolower($status) == 'v')
            {
                $filter = 'incident.incident_verified = 0';
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

       
        // check, has the form been submitted?
        $form_error = FALSE;
        $form_saved = FALSE;
        $form_action = "";
        
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

	$approved_text = " incident.incident_active = 1 ";
	
	
	
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
	
	$group_where = " AND ( ".$this->table_prefix."simplegroups_groups_incident.simplegroups_groups_id = ".$group_id.") ";
	$joins = groups::get_joins_for_groups($category_ids);		
	$sg_category_to_table_mapping = groups::get_category_to_table_mapping();
	
	$reports_count = reports::get_reports_count($category_ids, $approved_text, $location_where. " AND ". $filter. $group_where, $logical_operator, 
		$joins, $sg_category_to_table_mapping);
			
	
	echo $reports_count." ". Kohana::config('settings.items_per_page');
	// Pagination
	$pagination = new Pagination(array(
			'query_string' => 'page',
			'items_per_page' => (int) Kohana::config('settings.items_per_page'),
			'total_items' => $reports_count
			));

	$incidents = reports::get_reports($category_ids,  $approved_text, $location_where. " AND ". $filter. $group_where, $logical_operator, 	
		"incident.incident_date", "asc",
		(int) Kohana::config('settings.items_per_page'), $pagination->sql_offset, $joins, $sg_category_to_table_mapping );
		
		
	
		//Set default as not showing pagination. Will change below if necessary.
		$this->template->content->pagination = "";

		// Pagination and Total Num of Report Stats
		if ($pagination->total_items == 1)
		{
			$plural = "";
		}
		else
		{
			$plural = "s";
		}

		if ($pagination->total_items > 0)
		{
			$current_page = ($pagination->sql_offset/ (int) Kohana::config('settings.items_per_page')) + 1;
			$total_pages = ceil($pagination->total_items/ (int) Kohana::config('settings.items_per_page'));

			if ($total_pages > 1)
			{ // If we want to show pagination
				$this->template->content->pagination_stats = Kohana::lang('ui_admin.showing_page').' '.$current_page.' '.Kohana::lang('ui_admin.of').' '.$total_pages.' '.Kohana::lang('ui_admin.pages');

				$this->template->content->pagination = $pagination;
			}
			else
			{ // If we don't want to show pagination
				$this->template->content->pagination_stats = $pagination->total_items.' '.Kohana::lang('ui_admin.reports');
			}
		}
		else
		{
			$this->template->content->pagination_stats = '('.$pagination->total_items.' report'.$plural.')';
		}


		//locations
			$location_in = array();
		foreach ($incidents as $incident)
		{
			$location_in[] = $incident->location_id;
		}

		//check if location_in is not empty
		if( count($location_in ) > 0 )
		{
			    // Get location names
			    $query = 'SELECT id, location_name FROM '.$this->table_prefix.'location WHERE id IN ('.implode(',',$location_in).')';
			    $locations_query = $db->query($query);

			    $locations = array();
			    foreach ($locations_query as $loc)
			    {
				    $locations[$loc->id] = $loc->location_name;
			    }
		}
		else
		{
		    $locations = array();
		}
		
		$this->template->content->locations = $locations;

		
		//categories
		$localized_categories = array();
		foreach ($incidents as $incident)
		{
			foreach ($incident->category AS $category)
			{
				$ct = (string)$category->category_title;
				if( ! isset($localized_categories[$ct]))
				{
					$translated_title = Category_Lang_Model::category_title($category->id,$l);
					$localized_categories[$ct] = $category->category_title;
					if($translated_title)
					{
						$localized_categories[$ct] = $translated_title;
					}
				}
			}
		}

		$this->template->content->localized_categories = $localized_categories;



	// Category Title, if Category ID available
	$category_title = "All Categories";
	$count = 0;
	foreach($category_ids as $cat_id)
	{
		$category = ORM::factory('category')
			->find($cat_id);
		if($category->loaded)
		{
			$count++;
			if($count > 1)
			{
				$category_title = $category_title . " ". strtoupper($logical_operator). " ";
			}
			$category_title = $category_title . $category->category_title;
		}
	}
	$this->template->content->category_title = $category_title . ": ";



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

	$this->template->header->header_block = $this->themes->header_block();
    }//end of index()


   }

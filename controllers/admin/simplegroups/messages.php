<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Messages Controller.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Admin Messages Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class Messages_Controller extends Admin_simplegroup_Controller
{
    function __construct()
    {
        parent::__construct();

        $this->template->this_page = 'messages';
    }

    /**
    * Lists the messages.
    * @param int $service_id
    */
    function index($service_id = 1)
    {
        $this->template->content = new View('simplegroups/messages');

        // Get Title
        $service = ORM::factory('service', $service_id);
        $this->template->content->title = $service->service_name;



        // check, has the form been submitted?
        $form_error = FALSE;
        $form_saved = FALSE;
        $form_action = "";
        
        // check, has the form been submitted, if so, setup validation
        if ($_POST)
        {
            // Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
            $post = Validation::factory($_POST);

                //  Add some filters
            $post->pre_filter('trim', TRUE);

            // Add some rules, the input field, followed by a list of checks, carried out in order
            $post->add_rules('action','required', 'alpha', 'length[1,1]');
            $post->add_rules('message_id.*','required','numeric');

            // Test to see if things passed the rule checks
            if ($post->validate())
            {   
                if( $post->action == 'd' )              // Delete Action
                {
                    foreach($post->message_id as $item)
                    {
                        // Delete Message
                        $message = ORM::factory('message')->find($item);
                        $message->delete( $item );
			
			ORM::factory('simplegroups_groups_message')->where("simplegroups_groups_message.message_id", $item)->delete_all();
			//delete the category message maping
			ORM::factory('simplegroups_message_category')->where("simplegroups_message_category.message_id", $item)->delete_all();
                    }
                    
                    $form_saved = TRUE;
                    $form_action = strtoupper(Kohana::lang('ui_admin.deleted'));
                }
                elseif( $post->action == 'n' )          // Not Spam
                {
                    foreach($post->message_id as $item)
                    {
                        // Update Message Level
                        $message = ORM::factory('message')->find($item);
                        if ($message->loaded)
                        {
                            $message->message_level = '1';
                            $message->save();
                        }
                    }
                    
                    $form_saved = TRUE;
                    $form_action = strtoupper(Kohana::lang('ui_admin.modified'));
                }
                elseif( $post->action == 's' )          // Spam
                {
                    foreach($post->message_id as $item)
                    {
                        // Update Message Level
                        $message = ORM::factory('message')->find($item);
                        if ($message->loaded)
                        {
                            $message->message_level = '99';
                            $message->save();
                        }
                    }
                    
                    $form_saved = TRUE;
                    $form_action = strtoupper(Kohana::lang('ui_admin.modified'));
                }
            }
            // No! We have validation errors, we need to show the form again, with the errors
            else
            {
                // repopulate the form fields
                $form = arr::overwrite($form, $post->as_array());

                // populate the error fields, if any
                $errors = arr::overwrite($errors, $post->errors('message'));
                $form_error = TRUE;
            }
        }//end of  if($_POST)       
        
        $this->template->content = $this->setup_message_table($this->template->content, $service_id, 0);
        
	
	//create category array for drop down filter list
	$category_array = array(0=>"Show All");
	$categories = ORM::factory('simplegroups_category')
					->where('simplegroups_groups_id', $this->group->id)
					->where('applies_to_message', 1)
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
				$parent_array[$child->id] = $child->category_title;
			}
			$category_array[$category->category_title] = $parent_array;
		}
		else
		{
			$category_array[$category->id] = $category->category_title;
		}		
	}//end loop
		
        
	//populate the view
	$this->template->content->category_array = $category_array;	
        $this->template->content->services = ORM::factory('service')->find_all();
        $this->template->content->form_error = $form_error;
        $this->template->content->form_saved = $form_saved;
        $this->template->content->form_action = $form_action;
	$this->template->treeview_enabled = TRUE;
        
        $levels = ORM::factory('level')->orderby('level_weight')->find_all();
        $this->template->content->levels = $levels;
       
        // Javascript Header
        $this->template->js = new View('simplegroups/messages_js');
    }
    
    
	//creates the table of messages
	private function setup_message_table($view, $service_id, $cat_id=0, $tab_id="")
	{
	
		// Is this an Inbox or Outbox Filter?
		if (!empty($_GET['type']))
		{
			$type = $_GET['type'];

			if ($type == '2')
			{ // OUTBOX
				$filter = 'message_type = 2';
			}
			else
			{ // INBOX
				$type = "1";
				$filter = 'message_type = 1';
			}
		}
		else
		{
			$type = "1";
			$filter = 'message_type = 1';
		}

		// Do we have a reporter ID?
		if (isset($_GET['rid']) AND !empty($_GET['rid']))
		{
			$filter .= ' AND reporter_id=\''.$_GET['rid'].'\'';
		}

		// ALL / Trusted / Spam
		$level = '0';
		if (isset($_GET['level']) AND !empty($_GET['level']))
		{
			$level = $_GET['level'];
			if ($level == 4)
			{
				$filter .= " AND ( reporter.level_id = '4' OR reporter.level_id = '5' ) AND ( message.message_level != '99' ) ";
			}
			elseif ($level == 2)
			{
				$filter .= " AND ( message.message_level = '99' ) ";
			}
		}
	
		if($cat_id != 0)
		{
			$filter .= " AND (simplegroups_message_category.simplegroups_category_id = ". $cat_id.") ";
		}
		
		if($tab_id == "turned_into_reports_tab")
		{
			$filter .= " AND (message.incident_id <> 0) ";
		}
		elseif($tab_id == "three_days_tab")
		{
			$time_minus_three_days = date("Y-m-d G:i:s",time() - (3 * 24 * 60 * 60));
			$filter .= " AND (message.message_date > '$time_minus_three_days') ";
		}
		
		//messages count
		$message_count = ORM::factory('message')
						    ->join('reporter','message.reporter_id','reporter.id')
						    ->join('simplegroups_groups_message', 'message.id', 'simplegroups_groups_message.message_id');
		if($cat_id != 0)
		{
			$message_count= $message_count->join('simplegroups_message_category', 'message.id', 'simplegroups_message_category.message_id');
		}
		$message_count = $message_count->where("simplegroups_groups_message.simplegroups_groups_id", $this->group->id)										
										->where($filter)
										->where('service_id', $service_id)
										->count_all();
	
		// Pagination		
		//$pagination = new Ajax_Pagination(array(
		$pagination = new Pagination(array(
			'directory' => 'simplegroups/pagination',
			'style' => 'ajax_classic',
			'query_string'   => 'page',
			'items_per_page' => (int) Kohana::config('settings.items_per_page_admin'),
			'total_items'    => $message_count
		));

		$messages = ORM::factory('message')
				->select("message.*, simplegroups_groups_message.comments as comments")
				->join('reporter','message.reporter_id','reporter.id')
				->join('simplegroups_groups_message', 'message.id', 'simplegroups_groups_message.message_id');
		if($cat_id != 0)
		{
			$messages = $messages->join('simplegroups_message_category', 'message.id', 'simplegroups_message_category.message_id');
		}
		$messages = $messages->where("simplegroups_groups_message.simplegroups_groups_id", $this->group->id)
				->where('service_id', $service_id)
				->where($filter)
				->orderby('message_date','desc')
				->find_all((int) Kohana::config('settings.items_per_page_admin'), $pagination->sql_offset);


		//create a category to message mapping. This is annoyingly complex and I'm sure there's a better
		//way to do it. I coudln't rely on ORM all the way because I don't want to touch the Message model
		//I also didn't want to add Big-O(n) hits to the database, so this way I just add O(1) hit and O(2n) php cycles
		//but I think database hits are more expensive. Maybe I'm wrong. Anyway... Things stay polynomial with an overal
		//and reduced O(n)
		$message_ids = array();
		foreach($messages as $message)
		{
			$message_ids[] = $message->id;
		}

		$category_mapping = array();

		//make sure there are some messages
		if(count($message_ids) > 0)
		{
		$message_categories = ORM::factory('simplegroups_category')
					->select("simplegroups_category.*, simplegroups_message_category.message_id AS message_id")
					->join('simplegroups_message_category', 'simplegroups_category.id', 'simplegroups_message_category.simplegroups_category_id')
					->in("simplegroups_message_category.message_id", implode(',', $message_ids))
					->where('simplegroups_category.simplegroups_groups_id', $this->group->id)
					->find_all();

			foreach($message_categories as $message_category)
			{
				$category_mapping[$message_category->message_id][] = $message_category;
			}
		}
		
		$view->pagination = $pagination;
		$view->messages = $messages;
		$view->service_id = $service_id;
		$view->category_mapping = $category_mapping;
		$view->total_items = $pagination->total_items;
		$view->type = $type;
		$view->level = $level;
		
		return $view;
	}
    
    
	
	
	function get_table($service_id, $cat_id = 0, $tab_id="")
	{
		$this->template = "";
		$this->auto_render = FALSE;		
		$table_view = View::factory('simplegroups/messages/messages_table');
		
		$table_view = $this->setup_message_table($table_view, $service_id, $cat_id, $tab_id);
		
		$table_view->render(TRUE);
	}
	
	

    /**
    * Send A New Message Using Default SMS Provider
    */
    function send()
    {
        $this->template = "";
        $this->auto_render = FALSE;

        // setup and initialize form field names
        $form = array
        (
            'to_id' => '',
            'message' => ''
        );
        //  Copy the form as errors, so the errors will be stored with keys
        //  corresponding to the form field names
        $errors = $form;
        $form_error = FALSE;

        // check, has the form been submitted, if so, setup validation
        if ($_POST)
        {
            // Instantiate Validation, use $post, so we don't overwrite $_POST
            // fields with our own things
            $post = new Validation($_POST);

            // Add some filters
            $post->pre_filter('trim', TRUE);

            // Add some rules, the input field, followed by a list of checks, carried out in order
            $post->add_rules('to_id', 'required', 'numeric');
            $post->add_rules('message', 'required', 'length[1,160]');

            // Test to see if things passed the rule checks
            if ($post->validate())
            {
                // Yes! everything is valid
                $reply_to = ORM::factory('message', $post->to_id);
                
                if ($reply_to->loaded == true)
                {
                    // Yes! Replyto Exists
                    // This is the message we're replying to
                    $sms_to = intval($reply_to->message_from);

                    // Load Users Settings
                    $settings = new Settings_Model(1);
                    if ($settings->loaded == true) {
                        // Get SMS Numbers
                        if ( ! empty($settings->sms_no3))
                        {
                            $sms_from = $settings->sms_no3;
                        }
                        elseif ( ! empty($settings->sms_no2))
                        {
                            $sms_from = $settings->sms_no2;
                        }
                        elseif ( ! empty($settings->sms_no1))
                        {
                            $sms_from = $settings->sms_no1;
                        }
                        else
                        {
                            $sms_from = "000";      // User needs to set up an SMS number
                        }

                        // Send Message
						$response = sms::send($sms_to, $sms_from, $post->message);

                        // Message Went Through??
                        if ($response === true)
                        {
                            $newmessage = ORM::factory('message');
                            $newmessage->parent_id = $post->to_id;  // The parent message
                            $newmessage->message_from = $sms_from;
                            $newmessage->message_to = $sms_to;
                            $newmessage->message = $post->message;
                            $newmessage->message_type = 2;          // This is an outgoing message
                            $newmessage->reporter_id = $reply_to->reporter_id;
                            $newmessage->message_date = date("Y-m-d H:i:s",time());
                            $newmessage->save();

                            echo json_encode(array("status"=>"sent", "message"=>Kohana::lang('ui_admin.message_sent')));
                        }                        
                        else    // Message Failed 
                        {
                            echo json_encode(array("status"=>"error", "message"=>Kohana::lang('ui_admin.error')." - " . $response));
                        }
                    }
                    else
                    {
                        echo json_encode(array("status"=>"error", "message"=>Kohana::lang('ui_admin.error').Kohana::lang('ui_admin.check_sms_settings')));
                    }
                }
                // Send_To Mobile Number Doesn't Exist
                else {
                    echo json_encode(array("status"=>"error", "message"=>Kohana::lang('ui_admin.error').Kohana::lang('ui_admin.check_number')));
                }
            }

            // No! We have validation errors, we need to show the form again,
            // with the errors
            else
            {
                // populate the error fields, if any
                $errors = arr::overwrite($errors, $post->errors('messages'));
                echo json_encode(array("status"=>"error", "message"=>Kohana::lang('ui_admin.error').Kohana::lang('ui_admin.check_message_valid')));
            }
        }

    }




	/*******************************************
	* This will return info about the categories of
	* a message
	********************************************/
	function category_info($message_id = false)
	{	
		//we're not going to use the template
		$this->auto_render = FALSE;
		$this->template = "";
	
		//if not message id is specified then bounce.
		if ($message_id == false)
		{
			return;
		}
		
		//get message
		$message = ORM::factory('message', $message_id);
		
		if(!$message->loaded)
		{
			return;
		}
		
		// Retrieve Categories
                $message_category = array();
		$categories = ORM::factory('simplegroups_message_category')
			->where("message_id", $message_id)
			->find_all();

		foreach($categories as $category)
		{
			$message_category[] = $category->simplegroups_category_id;
		}
                
		// Load the View		
		$view = View::factory('simplegroups/messages/message_category_edit');					
		$view->message = $message;		
		$view->group_name = $this->group->name;
		$view->message_category = $message_category;
		$view->new_category_toggle_js = $this->_new_category_toggle_js();
		$view->categories = $this->_get_categories();
		$view->render(TRUE);
	}//end method

	
	
	/*************************************************
	* This method saves the category info for a message
	**************************************************/
	function save_category_info($message_id = false)
	{
		//we're not going to use the template
		$this->auto_render = FALSE;
		$this->template = "";
	
		//if not message id is specified then bounce.
		if ($message_id == false)
		{
			return;
		}
		
		//get message and make sure it exists
		$message = ORM::factory('message', $message_id);		
		if(!$message->loaded)
		{
			return;
		}
		
		//first delete all the current category mappings
		ORM::factory('simplegroups_message_category')
			->where('message_id', $message_id)
			->delete_all();
		
		//check if any categories were even selected
		if(isset($_POST['incident_category']))
		{
			//now loop through the new data and add it in there
			foreach($_POST['incident_category'] as $cat_id)
			{
				$message_cat = ORM::factory('simplegroups_message_category');
				$message_cat->message_id = $message_id;
				$message_cat->simplegroups_category_id = $cat_id;
				$message_cat->save();
			}
		}
		
		$this->render_message_categories($message_id);
	}
	
	/***************************************************
	* This will render the display of categories for a 
	* message
	***************************************************/
	function render_message_categories($message_id)
	{
		//we're not going to use the template
		$this->auto_render = FALSE;
		$this->template = "";
	
		//if not message id is specified then bounce.
		if ($message_id == false)
		{
			return;
		}
		
		//get message
		$message = ORM::factory('message', $message_id);
		
		if(!$message->loaded)
		{
			return;
		}
		
		// Retrieve Categories                
		$categories = ORM::factory('simplegroups_category')
			->join("simplegroups_message_category", "simplegroups_category.id", "simplegroups_message_category.simplegroups_category_id")
			->where("simplegroups_message_category.message_id", $message_id)
			->find_all();
		
		$message_categories = array();
		
		if(count($categories) > 0)
		{
			$message_categories[$message_id] = $categories;
		}


		// Load the View		
		$view = View::factory('simplegroups/messages/message_category_info');					
		$view->message_id = $message_id;
		$view->category_mapping = $message_categories;
		$view->render(TRUE);
	}
	
	
	
	
	
	/*************************************************
	* This method saves the category info for a message
	**************************************************/
	function delete_message($service_id, $cat_id = 0, $tab_id="")
	{
		$this->template = "";
		$this->auto_render = FALSE;		
		$table_view = View::factory('simplegroups/messages/messages_table');
				
		
		//check if any categories were even selected
		if(isset($_POST['message_id']))
		{
			//now loop through the new data and add it in there
			foreach($_POST['message_id'] as $message_id)
			{
				if($message_id == "" or $message_id == null)
				{
					continue;
				}
				$message = ORM::factory('message')->find($message_id);
				$message->delete( $message_id );
				
				ORM::factory('simplegroups_groups_message')->where("simplegroups_groups_message.message_id", $message_id)->delete_all();
				//delete the category message maping
				ORM::factory('simplegroups_message_category')->where("simplegroups_message_category.message_id", $message_id)->delete_all();
			}//end loop
		}
		
		
		$table_view = $this->setup_message_table($table_view, $service_id, $cat_id, $tab_id);		
		$table_view->render(TRUE);
			
	}//end method
	
	/*****************************************************************
	* Method for updating the comments associated with a message
	******************************************************************/
	function update_comments($message_id)
	{
		$this->template = "";
		$this->auto_render = FALSE;		
		if(isset($_POST['comments']))
		{
			$comments = $_POST['comments'];
			$simplegroups_msg = ORM::factory("simplegroups_groups_message")
				->where("message_id", $message_id)
				->find();
			
			$simplegroups_msg->comments = $comments;
			$simplegroups_msg->save();			
		}	
	}
	
	
	
	
	
	//get the javascript for the selecting categories
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
	
	//get categories
	private function _get_categories()
	{
		$categories = ORM::factory('simplegroups_category')	
			->where('parent_id', '0')
			->where('simplegroups_groups_id', $this->group->id)
			->where('applies_to_message', 1)
			->orderby('category_title', 'ASC')
			->find_all();

		return $categories;
	}


    /**
     * setup simplepie
     * @param string $raw_data
     */
    private function _setup_simplepie( $raw_data )
    {
        $data = new SimplePie();
        $data->set_raw_data( $raw_data );
        $data->enable_cache(false);
        $data->enable_order_by_date(true);
        $data->init();
        $data->handle_content_type();
        return $data;
    }








}//end class


	
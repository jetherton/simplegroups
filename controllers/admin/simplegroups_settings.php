<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SMS Automate Administrative Controller
 *
 * @author	   John Etherton
 * @package	   Simple Groups
 */

class Simplegroups_settings_Controller extends Admin_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'settings';

		// If this is not a super-user account, redirect to dashboard
		if(!$this->auth->logged_in('admin') && !$this->auth->logged_in('superadmin'))
		{
			url::redirect('admin/dashboard');
		}
	}
	
	public function index()
	{
		$user_counts_array = array();
		$report_counts_array = array();
		
		$form_action = false;
		//check to see if we're deleting anything
		if ($_POST)
		{
			$post = Validation::factory($_POST);

			//  Add some filters
			$post->pre_filter('trim', TRUE);

			// Add some rules, the input field, followed by a list of checks, carried out in order
			$post->add_rules('action','required', 'alpha', 'length[1,1]');
			$post->add_rules('group_id.*','required','numeric');

			if ($post->validate())
			{
				if ($post->action == 'd')   //Delete Action
				{
					foreach($post->group_id as $item)
					{
						$update = new simplegroups_groups_Model($item);
						if ($update->loaded == true)
						{
						    $group_id = $update->id;
						    $logo = $update->logo;
						    $update->delete();
						    
						    //delete logo.
						    if($logo && $logo != "")
						    {
							unlink(Kohana::config('upload.directory', TRUE)."groups/" . $logo.".jpg");
							unlink(Kohana::config('upload.directory', TRUE)."groups/" . $logo."_t.jpg");
						    }
						    
						    //delete phone numbers associated with this group
						    ORM::factory('simplegroups_groups_number')->where("simplegroups_groups_id", $group_id)->delete_all();

						    //delete the users of this group
						    ORM::factory('simplegroups_groups_users')->where("simplegroups_groups_id", $group_id)->delete_all();
						    
						    //delete the associations with incidents and this group
						    ORM::factory('simplegroups_groups_incident')->where("simplegroups_groups_id", $group_id)->delete_all();
						    
						    //delete the associations with incidents and this group
						    ORM::factory('simplegroups_groups_message')->where("simplegroups_groups_id", $group_id)->delete_all();
						}//end if
					}//end foreach
					$form_action = strtoupper("Group Deleted");
				}//end if
			}//end validate
		}//end if post
		
		$this->template->content = new View('simplegroups/simplegroups_admin');
		
		//get a list of groups
		$groups = ORM::factory('simplegroups_groups')
			->orderby("name", "ASC")
			->find_all();
		
		
		$users_counts = ORM::factory('simplegroups_groups_users')
			->select("simplegroups_groups_users.*, COUNT(simplegroups_groups_users.simplegroups_groups_id) as user_count")
			->groupby("simplegroups_groups_users.simplegroups_groups_id")
			->find_all();
			
		foreach($users_counts as $user_count)
		{
			$user_counts_array[$user_count->simplegroups_groups_id] = $user_count->user_count;
		}
			
		$reports_counts = ORM::factory('simplegroups_groups_incident')
			->select("simplegroups_groups_incident.*, COUNT(simplegroups_groups_incident.simplegroups_groups_id) as report_count")
			->groupby("simplegroups_groups_incident.simplegroups_groups_id")
			->find_all();
			
		foreach($reports_counts as $reports_count)
		{
			$report_counts_array[$reports_count->simplegroups_groups_id] = $reports_count->report_count;
		}
	
		$this->template->content->form_action = $form_action;
		$this->template->content->groups = $groups;
		$this->template->content->user_counts = $user_counts_array;
		$this->template->content->report_counts = $report_counts_array;
		
	}//end index method
	
	
	
    /***********************************************************************************
    * Edit a group
    * @param bool|int $id The id no. of the group
    * @param bool|string $saved
    */
    function edit( $id = false, $saved = false )
    {
        // If user doesn't have access, redirect to dashboard
        if ( ! groups::permissions($this->user, "settings"))
        {
            url::redirect(url::site().'admin/dashboard');
        }

        $this->template->content = new View('simplegroups/simplegroups_editgroups');
        $this->template->content->title = "edit group";
	$this->template->content->logo_file = false;
	$this->template->content->users = array();
	
        // setup and initialize form field names
        $form = array
        (
            'name'      => '',
            'description'      => '',
            'logo'           => '',
	    'own_instance' => ''
        );
	
	//initialize this stuff
	$this->template->content->whitelist = array();


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

        // check, has the form been submitted, if so, setup validation
        if ($_POST)
        {
		// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
		$post = Validation::factory(array_merge($_POST,$_FILES));
		//  Add some filters
		$post->pre_filter('trim', TRUE);

		// Add some rules, the input field, followed by a list of checks, carried out in order
		// $post->add_rules('locale','required','alpha_dash','length[5]');
		$post->add_rules('name','required', 'length[1,100]');
		$post->add_rules('description','required');
		$post->add_rules('own_instance','length[3,1000]');

		// Validate photo uploads
		$post->add_rules('logo', 'upload::valid', 'upload::type[gif,jpg,png]', 'upload::size[8M]');
			
		// Test to see if things passed the rule checks
		if ($post->validate())
		{
			
			// Save the Group
			$group = new simplegroups_groups_Model($id);
			$group->name = $post->name;
			$group->description = $post->description;
			$group->own_instance = $post->own_instance;
			$group->save();
			
			//logo
			$filename = upload::save('logo');
			if($filename)
			{
				if (!is_dir(Kohana::config('upload.directory', TRUE)."groups"))
				{
					mkdir(Kohana::config('upload.directory', TRUE)."groups", 0770, true);
				}
				
				$new_filename = $group->id . "_simple_group_logo";

				//Resize original file... make sure its max 408px wide
				Image::factory($filename)->save(Kohana::config('upload.directory', TRUE)."groups/" . $new_filename . ".jpg");
				
				// Create thumbnail
				Image::factory($filename)->resize(140,82,Image::HEIGHT)
				->save(Kohana::config('upload.directory', TRUE)."groups/" . $new_filename . "_t.jpg");

				// Remove the temporary file
				unlink($filename);
				$group->logo = $new_filename;
			}
			
			$group->save();
			$id = $group->id;
			
			
			//do the white list of numbers for this group

			//delete everything in the white list db to make room for the new ones
			ORM::factory('simplegroups_groups_number')->where("simplegroups_groups_id", $id)->delete_all();
			
			
			$white_list_size = $post->white_list_id;
			for($i = 1; $i < $white_list_size; $i++)
			{
				//check to make sure this is a valid number
				if(!isset($_POST["white_list_number_$i"]))
				{
					continue;
				}
				
				$number_item = ORM::factory('simplegroups_groups_number');
				
				$value = trim($_POST["white_list_number_$i"]);
				if(!$this->_check_length($value, 30, "Can't have a phone number with more than 30 characters")){return;}
				$number_item->number = $value;
				
				$value = trim($_POST["white_list_name_$i"]);
				if(!$this->_check_length($value, 100, "Can't have a phone number name with more than 100 characters")){return;}
				$number_item->name = $value;
				
				$value = trim($_POST["white_list_org_$i"]);
				if(!$this->_check_length($value, 100, "Can't have a phone number organization with more than 100 characters")){return;}
				$number_item->org = $value;
				
				$number_item->simplegroups_groups_id = $id;
				
				$number_item->save();
			}
	
			//update the users
			//delete everything
			ORM::factory('simplegroups_groups_users')->where("simplegroups_groups_id", $id)->delete_all();
			//put it all backtogether
			foreach($_POST as $post_id => $data)
			{
				if( substr($post_id, 0,8) == "user_id_")
				{
					//get the user ID number
					$user_id = substr($post_id, 8);
					$user_item = ORM::factory('simplegroups_groups_users');
					$user_item->simplegroups_groups_id = $id;
					$user_item->users_id = $user_id;
					$user_item->save();
				}
			}//end for each 
			
			
			//update the users to roles mapping
			//delete everything
			$roles = ORM::factory('simplegroups_users_roles')
				->join('simplegroups_groups_users', 'simplegroups_users_roles.users_id', 'simplegroups_groups_users.users_id','LEFT')
				->where("simplegroups_groups_users.simplegroups_groups_id", $id)
				->find_all();
			foreach($roles as $role)
			{
				$role->delete();
			}
				
			//put it all backtogether
			foreach($_POST as $post_id => $data)
			{
				
				if( substr($post_id, 0,8) == "role_id_")
				{
					//get the user ID number
					$ids_str = substr($post_id, 8);
					$position_of_ = strpos($ids_str, "_");
					$user_id = substr($ids_str, 0, $position_of_);
					$role_id = substr($ids_str, $position_of_ + 1);
					
					$user_role = ORM::factory('simplegroups_users_roles');
					$user_role->roles_id = $role_id;
					$user_role->users_id = $user_id;
					$user_role->save();
				}
			}//end for each 


			// SAVE AND CLOSE?
			if ($post->save == 1)       // Save but don't close
			{
				url::redirect('admin/simplegroups_settings/edit/'. $group->id .'/saved');
			}
			else                        // Save and close
			{
				url::redirect('admin/simplegroups_settings/');
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
        } //end if($_POST)
        else
        {

		if ( $id )
		{
			// Retrieve Current group
			$group = ORM::factory('simplegroups_groups', $id);
			if ($group->loaded == true)
			{
				// Combine Everything
				$group_arr = array
				(
					'name' => $group->name,
					'description' => $group->description,
					'own_instance' => $group->own_instance
				);
				$this->template->content->logo_file = $group->logo;

				// Merge To Form Array For Display
				$form = arr::overwrite($form, $group_arr);
				
				$listers = ORM::factory('simplegroups_groups_number')->where("simplegroups_groups_id", $id)->find_all();
				$this->template->content->whitelist = $listers;

			}
			else
			{
			    // Redirect
			    //url::redirect('admin/simplegroup_settings/');

			}
		}
		
			$this->template->content->users = $this->_get_users($id);
			$this->template->content->group_roles = $this->_get_group_roles();
			$this->template->content->group_users_roles = $this->_get_group_user_to_roles_mapping($id);

        }

        $this->template->content->id = $id;
        $this->template->content->form = $form;
        $this->template->content->errors = $errors;
        $this->template->content->form_error = $form_error;
        $this->template->content->form_saved = $form_saved;
	


        // Javascript Header
	$this->template->editor_enabled = TRUE;
        $this->template->js = new View('simplegroups/simplegroups_editgroups_js');
    }//end method

	/*************************************************
	* This will return a list of all the possible roles a group
	* user could have
	*************************************************/
	private function _get_group_roles()
	{
		$roles = ORM::factory('simplegroups_roles')->find_all();
		return $roles;
	}
	
	/************************************************************
	 * Returns a 2D array of group users and their roles
	 ************************************************************/
	private function _get_group_user_to_roles_mapping($id)
	{
		if(!$id)
		{
			return array();
		}
		
		$where_text = "roles.name = 'simplegroups' AND (simplegroups_groups_users.simplegroups_groups_id = $id 
					OR simplegroups_groups_users.simplegroups_groups_id is NULL)";
		
		//get all the users that have the 'simplegroups' role, but aren't part of other groups
		$users = ORM::factory('user')
			->select("users.*, simplegroups_roles.id as role_id")
			->join('simplegroups_users_roles', 'users.id', 'simplegroups_users_roles.users_id','LEFT')
			->join('simplegroups_roles', 'simplegroups_roles.id', 'simplegroups_users_roles.roles_id','LEFT')
			->join('roles_users', 'users.id', 'roles_users.user_id','LEFT')
			->join('roles', 'roles.id', 'roles_users.role_id','LEFT')
			->join('simplegroups_groups_users', 'users.id', 'simplegroups_groups_users.users_id','LEFT')
			->where($where_text)
			->find_all();
			
		//now turn this all into a 2d array where the first dimension is the user's id number and the 2nd dimension is the role id number
		$mapping = array();
		foreach ($users as $user)
		{
			$mapping[$user->id][$user->role_id] = 1;
		}
		
		return $mapping;
	}


	/*function to get the users that are available and are already signed up for a group*/
	private function _get_users( $id )
	{
		if($id)
		{
			$where_text = "roles.name = 'simplegroups' AND (simplegroups_groups_users.simplegroups_groups_id = $id 
					OR simplegroups_groups_users.simplegroups_groups_id is NULL)";
		}
		else
		{
			$where_text = "roles.name = 'simplegroups' AND (simplegroups_groups_users.simplegroups_groups_id is NULL)";
		}
		//get all the users that have the 'simplegroups' role, but aren't part of other groups
		$users = ORM::factory('user')
			->select("users.*, simplegroups_groups_users.simplegroups_groups_id")
			->join('roles_users', 'users.id', 'roles_users.user_id','LEFT')
			->join('roles', 'roles.id', 'roles_users.role_id','LEFT')
			->join('simplegroups_groups_users', 'users.id', 'simplegroups_groups_users.users_id','LEFT')
			->where($where_text)
			->find_all();

		
		return $users;
	}//end function

	
	private function _check_length($variable, $max_length, $message)
	{
		if(strlen($variable) >$max_length)
		{
			// repopulate the form fields
			$form = arr::overwrite($form, $post->as_array());
			// populate the error fields, if any
			$errors['numbers'] = $message;
			$form_error = TRUE;
			$this->template->content->id = $id;
			$this->template->content->form = $form;
			$this->template->content->errors = $errors;
			$this->template->content->form_error = $form_error;
			$this->template->content->form_saved = $form_saved;
			// Javascript Header
			$this->template->editor_enabled = TRUE;
			$this->template->js = new View('simplegroups/simplegroups_editgroups_js');
			return false;
		}
		return true;

	}

	
}
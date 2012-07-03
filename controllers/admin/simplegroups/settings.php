<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SMS Automate Administrative Controller
 *
 * @author	   John Etherton
 * @package	   Simple Groups
 */

class Settings_Controller extends Admin_simplegroup_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'simple_group_settings';

		//check the group user's permissions for this
		$permissions = groups::get_permissions_for_user($this->user->id);
		if(!$permissions["edit_group_settings"] )
		{
			url::redirect(url::site().'admin/simplegroups/dashboard');
		}
	}
	
	public function index($saved = false)
	{
	
        $id = $this->group->id;

        $this->template->content = new View('simplegroups/settings');
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
				Image::factory($filename)->save(Kohana::config('upload.directory', TRUE)."groups/" . $new_filename . "_t.jpg");

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
	
			//don't give group admins the option to add/remove random users
			/*
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
			*/
			
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
				url::redirect('admin/simplegroups/settings/index/saved');
			}
			else                        // Save and close
			{
				url::redirect('admin/simplegroups/settings/');
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
		
			$this->template->content->users = groups::get_users_for_group($id);
			$this->template->content->group_roles = groups::get_group_roles();
			$this->template->content->group_users_roles = groups::get_group_users_to_roles_mapping($id, false);

        }

        $this->template->content->id = $id;
        $this->template->content->form = $form;
        $this->template->content->errors = $errors;
        $this->template->content->form_error = $form_error;
        $this->template->content->form_saved = $form_saved;
	


        // Javascript Header
	$this->template->editor_enabled = TRUE;
        $this->template->js = new View('simplegroups/settings_js');
    }//end method

	



	
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

	


	/*
	Add Edit Categories
	*/
	function categories()
	{
		$this->template->content = new View('simplegroups/categories');
		$this->template->content->title = Kohana::lang('ui_admin.categories');

		// Locale (Language) Array
		$locales = ush_locale::get_i18n();

		// Setup and initialize form field names
		$form = array
		(
			'action' => '',
			'category_id'	   => '',
			'parent_id'		 => '',
			'category_title'	  => '',
			'category_description'	  => '',
			'category_color'  => '',
			'category_image'  => '',
			'category_image_thumb'  => '',
			'category_visible'  => '',
			'category_applies_to_report'  => '',
			'category_applies_to_msg'  => '',
			'category_select_by_default'  => ''
			
		);

		// Add the different language form keys for fields
		foreach($locales as $lang_key => $lang_name){
			$form['category_title_'.$lang_key] = '';
		}

		// copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;
		$form_action = "";
		$parents_array = array();

		// Check, has the form been submitted, if so, setup validation

		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things

			$post = Validation::factory(array_merge($_POST,$_FILES));

			 //	 Add some filters

			$post->pre_filter('trim', TRUE);

			// Add Action

			if ($post->action == 'a')
			{
				// Add some rules, the input field, followed by a list of checks, carried out in order
				$post->add_rules('parent_id','required','numeric');
				$post->add_rules('category_title','required', 'length[3,80]');
				$post->add_rules('category_description','required');
				$post->add_rules('category_color','required', 'length[6,6]');
				$post->add_rules('category_image', 'upload::valid', 'upload::type[gif,jpg,png]', 'upload::size[50K]');

				$post->add_callbacks('parent_id', array($this,'parent_id_chk'));

				// Add the different language form keys for fields
				foreach($locales as $lang_key => $lang_name){
					$post->add_rules('category_title_lang['.$lang_key.']','length[3,80]');
				}
			}

			// Test to see if things passed the rule checks
			if ($post->validate())
			{
				
				$category_id = $post->category_id;
				$category = new Simplegroups_category_Model($category_id);

				// Grab languages if they already exist

				$category_lang = Simplegroups_category_lang_Model::simplegroups_category_langs($category->id);
				if(isset($category_lang[$category->id]))
				{
					$category_lang = $category_lang[$category->id];
				}else{
					$category_lang = FALSE;
				}

				if( $post->action == 'd' )
				{ // Delete Action

					// Delete localizations

					ORM::factory('simplegroups_category_lang')
						->where(array('simplegroups_category_id' => $category_id))
						->delete_all();

					// Delete category itself

					ORM::factory('simplegroups_category')
						->where('category_trusted != 1 AND simplegroups_groups_id = '.$this->group->id.' ')
						->delete($category_id);

					$form_saved = TRUE;
					$form_action = strtoupper(Kohana::lang('ui_admin.deleted'));
				}
				elseif( $post->action == 'v' )
				{ // Show/Hide Action
					if ($category->loaded==true)
					{
						if ($category->category_visible == 1) {
							$category->category_visible = 0;
						}
						else
						{
							$category->category_visible = 1;
						}

						$category->save();
						$form_saved = TRUE;
						$form_action = strtoupper(Kohana::lang('ui_admin.modified'));
					}
				}
				elseif( $post->action == 'i' )
				{ // Delete Image/Icon Action

					if ($category->loaded==true)
					{
						$category_image = $category->category_image;
						$category_image_thumb = $category->category_image_thumb;

						if ( ! empty($category_image)
							 AND file_exists(Kohana::config('upload.directory', TRUE).$category_image))
						{
							unlink(Kohana::config('upload.directory', TRUE) . $category_image);
						}

						if ( ! empty($category_image_thumb)
							 AND file_exists(Kohana::config('upload.directory', TRUE).$category_image_thumb))
						{
							unlink(Kohana::config('upload.directory', TRUE) . $category_image_thumb);
						}

						$category->category_image = null;
						$category->category_image_thumb = null;
						$category->save();
						$form_saved = TRUE;
						$form_action = strtoupper(Kohana::lang('ui_admin.modified'));
					}

				}
				elseif( $post->action == 'a' )
				{
					// Save Action
					$category->parent_id = $post->parent_id;
					$category->category_title = $post->category_title;
					$category->category_description = $post->category_description;
					$category->category_color = $post->category_color;
					$category->simplegroups_groups_id = $this->group->id;
					$category->category_visible = isset($_POST['category_visible']) ? 1 : 0;
					$category->applies_to_report = isset($_POST['applies_to_report']) ? 1 : 0;
					$category->applies_to_message = isset($_POST['applies_to_message']) ? 1 : 0;
					$category->selected_by_default = isset($_POST['selected_by_default']) ? 1 : 0;
					//add visible, applies to report, applies to messge, select by default
					$category->save();

					// Save Localizations
					foreach($post->category_title_lang as $lang_key => $localized_category_name){

						if(isset($category_lang[$lang_key]['id']))
						{
							// Update
							$cl = ORM::factory('simplegroups_category_lang',$category_lang[$lang_key]['id']);
						}else{
							// Add New
							$cl = ORM::factory('simplegroups_category_lang');
						}
 						$cl->category_title = $localized_category_name;
 						$cl->locale = $lang_key;
 						$cl->simplegroups_category_id = $category->id;
						$cl->save();
					}

					// Upload Image/Icon
					$filename = upload::save('category_image');
					if ($filename)
					{
						$new_filename = "groups/simplegroups_category_".$category->id."_".time();

						// Resize Image to 32px if greater
						Image::factory($filename)->resize(32,32,Image::HEIGHT)
							->save(Kohana::config('upload.directory', TRUE) . $new_filename.".png");
						// Create a 16x16 version too
						Image::factory($filename)->resize(16,16,Image::HEIGHT)
							->save(Kohana::config('upload.directory', TRUE) . $new_filename."_16x16.png");

						// Remove the temporary file
						unlink($filename);

						// Delete Old Image
						$category_old_image = $category->category_image;
						if ( ! empty($category_old_image)
							AND file_exists(Kohana::config('upload.directory', TRUE).$category_old_image))
							unlink(Kohana::config('upload.directory', TRUE).$category_old_image);

						// Save
						$category->category_image = $new_filename.".png";
						$category->category_image_thumb = $new_filename."_16x16.png";
						$category->save();
					}

					$form_saved = TRUE;
					$form_action = strtoupper(Kohana::lang('ui_admin.added_edited'));

					// Empty $form array
					array_fill_keys($form, '');
				}
			}
			// No! We have validation errors, we need to show the form again, with the errors
			else
			{
				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

			   // populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('category'));
				$form_error = TRUE;
			}
		}
		// Pagination
		$pagination = new Pagination(array(
							'query_string' => 'page',
							'items_per_page' => (int) Kohana::config('settings.items_per_page_admin'),
							'total_items'	 => ORM::factory('simplegroups_category')
													->where('parent_id','0')
													->where('simplegroups_groups_id', $this->group->id)
													->count_all()
						));

		$categories = ORM::factory('simplegroups_category')
									->with('simplegroups_category_lang')
									->where('parent_id','0')
									->where('simplegroups_groups_id', $this->group->id)
									->orderby('category_title', 'asc')
									->find_all((int) Kohana::config('settings.items_per_page_admin'),
												$pagination->sql_offset);

		$parents_array = ORM::factory('simplegroups_category')
									 ->where('parent_id','0')
									 ->where('simplegroups_groups_id', $this->group->id)
									 ->select_list('id', 'category_title');

		// add none to the list
		$parents_array[0] = "--- Top Level Category ---";

		// Put "--- Top Level Category ---" at the top of the list
		ksort($parents_array);

		$this->template->content->form = $form;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_action = $form_action;
		$this->template->content->pagination = $pagination;
		$this->template->content->total_items = $pagination->total_items;
		$this->template->content->categories = $categories;

		$this->template->content->parents_array = $parents_array;

		// Javascript Header
		$this->template->colorpicker_enabled = TRUE;
		$this->template->js = new View('simplegroups/categories_js');
		$this->template->form_error = $form_error;

		$this->template->content->locale_array = $locales;
		$this->template->js->locale_array = $locales;
	} //end method

	/**
	 * Checks if parent_id for this category exists
	 * @param Validation $post $_POST variable with validation rules
	 */
	public function parent_id_chk(Validation $post)
	{
		// If add->rules validation found any errors, get me out of here!
		if (array_key_exists('parent_id', $post->errors()))
			return;

		$category_id = $post->category_id;
		$parent_id = $post->parent_id;
		// This is a parent category - exit
		if ($parent_id == 0)
			return;

		$parent_exists = ORM::factory('simplegroups_category')
									->where('id', $parent_id)
									->find();

		if ( ! $parent_exists->loaded)
		{ // Parent Category Doesn't Exist
			$post->add_error( 'parent_id', 'exists');
		}

		if ( ! empty($category_id) AND $category_id == $parent_id)
		{ // Category ID and Parent ID can't be the same!
			$post->add_error( 'parent_id', 'same');
		}
	}//end function


}//end class
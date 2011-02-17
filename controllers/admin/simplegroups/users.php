<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This controller is used to manage users
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Admin Users Controller  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Users_Controller extends Admin_simplegroup_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->template->this_page = 'users';        
    }
    
    function index()
    {   
	
	//check the group user's permissions for this
	$permissions = groups::get_permissions_for_user($this->user->id);
	if(!($permissions["add_users"] || $permissions["delete_users"]))
	{
		url::redirect(url::site().'admin/simplegroups/dashboard');
	}
    
    
        $this->template->content = new View('simplegroups/users');
        
        // Pagination
        $pagination = new Pagination(array(
                            'query_string' => 'page',
                            'items_per_page' => 500,
                            'total_items'  => ORM::factory('user')
								->join("simplegroups_groups_users", "users.id", "simplegroups_groups_users.users_id")
								->where("simplegroups_groups_users.simplegroups_groups_id", $this->group->id)
								->count_all()
                        ));

        $users = ORM::factory('user')
			->join("simplegroups_groups_users", "users.id", "simplegroups_groups_users.users_id")
			->where("simplegroups_groups_users.simplegroups_groups_id", $this->group->id)
			->orderby('name', 'asc')
			->find_all((int) Kohana::config('settings.items_per_page_admin'), $pagination->sql_offset);

        $this->template->content->pagination = $pagination;
        $this->template->content->total_items = $pagination->total_items;
        $this->template->content->users = $users;
    }
    
    /**
    * Edit a user
    * @param bool|int $user_id The id no. of the user
    * @param bool|string $saved
    */
    function edit( $user_id = false, $saved = false )
    {    
    
	//make sure this current user is allowed to edit this user
	if($user_id)
	{
		$user_to_edit = new User_Model($user_id);
		$users_group_id = groups::get_user_group($user_to_edit);
		if($users_group_id != $this->group->id)
		{
			url::redirect(url::site().'admin/simplegroups/dashboard');
		}
		
	}

	//check the group user's permissions for this
	$permissions = groups::get_permissions_for_user($this->user->id);
	if(!($permissions["add_users"] || $permissions["delete_users"]))
	{
		url::redirect(url::site().'admin/simplegroups/dashboard');
	}

        $this->template->content = new View('simplegroups/users_edit');
        
        if ($user_id)
        {
            $user_exists = ORM::factory('user')->find($user_id);
            if ( ! $user_exists->loaded)
            {
                // Redirect
                url::redirect(url::site().'admin/simplegroups/users/');
            }
        }
        
        
        // setup and initialize form field names
        $form = array
        (
            'username'  => '',
            'password'  => '',
            'password_again'  => '',
            'name'      => '',
            'email'     => '',
            'notify'    => '',
            'role'      => ''
        );
        
        //copy the form as errors, so the errors will be stored with keys corresponding to the form field names
        $errors = $form;
        $form_error = FALSE;
        $form_saved = FALSE;
        $form_action = "";
        $user = "";
        
        // check, has the form been submitted, if so, setup validation
        if ($_POST)
        {
            $post = Validation::factory($_POST);
	    
	    //are we deleting this user
	    if($post->action == "delete")
	    {
		//do you have permission to delete
		if(!$permissions["delete_users"])
		{
			url::redirect(url::site().'admin/simplegroups/dashboard');
		}
		
		//drop the group mapping
		ORM::factory("simplegroups_groups_users")
			->where("users_id", $user_id)
			->delete_all();
		
		//drop the group roles mapping
		ORM::factory("simplegroups_users_roles")
			->where("users_id", $user_id)
			->delete_all();		

		//delete the user
		ORM::factory("user")
			->where("id", $user_id)
			->delete_all();		
		
		url::redirect(url::site().'admin/simplegroups/users');
	    }
	    

            //  Add some filters
            $post->pre_filter('trim', TRUE);
    
            $post->add_rules('username','required','length[3,16]', 'alpha');
        
            //only validate password as required when user_id has value.
            $user_id == '' ? $post->add_rules('password','required',
                'length[5,16]','alpha_numeric'):'';
            $post->add_rules('name','required','length[3,100]');
        
            $post->add_rules('email','required','email','length[4,64]');
        
            $user_id == '' ? $post->add_callbacks('username',
                array($this,'username_exists_chk')) : '';
        
            $user_id == '' ? $post->add_callbacks('email',
                array($this,'email_exists_chk')) : '';

            // If Password field is not blank
            if (!empty($post->password))
            {
                $post->add_rules('password','required','length[5,16]'
                    ,'alpha_numeric','matches[password_again]');
            }
            
            $post->add_rules('role','required','length[3,30]', 'alpha_numeric');
            
            $post->add_rules('notify','between[0,1]');
	    
	    Event::run('ushahidi_action.user_submit_admin', $post);
            
            if ($post->validate())
            {
                $user = ORM::factory('user',$user_id);
                $user->name = $post->name;
                $user->email = $post->email;
                $user->notify = $post->notify;
                
                // Existing User??
                if ($user->loaded==true)
                {
                    // Prevent modification of the main admin account username or role
                    if ($user->id != 1)
                    {
                        $user->username = $post->username;
                        
                        // Remove Old Roles
                        foreach($user->roles as $role)
                        {
                            $user->remove($role); 
                        }
                        
                        // Add New Roles
                        $user->add(ORM::factory('role', 'login'));
                        $user->add(ORM::factory('role', 'simplegroups'));
                    }
                    
                    $post->password !='' ? $user->password=$post->password : '';
                }
                // New User
                else 
                {
                    $user->username = $post->username;
                    $user->password = $post->password;
                    
                    // Add New Roles
                    $user->add(ORM::factory('role', 'login'));
                    $user->add(ORM::factory('role', 'simplegroups'));
                }
                $user->save();
		
		// Action::report_edit - Edited a Report
                Event::run('ushahidi_action.user_edit', $user);
                
                // Redirect
                url::redirect(url::site().'admin/simplegroups/users/');
            }
            else 
            {
                // repopulate the form fields
                $form = arr::overwrite($form, $post->as_array());

                // populate the error fields, if any
                $errors = arr::overwrite($errors, $post->errors('auth'));
                $form_error = TRUE;
            }
        }
        else
        {
            if ( $user_id )
            {
                // Retrieve Current Incident
                $user = ORM::factory('user', $user_id);
                if ($user->loaded == true)
                {
                    foreach ($user->roles as $user_role)
                    {
                         $role = $user_role->name;
                    }
                    
                    $form = array
                    (
                        'user_id'   => $user->id,
                        'username'  => $user->username,
                        'password'  => '',
                        'password_again'  => '',
                        'name'      => $user->name,
                        'email'     => $user->email,
                        'notify'    => $user->notify,
                        'role'      => $role
                    );
                }
            }
        }
        
        $roles = ORM::factory('role')
            ->where('id != 1')
            ->orderby('name', 'asc')
            ->find_all();
        
	//only one role for these guys
        $role_array = array("simplegroups" => "SIMPLEGROUPS");
        
        
	$this->template->content->id = $user_id;
        $this->template->content->user = $user;
        $this->template->content->form = $form;
        $this->template->content->errors = $errors;
        $this->template->content->form_error = $form_error;
        $this->template->content->form_saved = $form_saved;
        $this->template->content->yesno_array = array('1'=>strtoupper(Kohana::lang('ui_main.yes')),'0'=>strtoupper(Kohana::lang('ui_main.no')));        
        $this->template->content->role_array = $role_array;
    }
    
   /**
     * Checks if username already exists.
     * @param Validation $post $_POST variable with validation rules 
     */
    public function username_exists_chk(Validation $post)
    {
        $users = ORM::factory('user');
        // If add->rules validation found any errors, get me out of here!
        if (array_key_exists('username', $post->errors()))
            return;
                
        if ($users->username_exists($post->username))
            $post->add_error( 'username', 'exists');
    }
    
    /**
     * Check if 
     */
    
    /**
     * Checks if email address is associated with an account.
     * @param Validation $post $_POST variable with validation rules 
     */
    public function email_exists_chk( Validation $post )
    {
        $users = ORM::factory('user');
        if (array_key_exists('email',$post->errors()))
            return;
            
        if ($users->email_exists( $post->email ) )
            $post->add_error('email','exists');
    }
    
    /**
     * Checks if role already exists.
     * @param Validation $post $_POST variable with validation rules 
     */
    public function role_exists_chk(Validation $post)
    {
        $roles = ORM::factory('role')
            ->where('name', $post->name)
            ->find();
            
        // If add->rules validation found any errors, get me out of here!
        if (array_key_exists('name', $post->errors()))
            return;
                
        if ($roles->loaded)
        {
            $post->add_error( 'name', 'exists');
        }
    }   
}

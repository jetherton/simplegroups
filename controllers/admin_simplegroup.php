<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This main controller for the simple groups section 
 */

class Admin_simplegroup_Controller extends Template_Controller
{
	public $auto_render = TRUE;

	// Main template
	public $template = 'simplegroups/layout';

	// Cache instance
	protected $cache;

	// Enable auth
	protected $auth_required = FALSE;

	protected $user;

	// Table Prefix
	protected $table_prefix;
    
    protected $release;

	public function __construct()
	{
		parent::__construct();	

		// Load cache
		$this->cache = new Cache;

		// Load session
		$this->session = new Session;

		// Load database
		$this->db = new Database();

		$upgrade = new Upgrade;

		$this->auth = new Auth();
		$this->session = Session::instance();
		$this->auth->auto_login();

		if ( ! $this->auth->logged_in('login'))
		{
			url::redirect('login');
		}

		// Set Table Prefix
		$this->table_prefix = Kohana::config('database.default.table_prefix');
		
		
		//fetch latest release of ushahidi
		$this->release = $upgrade->_fetch_core_release();
		
		if( ! empty($this->release) )
		{
			$this->template->version = $this->_get_release_version();
			$this->template->critical = $this->release->critical;
		}

		

		// Get Session Information
		$this->user = new User_Model($_SESSION['auth_user']->id);

		$this->template->admin_name = $this->user->name;
		
		//make sure the user is supposed to be here:
		$group_id = groups::get_user_group($this->user);
		if(!$group_id)
		{
			url::redirect(url::site().'admin/simplegroups/nogroup');
		}
		
		
		//Get Group Info
		$groups = ORM::factory("simplegroups_groups")
			->join("simplegroups_groups_users", "simplegroups_groups.id", "simplegroups_groups_users.simplegroups_groups_id")
			->where("simplegroups_groups_users.users_id", $this->user->id)
			->find_all();
			
		foreach($groups as $group)
		{
			$this->group = $group;
			$this->template->group_name = $group->name;
			$this->template->group_logo = $group->logo;
		}

		// Retrieve Default Settings
		$this->template->site_name = Kohana::config('settings.site_name');
		$this->template->mapstraction = Kohana::config('settings.mapstraction');
		$this->template->api_url = Kohana::config('settings.api_url');

		// Javascript Header
		$this->template->map_enabled = FALSE;
		$this->template->flot_enabled = FALSE;
		$this->template->treeview_enabled = FALSE;
		$this->template->protochart_enabled = FALSE;
		$this->template->colorpicker_enabled = FALSE;
		$this->template->editor_enabled = FALSE;
		$this->template->js = '';
		$this->template->form_error = FALSE;

		// Initialize some variables for raphael impact charts
		$this->template->raphael_enabled = FALSE;
		$this->template->impact_json = '';

		// Generate main tab navigation list.
		$this->template->main_tabs = admin::main_tabs();
		// Generate sub navigation list (in default layout, sits on right side).
        $this->template->main_right_tabs = admin::main_right_tabs($this->user);

		$this->template->this_page = "";

		// Load profiler
		// $profiler = new Profiler;	
    }

	public function index()
	{
		/* Not sure about this MHI stuff
		// Send them to the right page
		if(Kohana::config('config.enable_mhi') == TRUE && Kohana::config('settings.subdomain') == '') {
			url::redirect('admin/mhi');
		}else{
			url::redirect('admin/dashboard');
		}*/
		
		url::redirect(url::site().'admin/simplegroups/dashboard_simplegroup');
	}

	public function log_out()
	{
		$auth = new Auth;
		$auth->logout(TRUE);

		url::redirect('login');
	}

    /**
     * Fetches the latest ushahidi release version number
     *
     * @return int or string
     */
    private function _get_release_version()
    {
        
        $release_version = $this->release->version;
		
        $version_ushahidi = Kohana::config('settings.ushahidi_version');
	    	
        if ($this->_new_or_not($release_version,$version_ushahidi))
        {
			return $release_version;
		} 
        else 
        {
			return "";
		}

    }
    
    /**
     * Checks version sequence parts
     *
     * @param string release_version - The version released.
     * @param string version_ushahidi - The version of ushahidi installed.
     *
     * @return boolean
     */
    private function _new_or_not($release_version=NULL,
            $version_ushahidi=NULL )
    {
        if ($release_version AND $version_ushahidi)
	    {
		    // Split version numbers xx.xx.xx
		    $remote_version = explode($release_version, ".");
		    $local_version = explode($version_ushahidi, ".");

		    // Check first part .. if its the same, move on to next part
		    if (isset($remote_version[0]) AND isset($local_version[0])
			    AND (int) $remote_version[0] > (int) $local_version[0])
		    {
			    return true;
		    }

		    // Check second part .. if its the same, move on to next part
		    if (isset($remote_version[1]) AND isset($local_version[1])
			    AND (int) $remote_version[1] > (int) $local_version[1])
		    {
			    return true;
		    }

		    // Check third part
		    if (isset($remote_version[2]) AND isset($local_version[2])
			    AND (int) $remote_version[2] > (int) $local_version[2])
		    {
			    return true;
		    }

		}

        return false;
    }


} // End Admin


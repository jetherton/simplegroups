<?php
/**
 * Performs install/uninstall methods for the smsautomate plugin
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   smsautomate Installer
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Simplegroups_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the actionable plugin
	 */
	public function run_install()
	{
		// Create the database tables.
		// Also include table_prefix in name
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'simplegroups_groups` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `name` varchar(100) default NULL,
				  `description` longtext,
				  `logo` varchar(200) default NULL,
				  `own_instance` varchar(1000) default NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
		
		//create the table that tracks the phone numbers associated with a group
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'simplegroups_groups_numbers` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `simplegroups_groups_id` int(10) unsigned NOT NULL,
				  `number` varchar(30) default NULL,
				  `name` varchar(100) default NULL,
				  `org` varchar(100) default NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
				
				
		//create the table that tracks the users associated with a group
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'simplegroups_groups_users` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `simplegroups_groups_id` int(10) unsigned NOT NULL,
				  `users_id` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
				
		
		//create the table that tracks the incidents/reports associated with a group
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'simplegroups_groups_incident` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `simplegroups_groups_id` int(10) unsigned NOT NULL,
				  `incident_id` int(10) unsigned NOT NULL,
				  `number_id` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
				
			
		//create the table that tracks the messages associated with a group
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'simplegroups_groups_message` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `simplegroups_groups_id` int(10) unsigned NOT NULL,
				  `message_id` int(10) unsigned NOT NULL,
				  `number_id` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
				
		//check and see if the simplegroups role already exists
		if(ORM::factory('role')->where("name", "simplegroups")->count_all() == 0)
		{
			//create a role called simplegroups that all group users must be a part of.
			$this->db->query("INSERT INTO `".Kohana::config('database.default.table_prefix')."roles` (`id` ,`name` ,`description` ,`reports_view` ,
				`reports_edit` ,`reports_evaluation` ,`reports_comments` ,`reports_download` ,`reports_upload` ,
				`messages` ,`messages_reporters` ,`stats` ,`settings` ,`manage` ,`users`)
				VALUES (NULL ,  'simplegroups',  'All group members of the Simple Groups plugin should have this role',  
				'0',  '0',  '0',  '0',  '0',  '0',  '0',  '0',  '0',  '0',  '0', '0');");
		}
		
		//create roles table for simple groups
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'simplegroups_roles` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `name` varchar(100) default NULL,
				  `edit_group_settings` tinyint(4) NOT NULL default \'0\',
				  `add_users` tinyint(4) NOT NULL default \'0\',
				  `delete_users` tinyint(4) NOT NULL default \'0\',
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
				
		//now make up some roles
		//admin can add new users
		//super admin can add new users, remove users, and edit group settings
		if(!ORM::factory('simplegroups_roles')->where('name', 'Admin')->find()->loaded)
		{
			$admin = ORM::factory('simplegroups_roles');
			$admin->name = "Admin";
			$admin->edit_group_settings = 0;
			$admin->add_users = 1;
			$admin->delete_users = 0;
			$admin->save();
		}
		
		if(!ORM::factory('simplegroups_roles')->where('name', 'Super Admin')->find()->loaded)
		{
			$su_admin = ORM::factory('simplegroups_roles');
			$su_admin->name = "Super Admin";
			$su_admin->edit_group_settings = 1;
			$su_admin->add_users = 1;
			$su_admin->delete_users = 1;
			$su_admin->save();
		}
		
		//create mapping between simple groups users and their roles.
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'simplegroups_users_roles` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `roles_id` int(10) unsigned NOT NULL,
				  `users_id` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
		
		
		//check and see if the simplegroups_groups table already has the own_instance field. If not make it
		$result = $this->db->query('DESCRIBE `'.Kohana::config('database.default.table_prefix').'simplegroups_groups`');
		$has_own_instance = false;
		foreach($result as $row)
		{
			if($row->Field == "own_instance")
			{
				$has_own_instance = true;
				break;
			}
		}
		
		if(!$has_own_instance)
		{
			$this->db->query('ALTER TABLE `'.Kohana::config('database.default.table_prefix').'simplegroups_groups` ADD `own_instance` VARCHAR(1000) NULL DEFAULT NULL');
		}
		
					
	}//end of run_install

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'simplegroups_groups`');
	}
}
<?php defined('SYSPATH') or die('No direct script access.');
/**
* Model for Simeple Groups
 *
 * @author     John Etherton - john.etherton@gmail.com
 * @package    Simple Groups plugin
 */

class Simplegroups_groups_Model extends ORM
{
	
	// Database table name
	protected $table_name = 'simplegroups_groups';
	
	protected $has_many = array("simplegroups_groups_number", 
							"user"=>"simplegroups_groups_users", 
							"incident"=>"simplegroups_groups_incident", 
							"message"=>"simplegroups_groups_message");
}

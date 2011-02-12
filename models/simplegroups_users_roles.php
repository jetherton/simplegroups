<?php defined('SYSPATH') or die('No direct script access.');

/**
* Model for users in a group
 *
 * 
 * @author     John Etherton <john.etherton@gmail.com>
 */

class Simplegroups_users_roles_Model extends ORM
{
	protected $belongs_to = array('simplegroups_roles', 'user');
	
	// Database table name
	protected $table_name = 'simplegroups_users_roles';
}

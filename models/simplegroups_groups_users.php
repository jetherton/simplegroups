<?php defined('SYSPATH') or die('No direct script access.');

/**
* Model for users in a group
 *
 * 
 * @author     John Etherton <john.etherton@gmail.com>
 */

class Simplegroups_groups_users_Model extends ORM
{
	protected $belongs_to = array('simplegroups_groups', 'user');
	
	// Database table name
	protected $table_name = 'simplegroups_groups_users';
}

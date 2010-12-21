<?php defined('SYSPATH') or die('No direct script access.');
/**
* Model for Simeple Groups
 *
 * @author     John Etherton - john.etherton@gmail.com
 * @package    Simple Groups plugin
 */

class Simplegroups_groups_number_Model extends ORM
{
	
	// Database table name
	protected $table_name = 'simplegroups_groups_numbers';
	
	protected $belongs_to = array("simplegroups_groups");
}

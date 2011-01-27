<?php defined('SYSPATH') or die('No direct script access.');

/**
* Model for incidents in a group
 *
 * 
 * @author     John Etherton <john.etherton@gmail.com>
 */

class Simplegroups_groups_incident_Model extends ORM
{
	protected $belongs_to = array('simplegroups_groups', 'incident');
	
	// Database table name
	protected $table_name = 'simplegroups_groups_incident';
}

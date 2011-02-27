<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model for Categories of reported Incidents
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Category Model  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Simplegroups_category_Model extends ORM_Tree
{	
	protected $has_many = array('incident' => 'simplegroups_incident_category', 'simplegroups_category_lang');
	
	// Database table name
	protected $table_name = 'simplegroups_category';
	protected $children = 'simplegroups_category';
	
}

<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This controller is used for the main Admin panel
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Admin Dashboard Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class Forwardto_Controller extends Admin_Controller
{
    function __construct()
    {
        parent::__construct();
    }



    function index($message_id, $group_id)
    {
	$this->auto_render = FALSE;
	$this->template = "";
	
	//check if this link already exists
	$group_messages = ORM::factory("simplegroups_groups_message")
		->where("message_id", $message_id)
		->where("simplegroups_groups_id", $group_id)
		->find_all();
	foreach($group_messages as $group_message)
	{
		return;
	}
	
	$group_message = ORM::factory("simplegroups_groups_message");
	$group_message->message_id = $message_id;
	$group_message->simplegroups_groups_id = $group_id;
	$group_message->save();
	
    }//end index()

}
?>

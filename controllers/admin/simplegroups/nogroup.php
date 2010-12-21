<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This controller is used for the simple groupls plugin
 *
 * @author     John Etherton <john.etherton@gmail.com>
 * @package   Simple Groups plugin
 */

class Nogroup_Controller extends Controller
{
    function __construct()
    {
        parent::__construct();
    }



    function index()
    {
        $view = new View('simplegroups/nogroup');
	$view->render(TRUE);

    }//end index()

}
?>

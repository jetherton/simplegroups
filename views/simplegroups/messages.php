<?php 
/**
 * Messages view page.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     API Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>




			<div class="bg">
				<h2>
					<?php groups::messages_subtabs($service_id); ?>
				</h2>

<?php
	Event::run('ushahidi_action.admin_messages_custom_layout');
	// Kill the rest of the page if this event has been utilized by a plugin
	if( ! Event::has_run('ushahidi_action.admin_messages_custom_layout')){
?>

				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<ul class="tabset">
						<li><a href="#" id="inbox_tab"  onclick="filterTabClick('inbox_tab'); return false;" <?php if ($type == '1') echo "class=\"active\""; ?>><?php echo Kohana::lang('ui_main.all');?></a></li>
						<li><a href="#" id="turned_into_reports_tab" onclick="filterTabClick('turned_into_reports_tab'); return false;"><?php echo Kohana::lang('simplegroups.turn');?> </a></li>
						<li><a href="#" id="three_days_tab" onclick="filterTabClick('three_days_tab'); return false;"><?php echo Kohana::lang('simplegroups.recent');?> </a></li>
					</ul>
					<!-- tab -->
					<div class="tab">
						<ul>
							<li><a href="#" id="delete_all_button" onClick="messagesAction('d', 'DELETE', ''); return false;"><?php echo strtoupper(Kohana::lang('ui_main.delete'));?></a></li>
							<li> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
							<li><?php echo Kohana::lang('simplegroups.filter');?> <?php print form::dropdown(array('id'=>'cat_filter', 'onChange'=>'filterAction(); return false;'), $category_array); ?> </li>
							<li id="filter_wait"></li>
						</ul>
					</div>
				</div>
				<?php
				if ($form_error) {
				?>
					<!-- red-box -->
					<div class="red-box">
						<h3><?php echo Kohana::lang('ui_main.error');?></h3>
						<ul><?php echo Kohana::lang('ui_main.select_one');?></ul>
					</div>
				<?php
				}

				if ($form_saved) {
				?>
					<!-- green-box -->
					<div class="green-box" id="submitStatus">
						<h3><?php echo Kohana::lang('ui_main.messages');?> <?php echo $form_action; ?> <a href="#" id="hideMessage" class="hide"><?php echo Kohana::lang('simplegroups.hide');?></a></h3>
					</div>
				<?php
				}
				?>
				<!-- report-table -->
				<?php print form::open(NULL, array('id' => 'messageMain', 'name' => 'messageMain')); ?>
				
					<input type="hidden" name="service_id" id="service_id" value="<?php echo $service_id; ?>"/>
					
					<input type="hidden" name="action" id="action" value=""/>
					<input type="hidden" name="level"  id="level"  value=""/>
					<input type="hidden" name="message_id[]" id="message_single" value=""/>
					
					
					<div class="table-holder" id="table_holder">

					
						<?php //render the table via a seperate view
							$table_view = View::factory('simplegroups/messages/messages_table');
							$table_view->pagination = $pagination;
							$table_view->messages = $messages;
							$table_view->service_id = $service_id;
							$table_view->total_items = $total_items;
							$table_view->category_mapping = $category_mapping;
							$table_view->reply_to = $reply_to;
							$table_view->render(TRUE);														
						?>
							
					</div>					
				<?php //print form::close(); ?>
			</div>

<?php
	}
?>

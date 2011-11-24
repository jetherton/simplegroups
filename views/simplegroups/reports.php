<?php
/**
 * Reports view page.
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
					<?php groups::reports_subtabs("view"); ?>
				</h2>
				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<ul class="tabset">
						<li><a id="status_filter_0" href="#" onclick="changeStatus('0'); return false;" <?php if ($status != '2' && $status !='v') echo "class=\"active\""; ?>><?php echo Kohana::lang('ui_main.show_all');?></a></li>
						<li><a id="status_filter_2"href="#" onclick="changeStatus('2'); return false;"<?php if ($status == '2') echo "class=\"active\""; ?>><?php echo Kohana::lang('ui_main.awaiting_approval');?></a></li>
						<li><a id="status_filter_v"href="#" onclick="changeStatus('v'); return false;"<?php if ($status == 'v') echo "class=\"active\""; ?>><?php echo Kohana::lang('ui_main.awaiting_verification');?></a></li>
					</ul>
					<!-- tab -->
					<div class="tab">
						<ul>
							<li><a id="approve_button" href="#" onclick="reportAction('a','APPROVE', '', 'approve_button'); return false;"><?php echo Kohana::lang('ui_main.approve');?></a></li>
							<li><a id="unapprove_button" href="#" onclick="reportAction('u','UNAPPROVE', '', 'unapprove_button'); return false;"><?php echo Kohana::lang('ui_main.disapprove');?></a></li>
							<li><a id="verify_button" href="#" onclick="reportAction('v','VERIFY', '', 'verify_button'); return false;"><?php echo Kohana::lang('ui_main.verify');?></a></li>
							<li><a id="delete_button" href="#" onclick="reportAction('d','DELETE', '', 'delete_button'); return false;"><?php echo Kohana::lang('ui_main.delete');?></a></li>
							<li> <?php echo Kohana::lang('simplegroups.filter');?> <?php print form::dropdown(array('id'=>'cat_filter', 'onChange'=>'changeCategoryFilter(); return false;'), $category_array); ?> </li>
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
						<h3><?php echo Kohana::lang('ui_main.reports');?> <?php echo $form_action; ?> <a href="#" id="hideMessage" class="hide"><?php echo Kohana::lang('simplegroups.hide');?></a></h3>
					</div>
				<?php
				}
				?>
				<!-- report-table -->
				<?php print form::open(NULL, array('id' => 'reportMain', 'name' => 'reportMain')); ?>
					<input type="hidden" name="action" id="action" value="">
					<input type="hidden" name="incident_id[]" id="incident_single" value="">
					<div class="table-holder" id="table_holder">
						<?php
							$table_view = View::factory('simplegroups/reports/reports_table');
							$table_view->pagination = $pagination;
							$table_view->total_items = $total_items;
							$table_view->incidents = $incidents;
							$table_view->locations = $locations;
							$table_view->countries = $countries;
							$table_view->category_mapping = $category_mapping;
							$table_view->persons = $persons;
							$table_view->verifieds = $verifieds;
							$table_view->incident_translations = $incident_translations;
							$table_view->reg_category_mapping = $reg_category_mapping;
							$table_view->orm_incidents = $orm_incidents;
							$table_view->render(TRUE);
						?>
					</div>
				<?php print form::close(); ?>
			</div>

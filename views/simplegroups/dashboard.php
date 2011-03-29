<?php 
/**
 * Dashboard view page.
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
				<h2><?php echo $title; ?></h2>
				<!-- column -->
				<div class="column">
					<!-- info-container -->
					<div class="info-container">
						<div class="i-c-head">
							<h3><?php echo Kohana::lang('ui_main.recent_reports');?></h3>
							<ul>
								<li class="none-separator"><a href="<?php echo url::site() . 'admin/simplegroups/reports' ?>"><?php echo Kohana::lang('ui_main.view_all');?></a></li>
							</ul>
						</div>
						<?php
						if ($reports_total == 0)
						{
						?>
						<div class="post">
							<h3><?php echo Kohana::lang('ui_main.no_results');?></h3>
						</div>
						<?php	
						}
						foreach ($incidents as $incident)
						{
							$incident_id = $incident->id;
							$incident_title = $incident->incident_title;
							$incident_description = text::limit_chars(strip_tags($incident->incident_description), 150, '...');
							$incident_date = $incident->incident_date;
							$incident_date = date('D M d Y g:i A', strtotime($incident->incident_date));
							$incident_mode = $incident->incident_mode;	// Mode of submission... WEB/SMS/EMAIL?
							?>
							<div class="post">
								<h4><strong><?php echo $incident_date; ?></strong><a href="<?php echo url::site() . 'admin/simplegroups/reports/edit/' . $incident_id; ?>"><?php echo $incident_title; ?></a></h4>
								<p><?php echo $incident_description; ?></p>
							</div>
							<?php
						}
						?>
						<a href="<?php echo url::site() . 'admin/simplegroups/reports' ?>" class="view-all"><?php echo Kohana::lang('ui_main.view_all_reports');?></a>
					</div>
				</div>
				<div class="column-1">
					<!-- box -->
					<div class="box">
						<h3><?php echo Kohana::lang('ui_main.quick_stats');?></h3>
						<ul class="nav-list">
							<li>
								<a href="<?php echo url::site() . 'admin/simplegroups/reports' ?>" class="reports"><?php echo Kohana::lang('ui_main.reports');?></a>
								<strong><?php echo number_format($reports_total); ?></strong>
								<ul style="overflow:visible;">
									<li><a href="<?php echo url::site() . 'admin/simplegroups/reports?status=a' ?>"><?php echo Kohana::lang('ui_main.not_approved');?></a><strong>(<?php echo $reports_total_unapproved; ?>)</strong></li>
								</ul>
							</li>
							<li>
								<a href="<?php echo url::site() . 'admin/simplegroups/messages' ?>" class="messages"><?php echo Kohana::lang('ui_main.messages');?></a>
								<strong><?php echo number_format($message_count); ?></strong>
							</li>
							<li>
								<a href="<?php echo url::site() . 'admin/simplegroups/settings/categories' ?>" class="categories"><?php echo Kohana::lang('ui_main.categories');?></a>
								<strong><?php echo number_format($categories); ?></strong>
							</li>
						</ul>
					</div>
					<!-- info-container -->
					<div class="info-container">
						<div class="i-c-head">
							<h3><?php echo Kohana::lang('ui_main.news_feeds');?></h3>
						</div>
						<?php
						foreach ($feeds as $feed)
						{
							$feed_id = $feed->id;
							$feed_title = $feed->item_title;
							$feed_description = text::limit_chars(strip_tags($feed->item_description), 150, '...', True);
							$feed_link = $feed->item_link;
							$feed_date = date('M j Y', strtotime($feed->item_date));
							$feed_source = "NEWS";
							?>
							<div class="post">
								<h4><a href="<?php echo $feed_link; ?>" target="_blank"><?php echo $feed_title ?></a></h4>
								<em class="date"><?php echo $feed_source; ?> - <?php echo $feed_date; ?></em>
								<p><?php echo $feed_description; ?></p>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>


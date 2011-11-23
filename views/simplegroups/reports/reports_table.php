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
						<table class="table">
							<thead>
								<tr>
									<th class="col-1"><input id="checkallincidents" type="checkbox" class="check-box" onclick="CheckAll( this.id, 'incident_id[]' )" /></th>
									<th class="col-2"><?php echo Kohana::lang('ui_main.report_details');?></th>
									<th class="col-3"><?php echo Kohana::lang('ui_main.date');?></th>
									<th class="col-4"><?php echo Kohana::lang('ui_main.actions');?></th>
								</tr>
							</thead>
							<tfoot>
								<tr class="foot">
									<td colspan="4">
										<?php echo $pagination; ?>
									</td>
								</tr>
							</tfoot>
							<tbody>
								<?php
								if ($total_items == 0)
								{
								?>
									<tr>
										<td colspan="4" class="col">
											<h3><?php echo Kohana::lang('ui_main.no_results');?></h3>
										</td>
									</tr>
								<?php
								}
								foreach ($incidents as $incident)
								{
									$incident_id = $incident->incident_id;
									$incident_title = $incident->incident_title;
									$incident_description = text::limit_chars(strip_tags($incident->incident_description), 150, "...", true);
									$incident_date = $incident->incident_date;
									$incident_date = date('Y-m-d H:i' , strtotime($incident->incident_date));
									$incident_mode = $incident->incident_mode;	// Mode of submission... WEB/SMS/EMAIL?

									//XXX incident_Mode will be discontinued in favour of $service_id
									if ($incident_mode == 1)	// Submitted via WEB
									{
										$submit_mode = "WEB";
										// Who submitted the report?
										$person = $persons[$incident_id];
										if ($person != null)
										{
											// Report was submitted by a visitor
											$submit_by = $person->person_first . " " . $person->person_last;
										}
										else
										{
											if ($incident->user_id)					// Report Was Submitted By Administrator
											{
												$submit_by = $incident->user->name;
											}
											else
											{
												$submit_by = 'Unknown';
											}
										}
									}
									elseif ($incident_mode == 2) 	// Submitted via SMS
									{
										$submit_mode = "SMS";
										$submit_by = $incident->message->message_from;
									}
									elseif ($incident_mode == 3) 	// Submitted via Email
									{
										$submit_mode = "EMAIL";
										$submit_by = $incident->message->message_from;
									}
									elseif ($incident_mode == 4) 	// Submitted via Twitter
									{
										$submit_mode = "TWITTER";
										$submit_by = $incident->message->message_from;
									}
									elseif ($incident_mode == 5) 	// Submitted via Laconica
									{
										$submit_mode = "LACONICA";
										$submit_by = $incident->message->message_from;
									}

									$incident_location = $locations[$incident->location_id];

									// Retrieve Incident Categories
									$incident_category = "";
									$i = 0;
									
									if(isset($reg_category_mapping[$incident_id]))
									{
										foreach($reg_category_mapping[$incident_id] as $category)
										{
											$i++;
											if($i > 1)
											{
												$incident_category.= "&nbsp;|&nbsp;";
											}
											$incident_category .= "<span style=\"color:#9b0000;\">" . $category->category_title . "</span>&nbsp;&nbsp;";
										}
									}
									
									if(isset($category_mapping[$incident_id]))
									{
										foreach($category_mapping[$incident_id] as $category)
										{
											$i++;
											if($i > 1)
											{
												$incident_category.= "&nbsp;|&nbsp;";
											}
											$incident_category .= "<span style=\"color:#9b0000;\">" . $category->category_title . "</span>&nbsp;&nbsp;";
										}
									}
									

									// Incident Status
									$incident_approved = $incident->incident_active;
									$incident_verified = $incident->incident_verified;
									
									// Get Edit Log
									$incident_log = $verifieds[$incident_id];
									$edit_count = count($incident_log);
									$edit_css = ($edit_count == 0) ? "post-edit-log-red" : "post-edit-log-gray";
									$edit_log  = "<div class=\"".$edit_css."\">";
									$edit_log .= "<a href=\"javascript:showLog('edit_log_".$incident_id."')\">".Kohana::lang('ui_admin.edit_log').":</a> (".$edit_count.")</div>";
									$edit_log .= "<div id=\"edit_log_".$incident_id."\" class=\"post-edit-log\"><ul>";
									foreach ($incident_log as $verify)
									{
										$edit_log .= "<li>".Kohana::lang('ui_admin.edited_by')." ".$verify->user->name." : ".$verify->verified_date."</li>";
									}
									$edit_log .= "</ul></div>";

									// Get Any Translations
									$i = 1;
									$incident_translation  = "<div class=\"post-trans-new\">";
									$incident_translation .= "<a href=\"" . url::base() . 'admin/simplegroups/reports/translate/?iid=' . $incident_id . "\">".strtoupper(Kohana::lang('ui_main.add_translation')).":</a></div>";
									if(isset($incident_translations[$incident_id]))
									{
										$incident_langs = $incident_translations[$incident_id];
										foreach ($incident_langs as $translation) {
											$incident_translation .= "<div class=\"post-trans\">";
											$incident_translation .= Kohana::lang('ui_main.translation'). $i . ": ";
											$incident_translation .= "<a href=\"" . url::base() . 'admin/simplegroups/reports/translate/'. $translation->id .'/?iid=' . $incident_id . "\">"
												. text::limit_chars($translation->incident_title, 150, "...", true)
												. "</a>";
											$incident_translation .= "</div>";
										}
									}
									?>
									<tr>
										<td class="col-1"><input name="incident_id[]" id="incident" value="<?php echo $incident_id; ?>" type="checkbox" class="check-box"/></td>
										<td class="col-2">
											<div class="post">
												<h4><a href="<?php echo url::site() . 'admin/simplegroups/reports/edit/' . $incident_id; ?>" class="more"><?php echo $incident_title; ?></a></h4>
												<p><?php echo $incident_description; ?>... <a href="<?php echo url::base() . 'admin/simplegroups/reports/edit/' . $incident_id; ?>" class="more"><?php echo Kohana::lang('ui_main.more');?></a></p>
											</div>
											<ul class="info">
												<li class="none-separator"><?php echo Kohana::lang('ui_main.location');?>: <strong><?php echo $incident_location; ?></strong>, <strong><?php echo $countries[Kohana::config('settings.default_country')]; ?></strong></li>
												<li><?php echo Kohana::lang('ui_main.submitted_by');?> <strong><?php echo $submit_by; ?></strong><?php echo Kohana::lang('simplegroups.via');?><strong><?php echo $submit_mode; ?></strong></li>
											</ul>
											<ul class="links">
												<li class="none-separator"><?php echo Kohana::lang('ui_main.categories');?>:<?php echo $incident_category; ?></li>
											</ul>
											<?php
											echo $edit_log;
											
											// Action::report_extra_admin - Add items to the report list in admin
											Event::run('ushahidi_action.report_extra_admin', $incident);
											?>
										</td>
										<td class="col-3"><?php echo $incident_date; ?></td>
										<td class="col-4">
											<ul>
												<li class="none-separator">
													<?php if($incident_approved) {?>
														<a title="Click to Unapprove this report" href="#"<?php if ($incident_approved) echo " class=\"status_yes\"" ?> onclick="reportAction('a','APPROVE', '<?php echo $incident_id; ?>', 'individual_report_action_<?php echo $incident_id; ?>'); return false;"><?php echo Kohana::lang('ui_main.approve');?></a>
													<?php } else { ?>
													<?php echo Kohana::lang('simplegroups.unprove');?><a title="Click to Approve this report" style="font-size:75%;" href="#"<?php if ($incident_approved) echo " class=\"status_yes\"" ?> onclick="reportAction('a','APPROVE', '<?php echo $incident_id; ?>', 'individual_report_action_<?php echo $incident_id; ?>'); return false;"><?php echo Kohana::lang('simplegroups.approve');?></a>
													<?php } ?>
													
													
												</li>
												<li><a href="#"<?php if ($incident_verified) echo " class=\"status_yes\"" ?> onclick="reportAction('v','VERIFY', '<?php echo $incident_id; ?>', 'individual_report_action_<?php echo $incident_id; ?>'); return false;"><?php echo Kohana::lang('ui_main.verify');?></a></li>
												<li><a href="#" class="del" onclick="reportAction('d','DELETE', '<?php echo $incident_id; ?>', 'individual_report_action_<?php echo $incident_id; ?>'); return false;"><?php echo Kohana::lang('ui_main.delete');?></a></li>
												<li id="individual_report_action_<?php echo $incident_id; ?>"></li>
											</ul>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
					
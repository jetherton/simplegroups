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


						<table class="table">
							<thead>
								<tr>
									<th class="col-1"><input id="checkall" type="checkbox" class="check-box" onclick="CheckAll( this.id, 'message_id[]' )" /></th>
									<th class="col-2"><?php echo Kohana::lang('ui_main.message_details');?></th>
									<th class="col-3"><?php echo Kohana::lang('simplegroups.cat');?></th>
									<th class="col-4"><?php echo Kohana::lang('ui_main.actions');?></th>
								</tr>
							</thead>
							<tfoot>
								<tr class="foot">
									<td colspan="5">
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
										<td colspan="5" class="col">
											<h3><?php echo Kohana::lang('ui_main.no_results');?></h3>
										</td>
									</tr>
								<?php	
								}
								foreach ($messages as $message)
								{
									$message_id = $message->id;
									$message_from = $message->reporter->service_account;
									$message_to = $message->message_to;
									$incident_id = $message->incident_id;
									$message_description = text::auto_link($message->message);
									$message_detail = nl2br(text::auto_link($message->message_detail));
									$message_date = date('Y-m-d H:i', strtotime($message->message_date));
									$message_type = $message->message_type;
									$message_level = $message->message_level;																		
									$level_id = $message->reporter->level_id;
									
									?>
									
									<tr>
									<td colspan="4" style="border:none; height:20px;">
									</td>
									</tr>
									
									<tr>
									<td colspan="4" style="border:3px solid #999; font-size:15pt; line-height:normal;">
									<input style="margin: 0 10px;" name="message_id[]" id="message" value="<?php echo $message_id; ?>" type="checkbox" class="check-box"/>
									Message: <?php echo $message_description; ?> 
									</td>
									</tr>
									
									<tr <?php if ($message_level == "99") {
										echo " class=\"spam_tr\"";
									} ?> >
										<td class="col-2" colspan="2" style="border-left:2px solid #BBB; ">
											<div class="post" >
												<?php
												if ($message_detail)
												{
												?>
													<p><a href="javascript:preview('message_preview_<?php echo $message_id?>')"><?php echo Kohana::lang('ui_main.preview_message');?></a></p>
													<div id="message_preview_<?php echo $message_id?>" style="display:none;">
														<?php echo $message_detail; ?>
														
														<?php
														// Retrieve Attachments if any
														foreach($message->media as $photo) 
														{
															if ($photo->media_type == 1)
															{
																print "<div class=\"attachment_thumbs\" id=\"photo_". $photo->id ."\">";

																$thumb = $photo->media_thumb;
																$photo_link = $photo->media_link;
																			$prefix = url::base().Kohana::config('upload.relative_directory');
																print "<a class='photothumb' rel='lightbox-group".$message_id."' href='$prefix/$photo_link'>";
																print "<img src=\"$prefix/$thumb\" border=\"0\" >";
																print "</a>";
																print "</div>";
															}
														}
														?>
											</div>
											<?php
												}												
											?>
											
											<?php Event::run('ushahidi_action.message_extra_admin', $message_id); ?>
											
											<!--start reply_to-->
											<?php if($reply_to == TRUE) { ?>

												<?php
												if ($service_id == 1 && $message_type == 1)
												{
													?>
													<div id="replies">

													</div>
													<a href="javascript:showReply('reply_<?php echo $message_id; ?>')" class="more">+<?php echo Kohana::lang('ui_main.reply');?></a>
													<div id="reply_<?php echo $message_id; ?>" class="reply">
														<?php print form::open(url::site() . 'admin/messages/send/',array('id' => 'newreply_' . $message_id,
														 	'name' => 'newreply_' . $message_id)); ?>
														<div class="reply_can"><a href="javascript:cannedReply('1', 'message_<?php echo $message_id; ?>')">+<?php echo Kohana::lang('ui_main.request_location');?></a>&nbsp;&nbsp;&nbsp;<a href="javascript:cannedReply('2', 'message_<?php echo $message_id; ?>')">+<?php echo Kohana::lang('ui_main.request_information');?></a></div>
														<div id="replyerror_<?php echo $message_id; ?>" class="reply_error"></div>
														<div class="reply_input"><?php print form::input('message_' .  $message_id, '', ' class="text long2" onkeyup="limitChars(this.id, \'160\', \'replyleft_' . $message_id . '\')" '); ?></div>
														<div class="reply_input"><a href="javascript:sendMessage('<?php echo $message_id; ?>' , 'sending_<?php echo $message_id; ?>')" title="Submit Message"><img src="<?php echo url::base() ?>media/img/admin/btn-send.gif" alt="Submit" border="0" /></a></div>
														<div class="reply_input" id="sending_<?php echo $message_id; ?>"></div>
														<div style="clear:both"></div>
														<?php print form::close(); ?>
														<div id="replyleft_<?php echo $message_id; ?>" class="replychars"></div>
													</div>
													<?php
												}
												?>

											<?php } ?>
											<!--/end reply_to-->

											</div>
											
											<ul class="info">
												<?php
												if ($message_type == 2)
												{
													?>
														<li class="none-separator"><?php echo Kohana::lang('simplegroups.to');?><strong><?php echo $message_to; ?></strong></li>
													<?php
												}
													?>
												<li class="none-separator"><?php echo Kohana::lang('simplegroups.fro');?><strong class="reporters_<?php echo $level_id?>"><?php echo $message_from; ?></strong></li>
												
												<li class="none-separator"><?php echo Kohana::lang('simplegroups.date');?><strong class="reporters_0"><?php echo $message_date; ?></strong></li>
												
											</ul>
											<div><span style="font-size:10px; color:#636363;"><?php echo Kohana::lang('simplegroups.comets');?></span> 
												<textarea rows="3" cols="38" id="comments_<?php echo $message->id; ?>"><?php echo $message->comments; ?></textarea>
											</div>
										</td>
										<td class="col-3" style="padding-right:10px;" >
											<div id="message_cat_info_<?php echo $message->id; ?>">
											
											<?php
												$view = View::factory('simplegroups/messages/message_category_info');					
												$view->message_id = $message->id;
												$view->category_mapping = $category_mapping;
												$view->render(TRUE);
											?>
											
											</div>
										</td>										
										<td class="col-4">
												<?php
												if ($incident_id != 0 && $message_type != 2) {
													echo "<a class=\"comments_button\" href=\"". url::base() . 'admin/simplegroups/reports/edit/' . $incident_id ."\" >View Report</a>";
												}
												elseif ($message_type != 2)
												{
													echo "<a class=\"comments_button\" href=\"". url::base() . 'admin/simplegroups/reports/edit?mid=' . $message_id ."\">Create Report?</a>";
												}
												?>
												
												<a class="comments_button" id="commentsButton_<?php echo(rawurlencode($message_id)); ?>"  href="#" onclick="editComments('<?php echo(rawurlencode($message_id)); ?>'); return false;"><?php echo Kohana::lang('simplegroups.up');?></a>
												<a  class="comments_button"  href="#" onclick="editCategory(<?php echo $message->id; ?>,this); return false;"><?php echo Kohana::lang('simplegroups.edit');?></a>
												<a class="delete_button" href="javascript:messagesAction('d','DELETE','<?php echo(rawurlencode($message_id)); ?>')" id="delete_message_<?php echo(rawurlencode($message_id)); ?>" class="del"><?php echo Kohana::lang('ui_main.delete');?></a>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>

<?php 
/**
 * Edit User
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Edit User View
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>
			<div class="bg">
				<h2>
					<a href="http://montserrado/ushahidi-1/Ushahidi_Web/admin/simplegroups/users/">View Users</a>
					Add/Edit Users
				</h2>
				<?php
				if ($form_error) {
				?>
					<!-- red-box -->
					<div class="red-box">
						<h3><?php echo Kohana::lang('ui_main.error');?></h3>
						<ul>
						<?php
						foreach ($errors as $error_item => $error_description)
						{
							print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
						}
						?>
						</ul>
					</div>
				<?php
				}

				if ($form_saved) {
				?>
					<!-- green-box -->
					<div class="green-box">
						<h3><?php echo Kohana::lang('ui_main.profile_saved');?></h3>
					</div>
				<?php
				}
				?>
				<?php print form::open(null, array('id'=>'mainForm')); ?>
				<input type="hidden" name="action" id="action" value="none" />
				<div class="report-form">			
					<div class="head">
						<input type="image" src="<?php echo url::base() ?>media/img/admin/btn-save-settings.gif" class="save-rep-btn" />
						<a href="#" onclick="var answer = confirm('Are You Sure You Want To Delete This User?')
										if (answer)
										{
											// Set Category ID																						
											$('#action').val('delete');		
											// Submit Form
											$('#mainForm').submit();			
										}
			" style="border:solid 1px #995555; padding:5px; float:right; margin-right:20px; color:#000; background-color:#cc9999; text-decoration:none;" > DELETE</a>
					</div>
					<!-- column -->		
					<div class="sms_holder">
						<div class="row">
							<h4><?php echo Kohana::lang('ui_main.username');?></h4>
							<?php
							if ($user AND $user->loaded AND $user->id == 1)
							{
								print form::input('username', $form['username'], ' class="text long2" readonly="readonly"');
							}
							else
							{
								print form::input('username', $form['username'], ' class="text long2"');
							}
							?>
						</div>
						<div class="row">
							<h4><?php echo Kohana::lang('ui_main.full_name');?></h4>
							<?php print form::input('name', $form['name'], ' class="text long2"'); ?>
						</div>
						<div class="row">
							<h4><?php echo Kohana::lang('ui_main.email');?></h4>
							<?php print form::input('email', $form['email'], ' class="text long2"'); ?>
						</div>
						<div class="row">
							<h4><?php echo Kohana::lang('ui_main.password');?></h4>
							<?php print form::password('password', $form['password'], ' class="text"'); ?>
							<div style="clear:both;"></div>
							<?php echo Kohana::lang('ui_main.password_again');?>:<br />
							<?php print form::password('password_again', $form['password_again'], ' class="text"'); ?>
						</div>
						<div class="row">
							<h4><?php echo Kohana::lang('ui_main.role');?></h4>
							<?php
							if ($user AND $user->loaded AND $user->id == 1)
							{
								print form::dropdown('role', $role_array, $form['role'], ' readonly="readonly"');
							}
							else
							{
								print form::dropdown('role', $role_array, $form['role']);
							}
							?>
						</div>
						<div class="row">
							<h4><?php echo Kohana::lang('ui_main.receive_notifications');?>?</h4>
							<?php print form::dropdown('notify', $yesno_array, $form['notify']); ?>
						</div>
                        <?php 
                        // users_form_admin - add content to users from
                        Event::run('ushahidi_action.users_form_admin', $id);
                        ?>
					</div>
		
					<div class="simple_border"></div>
		
					<input type="image" src="<?php echo url::base() ?>media/img/admin/btn-save-settings.gif" class="save-rep-btn" />
				</div>
				<?php print form::close(); ?>
			</div>

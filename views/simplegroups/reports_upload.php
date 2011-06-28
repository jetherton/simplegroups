<?php 
/**
 * Reports upload view page.
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
		<?php groups::reports_subtabs("upload"); ?>
	</h2>
	<!-- report-form -->
	<div class="report-form">
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
		?>
		<!-- column -->
		<div class="upload_container">
		<p><?php echo Kohana::lang('ui_main.upload_reports_detail_1');?>.</p>
		<h3><?php echo Kohana::lang('ui_main.please_note');?></h3>
		<ul>
			<li><?php echo Kohana::lang('ui_main.upload_reports_detail_2');?>.</li>
			<li><?php echo Kohana::lang('ui_main.upload_reports_detail_3');?>.</li>
			<li><?php echo Kohana::lang('ui_main.upload_reports_detail_4');?></li>
		</ul>
			<p style="font-size:75%;">
				INCIDENT TITLE,INCIDENT DATE,LOCATION,DESCRIPTION,CATEGORY, GROUP CATEGORY, LATITUDE, LONGITUDE, APPROVED,VERIFIED
				<p style="font-size:75%;">					
					"Riot","2009-05-15 01:06:00","Gbanga","Three cases have been confirmed in C. del Uruguay","DEATHS, CIVILIANS, ","POLICE NOTIFIED, SENSATIVE, ", 7.0133, -10.513, YES,YES
					<br/>
					"Looting","2009-03-18 10:10:00","Accra","Looting happening everywhere","RIOTS, DEATHS, PROPERTY LOSS, ", "POLICE NOT NOTIFIED, SENSATIVE, ",6.34309, -11.7422, YES,NO
					
				</p>
			</p>
			<?php print form::open(NULL, array('id' => 'uploadForm', 'name' => 'uploadForm', 'enctype' => 'multipart/form-data')); ?>
            <p><b><?php echo Kohana::lang('ui_main.upload_file');?></b> <?php echo form::upload(array('name' => 'csvfile'), 'path/to/local/file'); ?></p>
			<button type="submit"><?php echo Kohana::lang('ui_main.upload');?></button>
			<?php print form::close(); ?>
		</div>
	</div>
</div>

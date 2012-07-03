<!-- main body -->
<div id="main" class="clearingfix" style="padding: 15px 0px;">
	<div style="padding: 0px 29px;">
		<h1><?php echo Kohana::lang('simplegroups.about');?> <?php echo $group_name ?>:</h1>
		<?php
			$thumb = $group_logo.".jpg";
				$prefix = url::base().Kohana::config('upload.relative_directory')."/groups";
				print "<img src=\"$prefix/$thumb\" >";
		?>
		<div class="page_text"><?php echo $group_description ?></div>
		
		<div style="border-top: 2px solid black; margin-top:20px; padding-top:20px;">
			<h1><?php echo Kohana::lang('simplegroups.map');?> <?php echo $group_name ?>:</h1>
		</div>
	</div>
	<div id="mainmiddle" class="floatbox withright">
		<div id="right">
		       <!-- status filters -->
			<div class="stat-filters clearingfix">
				<!-- keep track of what status we're looking at -->
				<form action="">
					<input type = "hidden" value="3" name="currentStatus" id="currentStatus">
					<input type = "hidden" value="2" name="colorCurrentStatus" id="colorCurrentStatus">
				</form>

				
			</div>		       
		       <!-- /status filters -->
		

			<?php echo $div_boolean_filter;?>

			<?php echo $div_category_filter;?>
		
			<?php echo $div_layers_filter;?>
			
			<?php echo $div_shares_filter;?>
			
			<br/>
			<?php
			// Action::main_sidebar - Add Items to the Entry Page Sidebar			
			Event::run('ushahidi_action.main_sidebar');
			?>
	
		</div>
		<!-- / right column -->
	
		<!-- content column -->
		<div id="content" class="clearingfix" style="margin:0px;">
			<div class="floatbox">
			
				<!-- filters -->
				<div class="filters clearingfix" style="padding: 0 29px 0 29px;">
				
				
					<?php
					// Action::main_filters - Add items to the main_filters
					Event::run('ushahidi_action.map_main_filters');
					?>
				</div>
				<!-- / filters -->
				
				<div id="adminmap_map_embedd">

					<INPUT style="width: auto; margin:6px; font-size:18px;" TYPE="BUTTON" VALUE="Print this Map" ONCLICK="window.location.href='<?php echo url::base()."printmap/groups/".$group_id; ?>'">  				
					&nbsp;&nbsp;
					<INPUT style="width: auto; margin:6px; font-size:18px;" TYPE="BUTTON" VALUE="List Reports" ONCLICK="window.location.href='<?php echo url::base()."reports?sgid=".$group_id; ?>'">  				
				<br/>
				<?php echo Kohana::lang("adminmap.embedd_html")?>
				<br/>
					<input type="text" value="&lt;iframe src=&quot;<?php echo url::base()."group_iframemap/index/".$group_id; ?>&quot; width=&quot;515px&quot; height=&quot;430px&quot;&gt;&lt;/iframe&gt;"/>					
				</div>
				
				
				<?php								
				// Map and Timeline Blocks
				echo $div_map;
				echo $div_timeline;
				?>
			</div>
		</div>
		<!-- / content column -->

	</div>
</div>
<!-- / main body -->


<!-- main body -->
<div id="main" class="clearingfix" style="padding: 15px 0px;">
	<div style="padding: 0px 29px;">
		<h1><?php echo Kohana::lang('simplegroups.about');?><?php echo $group_name ?>:</h1>
		<?php
			$thumb = $group_logo.".jpg";
				$prefix = url::base().Kohana::config('simplegroups.relative_directory')."/groups";
				print "<img src=\"$prefix/$thumb\" >";
		?>
		<div class="page_text"><?php echo $group_description ?></div>
		
		<div style="border-top: 2px solid black; margin-top:20px; padding-top:20px;">
			<h1><?php echo Kohana::lang('simplegroups.map');?><?php echo $group_name ?>:</h1>
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
		


		       <!-- logic filters -->
			<div class="stat-filters clearingfix">
				<strong><?php echo Kohana::lang('simplegroups.logical');?></strong>
				<!-- keep track of what status we're looking at -->
				<form action="">
					<input type = "hidden" value="or" name="currentLogicalOperator" id="currentLogicalOperator">
				</form>
				<ul id="status_switch" class="status-filters">
					<li>
						<a class="active" id="logicalOperator_1" href="#">							
							<div class="status-title"><?php echo Kohana::lang('simplegroups.or');?> - <span style="text-transform:none; font-size:85%;"><?php echo Kohana::lang('simplegroups.one');?></span> </div>
						</a>
					</li>
					<li>
						<a  id="logicalOperator_2" href="#">
							<div class="status-title"><?php echo Kohana::lang('simplegroups.and');?> - <span style="text-transform:none; font-size:85%;"><?php echo Kohana::lang('simplegroups.all');?></span></div>
						</a>
					</li>
				</ul>
			</div>		       
		       <!-- /logic filters -->



			<!-- category filters -->
				<strong><?php echo strtoupper(Kohana::lang('ui_main.category_filter'));?>: </strong>
		
			<ul id="category_switch" class="category-filters">
				<li><a class="active" id="cat_0" href="#"><div class="swatch" style="background-color:#<?php echo $default_map_all;?>"></div><div class="category-title"><?php echo Kohana::lang('simplegroups.reports');?></div></a></li>
				<?php
					foreach ($categories as $category => $category_info)
					{
						$category_title = $category_info[0];
						$category_color = $category_info[1];
						$category_image = '';
						$color_css = 'class="swatch" style="background-color:#'.$category_color.'"';
						if($category_info[2] != NULL && file_exists(Kohana::config('upload.relative_directory').'/'.$category_info[2])) {
							$category_image = html::image(array(
								'src'=>Kohana::config('upload.relative_directory').'/'.$category_info[2],
								'style'=>'float:left;padding-right:5px;'
								));
							$color_css = '';
						}
						//check if this category has kids
						if(count($category_info[3]) > 0)
						{
							echo '<li>';
							echo '<a style="float:right; text-align:center; width:15px; padding:2px 0px 1px 0px; " href="#" id="drop_cat_'.$category.'">+</a>';
							echo '<a  href="#" id="cat_'. $category .'"><div '.$color_css.'>'.$category_image.'</div><div class="category-title">'.$category_title.'</div></a>';
							
						}
						else
						{
							echo '<li><a href="#" id="cat_'. $category .'"><div '.$color_css.'>'.$category_image.'</div><div class="category-title">'.$category_title.'</div></a>';
						}
						// Get Children
						echo '<div class="hide" id="child_'. $category .'"><ul>';
						foreach ($category_info[3] as $child => $child_info)
						{
							$child_title = $child_info[0];
							$child_color = $child_info[1];
							$child_image = '';
							$color_css = 'class="swatch" style="background-color:#'.$child_color.'"';
							if($child_info[2] != NULL && file_exists(Kohana::config('upload.relative_directory').'/'.$child_info[2])) {
								$child_image = html::image(array(
									'src'=>Kohana::config('upload.relative_directory').'/'.$child_info[2],
									'style'=>'float:left;padding-right:5px;'
									));
								$color_css = '';
							}
							echo '<li style="padding-left:20px;"><a href="#" id="cat_'. $child .'" cat_parent="'.$category.'" ><div '.$color_css.'>'.$child_image.'</div><div class="category-title">'.$child_title.'</div></a></li>';
						}
						echo '</ul></div></li>';
					}
				?>
			</ul>
			<!-- / category filters -->
			
			<?php
			if ($layers)
			{
				?>
				<!-- Layers (KML/KMZ) -->
				<div class="cat-filters clearingfix" style="margin-top:20px;">
					<strong><?php echo Kohana::lang('ui_main.layers_filter');?> <span>[<a href="javascript:toggleLayer('kml_switch_link', 'kml_switch')" id="kml_switch_link"><?php echo Kohana::lang('ui_main.show'); ?></a>]</span></strong>
				</div>
				<ul id="kml_switch" class="category-filters" style="display:hidden;">
					<?php
					foreach ($layers as $layer => $layer_info)
					{
						$layer_name = $layer_info[0];
						$layer_color = $layer_info[1];
						$layer_url = $layer_info[2];
						$layer_file = $layer_info[3];
						$layer_link = (!$layer_url) ?
							url::base().Kohana::config('upload.relative_directory').'/'.$layer_file :
							$layer_url;
						echo '<li><a href="#" id="layer_'. $layer .'"
						onclick="switchLayer(\''.$layer.'\',\''.$layer_link.'\',\''.$layer_color.'\'); return false;"><div class="swatch" style="background-color:#'.$layer_color.'"></div>
						<div>'.$layer_name.'</div></a></li>';
					}
					?>
				</ul>
				<!-- /Layers -->
				<?php
			}
			?>
			
			
			<?php
			if ($shares)
			{
				?>
				<!-- Layers (Other Ushahidi Layers) -->
				<div class="cat-filters clearingfix" style="margin-top:20px;">
					<strong><?php echo Kohana::lang('ui_main.other_ushahidi_instances');?> <span>[<a href="javascript:toggleLayer('sharing_switch_link', 'sharing_switch')" id="sharing_switch_link"><?php echo Kohana::lang('ui_main.hide'); ?></a>]</span></strong>
				</div>
				<ul id="sharing_switch" class="category-filters">
					<?php
					foreach ($shares as $share => $share_info)
					{
						$sharing_name = $share_info[0];
						$sharing_color = $share_info[1];
						echo '<li><a href="#" id="share_'. $share .'"><div class="swatch" style="background-color:#'.$sharing_color.'"></div>
						<div>'.$sharing_name.'</div></a></li>';
					}
					?>
				</ul>
				<!-- /Layers -->
				<?php
			}
			?>
			
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


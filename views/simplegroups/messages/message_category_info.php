
	<?php 
		if(!isset($category_mapping[$message_id]))
		{
			echo "<div class=\"no_cat\">- - - - - - No Categories - - - - - - </div>";
		}
		else
		{
			
			echo "<table class=\"cat_table\">";
			//foreach($category_mapping[$message_id] as $category)
			for($i = 0; $i<count($category_mapping[$message_id]); $i++)
			{
				echo "<tr>";
				for($j = $i ; ($j < count($category_mapping[$message_id])) && ($j <= $i+1); $j++)
				{
					$category = $category_mapping[$message_id][$j];
					
					echo "<td class=\"cat\" width=\"22px\">";				
					if (!empty($category->category_image))
					{
						echo " <img style=\"width:16px;height:16px;\" src=\"".url::base().Kohana::config('upload.relative_directory')."/".$category->category_image."\">";
					}
					echo "</td><td class=\"cat\">";				
					//make a dull version of the color for the background
					$colors = array("FFFFFF", $category->category_color);
					$washed_out_color = groups::changeBrightness(groups::merge_colors($colors), 500);
					
					
					echo '<span class="cat_span" style="border:2px solid #'.$category->category_color.';background:#'.$washed_out_color.';">';
					echo $category->category_title;
					
					echo '</span>';
					echo "</td>";
				}
				
				$i = $j-1;
				//if we ended on an odd number, which is an even number since we're zero based, then finish out the table
				if( ($i % 2) == 0 && $i == count($category_mapping[$message_id])-1)
				{
					echo "<td class=\"cat\" width=\"22px\"></td><td class=\"cat\"></td>";
				}
				echo "</tr>";
			}
			
			
			echo "</table>";
		}
	?>

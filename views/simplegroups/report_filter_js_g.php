<script type="text/javascript">
/**
* Remove the Density Map filters from the reports query
*/
function simpleGroupsRemoveParameterKey()
{
	delete urlParameters['sgid'];
	$("#simple_group_filter_checkbox").removeAttr("checked");
	//turn off the category filters too
	$(".simple_group_category").removeAttr("checked");
	$(".simple_group_category").attr('disabled', true);
	$(".simple_group_category").trigger('change');

}

/**
* Toggle the Density Map filters from the reports query
*/
function simpleGroupsFilterToggle(id)
{
	//only use numbrs
	id = parseInt(id);
	
	if(id > 0)
	{
		urlParameters['sgid'] = id;
		//$(".simple_group_category").attr('disabled', false);
		console.log('here');
	}
	else
	{
		delete urlParameters['sgid'];
		//turn off the category filters too
		//$(".simple_group_category").removeAttr("checked");
		//$(".simple_group_category").attr('disabled', true);
		//$(".simple_group_category").trigger('change');
	}
}

//for remove things from urlParams
function simpleGroupsRemoveParameterItem(key, val) {
	if (! $.isEmptyObject(urlParameters))
	{
		// Get the object type
		objectType = Object.prototype.toString.call(urlParameters[key]);
		
		if (objectType == "[object Array]")
		{
			currentItems  = urlParameters[key];
			newItems = [];
			for (var j=0; j < currentItems.length; j++)
			{
				if (currentItems[j] != val)
				{
					newItems.push(currentItems[j]);
				}
			}
			
			if (newItems.length > 0)
			{
				urlParameters[key] = newItems;
			}
			else
			{
				delete urlParameters[key];
			}
		}
		else if (objectType == "[object String]")
		{
			delete urlParameters[key];
		}
	}
}

/**
 * Set the selected categories as selected
 */
 /*
$(document).ready(function() {

	//for when we have to add HTML so the categories JS can pick it up
	$('#footer').append('<ul class="fl-categories" id="simpleGroupCategories" style="display:block;"></ul>');

	var simple_group_categories = [<?php echo $selected_group_categories; ?>];
	for( i in simple_group_categories)
	{
		$('#simpleGroupCategories').append('<li class="filter_cat_sg" id="filter_cat_sg_' + simple_group_categories[i] + '"><a class="selected" href="#" id="filter_link_cat_sg_' + simple_group_categories[i] + '">SG: ' + simple_group_categories[i] +'</a></li>');
	}
	
	//create onchange function for simple_group_category
	$(".simple_group_category").change(function()
	{
		//to make up for the short comings of the report JS and the fact that they are kinda lazy with updates
		//we sneakily add and remove, extra HTML. It's not my first choice in ways to do this, but it works.
		var id = $(this).val();
		var selected = $(this).attr("checked"); 

		//if selected then add it
		if(selected)
		{
			$('#simpleGroupCategories').append('<li class="filter_cat_sg" id="filter_cat_sg_' + id + '"><a class="selected" href="#" id="filter_link_cat_sg_' + id + '">SG: ' + id +'</a></li>');
			console.log("selected");
		}
		else //remove it
		{
			$('#filter_cat_sg_' + id).remove();
			simpleGroupsRemoveParameterItem("c", "sg_" + id);
			console.log("unselected");
		}
	});

	$("#reset_all_filters").click(function(){simpleGroupsRemoveParameterKey();});
});
*/




</script>
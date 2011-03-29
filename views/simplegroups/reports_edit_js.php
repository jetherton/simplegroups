<?php
/**
 * Edit reports js file.
 *
 * Handles javascript stuff related to edit report function.
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
		// jQuery Textbox Hints Plugin
		// Will move to separate file later or attach to forms plugin
		jQuery.fn.hint = function (blurClass) {
		  if (!blurClass) { 
		    blurClass = 'texthint';
		  }

		  return this.each(function () {
		    // get jQuery version of 'this'
		    var $input = jQuery(this),

		    // capture the rest of the variable to allow for reuse
		      title = $input.attr('title'),
		      $form = jQuery(this.form),
		      $win = jQuery(window);

		    function remove() {
		      if ($input.val() === title && $input.hasClass(blurClass)) {
		        $input.val('').removeClass(blurClass);
		      }
		    }

		    // only apply logic if the element has the attribute
		    if (title) { 
		      // on blur, set value to title attr if text is blank
		      $input.blur(function () {
		        if (this.value === '') {
		          $input.val(title).addClass(blurClass);
		        }
		      }).focus(remove).blur(); // now change all inputs to title

		      // clear the pre-defined text when form is submitted
		      $form.submit(remove);
		      $win.unload(remove); // handles Firefox's autocomplete
			  $(".btn_find").click(remove);
		    }
		  });
		};

		/* Dynamic categories */
		$(document).ready(function() {
			$('#category_add').hide();
		    $('#add_new_category').click(function() { 
		        var category_name = $("input#category_name").val();
		        var category_description = $("input#category_description").val();
		        var category_color = $("input#category_color").val();

		        //trim the form fields
                        //Removed ".toUpperCase()" from name and desc for Ticket #38
		        category_name = category_name.replace(/^\s+|\s+$/g, '');
		        category_description = category_description.replace(/^\s+|\s+$/g,'');
		        category_color = category_color.replace(/^\s+|\s+$/g, '').toUpperCase();
        
		        if (!category_name || !category_description || !category_color) {
		            alert("Please fill in all the fields");
		            return false;
		        }
        
		        //category_color = category_color.toUpperCase();

		        re = new RegExp("[^ABCDEF0123456789]"); //Color values are in hex
		        if (re.test(category_color) || category_color.length != 6) {
		            alert("Please use the Color picker to help you choose a color");
		            return false;
		        }
		
				$.post("<?php echo url::base() . 'admin/reports/save_category/' ?>", { category_title: category_name, category_description: category_description, category_color: category_color },
					function(data){
						if ( data.status == 'saved')
						{
							// alert(category_name+" "+category_description+" "+category_color);
					        $('#user_categories').append("<li><label><input type=\"checkbox\"name=\"incident_category[]\" value=\""+data.id+"\" class=\"check-box\" checked />"+category_name+"</label></li>");
							$('#category_add').hide();
						}
						else
						{
							alert("Your submission had errors!!");
						}
					}, "json");
		        return false; 
		    });
		
			// Category treeview
			$("#category-column-1,#category-column-2").treeview({
			  persist: "location",
			  collapsed: true,
			  unique: false
			});
			
		});
		

		// Date Picker JS
		$(document).ready(function() {
			$("#incident_date").datepicker({ 
			    showOn: "both", 
			    buttonImage: "<?php echo url::base() ?>media/img/icon-calendar.gif", 
			    buttonImageOnly: true 
			});
		});
		
		function addFormField(div, field, hidden_id, field_type) {
			var id = document.getElementById(hidden_id).value;
			$("#" + div).append("<div class=\"row link-row second\" id=\"" + field + "_" + id + "\"><input type=\"" + field_type + "\" name=\"" + field + "[]\" class=\"" + field_type + " long\" /><a href=\"#\" class=\"add\" onClick=\"addFormField('" + div + "','" + field + "','" + hidden_id + "','" + field_type + "'); return false;\">add</a><a href=\"#\" class=\"rem\"  onClick='removeFormField(\"#" + field + "_" + id + "\"); return false;'>remove</a></div>");

			$("#" + field + "_" + id).effect("highlight", {}, 800);

			id = (id - 1) + 2;
			document.getElementById(hidden_id).value = id;
		}

		function removeFormField(id) {
			var answer = confirm("Are You Sure You Want To Delete This Item?");
		    if (answer){
				$(id).remove();
		    }
			else{
				return false;
		    }
		}
		
		function deletePhoto (id, div)
		{
			var answer = confirm("Are You Sure You Want To Delete This Photo?");
		    if (answer){
				$("#" + div).effect("highlight", {}, 800);
				$.get("<?php echo url::base() . 'admin/simplegroups/reports/deletePhoto/' ?>" + id);
				$("#" + div).remove();
		    }
			else{
				return false;
		    }
		}
		
		/**
		 * Google GeoCoder
		 */
		function geoCode()
		{
			$('#find_loading').html('<img src="<?php echo url::base() . "media/img/loading_g.gif"; ?>">');
			address = $("#location_find").val();
			$.get("<?php echo url::site() . 'findlocation/geocode/' ?>", { address: address },
				function(data){
				
					$('#find_location_results').html(data);
					$('#find_loading').html('');
					
				}); 
			return false;
		}
		
		/***************************************
		*Put things on the map based on a geolocation
		****************************************/
		function placeLocation(lat, lon, name)
		{
		
			var lonlat = new OpenLayers.LonLat(lon, lat);
			
			lonlat.transform(proj_4326,proj_900913);
					
			m = new OpenLayers.Marker(lonlat);
			markers.clearMarkers();
			markers.addMarker(m);
			map.setCenter(lonlat, <?php echo $default_zoom; ?>);

			// Update form values
			$("#latitude").attr("value", lat);
			$("#longitude").attr("value", lon);
			$("#location_name").attr("value", name);

			return false;
		}
		
		var map;
		var thisLayer;
		var proj_4326 = new OpenLayers.Projection('EPSG:4326');
		var proj_900913 = new OpenLayers.Projection('EPSG:900913');
		var markers;
		$(document).ready(function() {
			// Now initialise the map
			var options = {
			units: "m"
			, numZoomLevels: 20
			, controls:[],
			projection: proj_900913,
			'displayProjection': proj_4326
			};
			map = new OpenLayers.Map('divMap', options);
			
			<?php echo map::layers_js(FALSE); ?>
			map.addLayers(<?php echo map::layers_array(FALSE); ?>);
			
			map.addControl(new OpenLayers.Control.Navigation());
			map.addControl(new OpenLayers.Control.PanZoomBar());
			map.addControl(new OpenLayers.Control.MousePosition());
			map.addControl(new OpenLayers.Control.LayerSwitcher());
			
			// Create the markers layer
			markers = new OpenLayers.Layer.Markers("Markers");
			map.addLayer(markers);
			
			// create a lat/lon object
			var myPoint = new OpenLayers.LonLat(<?php echo $longitude; ?>, <?php echo $latitude; ?>);
			myPoint.transform(proj_4326, map.getProjectionObject());
			
			// create a marker positioned at a lon/lat
			var marker = new OpenLayers.Marker(myPoint);
			markers.addMarker(marker);
			
			// display the map centered on a latitude and longitude (Google zoom levels)
			map.setCenter(myPoint, <?php echo $default_zoom; ?>);
			
			// Detect Map Clicks
			map.events.register("click", map, function(e){
				var lonlat = map.getLonLatFromViewPortPx(e.xy);
				var lonlat2 = map.getLonLatFromViewPortPx(e.xy);
			    m = new OpenLayers.Marker(lonlat);
				markers.clearMarkers();
		    	markers.addMarker(m);
				
				lonlat2.transform(proj_900913,proj_4326);	
				// Update form values (jQuery)
				$("#latitude").attr("value", lonlat2.lat);
				$("#longitude").attr("value", lonlat2.lon);
			});
			
			// GeoCode
			$('.btn_find').live('click', function () {
				geoCode();
			});
			$('#location_find').bind('keypress', function(e) {
				var code = (e.keyCode ? e.keyCode : e.which);
				if(code == 13) { //Enter keycode
					geoCode();
					return false;
				}
			});
			
			// Event on Latitude/Longitude Typing Change
			$('#latitude, #longitude').bind("change keyup", function() {
				var newlat = $("#latitude").val();
				var newlon = $("#longitude").val();
				if (!isNaN(newlat) && !isNaN(newlon))
				{
					var lonlat = new OpenLayers.LonLat(newlon, newlat);
					lonlat.transform(proj_4326,proj_900913);
					m = new OpenLayers.Marker(lonlat);
					markers.clearMarkers();
			    	markers.addMarker(m);
					map.setCenter(lonlat, <?php echo $default_zoom; ?>);
				}
				else
				{
					alert('Invalid value!')
				}
			});
			
			
			/* Form Actions */
			// Action on Save Only
			$('.btn_save').live('click', function () {
				$("#save").attr("value", "1");
				$(this).parents("form").submit();
				return false;
			});
			
			$('.btn_save_close').live('click', function () {
				$(this).parents("form").submit();
				return false;
			});
			
			// Delete Action
			$('.btn_delete').live('click', function () {
				var agree=confirm("Are You Sure You Want To DELETE item?");
				if (agree){
					$('#reportMain').submit();
				}
				return false;
			});
			
			// Toggle Date Editor
			$('a#date_toggle').click(function() {
		    	$('#datetime_edit').show(400);
				$('#datetime_default').hide();
		    	return false;
			});
			
			// Show Messages Box
		    $('a#messages_toggle').click(function() {
		    	$('#show_messages').toggle(400);
		    	return false;
			});
			
			// Textbox Hints
			$("#location_find").hint();
		});
		
		function formSwitch(form_id, incident_id)
		{
			var answer = confirm('Are You Sure You Want To SWITCH Forms?');
			if (answer){
				$('#form_loader').html('<img src="<?php echo url::base() . "media/img/loading_g.gif"; ?>">');
				$.post("<?php echo url::base() . 'admin/simplegroups/reports/switch_form' ?>", { form_id: form_id, incident_id: incident_id },
					function(data){
						if (data.status == 'success'){
							$('#custom_forms').html('');
							$('#custom_forms').html(unescape(data.response));
							$('#form_loader').html('');
						}
				  	}, "json");
			}
		}
		
		// Initialize tinyMCE Wysiwyg Editor
		tinyMCE.init({
		convert_urls : false,
		relative_urls : false,
		mode : "exact",
		elements : "incident_description",
		theme : "advanced",
		plugins : "pagebreak,advhr,advimage,advlink,iespell,inlinepopups,contextmenu,paste,directionality,noneditable,advlist",
		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "outdent,indent,blockquote,|,undo,redo,|,link,unlink,image,code,|,forecolor,backcolor",
		theme_advanced_buttons3 : "cut,copy,paste,pastetext,pasteword,|,hr,removeformat,visualaid,|,sub,sup,|,advhr,|,ltr,rtl",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left"
		});
		

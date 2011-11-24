/**
 * Main reports js file.
 * 
 * Handles javascript stuff related to reports function.
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

<?php require SYSPATH.'../application/views/admin/form_utils_js.php' ?>


 
		var currentPage = "1";
		var currentStatus = "O";

		// Ajax Submission
		function reportAction ( action, confirmAction, incident_id, ui_id )
		{
			var statusMessage;
			if( !isChecked( "incident" ) && incident_id=='' )
			{ 
				alert('Please select at least one report.');
			} 
			else 
			{
				var answer = confirm('Are You Sure You Want To ' + confirmAction + ' items?')
				if (answer){
					
					// Set Submit Type
					$("#action").attr("value", action);
					
					if (incident_id != '') 
					{
						// Submit Form For Single Item
						$("#incident_single").attr("value", incident_id);
						
					}
					else
					{
						//we're acting on lots of stuff at once. 
						
						// Set Hidden form item to 000 so that it doesn't return server side error for blank value
						$("#incident_single").attr("value", "000");
					}
					
					//get the category id that we want to use
					var cat_id = encodeURIComponent($("#cat_filter").val());
						
					//turn on the wating image
					$('<img id="reportAction_wait" src="<?php echo url::base(); ?>media/img/loading_g.gif"/>').insertAfter($("#"+ui_id));
					$.post("<?php echo url::base() ?>admin/simplegroups/reports/get_table?c="+cat_id+"&page="+currentPage+"&status="+currentStatus,
						$("#reportMain").serialize(), 
						function(data){
							var parent = $('#table_holder').parent();
							$('#table_holder').remove();
							parent.append('<div class="table-holder" id="table_holder">'+data+'</div>');

							$("#reportAction_wait").remove();


					});
				
				
				} 
				
			}
			return false;
		}//end method
		
		function showLog(id)
		{
			$('#' + id).toggle(400);
		}
		
		
		//called when the user changes what category to filter by
		function changeCategoryFilter()
		{
			//start at the first page
			currentPage = "1";
			
			//turn on the wating image
			$('#filter_wait').html('<img src="<?php echo url::base(); ?>media/img/loading_g.gif"/>');			
			updateTable('#filter_wait');
			return false;
		}
		
		/*
		* This sets the page we're looking at
		*/
		function pagination(page)
		{
			currentPage = page;
			$('<img id="page_wait" src="<?php echo url::base(); ?>media/img/loading_g.gif"/>').insertAfter($("#pagination_"+page));
			updateTable('#pagination_'+page);
		}
		
		/*
		* Sets the status of messages that we're looking at
		*/
		function changeStatus(status)
		{
			currentStatus = status;

			//remove the selected class from all the tabs		
			var kids = $(".tabset").find('a');
			kids.removeClass("active");		
			$("#status_filter_"+status).addClass("active");
			
			//turn on the wating image
			$('<img id="tab_wait" src="<?php echo url::base(); ?>media/img/loading_g.gif"/>').insertAfter($("#status_filter_a"+status));
			
			updateTable('#tab_wait');
		}
		
		
		//Updates the table
		function updateTable(waitingID)
		{
			//get the category id that we want to use
			var cat_id = encodeURIComponent($("#cat_filter").val());
			
			//get the HTML for the next set of kid admin areas
			$.get("<?php echo url::base() ?>admin/simplegroups/reports/get_table?c="+cat_id+"&page="+currentPage+"&u="+currentStatus,
				function(data){								
					var parent = $('#table_holder').parent();
					$('#table_holder').remove();
					parent.append('<div class="table-holder" id="table_holder">'+data+'</div>');
					
					$(waitingID).html('');
				}
			);
		
			return false;
		}
		


/**
 * Messages js file.
 *
 * Handles javascript stuff related to messages function.
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
		
		function limitChars(textid, limit, infodiv)
		{
			var text = $('#'+textid).val();	
			var textlength = text.length;
			if(textlength > limit)
			{
				$('#' + infodiv).html('You cannot write more then '+limit+' characters!');
				$('#'+textid).val(text.substr(0,limit));
				return false;
			}
			else
			{
				$('#' + infodiv).html('You have '+ (limit - textlength) +' characters left.');
				return true;
			}
		}
		
		function showReply(id)
		{
			if (id) {
				$('#' + id).toggle(400);
			}
		}
		
		function sendMessage(id, loader)
		{
			$('#' + loader).html('<img src="<?php echo url::base() . "media/img/loading_g.gif"; ?>">');
			$.post("<?php echo url::site() . 'admin/simplegroups/messages/send/' ?>", { to_id: id, message: $("#message_" + id).attr("value") },
				function(data){
					if (data.status == 'sent'){
						$('#reply_' + id).hide();
					} else {
						$('#replyerror_' + id).show();
						$('#replyerror_' + id).html(data.message);
						alert('ERROR!');
					}
					$('#' + loader).html('');
			  	}, "json");
		}
		
		function cannedReply(id, field)
		{
			var autoreply;
			$("#" + field).attr("value", "");
			if (id == 1) {
				autoreply = "Thank you for sending a message to Ushahidi. What is the closest town or city for your last message?";
			}else if (id == 2) {
				autoreply = "Thank you for sending a message to Ushahidi. Can you send more information on the incident?"
			};
			$("#" + field).attr("value", autoreply);		
		}

		function messagesAction ( action, confirmAction, message_id )
		{
			var statusMessage;
			if( !isChecked( "message" ) && message_id=='' )
			{ 
				alert('Please select at least one message.');
			} else {
				var answer = confirm('Are You Sure You Want To ' + confirmAction + ' items?')
				if (answer){

					// Set Submit Type
					$("#action").attr("value", action);

					if (message_id != '') 
					{
						// Submit Form For Single Item
						$("#message_single").attr("value", message_id);
						$("#messageMain").submit();
					}
					else
					{
						// Set Hidden form item to 000 so that it doesn't return server side error for blank value
						$("#message_single").attr("value", "000");

						// Submit Form For Multiple Items
						$("#messageMain").submit();
					}

				} else {
				//	return false;
				}
			}
		}
		
		// Preview Message
		function preview ( id ){
			if (id) {
				$('#' + id).toggle(400);
			}
		}
		
	
	</script>
	
	<style type="text/css">
		.lightbox_bg {
			background:#777 none repeat scroll 0 0;
			display:none;
			height:100%;
			left:0;
			filter:alpha(opacity=50);
			opacity: 0.5;
			top:0;
			width:100%;
			z-index:50;
			display:none;
			position:fixed;
		}
		.modal{
			border: solid 2px black;
			padding: 10px;
			position:absolute;			
			z-index:51;
			background: white;
			width: 400px;

		}
		
		.cat_span{
			font-size:10pt;
			color:black;
			padding-left:3px;
			padding-right:3px;
		}
		
		td.cat{
			border:none;
			padding:5px 3px 3px 3px; 			
			vertical-align:middle;
		}
		
		.no_cat{
			text-align:center;
		}
		
	</style>
    
    <script type="text/javascript">
		
		
	function editCategory ( message_id, this_this )
	{
		//add modal background
		$('<div id="mask" />').addClass('lightbox_bg').appendTo('body').show();
		//show the modal dialog box
		$('<div id="modalbox" />').text('').addClass('modal').appendTo('body');
		
		//find out where on the page the edit categories link was clicked
		var message_pos = findPosition(this_this);
		
		//Get the window height and width
		var winH = $(window).height();
		var winW = $(window).width();
               
		//Set the popup window to center
		$('#modalbox').css('top',  message_pos[1]-50);
		$('#modalbox').css('left', winW/2-$('#modalbox').width()/2);
		
		
		$('#modalbox').html('<div style="float:right;"><a href="#" onclick="closeEditCategory(); return false;"> Close</a></div><div id="category_content" style="text-align:center;"> <img src="<?php echo url::base(); ?>media/img/loading_g2.gif"/></div>');
		
		//get the HTML for the next set of kid admin areas
			$.get("<?php echo url::base() ?>admin/simplegroups/messages/category_info/"+message_id,
			function(data){
				$('#category_content').html(data);	

				// Category treeview
				$("#category-column-1,#category-column-2").treeview({
					persist: "location",
					collapsed: true,
					unique: false
				});
				
			});
			
		return false;
	}
	
	
	//use this to save the category info
	function saveCategories(message_id)
	{
			
		$.post("<?php echo url::base() ?>admin/simplegroups/messages/save_category_info/"+message_id, 
			$("#message_categories").serialize(), 
			function(data){
				$('#category_content').html("<h2>Categories Saved</h2>");	
				
				$('#message_cat_info_'+message_id).html(data);	
				
				timeOutStr = "closeEditCategory();";
				setTimeout(timeOutStr, 2000);
			});
		/*.error(function(){ //save this for jquery 1.5
			$('#category_content').html("<h2> Error saving changes. Please try again</h2>");	
			});
		*/
		$('#category_content').html('<img src="<?php echo url::base(); ?>media/img/loading_g2.gif"/>');
		return false;
	}
	
	
	function closeEditCategory()
	{
		$('#modalbox').remove();
		$('#mask').remove();
		return false;
	}
	
	
	function findPosition( oElement ) 
	{
		if( typeof( oElement.offsetParent ) != 'undefined' ) 
		{
			for( var posX = 0, posY = 0; oElement; oElement = oElement.offsetParent ) 
			{
				posX += oElement.offsetLeft;
				posY += oElement.offsetTop;
			}
			return [ posX, posY ];
		} 
		else 
		{
			return [ oElement.x, oElement.y ];
		}
	}
	
	/*Figures out what filter the user wants
	 * calls the server to get the results
	 * and then displays it
	 */
	function filterAction()
	{
		//get the id of the category that we're to filter by
		var cat_id = $("#cat_filter").val();
		var service_id = $("#service_id").val();
		
		//figure out which tab is currently selected
		var tab_id = "";
		var kids = $(".tabset").find('a');
		kids.each(function(){
			if($(this).hasClass("active"))
			{
				tab_id = $(this).attr("id");
			}
		});
		
		
		
		//turn on the wating image
		$('#filter_wait').html('<img src="<?php echo url::base(); ?>media/img/loading_g.gif"/>');
		
		//get the HTML for the next set of kid admin areas
			$.get("<?php echo url::base() ?>admin/simplegroups/messages/get_table/"+service_id+"/"+cat_id+"/"+tab_id,
			function(data){
				$('#table_holder').html(data);	
				$('#filter_wait').html('');
			});
		
		return false;
	}
	
	function filterTabClick(tabId)
	{
		//remove the selected class from all the tabs		
		var kids = $(".tabset").find('a');
		kids.removeClass("active");		
		$("#"+tabId).addClass("active");
		
		var cat_id = $("#cat_filter").val();
		var service_id = $("#service_id").val();
		
		//turn on the wating image
		$('<img id="tab_wait" src="<?php echo url::base(); ?>media/img/loading_g.gif"/>').insertAfter($("#"+tabId));
		
		//get the HTML for the next set of kid admin areas
			$.get("<?php echo url::base() ?>admin/simplegroups/messages/get_table/"+service_id+"/"+cat_id+"/"+tabId,
			function(data){
				$('#table_holder').html(data);	
				$('#tab_wait').remove();
			});
		
		return false;
		
	}
		

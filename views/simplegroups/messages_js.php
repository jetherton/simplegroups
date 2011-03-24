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
				
					//if the action is "d" for delete then handle that in some seperate code
					if(action == "d")
					{
						deleteMessages(message_id);
						return;
					}

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
		
		//delete messages button handler
		function deleteMessages(message_id)
		{
			
			//get the active category
			var cat_id = $("#cat_filter").val();
			//get the serive id
			var service_id = $("#service_id").val();
			//figure out which tab is currently selected
			var tab_id = getSelectedTab();
			//figure out which page we're on
			var page = $("#pagination_active_page").attr("value"); //cause it's not really a "value"
			if (message_id == '')  //if we're deleting lots of stuff at once.
			{
				//turn on the wating image
				$('<img id="delete_all_wait" src="<?php echo url::base(); ?>media/img/loading_g.gif"/>').insertAfter($("#delete_all_button"));
				$.post("<?php echo url::base() ?>admin/simplegroups/messages/delete_message/"+service_id+"/"+cat_id+"/"+tab_id+"?page="+page,
					$("#messageMain").serialize(), 
					function(data){
						var parent = $('#table_holder').parent();
						$('#table_holder').remove();
						parent.append('<div class="table-holder" id="table_holder">'+data+'</div>');
						$("#delete_all_wait").remove();

				});
			}
			else
			{
				//turn on the wating image
				$('<img id="delete_wait" src="<?php echo url::base(); ?>media/img/loading_g.gif"/>').insertAfter($("#delete_message_"+message_id));
				$.post("<?php echo url::base() ?>admin/simplegroups/messages/delete_message/"+service_id+"/"+cat_id+"/"+tab_id+"?page="+page,
					 { 'message_id[]': [message_id] },
					function(data){
						var parent = $('#table_holder').parent();
						$('#table_holder').remove();
						parent.append('<div class="table-holder" id="table_holder">'+data+'</div>');

				});
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
		
		.table .col-2 {
			width: 315px;
			padding-left:5px;
		}
		.table .col-3 {
			width: 365px;
		}

		.table .post {
			width: 335px;
		}

		.table .col-4 {
			width: 140px;			
		}
		
		.table th.col-4 {
		
			text-align:left;
		}

		.cat_edit{
			float:right;
			font-size:75%;
			margin-right:15px;
		}
		
		.comments_button{
			margin: 3px 0px;
			padding:0 9px; 
			font-size: 75%;
			background:#f2f7fa; 
			text-decoration:none; 
			border:1px solid #d1d1d1;
			width: 90%;
			float:left;
		}
		
		.comments_button img{			
			border:none;			
		}
		
		.comments_button:hover{
			
			background:#c2c7fa; 
			text-decoration:underline; 
			
		}
		
		.delete_button{
			width: 90%;
			float:left;
			margin: 3px 0px;
			padding:0 9px; 
			font-size: 75%;
			background:#fad7da; 
			text-decoration:none; 
			border:1px solid #c11;
			color: #c00;
		}
		
		.delete_button:hover{
			
			background:#fac7ca; 
			text-decoration:underline; 
			
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
		var tab_id = getSelectedTab();
		
		
		
		//turn on the wating image
		$('#filter_wait').html('<img src="<?php echo url::base(); ?>media/img/loading_g.gif"/>');
		
		//get the HTML for the next set of kid admin areas
			$.get("<?php echo url::base() ?>admin/simplegroups/messages/get_table/"+service_id+"/"+cat_id+"/"+tab_id,
			function(data){
				/* I would have just done this:
				
				$('#table_holder').html(data);
				
				But IE freakes out for some weird reason because of the <form> (if I commented out the <form> everything worked fine).
				IE would always append the HTML instead of replacing it. So I came up with the below hack that seems to work
				in FireFox and Chrome. So I'm just leaving it as is. I know it's not pretty, but just another reason... IE Sucks.
				*/
				
				var parent = $('#table_holder').parent();
				$('#table_holder').remove();
				parent.append('<div class="table-holder" id="table_holder">'+data+'</div>');
				
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
				
				var parent = $('#table_holder').parent();
				$('#table_holder').remove();
				parent.append('<div class="table-holder" id="table_holder">'+data+'</div>');
				
				$('#tab_wait').remove();
			});
		
		return false;
		
	}
	
	/*
	* This sets the page we're looking at
	*/
	function pagination(page)
	{
		//get the active category
		var cat_id = $("#cat_filter").val();
		//get the serive id
		var service_id = $("#service_id").val();
		//figure out which tab is currently selected
		var tab_id = getSelectedTab();
		
		//turn on the wating image
		$('<img id="page_wait" src="<?php echo url::base(); ?>media/img/loading_g.gif"/>').insertAfter($("#pagination_"+page));
		
		//get the HTML for the next set of kid admin areas
			$.get("<?php echo url::base() ?>admin/simplegroups/messages/get_table/"+service_id+"/"+cat_id+"/"+tab_id+"?page="+page,
			function(data){
				
				var parent = $('#table_holder').parent();
				$('#table_holder').remove();
				parent.append('<div class="table-holder" id="table_holder">'+data+'</div>');
				
			});
		
		return false;

	}
	
	
	function getSelectedTab()
	{
		//figure out which tab is currently selected
		var tab_id = "";
		var kids = $(".tabset").find('a');
		kids.each(function(){
			if($(this).hasClass("active"))
			{
				tab_id = $(this).attr("id");
			}
		});
		
		return tab_id;
	}
	
	/*********************************
	* Used to update the comments
	* of a message
	**********************************/
	function editComments(id)
	{
		//turn on the wating image		
		$("#commentsButton_"+id).html('Update Comments <img src="<?php echo url::base() . "media/img/loading_g.gif"; ?>">');
		
			$.post("<?php echo url::site() . 'admin/simplegroups/messages/update_comments/' ?>"+id, { to_id: id, comments: $("#comments_"+id).val() },
				function(data){
					//remove image
					$("#commentsButton_"+id).html('Update Comments');
			  	});
	}
	
	
		

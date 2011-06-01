<?php
	$lang = array(
		'explain_applies_to_reports'=>"The <em>Applies to Reports</em> setting is used to determine whether the given ".
											"category can be assigned to a report or not. <br/><br/>If  <em>Applies to Reports</em> ".
											"is set to &quot;No&quot; for a category, then that category will not be shown as a category ".
											"that can be applied to a report. <br/><br/>If <em>Applies to Reports</em> is set to &quot;Yes&quot;, ".
											"then that category will be shown as one that can apply to any given report.",
		
		'explain_applies_to_messages'=>"The <em>Applies to Messages</em> setting is used to determine whether the given ".
											"category can be assigned to a message or not. <br/><br/>If  <em>Applies to Messagess</em> ".
											"is set to &quot;No&quot; for a category, then that category will not be shown as a category ".
											"that can be applied to a message. <br/><br/>If <em>Applies to Messages</em> is set to &quot;Yes&quot;, ".
											"then that category will be shown as one that can apply to any given message.",
											
		'explain_selected_by_default'=>"The <em>Selected by Default</em> setting is used to determine whether the given ".
											"category is automatically applied to a newly created report or message. <br/><br/>If ".
											"<em>Select by Default</em> is set to &quot;Yes&quot;, than the given category will be automatically ".
											"assigned to any new report or message (determined by the Applies to Reports and Applies to ".
											"Messages settings). This could be usefull when all new messages should be categorized as ".
											"being 'unread'. <br/><br/>If <em>Select by Default</em> is set to &quot;No&quot;, then a new report or message ".
											"will only be categorized with the given category when done so explicitly by a user.",
											
		'explain_visible'=>"The <em>Visible</em> setting is used to determine whether the given category can be seen by regular users on the publicly ".
											"available part of the website".
											"<br/><br/>If<em>Visible</em> is set to &quot;Yes&quot;, then people coming to the public part of the ".
											"website will be able to see this category. <br/><br/>If <em>Visible</em> is set to &quot;No&quot; then ".
											"people coming to the public part of the website will not be able to see this category.<br/><br/>".
											"No matter what setting <em>Visible</em> is set to, all authorized users of the site will be able to see it.",
		
		"not_visible" => "<span style=\"font-size:10px;font-weight:normal;color:#a1a1a1;\">Not Visible</span>",
		"group" => "Group",
		"include_group_categories"=>"Include Group Categories",
		
		'name'=>array('default'=>'Group Name cannot be blank'),
		'description'=>array('default'=>'Group Description cannot be blank'),
		'logo'=>array('default'=>'The logo file must be a .gif, .jpg, .png and less than 8mb'),
		'own_instance'=>array('default'=>'The Own Instance field must be at least 3 characters long'),	
		'group_site'=>array('default'=>'The Group Site must be a valid URL, or it must be blank.'),
		'contact_person'=>array('default'=>'The contact person\'s name cannot be longer than 100 characters.'),							
		'contact_phone'=>array('default'=>'The contact phone cannot be longer than 100 characters, or left blank.'),
		'contact_email'=>array('default'=>'The contact email cannot be longer than 100 characters and must be a valid email address, or left blank.'),
		
	);
?>

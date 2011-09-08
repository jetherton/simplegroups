<?php
/**
 *  End Time English Language file
 *
 * This plugin was written for Ushahidi Liberia, by the contractors of Ushahidi Liberia
 * 2011
 *
 * @package  End Time plugin
 * @author     Carter Draper <carjimdra@gmail.com>
 * 
 */

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
		'group_name'=>'Group Name',
		'group_members'=>'# Group Members',
		'group_reports'=>'# Group Reports',
		'about' => 'About Group',
		'map' => 'Map of all the reports created by group',
		'logical' => 'Logical Operators:',
		'and' => 'AND',
		'or' => 'OR',
		'one' => 'Show all reports that fall under at least one of the categories selected below',
		'all' => 'Show all reports that fall under all of the categories selected below',
		'reports' =>'Show All Reports',
		'category' =>'Category',
		'translations' =>'Category Translations',
		'color' => 'Color:',
		'visible' => 'Visible To Public:',
		'applies' =>'Applies to Reports:',
		'messages' => 'Applies to Messages:',
		'assign' => 'This category is assigned by default to all new messages/reports:',
		'properties' =>'Properties',
		'icon' => 'Icon',
		'created' => 'This report created by:',
		'credit_group' =>'Group',
		'belong' =>'Group User should belong to:',
		'user' =>'For user to be assigned to a group, the user must have the "SIMPLEGROUPS" role.',
		'roles' =>'Group Roles:',
		'group_user' =>'If the user is a group user, these are the group roles the user could have.',
		'grouplist_groups' =>'Groups:',
		'forward_to' => 'Forward To:',
		'forward' => 'Forward',
		'forwarded' => 'Forwarded',
		'turn' => 'Turned Into Reports',
		'recent' => 'Recent',
		'filter' => 'Filter by category:',
		'hide' => 'hide this message',
		'no' => 'No Group',
		'error' => 'Error: No Group Assigned',
		'sorry' =>'We are sorry, but it seems that your user account has not been assigned to a group. <br/><br/> Please contact this web site
				administrator to correct this. 
				<br/><br/>
				If you have another user account you can',
		'you' => 'and t <br/> Thank you.',
		'reporter' => 'Reporter',
		'from' => 'This report was created by a message sent in from:',
		'tags' => 'Remove HTML tags',
		'smaller' => 'Smaller map',
		'wider' => 'Wider map',
		'taller' => 'Taller map',
		'shorter' =>'Shorter map',
		'approved' => 'INCIDENT TITLE,INCIDENT DATE,LOCATION,DESCRIPTION,CATEGORY, GROUP CATEGORY, LATITUDE, LONGITUDE, APPROVED,VERIFIED',
		'riot' => '"Riot","2009-05-15 01:06:00","Gbanga","Three cases have been confirmed in C. del Uruguay","DEATHS, CIVILIANS, ","POLICE NOTIFIED, SENSATIVE, ", 7.0133, -10.513, YES,YES <br/>
				"Looting","2009-03-18 10:10:00","Accra","Looting happening everywhere","RIOTS, DEATHS, PROPERTY LOSS, ", "POLICE NOT NOTIFIED, SENSATIVE, ",6.34309, -11.7422, YES,NO',
		'group_description' => 'Group Description',
		
		'url' => 'Group Own Ushahidi FrontlineSMS URL',
		'http' => 'Optional. Must be in the format http://myhost/frontlinesms/?key=*MY_KEY*&s=${sender_number}&m=${message_content}',
		'logo' => 'Logo',
		'users' => 'Group Users',
		'use' =>'Users',
		'user_role' => 'Users role',
		'white' => 'White Listed Phone Numbers',
		'enter' => 'Enter phone numbers that are allowed to send in SMSs to this group.',
		'exact' => 'Numbers must be in the exact same format as when they are recieved.',
		'number' => 'Number',
		'name' => 'Name',
		'organ' => 'Organization',
		'#_member' => '# Group Members',
		'#_report' => '# Group Reports',
		'groups' => 'Groups:', 
		'person' => 'Group Contact Person',
		'opt' => 'Optional',
		'contact_phone' => 'Group Contact Phone Number',
		'contact_email' => 'Group Contact Email Address',
		'physical_address' => 'Group Physical Address',
		'website' => 'Group Website',
		'del' => 'DELETE',
		'fro' =>'From:',
		'to' => 'To:',
		'categories' => 'Categories For Message:',
		'categories_report' => 'Group categories for report:',
		'save' => 'Save Categories',
		'date' => 'Date:',
		'comets' => 'Message Comments:',
		'up' => 'Update Comments:',
		'edit' => 'Edit Categories',
		'cat' => 'Categories',
		'via' => 'via',
		'unprove' => 'Unapproved:',
		'approve' => 'Approve',
		'dash' => 'Dashboard', 
		'rep' => 'Reports',
		'mess' => 'Messages',
		'pam' => 'Map',
		'group_set' => 'Group Settings',
		
		
		
		
	);
?>

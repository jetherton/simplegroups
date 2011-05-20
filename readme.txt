=== About ===
name: Simple Groups
website: http://www.ushahidi.com
description: Creates a seperate little backend for members of different groups.
version: 2.0
requires: Ushahidi 2.0b10,  Admin Map plugin 
tested up to: 2.0
author: John Etherton
author website: http://johnetherton.com

== Description ==
Built for the Liberia_2.0 flavor of Ushahidi. You can find this here: https://github.com/jetherton/Ushahidi_Web/tree/liberia_2.0. It also requires the Liberia theme found here: http://johnetherton.com/file-share/Ushahidi/themes/Liberia-Theme/. Finally, this plugin also uses the map that's made by the Admin Map plugin found here: http://apps.ushahidi.com/p/adminmap/source/download/master/

This plugin is built to allow seperate groups of users have access to their own report data, but not to the data of another group. This functionality was built for the Liberian 2011 general elections as multiple organizations, with their own metrics for validation, would be monitoring the electoral process, but using one ushahidi instance. 

This plugin is designed to give just the bare minimium of functionalty to users so that they can add/edit their own reports and manage incoming SMSs that are from whitelisted numbers that belong to their group.

!!!!This plugins requires PHP 5.3!!!!!!!!!!!

This plugin requires:
	 Admin Map Plugin: http://apps.ushahidi.com/p/adminmap/
	 Ushahidi API Library Plugin: http://apps.ushahidi.com/p/ushahidiapilibrary/


== Installation ==
1. Get the Libiria_2.0 flavor of Ushahidi from: https://github.com/jetherton/Ushahidi_Web/tree/liberia_2.0. This flavor is used because it allows themes to overwrite views on the admin side of the website.
2. Use the Liberia theme, or theme derived from this one, found here: http://johnetherton.com/file-share/Ushahidi/themes/Liberia-Theme/. This theme has the hooks that this plugin requires.
3. Install the Admin Map plugin found here: http://apps.ushahidi.com/p/adminmap/source/download/master/. This plugin allows the group users to see a map of their reports on the backend.
4. Install the Ushahidi API Library plugin found here: http://apps.ushahidi.com/p/ushahidiapilibrary/
5. Copy the entire /simplegroups/ directory into your /plugins/ directory.
6. Activate the plugin.
7. Then use the plugin settings page to create groups and assign members to them. Note, for a user to be a member they must be given the role of "groupuser"

== Changelog ==




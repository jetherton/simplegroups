Simple Groups - Ushahidi Plugin
====================


About
----------
* name: Simple Groups
* website: https://github.com/jetherton/simplegroups
* description: Creates a seperate little backend for members of different groups.
* version: 2.5
* requires: Ushahidi 2.1(latest from the repo, not download.ushahidi.com),  Admin Map plugin, Ushahidi API Library plugin
* tested up to: 2.1
* author: John Etherton
* author website: http://johnetherton.com

Description
------------------
This plugin is built to allow seperate groups of users have access to their own report data, but not to the data of another group. This functionality was built for the Liberian 2011 general elections as multiple organizations, with their own metrics for validation, would be monitoring the electoral process, but using one ushahidi instance. 

This plugin is designed to give just the bare minimium of functionalty to users so that they can add/edit their own reports and manage incoming SMSs that are from whitelisted numbers that belong to their group.


This plugin requires:

	 Admin Map Plugin: https://github.com/jetherton/adminmap
	 
	 Ushahidi API Library Plugin: https://github.com/jetherton/ushahidiapilibrary


Installation
----------------
1. Install the Admin Map plugin found here: https://github.com/jetherton/adminmap. This plugin allows the group users to see a map of their reports on the backend.
2. Install the Ushahidi API Library plugin found here: https://github.com/jetherton/ushahidiapilibrary - You only need to do this if you want to be able to forward reports to another ushahidi instance
3. Copy the entire /simplegroups/ directory into your /plugins/ directory.
4. Activate the plugin.
5. Then use the plugin settings page to create groups and assign members to them. Note, for a user to be a member they must be given the role of "groupuser"

Changelog
----------------
2.5 -- 2011-11-30 -- Now takes advantage of the reports::fetch_incidents() helper method



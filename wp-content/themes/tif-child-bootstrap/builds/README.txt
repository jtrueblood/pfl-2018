---- Updated July 2018 ----

The website is in the process of being migrated away from the 'cached' text file models and displays most of its content from the database.  

There are two databases locally...

local -- $wpdb
	The new one.  All tables start with the pre 'wp_' except for the player tables which use their player id as the name.
	I have started to move all tables to this database.  All player data should be here.  All of the player data now has home/away, win/loss and stadium name directly in the database.  Some older data is still connected to...

pflmicro -- $mydb
	Old DB.  The goal should be to migrate everything out of here and into the new 'local' DB.  You can not write to this DB using ->insert().  
	
	
When the 2018 Season is over...	
	First start with the simple stuff that is disconnected.
		-- 2018 Draft = use 'build-draft.php'. Update the json url to the MFL API.  This file generates insert statement that can be copied and pasted into mysql.
	
	Then update 'Protections, Playoffs, ProBowl, Overtime, Champions, Awards'  tables by hand.  It makes sense to spot check these as you go and the labor of updating them is not as intensive.  Eventually they should be 'wp_protections', 'wp_playoffs', etc and moved to the 'local' database.
	
	Create the '2018stand' table and update it manually as well
	
	--- Do a Backup at this point before staring Teams and Players --
	
	TEAMS
	Then update all Teams tables ... 'wp_team_ETS, wp_team_WRZ, wp_team_PEP, etc'.  These are all in local.  Use the 'update-team-data-tables.php' file as a basis for this, but the file will need to be checked and modified to work for new 2018 data.  
	
	Once Teams are done you will have points, head-to-head, wins, locations, etc that can be used to build the Players tables.
	
	wp_players TABLE
	Add any players that are new to 2018.  Currently I run a report of Starter Points Week 1-14 in MFL and then go through that and manually add the new pflids, names, mflids to this table so that they are ready to be added once the individual player data is ready. There could be a script written to automatically add this info to wp_players and check to see if a table for that player exists and if not create it.
	
	PLAYERS individual TABLES
	Here you will have to start clean for 2018.  The script that was built for 2017 was 'update-data-wins-locations.php' was designed to migrate old data from pflmicro -> local and add wins/loss, home/away, location and playerid to each table.  The new script will want to leave all of the old data in place and just add then new data for 2018.  You can use this php script as a starting point, but it will need to be modified heavily.  
	
	
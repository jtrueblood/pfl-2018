<?php
/*
 * Template Name: Build Transients from mysql
 * Description: Builds transients from mysql tables
 */
 ?>

<?php
	
$return_teams = array();

$mydb = new wpdb('root','root','pflmicro','localhost');
$teaminfo = $mydb->get_results("SELECT * FROM teams", ARRAY_N);
set_transient( 'teaminfo', $teaminfo, 12 * HOUR_IN_SECONDS );
echo 'teaminfo transient set for 12 hours';

?>
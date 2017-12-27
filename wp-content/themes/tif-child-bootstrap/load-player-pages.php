<?php
/*
 * Template Name: Load Players Pages
 * Description: Loads all players pages in sequense in order to generate scripts where that is useful
 */
 
$playerassoc = get_players_assoc();


//printr($justids, 0);
// $load = '1991KellQB';


foreach ($playerassoc as $key => $value){
	$urls[] = 'http://posse-football.dev/player/?id='.$key;
}


/*
printr($urls, 0);
 
*/
 
?>
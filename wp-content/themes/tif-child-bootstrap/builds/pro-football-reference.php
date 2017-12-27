<?php
/*
 * Template Name: Parse Pro Football Ref
 * Description: Designed to parse the extracted json from http://www.pro-football-reference.com/ for use in player info (historical players only)
 */
 ?>


<?php 
	
get_header(); 
?><div class="add-to-top"></div>

<?php

get_cache('playerdetails', 0);	
$playerdetails = $_SESSION['playerdetails'];

$playerid = '1991RiceWR';


$jsonfile = file_get_contents(get_stylesheet_directory_uri() . '/json/pro-football-reference/'.$playerid.'.json');
$decode = json_decode($jsonfile, true); 
$breakup = explode(" ", $decode[0]['Info']);
$month = $breakup[6];
$mnt = checkmonth($month);
$day = rtrim($breakup[7],',');
$year = $breakup[8];

printr($decode, 0);

$name = $decode[0]['Name'];
$number = $decode[0]['Number'];
$height = substr($breakup[2], 0, 3);
$feet = substr($height, 0, -2);
$inches = substr($height, 2, 1);
$covheight = $feet."'".$inches.'"';
$weight = $breakup[3];
$birthday = $month.' '.$day.' '.$year;
$college = rtrim($breakup[16], ',');
$age = $year.'-'.$mnt.'-'.$day;


$addtheplayer[$playerid] = array('College' => $college, 'Height' => $covheight, 'Weight' => $weight, 'Age' => $age, 'Number' => $number);
printr($addtheplayer, 0);

$result = array_merge($playerdetails, $addtheplayer);


$gethtml = fetchHTML('http://www.pro-football-reference.com/players/R/RiceJe00.htm');
var_dump($gethtml);



$cache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/playerdetails.txt';
/*
if (file_exists($cache)){
	$get = file_get_contents($cache);
	$data = unserialize($get);

	echo '<pre>playerdetails.txt is in cache</pre>';
	printr($data, 0);
				
} else {
*/
	
	$put = serialize($result);
	file_put_contents($cache, $put);
	
// }


printr($result, 0);



get_footer(); 
?>
<?php
/**
 * TIF Bootstrap Child Theme functions and definitions
 *
 * @link http://codex.wordpress.org/Theme_Development
 * @link http://codex.wordpress.org/Child_Themes
 *
 * @package WordPress
 * @subpackage TIF Bootstrap
 * @since TIF Bootstrap 1.0
 */
 
 
 /* Enqueue Scripts and Styles */

/*
function tif_scripts() {	
	wp_enqueue_script('appjs', get_stylesheet_directory_uri() . '/js/app.js', array());	
}
*/

function tif_styles() {	
	wp_enqueue_style( 'style-bootstrap', get_stylesheet_directory_uri() . '/css/bootstrap.css', array() );
	wp_enqueue_style( 'style-footable', get_stylesheet_directory_uri() . '/plugins/fooTable/css/footable.core.css', array() );	
	wp_enqueue_style( 'style-pace', get_stylesheet_directory_uri() . '/css/pace.min.css', array() );
	wp_enqueue_style( 'style-bs-result', get_stylesheet_directory_uri() . '/css/result.css', array() );	
	wp_enqueue_style( 'style-bs-chosen', get_stylesheet_directory_uri() . '/plugins/chosen/chosen.min.css', array() );	
	wp_enqueue_style( 'style-bs-datatables', get_stylesheet_directory_uri() . '/plugins/datatables/media/css/dataTables.bootstrap.css', array() );
	wp_enqueue_style( 'style-bs-data-responsive', get_stylesheet_directory_uri() . '/plugins/datatables/extensions/Responsive/css/dataTables.responsive.css', array() );
	wp_enqueue_style( 'style-nifty', get_stylesheet_directory_uri() . '/css/nifty.css', array() );	
}

// add_action( 'wp_enqueue_scripts', 'tif_scripts' );
add_action( 'wp_enqueue_scripts', 'tif_styles' );

//custom image sizes
add_image_size( 'player-mini', 50, 50, true );


/* Vars used commonly in functions */
session_start();
$season == date("Y");

/* Store all team IDs and Names in a Session Variable */
$teamids = array( 'RBS'=>'Red Barons', 'ETS'=>'Euro-Trashers', 'PEP'=>'Peppers', 'WRZ'=>'Space Warriorz',  'CMN'=>'C-Men', 'BUL'=>'Raging Bulls', 'SNR'=>'Sixty Niners', 'TSG'=>'Tsongas', 'BST'=>'Booty Bustas', 'SON'=>'Rising Sons',  'PHR'=>'Paraphernalia', 'HAT'=>'Jimmys Hats',  'ATK'=>'Melmac Attack',  'MAX'=>'Mad Max', 'DST'=>'Destruction');
$_SESSION['teamids'] = $teamids;

/* connect to pflmicro database */
$mydb = new wpdb('root','root','pflmicro','localhost');


if( function_exists('acf_add_options_page') ) {

$args = array('title' => 'Options');
	
	acf_add_options_page($args);
	
}

/* allow plugin updates on localhost */
if ( is_admin() ) {
add_filter( 'filesystem_method', create_function( '$a', 'return "direct";' ) );
	if ( ! defined( 'FS_CHMOD_DIR' ) ) {
		define( 'FS_CHMOD_DIR', 0751 );
	}
}

add_image_size( 'player-card', 400, 400, array( 'center', 'top' ) );

/* clean print_r */
function printr($data, $die) {
   echo '<pre>';
      print_r($data);
   echo '</pre>';
   if ($die == 1):
	   echo die();
	   echo exit(0);
   endif;
}

/* clean print_r */

function printrlabel($data, $label) {
   echo '<pre>';
   echo '<h3>'.$label.'</h3>';	
   print_r($data);
   echo '</pre>';
}


/* Remove special characters from string */
function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

function check_if_image($image_src){
	global $wpdb;
	$query = $wpdb->query("SELECT ID FROM wp_posts WHERE post_title = '$image_src'" );
	return $query;	
}

function get_attachment_url_by_slug( $slug ) {
  $args = array(
    'post_type' => 'attachment',
    'name' => sanitize_title($slug),
    'posts_per_page' => 1,
    'post_status' => 'inherit',
  );
  $_header = get_posts( $args );
  $header = $_header ? array_pop($_header) : null;
  return $header ? wp_get_attachment_url($header->ID) : '';
}


/* Get Single txt file from Cache and store as array.  0 no print, 1 for print on page */

function get_cache($file, $print){
	$cache = 'http://pfl-data.local/wp-content/themes/tif-child-bootstrap/cache/'.$file.'.txt';
	$get = file_get_contents($cache, FILE_USE_INCLUDE_PATH);
	$data = unserialize($get);
	if(!empty($data)){
		$count = count(array_keys($data));
	} 
	$_SESSION[$file] = $data;
		
	if ($print == 1){
		echo '<p><pre>';
			var_dump($data);
		echo '</pre></p>';	
	} 
}

function simple_cache($file){
	$cache = 'http://pfl-data.local/wp-content/themes/tif-child-bootstrap/cache/'.$file.'.txt';
	$get = file_get_contents($cache, FILE_USE_INCLUDE_PATH);
	$data = unserialize($get); 
	$_SESSION[$file] = $data;
}


/* Get Player data from cache */

function get_player_cache($file, $print, $year){
	$playercache = 'http://pfl-data.local/wp-content/themes/tif-child-bootstrap/cache/playerdata/'.$file.'.txt';
	$playerget = file_get_contents($playercache, FILE_USE_INCLUDE_PATH);
	$playerdata = unserialize($playerget);
	$playercount = count(array_keys($playerdata));
	
		$_SESSION[$indplayerdata] = $playerdata ;
		
 		if ($print == 1){
			echo '<p><pre>';
			var_dump($playerdata);
			echo '</pre></p>';	
		}
		
		//all of the yearly data
		$q = 0;
		if ($year > 0){
			while($q < $playercount){
				if($playerdata[$q][1] == $year){
					 $theplayer = $playerdata[0][6];
					 $yearonly[] = array($playerdata[$q][1], $playerdata[$q][2], $playerdata[$q][3], $playerdata[$q][4], $playerdata[$q][5]);
					$q++;
				} else {
					$q++;
				}
			}
			$_SESSION[$theplayer] = $yearonly;
		} 
		
		if ($year < 0){
			
			while($q < $playercount){
				$theplayer = $playerdata[0][6];
				$pointsonly[] = $playerdata[$q][3];
				$q++;
			}
			
			$_SESSION[$theplayer] = $pointsonly;
			
			$r=0;
			while($r < $playercount){
				$theplayer = $playerdata[0][6];
				$seasonsonly[] = $playerdata[$r][1];
				$r++;
			}
				
			$_SESSION[$theplayer] = $seasonsonly;
		}
	
}


function player_data_cache($playerid){
	$playercache = 'http://pfl-data.local/wp-content/themes/tif-child-bootstrap/cache/playerdata/'.$playerid.'.txt';
	$playerget = file_get_contents($playercache, FILE_USE_INCLUDE_PATH);
	$playerdata = unserialize($playerget);
	$playercount = count(array_keys($playerdata));
	
	$_SESSION[$playerid] = $data;
	
}


// retrieve player data from transient
function get_allplayerdata_trans($pid) {
	$transient = get_transient( $pid.'_trans' );
	if( ! empty( $transient ) ) {
    	return $transient;
  	}
}


// set or retrieve player data from transient
function allplayerdata_trans($pid) {
	$transient = get_transient( $pid.'_trans' );
	if( ! empty( $transient ) ) {
    	return $transient;
  	} else {
	   	$set = get_allplayerdata_trans($pid);
	    set_transient( $pid.'_trans', $set, '' );
	    return $set;
    }
}


// retrieve team array from transient. -- these team transinets are set in the homepage

function get_team_data_trans($teamid) {
	$transient = get_transient( $teamid.'_trans' );
	if( ! empty( $transient ) ) {
    	return $transient;
  	} else {
	  	'Could not find transient';
  	}
}


// set team array as a transient

function set_team_trans() {
  $transient = get_transient( 'team_trans' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_teams();
    set_transient( 'team_trans', $set, '' );
    return $set;
  }

}


// set masterschedule array as a transient

function set_schedule_trans() {
  $transient = get_transient( 'schedule_trans' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = master_schedule();
    set_transient( 'schedule_trans', $set, YEAR_IN_SECONDS );
    return $set;
  }

}


// simple get functions
function the_seasons(){
	$year = date('Y');

	$o = 1991;
	while ($o <= $year){
		$theseasons[] = $o;
		$o++;
	}

	return $theseasons;
}

function the_games(){
	$theseasons = the_seasons();
	foreach($theseasons as $gameids){
		$weekformat = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14');
		foreach($weekformat as $weeknum){
			$ids[$gameids.$weeknum] = '';
		}
	}
	return $ids;
}

function the_weeks(){
	$years = the_seasons();
	$weeks = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14');
	
	foreach ($years as $year){
		foreach ($weeks as $week){
			$theweeks[] = $year.$week;
		}
	}
	
	return $theweeks;
	
}

function the_weeks_with_post(){
    $years = the_seasons();
    $weeks = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17');

    foreach ($years as $year){
        foreach ($weeks as $week){
            $theweeks[] = $year.$week;
        }
    }

    return $theweeks;

}


// shorten players name 

function shortenname($pname){
	if (strlen($pname) > 15){
		$pname = substr($pfirst, 0, 1).'. '.$plast;
		}
}

function firstinitial($name){
	$pname = substr($name, 0, 1);
}


function convert_week_id($wid){
	$year = substr($wid, 0, 4);
	$w = substr($wid, -2);
	$week = ltrim($w, '0');
	$output = 'Week '.$week.', '.$year;
	return $output;
}

// if body has class page-id-XX then echo the class requested... 

function ifbodyclass ($theid, $theclass){
	$bodyclasses = get_body_class();
	if (in_array('page-id-'.$theid,$bodyclasses)) {
	    echo $theclass;
	} 
}

//  converts Object into Array

function objectToArray($d) {
	if (is_object($d)) {
		$d = get_object_vars($d);
	}
	
	if (is_array($d)) {
		return array_map(__FUNCTION__, $d);
	}
	else {
		return $d;
	} 
}

//  flattens a multi-dimaensional array

function array_flatten($array) {
  
	   $return = array();
	   foreach ($array as $key => $value) {
	       if (is_array($value)){ $return = array_merge($return, array_flatten($value));}
	       else {$return[$key] = $value;}
	   }
	   return $return;

}





function pointsleaders($theposition){
		$i = 0; 
		
		if ($theposition == 'QB'){
			get_cache('rankyears/alltimeleaders_QB', 0);	
			$leadersbuild = $_SESSION['rankyears/alltimeleaders_QB'];
		} else {
			if ($theposition == 'RB'){
				get_cache('rankyears/alltimeleaders_RB', 0);	
				$leadersbuild = $_SESSION['rankyears/alltimeleaders_RB'];
			} else {
				if ($theposition == 'WR'){
					get_cache('rankyears/alltimeleaders_WR', 0);	
					$leadersbuild = $_SESSION['rankyears/alltimeleaders_WR'];
				} else {
				get_cache('rankyears/alltimeleaders_PK', 0);	
				$leadersbuild = $_SESSION['rankyears/alltimeleaders_PK'];
				}
			}
		}
	
		
		$j = 1;
		foreach ($leadersbuild as $leadersprint){
		if ($leadersprint[6] == 1){
			$isactive = 'active-player';
		} else {
			$isactive = '';
		}
		echo '<tr>
				<td class="text-center">'.$j.'-'.$leadersprint[4].'</td>
				<td class="text-center"><img src="http://pfl-data.local/wp-content/themes/tif-child-bootstrap/img/players/'.$leadersprint[0].'.jpg" class="leaders-image"></td>
				<td>
				<a href="/player?id='.$leadersprint[0].'"><span class="text-semibold '.$isactive.'">'.$leadersprint[2].' '.$leadersprint[3].'</span></a>
				</td>
				<td class="text-center"><span class="text-semibold">'.$leadersprint[5].'</span></td>
				<td class="text-center"><span class="text-semibold">'.$leadersprint[1].'</span></td>
			</tr>';
		$j++;
		}
}

function topgames($theposition, $low){
		
	get_cache('allplayerdata', 0);	
	$allplayerdata = $_SESSION['allplayerdata'];
	
	$players = get_players_assoc ();
	
	$f = 0;
	foreach ($allplayerdata as $getposition){
		foreach ($getposition as $stillget){
			$thepos = substr($stillget[6], -2);
			$gmpoints = $stillget[3];
			if ($thepos == $theposition && $gmpoints >= $low){
				$highgames[] = array($stillget[0], $stillget[1], $stillget[2], $gmpoints, $stillget[4], $stillget[6]);
			}
		}
		
	}
			
	foreach ($highgames as $highprint){
	$playername =  $players[$highprint[5]][0].' '.$players[$highprint[5]][1];
	echo '<tr>
			<td class="text-center">'.$highprint[3].'</td>
			<td class="text-center"><img src="http://pfl-data.local/wp-content/themes/tif-child-bootstrap/img/players/'.$highprint[5].'.jpg" class="leaders-image"></td>
			<td class="text-bold">'.$playername.'</td>
			<td class="text-center">Week '.$highprint[2].', '.$highprint[1].'</td>
			<td class="text-center">'.$highprint[4].'</td>
		</tr>';
	}
		
}

function tablehead ($title, $labelarray){
	$prheader .= '<div class="panel"><div class="panel-heading"><h3 class="panel-title">'.$title.'</h3></div>';
	
	$prheader .= '<div class="panel-body"><div class="table-responsive"><table class="table table-striped"><thead><tr>';
	
	foreach ($labelarray as $getlabels){
		$prheader .= '<th>'.$getlabels.'</th>';
	}
				
	$prheader .= '</tr></thead><tbody>';
	echo $prheader;	
}
	
	
function tablefoot ($note){	
	echo '</tbody></table><span class="text-small">'.$note.'</span></div></div></div>';
}

class table_foot {
	var $footnote;
	
	function tablefoot (){	
		echo '</tbody></table><span class="text-small">'.$footnote.'</span></div></div></div>';
	}
	
}

/*
function player_id_transient($plid) {

  $transient = get_transient( 'player_news' );
  
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
  
    $url = 'https://api.fantasydata.net/nfl/v2/json/NewsByPlayerID/'.$plid.'';
    
    // We are structuring these args based on the API docs as well.
    $args = array(
      'headers' => array(
        'Ocp-Apim-Subscription-Key' => '610b08432214484cbb360777ed373371'
      ),
    );
    
    // Call the API.
    $out = wp_remote_get( $url, $args );
    set_transient( 'player_news', $out, 86400 );
    return $out;
    
  }
  
}
*/


/*
function get_player_name($y, $l, $i){
	// player specific info by MFL id  ## year, league, player id
	$jsononeplayer = file_get_contents('http://football24.myfantasyleague.com/'.$y.'/export?TYPE=players&L='.$l.'&JSON=1&PLAYERS='.$i.'');
	$oneplayer  = json_decode($jsononeplayer, true);
	$player = $oneplayer['players']['player'];
	return $player;
}
*/

function getallstarter($theweek, $matchup, $franchise){
	$jsonweek = file_get_contents('http://football24.myfantasyleague.com/2015/export?TYPE=weeklyResults&L=47099&W='.$theweek.'&JSON=1');
	$theweek = json_decode($jsonweek, true);
	$starters = $theweek['weeklyResults']['matchup'][$matchup]['franchise'][$franchise]['starters'];
	return $starters;
}

function getallteamids($theweek, $matchup, $franchise){
	$jsonweek = file_get_contents('http://football24.myfantasyleague.com/2015/export?TYPE=weeklyResults&L=47099&W='.$theweek.'&JSON=1');
	$theweek = json_decode($jsonweek, true);
	$teamid = $theweek['weeklyResults']['matchup'][$matchup]['franchise'][$franchise]['id'];
	return $teamid;
}

/*
function getallteamcache ($team){
	get_cache('team/'.$team.'_f', 0);	
	$build = $_SESSION['team/'.$team.'_f'];
	
	return $build;
}
*/


// requires 'playersassoc' cache added to page
function leadersbyseason ($array, $year, $labelpos){

    $playersassoc = get_players_assoc ();
    
    $probowl = probowl_boxscores_player_year($year);
    if(isset($probowl)){
		foreach($probowl as $key => $value){
			$ispro[$value['playerid']] = $value['starter'];
		}
	}
	
	$printval .= '<div class="col-xs-24 col-sm-12">';
		$printval .= '<div class="panel">';
			$printval .= '<div class="panel-heading">';
				$printval .= '<h2 class="panel-title">'.$labelpos.'</h2>';
			$printval .= '</div>';
		$printval .= '<div class="panel-body">';
			$printval .= '<div class="table-responsive">';
				$printval .= '<table class="table table-striped leaders-season">';
					$printval .= '<thead>';
						$printval .= '<tr>';
							$printval .= '<th>Player</th><th class="text-center">Points</th><th class="text-center">Games</th><th class="text-center">Teams</th><th class="text-center min-width">Pro</th>';
						$printval .= '</tr>';
					$printval .= '</thead>';
					$printval .= '<tbody>';
							$rank = 1;
							
							foreach ($array as $key => $getvars){
								$first = $playersassoc[$getvars['playerid']][0];
								$last = $playersassoc[$getvars['playerid']][1];
								$getteam = get_player_teams_season($getvars['playerid']);
								$teams = $getteam[$year];
								
								$pr = '';
								if($teams > 0){
									$c = count($teams);
								}
								if ($c == 1){
									$pr = $teams[0];	
								} else {
									if(is_array($teams)):
										foreach ($teams as $team){
											$pr .= $team.' ';
										}
									endif;
								}
								
								//setprobowl markers
								$getpro = $ispro[$getvars['playerid']];
								
								if($getpro == 0){
									$pro = '<i class="fa fa-circle"></i>';
								}
								if($getpro == 1){
									$pro = '<i class="fa fa-circle-o"></i>';
								} 
								if($getpro == 2){
									$pro = '<i class="fa fa-circle-thin"></i>';
								} 
								if($getpro == '' ){
									$pro = '';
								} 
								
								
								// get rookie year to display (R) and omit 1991
								$info = get_player_basic_info($getvars['playerid']);
								$rookie = $info[0]['rookie'];
								if($rookie == $year && $year != 1991){
									$pr_rook = ' <small>(R)</small>';
								} else {
									$pr_rook = '';
								}
								
// 								$printval .= '<td>'.$rank.'.</td>';
								$printval .= '<td><a href="/player/?id='.$getvars['playerid'].'" class="btn-link">'.$first.' '.$last.'</a>'.$pr_rook.'</td>';
								$printval .= '<td class="text-center min-width">'.$getvars['points'].'</td>';
								$printval .= '<td class="text-center min-width">'.$getvars['games'].'</td>';
								$printval .= '<td class="text-center min-width">'.$pr.'</td>';
								$printval .= '<td class="text-center min-width">'.$pro.'</td>';
								$printval .= '</tr>';
								$rank++;
								
								
							}
							
						$printval .= '</tbody></table></div></div></div></div>';
	echo $printval;
}


// requires 'playersassoc' cache added to page
function get_player_season_rank ($playerid, $year){
	$pos = substr($playerid, -2); 
	global $wpdb;
	$getdata = $wpdb->get_results("select * from wp_season_leaders where '$year' like season", ARRAY_N);	
	foreach ($getdata as $key => $value){
		$position = substr($value[1], -2);
		if ($position == $pos){
			$data[$value[1]] = $value[3];
		}
	}
	if($data):
		arsort($data);
		$getindex = array_search($playerid,array_keys($data));
		// add one to the index to get a rank
		$output = $getindex + 1;	
		return $output;
	else:	
		$output = 0;
		return $output;
	endif;	
	

}



//convert month
function checkmonth($month)
{
if($month == 'January')
{
$val = 1;
}else
if($month == 'February')
{
$val = 2;
 
}else
if($month == 'March')
{
$val = 3;
 
}else
if($month == 'April')
{
$val = 4;
 
}else
if($month == 'May')
{
$val = 5;
 
}else
if($month == 'June')
{
$val = 6;
}else
if($month == 'July')
{
$val = 7;
}else
if($month == 'August')
{
$val = 8;
}else
if($month == 'September')
{
$val = 9;
}else
if($month == 'October')
{
$val = 10;
}else
if($month == 'November')
{
$val = 11;
}else
if($month == 'December')
{
$val = 12;
}
return $val;
}


function fetchHTML( $url )
{
    $feed = '<div #id="info_box">Lots of stuff in here</div>';
    $content = file_get_contents( $url );
    preg_match( '/<div>([\s\S]{1,})<\/body>/m', $content, $match );
    $content = $match[1];
    return $content;
} 


// print player image
function playerimg ( $playerid ){
	$playerimg = 'http://pfl-data.local/wp-content/themes/tif-child-bootstrap/img/players/'.$playerid.'.jpg';
	if (!empty($playerimg)){
		return $playerimg;
	} 
}



// used for dropdown to select a year and post to $year = $_GET['id'] .  Also requires #yearbtn click function in app.js
function selectseason (){

	$theyears = the_seasons();

	$prseason .= '<div class="panel">
	<div class="panel-body">
		<div class="col-xs-24 col-sm-18">
			<select data-placeholder="Select Season..." class="chzn-select" style="width:100%;" tabindex="2" id="pickyear">
				<option value=""></option>';
				
				foreach($theyears as $select_year){ 
					$prseason .= '<option value="'.$select_year.'">'.$select_year.'</option>';    
				}
			
	$prseason .= '</select>
		</div>
		<div class="col-xs-24 col-sm-6">
			<button class="btn btn-warning" id="yearbtn">Submit</button>
		</div>
	</div>
	</div>';
	echo $prseason;
}

// used for dropdown to select a team and post to $team = $_GET['id'] .  Also requires #teambtn click function in app.js
function selectteam (){
	get_cache('teaminfo', 0);	
	$theteams = $_SESSION['teaminfo'];

	$prseason .= '<div class="panel">
	<div class="panel-body">
		<div class="col-xs-24 col-sm-18">
			<select data-placeholder="Select Team..." class="chzn-select" style="width:100%;" tabindex="2" id="pickyear">
				<option value=""></option>';
				
				foreach($theteams as $select_team){ 
					$prseason .= '<option value="'.$select_team[3].'">'.$select_team[3].'</option>';    
				}
			
	$prseason .= '</select>
		</div>
		<div class="col-xs-24 col-sm-6">
			<button class="btn btn-warning" id="yearbtn">Submit</button>
		</div>
	</div>
	</div>';
	echo $prseason;
}


// new functions for just database data
function get_table($table){
	global $wpdb;
	$getdata = $wpdb->get_results("select * from $table", ARRAY_N);
	return $getdata;	
}

function get_players_index(){
	global $wpdb;
	$getplayers = $wpdb->get_results("select * from wp_players", ARRAY_N);
	
	foreach ($getplayers as $revisequery){
		$playersindex[] = array( 
			$revisequery[1], 
			$revisequery[2], 
			$revisequery[3],  
			$revisequery[4]
		);
	}
	
	return $playersindex;
}

function get_players_assoc(){
	global $wpdb;
	$getplayers = $wpdb->get_results("select * from wp_players", ARRAY_N);
	
	foreach ($getplayers as $revisequery){
		$playersassoc[$revisequery[0]] = array( 
			$revisequery[1], 
			$revisequery[2], 
			$revisequery[3],  
			$revisequery[4],
			$revisequery[5],
			$revisequery[6],
			$revisequery[7],
			$revisequery[8],
			$revisequery[9],
			$revisequery[10],
			$revisequery[11],
			$revisequery[12],
			$revisequery[13]
		);
	}
	
	return $playersassoc;
}

function just_player_ids(){
	$playerassoc = get_players_assoc ();
	foreach ($playerassoc as $key => $players){
		$playerids[] = $key;
	}
	return $playerids;
}

function just_player_ids_with_position(){
	$playerassoc = get_players_assoc ();
	foreach ($playerassoc as $key => $players){
		$playerids[$key] = $players[2];
	}
	return $playerids;
}

function get_overtime(){
	global $wpdb;
	$getovertime = $wpdb->get_results("select * from wp_overtime", ARRAY_N);
	
	foreach ($getovertime as $revisequery){
		$theot[$revisequery[0]] = array(
			$revisequery[0], 
			$revisequery[1], 
			$revisequery[2], 
			$revisequery[3], 
			$revisequery[4], 
			$revisequery[5],
			$revisequery[6],
			$revisequery[7],
			$revisequery[8],
			$revisequery[9],
			$revisequery[10],
			$revisequery[11],
			$revisequery[12],
			$revisequery[13]
		);
	}
	
	return $theot;
}

function get_players_name_pfrdata(){
	global $wpdb;
	$getplay = $wpdb->get_results("select * from wp_players", ARRAY_N);
	
	foreach ($getplay as $r){
		$name = $r[1].' '.$r[2];
		$a = substr($r[2], 0, 1);
		$l = substr($r[2], 0, 4);
		$f = substr($r[1], 0, 2);
		$n = '00';
		$playernames[$r[0]] = array( 
			'name' => $name,
			'pro-uri' => $l.$f.$n
		);
	}
	
	return $playernames;
}


function get_team_overtime($team){
	global $wpdb;
	$getovertime = $wpdb->get_results("select * from wp_overtime where winteam like '$team' or loseteam like '$team'", ARRAY_N);
	
	foreach ($getovertime as $revisequery){
		$teamot[$revisequery[0]] = array(
			$revisequery[0], 
			$revisequery[1], 
			$revisequery[2], 
			$revisequery[3], 
			$revisequery[4], 
			$revisequery[5],
			$revisequery[6],
			$revisequery[7],
			$revisequery[8],
			$revisequery[9],
			$revisequery[10],
			$revisequery[11],
			$revisequery[12],
			$revisequery[13]
		);
	}
	
	return $teamot;
}


function get_player_overtime($pid){
	$playerpos = substr($pid, -2);
	
	global $wpdb;
	$getplayerovertime = $wpdb->get_results("select * from wp_overtime", ARRAY_N);
	
	foreach ($getplayerovertime as $revisequery){
		
			$weekid = substr($revisequery[0], 0, 6);		
			if ($revisequery[3] == $pid){ $playerot[$weekid] = 1; } 
			if ($revisequery[4] == $pid){ $playerot[$weekid] = 1; }  
			if ($revisequery[5] == $pid){ $playerot[$weekid] = 1; } 
			if ($revisequery[6] == $pid){ $playerot[$weekid] = 1; } 
			if ($revisequery[7] == $pid){ $playerot[$weekid] = 1; } 
			if ($revisequery[8] == $pid){ $playerot[$weekid] = 1; } 
			if ($revisequery[9] == $pid){ $playerot[$weekid] = 1; } 
			if ($revisequery[10] == $pid){ $playerot[$weekid] = 1; } 
	
	}
	
	return $playerot;
	
}

function get_player_rookie_years(){
	$playerassoc = just_player_ids();
	foreach ($playerassoc as $val){
		$get = get_player_career_stats($val); 
		$rookieyears[$val] = $get['years'][0];
	}
	return $rookieyears;
}

function get_protections(){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_protections", ARRAY_N);
	
	// set the value of the key -- id = 0,  year = 2,  team = 5	
	
	foreach ($get as $key => $revisequery){
		$protections[$revisequery[0]] = array(
			'protectionid' => $revisequery[0], 
			'year' => $revisequery[1], 
			'first' => $revisequery[2], 
			'last' => $revisequery[3],  
			'team' => $revisequery[4],
			'position' => $revisequery[5],
			'playerid' => $revisequery[6]
		);
	}
	
	return $protections;
}

function get_trades(){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_trades", ARRAY_N);
	
	// set the value of the key -- id = 0,  year = 2,  team = 5	
	
	foreach ($get as $key => $revisequery){
		$trades[] = array(
			'year' => $revisequery[1], 
			'team1' => $revisequery[2], 
			'players1' => $revisequery[3], 
			'picks1' => $revisequery[4], 
			'protections1' => $revisequery[5],
			'team2' => $revisequery[6],
			'players2' => $revisequery[7], 
			'picks2' => $revisequery[8], 
			'protections2' => $revisequery[9],
			'notes' => $revisequery[10],
			'when' => $revisequery[11]
		);
	}
	
	return $trades;
}

function get_trade_by_player($pid){
	global $wpdb;
	$get1 = $wpdb->get_results("select * from wp_trades where players1 like '%$pid%' or players2 like '%$pid%'", ARRAY_N);
	// set the value of the key -- id = 0,  year = 2,  team = 5	
	
	foreach ($get1 as $key => $revisequery){
		$players1 = explode(',', $revisequery[3]);
		$picks1 = explode(',', $revisequery[4]);
		$players2 = explode(',', $revisequery[7]);
		$picks2 = explode(',', $revisequery[8]);
		
		if(in_array($pid, $players1)){
			$numplayer = 1;
			$traded_from_team = $revisequery[6];
			$traded_to_team = $revisequery[2];
			$players_to = $players2;
			$picks_to = $picks2;
			$players_from =  $players1;
			$picks_from = $picks1;
		} else {
			$numplayer = 2;
			$traded_from_team = $revisequery[2];
			$traded_to_team = $revisequery[6];
			$players_to = $players1;
			$picks_to = $picks1;
			$players_from =  $players2;
			$picks_from = $picks2;
		}
		
		$trades[$revisequery[1]][] = array(
			'playerid' => $pid,
			'tradeteamnum' => $numplayer,
			'year' => $revisequery[1], 
			'traded_to_team' => $traded_to_team,
			'received_players' => $players_from,
			'received_picks' => $picks_from,
			'traded_from_team' => $traded_from_team,
			'sent_players' => $players_to,
			'sent_picks' => $picks_to,
			'notes' => $revisequery[10],
			'when' => $revisequery[11]
		);
	}	
	
	return $trades;
	//return $get1;
	
}

function format_pick($pick){
	$explode = explode('.', $pick);
	echo 'R'.$explode[1].'-'.$explode[2].', '.$explode[0];
}

function get_protections_player($pid){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_protections where playerid = '$pid'", ARRAY_N);
	
	// set the value of the key -- id = 0,  year = 2,  team = 5	
	
	foreach ($get as $key => $revisequery){
		$protections[$revisequery[0]] = array(
			'protectionid' => $revisequery[0], 
			'year' => $revisequery[1], 
			'first' => $revisequery[2], 
			'last' => $revisequery[3],  
			'team' => $revisequery[4],
			'position' => $revisequery[5],
			'playerid' => $revisequery[6]
		);
	}
	
	return $protections;
}

// get a standing table by year
function get_standings($year){
	global $wpdb;
	$get = $wpdb->get_results("select * from stand$year", ARRAY_N);
	
	// set the value of the key -- id = 0,  year = 2,  team = 5	
	
	foreach ($get as $key => $revisequery){
		$standings[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'year' => $revisequery[1], 
			'seed' => $revisequery[2], 
			'division' => $revisequery[3],  
			'teamid' => $revisequery[4],
			'teamname' => $revisequery[5],
			'win' => $revisequery[6],
			'loss' => $revisequery[7],
			'gb' => $revisequery[9],
			'pts' => $revisequery[10],
			'ptsvs' => $revisequery[12],
			'divwin' => $revisequery[14],
			'divloss' => $revisequery[15]
		);
	}
	
	return $standings;
}


// get all of the standings tables in one big array
function get_all_standings(){
	global $wpdb;
	$seasons = the_seasons();
	
	foreach ($seasons as $season){
		$allstandings[$season] = get_standings($season);
	}	
	return $allstandings;
}


function get_standings_by_team($year, $team){
	global $wpdb;
	$get = $wpdb->get_results("SELECT * from stand$year where teamid = '$team';", ARRAY_N);
	
	// set the value of the key -- id = 0,  year = 2,  team = 5	
	
	foreach ($get as $key => $revisequery){
		$standings[] = array(
			'id' => $revisequery[0], 
			'year' => $revisequery[1], 
			'seed' => $revisequery[2], 
			'division' => $revisequery[3],  
			'teamid' => $revisequery[4],
			'teamname' => $revisequery[5],
			'win' => $revisequery[6],
			'loss' => $revisequery[7],
			'gb' => $revisequery[9],
			'pts' => $revisequery[10],
			'ptsvs' => $revisequery[12],
			'divwin' => $revisequery[14],
			'divloss' => $revisequery[15]
		);
	}
	
	return $standings;
}

function get_team_division_by_year($team, $year){
	global $wpdb;
	$div = $wpdb->get_results("SELECT division from stand$year where teamID = '$team';", ARRAY_N);
	return $div[0];
}

function get_standings_weekly_by_team($team, $year, $week){
	global $wpdb;
	$get = $wpdb->get_results("SELECT * from wp_team_$team where season = $year && week <= $week;", ARRAY_N);
	
	
	foreach ($get as $key => $revisequery){
		$div = get_team_division_by_year($team, $year);
		$oppdiv = get_team_division_by_year($revisequery[5], $year);
		
		$result = $revisequery[9];
		if ($result > 0){
			$win = 1;
			$loss = 0;
		} else {
			$win = 0;
			$loss = 1;
		}
		
		if($oppdiv[0] != $div[0]){
			$divwin = 0;
			$divloss = 0;
		} else {
			if ($result > 0){
				$divwin = 1;
				$divloss = 0;
			} else {
				$divwin = 0;
				$divloss = 1;
			}
		}
	
		$standings[$revisequery[2]] = array(
			'division' => $div[0],
			'id' => $revisequery[0], 
			'year' => $revisequery[1], 
			'week' => $revisequery[2], 
			'teamid' => $revisequery[3],  
			'points' => $revisequery[4],
			'versus' => $revisequery[5],
			'oppodiv' => $oppdiv[0],
			'vspoints'=> $revisequery[6],
			'win' => $win,
			'loss' => $loss,
			'plusmin' => $revisequery[9],
			'divwin' => $divwin,
			'divloss' => $divloss
		);
	}
	return $standings;
}	


// returns just week 15 playoff games
function get_playoffs(){
	
	global $wpdb;
	$getplayoffs = $wpdb->get_results("select * from wp_playoffs WHERE week = '15'", ARRAY_N);
	
	foreach ($getplayoffs as $revisequery){
		$playoffs[] = array(
			'playoffid' => $revisequery[0], 
			'year' => $revisequery[1], 
			'week' => $revisequery[2], 
			'playerid' => $revisequery[3],  
			'score' => $revisequery[4],
			'team' => $revisequery[5],
			'versus' => $revisequery[6],
			'overtime' => $revisequery[7],
			'result' => $revisequery[8]
		);
	}
	
	return $playoffs;
}

// returns just team info for playoffs and possebowl
function get_postseason(){
	
	global $wpdb;
	$getplayoffs = $wpdb->get_results("select * from wp_playoffs", ARRAY_N);
	
	foreach ($getplayoffs as $revisequery){
		$playoffs[] = array(
			'playoffid' => $revisequery[0], 
			'year' => $revisequery[1], 
			'week' => $revisequery[2], 
			'playerid' => $revisequery[3],  
			'score' => $revisequery[4],
			'team' => $revisequery[5],
			'versus' => $revisequery[6],
			'overtime' => $revisequery[7],
			'result' => $revisequery[8]
		);
	}
	
	return $playoffs;
}

// returns just team info for playoffs and possebowl
function get_team_postseason($team){
	
	global $wpdb;
	$getplayoffs = $wpdb->get_results("select * from wp_playoffs where team = '$team'", ARRAY_N);
	
	foreach ($getplayoffs as $revisequery){
		$playoffs[] = array(
			'playoffid' => $revisequery[0], 
			'year' => $revisequery[1], 
			'week' => $revisequery[2], 
			'playerid' => $revisequery[3],
			'position' => substr($revisequery[3], -2),   
			'score' => $revisequery[4],
			'team' => $revisequery[5],
			'versus' => $revisequery[6],
			'overtime' => $revisequery[7],
			'result' => $revisequery[8]
		);
	}
	
	return $playoffs;
}

//

function get_team_postseason_by_game($team){
	$postseason = get_team_postseason($team);
	
	if(isset($postseason)){
		foreach ($postseason as $key => $item){
			$byyear[$item['year'].$item['week']] = $item['result'];
		}
	}
		
	return $byyear;
}

function get_playoff_points_by_team_year($year, $team, $week){
	global $wpdb;
	$get = $wpdb->get_results("select points from wp_playoffs where week = '$week' && team = '$team' && year='$year' && overtime <> 1", ARRAY_N);
	
	foreach ($get as $value){
		$sumval[] = $value[0];
	}
	if(isset($sumval)){
		$output = array_sum($sumval);
	}
	return $output;
}

function get_all_team_postseason_by_week($team){
	$seasons = the_seasons();
	
	foreach ($seasons as $year){
		$get[$year.'15'] = get_playoff_points_by_team_year($year, $team, 15);
		$get[$year.'16'] = get_playoff_points_by_team_year($year, $team, 16);
	}
	
	return $get;
	
}

// returns just team info for playoffs and possebowl as a summary
function get_team_postseason_summary($team){
	
	global $wpdb;
	$getplayoffssum = $wpdb->get_results("select * from wp_playoffs where team = '$team'", ARRAY_N);
	$winsonly = $wpdb->get_results("select * from wp_playoffs where team = '$team' && result = 1", ARRAY_N);
	
	if($getplayoffssum != ''){
		foreach ($getplayoffssum as $query){
			$pos = substr($query[3], -2);
			$plscore[] = $query[4];
			if($pos == 'QB'){
				$plresult[] = $query[8];
			}
		}
		
		$countwin = 0;
		
		foreach ($winsonly as $getwins){
			$checkid = $getwins[0];
			$subval[] = substr($checkid, 0, 6);
		}
		if(is_array($subval)){
			$unique = array_unique($subval);
			
			$teamscore = get_all_team_postseason_by_week($team);
			$removeblanks = array_filter($teamscore);
			$games = count($removeblanks); 
			$wins = count($unique);
			$lowweek = array_search(min($teamscore), $teamscore);
			$low = min($removeblanks);
			$loss = $games - $wins;
			$plwinper = $wins / $games;
				
			$playoffsummary = array(
				'teamid' 		=> $team,
				'total_points' 	=> array_sum($teamscore),
				'total_games' 	=> $games,
				'total_wins' 	=> $wins,
				'total_loss' 	=> $loss,
				'winper' 		=> $plwinper,
				'high' 			=> array(
									'highpts' => max($teamscore),
									'highweek' => array_search(max($teamscore), $teamscore)
								),
				'low' 			=> array(
									'lowpts' => $low,
									'lowweek' => $lowweek
				)
			);
		}
	}
	return $playoffsummary;
}


function get_award($awardopt, $thekey){
	global $wpdb;
	$getaward = $wpdb->get_results("select * from wp_awards WHERE award = '$awardopt'", ARRAY_N);
	
	// set the value of the key -- id = 0,  year = 2,  team = 5	
	
	foreach ($getaward as $key => $revisequery){
		$awardinfo[$revisequery[$thekey]] = array(
			'awardid' => $revisequery[0], 
			'award' => $revisequery[1], 
			'year' => $revisequery[2], 
			'first' => $revisequery[3],  
			'last' => $revisequery[4],
			'team' => $revisequery[5],
			'position' => $revisequery[6],
			'owner' => $revisequery[7],
			'pid' => $revisequery[8],
			'gamepoints' => $revisequery[9],
		);
	}
	
	return $awardinfo;
}

function get_awards(){
    global $wpdb;
    $getaward = $wpdb->get_results("select * from wp_awards", ARRAY_N);

    // set the value of the key -- id = 0,  year = 2,  team = 5

    foreach ($getaward as $key => $revisequery){
        $awards[$revisequery[2]][$revisequery[0]] = array(
            'awardid' => $revisequery[0],
            'award' => $revisequery[1],
            'year' => $revisequery[2],
            'first' => $revisequery[3],
            'last' => $revisequery[4],
            'team' => $revisequery[5],
            'position' => $revisequery[6],
            'owner' => $revisequery[7],
            'pid' => $revisequery[8],
            'gamepoints' => $revisequery[9],
        );
    }

    return $awards;
}

function get_award_team($teamid){
	global $wpdb;
	$getaward = $wpdb->get_results("select * from wp_awards WHERE team = '$teamid'", ARRAY_N);
	
	// set the value of the key -- id = 0,  year = 2,  team = 5	
	
	foreach ($getaward as $key => $revisequery){
		$awardteam[] = array(
			'awardid' => $revisequery[0], 
			'award' => $revisequery[1], 
			'year' => $revisequery[2], 
			'first' => $revisequery[3],  
			'last' => $revisequery[4],
			'team' => $revisequery[5],
			'position' => $revisequery[6],
			'owner' => $revisequery[7],
			'pid' => $revisequery[8],
			'gamepoints' => $revisequery[9],
		);
	}
	
	return $awardteam;
}


// get award data by player id
function get_player_award($pid){
	global $wpdb;
	$getaward = $wpdb->get_results("select * from wp_awards WHERE pid = '$pid'", ARRAY_N);
	
	foreach ($getaward as $key => $revisequery){
		$awardinfo[] = array(
			'awardid' => $revisequery[0], 
			'award' => $revisequery[1], 
			'year' => $revisequery[2], 
			'first' => $revisequery[3],  
			'last' => $revisequery[4],
			'team' => $revisequery[5],
			'position' => $revisequery[6],
			'owner' => $revisequery[7],
			'pid' => $revisequery[8],
			'gamepoints' => $revisequery[9]
		);
	}
	
	return $awardinfo;
}

// get award data by player id
function get_season_award($year){
	global $wpdb;
	$getawardsea = $wpdb->get_results("select * from wp_awards WHERE year = '$year'", ARRAY_N);
	
	foreach ($getawardsea as $key => $revisequery){
		$awardsea[] = array(
			'awardid' => $revisequery[0], 
			'award' => $revisequery[1], 
			'year' => $revisequery[2], 
			'first' => $revisequery[3],  
			'last' => $revisequery[4],
			'team' => $revisequery[5],
			'position' => $revisequery[6],
			'owner' => $revisequery[7],
			'pid' => $revisequery[8],
			'gamepoints' => $revisequery[9]
		);
	}
	
	return $awardsea;
}


// get hall of fame inductees only
function get_award_hall(){
	global $wpdb;
	$gethall = $wpdb->get_results("select * from wp_awards WHERE award = 'Hall of Fame Inductee'", ARRAY_N);
	foreach ($gethall as $hall){
		$hallids[$hall[2]] = $hall[8];
	}
	
	return $hallids;
}

// get hall of fame inductees only
function get_award_rookie(){
	global $wpdb;
	$getrook = $wpdb->get_results("select * from wp_awards WHERE award = 'Rookie of the Year'", ARRAY_N);
	foreach ($getrook as $val){
		$rookids[$val[8]] = $val[2];
	}
	return $rookids;
}

//get all ring of honor players
function get_ring_of_honor(){
global $wpdb;
	
	$gethonor = $wpdb->get_results("select * from wp_postmeta where meta_key like '%honored_player_%'", ARRAY_N);
	foreach ($gethonor as $c){
		$ring[] = $c[3];
	}
	$i = 0;
	foreach ($ring as $value){
		if( $i % 2 == 0){
			$ringsecond[] = $value;
		}
		$i++;
	}
	$j = 0;
	foreach ($ringsecond as $value){
		if( $j % 2 == 0){
			$team = $value;
		} else {
			$pid = $value;
		}
		$rhonor[$team.$j] = $pid;
		$j++;
	}
	$ringofhonor = array_unique($rhonor);
	
	return $ringofhonor;
}


// gets the weekly stats from the player table
function get_player_data($pid) {
	global $wpdb;
	$getplayer = $wpdb->get_results("select * from $pid", ARRAY_N);
    $storepts = 0;
    $storewins = 0;
	foreach ($getplayer as $key => $revisequery){
	    $cumpoints = $storepts+=$revisequery[3];
	    $cumwins = $storewins+=$revisequery[7];
		$playerstats[$revisequery[0]] = array( 
			'weekids' => $revisequery[0],
			'year' => $revisequery[1], 
			'week' => $revisequery[2], 
			'points' => $revisequery[3],  
			'team' => $revisequery[4],
			'versus' => $revisequery[5],
			'playerid' => $revisequery[6],
			'win_loss' => $revisequery[7],
			'home_away' => $revisequery[8],
			'location' => $revisequery[9],
            'cum_points' => $cumpoints,
            'cum_wins' => $cumwins
		);

	}
	
	return $playerstats;
	
}

// Player Data Transient
function allplayerdata_tables_trans() {
    $transient = get_transient( 'allplayerdata_table_trans' );
    if( empty( $transient ) ) {
        global $playersassoc;
        foreach ($playersassoc as $key => $value){
            $allplayerdata[$key] = get_player_data($key);
        }
        set_transient( 'allplayerdata_table_trans', $allplayerdata, 129600 );
        return $allplayerdata;
    } else {
        return $transient;
    }
}


// gets the weeks a player played from the player table 
function get_player_weeks_played($pid) {
	global $wpdb;
	$getplayer = $wpdb->get_results("select week_id from $pid", ARRAY_N);
	
	foreach ($getplayer as $key => $revisequery){
		$playerstats[] = $revisequery[0];
	}
	
	return $playerstats;
	
}

// gets the years a player played from the player table 
function get_player_years_played($pid) {
	global $wpdb;
	$getplayer = $wpdb->get_results("select year from $pid", ARRAY_N);
	
	foreach ($getplayer as $key => $revisequery){
		$player_the_years[] = $revisequery[0];
	}
	
	return array_unique($player_the_years);
	
}

// gets the weekly stats from the player table
function get_raw_player_data_team($pid, $team) {
	global $wpdb;
	$getplayer = $wpdb->get_results("select * from $pid where team = '$team'", ARRAY_N);
	
	foreach ($getplayer as $key => $revisequery){
		$playerstats[$revisequery[0]] = array( 
			'weekids' => $revisequery[0],
			'year' => $revisequery[1], 
			'week' => $revisequery[2], 
			'points' => $revisequery[3],  
			'team' => $revisequery[4],
			'versus' => $revisequery[5],
			'playerid' => $revisequery[6],
			'win_loss' => $revisequery[7],
			'home_away' => $revisequery[8],
			'location' => $revisequery[9]
		);
	}
	
	return $playerstats;
	
}


function get_player_basic_info($pid){
	global $wpdb;
	$getplayer = $wpdb->get_results("select * from wp_players where p_id = '$pid'", ARRAY_N);
	
	foreach ($getplayer as $key => $revisequery){
		$playerinfo[] = array( 
			'pid' => $revisequery[0],
			'first' => $revisequery[1], 
			'last' => $revisequery[2], 
			'position' => $revisequery[3],  
			'rookie' => $revisequery[4],
			'mflid' => $revisequery[5],
			'height' => $revisequery[6],
			'weight' => $revisequery[7],
			'college' => $revisequery[8],
			'number' => $revisequery[10],
			'pfruri' => $revisequery[11],
			'pfrcurl' => $revisequery[12],
			'nickname' => $revisequery[13]
		);
	}
	
	return $playerinfo;
	
}

// used to set transient to player data array anywhere
function set_allplayerdata_trans($pid) {
  global $randomplayer;
  $transient = get_transient( $pid.'_trans' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set[$pid] = get_player_data($pid);
    set_transient( $pid.'_trans', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

// set transient for team data
/*
function set_team_data_trans($teamid) {
  $transient = get_transient( $teamid.'_trans' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results($teamid);
    set_transient( $teamid.'_trans', $set, MONTH_IN_SECONDS );
    return $set;
  }
  
}
*/


// gets the stats for a player for a specific season
function get_player_season_stats($pid, $season){
	
	$data_array = get_player_data($pid);
	
	if(isset($data_array)){
		foreach ($data_array as $get){
			if ($get['year'] == $season){
				$justseason[$get['week']] = array(
					'season' => $season,
					'week' => $get['week'],
					'points' => $get['points'],
					'team' => $get['team'],
					'versus' => $get['versus']	
				);
			}
		}
	}
	
	//summary 
	if(isset($justseason)){
		foreach ($justseason as $get){
			$points[] = $get['points'];
			$storeteams[] = $get['team'];
		}
	
		$totalpoints = array_sum($points);
		$totalgames = count($points);
		$high = max($points);
	
	$value = 1;
	while ($value < 15){
		$newseason['week'.$value] = $justseason[$value];
		$value++;
	}
	
	$summary = array(
		'points' => $totalpoints,
		'games' => $totalgames,
		'ppg' => round(($totalpoints / $totalgames), 1),
		'high' => $high,
		'teams' => $storeteams
	);
	
	$merge = array_merge($newseason, $summary);
	}
	return $merge;
}


function playerplayoffs($pid){
	global $wpdb;
	$newgetplayer = $wpdb->get_results("select * from wp_playoffs WHERE playerid = '$pid'", ARRAY_N);
	
	foreach ($newgetplayer as $key => $revisequery){
		$playerplayoffs[$revisequery[0]] = array( 
			'id' => $revisequery[0], 
			'year' => $revisequery[1], 
			'week' => $revisequery[2],  
			'playerid' => $revisequery[3],
			'points' => $revisequery[4],
			'team' => $revisequery[5],
			'versus' => $revisequery[6],
			'overtime' => $revisequery[7],
			'result' => $revisequery[8]
		);
	}
	
	return $playerplayoffs;
	
}

// returns an array of all weeks played by that player weekid => points
function gettheweek ($pid){
	$data_array = get_player_data($pid);
	
	if(!empty($data_array )){
		foreach ($data_array as $get){
			$weeks[$get['weekids']] = $get['points'];
		}
	}
	return $weeks;
}


// returns an the points of a player on a given week.
function get_one_player_week ($pid, $weekid){
	$data_array = get_player_data($pid);
	
	if(!empty($data_array )){
		foreach ($data_array as $get){
			$week[$get['weekids']] = $get['points'];
		}
		return $week[$weekid];
	} else {
		return 'did not play';
	}
}



// gets consecutive game streak of player. returns a sum of games played.  They are allowed to miss one but once they miss two in a row the streak is broken.
function get_player_game_streak($pid){
	$theseasons = the_seasons();
	$thegames = the_games();
	$weeks = gettheweek ($pid);
	
 	$data_array = get_player_data($pid);
	
	// set all games from 1991 to present and add player points for that week if they played
	$value = 1;
	foreach($thegames as $key => $value){
		$newgame[$key] = $weeks[$key];
	}
	
	// single game limit
	$c = 0;
	$e = 0;
	foreach($newgame as $key => $value){
		if ($value != null ){
			$c++;
			$countstreak[] = 1;
		} else {
			$c = 0;
			$countstreak[] = 0;
		}
	}
	
	$onecount = 0;
	$maxones = 0;
	$prev = 0;
	$arraylength = count($countstreak);
	
	for ($x = 0; $x < $arraylength; $x++) {
		if($countstreak[$x] == 0){
			if($prev == 0){
				$onecount = 0;
			} else {
				$prev = 0;
			} 
		} else {
			$prev = 1;
			$onecount++;
			$maxones = ($onecount > $maxones) ? $onecount : $maxones;
		}
	}
	
	return $maxones;
	
}

function get_champions(){
	global $wpdb;
	
	$getchampionstable = $wpdb->get_results("select * from wp_champions", ARRAY_N);
	
	$buildchamps = array();
	foreach ($getchampionstable as $revisequery){
		$champions[$revisequery[0]] = array(
			'year' => $revisequery[0], 
			'numeral' => $revisequery[1], 
			'winner' => $revisequery[2], 
			'win_pts' => $revisequery[3],  
			'win_seed' => $revisequery[4],
			'loser' => $revisequery[5],
			'lose_pts' => $revisequery[6],
			'lose_seed' => $revisequery[7],
			'location' => $revisequery[8]
		);
	}
	
	
	return $champions;
}

function get_just_champions(){
	$possebowl = get_champions();
	foreach ($possebowl as $winners){
		$champs[$winners['year']] = $winners['winner'];
	}
	return $champs;
}


// gets basic team table 
function get_team_results($team){
	global $wpdb;
	
	$getteam = $wpdb->get_results("select * from $team", ARRAY_N);
	foreach ($getteam as $revisequery){
		$simpleteam[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'season' => $revisequery[1], 
			'week' => $revisequery[2], 
			'team_int' => $revisequery[3],  
			'points' => $revisequery[4],
			'versus' => $revisequery[5],
			'versus_pts' => $revisequery[6],
			'home_away' => $revisequery[7],
			'stadium' => $revisequery[8],
			'result' => $revisequery[9],
			'qb1' => $revisequery[10],
			'rb1' => $revisequery[11],
			'wr1' => $revisequery[12],
			'pk1' => $revisequery[13],
			'overtime' => $revisequery[14],
			'qb2' => $revisequery[15],
			'rb2' => $revisequery[16],
			'wr2' => $revisequery[17],
			'pk2' => $revisequery[18],
			'extra_ot' => $revisequery[19]
		);
	}
	
	return $simpleteam;
}

// combines all of the week results into one master array
function master_schedule(){
	$teamids = $_SESSION['teamids'];
	foreach($teamids as $key => $team){
		$masterschedule[$key] = get_team_results($key);
	}	
	return $masterschedule;
} 

// gets basic info about team
function get_teams(){
	global $wpdb;
	$getteams = $wpdb->get_results("select * from wp_teams", ARRAY_N);
	
	foreach ($getteams as $revisequery){
		$teams[$revisequery[4]] = array(
			'id' => $revisequery[0], 
			'team' => $revisequery[1], 
			'owner' => $revisequery[2], 
			'stadium' => $revisequery[3],  
			'int' => $revisequery[4],
			'mfl_team_id' => $revisequery[5],
			'first_season' => $revisequery[6], 
			'folded' => $revisequery[7] 
		);
	}
	
	return $teams;
	
}



// gets team stadium name 
function get_stadium($teamid){
	$teams = get_teams();
	foreach ($teams as $key => $val){
		$stadiums[$val['id']] = $val['stadium'];
	}
	
	return $stadiums;
}

// extends get_team_results to include record and location
function get_team_results_expanded($team){
	$teamresults = get_team_data_trans($team);
	$masterschedule = set_schedule_trans();
	$teaminfo = set_team_trans();
	
	if(!empty($teamresults)){
		foreach ($teamresults as $key => $value){
			
			$versuspts = $masterschedule[$value['versus']][$value['id']]['points'];
			if ($value['points'] > $versuspts){
				$result = 1;
			} else {
				$result = 0;
			}
			
			$versus = $value['versus'];
			$home = $value['home'];
			
			if ($home == 'H'){
				$stadium = $teaminfo[$team]['stadium'];
			} else {
				$stadium = $teaminfo[$versus]['stadium'];
			}
			
			$expanded[$value['id']] = array (
				'id' => $value['id'],
				'season' => $value['season'],	
				'week' => $value['week'],
				'points' => $value['points'],
				'versus' => $versus,
				'vspts' => $versuspts,
				'result' => $result,
				'venue' => $home,
				'location' => $stadium
			);
		}
	}
	return $expanded;
}

function team_record($team){
	$expanded = get_team_results_expanded($team);
	
	if (!empty($expanded)){
		foreach ($expanded as $key => $value){
			$data[$value['id']] = $value['result'];
		}
	}
	
	if(!empty($data)){
		$games = count($data);
		$wins = array_sum($data);
		$losses = $games - $wins;
		$winper = $wins / $games;
	}
	
	$results[$team] = array( 
	'games' => $games,
	'wins' => $wins,
	'losses' => $losses,
	'winper' => $winper,
	'data' => $data
	);
	
	return $results;
}

//used for QB, WR, RB
function pos_score_converter($year, $passyd, $passtd, $rushyd, $rushtd, $int, $recyd, $rectd){
	if($int < 0){ $int = 0; }
	if($passyd < 0){ $passyd = 0; }
	if($rushyd < 0){ $rushyd = 0; }
	if($recyd < 0){ $recyd = 0; }
	
	if($year == 1991):
		$pass_get = floor($passyd / 50);
		if($pass_get < 0){
			$passdata = 0;
		} else {
			$passdata = $pass_get;
		}
		
		$posscore = $passdata + (floor($rushyd / 25)) + (($passtd + $rushtd + $rectd) * 2) + floor($recyd / 25) - $int;
		return $qbscore;
	else:
		$posscore = (floor($passyd / 30)) + (floor($rushyd / 10)) + (($passtd + $rushtd + $rectd) * 2) + floor($recyd / 10) - $int ;
		return $posscore;
	endif;	
}

/*
function rb_wr_score_converter($year, $rushyd, $rushtd, $recyd, $rectd){
	if($year == 1991):
		$rwscore = floor($rushyd / 25)  + (($rushtd + $rectd) * 2) + floor($recyd / 25);
		return $rwscore;
	else:
		$rwscore = floor($rushyd / 10)  + (($rushtd + $rectd) * 2) + floor($recyd / 10);
		return $rwscore;
	endif;
}
*/

function pk_score_converter($year, $xpm, $fgm){
	$pkscore = $xpm + ($fgm * 2);
	return $pkscore;
}

// gets correctted score data for nfl tables including 2pt conversions, special teams tds, passed tds by non-qbs, and misc.
function get_score_correct_by_player($pid){
	global $wpdb;
	$getcorrect = $wpdb->get_results("select * from wp_score_correct where playerid = '$pid'", ARRAY_N);
	
	foreach ($getcorrect as $revisequery){
		$correct[$revisequery[1]] = array(
			//'id' => $revisequery[0], 
			//'weekid' => $revisequery[1], 
			'score' => $revisequery[3], 
			'type' => $revisequery[4]
		);
	}
	
	return $correct;
}

function countthepts($p){
	foreach ($p as $k => $v){
		$thecount[] = $v['points'];
	}
	return array_sum($thecount);
}
	
// gets pvq season multiplier values.  not actually used in the leaders by season pages to calculate pvq on that page.						
function getpvqmultipliers($year){
	$leadersyear = get_season_leaders($year);
	if (isset($leadersyear)){
	
		foreach ($leadersyear as $key => $value){
			$leaders[$value['position']][$key] = $value;
		}
		$qb_year = $leaders['QB'];
		$rb_year = $leaders['RB'];
		$wr_year = $leaders['WR'];
		$pk_year = $leaders['PK'];
	
		$sum_qb = countthepts($qb_year);
		$sum_rb = countthepts($rb_year);
		$sum_wr = countthepts($wr_year);
		$sum_pk = countthepts($pk_year);
		$total_pts = $sum_qb + $sum_rb + $sum_wr + $sum_pk;
		$max = max($sum_qb, $sum_rb, $sum_wr, $sum_pk);
		$qb_mult = $max / $sum_qb;
		$rb_mult = $max / $sum_rb;
		$wr_mult = $max / $sum_wr;
		$pk_mult = $max / $sum_pk;
		
		$multipliers = array(
			'Year' => $year,
			'QB' => $sum_qb,
			'RB' => $sum_rb,
			'WR' => $sum_wr,
			'PK' => $sum_pk,
			'Totals' => $total_pts,
			'Max' => $max,
			'QB_Mult' => round($qb_mult, 3),
			'RB_Mult' => round($rb_mult, 3),
			'WR_Mult' => round($wr_mult, 3),
			'PK_Mult' => round($pk_mult, 3)
			
		);
		
		return $multipliers;
	}
}

//gets season winner of PVQ score. 1.000
function get_season_pvq_leader(){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_player_pvqs where pvq = '1.00000000'", ARRAY_N);
	
	foreach ($get as $revisequery){
		$sealeadpvq[$revisequery[2]] = array(
			'id' => $revisequery[0], 
			'playerid' => $revisequery[1], 
			'year' => $revisequery[2], 
			'pvq' => $revisequery[3]
		);
	}
	
	return $sealeadpvq;
}



function printmultiplypvq(){
		$theyears = the_seasons();
		
		foreach ($theyears as $season){
		$test = getpvqmultipliers($season);
		if($test['QB'] != ''):
			$print_multiply[$season] = getpvqmultipliers($season);
		endif;
	}	
	return $print_multiply;
}



// gets the cumulative career stats from the player table
function get_player_career_stats($pid){
	
	$data_array = get_player_data($pid);
		
	if(!empty($data_array)){
		foreach ($data_array as $get){
			$pointsarray[] = $get['points'];
			$yeararray[] = $get['year'];
			$gamearray[] = $get['win_loss'];
			
		}
		
		foreach ($data_array as $key => $value){
			$sort_flat[$value['year']][] = $value['points'];
			$pointscum[$key] = $value['cum_points'];
            $winscum[$key] = $value['cum_wins'];

		}
		
		foreach ($sort_flat as $key => $value){
			$new_sort_flat[$key] = array_sum($value);
			$new_sort_flat_games[$key] = count($value);
			$new_sort_flat_ppg[$key] = array_sum($value) / count($value);
			$seasonrank[$key] = get_player_season_rank ($pid, $key);
            $pvqflat = get_player_pvqs($pid);
		}

		foreach($pointscum as $key => $value):
//            if($value >= 500 && $value <= 999):
//                $finalpointscum[$key] = 500;
//            endif;
            if($value >= 1000 && $value <= 1499):
                $finalpointscum[$key] = 1000;
            endif;
            if($value >= 1500 && $value <= 1999):
                $finalpointscum[$key] = 1500;
            endif;
            if($value >= 2000 && $value <= 2499):
                $finalpointscum[$key] = 2000;
            endif;
            if($value >= 2500 && $value <= 2999):
                $finalpointscum[$key] = 2500;
            endif;
            if($value > 3000):
                $finalpointscum[$key] = 3000;
            endif;
        endforeach;
        if($finalpointscum):
            $ptscum = array_unique($finalpointscum);
        endif;

        foreach($winscum as $key => $value):
            if($value >= 50 && $value <= 99):
                $finalwcum[$key] = 50;
            endif;
            if($value >= 100 && $value <= 149):
                $finalwcum[$key] = 100;
            endif;
            if($value >= 150 && $value <= 199):
                $finalwcum[$key] = 150;
            endif;
            if($value > 200):
                $finalwcum[$key] = 200;
            endif;
        endforeach;
        if($finalwcum):
            $wcum = array_unique($finalwcum);
        endif;
		
		$indyears = array_unique($yeararray);
		
		$points = array_sum($pointsarray);
		$games = count($data_array);
		$seasons = count($indyears);
		$ppg = round(($points / $games), 1);
		$high = max($pointsarray);
		$low = min($pointsarray);
		$wins = array_sum($gamearray);
		$loss = $games - $wins; 
		$highseapts = max($new_sort_flat);		
		$maxseason = array_keys($new_sort_flat, max($new_sort_flat));
		
		$justchamps = get_just_champions();
		$playoffsplayer = playerplayoffs($pid);
		
		if(!empty($playoffsplayer)){
			foreach($playoffsplayer as $key => $value){
				if ($value['week'] == '16'){
					$pb_apps[$value['year']] = $value['team'];	
				}
			}
		}
		// get Posse Bowl Wins 
		foreach ($justchamps as $key => $possebowls){
			if ($possebowls == $pb_apps[$key]){
				$pbwins[$key] = $possebowls;
				$pbwins_index[$key] = 1;
			}
		}
		$lastseason = end($indyears);
		$checkseason = date("Y") - $lastseason;
		if($checkseason <= 3){
			$activepl = 1;
		} else {
			$activepl = 0;
		}

		$carrer_stats = array(
			'pid' => $pid,
			'active' => $activepl,
			'games' => $games,
			'points' => $points,
			'ppg' => $ppg,
			'seasons' => $seasons,
			'high' => $high,
			'low' => $low,
			'wins' => $wins,
			'loss' => $loss,
			'highseasonpts' => $highseapts,
			'highseason' => $maxseason[0],
			'years' => $indyears,
			'yeartotal' => $new_sort_flat,
			'gamesbyseason' => $new_sort_flat_games,
			'ppgbyseason' => $new_sort_flat_ppg,
			'seasonrank' => $seasonrank,
			'pvqbyseason' => $pvqflat,
            'pointsmilestone' => $ptscum,
			'winmilestone' => $wcum,
			//'avgpvq' => array_sum($pvqflat)/count($pvqflat),
			'possebowlwins' => $pbwins_index,
            'careerposrank' => get_player_career_rank($pid)
		);
		
		return $carrer_stats;
	}
}



// gets the cumulative career stats from the player table for a single team only
function get_player_career_stats_team($pid, $team){
	
	$data_array = get_raw_player_data_team($pid, $team);
	
	if(!empty($data_array)){
		foreach ($data_array as $get){
			$pointsarray[] = $get['points'];
			$yeararray[] = $get['year'];
			$gamearray[] = $get['win_loss'];
	
		}
		
		
		$indyears = array_unique($yeararray);
		
		$points = array_sum($pointsarray);
		$games = count($data_array);
		$seasons = count($indyears);
		$ppg = round(($points / $games), 1);
		$high = max($pointsarray);
		$low = min($pointsarray);
		$wins = array_sum($gamearray);
		$loss = $games - $wins; 
		
		$carrer_stats = array(
			'pid' => $pid,
			'games' => $games,
			'points' => $points,
			'ppg' => $ppg,
			'seasons' => $seasons,
			'high' => $high,
			'low' => $low,
			'wins' => $wins,
			'loss' => $loss,
			'years' => $indyears
			
		);
		
		return $carrer_stats;
	}
}



// gets just the win=1 loss=0 for a particular team by week
function just_team_record($team, $week){
	$get = team_record($team);
	$data = $get[$team]['data'];
	$justweek = $data[$week];
	return $justweek;
}

// displays just the teams that the player played for by weekid => ETS
function get_player_record($pid){
	//$playerdata = allplayerdata_trans($pid);
	$playerdata = get_player_data($pid);
	if(!empty($playerdata)){
		foreach ($playerdata as $key => $value){
			$cleanteams[$key] = $value['team'];
		}
	}
	return $cleanteams;
}

//get player games by team count
function get_player_team_games($pid){
    $allgames = get_player_record($pid);
    if($allgames):
        $count = array_count_values($allgames );
    endif;
    return $count;
}


// combines player data tables and get_player_record to show if the player was on the winning or losing side that week
function get_player_results($pid){
	$playerrecord = get_player_record($pid);
	
	if(!empty($playerrecord)){
		foreach ($playerrecord as $key => $value){
			$schedule = get_team_results_expanded($value);
			$playerresults[$key] = array(
				'result' => $schedule[$key]['result'],
				'venue' => $schedule[$key]['venue']
			);
		}
	}
	return $playerresults;
}

// get probowl detials
function probowl_details(){
    global $wpdb;
    $getprobowl = $wpdb->get_results("select * from wp_probowl", ARRAY_N);

    foreach ($getprobowl as $value){
        $probowldetails[$value[1]] = array(
            'winner' => $value[2],
            'host' => $value[3],
            'egad' => $value[4],
            'dgas' => $value[5],
            'mvp' => $promvp[$value[1]]['pid']
        );
    }
    return $probowldetails;
}

// get probowl boxscore
function probowl_boxscores(){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_probowlbox", ARRAY_N);
	
	foreach ($get as $revisequery){
		$probowlbox[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'playerid' => $revisequery[1], 
			'position' => $revisequery[2], 
			'team' => $revisequery[3],  
			'league' => $revisequery[4],
			'year' => $revisequery[5],
			'points' => $revisequery[6],
			'starter' => $revisequery[7],
			'pointsused' => $revisequery[8]
		);
	}
	
	return $probowlbox;
}

// get team name and helmet history
function get_helmet_name_history(){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_helmet_history", ARRAY_N);
	
	foreach ($get as $revisequery){
		$helm_hist[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'team' => $revisequery[1], 
			'year' => $revisequery[2], 
			'name' => $revisequery[3],  
			'helmet' => $revisequery[4],
			'color1' => $revisequery[5],
			'color2' => $revisequery[6],
			'color3' => $revisequery[7]
		);
	}
	return $helm_hist;
}

function get_helmet_name_history_by_team($team, $year){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_helmet_history where team = '$team'", ARRAY_N);
	
	foreach ($get as $revisequery){
		$helm_hist_m[$revisequery[2]] = array(
			'team' => $revisequery[1], 
			'yearstart' => $revisequery[2],
			'name' => $revisequery[3],
			'helmet' => $revisequery[4],
            'jersey' => $revisequery[5],
			'color1' => $revisequery[6],
			'color2' => $revisequery[7],
			'color3' => $revisequery[8]
		);
	}
	
	$i = 1991;
	$thisyear = date('Y');
	
	while ($i <= $thisyear){
		if($helm_hist_m[$i]):
			$build_helm_hist_m[$i] = $helm_hist_m[$i];
			$store = $helm_hist_m[$i];
		else:
			$build_helm_hist_m[$i] = $store;	
		endif;
		$i++;
	}
	
	if($build_helm_hist_m[$year]):
		return $build_helm_hist_m[$year];
	else:
		echo 'Not Found';
	endif;
}


// get drafts
function get_drafts(){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_drafts", ARRAY_N);
	
	foreach ($get as $getdraft){
		$drafts[$getdraft[0]] = array(
			'key' => $getdraft[0], 
			'season' => $getdraft[1],
			'round' => $getdraft[2],	
			'pick' => $getdraft[3],	
			'overall' => $getdraft[4],
			'playerfirst' => $getdraft[7],
			'playerlast' => $getdraft[8],	
			'position' => $getdraft[9],
			'playerid' => $getdraft[10],	
			'orteam' => $getdraft[5],	
			'acteam' => $getdraft[6],	
			'tradeid' => $getdraft[11],
			'tradehappened' => $tradehappened
		);
	}
	
	return $drafts;
}

function get_draft_number_ones() {
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_drafts where round = '01' && picknum = '01'", ARRAY_N);
	
	foreach ($get as $getdraft){
		$draftones[$getdraft[0]] = array(
			'key' => $getdraft[0], 
			'season' => $getdraft[1],
			'round' => $getdraft[2],	
			'pick' => $getdraft[3],	
			'overall' => $getdraft[4],
			'playerfirst' => $getdraft[7],
			'playerlast' => $getdraft[8],	
			'position' => $getdraft[9],
			'playerid' => $getdraft[10],	
			'orteam' => $getdraft[5],	
			'acteam' => $getdraft[6],	
			'tradeid' => $getdraft[11],
			'tradehappened' => $tradehappened
		);
	}
	
	return $draftones;
}


function get_drafts_by_year($season){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_drafts where year = '$season'", ARRAY_N);
	
	foreach ($get as $getdraft){
		$draftsyear[$getdraft[0]] = array(
			'key' => $getdraft[0], 
			'round' => $getdraft[2],	
			'pick' => $getdraft[3],	
			'overall' => $getdraft[4],
			'playerfirst' => $getdraft[7],
			'playerlast' => $getdraft[8],	
			'position' => $getdraft[9],
			'playerid' => $getdraft[10],	
			'orteam' => $getdraft[5],	
			'acteam' => $getdraft[6],	
			'tradeid' => $getdraft[11],
			'tradehappened' => $tradehappened
		);
	}
	
	return $draftsyear;
}

function get_drafts_player($pid){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_drafts where playerid = '$pid'", ARRAY_N);
	
	foreach ($get as $getdraft){
		$drafts[$getdraft[0]] = array(
			'key' => $getdraft[0], 
			'season' => $getdraft[1],
			'round' => $getdraft[2],	
			'pick' => $getdraft[3],	
			'overall' => $getdraft[4],
			'playerfirst' => $getdraft[7],
			'playerlast' => $getdraft[8],	
			'position' => $getdraft[9],
			'playerid' => $getdraft[10],	
			'orteam' => $getdraft[5],	
			'acteam' => $getdraft[6],	
			'tradeid' => $getdraft[11],
			'tradehappened' => $tradehappened
		);
	}
	
	return $drafts;
}


function get_drafts_player_first_instance($pid){
	$draftfirst = get_drafts_player($pid);	
	if($draftfirst):
		return reset($draftfirst);
	endif;
}

// get probowl by player 
function probowl_boxscores_player($pid){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_probowlbox where playerid = '$pid'", ARRAY_N);
	
	foreach ($get as $revisequery){
		$probowlbox[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'playerid' => $revisequery[1], 
			'position' => $revisequery[2], 
			'team' => $revisequery[3],  
			'league' => $revisequery[4],
			'year' => $revisequery[5],
			'points' => $revisequery[6],
			'starter' => $revisequery[7],
			'pointsused' => $revisequery[8]
		);
	}
	
	return $probowlbox;
	
}

// get probowl by player 
function probowl_teams_player($teamid){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_probowlbox where team = '$teamid'", ARRAY_N);
	
	foreach ($get as $revisequery){
		$probowlbox[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'playerid' => $revisequery[1], 
			'position' => $revisequery[2], 
			'team' => $revisequery[3],  
			'league' => $revisequery[4],
			'year' => $revisequery[5],
			'points' => $revisequery[6],
			'starter' => $revisequery[7],
			'pointsused' => $revisequery[8]
		);
	}
	
	return $probowlbox;
	
}

// get probowl by year 
function probowl_boxscores_player_year($year){
	global $wpdb;
	$get = $wpdb->get_results("select * from wp_probowlbox where year = '$year'", ARRAY_N);
	
	foreach ($get as $revisequery){
		$probowlboxyear[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'playerid' => $revisequery[1], 
			'position' => $revisequery[2], 
			'team' => $revisequery[3],  
			'league' => $revisequery[4],
			'year' => $revisequery[5],
			'points' => $revisequery[6],
			'starter' => $revisequery[7],
			'pointsused' => $revisequery[8]
		);
	}
	
	return $probowlboxyear;
	
}



function get_player_teams_season($pid){
	$player = get_player_career_stats($pid);
	$years = $player['years'];
	
	if(!empty($years)){
		foreach ($years as $year){
			$get = get_player_season_stats($pid, $year);
			$playeryears[$year] = array_unique($get['teams']);
		}
	}
	return $playeryears;
}


// inserts data into wp_allleaders table to store for use in leaders and other places
// Data is grouped by CAREER
function insert_wp_career_leaders($pid){
	$player = get_player_career_stats($pid);
	$streak = get_player_game_streak($pid);	
			if (!empty($streak)){
				$clean = array(
					'pid' => $player['pid'],
					'games' => $player['games'],
					'points' => $player['points'],
					'ppg' => $player['ppg'],
					'seasons' => $player['seasons'],
					'high' => $player['high'],
					'low' => $player['low'],
					'firstyear' => $player['years'][0],
					'lastyear' => end($player['years']),
					'gamestreak' => $streak,
					'position' => substr($player['pid'], -2)
					
				);
			}
			
	global $wpdb;
	
	//remove the row for the player if it exsists
	$delete = $wpdb->query("delete from wp_allleaders where pid = '$pid'");
	
	$inserted = $wpdb->insert(
		 'wp_allleaders',
	     array(
	        'pid' => $clean['pid'],
	        'points' => $clean['points'],
	        'games' => $clean['games'],
			'seasons' => $clean['seasons'],
			'high' => $clean['high'],
			'low' => $clean['low'],
			'firstyear' => $clean['firstyear'],
			'lastyear' => $clean['lastyear'],
			'gamestreak' => $clean['gamestreak'],
			'position' => $clean['position']
	     ),
		 array( 
			'%s','%d','%d','%d','%d','%d','%d','%d','%d','%s'
		 )
		);
		
	 return $inserted;
 
}

// insert projected player season scores into table for preseason trends
function insert_wp_projections($array){
    global $wpdb;

    $inserted = $wpdb->insert(
        'wp_draftplan_projections',
        array(
            'id' => '',
            'name' => $array['name'],
            'date' => date('Y-m-d'),
            'projection' => $array['est_pfl_season_score']
        ),
        array(
            '%d','%s','%d','%d'
        )
    );

    return $inserted;

}


// get player career stats for one season only inserts when page is loaded (homepage, playerpage)

function insert_wp_season_leaders($pid){
	$player = get_player_career_stats($pid);
	$years = $player['years'];
	
	if(!empty($years)){
		
		foreach ($years as $key => $years){
			$get[$years] = get_player_season_stats($pid, $years);
		}
	
	
		foreach ($get as $k => $v){
			$justseason[$k] = array(
				'points' => $v['points'],
				'games' => $v['games']
				);
		}
		
		foreach ($justseason as $key => $value){
			$clean[$key] = array(
				'id' => $pid.$key,
				'playerid' => $pid,
				'season' => (int)$key,
				'points' => (int)$value['points'],
				'games' => (int)$value['games']	
			);
		}

		global $wpdb;
			
	 	foreach ($clean as $key => $value){
		 	
		 	$delid = $value['id'];
		 	$delete = $wpdb->query("delete from wp_season_leaders where id = '$delid'");
		 	
		 	$testarray = $clean[$key];
		 	
			$insertyears = $wpdb->insert(
				'wp_season_leaders',
			    array(
			        'id' => $testarray['id'],
					'playerid' => $testarray['playerid'],
					'season' => $testarray['season'],
					'points' => $testarray['points'],
					'games' => $testarray['games']
			    ),
				array( 
					'%s','%s','%d','%d','%d'
				)
			);
		
		}
	
	}

	return $insertyears;
	
}


// returns wp_allleaders data from table 

function get_allleaders(){
	
	global $wpdb;
	$get_all_leader = $wpdb->get_results("select * from wp_allleaders", ARRAY_N);
	
	foreach ($get_all_leader as $revisequery){
		$leaders_all[$revisequery[0]] = array(
			'pid' => $revisequery[0],
	        'points' => $revisequery[1],
	        'games' => $revisequery[2],
			'seasons' => $revisequery[3],
			'high' => $revisequery[4],
			'low' => $revisequery[5],
			'firstyear' => $revisequery[6],
			'lastyear' => $revisequery[7],
			'streak' => $revisequery[8],
			'position' => $revisequery[9]
		);
	}

return $leaders_all;

}

function get_player_career_rank($pid){
	$pos = substr($pid, -2);
	global $wpdb;
	$player_leader = $wpdb->get_results("SELECT * FROM wp_allleaders where position = '$pos'", ARRAY_N);
	foreach ($player_leader as $revisequery){
		$leader[$revisequery[0]] = $revisequery[1];
		}
	arsort($leader);
	$i = 1;
	foreach($leader as $key => $value){
		if($key == $pid ){
			return $i;
		}
		$i++;
	}
	
}


function teamlist(){
	$teamlist = array(
		'RBS' => 'Red Barons',
		'ETS' => 'Euro-Trashers',
		'PEP' => 'Peppers',
		'WRZ' => 'Space Warriorz',
		'CMN' => 'C-Men',
		'BUL' => 'Raging Bulls',
		'SNR' => 'Sixty Niners',
		'TSG' => 'Tsongas',
		'BST' => 'Booty Bustas',
		'MAX' => 'Mad Max',
		'PHR' => 'Paraphernalia',
		'SON' => 'Rising Son',
		'ATK' => 'Melmac Attack',
		'HAT' => 'Jimmys Hats',
		'DST' => 'Destruction'	
	);
	return $teamlist;
}


function get_number_ones(){
	
	global $wpdb;
	$get_number_ones = $wpdb->get_results("select * from wp_number_ones", ARRAY_N);
	
	foreach ($get_number_ones as $revisequery){
		$number_ones[$revisequery[0]] = array(
			'id' => $revisequery[0],
			'year' => $revisequery[1],
	        'playerid' => $revisequery[2],
	        'points' => $revisequery[3],
	        'teams' => $revisequery[4],
	        'pos' => $revisequery[5],
	        'avg' => $revisequery[6]
		);
	}

return $number_ones;

}

// get all grandslams.  slams are save on the weekly results pages to wp_grandslams
function get_grandslams(){
	
	global $wpdb;
	$get_slams = $wpdb->get_results("select * from wp_grandslams", ARRAY_N);
	
	foreach ($get_slams as $revisequery){
		$slams[$revisequery[0]] = array(
			'weekid' => $revisequery[1],
	        'teamid' => $revisequery[2]
		);
	}

	return $slams;

}

function url_exists($url) {
    $hdrs = @get_headers($url);

    echo @$hdrs[1]."\n";

    return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false;
}


function career_draft_value($pid, $teamid){
	//return $pid;
	$career = get_player_career_stats_team($pid, $teamid);
	return array('points' => $career['points'], 'seasons' => $career['seasons'] );
}

// print season draft table
function getdraft($year, $array){
		
$players = get_players_assoc ();	
$teaminfo = get_teams();

$printit .= '<div class="panel panel-dark">';
$printit .= '<div class="panel-heading">';
		$printit .= '<h3 class="panel-title">'.$year.' Draft</h3>';
	$printit .= '</div>';
	$printit .= '<div class="panel-body">';
		$printit .= '<table class="table table-hover table-vcenter">';
			$printit .= '<thead>';
				$printit .= '<tr>';
					$printit .= '<th class="min-width"><span class="hidden-xs">Selection</span></th>';
					$printit .= '<th class="min-width">Team</th>';
					$printit .= '<th class="min-width">Orig Team</th>';
					$printit .= '<th class="min-width hidden-xs"></th>';
					$printit .= '<th>Name</th>';
					$printit .= '<th class="min-width">Pos</th>';
					$printit .= '<th class="min-width">Season PTS</th>';
					$printit .= '<th class="min-width">Career PTS</th>';
				$printit .= '</tr>';
		$printit .= '</thead>';
		$printit .= '<tbody>';

				foreach ($array as $build){
							
					$picknumber = ltrim($build['pick'], '0');
					$selectingteam = $teaminfo[$build['acteam']];
					
					$selteam_sm = $build['acteam'];
					$origteam_sm = $build['orteam'];
					if ($select_team == $origteam_sm){
						$stary = '*';
					} else {
						$stary = '';
					}

					$pid = $build['playerid'];
					$round = $build['round'];	
					
					$first = $build['playerfirst'];
					$last = $build['playerlast'];
					$containsLetter  = preg_match('/[a-zA-Z]/', $pid);
					$idlength = strlen($pid);
					

					$playerimgobj = get_attachment_url_by_slug($pid);
					$imgid =  attachment_url_to_postid( $playerimgobj );
					$image_attributes = wp_get_attachment_image_src($imgid, array( 100, 100 ));	
					$playerimg = $image_attributes[0];
					
					//$playerimg = '/wp-content/themes/tif-child-bootstrap/img/players/'.$pid.'.jpg';
					$pflmini = '/wp-content/themes/tif-child-bootstrap/img/pfl-mini-dark.jpg';
					$position = $build['position'];
					
					//career value of the pick
					$careerdraftvalue = career_draft_value($pid, $selectingteam['int']);
					
					if ($activenum > $picknumber){
						$printit .= '<tr class="text-center bg-dark text-2x"><td colspan="8">Round '.$round.'</td></tr>';
					}
					
					$printit .= '<tr>';
						$printit .= '<td class="text-center min-width text-2x hidden-xs">'.$picknumber.'</td>';
						$printit .= '<td class="text-center min-width visible-xs">'.$picknumber.'</td>'; // either this one or the one above for non phone devices
						$printit .= '<td class="min-width hidden-xs">'.$selectingteam['int'].'</td>';
						if ($selectingteam['int'] != $origteam_sm){
							$printit .= '<td class="min-width hidden-xs">'.$origteam_sm.'</td>';
						} else {
							$printit .= '<td class="min-width hidden-xs">&nbsp;</td>';
						}
						//$printit .= '<td class="min-width visible-xs">'.$selteam_sm.'</td>'; // either this one or the one above for non phone devices
						
						
						if ($pid){		
							$printit .= '<td class="min-width hidden-xs"><div class="draft-img-sm"><img src="'.$playerimg.'" class="player-image" style="background-color:#515151;"/></div></td>';
						} else {
							$printit .= '<td class="min-width hidden-xs"><div class="draft-img-sm"><img src="'.$pflmini.'" class="player-image"/></div></td>';
						}
						
						if ($pid){
							$printit .= '<td class="text-bold"><a href="/player/?id='.$pid.'" class="player-link">'.$first.' '.$last.'</a></td>';
						} else {
							$printit .= '<td class="text-bold">'.$first.' '.$last.'</td>';
						}
						if($pid):
							$getstats = get_player_season_stats($pid, $year);
							$seasonpoints = $getstats['points'];
						endif;	
						$printit .= '<td class="text-center"><span class="">'.$position.'</span></td>';
						$printit .= '<td class="text-center"><span class="">'.$seasonpoints.'</span></td>';
						$printit .= '<td class="text-center"><span class="">'.$careerdraftvalue['points'].'</span></td>';
						//printr($careerdraftvalue['seasons'], 0);
					$printit .= '</tr>';
					
					$activenum = $picknumber;
					//$printit .= $pid.'<br>';
				}
							
		$printit .= '</tbody>';
	$printit .= '</table>';
$printit .= '</div>';
$printit .= '</div>';

echo $printit;

}

// Points	
function get_season_leaders($yearval){
	
	global $wpdb;
	$get_season_leaders = $wpdb->get_results("select * from wp_season_leaders where season = '$yearval'", ARRAY_N);
	
	foreach ($get_season_leaders as $revisequery){
		$season_leaders_all[$revisequery[0]] = array(
			'id' => $revisequery[0],
			'playerid' => $revisequery[1],
			'season' => $revisequery[2],
			'points' => $revisequery[3],
			'games' => $revisequery[4],
			'position' => substr($revisequery[0], 8, 2)
		);
	}

return $season_leaders_all;

}


// GET MFL player id by passing PFL id and vise versa
function playerid_mfl_to_pfl(){
	global $wpdb;
	$query = $wpdb->get_results("SELECT p_id, mflid FROM wp_players" );
	
	foreach ($query as $val){
		if(!empty($val->mflid)){
			$theids[$val->mflid] = $val->p_id;
		}
	}
	arsort($theids);
	return $theids ;	
}

function playerid_pfl_to_mfl(){
	global $wpdb;
	$query = $wpdb->get_results("SELECT p_id, mflid FROM wp_players" );
	
	foreach ($query as $val){
		if(!empty($val->p_id)){
			$theids[$val->p_id] = $val->mflid;
		}
	}
	ksort($theids);
	return $theids ;	
}

// convert MFL team id to PFL team id
function teamid_mfl_to_name(){
	
	global $wpdb;
	$query = $wpdb->get_results("SELECT team_int, mfl_team_id FROM wp_teams" );
	
	foreach ($query as $val){
		if(!empty($val->mfl_team_id)){
			$theteams[$val->mfl_team_id] = $val->team_int;
		}
	}
	
	return $theteams ;
	
}

// function to return player data from MFL
// Year and possibly league ID value needs to be updated

function get_mfl_player_details($mflid){

	$lid = 38954;
	$year = date('Y');
	$curl = curl_init();

	curl_setopt_array($curl, array(
	
	  CURLOPT_URL => "https://api.myfantasyleague.com/$year/export?TYPE=playerProfile&SINCE=&PLAYERS=$mflid&JSON=1",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "Cache-Control: no-cache",
	    "Postman-Token: 12d23246-b5e0-73c0-4b72-2d5c0ea6d955"
	  ),
	));
	
	$mflplayerinfo = curl_exec($curl);
	
	$err = curl_error($curl);
	
	curl_close($curl);
	
	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  echo "curl Worked";
	}
	
	$mflguy = json_decode($mflplayerinfo, true);
	
	return $mflguy;
	
}

// function to return mfl player restults by week 
function get_weekly_mfl_player_results($mflid, $year, $week){
	
	$curl = curl_init();
    //  2020 Request Curl
//	curl_setopt_array($curl, array(
//	  CURLOPT_URL => "https://www58.myfantasyleague.com/$year/export?TYPE=playerScores&L=38954&APIKEY=aRNp1sySvuWvx0WmO1HIZDYeFbox&W=$week&YEAR=$year&PLAYERS=$mflid&JSON=1",
//	  CURLOPT_RETURNTRANSFER => true,
//	  CURLOPT_ENCODING => "",
//	  CURLOPT_MAXREDIRS => 10,
//	  CURLOPT_TIMEOUT => 0,
//	  CURLOPT_FOLLOWLOCATION => true,
//	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//	  CURLOPT_CUSTOMREQUEST => "GET",
//	  CURLOPT_HTTPHEADER => array(
//	    "Cookie: MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D; MFL_LAST_LEAGUE_ID=38954; MFL_PW_SEQ=ah9q2MiTtein3AK%2B"
//	  ),
//	));

    // 2021 Request Curl
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www58.myfantasyleague.com/$year/export?TYPE=playerScores&L=38954&APIKEY=aRNp1sySvuWux0CmO1HIZDYeF7ox&W=$week&YEAR=$year&PLAYERS=$mflid&JSON=1",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Cookie: utf-8; MFL_PW_SEQ=ah9q2MuSs%2BGq2gG6; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D'
        ),
    ));
	
	$mflplayerscore = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	$mflscores = json_decode($mflplayerscore, true);
	
	$playerscores = $mflscores['playerScores']['playerScore'];
	
	if(isset($playerscores)){
	    $score[$playerscores['week']] = $playerscores['score'];
	}
	sleep(3);
	return $score;
	
}

// pulls data from MFL API and inserts into wp_players as well as creating player table for weekly score data
function createnewplayer($array){
												
	global $wpdb;
	$arr = $array;
	
	$pid = $arr['p_id'];
	
	// insert info into wp_players
	$insertarr = $wpdb->insert(
		 'wp_players',
	     array(
	        'p_id' 			=> $arr['p_id'],
			'playerFirst' 	=> $arr['playerFirst'],
			'playerLast' 	=> $arr['playerLast'],
			'position' 		=> $arr['position'],
			'rookie' 		=> $arr['rookie'],
			'mflid' 		=> $arr['mflid'],	
			'height' 		=> $arr['height'],
			'weight' 		=> $arr['weight'],
			'college' 		=> $arr['college'],
			'birthdate' 	=> '',
			'number' 		=> $arr['number']
	     ),
		 array( 
			'%s','%s','%s','%s','%d','%s','%s','%d','%s','','%d' 
		 )
	);	

	// create new table
	$wpdb->query("CREATE TABLE $pid LIKE 1991SmitRB" );
			
	return $insertarr;
}

function get_player_week($playerid, $weekid){
    global $wpdb;

    $playerinfo = $wpdb->get_results("select * from wp_players", ARRAY_N);
    $playerdata = $wpdb->get_results("select * from $playerid", ARRAY_N);

    foreach ($playerinfo as $data){
        $plarray[$data[0]] = $data;
    }

    foreach ($playerdata as $data){
        $array[$data[0]] = $data;
    }
    $playerbyweek = array(
        'points' 	=>  $array[$weekid][3],
        'team'		=>	$array[$weekid][4],
        'first' 	=>  $plarray[$playerid][1],
        'last' 		=>  $plarray[$playerid][2],
        'position' 	=>  $plarray[$playerid][3]
    );
    return $playerbyweek;
}

function get_boxscore_cache($weekvar){
	$boxscorecache = 'http://pfl-data.local/wp-content/themes/tif-child-bootstrap/cache/boxscores/'.$weekvar.'box.txt';

		$boxscoreget = @file_get_contents($boxscorecache, FILE_USE_INCLUDE_PATH);
		$boxscoredata = @unserialize($boxscoreget);
		$boxscorecount = @count(array_keys($boxscoredata));
	
	if($boxscorecount > 0){
		foreach ($boxscoredata as $buildboxreg){
			if ($buildboxreg[4] == 0){
				$boxscoredata_reg[] = array($buildboxreg[0],$buildboxreg[1],$buildboxreg[2],$buildboxreg[3]);	
			}	
		}
	}
	
	return $boxscoredata_reg;
}


function put_boxscore_results($weekid){
	
	$boxscore = get_boxscore_cache($weekid);
	$masterschedule = master_schedule();
	
	// get schedule by week
	$pep_sched = $masterschedule['PEP'][$weekid];
	$ets_sched = $masterschedule['ETS'][$weekid];
	$wrz_sched = $masterschedule['WRZ'][$weekid];
	$cmn_sched = $masterschedule['CMN'][$weekid];
	$snr_sched = $masterschedule['SNR'][$weekid];
	$bul_sched = $masterschedule['BUL'][$weekid];
	$tsg_sched = $masterschedule['TSG'][$weekid];
	
	$rbs_sched = $masterschedule['RBS'][$weekid];
	$bst_sched = $masterschedule['BST'][$weekid];
	$max_sched = $masterschedule['MAX'][$weekid];
	
	$son_sched = $masterschedule['SON'][$weekid];
	$hat_sched = $masterschedule['HAT'][$weekid];
	$phr_sched = $masterschedule['PHR'][$weekid];
	$atk_sched = $masterschedule['ATK'][$weekid];
	$dst_sched = $masterschedule['DST'][$weekid];
	
	$weeksched_build = array(
		'PEP' => $pep_sched,	
		'ETS' => $ets_sched,
		'WRZ' => $wrz_sched,
		'CMN' => $cmn_sched,
		'SNR' => $snr_sched,
		'BUL' => $bul_sched,
		'TSG' => $tsg_sched,
		'RBS' => $rbs_sched,	
		'BST' => $bst_sched,
		'MAX' => $max_sched,	
		'SON' => $son_sched,
		'HAT' => $hat_sched,
		'PHR' => $phr_sched,
		'ATK' => $atk_sched,
		'DST' => $dst_sched
	);
	
	foreach ($weeksched_build as $key => $value){
		if($value['home'] == 'H'){
			$arraynew[$key] = array(
				$weekid.$key,
				$key,
				$value['points'],
				$value['versus'],
				$weeksched_build[$value['versus']]['points']
				
			);
		}
	}
	
/*
	$hometeam = 'ETS';
	$roadteam = 'PEP';
	
	
	$value = array(
		$theid,
		$hometeam,
		22,
		$roadteam,
		15,
		'1991KellQB',
		'1991KellQB',
		'1991KellQB',
		'1991KellQB',
		'',
		'',
		'',
		'',
		'1991KellQB',
		'1991KellQB',
		'1991KellQB',
		'1991KellQB',
		'',
		'',
		'',
		'',
		''
	);
	
*/
	
	global $wpdb;
	
	 	 	
		$insertresults = $wpdb->insert(
			'wp_weekly_results',
		    array(
		        'weekid' => $arraynew[0],
				'hometeam' => $arraynew[1],
				'homescore' => $arraynew[2],
				'roadteam' => $arraynew[3],
				'roadscore' => $arraynew[4],
				'homeqb1' => $arraynew[5],
				'homerb1' => $arraynew[6],
				'homewr1' => $arraynew[7],
				'homepk1' => $arraynew[8],
				'homeqb2' => $arraynew[9],
				'homerb2' => $arraynew[10],
				'homewr2' => $arraynew[11],
				'homepk2' => $arraynew[12],
				'roadqb1' => $arraynew[13],
				'roadrb1' => $arraynew[14],
				'roadwr1' => $arraynew[15],
				'roadpk1' => $arraynew[16],
				'roadqb2' => $arraynew[17],
				'roadrb2' => $arraynew[18],
				'roadwr2' => $arraynew[19],
				'roadpk2' => $arraynew[20],
				'overtime' => $arraynew[21]
		    ),
			array( 
				'%s','%s','%d','%s','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
			)
		);
	

	return $boxscore;
	
}


function get_player_name($playerid){
	$values = get_players_assoc();
	$first = $values[$playerid][0];
	$last = $values[$playerid][1];
	$pos = $values[$playerid][2];
	$name = array('first' => $first, 'last' => $last, 'pos' => $pos);
	return $name;
}

function pid_to_name($pid, $init){
    $info = get_player_name($pid);
    if($init == 1):
        $t = substr($info['first'], 0, 1).'. '.$info['last'];
    else:
        $t = $info['first'].' '.$info['last'];
    endif;
    return $t;
}

// results for team by week
function get_team_results_by_week($team, $weekid){
	global $wpdb;
	
	$getteam = $wpdb->get_results("select * from wp_team_$team where id = '$weekid'", ARRAY_N);
	foreach ($getteam as $revisequery){
		$byweekteam[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'season' => $revisequery[1], 
			'week' => $revisequery[2], 
			'team_int' => $revisequery[3],  
			'points' => $revisequery[4],
			'versus' => $revisequery[5],
			'versus_pts' => $revisequery[6],
			'home_away' => $revisequery[7],
			'stadium' => $revisequery[8],
			'result' => $revisequery[9],
			'qb1' => $revisequery[10],
			'rb1' => $revisequery[11],
			'wr1' => $revisequery[12],
			'pk1' => $revisequery[13],
			'overtime' => $revisequery[14],
			'qb2' => $revisequery[15],
			'rb2' => $revisequery[16],
			'wr2' => $revisequery[17],
			'pk2' => $revisequery[18],
			'extra_ot' => $revisequery[19]
		);
	}
	
	return $byweekteam;
}

function get_player_score_by_week($pid, $weekid){
    global $wpdb;
    $query = $wpdb->get_results("select * from $pid where week_id = '$weekid'", ARRAY_N);
    $clean = array(
        'pid' => $query[0][6],
        'pos' => strtoupper(substr($query[0][6], -2)),
        'weekid' => $query[0][0],
        'year' => $query[0][1],
        'week' => $query[0][2],
        'points' => $query[0][3],
        'team' => $query[0][4],
        'vs' => $query[0][5],
        'result' => $query[0][7],
        'location' => $query[0][8],
        'venue' => $query[0][9],
    );
    return $clean;
}

// boxscore information by week
function get_boxscore_by_week($weekid){
    global $wpdb;
    $teams = get_teams();

    foreach ($teams as $key => $value){
        $revisequery = $wpdb->get_results("select * from wp_team_$key where id = '$weekid'", ARRAY_N);
        if($revisequery):
            $ot = $revisequery[0][14];
            $boxscoreweek[$key] = array(
                'id' => $revisequery[0][0],
                'season' => $revisequery[0][1],
                'week' => $revisequery[0][2],
                'team_int' => $revisequery[0][3],
                'points' => $revisequery[0][4],
                'versus' => $revisequery[0][5],
                'versus_pts' => $revisequery[0][6],
                'home_away' => $revisequery[0][7],
                'stadium' => $revisequery[0][8],
                'result' => $revisequery[0][9],
                'qb1' => get_player_score_by_week($revisequery[0][10], $revisequery[0][0]),
                'rb1' => get_player_score_by_week($revisequery[0][11], $revisequery[0][0]),
                'wr1' => get_player_score_by_week($revisequery[0][12], $revisequery[0][0]),
                'pk1' => get_player_score_by_week($revisequery[0][13], $revisequery[0][0]),
                'overtime' => $revisequery[0][14],
                'qb2' => get_player_score_by_week($revisequery[0][15], $revisequery[0][0]),
                'rb2' => get_player_score_by_week($revisequery[0][16], $revisequery[0][0]),
                'wr2' => get_player_score_by_week($revisequery[0][17], $revisequery[0][0]),
                'pk2' => get_player_score_by_week($revisequery[0][18], $revisequery[0][0]),
                'extra_ot' => $revisequery[0][19]
            );
        endif;
    }

    return $boxscoreweek;
}

// results for all teams by week
function get_all_team_results_by_week($weekid, $team){
	global $wpdb;

		$getteam = $wpdb->get_results("select * from wp_team_$team where id = '$weekid'", ARRAY_N);
		foreach ($getteam as $revisequery){
		
			if($revisequery[9] > 0){
				$wl = 1;
			} else {
				$wl = 0;
			}
			$theweek = $revisequery[2];
			$sum += $revisequery[4];
			
			
			
			$weekteams = array(
				'season' => $revisequery[1], 
				'week' => $theweek, 
				'team' => $team,
				'points' => $sum,
				'result' => $revisequery[9],
				'victory' => $wl
			);
			
			
			
		}
	
	
	return $weekteams;
}


// results for team by week
function get_team_results_expanded_new($team){
	global $wpdb;
	
	$getteam = $wpdb->get_results("select * from wp_team_$team", ARRAY_N);
	foreach ($getteam as $revisequery){
		$teamresults[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'season' => $revisequery[1], 
			'week' => $revisequery[2], 
			'team_int' => $revisequery[3],  
			'points' => $revisequery[4],
			'versus' => $revisequery[5],
			'versus_pts' => $revisequery[6],
			'home_away' => $revisequery[7],
			'stadium' => $revisequery[8],
			'result' => $revisequery[9],
			'totalscore' => $revisequery[4] + $revisequery[6],
			'qb1' => $revisequery[10],
			'rb1' => $revisequery[11],
			'wr1' => $revisequery[12],
			'pk1' => $revisequery[13],
			'overtime' => $revisequery[14],
			'qb2' => $revisequery[15],
			'rb2' => $revisequery[16],
			'wr2' => $revisequery[17],
			'pk2' => $revisequery[18],
			'extra_ot' => $revisequery[19]
		);
	}
	
	return $teamresults;
}


//format number as ordinal
function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}


// get the total number of regular season games played
function get_total_games_played(){
	$teams = get_teams();
	$seasons = the_seasons();
	
}

function get_team_points($team){
	global $wpdb;
	
	$getteam = $wpdb->get_results(" select * from wp_team_$team ", ARRAY_N);
	foreach ($getteam as $revisequery){
		$teampoints[$revisequery[0]] = $revisequery[4];
	}
	
	return $teampoints;
}

function format_draft_pick($pickvar){
	if($pickvar != ''){
		$ex = explode('.', $pickvar);
		echo $ex[0].' '.ordinal($ex[1]).' Round Pick ('.$ex[1].'.'.$ex[2].')<br/>';
	}
}

//get values from PVQ table and sort by player id

function get_allpvqs(){
	global $wpdb;
	
	$getpvq = $wpdb->get_results(" select * from wp_player_pvqs ", ARRAY_N);
	foreach ($getpvq as $c){
		$allpvq[$c[1]][$c[2]] = $c[3];
	}
	
	return $allpvq;
}

// get pvq data by player
function get_player_pvqs($pid){
global $wpdb;
	
	$getpvq = $wpdb->get_results(" select * from wp_player_pvqs where playerid = '$pid' ", ARRAY_N);
	foreach ($getpvq as $c){
		$thepvq[$c[2]] = $c[3];
	}
	
	return $thepvq;
}

//get values from PVQ table and sort by year

function get_allpvqs_year(){
	global $wpdb;
	
	$getpvq = $wpdb->get_results(" select * from wp_player_pvqs ", ARRAY_N);
	foreach ($getpvq as $c){
		$allpvq[$c[1]] = $c[3];
	}
	
	foreach ($allpvq as $key => $d){
		$pos = substr($key, -2);
		$grouppvq[$pos][] = $d;
	}
	
	foreach ($grouppvq as $key => $e){
		$countthem[$key] = count($e);
		$thepvq[$key] = array_sum($e);
	}
	
	foreach ($thepvq as $key => $f){
		$pvq[$key] = $f / $countthem[$key];
	}
	
	return $pvq;
}

// get team names by season.
function get_all_teams_by_season(){ 
	$thestandings = get_all_standings();
	foreach ($thestandings as $key => $value){
		if(is_array($value)){	
			$i = 0;
			foreach ($value as $k => $v){
				$standbyyear[$key][$v['teamid']] = $v['division'];
				$i++;
			}
			$standbyyear[$key]['count'] = $i;
			$standbyyear[$key]['games'] = ($i / 2) * 14;
		}
	}
	return $standbyyear;
}

// get player of the week data
function get_player_of_week(){
global $wpdb;	
	$getpotw = $wpdb->get_results(" select * from wp_player_of_week", ARRAY_N);
	foreach ($getpotw as $p){
		$potw[$p[0]] = $p[1];
	}
	return $potw;
}


// get player of the week data by player
function get_player_of_week_player($pid){
global $wpdb;	
	$getpotw = $wpdb->get_results(" select * from wp_player_of_week where playerid = '$pid' ", ARRAY_N);
	foreach ($getpotw as $p){
		$potwp[] = $p[0];
	}
	return $potwp;
}

// gets all players games played by position
function get_all_players_games_played(){

	$transient = get_transient( 'games_played_by_pos' );
	  if( ! empty( $transient ) ) {
	    return $transient;
	  } else {
	   	$playerids = just_player_ids();
		foreach ($playerids as $id):
			$getgames = get_player_career_stats($id);
			$pos = substr($id, -2);
			$justgames[$pos][$id] = $getgames['games'];
		endforeach;
				
		$game_output = array(
			'QB' => array(
				'sum' => array_sum($justgames['QB']),
				'count' => count($justgames['QB']),
				'avg' => array_sum($justgames['QB']) / count($justgames['QB']),
				'season' => (array_sum($justgames['QB']) / count($justgames['QB'])) / 13
			),
			'RB' => array(
				'sum' => array_sum($justgames['RB']),
				'count' => count($justgames['RB']),
				'avg' => array_sum($justgames['RB']) / count($justgames['RB']),
				'season' => (array_sum($justgames['RB']) / count($justgames['RB'])) / 13,
			),
			'WR' => array(
				'sum' => array_sum($justgames['WR']),
				'count' => count($justgames['WR']),
				'avg' => array_sum($justgames['WR']) / count($justgames['WR']),
				'season' => (array_sum($justgames['WR']) / count($justgames['WR'])) / 13,
			),
			'PK' => array(
				'sum' => array_sum($justgames['PK']),
				'count' => count($justgames['PK']),
				'avg' => array_sum($justgames['PK']) / count($justgames['PK']),
				'season' => (array_sum($justgames['PK']) / count($justgames['PK'])) / 13
			)
		);
	    set_transient( 'games_played_by_pos', $game_output, DAY_IN_SECONDS );
	    return $game_output;
	  }

}



// Adds ACF Options Page to the backend
if (function_exists('acf_add_options_page')) {
	acf_add_options_page();
}
// Changes ACF Options page title on backend menu
if (function_exists('acf_set_options_page_title')) {
	acf_set_options_page_title(__('Theme Options'));
}
// Changes ACF Options page backend menu order
function custom_menu_order( $menu_ord ) {
	if (!$menu_ord) return true;
	$menu = 'acf-options';
	$menu_ord = array_diff($menu_ord, array($menu));
	array_splice($menu_ord, 1, 0, array($menu));
	return $menu_ord;
}
add_filter('custom_menu_order', 'custom_menu_order');
add_filter('menu_order', 'custom_menu_order');



//sportsreference api data functions from compiled datatables
function get_sportsref_shedule(){
global $wpdb;
	
	$get = $wpdb->get_results(" select * from wp_sdapi_schedule_all ", ARRAY_N);
	foreach ($get as $g){
		$sdschedule[] = array(
			'id' => $g[0], 
			'schedule' => $g[1], 
			'pflweekid' => $g[2], 
			'year' => $g[3],  
			'week' => $g[4],
			'team' => $g[5],
			'oppo' => $g[6],
			'teamfull' => $g[7],
			'date' => $g[8]
			);
	}
	
	return $sdschedule;
}

function get_sportsref_shedule_just_ids(){
global $wpdb;
	
	$get = $wpdb->get_results(" select * from wp_sdapi_schedule_all ", ARRAY_N);
	foreach ($get as $g){
		$year = $g[3];
		$week = $g[4];
		$padded = sprintf("%02d", $week);
		
		if($week <= 14){
			#sportsrefernce boxscore in key and pfl week id in value
			$ched[$g[1]] = $year.$padded;
		}
	}
	
	return $ched;
}

// needs the function above to retrieve the PROFOOTBALL REFERENCE IDS before you loop through to add pfl week ids to the table.  Runt the python script 'get_schedule_by_year.py' first then use the function below to attach pfl ids to the proper schedule weeks.
function insert_pfl_week_id($id, $pfl){
	global $wpdb;
	$pfdsched = $id;
		$wpdb->update(
	    'wp_sdapi_schedule_all', 
	    array( 
	        'pflweekid' => $pfl,  // string
	    ), 
	    array( 
	    	'schedule' => $id
	    )
	);
	echo $id.'-'.$pfl.'<br>';
}


function insert_pfrcurl($p, $l){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $l);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt($ch, CURLINFO_HTTP_CODE, true);
	curl_exec($ch);
	$curlvalue = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	
	global $wpdb;

		$wpdb->update( 
	    'wp_players', 
	    array( 
	        'pfrcurl' => $curlvalue,  // string
	    ), 
	    array( 
	    	'p_id' => $p
	    )
	);

}

//check if curl for sportsreference returned a success response to the database
function get_check_for_pfr_success($playerid){
	global $wpdb;
	
	$get = $wpdb->get_results("select * from wp_players where p_id = '$playerid'", ARRAY_N);
	$store = $get[0][12];
	return $store;
}

#calculate an estimated pfl score based on data provided in profootballreference
function estimated_pfl_score($py, $ptd, $pint, $ruyd, $rutd, $reyd, $retd, $fg, $ep, $tpc, $o){
	$pvar = 30;
	$rvar = 10;

	if($py):
	    $pass = floor($py / $pvar);
	endif;
	if($ruyd):
	    $rush = floor($ruyd / $rvar);
	endif;
	if($reyd):
	    $rec = floor($reyd / $rvar);
	endif;
	if( $ptd || $rutd || $retd):
	    $tds = ($ptd + $rutd + $retd) * 2;
	endif;
	if($ep):
	    $kicks = ($fg * 2) + $ep;
	endif;
	
 	$thecalc = ($pass + $rush + $rec + $tds + $kicks + $tpc + $o) - $pint;
 	$calculated = array(
 		'pass' => $pass , 
 		'rush' => $rush , 
 		'rec' => $rec , 
 		'tds' => $tds , 
 		'kicks' => $kicks , 
 		'twopt' => $tpc , 
 		'other' => $o , 
 		'int' => $pint
 	);
	
	return $thecalc;
}

#get linescores from wp_sdapi_boxscores by player
function get_pfr_linescores_by_player($playerid){
	global $wpdb;
	
	$get = $wpdb->get_results("select * from wp_sdapi_boxscores where pflplayerid = '$playerid'", ARRAY_N);
	foreach ($get as $g){
		$calc = estimated_pfl_score($g[5], $g[6], $g[7], $g[8], $g[9], $g[10], $g[11], $g[12], $g[13], '', '');
		$playerlines[$g[2]] = array(
			'id' 			=> $g[0], 
			'refgameid' 	=> $g[1], 
			'pflgameid' 	=> $g[2], 
			'refplayerid' 	=> $g[3],  
			'pflplayerid' 	=> $g[4],
			'passyards' 	=> $g[5],
			'passtds' 		=> $g[6],
			'passints'	 	=> $g[7],
			'rushyards' 	=> $g[8],
			'rushtds' 		=> $g[9],
			'recyards' 		=> $g[10],
			'rectds' 		=> $g[11],
			'fieldgoals'	=> $g[12],
			'extrapoints' 	=> $g[13],
			'twopointcon'	=> '',
			'notes'			=> '',
			'calculatedscore'	=> $calc
			);
		}
	
	return $playerlines;
}

function labeltheseaward($awardid){
	if ($awardid == 'mvp'){
		echo 'Most Valuable Player';
	}
	if ($awardid == 'pbm'){
		echo 'Posse Bowl MVP';
	}
	if ($awardid == 'pro'){
		echo 'Pro Bowl MVP';
	}
	if ($awardid == 'roty'){
		echo 'Rookie of the Year';
	}
}



# player supercard -- used for presenting the entire supercard
function supercard($pid){
	
	$playerstats = get_player_data($pid);
	$info = get_player_basic_info($pid);
	$firstname = $info[0]['first'];
	$lastname = $info[0]['last'];
	$position = $info[0]['position'];

	$getpotw = get_player_of_week();
	$thecount = array_count_values($getpotw);

	$plpotw = $totalpotw[$pid];
	if(is_array($sumpotw)){
		$sumpotw = array_sum($plpotw);
	}
	
	$career = get_player_career_stats($pid);
	$pbapps = array();
	$playerchamps = array();
	$printawards = array();
	
	$playerimgobj = get_attachment_url_by_slug($pid);
	$imgid =  attachment_url_to_postid( $playerimgobj );
	$image_attributes = wp_get_attachment_image_src($imgid, array( 100, 100 ));	
	$playerimg = $image_attributes[0];
	
	$justchamps = get_just_champions();
	$teams = get_teams();
	$teamslist = get_player_teams_season($pid);
						
	$get = playerplayoffs($pid);
	
	if(!empty($get)){
		foreach($get as $key => $value){
			if($value['week'] == 16){
				$pbapps[$value['year']] = $value['team'];
			}
		}
	}
	
	foreach ($pbapps as $key => $value){
		if ($value == $justchamps[$key]){
			$playerchamps[$key] = $value;
		}
	}

	$plawards = get_player_award($pid);
	
	if(!empty($plawards)){
		foreach ($plawards as $key => $value){
			if ($value['award'] != 'Hall of Fame Inductee'){
				$printawards[] = $value['awardid'];
			}
		}
	}
	
	$halloffame = get_award_hall();
	
	$number_ones = get_number_ones();
	
	$halloffame = get_award_hall();

	$gamesbyteam = get_player_team_games($pid);
	
		echo '<div class="col-xs-24 eq-box-sm">';
			echo '<div class="panel panel-bordered panel-dark the-supercard">';
				echo '<div class="panel-heading">';
					echo '<h3 class="panel-title">Posse Football League</h3>';
				echo '</div>';
				
				echo '<div class="panel-body">';
					echo '<span class="text-2x text-bold"><a href="/player?id='.$pid.'">'.$firstname.' '.$lastname.'</a></span>&nbsp;'.$position;
					if($info[0]['height'] != ''):
						echo '<p>'.$info[0]['height'].', '.$info[0]['weight'].' lbs. | '.$info[0]['college'].' | <span class="text-bold">#'.$info[0]['number'].'</span></p>';
					endif;
				//echo '<a href="/player/?id='.$val['pid'].'"><img src="'.$playerimg.'" class="img-responsive"/></a>';
				echo '<img alt="Profile Picture" class="widget-img img-border-light" style="width:100px; height:100px; left:75%; top:10px;" src="'.$playerimg.'">';
				?>
                <?php
                    if($gamesbyteam):
                        arsort($gamesbyteam);
                            $r = 0;
                            foreach ($gamesbyteam as $key => $value):
                                if ($r == 0):
                                    $string .= '<strong>'.$teams[$key]['team'].'</strong>, ';
                                else:
                                    $string .= $teams[$key]['team'].', ';
                               endif;
                               $r++;
                            endforeach;
                        echo 'PFL Teams: '.substr($string, 0, -2);
                    endif;
                    ?>

				<div class="table-responsive mar-top">
						
					<table class="table table-striped">
					<tbody>
					<tr>
						<td class="text-left">Points Scored</td>
						<td><span class="text-bold"><?php if ($career['points'] > 0){ echo number_format($career['points'],0); } else {echo 0; } ?></span></td>
					</tr>
					<tr>
						<td class="text-left">Games Played</td>
						<td><span class="text-bold"><?php if ($career['games'] > 0){ echo $career['games']; } else { echo 0; } ?></span></td>
					</tr>
					<tr>
						<td class="text-left">Position Rank</td>
						<td><span class="text-bold"><?php if ($career['careerposrank'] != ''){ echo ordinal($career['careerposrank']);} else { echo '--';} ?></span></td>
					</tr>
					<tr>
						<?php 
							$st = $career['years'][0];
						if($career['games'] > 0){	
							$end = end($career['years']);
						}
						if ($st != $end){	?>
							<td class="text-left">Seasons</td>
							<td><span class="text-bold"><?php echo $st.' - '.$end; ?></span></td>
						<?php } else { ?>
							<td class="text-left">Season</td>
							<td><span class="text-bold"><?php echo $st; ?></span></td>
						<?php } ?>
					</tr>
					<tr>
						<td class="text-left">Points Per Game</td>
						<td><span class="text-bold"><?php echo number_format($career['ppg'], 1); ?></span></td>
					</tr>
					<tr>
						<td class="text-left">Career High</td>
						<td><span class="text-bold"><?php echo $career['high']; ?></span></td>
					</tr>
					<?php if(!empty($printawards)){
						asort($printawards);
						
						?>
					<tr>
						<td class="text-left">Career Awards</td>
						<td><span class="text-bold">
							<?php foreach ($printawards as $value){
								$awid = substr($value , 0, -4);
								$awyr = substr($value, -4);
								
								echo $awyr.' ';
								labeltheseaward($awid);
								echo '<br>';
								
								
								}?>
						</span></td>
					</tr>
					
					
					<?php
						foreach ($number_ones as $key => $value){
							if ($value['playerid'] == $pid){
								$player_number_ones[$key] = array(
									'id' => $value['id'],
									'points' => $value['points'],
									'team' => $value['teams']
								);
							}
						}
						
						if(!empty($player_number_ones)){?>
						
						<tr>
						<td class="text-left">Position Scoring Titles</td>
						<td><span class="text-bold">
							<?php 
								foreach ($player_number_ones as $value){
									$stid = substr($value['id'], 2, 4);
									$stpts = $value['points'];
									$stteam = $value['team'];
									
									echo $stid.' - '.$teams[$stteam]['team'].' | '.$stpts.' Pts';
									echo '<br>';
				
								}?>
							</span></td>
						</tr>
						<?php 
							
							}
						?>
					
					<?php
						}
						if ($thecount[$pid] != ''){ 
						?>
						<tr>
							<td class="text-left">Player of The Week</td>
							<?php if ($thecount[$pid] == 1){ ?>
								<td><span class="text-bold"><?php echo $thecount[$pid]; ?> Time</span></td>
							<?php } else { ?>	
								<td><span class="text-bold"><?php echo $thecount[$pid]; ?> Times</span></td>
							<?php } ?>
						</tr>
						<?php 
						}
						if (!empty($pbapps)){ 
					?>
					<tr>
						<td class="text-left">Posse Bowl Appearances</td>
						<td><span class="text-bold">
							<?php foreach($pbapps as $key => $value){
									echo $key.' - '.$teams[$value]['team'].'<br>';
								}
							?>	
						</span></td>
					</tr>
					<?php 
						}
						if (!empty($playerchamps)){ 
					?>
					<tr>
						<td class="text-left">PFL Championships</td>
						<td><span class="text-bold">
							<?php foreach($playerchamps as $key => $value){
									echo $key.' - '.$teams[$value]['team'].'<br>';
								}
							?>	
						</span></td>
					</tr>
	
					<?php 
						
						} ?>
					
					</tbody>
					</table>
					<?php
						if (in_array($pid, $halloffame)){ 
							echo '<h5 class="text-left text-bold">&nbsp;Inducted into the PFL Hall of Fame</h5>';
						}
			echo '</div>';

			//TEMPORARY TO BUILD OLD PLAYER INFO DATA
			if($info[0]['weight'] == ''):
				if($position == 'PK'):
					echo '<a href="http://localhost:10060/?id='.$pid.'" target="_blank">INDEX LINK</a>';
				endif;
			endif;
			
		echo '</div>';
		
		echo '</div>';
		echo '</div>';

}

// gets all game scores by players for an individual season, sorted by best.
function get_season_game_highs($yearval){
	$playerslist = get_season_leaders($yearval);
	foreach ($playerslist as $player){
		$lists[$player['playerid']] = get_player_season_stats($player['playerid'], $yearval);
	}
	foreach ($lists as $key => $value){
		$output[$key] = array(
			$value['week1']['team'].'01' => $value['week1']['points'], 
			$value['week2']['team'].'02' => $value['week2']['points'], 
			$value['week3']['team'].'03' => $value['week3']['points'],
			$value['week4']['team'].'04' => $value['week4']['points'],
			$value['week5']['team'].'05' => $value['week5']['points'],
			$value['week6']['team'].'06' => $value['week6']['points'],
			$value['week7']['team'].'07' => $value['week7']['points'],
			$value['week8']['team'].'08' => $value['week8']['points'],
			$value['week9']['team'].'09' => $value['week9']['points'],
			$value['week10']['team'].'10' => $value['week10']['points'],
			$value['week11']['team'].'11' => $value['week11']['points'],
			$value['week12']['team'].'12' => $value['week12']['points'],
			$value['week13']['team'].'13' => $value['week13']['points'],
			$value['week14']['team'].'14' => $value['week14']['points']
		); 
	}
	foreach ($output as $kr => $vu){
		foreach ($vu as $k => $v){
			if($v > 0){
				$thruput[$kr.$k] = $v;
			}
		}
	}
	arsort($thruput);
	return $thruput;
}


//get week 1 revenge game results
function revenge_game(){
	$champs = get_champions(); 
		foreach($champs as $key => $val):
			$nextyear = $key + 1;
			$getteamresult = get_team_results_by_week($val['winner'], $nextyear.'01');
			$teamresult = $getteamresult[$nextyear.'01'];
			
			if($teamresult['result'] > 0):
				$nextwinner = $teamresult['team_int'];
				$nextloser = $teamresult['versus'];
			else:
				$nextwinner = $teamresult['versus'];
				$nextloser = $teamresult['team_int'];	
			endif;

			$pbgames[$key + 1] = array(
				'pb_winner'	=> $val['winner'],
				'pb_loser'	=> $val['loser'],
				'next_win' => $nextwinner,
				'next_loser' => $nextloser,
				//'data'	=> $teamresult
			);								
		endforeach;	
		
	return $pbgames;	
}

function get_uni_info_by_team($teamid){
    global $wpdb;
    $get = $wpdb->get_results("select * from wp_helmet_history where team = '$teamid'", ARRAY_N);

    $jersey_val = array();

    foreach ($get as $revisequery){
        $jersey_val[$revisequery[2]] = $revisequery[5];
    }

    $i = 1991;
    $thisyear = date('Y');

    while ($i <= $thisyear){
        if($jersey_val[$i]):
            $build_jersey[$i] = $jersey_val[$i];
            $store = $jersey_val[$i];
        else:
            $build_jersey[$i] = $store;
        endif;
        $i++;
    }

    return $build_jersey;
}

// Return a svg of a jersey by number
// team id abv ALLCAP, H or R or A (alt), year jersey was worn, number on jersey

function show_jersey_svg ($teamid, $location, $year, $num){
    $number = str_pad($num, 2, '0', STR_PAD_LEFT);
    $jersey =  '/img/uni-svgs/'.$teamid.'/'.$teamid.'_'.$location.'_'.$year.'_0'.$number.'000.svg';
    return $jersey;
}

function show_helmet ($teamid, $year, $facing){
    $helmet =  '/img/helmets/weekly/'.$teamid.'-helm-'.$facing.'-'.$year.'.png';
    return $helmet;
}

// gets json files stored locally from MFL website
function get_mfl_year_rosters($rosteryear){
    $mfl_roster_file = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/mfl-rosters/'.$rosteryear.'-rosters.json';
    if (file_exists($mfl_roster_file)):
        $getfile = file_get_contents($mfl_roster_file);
        $decode_json = json_decode($getfile);
    endif;
    return $decode_json;
}

// gets json mfl file (created on the '/transactions' page) that merges all of the files above into a name -> id format
function get_all_merged_mfl_year_rosters(){
    $mfl_roster_file = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/mfl-rosters/mfl-all-player-names-ids-2011-2020.json';
    if (file_exists($mfl_roster_file)):
        $getfile = file_get_contents($mfl_roster_file);
        $decode_json = json_decode($getfile);
    endif;
    return $decode_json;
}

function teams_for_mfl(){
    $teams = get_teams();
    foreach ($teams as $key => $value):
        if ($value['mfl_team_id']):
            $teambyid[$value['mfl_team_id']] = $key;
        endif;
    endforeach;
    return $teambyid;
}


// wp_teams table shows current team mfl ID.  This array accounts for the fact that the ids have shifted a bit over the years.
function teams_for_mfl_history(){
    $mfl_team_id_history = array(
        2011 => array(
            '0001' => 'BST',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'PHR',
            '0006' => 'SON',
            '0007' => 'ATK',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL',
            '0011' => 'SNR',
            '0012' => 'TSG'
        ),
        2012 => array(
            '0001' => 'MAX',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'PHR',
            '0006' => 'SON',
            '0007' => 'ATK',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL',
            '0011' => 'SNR',
            '0012' => 'TSG'
        ),
        2013 => array(
            '0001' => 'MAX',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'PHR',
            '0006' => 'SON',
            '0007' => 'ATK',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL',
            '0011' => 'SNR',
            '0012' => 'TSG'
        ),
        2014 => array(
            '0001' => 'MAX',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'PHR',
            '0006' => 'SON',
            '0007' => 'ATK',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL',
            '0011' => 'SNR',
            '0012' => 'TSG'
        ),
        2015 => array(
            '0001' => 'MAX',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'PHR',
            '0006' => 'SON',
            '0007' => 'ATK',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL',
            '0011' => 'SNR',
            '0012' => 'TSG'
        ),
        2016 => array(
            '0001' => 'TSG',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'DST',
            '0006' => 'SON',
            '0007' => 'SNR',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL'
        ),
        2017 => array(
            '0001' => 'TSG',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'DST',
            '0006' => 'SON',
            '0007' => 'SNR',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL'
        ),
        2018 => array(
            '0001' => 'TSG',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'DST',
            '0006' => 'SON',
            '0007' => 'SNR',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL'
        ),
        2019 => array(
            '0001' => 'TSG',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'DST',
            '0006' => 'BST',
            '0007' => 'SNR',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL'
        ),
        2020 => array(
            '0001' => 'TSG',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'DST',
            '0006' => 'BST',
            '0007' => 'SNR',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL'
        )
    );
    return $mfl_team_id_history;
}


function mfl_years(){
    $y = date('Y');
    $d = 2011;
    while ($d <= $y) {
        $yearslist[] = $d;
        $d++;
    }
    return $yearslist;
}

// checks if MFL ID is present for player on wp_players table, then will insert MFL ID from json exports (function above).
function check_mfl_id_exsists($pid){
    $getinfo = get_player_basic_info($pid);
    $check = $getinfo[0]['mflid'];
    if($check > 1):
        echo 'already exsists';
    else:
        $getmfl = get_all_merged_mfl_year_rosters();
        $name = $getinfo[0]['last'].', '.$getinfo[0]['first'];
        $mflid = $getmfl->$name;
        if($mflid):
            global $wpdb;
            $wpdb->update(
                'wp_players',
                array(
                    'mflid' => $mflid
                ),
                array(
                    'p_id' => $pid
                )
            );
        else:
            echo 'no id found';
        endif;
    endif;
}

function get_mfl_transactions($yearid){
    $mfl_trans_file = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/mfl-transactions/'.$yearid.'-trans.json';
    if (file_exists($mfl_trans_file)):
        $getfile = file_get_contents($mfl_trans_file);
        $decode_json = json_decode($getfile);
    endif;
    return $decode_json;
}

function explode_strip($string){
    $newstring = explode('|', $string);
    // $morestring = explode(',' $newstring);
    return $newstring;
}

function clean_up_array($item){
    $theitem = array();
    $val = explode_strip($item);
    foreach ($val as $v):
        if($v):
            $theitem = explode(',', $v);
        endif;
    endforeach;
    return array_filter($theitem);
}

function get_player_mfl_transactions ($array, $pid){
    foreach ($array as $key => $value):
        foreach ($value as $v):
            if(is_array($v)):
                foreach($v as $a):
                    if($a == $pid):
                        $justplayer[] = $value;
                    endif;
                endforeach;
            endif;
        endforeach;
    endforeach;
    return $justplayer;
}

function new_mfl_transactions($pid){
    $convertids = playerid_mfl_to_pfl();
    $yearslist = mfl_years();
    $teambyid = teams_for_mfl_history();


    foreach ($yearslist as $year):
        $cleantrans = array();
        $gettransaction = get_mfl_transactions($year);
        $transaction = $gettransaction->transactions->transaction;

        if($transaction):
            foreach ($transaction as $value):
                $a_n_val = array();
                $de_n_val = array();
                $ad_n_val = array();
                $dr_n_val = array();
                $t_n_val = array();
                $give1 = array();
                $give2 = array();
                $action = array();

                if ($value->type == 'TRADE'):
                    $gaveup1 = clean_up_array($value->franchise1_gave_up);
                    $gaveup2 = clean_up_array($value->franchise2_gave_up);

                    foreach ($gaveup1 as $g):
                        $give1[$g] = $convertids[$g];
                    endforeach;
                    foreach ($gaveup2 as $f):
                        $give2[$f] = $convertids[$f];
                    endforeach;

                    $cleantrans[] = array(
                        'timestamp' => $value->timestamp,
                        'realtime' => date("m-d-Y h:ia", $value->timestamp),
                        'type' => $value->type,
                        'franchise1' => $teambyid[$year][$value->franchise],
                        'franchise_1_gave_up' => $give1,
                        'franchise2' => $teambyid[$year][$value->franchise2],
                        'franchise2_gave_up' => $give2
                    );

                else:
                    if ($value->type != 'TRADE_OFFER_EXPIRED'):
                        if ($value->type != 'TRADE_PROPOSAL'):
                            $a_val = clean_up_array($value->activated);
                            foreach ($a_val as $v): $a_n_val[$v] = $convertids[$v]; endforeach;
                            $de_val = clean_up_array($value->deactivated);
                            foreach ($de_val as $v): $de_n_val[$v] = $convertids[$v]; endforeach;
                            $ad_val = clean_up_array($value->added);
                            foreach ($ad_val as $v): $ad_n_val[$v] = $convertids[$v]; endforeach;
                            $dr_val = clean_up_array($value->dropped);
                            foreach ($dr_val as $v): $dr_n_val[$v] = $convertids[$v]; endforeach;
                            $t_val = clean_up_array($value->transaction);
                            foreach ($t_val as $v): $t_n_val[$v] = $convertids[$v]; endforeach;

                            if($value->action)
                                $action = $value->action;

                            $cleantrans[] = array(
                                'timestamp' => $value->timestamp,
                                'realtime' => date("m-d-Y h:ia", $value->timestamp),
                                'type' => $value->type,
                                'action' =>  $action,
                                'franchise' => $teambyid[$year][$value->franchise],
                                'activated' => $a_n_val,
                                'deactivated' => $de_n_val,
                                'added' => $ad_n_val,
                                'dropped' => $dr_n_val,
                                'transaction' => $t_n_val

                            );
                        endif;
                    endif;
                endif;
            endforeach;
        endif;

        $clean = array_filter($cleantrans);
        $playertrans[$year] = get_player_mfl_transactions($clean, $pid);

        //printr($clean, 0);

    endforeach;
    return $playertrans;
}

//
//Get List of Tight Ends
function get_tightends(){
    global $wpdb;
    $gette = $wpdb->get_results(" select * from wp_tightends ", ARRAY_N);
    foreach ($gette as $p){
        $te[] = $p[1];
    }
    return $te;
}

// Check if player is TE by playerid
function check_tightend($playerid){
    $telist = get_tightends();
    if(in_array_r($playerid, $telist)){
        $check = 1;
    } else {
        $check = 0;
    }
    return $check;
}

//Get ALL BS WINS
function get_bswins(){
    global $wpdb;
    $bsw = $wpdb->get_results(" select * from wp_bswoty ", ARRAY_N);
    foreach ($bsw as $p){
        $week = str_pad($p[1], 2, '0', STR_PAD_LEFT);
        $bswoty[$p[0].$week] = array(
            'winner' => $p[2],
            'loser' => $p[3]
        );
    }
    return $bswoty;
}


//alter players tables to include game yardage and td data
function alter_player_table_columns ($pid){
    global $wpdb;
    $myData = $wpdb->get_row("SELECT * FROM $pid");
    //Add column if not present.
    if (!isset($myData->nfl_game)) {
        $wpdb->query("ALTER TABLE $pid ADD nfl_game VARCHAR(20)");
    }
    printr($myData, 0);
}



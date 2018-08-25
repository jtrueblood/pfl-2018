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

/* Vars used commonly in functions */
session_start();
$season == date("Y");

/* Store all team IDs and Names in a Session Variable */
$teamids = array( 'RBS'=>'Red Barons', 'ETS'=>'Euro-Trashers', 'PEP'=>'Peppers', 'WRZ'=>'Space Warriorz',  'CMN'=>'C-Men', 'BUL'=>'Raging Bulls', 'SNR'=>'Sixty Niners', 'TSG'=>'Tsongas', 'BST'=>'Booty Bustas', 'SON'=>'Rising Sons',  'PHR'=>'Paraphernalia', 'HAT'=>'Jimmys Hats',  'ATK'=>'Melmac Attack',  'MAX'=>'Mad Max', 'DST'=>'Destruction');
$_SESSION['teamids'] = $teamids;

/* connect to pflmicro database */
$mydb = new wpdb('root','root','pflmicro','localhost');


/* allow plugin updates on localhost */
if ( is_admin() ) {
add_filter( 'filesystem_method', create_function( '$a', 'return "direct";' ) );
	if ( ! defined( 'FS_CHMOD_DIR' ) ) {
		define( 'FS_CHMOD_DIR', 0751 );
	}
}



/* clean print_r */

function printr($data, $die) {
   echo '<pre>';
      print_r($data);
   echo '</pre>';
   if ($die == 1){
	   echo die();
	   echo exit(0);
   }
}

/* clean print_r */

function printrlabel($data, $label) {
   echo '<pre>';
   echo '<h3>'.$label.'</h3>';	
   print_r($data);
   echo '</pre>';
}


/* Get Single txt file from Cache and store as array.  0 no print, 1 for print on page */

function get_cache($file, $print){
	$cache = 'http://posse-football.dev/wp-content/themes/tif-child-bootstrap/cache/'.$file.'.txt';
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
	$cache = 'http://posse-football.dev/wp-content/themes/tif-child-bootstrap/cache/'.$file.'.txt';
	$get = file_get_contents($cache, FILE_USE_INCLUDE_PATH);
	$data = unserialize($get); 
	$_SESSION[$file] = $data;
}


/* Get Player data from cache */

function get_player_cache($file, $print, $year){
	$playercache = 'http://posse-football.dev/wp-content/themes/tif-child-bootstrap/cache/playerdata/'.$file.'.txt';
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
	$playercache = 'http://posse-football.dev/wp-content/themes/tif-child-bootstrap/cache/playerdata/'.$playerid.'.txt';
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
	while ($o < $year){
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
				<td class="text-center"><img src="https://posse-football.dev/wp-content/themes/tif-child-bootstrap/img/players/'.$leadersprint[0].'.jpg" class="leaders-image"></td>
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
			<td class="text-center"><img src="http://posse-football.dev/wp-content/themes/tif-child-bootstrap/img/players/'.$highprint[5].'.jpg" class="leaders-image"></td>
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

function getallteamcache ($team){
	get_cache('team/'.$team.'_f', 0);	
	$build = $_SESSION['team/'.$team.'_f'];
	
	return $build;
}


// requires 'playersassoc' cache added to page
function leadersbyseason ($array, $year, $labelpos){

    $playersassoc = get_players_assoc ();
	
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
							$printval .= '<th>Player</th><th class="text-center">Points</th><th class="text-center">Games</th>';
						$printval .= '</tr>';
					$printval .= '</thead>';
					$printval .= '<tbody>';
							$rank = 1;
							
							foreach ($array as $key => $getvars){
								$first = $playersassoc[$getvars['playerid']][0];
								$last = $playersassoc[$getvars['playerid']][1];
								
// 								$printval .= '<td>'.$rank.'.</td>';
								$printval .= '<td><a href="/player/?id='.$getvars['playerid'].'" class="btn-link">'.$first.' '.$last.'</a></td>';
								$printval .= '<td class="text-center">'.$getvars['points'].'</td>';
								$printval .= '<td class="text-center">'.$getvars['games'].'</td>';
								$printval .= '</tr>';
								$rank++;
							}
							
						$printval .= '</tbody></table></div></div></div></div>';
	echo $printval;
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
	$playerimg = 'http://posse-football.dev/wp-content/themes/tif-child-bootstrap/img/players/'.$playerid.'.jpg';
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

function get_players_assoc (){
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
			$revisequery[10]
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
		
		$trades[$revisequery[1]] = array(
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
	//return $playertrade;
	
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


function get_playoff_points_by_team_year($year, $team, $week){
	global $wpdb;
	$get = $wpdb->get_results("select points from wp_playoffs where week = '$week' && team = '$team' && year='$year'", ARRAY_N);
	
	foreach ($get as $value){
		$sumval[] = $value[0];
	}
	if(isset($sumval)){
		$output = array_sum($sumval);
	}
	return $output;
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

// get hall of fame inductees only
function get_award_hall(){
	global $wpdb;
	$gethall = $wpdb->get_results("select * from wp_awards WHERE award = 'Hall of Fame Inductee'", ARRAY_N);
	foreach ($gethall as $hall){
		$hallids[$hall[2]] = $hall[8];
	}
	
	return $hallids;
}

// gets the weekly stats from the player table
function get_player_data($pid) {
	global $wpdb;
	$getplayer = $wpdb->get_results("select * from $pid", ARRAY_N);
	
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
			'versus' => $revisequery[3],  
			'home' => $revisequery[4],
			'points' => $revisequery[5]
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
			'int' => $revisequery[4]
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

// gets the cumulative career stats from the player table
function get_player_career_stats($pid){
	
	$data_array = get_player_data($pid);
	
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

// get probowl boxscore
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




function get_number_ones(){
	
	global $wpdb;
	$get_number_ones = $wpdb->get_results("select * from wp_number_ones", ARRAY_N);
	
	foreach ($get_number_ones as $revisequery){
		$number_ones[$revisequery[0]] = array(
			'id' => $revisequery[0],
	        'playerid' => $revisequery[1],
	        'points' => $revisequery[2],
	        'teams' => $revisequery[3]
		);
	}

return $number_ones;

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
					$printit .= '<th class="min-width">Position</th>';
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
					$containsLetter  = preg_match('/[a-zA-Z]/',    $pid);
					$idlength = strlen($pid);
					$playerimg = '/wp-content/themes/tif-child-bootstrap/img/players/'.$pid.'.jpg';
					$pflmini = '/wp-content/themes/tif-child-bootstrap/img/pfl-mini-dark.jpg';
					$position = $build['position'];
		
					
					if ($activenum > $picknumber){
						$printit .= '<tr class="text-center bg-dark text-2x"><td colspan="6">Round '.$round.'</td></tr>';
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
					
						if ($containsLetter != 0){		
							$printit .= '<td class="min-width hidden-xs"><img src="'.$playerimg.'" class="img-sm player-image" style="background-color:#515151;"/></td>';
						} else {
							$printit .= '<td class="min-width hidden-xs"><img src="'.$pflmini.'" class="img-sm player-image"/></td>';
						}
						

						if ($containsLetter != 0){
							$printit .= '<td class="text-bold"><a href="/player/?id='.$pid.'" class="player-link">'.$first.' '.$last.'</a></td>';
						} else {
							$printit .= '<td class="text-bold">'.$first.' '.$last.'</td>';
						}
						$printit .= '<td class="text-center"><span class="">'.$position.'</span></td>';
					$printit .= '</tr>';
					
					$activenum = $picknumber;
				}
							
		$printit .= '</tbody>';
	$printit .= '</table>';
$printit .= '</div>';
$printit .= '</div>';

echo $printit;

}



// function to return player data from MFL

function get_mfl_player_details($mflid){
	$year = 2017;
	$lid = 38954;
	$curl = curl_init();

	curl_setopt_array($curl, array(
	 // CURLOPT_URL => "http://www58.myfantasyleague.com/$year/export?TYPE=players&DETAILS=&SINCE=&PLAYERS=$mflid&JSON=1",
	 
	  CURLOPT_URL => "http://www58.myfantasyleague.com/$year/export?TYPE=players&DETAILS=8931&SINCE=&PLAYERS=$mflid&JSON=1",
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
	
	return $mflguy['players']['player'];

}


function get_boxscore_cache($weekvar){
	$boxscorecache = 'http://posse-football.dev/wp-content/themes/tif-child-bootstrap/cache/boxscores/'.$weekvar.'box.txt';
	
	
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














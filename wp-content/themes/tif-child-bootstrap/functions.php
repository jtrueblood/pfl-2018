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

// Enqueue stylesheets for the theme
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

add_action( 'wp_enqueue_scripts', 'tif_styles' );

// Custom image sizes
add_image_size( 'player-mini', 50, 50, true );
add_image_size( 'player-card', 400, 400, array( 'center', 'top' ) );

// Initialize session and common variables
session_start();
$season = '';
$season == date("Y");

// Store all team IDs and Names in a Session Variable
$teamids = array( 'RBS'=>'Red Barons', 'ETS'=>'Euro-Trashers', 'PEP'=>'Peppers', 'WRZ'=>'Space Warriorz',  'CMN'=>'C-Men', 'BUL'=>'Raging Bulls', 'SNR'=>'Sixty Niners', 'TSG'=>'Tsongas', 'BST'=>'Booty Bustas', 'SON'=>'Rising Sons',  'PHR'=>'Paraphernalia', 'HAT'=>'Jimmys Hats',  'ATK'=>'Melmac Attack',  'MAX'=>'Mad Max', 'DST'=>'Destruction');
$_SESSION['teamids'] = $teamids;

// Connect to pflmicro database
$mydb = new wpdb('root','root','pflmicro','localhost');

// Add ACF options page if available
if( function_exists('acf_add_options_page') ) {
	$args = array('title' => 'Options');
	acf_add_options_page($args);
}

// Allow plugin updates on localhost
if ( is_admin() ) {
	add_filter( 'filesystem_method', create_function( '$a', 'return "direct";' ) );
	if ( ! defined( 'FS_CHMOD_DIR' ) ) {
		define( 'FS_CHMOD_DIR', 0751 );
	}
}

// Clean print_r output with formatting options
function printr($data, $die) {
	echo '<pre>';
	print_r($data);
	echo '</pre>';
	if ($die == 1):
		echo die();
		echo exit(0);
	endif;
	if ($die == 2):
		var_dump($data);
	endif;
}

// Clean print_r output with custom label
function printrlabel($data, $label) {
	echo '<pre>';
	echo '<h3>'.$label.'</h3>';
	print_r($data);
	echo '</pre>';
}

// Remove special characters from string and replace spaces with hyphens
function clean($string) {
	$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
	return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

// Check if image exists in WordPress posts by title
function check_if_image($image_src){
	global $wpdb;
	$query = $wpdb->query("SELECT ID FROM wp_posts WHERE post_title = '$image_src'" );
	return $query;
}

// Get attachment URL by slug
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
    $indplayerdata =array();
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
function the_seasons($chmonth = 8){  // Returns a list of PFL Seasona as a 4 digit year.  There is a vaiable in here that can set the month the year turns over.
    $month = date('m'); // get the current month as an int
    $smonth = sprintf('%0d', $month);  //format with no leading zero
    // set to best month where the PFL seasons turn over.  Can change it when the function is called but it will default to august (8)

    $syear = date('Y');
    if($smonth < $chmonth):
	    $year = $syear - 1;
	else:
        $year = $syear;
	endif;

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

function the_weeks_with_key(){
    $years = the_seasons();
    $weeks = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14');

    foreach ($years as $year){
        foreach ($weeks as $week){
            $theweeks[$year.$week] = $year.$week;
        }
    }

    return $theweeks;
}

// check if a game was the second half of a head to head matchup
function checkheadhead($weeka){
    $schedule = schedule_by_week();
    $theweekids = the_weeks();
    $current_array_val = array_search($weeka, $theweekids);
    $prevweek = $theweekids[$current_array_val-1];
    $theweeka = $schedule[$weeka];
    $theweekb = $schedule[$prevweek];
    $yeara = substr($weeka, 0, 4);
    $yearb = substr($prevweek, 0, 4);

    if($yeara == $yearb):
        if(is_array($theweeka)):
            foreach ($theweeka as $item) {
                $home = $item['hometeam'];
                $road = $item['roadteam'];
                $weekarra[$home] = $road;
            }
        endif;
        if(is_array($theweekb)):
            if($theweekb):
                foreach ($theweekb as $item) {
                    $home = $item['hometeam'];
                    $road = $item['roadteam'];
                    $weekarrb[$home] = $road;
                }
            endif;
        endif;
            if(is_array($weekarra)):
            foreach($weekarra as $key => $value):
                if($weekarrb[$value] == $key):
                    $output[$key.'-'.$value] = 1;
                else:
                    $output[$key.'-'.$value] = 0;
                endif;
            endforeach;
            endif;
    endif;
    return $output;
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
    
    // Sort array by points in descending order
    usort($array, function($a, $b) {
        return $b['points'] - $a['points'];
    });
    
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
							$printval .= '<th class="copy-col"></th><th>Player</th><th class="text-center">Points</th><th class="text-center">Games</th><th class="text-center">Teams</th><th class="text-center min-width">Pro</th>';
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
								$pythonCmd = 'python3 getplayernfldata.py "' . $first . ' ' . $last . '" ' . $year . ' all Yes';
								$printval .= '<td class="copy-col"><button class="copy-python-btn" data-command="'.htmlspecialchars($pythonCmd, ENT_QUOTES, 'UTF-8').'" title="Copy Python command"><i class="fa fa-clipboard"></i></button></td>';
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


// requires 'playersassoc' cache added to page
function get_gamedate_by_player ($playerid, $weekid){
    global $wpdb;
    $getdata = $wpdb->get_results("select game_date from $playerid where week_id = $weekid");
    return $getdata[0]->game_date;
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
	
	$protections = array(); 
	foreach ($get as $key => $revisequery){
		$protections[] = array(
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
	
	$trades = array();
	foreach ($get as $key => $revisequery){
		$trades[] = array(
            'id' => $revisequery[0],
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
			'when' => $revisequery[11],
            'tradewinner' => $revisequery[12],
            'tradeloser' => $revisequery[13],
            'tradewinpoints' => $revisequery[14]
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

// Get released players - players on roster in year N but not protected in year N+1
function get_released_player($pid){
	global $wpdb;
	
	// Get all rosters for this player
	$rosters = $wpdb->get_results("select * from wp_rosters where pid = '$pid' ORDER BY year", ARRAY_N);
	
	// Get all protections for this player
	$protections_raw = $wpdb->get_results("select * from wp_protections where playerid = '$pid'", ARRAY_N);
	
	// Build protections lookup by year
	$protected_years = array();
	foreach ($protections_raw as $prot) {
		$protected_years[$prot[1]] = true; // $prot[1] is the year
	}
	
	$released = array();
	
	// Check each roster year
	foreach ($rosters as $roster) {
		$current_year = $roster[2]; // year field
		$next_year = $current_year + 1;
		$team = $roster[3]; // team field
		
		// If player was on roster in year N and NOT protected in year N+1, they were RELEASED
		if (!isset($protected_years[$next_year])) {
			$released[] = array(
				'year' => $next_year,
				'team' => $team,
				'playerid' => $pid
			);
		}
	}
	
	return $released;
}

// returns an array of the manner in which a player was acquired
function how_player_was_acquired($playerid, $season, $teamid, $season_drafts = null, $all_protections = null, $all_trades = null){
    // check for protection
    // Use pre-fetched protections if provided, otherwise query
    if($all_protections !== null):
        // Look up in pre-fetched data
        $protections = array();
        foreach($all_protections as $prot):
            if($prot['playerid'] == $playerid):
                $protections[$prot['year']] = $prot['team'];
            endif;
        endforeach;
        if(isset($protections[$season]) && $protections[$season] == $teamid):
            $protected = 'Protected';
        endif;
    else:
        // Fallback to individual query
        $getprotections = get_protections_player($playerid);
        if($getprotections):
            foreach($getprotections as $key => $value):
                $protections[$value['year']] = $value['team'];
            endforeach;
            if(isset($protections[$season]) && $protections[$season] == $teamid):
                $protected = 'Protected';
            endif;
        endif;
    endif;

    // check for drafted
    // Use pre-fetched drafts if provided, otherwise query
    $getdrafts = $season_drafts !== null ? $season_drafts : get_drafts_by_year($season);
    if($getdrafts):
        foreach ($getdrafts as $key => $value):
            // Only mark as drafted if THIS team drafted them
            if($value['playerid'] == $playerid && $value['acteam'] == $teamid):
                $drafted = 'Drafted';
                break;
            endif;
        endforeach;
    endif;

    // check for traded
    // Use pre-fetched trades if provided, otherwise query
    if($all_trades !== null):
        // Look up in pre-fetched data
        $traded_to_teams = array();
        foreach($all_trades as $trade):
            if($trade['year'] == $season):
                $players1 = explode(',', $trade['players1']);
                $players2 = explode(',', $trade['players2']);
                
                if(in_array($playerid, $players1)):
                    $traded_to_teams[] = $trade['team1'];
                elseif(in_array($playerid, $players2)):
                    $traded_to_teams[] = $trade['team2'];
                endif;
            endif;
        endforeach;
        
        if($traded_to_teams):
            foreach ($traded_to_teams as $value):
                if($value == $teamid):
                    $traded = 'Traded';
                    break;
                endif;
            endforeach;
        endif;
    else:
        // Fallback to individual query
        $gettrades = get_trade_by_player($playerid);
        $seasontrade = $gettrades[$season];
        if($seasontrade):
            foreach ($seasontrade as $key => $value):
                $traded_to_teams[] = $value['traded_to_team'];
            endforeach;
        endif;
        
        if($traded_to_teams):
            foreach ($traded_to_teams as $key => $value):
                if($value == $teamid):
                    $traded = 'Traded';
                endif;
            endforeach;
        endif;
    endif;

    if($traded == '' && $protected == '' && $drafted == ''):
        $freeagent = 'Free Agent';
    endif;

    // else was picked up as a free agent
    $returnarray = array('traded' => $traded,'protected' => $protected,'drafted' => $drafted,'freeagent' => $freeagent);
    $returnarray = array_filter($returnarray);

    return $returnarray;
}

//gets all players from the team results by teamid
function get_just_players_by_team($teamid){
    $teamresults = get_team_results_expanded_new($teamid);
    foreach ($teamresults as $key => $value):
        $getallplayers[$key] = array(
            'QB1' => $value['qb1'],
            'RB1' => $value['rb1'],
            'WR1' => $value['wr1'],
            'PK1' => $value['pk1'],
            'Overtime' => array(
                'QB2' => $value['qb2'],
                'RB2' => $value['rb2'],
                'WR2' => $value['wr2'],
                'PK2' => $value['pk2']
            )
        );
    endforeach;
    return $getallplayers;
}

function get_overtime_games_players($teamid, $season){
    $teamresults = get_just_players_by_team($teamid);
    foreach ($teamresults as $key => $value):
        $year = substr($key, 0, 4);
        if($year == $season):
            if($value['Overtime']['QB2'] || $value['Overtime']['RB2'] || $value['Overtime']['WR2'] || $value['Overtime']['PK2']):
                $otarray[$key] = $value['Overtime'];
            endif;
        endif;
    endforeach;
    return $otarray;
}

function count_the_nones($teamid, $season){
    $teamplayers = get_just_players_by_team($teamid);
    $i = 0;
    foreach ($teamplayers as $key => $value):
        $year = substr($key, 0, 4);
        if($year == $season):
            if($value['QB1'] == 'None' || $value['QB1'] == '' || $value['QB1'] == NULL):
                $i++;
            endif;
            if($value['RB1'] == 'None' || $value['RB1'] == '' || $value['RB1'] == NULL):
                $i++;
            endif;
            if($value['WR1'] == 'None' || $value['WR1'] == '' || $value['WR1'] == NULL):
                $i++;
            endif;
            if($value['PK1'] == 'None' || $value['PK1'] == '' || $value['PK1'] == NULL):
                $i++;
            endif;
        endif;
    endforeach;
    return $i;
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

function get_seeds_by_year($year)
{
    $standings = get_standings($year);
    foreach ($standings as $kstand => $valuestand):
        $teamseed[$valuestand['teamid']] = $valuestand['seed'];
    endforeach;
    return $teamseed;
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


// returns just week 16 posse bowl playoff games
function get_posse_bowl(){

    global $wpdb;
    $getplayoffs = $wpdb->get_results("select * from wp_playoffs WHERE week = '16'", ARRAY_N);

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

// Get all players who won a Posse Bowl (all instances)
function get_player_champions(){
    $possebowl = get_posse_bowl();
    $i = 0;
    foreach ($possebowl as $key => $value):
        if($value['result'] == 1):
            $winners[$value['year'].$value['team'].$i] = $value['playerid'];
            $i++;
        endif;
    endforeach;
    return $winners;
}

// GET number of Championships by player
function count_player_championships($playerid){
    $playerchamps = get_player_champions();
    foreach ($playerchamps as $key => $value):
        if($value == $playerid):
            $countchamp[] = $playerid;
        endif;
    endforeach;
    return $countchamp;
}

// Same functions as count_player_championship() above but better organized and sorted
function player_championship_count(){
    $championplayers = get_player_champions();
    foreach ($championplayers as $key => $value):
        $playerchamps[$value] = count_player_championships($value);
    endforeach;
    foreach ($playerchamps as $k => $v):
        $newchampioncount[] = array_count_values($v);
    endforeach;
    foreach ($newchampioncount as $ke => $va):
        foreach ($va as $a => $b):
            $finalplayerchampcount[$a] = $b;
        endforeach;
    endforeach;
    arsort($finalplayerchampcount);
    return $finalplayerchampcount;
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


// get highest individual game score for a season
function get_highest_individual_game_score($year){
	global $wpdb;
	$players = get_players_assoc();
	
	$maxpoints = 0;
	$maxplayerinfo = array();
	
	foreach($players as $pid => $player){
		$playerdata = $wpdb->get_results("SELECT * FROM `".$pid."` WHERE year = ".$year." ORDER BY points DESC LIMIT 1", ARRAY_N);
		
		if(!empty($playerdata)){
			if($playerdata[0][3] > $maxpoints){
				$maxpoints = $playerdata[0][3];
				$maxplayerinfo = array(
					'first' => $player[0],
					'last' => $player[1],
					'position' => $player[2],
					'team' => $playerdata[0][4],
					'points' => $maxpoints,
					'pid' => $pid
				);
			}
		}
	}
	
	return $maxplayerinfo;
}

// get best bench player for a season (player with 5 or fewer games, highest PPG, not an early starter)
// Priority: more games > higher PPG > more games in second half (weeks 8+)
function get_best_bench_player($year){
	global $wpdb;
	$players = get_players_assoc();
	
	$maxgames = 0;
	$maxppg = 0;
	$maxlatergames = 0;
	$benchplayers = array();
	
	foreach($players as $pid => $player){
		$playerdata = @$wpdb->get_results("SELECT COUNT(*) as games, SUM(points) as total FROM `".$pid."` WHERE year = ".$year, ARRAY_N);
		
		if(!empty($playerdata) && $playerdata[0][0] > 0 && $playerdata[0][0] <= 5){
			// Check if player is an early starter (5+ games in first 7 weeks)
			$earlygames = @$wpdb->get_results("SELECT COUNT(*) as early FROM `".$pid."` WHERE year = ".$year." AND week <= 7", ARRAY_N);
			
			if(!empty($earlygames) && $earlygames[0][0] >= 5) {
				// Skip players who started early
				continue;
			}
			
			// Calculate PPG (points per game)
			$ppg = $playerdata[0][1] / $playerdata[0][0];
			$games = $playerdata[0][0];
			
			// Count games in second half (weeks 8+)
			$laterdata = @$wpdb->get_results("SELECT COUNT(*) as latergames FROM `".$pid."` WHERE year = ".$year." AND week >= 8", ARRAY_N);
			$latergames = !empty($laterdata) ? $laterdata[0][0] : 0;
			
			// Check if this player should replace current best
			// Priority: more games first, then higher PPG, then more games in second half
			if($games > $maxgames || ($games == $maxgames && $ppg > $maxppg) || ($games == $maxgames && $ppg == $maxppg && $latergames > $maxlatergames)){
				if($games > $maxgames) {
					// New highest game count, reset the list
					$benchplayers = array();
					$maxgames = $games;
					$maxppg = $ppg;
					$maxlatergames = $latergames;
				} else if($games == $maxgames && $ppg > $maxppg) {
					// Same game count but higher PPG, reset the list
					$benchplayers = array();
					$maxppg = $ppg;
					$maxlatergames = $latergames;
				} else if($games == $maxgames && $ppg == $maxppg && $latergames > $maxlatergames) {
					// Same games and PPG but more second-half games, reset the list
					$benchplayers = array();
					$maxlatergames = $latergames;
				}
				
				$teamdata = @$wpdb->get_results("SELECT team FROM `".$pid."` WHERE year = ".$year." LIMIT 1", ARRAY_N);
				$benchplayers[] = array(
					'first' => $player[0],
					'last' => $player[1],
					'position' => $player[2],
					'team' => !empty($teamdata) ? $teamdata[0][0] : '',
					'points' => $playerdata[0][1],
					'games' => $games,
					'ppg' => $ppg,
					'pid' => $pid
				);
			} else if($games == $maxgames && $ppg == $maxppg && $latergames == $maxlatergames) {
				// Tie: same games, PPG, and second-half games
				$teamdata = @$wpdb->get_results("SELECT team FROM `".$pid."` WHERE year = ".$year." LIMIT 1", ARRAY_N);
				$benchplayers[] = array(
					'first' => $player[0],
					'last' => $player[1],
					'position' => $player[2],
					'team' => !empty($teamdata) ? $teamdata[0][0] : '',
					'points' => $playerdata[0][1],
					'games' => $games,
					'ppg' => $ppg,
					'pid' => $pid
				);
			}
		}
	}
	
	return $benchplayers;
}

// Get all players tied for position leader (handles ties)
function get_position_leaders($year, $position) {
	global $wpdb;
	
	$leaderdata = $wpdb->get_results($wpdb->prepare("SELECT playerid, MAX(points) as points FROM wp_season_leaders WHERE season = %d AND playerid LIKE %s GROUP BY playerid", $year, $position.'%'), ARRAY_A);
	
	$players_assoc = get_players_assoc();
	$maxpoints = 0;
	$topplayers = array();
	
	foreach($leaderdata as $leader) {
		if($leader['points'] > $maxpoints) {
			$maxpoints = $leader['points'];
			$topplayers = array();
		}
		
		if($leader['points'] >= $maxpoints) {
			$player_info = $players_assoc[$leader['playerid']];
			$topplayers[] = array(
				'playerid' => $leader['playerid'],
				'first' => $player_info[0],
				'last' => $player_info[1],
				'position' => $player_info[2],
				'points' => $leader['points']
			);
		}
	}
	
	return $topplayers;
}

// Helper function to generate HTML for position title winners with tie support
function render_position_title_winner($players, $title, $year, $teamlist) {
	$html = '<div class="col-xs-24 col-sm-6">';
	
	if(count($players) == 2) {
		// Two tied players
		$html .= '<div class="panel">';
		$html .= '<div style="display: flex;">';
		
		foreach($players as $player_info) {
			$html .= '<div style="flex: 1;">';
			
			$playerimgobj = get_attachment_url_by_slug($player_info['playerid']);
			$imgid = attachment_url_to_postid($playerimgobj);
			$image_attributes = wp_get_attachment_image_src($imgid, array(400, 400));
			$playerimg = $image_attributes[0];
			
			$html .= '<div class="widget-header" style="height: 150px; overflow: hidden; position: relative;">';
			$html .= '<img class="widget-bg img-responsive" src="'.$playerimg.'" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">';
			$html .= '</div>';
			
			$playerteam = get_player_teams_season($player_info['playerid']);
			$teams = $playerteam[$year];
			if(is_array($teams)) {
				$tags = implode(', ', $teams);
			} else {
				$tags = $teams;
			}
			
			$html .= '<div class="widget-body text-center" style="padding: 10px;">';
			$html .= '<h6 class="mar-no text-center" style="font-size: 12px;">'.$player_info['first'].'<br>'.$player_info['last'].'</h6>';
			$html .= '<p class="text-light text-center mar-top" style="font-size: 12px;">'.$player_info['points'].' pts</p>';
			$html .= '<p class="text-light text-center" style="font-size: 11px;">'.$tags.'</p>';
			$html .= '</div>';
			$html .= '</div>';
		}
		
		$html .= '</div>';
		$html .= '<div style="text-align: center; padding: 10px; border-top: 1px solid #ddd;">';
		$html .= '<h5 style="margin: 0;">'.$year.' '.$title.' (Tied)</h5>';
		$html .= '</div>';
		$html .= '</div>';
	} else {
		// Single player
		$player_info = $players[0];
		$html .= '<div class="panel">';
		
		$playerimgobj = get_attachment_url_by_slug($player_info['playerid']);
		$imgid = attachment_url_to_postid($playerimgobj);
		$image_attributes = wp_get_attachment_image_src($imgid, array(400, 400));
		$playerimg = $image_attributes[0];
		
		$html .= '<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">';
		$html .= '<img class="widget-bg img-responsive" src="'.$playerimg.'" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">';
		$html .= '</div>';
		
		$playerteam = get_player_teams_season($player_info['playerid']);
		$teams = $playerteam[$year];
		if(is_array($teams)) {
			$tags = implode(', ', $teams);
		} else {
			$tags = $teams;
		}
		
		$html .= '<div class="widget-body text-center">';
		$html .= '<h5>'.$year.' '.$title.'</h5>';
		$html .= '<h4 class="mar-no text-center">'.$player_info['first'].'<br>'.$player_info['last'].'</h4>';
		$html .= '<p class="text-light text-center mar-top">Points: '.$player_info['points'].'</p>';
		$html .= '<p class="text-light text-center">'.$tags.'</p>';
		$html .= '</div>';
		$html .= '</div>';
	}
	
	$html .= '</div>';
	return $html;
}

// get iron men for a season (players who played 13+ games)
function get_iron_men($year){
	global $wpdb;
	$players = get_players_assoc();
	$ironmen = array();
	
	foreach($players as $pid => $player){
		$playerdata = @$wpdb->get_results("SELECT COUNT(*) as games FROM `".$pid."` WHERE year = ".$year, ARRAY_N);
		
		if(!empty($playerdata) && !empty($playerdata[0]) && $playerdata[0][0] >= 13){
			$teamdata = @$wpdb->get_results("SELECT team FROM `".$pid."` WHERE year = ".$year." LIMIT 1", ARRAY_N);
			
			if(!empty($teamdata) && !empty($teamdata[0]) && !empty($teamdata[0][0])) {
				$ironmen[] = array(
					'first' => $player[0],
					'last' => $player[1],
					'position' => $player[2],
					'team' => $teamdata[0][0],
					'games' => $playerdata[0][0],
					'pid' => $pid
				);
			}
		}
	}
	
	return $ironmen;
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

//get all awards by year
function get_all_awards(){
    $mvp = get_award('MVP', 8);

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
            'cum_wins' => $cumwins,
            'game_date' => $revisequery[10],
            'nflteam' => $revisequery[11],
            'game_location' => $revisequery[12],
            'nflopp' => $revisequery[13],
            'pass_yds' => $revisequery[14],
            'pass_td' => $revisequery[15],
            'pass_int' => $revisequery[16],
            'rush_yds' => $revisequery[17],
            'rush_td' => $revisequery[18],
            'rec_yds' => $revisequery[19],
            'rec_td' => $revisequery[20],
            'xpm' => $revisequery[21],
            'xpa' => $revisequery[22],
            'fgm' => $revisequery[23],
            'fga' => $revisequery[24],
            'nflscore' => $revisequery[25],
            'scorediff' => $revisequery[26],
            'twopt' => $revisequery[27],
		);

	}
	
	return $playerstats;
	
}


function get_player_nfl_team_by_week($pid) {
    global $wpdb;
    $getplayer = $wpdb->get_results("select * from $pid", ARRAY_N);
    foreach ($getplayer as $key => $revisequery){
        $nflteam[$revisequery[0]] = $revisequery[11];
    }
    return $nflteam;
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

// gets the PFL team that a player played for in a given week
function get_player_team_played_week($pid, $week) {
    global $wpdb;
    $get = $wpdb->get_results("select team from $pid where week_id ='$week'", ARRAY_N);
    return $get;
}

// gets the NFL team that a player played for in a given week
function get_player_team_played_week_nfl($pid, $week) {
    global $wpdb;
    $get = $wpdb->get_results("select nflteam from $pid where week_id ='$week'", ARRAY_N);
    return $get;
}

// gets the team that a player played for in a given week
function get_player_points_by_week($pid, $week) {
    global $wpdb;
    $get = $wpdb->get_results("select points from $pid where week_id ='$week'", ARRAY_N);
    return $get;
}

// Check if a player was on bye for a specific week
function is_player_on_bye($pid, $weekid) {
    global $wpdb;
    
    // Extract year and week from weekid (e.g., '201411' -> year: 2014, week: 11)
    $year = (int)substr($weekid, 0, 4);
    $week = (int)substr($weekid, 4, 2);
    
    // Try to get player's NFL team for that specific week
    $result = $wpdb->get_results($wpdb->prepare(
        "SELECT nflteam FROM `{$pid}` WHERE week_id = %s LIMIT 1",
        $weekid
    ), ARRAY_N);
    
    // If player didn't play that week, check previous/next weeks for their NFL team
    $nfl_team = null;
    if (!empty($result) && isset($result[0][0]) && $result[0][0] != '' && $result[0][0] !== null) {
        $nfl_team = $result[0][0];
    } else {
        // Try to find NFL team from surrounding weeks in the same season
        $surrounding_weeks = array();
        for ($i = 1; $i <= 3; $i++) {
            $surrounding_weeks[] = sprintf('%04d%02d', $year, $week - $i);
            $surrounding_weeks[] = sprintf('%04d%02d', $year, $week + $i);
        }
        
        foreach ($surrounding_weeks as $check_week) {
            $check_result = $wpdb->get_results($wpdb->prepare(
                "SELECT nflteam FROM `{$pid}` WHERE week_id = %s AND nflteam IS NOT NULL AND nflteam != '' LIMIT 1",
                $check_week
            ), ARRAY_N);
            
            if (!empty($check_result) && isset($check_result[0][0]) && $check_result[0][0] != '' && $check_result[0][0] !== null) {
                $nfl_team = $check_result[0][0];
                break;
            }
        }
    }
    
    // If we couldn't determine the NFL team, return 'Unknown'
    if (empty($nfl_team) || $nfl_team === null || $nfl_team === '') {
        return 'Unknown';
    }
    
    // Load bye week data from JSON file
    $json_file = get_stylesheet_directory() . '/nfl-bye-weeks/bye_weeks_' . $year . '.json';
    
    if (!file_exists($json_file)) {
        return 'No Data';
    }
    
    $json_data = file_get_contents($json_file);
    $bye_data = json_decode($json_data, true);
    
    if (!isset($bye_data['bye_weeks'])) {
        return 'No Data';
    }
    
    // Check if the NFL team is on bye for this specific week
    foreach ($bye_data['bye_weeks'] as $bye_week) {
        if ($bye_week['week'] == $week) {
            if (in_array($nfl_team, $bye_week['teams'])) {
                return 'Yes';
            }
        }
    }
    
    return 'No';
}


// Find players who claim to have played for a team but aren't in the team's lineup
function get_unaccounted_players($team_abbr, $weekid, $lineup_players) {
    global $wpdb;
    
    $unaccounted = array();
    
    // Get all players whose player table shows they played for this team this week
    $all_players = $wpdb->get_results("SELECT p_id FROM wp_players", ARRAY_A);
    
    foreach ($all_players as $player) {
        $pid = $player['p_id'];
        
        // Check if player table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $pid
        ));
        
        if (!$table_exists) continue;
        
        // Check if this player's table shows they played for this team this week
        $player_team_data = $wpdb->get_results($wpdb->prepare(
            "SELECT team, points FROM `{$pid}` WHERE week_id = %s AND team = %s",
            $weekid,
            $team_abbr
        ), ARRAY_A);
        
        // If player says they played for this team
        if (!empty($player_team_data)) {
            // Check if they're in the lineup
            if (!in_array($pid, $lineup_players)) {
                // Get player name
                $player_info = $wpdb->get_row($wpdb->prepare(
                    "SELECT playerFirst, playerLast, position FROM wp_players WHERE p_id = %s",
                    $pid
                ), ARRAY_A);
                
                $unaccounted[] = array(
                    'pid' => $pid,
                    'name' => $player_info['playerFirst'] . ' ' . $player_info['playerLast'],
                    'position' => $player_info['position'],
                    'points' => $player_team_data[0]['points']
                );
            }
        }
    }
    
    return $unaccounted;
}

// gets the years a player played from the player table 
function get_player_years_played($pid) {
	global $wpdb;
	$getplayer = $wpdb->get_results("select year from $pid", ARRAY_N);
	
	foreach ($getplayer as $key => $revisequery){
		$player_the_years[] = $revisequery[0];
	}
    if($player_the_years):
        return array_unique($player_the_years);
    endif;
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

function get_player_points_team_season($pid, $team, $season) {
    global $wpdb;
    $getplayer = $wpdb->get_results("select * from $pid where team = '$team' AND year = '$season'", ARRAY_N);

    foreach ($getplayer as $key => $revisequery):
        $playerstats[$revisequery[0]] = $revisequery[3];
    endforeach;

    return $playerstats;
}


// gets the PFR URL for a player
function get_player_pfr_url($pid) {
	global $wpdb;
	$getplayer = $wpdb->get_results("select pfruri, playerLast from wp_players where p_id = '$pid'", ARRAY_N);
	if (!empty($getplayer)) {
		$pfruri = $getplayer[0][0];
		$lastname = $getplayer[0][1];
		if (!empty($pfruri) && !empty($lastname)) {
			$first_initial = strtoupper(substr($lastname, 0, 1));
			return 'https://www.pro-football-reference.com/players/' . $first_initial . '/' . $pfruri . '.htm';
		}
	}
	return '';
}

// gets the PFR boxscore URL for a player's game in a specific week
function get_player_game_pfr_url($pid, $weekid) {
	global $wpdb;
	$playerdata = $wpdb->get_results("select game_date, nflteam, game_location, nflopp from $pid where week_id = '$weekid'", ARRAY_N);
	if (!empty($playerdata) && !empty($playerdata[0])) {
		$game_date = $playerdata[0][0];
		$nflteam = $playerdata[0][1];
		$game_location = $playerdata[0][2];
		$nflopp = $playerdata[0][3];
		
		if (!empty($game_date) && !empty($nflteam) && !empty($nflopp) && !empty($game_location)) {
			// Format: YYYYMMDD0{hometeam}.htm
			$date_formatted = str_replace('-', '', $game_date);
			// Home team is the one with 'vs' (home), away is '@'
			$home_team = ($game_location == 'vs' || $game_location == 'V') ? strtolower($nflteam) : strtolower($nflopp);
			return 'https://www.pro-football-reference.com/boxscores/' . $date_formatted . '0' . $home_team . '.htm';
		}
	}
	return '';
}

function get_player_basic_info($pid){
	global $wpdb;
	$getplayer = $wpdb->get_results("select * from wp_players where p_id = '$pid'", ARRAY_N);
	$rookieyear = get_player_rookie_year($pid);
	foreach ($getplayer as $key => $revisequery){
		$playerinfo[] = array(
			'pid' => $revisequery[0],
			'first' => $revisequery[1], 
			'last' => $revisequery[2], 
			'position' => $revisequery[3],  
			'rookie' => $rookieyear, // revised in 2023 to include wp_rosters
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

// Get Mr Irrelevant data - last draft pick from each season
function mr_irrelevant_table(){
	global $wpdb;
	$getdata = $wpdb->get_results("SELECT year, round, picknum, team, playerfirst, playerlast, pos, IFNULL(playerid, '') as playerid FROM wp_drafts WHERE id IN (SELECT MAX(id) FROM wp_drafts GROUP BY year) ORDER BY year", ARRAY_A);
	return $getdata;
}

// debugging datatbase conncetions
function wpdb() {
    global $wpdb;
    return $wpdb;
}
function var_dump_database() {
    var_dump(wpdb()->num_queries , wpdb()->queries);
}

function getLastNotNullValueInArray($array)
{
    $reversed = array_reverse($array);
    foreach($reversed as $arrValue)
    {
        if($arrValue)
            return  $arrValue;
    }
    return false;
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

// gets teams that were active in a specific season
function get_teams_by_season($season){
    $allteams = get_teams();
    $activeteams = array();
    
    foreach ($allteams as $teamid => $teamdata){
        $first_season = $teamdata['first_season'];
        $folded = $teamdata['folded'];
        
        // Team is active if season >= first_season AND (folded is empty OR season < folded)
        if ($season >= $first_season && ($folded == '' || $folded == null || $season < $folded)){
            $activeteams[$teamid] = $teamdata;
        }
    }
    
    return $activeteams;
}

// gets team PFL & MFL IDs by Season
// 2011 was the first season that we used MFL
function get_pfl_mfl_ids_season(){
    $pflmflids = array(
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
            '0010' => 'BUL',
            '0011' => '',
            '0012' => ''
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
            '0010' => 'BUL',
            '0011' => '',
            '0012' => ''
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
            '0010' => 'BUL',
            '0011' => '',
            '0012' => ''
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
            '0010' => 'BUL',
            '0011' => '',
            '0012' => ''
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
            '0010' => 'BUL',
            '0011' => '',
            '0012' => ''
        ),
        2021 => array(
            '0001' => 'TSG',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'DST',
            '0006' => 'BST',
            '0007' => 'SNR',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL',
            '0011' => '',
            '0012' => ''
        ),
        2022 => array(
            '0001' => 'TSG',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'DST',
            '0006' => 'BST',
            '0007' => 'SNR',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL',
            '0011' => '',
            '0012' => ''
        ),
        2023 => array(
            '0001' => 'TSG',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'DST',
            '0006' => 'BST',
            '0007' => 'SNR',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL',
            '0011' => '',
            '0012' => ''
        ),
        2024 => array(
            '0001' => 'TSG',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'DST',
            '0006' => 'BST',
            '0007' => 'SNR',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL',
            '0011' => '',
            '0012' => ''
        ),
        2025 => array(
            '0001' => 'TSG',
            '0002' => 'ETS',
            '0003' => 'PEP',
            '0004' => 'WRZ',
            '0005' => 'DST',
            '0006' => 'BST',
            '0007' => 'SNR',
            '0008' => 'HAT',
            '0009' => 'CMN',
            '0010' => 'BUL',
            '0011' => '',
            '0012' => ''
        )
    );
    return $pflmflids;
}



// gets team stadium name 
function get_stadium($teamid){
	$teams = get_teams();
	foreach ($teams as $key => $val){
		$stadiums[$val['id']] = $val['stadium'];
	}
	
	return $stadiums;
}

// gets team stadium name
function get_stadium_by_team($teamid){
    $teams = get_teams();
    foreach ($teams as $key => $val){
        $stadiums[$key] = $val['stadium'];
    }

    return $stadiums[$teamid];
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

    if($passyd == ''):
        $passyd = 0;
    endif;
    if($passtd == ''):
        $passtd = 0;
    endif;

	if($year == 1991):
		$pass_get = floor($passyd / 50);
		if($pass_get < 0){
			$passdata = 0;
		} else {
			$passdata = $pass_get;
		}
		
		$posscore = $passdata + (floor($rushyd / 25)) + (($passtd + $rushtd + $rectd) * 2) + floor($recyd / 25) - $int;
		return $posscore;
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
    if($xpm == ''):
        $xpm = 0;
    endif;
    if($fgm == ''):
        $fgm = 0;
    endif;

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
    if($p):
        foreach ($p as $k => $v){
            $thecount[] = $v['points'];
        }
        return array_sum($thecount);
    endif;
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
	// Use numeric comparison to handle different precision levels
	$get = $wpdb->get_results("select * from wp_player_pvqs where CAST(pvq AS DECIMAL(10,8)) >= 0.99999999", ARRAY_N);
	
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

function get_numbers_by_season($pid){
    global $wpdb;
    $numbers = $wpdb->get_results("select numberarray from wp_players where p_id = '$pid'", ARRAY_N);
    $arrnumbers = json_decode($numbers[0][0]);
    return $arrnumbers;
}

// gets the cumulative career stats from the player table
function get_player_career_stats($pid){
	
	$data_array = get_player_data($pid);
		
	if(!empty($data_array)){
		foreach ($data_array as $get){
			$pointsarray[] = $get['points'];
			$yeararray[] = $get['year'];
			$gamearray[] = $get['win_loss'];
			$passyardsarray[] = $get['pass_yds'];
            $passtdarray[] = $get['pass_td'];
            $passintarray[] = $get['pass_int'];
            $rushydsarray[] = $get['rush_yds'];
            $rushtdarray[] = $get['rush_td'];
            $recydsarray[] = $get['rec_yds'];
            $rectdarray[] = $get['rec_td'];
            $xpmarray[] = $get['xpm'];
            $xpaarray[] = $get['xpa'];
            $fgmarray[] = $get['fgm'];
            $fgaarray[] = $get['fga'];
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
        $passingyds  = array_sum($passyardsarray);
        $passingtds = array_sum($passtdarray);
        $passingint = array_sum($passintarray);
        $rushyrds = array_sum($rushydsarray);
        $rushtds = array_sum($rushtdarray);
        $recyds = array_sum($recydsarray);
        $rectds = array_sum($rectdarray);
        $xpm = array_sum($xpmarray);
        $xpa = array_sum($xpaarray);
        $fgm = array_sum($fgmarray);
        $fga = array_sum($fgaarray);
		
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
            'numbersseason' => get_numbers_by_season($pid),
            'pointsmilestone' => $ptscum,
			'winmilestone' => $wcum,
			//'avgpvq' => array_sum($pvqflat)/count($pvqflat),
			'possebowlwins' => $pbwins_index,
            'careerposrank' => get_player_career_rank($pid),
            'passingyards' => $passingyds,
            'passingtds' => $passingtds,
            'passingint' => $passingint,
            'rushyrds' => $rushyrds,
            'rushtds' => $rushtds,
            'recyrds' => $recyds,
            'rectds' => $rectds,
            'xpm' => $xpm,
            'xpa' => $xpa,
            'fgm' => $fgm,
            'fga' => $fga
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

function get_player_career_points($pid){
    $stats = get_player_career_stats($pid);
    return $stats['points'];
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

function get_player_by_pick($pickid){
    $drafts = get_drafts();
    foreach($drafts as $key => $value):
        $newarray[$value['season'].'.'.$value['round'].'.'.$value['pick']] = $value;
    endforeach;
    //return $newarray;
    $player = $newarray[$pickid];
    return $player['playerid'];
}

function get_draft_player_team ($pid, $season){
    global $wpdb;
    $get = $wpdb->get_results("select * from wp_drafts where year = '$season' && playerid = '$pid'", ARRAY_N);

    foreach ($get as $getdraft) {
        $teampick = $getdraft[6];
    }
    return $teampick;
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

function get_draft_info(){
    $post_fields = get_fields( 195 );
    return $post_fields;
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

function insert_roster($pid, $teamid, $season){
    global $wpdb;
    $dbid = $pid.$season.$teamid;
    $wpdb->insert(
        'wp_rosters',
        array(
            'id' => $dbid,
            'pid' => $pid,
            'year' => $season,
            'team' => $teamid
        ),
        array(
            '%s','%s','%d','%s'
        )
    );
}

function get_all_rosters(){
    global $wpdb;
    $get = $wpdb->get_results("select * from wp_rosters", ARRAY_N);

    foreach ($get as $revisequery){
        $rosters[$revisequery[0]] = array(
            'id' => $revisequery[0],
            'pid' => $revisequery[1],
            'year' => $revisequery[2],
            'team' => $revisequery[3]
        );
    }

    return $rosters;
}

function get_rostered_player($pid){
    $rosters = get_all_rosters();
    foreach($rosters as $key => $value):
        if($value['pid'] == $pid):
            $rostered[$key] = $value;
        endif;
    endforeach;
    return $rostered;
}

function get_rostered_player_by_season($pid, $season){
    $rosters = get_all_rosters();
    foreach($rosters as $key => $value):
        if($value['pid'] == $pid && $value['year'] == $season):
            $rostered[$key] = $value;
        endif;
    endforeach;
    return $rostered;
}

//This returns the teams that players played a game
function get_player_teams_by_season($pid){
    $years = the_seasons();
    foreach ($years as $season):
        $playerdata = get_player_season_stats($pid, $season);
        if ($playerdata['teams'] != null) {
            $teams[$season] = array_unique($playerdata['teams']);
        }
    endforeach;
    if($teams):
        foreach($teams as $key => $value):
            foreach($value as $team):
                insert_roster($pid, $team, $key);
            endforeach;
        endforeach;
    endif;
    return $teams;
}

//get only player ids of players that have played for a team
function get_player_ids_by_team($team){
    $playerteam = get_players_played_by_team($team);
    foreach ($playerteam as $key => $value):
        $newer[] = array_filter_recursive($value, null);
    endforeach;

    $newqb1[] = array_unique(array_column($newer, 'qb1'));;
    $newqb2[] = array_unique(array_column($newer, 'qb2'));;
    $newrb1[] = array_unique(array_column($newer, 'rb1'));;
    $newrb2[] = array_unique(array_column($newer, 'rb2'));;
    $newwr1[] = array_unique(array_column($newer, 'wr1'));;
    $newwr2[] = array_unique(array_column($newer, 'wr2'));;
    $newpk1[] = array_unique(array_column($newer, 'pk1'));;
    $newpk2[] = array_unique(array_column($newer, 'pk2'));;

    $merge = array_merge($newqb1, $newqb2, $newrb1, $newrb2, $newwr1, $newwr2, $newpk1, $newpk2);
    $flattened = array_reduce($merge, function($carry, $item) {
        return array_merge($carry, is_array($item) ? $item : [$item]);
    }, []);
    $teamarray = array_diff($flattened, array('None', '', null, '0', '[Null]', 'null'));

    return $teamarray;

}

// This returns teams for seasons where players were ROSTERED.  Not quite the same as the function get_player_teams_by_season
function get_player_teams_rostered_by_season ($pid){
    $rostered = get_rostered_player($pid);
        if($rostered):
            foreach($rostered as $key => $value):
                $teams[$value['year']][] = $value['team'];
            endforeach;
        endif;
    return $teams;
}

//This checks if a player is listed as rostered in a specific week.
//If they played that week it will add them to the wp_rostered table.

function check_player_rostered($pid, $year){
    global $wpdb;
    $get_player_rostered = $wpdb->get_results("select * from wp_rosters where year = $year && pid = '$pid';", ARRAY_N);
    $yearsplayed = get_player_years_played($pid);
    $seasonsplayed = get_player_teams_by_season($pid);
    if($yearsplayed):
        if(in_array($year, $yearsplayed)):
            $played = 'played';
        else:
            $played = 'not played';
        endif;
        if($get_player_rostered):
            $rostered = 'rostered';
        else:
            $rostered =  'not rostered';
        endif;
        if($played == 'played' & $rostered == 'not rostered'):
            foreach($seasonsplayed[$year] as $team):
                insert_roster($pid, $team, $year);
            endforeach;
            $inserted = 'inserted';
        endif;
    endif;
    return $played.' '.$rostered.' '.$inserted;
}

// This new function created in 2023 accounts for the fact that players may have been on teams but not played at all
// before the year listed in their PID.  This leverages the new wp_rosters table which is more accurate and flexible
// in defining the players rookie year.  No need to change PIDs of players.
function get_player_rookie_year($pid){
    $rostered = get_rostered_player($pid);
    if($rostered):
        $rook = reset($rostered);
    endif;
    return $rook['year'];
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

// get all rosters by season
function get_rosters($year, $team){

    global $wpdb;
    $get_rosters = $wpdb->get_results("select * from wp_rosters where year = '$year' AND team = '$team'", ARRAY_N);

    foreach ($get_rosters as $revisequery){
        $pos = pid_to_position($revisequery[1]);
        $rosters_all[$pos][] = $revisequery[1];
    }

    return $rosters_all;
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


// Convert IDs of teams to full name or just list all teams by ID or name.
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

function team_long($teamid){
    $teamlist = teamlist();
    $longname = $teamlist[$teamid];
    return $longname;
}

function get_all_team_data()
{
    global $wpdb;

    $RBS = $wpdb->get_results("select * from wp_team_RBS", ARRAY_N);
    $ETS = $wpdb->get_results("select * from wp_team_ETS", ARRAY_N);
    $PEP = $wpdb->get_results("select * from wp_team_PEP", ARRAY_N);
    $WRZ = $wpdb->get_results("select * from wp_team_WRZ", ARRAY_N);
    $CMN = $wpdb->get_results("select * from wp_team_CMN", ARRAY_N);
    $BUL = $wpdb->get_results("select * from wp_team_BUL", ARRAY_N);
    $SNR = $wpdb->get_results("select * from wp_team_SNR", ARRAY_N);
    $TSG = $wpdb->get_results("select * from wp_team_TSG", ARRAY_N);
    $BST = $wpdb->get_results("select * from wp_team_BST", ARRAY_N);
    $MAX = $wpdb->get_results("select * from wp_team_MAX", ARRAY_N);
    $PHR = $wpdb->get_results("select * from wp_team_PHR", ARRAY_N);
    $SON = $wpdb->get_results("select * from wp_team_SON", ARRAY_N);
    $ATK = $wpdb->get_results("select * from wp_team_ATK", ARRAY_N);
    $HAT = $wpdb->get_results("select * from wp_team_HAT", ARRAY_N);
    $DST = $wpdb->get_results("select * from wp_team_DST", ARRAY_N);

    $teamarrays = array (
        'RBS' => $RBS,
        'ETS' => $ETS,
        'PEP' => $PEP,
        'WRZ' => $WRZ,
        'CMN' => $CMN,
        'BUL' => $BUL,
        'SNR' => $SNR,
        'TSG' => $TSG,
        'BST' => $BST,
        'MAX' => $MAX,
        'PHR' => $PHR,
        'SON' => $SON,
        'ATK' => $ATK,
        'HAT' => $HAT,
        'DST' => $DST
    );

    return $teamarrays;
}

function schedule_by_week(){
    $theweeks = the_weeks();
    $teamdata = get_all_team_data();

    foreach($teamdata as $team => $theweek):
        foreach ($theweek as $key => $value):
            $week = $value[0];
            $hometeam = $value[3];
            $homescore = $value[4];
            $roadteam = $value[5];
            $roadscore = $value[6];
            $loc = $value[7];  // gets H or A value
            $stad = $value[8];
            if($loc == 'H'):
                $schedule[$week][] = array(
                    'hometeam' => $hometeam,
                    'homescore' => $homescore,
                    'roadteam' => $roadteam,
                    'roadscore' => $roadscore,
                    'stadium' => $stad
                );
            endif;
        endforeach;
    endforeach;

    ksort($schedule);

    return $schedule;
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
					//$printit .= '<th class="min-width">Career PTS</th>';
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
                        else:
                            $seasonpoints = 'Never Played';
						endif;	
						$printit .= '<td class="text-center"><span class="">'.$position.'</span></td>';
						$printit .= '<td class="text-center"><span class="">'.$seasonpoints.'</span></td>';
						//$printit .= '<td class="text-center"><span class="">'.$careerdraftvalue['points'].'</span></td>';  replaced by more logical ai python script in 2025
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

// get list of players who never played in the PFL but were at one point activly rostered.
function get_non_pfl_players(){
    global $wpdb;
    $get = $wpdb->get_results("select * from wp_rosters_nopid", ARRAY_N);
    foreach ($get as $getplayers){
        $players[$getplayers[0]] = array(
            'id' => $getplayers[0],
            'firstname' => $getplayers[1],
            'lastname' => $getplayers[2],
            'position' => $getplayers[3],
            'team' => $getplayers[4]
        );
    }
    return $players;
}

function nonpid_to_name($nonpid, $var){
    $players = get_non_pfl_players();
    $player = $players[$nonpid];
    if($var == 0):
        $printname = $player['firstname'].' '.$player['lastname'];
    endif;
    if($var == 1):
        $printname = $player['firstname'].' '.$player['lastname'].' *';
    endif;
    return $printname;
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

// Get position leader(s) for a season including ties
function get_position_leader($season, $position){
	$season_leaders = get_season_leaders($season);
	$playersassoc = get_players_assoc();
	
	if(!$season_leaders){
		return null;
	}
	
	// Filter by position and sort by points
	$position_leaders = array();
	foreach ($season_leaders as $leader){
		if(substr($leader['playerid'], -2) == $position){
			$position_leaders[$leader['playerid']] = $leader['points'];
		}
	}
	
	if(empty($position_leaders)){
		return null;
	}
	
	// Sort by points descending
	arsort($position_leaders, SORT_NUMERIC);
	
	// Get the highest point value
	$max_points = reset($position_leaders);
	
	// Get all players with that point value (handles ties)
	$leaders = array();
	foreach($position_leaders as $pid => $points){
		if($points == $max_points){
			// Get player's team for that season
			$player_stats = get_player_season_stats($pid, $season);
			$teams = $player_stats['teams'];
			$team = is_array($teams) ? $teams[0] : $teams;
			
			$leaders[] = array(
				'pid' => $pid,
				'first' => $playersassoc[$pid][0],
				'last' => $playersassoc[$pid][1],
				'team' => $team,
				'points' => $points
			);
		}
	}
	
	return $leaders;
}

// Get division winners for a season
function get_division_winners($season){
	$standings = get_standings($season);
	$teamids = $_SESSION['teamids'];
	
	if(!$standings){
		return array('EGAD' => null, 'DGAS' => null, 'MGAC' => null);
	}
	
	$division_winners = array();
	
	// Group teams by division
	$divisions = array();
	foreach ($standings as $team){
		$division = $team['division'];
		if(!isset($divisions[$division])){
			$divisions[$division] = array();
		}
		$divisions[$division][] = $team;
	}
	
	// Find winner in each division (team with gb == 0)
	foreach ($divisions as $div_name => $teams){
		foreach ($teams as $team){
			if($team['gb'] == 0){
				$team_id = $team['teamid'];
				$team_name = isset($teamids[$team_id]) ? $teamids[$team_id] : $team_id;
				$division_winners[$div_name] = $team_name;
				break; // Found the winner for this division
			}
		}
	}
	
	return $division_winners;
}

// Get player(s) with highest PVQ for a season (handles ties)
function get_highest_pvq_player($season){
	global $wpdb;
	$playersassoc = get_players_assoc();
	
	// Query all PVQ data for the season and find the max
	$getpvq = $wpdb->get_results("SELECT * FROM wp_player_pvqs WHERE year = '$season' ORDER BY CAST(pvq AS DECIMAL(10,8)) DESC", ARRAY_N);
	
	if(empty($getpvq)){
		return null;
	}
	
	// Get the highest PVQ value (first row after ordering)
	$max_pvq = floatval($getpvq[0][3]);
	
	// Get all players with that PVQ value (handles ties)
	$leaders = array();
	foreach($getpvq as $row){
		$pid = $row[1];
		$pvq_value = floatval($row[3]);
		
		// Only include players with the maximum PVQ
		if(abs($pvq_value - $max_pvq) < 0.0001){
			// Check if player exists in playersassoc
			if(!isset($playersassoc[$pid])){
				// Try to find the player by matching the last 6 characters (name+position)
				// e.g., 1991CarnPK should match 1991CarnPK
				$name_suffix = substr($pid, 4); // Get everything after the year
				$found_pid = null;
				foreach($playersassoc as $key => $value){
					if(substr($key, 4) == $name_suffix){
						$found_pid = $key;
						break;
					}
				}
				if($found_pid){
					$pid = $found_pid; // Use the correct player ID
				} else {
					continue; // Skip this player if still not found
				}
			}
			
			// Get player's team for that season
			$player_stats = get_player_season_stats($pid, $season);
			$teams = isset($player_stats['teams']) ? $player_stats['teams'] : null;
			$team = is_array($teams) ? $teams[0] : $teams;
			
			$leaders[] = array(
				'pid' => $pid,
				'first' => $playersassoc[$pid][0],
				'last' => $playersassoc[$pid][1],
				'team' => $team,
				'pvq' => $pvq_value
			);
		} else {
			// Since we're sorted descending, once we hit a lower value we can break
			break;
		}
	}
	
	return $leaders;
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

function one_player_mfl_to_pfl($mflid){
    global $wpdb;
    $query = $wpdb->get_results("SELECT p_id, mflid FROM wp_players" );

    foreach ($query as $val){
        if(!empty($val->mflid)){
            $theids[$val->mflid] = $val->p_id;
        }
    }
    arsort($theids);
    return $theids[$mflid] ;
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

    // check that the API key hasn't changed.  Updated in 2024.
    // 2022 Updated Request Curl
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www58.myfantasyleague.com/$year/export?TYPE=playerScores&L=38954&W=$week&YEAR=$year&PLAYERS=$mflid&JSON=1&APIKEY=aRNp1sySvuWqx0CmO1HIZDYeFbox",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Cookie: MFL_PW_SEQ=ah9q2M6Ss%2Bis3Q29; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D'
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
	sleep(2);
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
        'position' 	=>  $plarray[$playerid][3],
        'scorediff' =>  $array[$weekid][26]
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
    $pos = pid_to_position($pid);
    // Full Name
    if($init == 0):
        $t = $info['first'].' '.$info['last'];
    endif;
    // F. Name
    if($init == 1):
        $t = substr($info['first'], 0, 1).'. '.$info['last'];
    endif;
    // Full Name, QB
    if($init == 2):
        if($pos):
            $t = $info['first'].' '.$info['last'].', '.$pos;
        endif;
    endif;
    return $t;
}

function pid_to_position($pid){
    $pos = substr($pid, -2);
    return $pos;
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

// get player leaders by team and position
function get_player_leaders_by_team($teamid, $position){
    $playersall = get_players_assoc();
    foreach ($playersall as $key => $val) {
        if($val):
            $careerstats_team[$key] = get_player_career_stats_team($key, $teamid);
        endif;
    }
    foreach ($careerstats_team as $k => $v):
        $pos = pid_to_position($k);
        if($v['points']):
            if($position == $pos):
                $output[$k] = $v['points'];
            endif;
        endif;
    endforeach;
    arsort($output);

    return $output;
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

function get_team_boxscore_by_week($weekid, $teamid){
    $boxscore = get_boxscore_by_week($weekid);
    return $boxscore[$teamid];
}

function get_players_by_week($weekid){
    global $wpdb;
    $teams = get_teams();

    foreach ($teams as $key => $value){
        $revisequery = $wpdb->get_results("select * from wp_team_$key where id = '$weekid'", ARRAY_N);
        if($revisequery):
            $ot = $revisequery[0][14];
            $boxscoreplayerweek[$key] = array(
                'qb1' => $revisequery[0][10],
                'rb1' => $revisequery[0][11],
                'wr1' => $revisequery[0][12],
                'pk1' => $revisequery[0][13],
                'qb2' => $revisequery[0][15],
                'rb2' => $revisequery[0][16],
                'wr2' => $revisequery[0][17],
                'pk2' => $revisequery[0][18]
            );
        endif;
    }

    return $boxscoreplayerweek;
}

function get_flat_players_by_week($weekid){
    $flatplayers = array();
    $data = get_players_by_week($weekid);
    foreach ($data as $team => $value):
        foreach ($value as $key => $val):
            if($val):
                $flatplayers[] = $val;
            endif;
        endforeach;
    endforeach;
    return $flatplayers;
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

function get_team_score_by_week($weekid){
    global $wpdb;
    $teams = get_teams();

    foreach ($teams as $key => $value){
        $revisequery = $wpdb->get_results("select * from wp_team_$key where id = '$weekid'", ARRAY_N);
        if($revisequery):
            $boxscoreweek[$key] = $revisequery[0][4];
        endif;
    }
   // arsort($boxscoreweek);
    return $boxscoreweek;
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

function get_championships_by_player ($playerid){
    $ships = get_player_champions();
    if($ships):
        foreach ($ships as $key => $value):
            if($value == $playerid):
                $output[$key] = $value;
            endif;
        endforeach;
        if($output):
            foreach ($output as $key => $value):
                $year = substr($key, 0, 4);
                $team = substr($key, 4, 3);
                $newchamps[$year] = $team;
            endforeach;
        endif;
    endif;
    return $newchamps;
}

function get_drafted_by_player($playerid){
    $drafts = get_drafts();
    foreach ($drafts as $key => $value):
        $newdraft[$key] = array(
            'player' => $value['playerid'],
            'team' => $value['acteam'],
            'season' => $value['season'],
            'round' => $value['round'],
            'pick' => $value['pick'],
            'overall' => $value['overall']
        );
    endforeach;
    foreach ($newdraft as $key => $value):
        if($value['player'] == $playerid):
            $output[$key] = $value;
        endif;
    endforeach;
    return $output;
}

function get_number_ones_by_player ($playerid){
    $numberones = get_number_ones();
    foreach ($numberones as $key => $value):
        if($value['playerid'] == $playerid):
            $output[$value['year']] = $value['teams'];
        endif;
    endforeach;
    return $output;
}

function get_career_high_player($playerid){
    $stats = get_player_career_stats($playerid);
    return $stats;
}

//get extended view of player career with details
function get_player_complete_history($playerid) {
    $playertable = get_table($playerid);
    foreach ($playertable as $key => $value):
        $gamestats[$value[0]] = array(
            'weekid' => $value[0],
            'year' => $value[1],
            'week' => $value[2],
            'points' => $value[3],
            'team' => $value[4],
            'versus' => $value[5],
//          '' => $value[6], player id not used
            'result' => $value[7],
            'location' => $value[8],
            'stadium' => $value[9],
            'date' => $value[10],
            'nflteam' => $value[11],
            'nfllocation' => $value[12],
            'nflversus' => $value[13],
            'passyards' => $value[14],
            'passtds' => $value[15],
            'passints' => $value[16],
            'rushyards' => $value[17],
            'rushtds' => $value[18],
            'recyards' => $value[19],
            'rectds' => $value[20],
            'xpm' => $value[21],
            'xpa' => $value[22],
            'fgm' => $value[23],
            'fga' => $value[24],
            'nflscore' => $value[25],
            'scoredifference' => $value[26]
        );
    endforeach;
    $generalcareer = get_career_high_player($playerid);
    $protections = get_protections_player($playerid);
    $traded = get_trade_by_player($playerid);
    $awards =  get_player_award($playerid);
   // $slams = get_grandslams(); for later
    $champs = get_championships_by_player($playerid);
    $drafts = get_drafted_by_player($playerid);
    $potw = get_player_of_the_week_by_player($playerid);
    $numberones = get_number_ones_by_player($playerid);
    $gamestreak = get_player_game_streak($playerid);
    $playoffs = playerplayoffs($playerid);
    if($playoffs):
        foreach ($playoffs as $key => $value):
            $id = $value['year'].$value['week'];
            $reviseplayoffs[$id] = $value;
        endforeach;
    endif;
    $probowl = probowl_boxscores_player($playerid);
    if($probowl):
        foreach ($probowl as $key => $value):
            $id = $value['year'].'17';
            $revisedprobowl[$id] = $value;
        endforeach;
    endif;

    $finaloutput = array(
        'generalcareer' => $generalcareer,
        'weeksplayed' => $gamestats,
        'protections' => $protections,
        'traded' => $traded,
        'awards' => $awards,
        'grandslams' => $slams,
        'championships' => $champs,
        'drafts' => $drafts,
        'potw' => $potw,
        'numberones' => $numberones,
        'gamestreak' => $gamestreak,
        'playoffs' => $reviseplayoffs,
        'probowl' => $revisedprobowl

    );

    return $finaloutput;
}

//get just players played for team
function get_players_played_by_team($teamid){
    $results = get_team_results_expanded_new($teamid);
    foreach ($results as $key => $value):
        $output[$key] = array(
            'qb1' => $value['qb1'],
            'rb1' => $value['rb1'],
            'wr1' => $value['wr1'],
            'pk1' => $value['pk1'],
            'qb2' => $value['qb2'],
            'rb2' => $value['rb2'],
            'wr2' => $value['wr2'],
            'pk2' => $value['pk2']
        );
    endforeach;
    return $output;
}

function array_filter_recursive( array $array, callable $callback = null ) {
    $array = is_callable( $callback ) ? array_filter( $array, $callback ) : array_filter( $array );
    foreach ( $array as &$value ) {
        if ( is_array( $value ) ) {
            $value = call_user_func( __FUNCTION__, $value, $callback );
        }
    }

    return $array;
}

//format number as ordinal
function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}

// get or set a general transient from any array.  Set expiration in seconds.
function get_or_set($array, $name, $seconds){
    $transient = get_transient( $array.'_trans' );
    if( ! empty( $transient ) ) {
        echo '<script>console.log("'.$name.' - From Transient");</script>';
        return $transient;
    } else {
        $set = $array;
        set_transient( $name.'_pfl_trans', $set, $seconds );
        echo '<script>console.log("'.$name.' - Transient Set");</script>';
        return $set;
    }
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

function format_draft_pick_return($pickvar){
    if($pickvar != ''){
        $ex = explode('.', $pickvar);
        $output = $ex[0].' '.ordinal($ex[1]).' Round Pick ('.$ex[1].'.'.$ex[2].')<br/>';
    }
    return $output;
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

function get_player_of_the_week_by_player($playerid){
    $potw = get_player_of_week();
    foreach ($potw as $key => $value):
        if($value == $playerid):
            $output[$key] = $value;
        endif;
    endforeach;
    return $output;
}

function all_nfl_teams(){
    $array = array(
        'IND' => 'Colts',
        'GNB' => 'Packers',
        'PHI' => 'Eagles',
        'NWE' => 'Patriots',
        'MIN' => 'Vikings',
        'ATL' => 'Falcons',
        'DAL' => 'Cowboys',
        'DEN' => 'Broncos',
        'BUF' => 'Bills',
        'SFO' => '49ers',
        'NOR' => 'Saints',
        'CIN' => 'Bengals',
        'KAN' => 'Chiefs',
        'SEA' => 'Seahawks',
        'DET' => 'Lions',
        'PIT' => 'Steelers',
        'ARI' => 'Cardinals',
        'STL' => 'Rams',
        'SDG' => 'Chargers',
        'HOU' => 'Oilers/Texans',
        'MIA' => 'Dolphins',
        'NYG' => 'Giants',
        'BAL' => 'Ravens',
        'WAS' => 'Skins/Commies',
        'CHI' => 'Bears',
        'CAR' => 'Panthers',
        'OAK' => 'Raiders',
        'TEN' => 'Titans',
        'JAX' => 'Jaguars',
        'TAM' => 'Buccaneers',
        'NYJ' => 'Jets',
        'CLE' => 'Browns',
        'LAR' => 'Rams',
        'LAC' => 'Chargers',
        'LVR' => 'Raiders',
        'RAM' => 'Rams',
        'RAI' => 'Raiders',
        'PHO' => 'Cardinals'
    );
    return $array;
}

function all_nfl_teams_flipped(){
    $array = array(
        'Colts' => 'IND',
        'Packers' => 'GNB',
        'Eagles' => 'PHI',
        'Patriots' => 'NWE',
        'Vikings' => 'MIN',
        'Falcons' => 'ATL',
        'Cowboys' => 'DAL',
        'Broncos' => 'DEN',
        'Bills' => 'BUF',
        '49ers' => 'SFO',
        'Saints' => 'NOR',
        'Bengals' => 'CIN',
        'Chiefs' => 'KAN',
        'Seahawks' => 'SEA',
        'Lions' => 'DET',
        'Steelers' => 'PIT',
        'Cardinals' => 'ARI',
        'SL Rams' => 'STL',
        'Chargers' => 'SDG',
        'Oilers' => 'HOU',
        'Texans' => 'HOU',
        'Dolphins' => 'MIA',
        'Giants' => 'NYG',
        'Ravens' => 'BAL',
        'Redskins' => 'WAS',
        'Commanders' => 'WAS',
        'Bears' => 'CHI',
        'Panthers' => 'CAR',
        'OAK Raiders' => 'OAK',
        'LA Raiders' => 'LAR',
        'LV Raiders' => 'LVR',
        'Titans' => 'TEN',
        'Jaguars' => 'JAX',
        'Buccaneers' => 'TAM',
        'Jets' => 'NYJ',
        'Browns' => 'CLE',
        'Rams' => 'LAR'
       // 'Chargers' => 'LAC'
    );
return $array;
}

function get_nfl_full_team_name_from_id($tid){
    $array = all_nfl_teams();
    $myteam = $array[$tid];
    return $myteam;
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

// Get Player of the Week for just the Playoffs - Week 15 in wp_player_of_week table
function potw_playoffs() {
    $potw = get_player_of_week();
    foreach ($potw as $key => $value):
        $weekst = substr($key, -2);
        if($weekst == 15):
            $playoffpotw[$key] = $value;
        endif;
    endforeach;
    return $playoffpotw;
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

	$pass = 0; $rush = 0; $rec = 0; $tds = 0; $kicks = 0; $tpc = 0; $o = 0;
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

// Functions needed to convert the HTML table we get back from PFR into a CSV
function tdrows($elements)
{
    $str = "";
    foreach ($elements as $element) {
        $str .= $element->nodeValue . ", ";
    }

    return $str;
}

function getdata($contents)
{
    $DOM = new DOMDocument;
    $DOM->loadHTML($contents);

    $items = $DOM->getElementsByTagName('tr');

    foreach ($items as $node) {
        $storedata[] = tdrows($node->childNodes);
    }
    return $storedata;
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

# format player linescore for display underneath player name in boxscore
function format_player_linescore($playerid, $weekvar) {
	// weekvar is already in YEARWEEK format (e.g., 202103)
	$week_id = $weekvar;
	
	// Check if player is 'None' or invalid
	if (!$playerid || $playerid == 'None' || strtolower($playerid) == 'none') {
		return '<div style="font-size: 14px; color: #999999; line-height: 1.2em; margin-bottom: 5px;">0</div>';
	}
	
	// Get all player data from their individual table
	$playerdata = get_player_data($playerid);
	
	// Check if we have data for this game (week_id)
	if (!isset($playerdata[$week_id])) {
		return '<div style="font-size: 14px; color: #999999; line-height: 1.2em; margin-bottom: 5px;">0</div>';
	}
	
	$game = $playerdata[$week_id];
	$parts = array();
	
	// Check if player has any non-zero stats to determine if they played
	$has_pass_stats = ($game['pass_yds'] != 0 || $game['pass_td'] != 0 || $game['pass_int'] != 0);
	$has_rush_stats = ($game['rush_yds'] != 0 || $game['rush_td'] != 0);
	$has_rec_stats = ($game['rec_yds'] != 0 || $game['rec_td'] != 0);
	$is_kicker = (substr($playerid, -2) === 'PK');
	$has_kick_stats = ($game['fgm'] != 0 || $game['xpm'] != 0 || $game['fga'] != 0 || $game['xpa'] != 0 || $is_kicker);
	$has_twopt = (isset($game['twopt']) && $game['twopt'] != 0);

	// If player has ANY stats (even if points = 0), show all relevant stats including zeros
	$player_played = ($has_pass_stats || $has_rush_stats || $has_rec_stats || $has_kick_stats || $has_twopt);

	// Pass stats (Pass Yds/TDs/INTs)
	if ($has_pass_stats) {
		$parts[] = 'Pass: ' . $game['pass_yds'] . ' / ' . $game['pass_td'] . ' / ' . $game['pass_int'];
	}

	// Rush stats (Rush Yds/TDs)
	if ($has_rush_stats) {
		$parts[] = 'Rush: ' . $game['rush_yds'] . ' / ' . $game['rush_td'];
	}

	// Rec stats (Rec Yds/TDs)
	if ($has_rec_stats) {
		$parts[] = 'Rec: ' . $game['rec_yds'] . ' / ' . $game['rec_td'];
	}

	// Kicking stats (EP: made/att FG: made/att)
	if ($has_kick_stats) {
		$xpm = (int)$game['xpm'];
		$xpa = (int)$game['xpa'];
		$fgm = (int)$game['fgm'];
		$fga = (int)$game['fga'];
		$parts[] = 'EP: ' . $xpm . '/' . $xpa . ' | FG: ' . $fgm . '/' . $fga;
	}
	
	// Two-point conversions
	if (isset($game['twopt']) && $game['twopt'] > 0) {
		$parts[] = 'Two Pt: ' . $game['twopt'];
	}
	
	if (empty($parts)) {
		return '';
	}
	
	return '<div style="font-size: 14px; color: #999999; line-height: 1.2em; margin-bottom: 5px;">' . implode(' | ', $parts) . '</div>';
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
    $teamsbyid = teamlist();
	$honorring = get_ring_of_honor();
	
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
                if($playerimg):
				    echo '<img alt="Profile Picture" class="widget-img img-border-light" style="width:100px; height:100px; left:75%; top:10px;" src="'.$playerimg.'">';
                else:
                    echo '<p>No Image - '.$pid.'</p>';
                endif;
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
						if (in_array($pid, $halloffame)):
							echo '<h5 class="text-left text-bold">&nbsp;Inducted into the PFL Hall of Fame</h5>';
                        endif;
						if(in_array($pid, $honorring)):
                            $pidkey = array_search ($pid, $honorring);
						    $honorteam = substr($pidkey, 0,3);
                            echo '<h5 class="text-left text-bold">&nbsp;'.$teamsbyid[$honorteam].' Ring of Honor</h5>';
                        endif;
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

// MFL League ID by Season
function get_mfl_league_id(){
    $leagueids = array(
        2010 => 79850,
        2011 => 79122,
        2012 => 47001,
        2013 => 23875,
        2014 => 11521,
        2015 => 47099,
        2016 => 38954,
        2017 => 38954,
        2018 => 38954,
        2019 => 38954,
        2020 => 38954,
        2021 => 38954,
        2022 => 38954,
        2023 => 38954,
        2024 => 38954,
        2025 => 38954
    );
    return $leagueids;
}

// wp_teams table shows current team mfl ID.  This array accounts for the fact that the ids have shifted a bit over the years.
function teams_for_mfl_history($check_year = null){
    $mfl_team_id_history = array(
        2010 => array(
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
        ),
        2021 => array(
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
        2022 => array(
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
        2023 => array(
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
        2024 => array(
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
        2025 => array(
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
        2026 => array(
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
    
    // Check if a specific year was requested and if it exists in the array
    if ($check_year !== null && !isset($mfl_team_id_history[$check_year])) {
        return false; // Year not found
    }
    
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
    
    // Check if current year exists in team mapping
    $current_year = date('Y');
    $missing_years = array();
    foreach ($yearslist as $year) {
        if (!isset($teambyid[$year])) {
            $missing_years[] = $year;
        }
    }
    
    // If there are missing years, return error message
    if (!empty($missing_years)) {
        $error_data = array();
        $error_data['error'] = array(
            'message' => 'MFL Team mapping missing for year(s): ' . implode(', ', $missing_years),
            'instruction' => 'Please update the teams_for_mfl_history() function in functions.php to include team mappings for the missing year(s).',
            'missing_years' => $missing_years
        );
        return $error_data;
    }

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


function get_pfr_json($file)
{
    $pfr_gamelog_file = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/tif-child-bootstrap/pfr-gamelogs/' . $file . '.json';
    if (file_exists($pfr_gamelog_file)):
        $getfile = file_get_contents($pfr_gamelog_file);
        $decode_json = json_decode($getfile);

        $weeks = $decode_json->week_num;

        if ($weeks):
            foreach ($weeks as $theyear => $theweek) {
                $listyears[] = $theyear;
                $newyears[$theyear] = explode(",", $theweek);
            }
        endif;

        if ($decode_json):
            foreach ($decode_json as $key => $value) {
                if ($value):
                    foreach ($value as $k => $v) {
                        $exploded[$k][$key] = array_filter(explode(",", $v));
                    }
                endif;
            }

            if ($listyears):
                foreach ($listyears as $y) {
                    $i = 0;
                    $yearget = $exploded[$y]['week_num'];
                    $count = count($yearget);
                    //$y = 1991;
                    while ($i < $count) {
                        $getw = $exploded[$y]['week_num'][$i];
                        $w = str_pad($getw, 2, '0', STR_PAD_LEFT);
                        $location = $exploded[$y]['game_location'][$i];
                        if ($location != '@') {
                            $location = 'vs';
                        }

                        $xpm = $exploded[$y]['xpm'][$i];
                        $xpa = $exploded[$y]['xpa'][$i];
                        $fgm = $exploded[$y]['fgm'][$i];
                        $fga = $exploded[$y]['fga'][$i];

                        if ($xpm == '') {
                            $xpm = 0;
                        }
                        if ($xpa == '') {
                            $xpa = 0;
                        }
                        if ($fgm == '') {
                            $fgm = 0;
                        }
                        if ($fga == '') {
                            $fga = 0;
                        }

                        $get_score_correct = get_score_correct_by_player($playerid);
                        $score_correct = $get_score_correct[$y . $w]['score'];

                        $playerdata[$y . $w] = array(
                            'year' => $y,
                            'week_num' => $w,
                            'game_date' => $exploded[$y]['game_date'][$i],
                            'team' => $exploded[$y]['team'][$i],
                            'game_location' => $location,
                            'opp' => $exploded[$y]['opp'][$i],
                            'pass_yds' => $exploded[$y]['pass_yds'][$i],
                            'pass_td' => $exploded[$y]['pass_td'][$i],
                            'pass_int' => $exploded[$y]['pass_int'][$i],
                            'rush_yds' => $exploded[$y]['rush_yds'][$i],
                            'rush_td' => $exploded[$y]['rush_td'][$i],
                            'rec_yds' => $exploded[$y]['rec_yds'][$i],
                            'rec_td' => $exploded[$y]['rec_td'][$i],
                            'score_correct' => $score_correct,
                            'xpm' => $xpm,
                            'xpa' => $xpa,
                            'fgm' => $fgm,
                            'fga' => $fga
                        );
                        $i++;
                    }
                }
            endif;

        endif;

    endif;
    return $playerdata;
}


function insert_stat_columns($pid){
    global $wpdb;

    $myData = $wpdb->get_row("SELECT * FROM $pid");
    //Add column if not present.
    if (!isset($myData->game_date)) { $wpdb->query("ALTER TABLE $pid ADD game_date VARCHAR(10)"); }
    if (!isset($myData->nflteam)) { $wpdb->query("ALTER TABLE $pid ADD nflteam VARCHAR(3)"); }
    if (!isset($myData->game_location)) { $wpdb->query("ALTER TABLE $pid ADD game_location VARCHAR(3)"); }
    if (!isset($myData->nflopp)) { $wpdb->query("ALTER TABLE $pid ADD nflopp VARCHAR(3)"); }
    if (!isset($myData->pass_yds)) { $wpdb->query("ALTER TABLE $pid ADD pass_yds INT(3)"); }
    if (!isset($myData->pass_td)) { $wpdb->query("ALTER TABLE $pid ADD pass_td INT(2)"); }
    if (!isset($myData->pass_int)) { $wpdb->query("ALTER TABLE $pid ADD pass_int INT(2)"); }
    if (!isset($myData->rush_yds)) { $wpdb->query("ALTER TABLE $pid ADD rush_yds INT(2)"); }
    if (!isset($myData->rush_td)) { $wpdb->query("ALTER TABLE $pid ADD rush_td INT(2)"); }
    if (!isset($myData->rec_yds)) { $wpdb->query("ALTER TABLE $pid ADD rec_yds INT(2)"); }
    if (!isset($myData->rec_td)) { $wpdb->query("ALTER TABLE $pid ADD rec_td INT(2)"); }
    if (!isset($myData->xpm)) { $wpdb->query("ALTER TABLE $pid ADD xpm INT(2)"); }
    if (!isset($myData->xpa)) { $wpdb->query("ALTER TABLE $pid ADD xpa INT(2)"); }
    if (!isset($myData->fgm)) { $wpdb->query("ALTER TABLE $pid ADD fgm INT(2)"); }
    if (!isset($myData->fga)) { $wpdb->query("ALTER TABLE $pid ADD fga INT(2)"); }
    if (!isset($myData->nflscore)) { $wpdb->query("ALTER TABLE $pid ADD nflscore INT(2)"); }
    if (!isset($myData->scorediff)) { $wpdb->query("ALTER TABLE $pid ADD scorediff INT(2)"); }

    printr($myData, 0);

}

function insert_player_stats($pid, $exp)
{
    global $wpdb;

    foreach ($exp as $key => $value):
        $wpdb->update(
            $pid,
            array(
                'game_date' => $value['game_date'],
                'nflteam' => $value['nflteam'],
                'game_location' => $value['game_location'],
                'nflopp' => $value['opp'],
                'pass_yds' => $value['pass_yds'],
                'pass_td' => $value['pass_td'],
                'pass_int' => $value['pass_int'],
                'rush_yds' => $value['rush_yds'],
                'rush_td' => $value['rush_td'],
                'rec_yds' => $value['rec_yds'],
                'rec_td' => $value['rec_td'],
                'xpm' => $value['xpm'],
                'xpa' => $value['xpa'],
                'fgm' => $value['fgm'],
                'fga' => $value['fga'],
                'nflscore' => $value['nflscore'],
                'scorediff' => $value['scorediff']
            ),
            array(
                'week_id' => $value['weekids']
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d',
                '%d'
            )
        );
    endforeach;
    return 'inserted';
}

function insert_player_number_array($pid, $numbers)
{
    global $wpdb;

        $wpdb->update(
            'wp_players',
            array(
                'numberarray' => $numbers
            ),
            array(
                'p_id' => $pid
            ),
            array(
                '%s'
            )
        );

    return 'inserted';
}

function array_count_values_of($value, $array) {
    $counts = array_count_values($array);
    return $counts[$value];
}

//build bench for Results page
function get_the_bench($year, $week, $teamid){
    $getseasonids = get_pfl_mfl_ids_season();
    $oneseasonids = $getseasonids[$year];

    $destination_folder = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/mfl-weekly-rosters';
    if (file_exists($destination_folder.'/'.$year.$week.'.json')):
        $get_roster = file_get_contents($destination_folder.'/'.$year.$week.'.json');
        $results = json_decode($get_roster, true);
        $rosterarray = $results['rosters']['franchise'];

        foreach ($rosterarray as $key => $value):
            $pflid = $oneseasonids[$value['id']];
            $newrosterarray[$pflid] = $value['player'];
        endforeach;

        foreach ($newrosterarray as $key => $value):
            if($key != ''):
                foreach($value as $k => $v):
                    $status = $v['status'];
                    $playerid = one_player_mfl_to_pfl($v['id']);
                    $playerinfo = get_player_basic_info($playerid);
                    $fullname = substr($playerinfo[0]['first'], 0, 1).'.'.$playerinfo[0]['last'];
                    $position = $playerinfo[0]['position'];
                    if($k != ''):
                        $teamrosters[$key][$status][$playerid] = array(
                            'player' => $v['id'],
                            'name' => $fullname,
                            'position' => $position,
                            'drafted' => $v['drafted']
                        );
                    endif;
                endforeach;
            endif;
        endforeach;
    endif;
    return $teamrosters[$teamid];
}

//check if a team won on a specific week
function check_for_win($team, $weekid){
    global $wpdb;
    $getdata = $wpdb->get_results("select * from wp_team_$team where '$weekid' like id", ARRAY_N);
    $wincheck = $getdata[0][9];
    $check = ($wincheck > 0) ? 1 : 0;
    return $check;
}

// find the second highest value in an array (used on number-2s)
function findSecondLargest($arr){
    sort($arr);
    $secondLargest = $arr[sizeof($arr)-2];
    return $secondLargest;
}

// function that takes an associative array and a value, and returns an array of keys where the items in the associative array match the given value.
function findKeysByValue($array, $searchValue) {
    $matchingKeys = array();
    foreach ($array as $key => $value) {
        if ($value === $searchValue) {
            $matchingKeys[] = $key;
        }
    }
    return $matchingKeys;
}

// returns logic for number twoed table on 'tables' page
function get_number_twoed() {
    $theweeks = the_weeks();
    $thefinal = [];
    $justteam = [];

    foreach ($theweeks as $key) {
        $teamscore = get_team_score_by_week($key);

        if (!empty($teamscore)) {
            $secondvalue = findSecondLargest($teamscore);
            $getkeys = findKeysByValue($teamscore, $secondvalue);
            $newarray[$key][$secondvalue] = $getkeys;

            foreach ($getkeys as $team) {
                if (check_for_win($team, $key) === 0) {
                    $final = get_team_results_by_week($team, $key);
                    $thefinal[$key] = $final[$key];
                    $justteam[$key] = $team;
                }
            }
        }
    }

    return $thefinal;
}

// Check if a game was a number two event
function check_for_number_two($weekid, $teamid){
    $thetwos = get_number_twoed();
    $checktwo = $thetwos[$weekid];
        if($teamid == $checktwo['team_int']):
            $return = $teamid.' got Number Two-ed!<br>';
        endif;
return $return;
}

// for positions difference page
function position_difference($weekid) {
    $teamscore = get_boxscore_by_week($weekid);
    if($teamscore):
        foreach ($teamscore as $team => $array):
            $justboxes[$team] = array(
                'versus' => $array['versus'],
                'players' => array(
                    $array['qb1']['pos'] => $array['qb1']['points'],
                    $array['rb1']['pos'] => $array['rb1']['points'],
                    $array['wr1']['pos'] => $array['wr1']['points'],
                    $array['pk1']['pos'] => $array['pk1']['points'],
                )
            );
        endforeach;

        foreach ($justboxes as $key => $value):
            $matchups[$key] = array(
                'thisteam' => $value['players'],
                'versus' => $justboxes[$value['versus']]['players']
            );
        endforeach;

        foreach ($matchups as $k => $v):
            $finals[$k] = array(
                'QB' => $v['thisteam']['QB'] - $v['versus']['QB'],
                'RB' => $v['thisteam']['RB'] - $v['versus']['RB'],
                'WR' => $v['thisteam']['WR'] - $v['versus']['WR'],
                'PK' => $v['thisteam']['PK'] - $v['versus']['PK'],
            );
        endforeach;
    endif;
    return $finals;
}

function single_team_player_difference($theteam){
    $theweeks = the_weeks();
    foreach ($theweeks as $key => $week):
        $getinfo[$week] = position_difference($week);
    endforeach;

    foreach ($getinfo as $week => $teams):
        $oneteam[$week] = $teams[$theteam];
    endforeach;
    return $oneteam;

}

function print_array_as_csv($die, $team, $pos){
    $top_team_qb_test = get_player_leaders_by_team($team, $pos);
    $i = 0;
    foreach ($top_team_qb_test as $playerid => $points):
        if($i < $die):
            $string .= pid_to_name($playerid, 1) . ', ';
        else:
            break;
        endif;
        $i++;
    endforeach;
    rtrim($string ,", ");
    return $string;
}

function team_output($teamid) {
    $printout = single_team_player_difference($teamid);
    foreach ($printout as $key => $value):
        $qb_plusmin[$key] = $value['QB'];
        $rb_plusmin[$key] = $value['RB'];
        $wr_plusmin[$key] = $value['WR'];
        $pk_plusmin[$key] = $value['PK'];
    endforeach;

    $qb_sum = array_sum($qb_plusmin);
    $rb_sum = array_sum($rb_plusmin);
    $wr_sum = array_sum($wr_plusmin);
    $pk_sum = array_sum($pk_plusmin);

    $all_pos_output = array(
        'QB' => $qb_sum,
        'RB' => $rb_sum,
        'WR' => $wr_sum,
        'PK' => $pk_sum,
    );
    return $all_pos_output;
}

function get_or_set_comps(){
    $transient = get_transient( 'comps_pfl_trans' );
    if( ! empty( $transient ) ) {
        echo '<script>console.log("Comps - From Transient");</script>';
        return $transient;
    } else {
        $teamlist = teamlist();
        foreach ($teamlist as $team => $name):
            $comps[$team] = team_output($team);
        endforeach;
        set_transient( 'comps_pfl_trans', $comps, 600000 );
        echo '<script>console.log("Comps - Transient Set");</script>';
        return $comps;
    }
}

/**
 * Get draft timestamp from MFL draft results JSON files
 * 
 * @param int $year The draft year
 * @param string $player_mfl_id The MFL player ID
 * @return string|null The draft timestamp in 'YYYY-MM-DD HH:MM:SS' format, or null if not found
 */
function get_mfl_draft_timestamp($year, $player_mfl_id) {
    $draft_file = get_stylesheet_directory() . '/mfl-drafts/' . $year . '_draft_results.json';
    
    // Check if file exists
    if (!file_exists($draft_file)) {
        return null;
    }
    
    try {
        // Read and decode JSON file
        $json_content = file_get_contents($draft_file);
        $draft_data = json_decode($json_content, true);
        
        // Navigate to draftPick array
        if (!isset($draft_data['draftResults']['draftUnit']['draftPick'])) {
            return null;
        }
        
        $draft_picks = $draft_data['draftResults']['draftUnit']['draftPick'];
        
        // Handle case where draftPick is a single object instead of array
        if (!is_array($draft_picks) || (isset($draft_picks['player']) && !isset($draft_picks[0]))) {
            $draft_picks = [$draft_picks];
        }
        
        // Search for the player's pick
        foreach ($draft_picks as $pick) {
            if (isset($pick['player']) && $pick['player'] == $player_mfl_id && isset($pick['timestamp'])) {
                // Convert Unix timestamp to MySQL datetime format
                $timestamp = intval($pick['timestamp']);
                return date('Y-m-d H:i:s', $timestamp);
            }
        }
        
    } catch (Exception $e) {
        // Silent fail - will fall back to default date
        return null;
    }
    
    return null;
}

/**
 * Get draft date for display (without time)
 * 
 * @param int $year The draft year
 * @param string $player_mfl_id The MFL player ID (optional)
 * @return string Draft date in 'YYYY-MM-DD' format
 */
function get_draft_date_for_player($year, $player_mfl_id = null) {
    // Try to get actual timestamp from MFL data if player ID provided
    if ($player_mfl_id) {
        $timestamp = get_mfl_draft_timestamp($year, $player_mfl_id);
        
        if ($timestamp) {
            return substr($timestamp, 0, 10); // Extract just the date part
        }
    }
    
    // Try to get date from ACF post meta (post ID 195 - Draft Info page)
    $draft_info = get_fields(195);
    
    if ($draft_info && isset($draft_info['draft_info'])) {
        // Search through draft_info array for matching year
        foreach ($draft_info['draft_info'] as $draft) {
            if (isset($draft['year']) && intval($draft['year']) == intval($year)) {
                if (isset($draft['date']) && !empty($draft['date'])) {
                    // Convert MM-DD-YYYY format to YYYY-MM-DD
                    $date_parts = explode('-', $draft['date']);
                    if (count($date_parts) == 3) {
                        return $date_parts[2] . '-' . $date_parts[0] . '-' . $date_parts[1];
                    }
                }
            }
        }
    }
    
    // Ultimate fallback - August 1st of the year
    return $year . '-08-01';
}


/**
 * Custom Walker for Sidebar Navigation
 * Handles the specific structure needed for the Bootstrap sidebar menu
 */
class Sidebar_Nav_Walker extends Walker_Nav_Menu {
    
    function start_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"collapse in\">\n";
    }
    
    function end_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }
    
    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
        
        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        
        // Add active-link class if this is the current page
        if ( $item->current ) {
            $classes[] = 'active-link';
        }
        
        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
        
        // Top level items need special class
        if ( $depth == 0 ) {
            $class_names = 'active-sub';
        }
        
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
        
        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
        
        $output .= $indent . '<li' . $id . $class_names .'>';
        
        $atts = array();
        $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
        $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
        $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
        $atts['href']   = ! empty( $item->url )        ? $item->url        : '';
        
        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );
        
        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }
        
        $item_output = $args->before;
        
        // Top level items need icon and arrow
        if ( $depth == 0 ) {
            $item_output .= '<a'. $attributes .'>';
            $item_output .= '<i class="fa fa-th"></i>';
            $item_output .= '<span class="menu-title">' . $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after . '</span>';
            $item_output .= '<i class="arrow"></i>';
            $item_output .= '</a>';
        } else {
            $item_output .= '<a'. $attributes .'>';
            $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
            $item_output .= '</a>';
        }
        
        $item_output .= $args->after;
        
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
    
    function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= "</li>\n";
    }
}

/**
 * Build Sidebar Navigation Menu - One-time setup function
 * To trigger: Add ?build_sidebar_menu=1 to any admin page URL
 */
function build_sidebar_navigation_menu() {
    // Only run if explicitly requested and user is admin
    if (!isset($_GET['build_sidebar_menu']) || !current_user_can('manage_options')) {
        return;
    }
    
    // Delete existing menu if it exists
    $existing_menu = wp_get_nav_menu_object('Sidebar Navigation');
    if ($existing_menu) {
        wp_delete_nav_menu($existing_menu->term_id);
    }
    
    // Create new menu
    $menu_id = wp_create_nav_menu('Sidebar Navigation');
    
    // Define the menu structure
    $menu_structure = array(
        'Awards' => array(
            'url' => '#',
            'children' => array(
                'Hall of Fame' => '/hall-of-fame',
                'Most Valuable Player' => '/mvp',
                'Rookie of the Year' => '/rookie',
                'Posse Bowl MVP' => '/posse-bowl-mvp',
                'Pro Bowl MVP' => '/pro-bowl-mvp',
                'All Awards' => '/all-awards'
            )
        ),
        'Players' => array(
            'url' => '#',
            'children' => array(
                'Individual Players' => '/player/?id=1998MannQB',
                'Career Leaders' => '/leaders',
                'Leaders By Season' => '/leaders-season/?id=2025',
                'Supercards' => '/supercards/'
            )
        ),
        'Seasons' => array(
            'url' => '#',
            'children' => array(
                'Seasons' => '/seasons/?id=2025',
                'Drafts by Year' => '/drafts/?id=2025',
                'Standings By Year' => '/standings/?id=2025',
                'Playoff Brackets' => '/playoff-brackets',
                'Team Rosters' => '/team-rosters/?season=2025'
            )
        ),
        'Teams' => array(
            'url' => '#',
            'children' => array(
                'Teams' => '/teams/?id=ETS',
                'Eras' => '/eras/?id=ETS',
                'Protections By Team' => '/protections-team/?Y=&TEAM='
            )
        ),
        'Games' => array(
            'url' => '#',
            'children' => array(
                'Weekly Results' => '/results?Y=2025&W=01',
                'All Schedules' => '/schedules',
                'Grandslams' => '/grandslams',
                'Home and Away' => '/home-and-away',
                'The Playoffs' => '/playoffs',
                'The Posse Bowl' => '/champions',
                'The Pro Bowl' => '/pro-bowl'
            )
        ),
        'Table Data' => array(
            'url' => '#',
            'children' => array(
                'Tables - Players' => '/tables-players',
                'Tables - Teams' => '/tables-teams',
                'Tables - Postseason' => '/tables-postseason',
                'Tables - NFL' => '/tables-nfl',
                'Tables - Drafts' => '/tables-drafts',
                'Tables - Scoring Title' => '/scoring-title-pages',
                'Tables - Other' => '/tables-other'
            )
        ),
        'Resources' => array(
            'url' => '#',
            'children' => array(
                'Players by NFL Team' => '/nfl-team-page',
                'Timeline' => '/timeline',
                'HOF Eligibility' => '/hall-eligible-players',
                'Head to Head Matrix' => '/head-to-head',
                'Trades' => '/trades',
                'Trade Analyzer' => '/trade-analyzer?TRADE=130',
                'Playoff Probability' => '/playoff-probability',
                'Draft Research' => '/research',
                'Unis & Helmets' => '/uniforms',
                'Number Ones' => '/number-ones',
                'Mr Irrelevant' => '/mr-irrelevant',
                'Kicker Drafts' => '/kicker-draft/?draft_year=2025/',
                'Scorigami' => '/scorigami/?W=202501',
                'Position Difference' => '/position-difference',
                'Colleges' => '/colleges',
                'Error Check' => '/error-check'
            )
        )
    );
    
    // Recursive function to add menu items
    function add_sidebar_menu_items($menu_id, $menu_structure, $parent_id = 0) {
        $position = 0;
        
        foreach ($menu_structure as $title => $data) {
            $position++;
            
            $item_data = array(
                'menu-item-title' => $title,
                'menu-item-url' => is_array($data) ? $data['url'] : $data,
                'menu-item-status' => 'publish',
                'menu-item-parent-id' => $parent_id,
                'menu-item-position' => $position
            );
            
            $item_id = wp_update_nav_menu_item($menu_id, 0, $item_data);
            
            if (is_array($data) && isset($data['children'])) {
                add_sidebar_menu_items($menu_id, $data['children'], $item_id);
            }
        }
    }
    
    // Build the menu
    add_sidebar_menu_items($menu_id, $menu_structure);
    
    // Assign menu to location
    $locations = get_theme_mod('nav_menu_locations');
    $locations['sidebar_navigation'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
    
    // Redirect to menu editor
    wp_redirect(admin_url('nav-menus.php?action=edit&menu=' . $menu_id));
    exit;
}
add_action('admin_init', 'build_sidebar_navigation_menu');

// AJAX handler for adding correction notes
function add_correction_note_ajax() {
    // Verify nonce for security
    check_ajax_referer('correction_note_nonce', 'nonce');
    
    // Get the parameters
    $player_name = sanitize_text_field($_POST['player_name']);
    $team_abbr = sanitize_text_field($_POST['team_abbr']);
    $year = intval($_POST['year']);
    $week = intval($_POST['week']);
    $point_change = intval($_POST['point_change']);
    
    // Format week ID as YYYYWW
    $week_id = $year . sprintf('%02d', $week);
    
    // Create the note text with point change
    $note_text = $player_name . ' (' . $team_abbr . ') had a ' . $point_change . ' point correction to his update boxscore data. Player score and game score were modified but outcome remains the same.';
    
    // Get existing week notes
    $week_notes = get_field('week_notes', 'options');
    if (!is_array($week_notes)) {
        $week_notes = array();
    }
    
    // Add new note
    $new_note = array(
        'week_id' => $week_id,
        'team_reference' => $team_abbr,
        'weekly_note' => $note_text
    );
    
    $week_notes[] = $new_note;
    
    // Update the field
    $result = update_field('week_notes', $week_notes, 'options');
    
    if ($result) {
        wp_send_json_success(array(
            'message' => 'Note added successfully',
            'week_id' => $week_id,
            'team' => $team_abbr,
            'note' => $note_text
        ));
    } else {
        wp_send_json_error(array('message' => 'Failed to add note'));
    }
}

add_action('wp_ajax_add_correction_note', 'add_correction_note_ajax');
add_action('wp_ajax_nopriv_add_correction_note', 'add_correction_note_ajax');

add_action('wp_ajax_upload_player_image', function () {
    require get_stylesheet_directory() . '/inc/upload-player-image-handler.php';
});


// ── PFL REST API ──────────────────────────────────────────────────────────────

add_action('rest_api_init', function () {

    // Generic award winners endpoint — ?type=<award name>
    // Shortcuts: /mvp, /roty
    register_rest_route('pfl/v1', '/awards', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_awards',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/mvp', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_mvp_shortcut',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/roty', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_roty_shortcut',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/probowl-mvp', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_probowl_shortcut',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/possebowl-mvp', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_possebowl_shortcut',
        'permission_callback' => '__return_true',
    ]);

});

function pfl_api_awards(WP_REST_Request $request) {
    $type = sanitize_text_field($request->get_param('type'));
    if (empty($type)) {
        return new WP_Error('missing_param', 'Missing required parameter: type', ['status' => 400]);
    }
    return pfl_get_award_data($type);
}

function pfl_api_mvp_shortcut()     { return pfl_get_award_data('Most Valuable Player'); }
function pfl_api_roty_shortcut()    { return pfl_get_award_data('Rookie of the Year'); }
function pfl_api_probowl_shortcut()    { return pfl_get_award_data('Pro Bowl MVP'); }
function pfl_api_possebowl_shortcut()  { return pfl_get_award_data('Posse Bowl MVP'); }

function pfl_get_helmet_num(string $team, int $year): int {
    global $wpdb;
    $history = $wpdb->get_results($wpdb->prepare(
        "SELECT yearstart, helmet FROM wp_helmet_history WHERE team = %s ORDER BY yearstart ASC",
        $team
    ), ARRAY_A);
    $by_year = [];
    foreach ($history as $row) {
        $by_year[(int) $row['yearstart']] = (int) $row['helmet'];
    }
    $active = 1;
    for ($y = 1991; $y <= $year; $y++) {
        if (isset($by_year[$y])) $active = $by_year[$y];
    }
    return $active;
}

function pfl_get_award_data(string $award_name) {
    global $wpdb;

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT a.year, a.playerFirst, a.playerLast, a.team, a.position, a.pid, a.gamepts,
                sl.points, sl.games, t.team AS team_name
         FROM wp_awards a
         LEFT JOIN wp_season_leaders sl
               ON sl.playerid = a.pid AND sl.season = a.year
         LEFT JOIN wp_teams t ON t.team_int = a.team
         WHERE a.award = %s
         ORDER BY a.year DESC",
        $award_name
    ), ARRAY_A);

    $data = [];
    foreach ($rows as $row) {
        $games    = (int) $row['games'];
        $points   = (int) $row['points'];
        $ppg      = ($games > 0) ? round($points / $games, 1) : 0;
        $img_url  = get_attachment_url_by_slug($row['pid']);
        $year     = (int) $row['year'];

        $data[] = [
            'year'      => $year,
            'first'     => $row['playerFirst'],
            'last'      => $row['playerLast'],
            'team'      => $row['team'],
            'teamName'  => $row['team_name'] ?? $row['team'],
            'helmetNum' => pfl_get_helmet_num($row['team'], $year),
            'position'  => $row['position'],
            'pid'       => $row['pid'],
            'gamepts'   => isset($row['gamepts']) ? (int) $row['gamepts'] : null,
            'points'    => $points,
            'games'     => $games,
            'ppg'       => $ppg,
            'img'       => $img_url ?: null,
        ];
    }

    return rest_ensure_response($data);
}

// ── HOF endpoint ──────────────────────────────────────────────────────────────

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/hof', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_hof',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_hof() {
    global $wpdb;

    $award_labels = [
        'mvp'    => 'Most Valuable Player',
        'pflmvp' => 'Most Valuable Player',
        'pbm'    => 'Posse Bowl MVP',
        'pbmvp'  => 'Posse Bowl MVP',
        'pro'    => 'Pro Bowl MVP',
        'promvp' => 'Pro Bowl MVP',
        'roty'   => 'Rookie of the Year',
        'ooty'   => 'Owner of the Year',
    ];

    $inductees = $wpdb->get_results(
        "SELECT a.year, a.playerFirst, a.playerLast, a.team, a.position, a.pid
         FROM wp_awards a
         WHERE a.award = 'Hall of Fame Inductee'
         ORDER BY a.year DESC",
        ARRAY_A
    );

    $data = [];

    foreach ($inductees as $row) {
        $pid = $row['pid'];

        // Player image
        $img = get_attachment_url_by_slug($pid) ?: null;

        // Career stats from season leaders
        $sl = $wpdb->get_row($wpdb->prepare(
            "SELECT SUM(points) as pts, SUM(games) as gms,
                    MIN(season) as first_yr, MAX(season) as last_yr
             FROM wp_season_leaders WHERE playerid = %s",
            $pid
        ), ARRAY_A);

        // Career high + W-L from individual player table (sanitise pid strictly)
        $table = preg_replace('/[^a-zA-Z0-9]/', '', $pid);

        // Most-played team (by game count)
        $primary_team_int = $wpdb->get_var(
            "SELECT team FROM `{$table}` WHERE team != '' AND team IS NOT NULL
             GROUP BY team ORDER BY COUNT(*) DESC LIMIT 1"
        );
        $primary_team_name = $primary_team_int
            ? $wpdb->get_var($wpdb->prepare(
                "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $primary_team_int
              ))
            : null;
        $hl = $wpdb->get_row(
            "SELECT MAX(points) as high, SUM(win_loss) as wins, COUNT(*) as games
             FROM `{$table}` WHERE team != ''",
            ARRAY_A
        );

        $total_pts  = (int)   ($sl['pts']      ?? 0);
        $total_gms  = (int)   ($sl['gms']      ?? 0);
        $first_yr   = (int)   ($sl['first_yr'] ?? 0);
        $last_yr    = (int)   ($sl['last_yr']  ?? 0);
        $high       = (int)   ($hl['high']     ?? 0);
        $wins       = (int)   ($hl['wins']     ?? 0);
        $losses     = $total_gms - $wins;
        $ppg        = $total_gms > 0 ? round($total_pts / $total_gms, 1) : 0;
        $win_pct    = $total_gms > 0 ? round($wins / $total_gms, 3)      : 0;

        // Awards (excluding HOF)
        $award_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT awardID, award, year FROM wp_awards
             WHERE pid = %s AND award != 'Hall of Fame Inductee'
             ORDER BY year",
            $pid
        ), ARRAY_A);

        $awards = [];
        foreach ($award_rows as $a) {
            $prefix = substr($a['awardID'], 0, -4);
            $awards[] = [
                'id'    => $a['awardID'],
                'label' => $award_labels[$prefix] ?? $a['award'],
                'year'  => (int) $a['year'],
            ];
        }

        // Scoring titles
        $title_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT id, year, points, teams, pos FROM wp_number_ones
             WHERE playerid = %s ORDER BY year",
            $pid
        ), ARRAY_A);

        $scoring_titles = [];
        foreach ($title_rows as $t) {
            $team_name = $wpdb->get_var($wpdb->prepare(
                "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $t['teams']
            ));
            $scoring_titles[] = [
                'year'     => (int) $t['year'],
                'points'   => (int) $t['points'],
                'team'     => $t['teams'],
                'teamName' => $team_name ?? $t['teams'],
                'pos'      => $t['pos'],
            ];
        }

        // Longest consecutive game streak
        $game_streak = (int) get_player_game_streak($pid);

        // Player of the week count
        $potw = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM wp_player_of_week WHERE playerid = %s", $pid
        ));

        // Posse Bowl appearances + championships
        $pb_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT p.year, p.team,
                    t.team as team_name,
                    IF(c.winTeam IS NOT NULL, 1, 0) as champion
             FROM wp_playoffs p
             JOIN wp_teams t ON t.team_int = p.team
             LEFT JOIN wp_champions c ON c.year = p.year AND c.winTeam = p.team
             WHERE p.playerid = %s AND p.week = 16
             ORDER BY p.year",
            $pid
        ), ARRAY_A);

        $posse_bowl_apps = [];
        foreach ($pb_rows as $pb) {
            $posse_bowl_apps[] = [
                'year'     => (int) $pb['year'],
                'team'     => $pb['team'],
                'teamName' => $pb['team_name'],
                'champion' => (bool) $pb['champion'],
            ];
        }

        $data[] = [
            'pid'           => $pid,
            'first'         => $row['playerFirst'],
            'last'          => $row['playerLast'],
            'position'      => $row['position'],
            'inductionYear' => (int) $row['year'],
            'img'           => $img,
            'totalPoints'   => $total_pts,
            'totalGames'    => $total_gms,
            'firstYear'     => $first_yr,
            'lastYear'      => $last_yr,
            'primaryTeam'   => $primary_team_name ?? $primary_team_int,
            'ppg'           => $ppg,
            'careerHigh'    => $high,
            'wins'          => $wins,
            'losses'        => $losses,
            'winPct'        => $win_pct,
            'gameStreak'    => $game_streak,
            'awards'        => $awards,
            'scoringTitles' => $scoring_titles,
            'potwCount'     => $potw,
            'posseBowlApps' => $posse_bowl_apps,
        ];
    }

    return rest_ensure_response($data);
}


add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/hof-eligible', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_hof_eligible',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_hof_eligible() {
    global $wpdb;

    $award_labels = [
        'mvp'    => 'Most Valuable Player',
        'pflmvp' => 'Most Valuable Player',
        'pbm'    => 'Posse Bowl MVP',
        'pbmvp'  => 'Posse Bowl MVP',
        'pro'    => 'Pro Bowl MVP',
        'promvp' => 'Pro Bowl MVP',
        'roty'   => 'Rookie of the Year',
        'ooty'   => 'Owner of the Year',
    ];

    // Players with 900+ career points who are NOT already HOF inductees
    $candidates = $wpdb->get_results(
        "SELECT al.pid, al.points, al.games, al.high, al.gamestreak,
                al.firstyear, al.lastyear, al.position,
                p.playerFirst AS first, p.playerLast AS last
         FROM wp_allleaders al
         LEFT JOIN wp_players p ON p.p_id = al.pid
         WHERE al.points >= 900
           AND NOT EXISTS (
               SELECT 1 FROM wp_awards a
               WHERE a.pid = al.pid AND a.award = 'Hall of Fame Inductee'
           )
         ORDER BY al.points DESC",
        ARRAY_A
    );

    $eligible   = [];
    $needs_time = [];
    $on_bubble  = [];

    foreach ($candidates as $row) {
        $pid = $row['pid'];

        $img = get_attachment_url_by_slug($pid) ?: null;

        $total_pts = (int) ($row['points']     ?? 0);
        $total_gms = (int) ($row['games']      ?? 0);
        $first_yr  = (int) ($row['firstyear']  ?? 0);
        $last_yr   = (int) ($row['lastyear']   ?? 0);
        $high      = (int) ($row['high']       ?? 0);
        $ppg       = $total_gms > 0 ? round($total_pts / $total_gms, 1) : 0;

        // Wins from per-player table
        $table = preg_replace('/[^a-zA-Z0-9]/', '', $pid);
        $hl = $wpdb->get_row(
            "SELECT SUM(win_loss) as wins, COUNT(*) as games
             FROM `{$table}` WHERE team != ''",
            ARRAY_A
        );
        $wins     = (int) ($hl['wins']  ?? 0);
        $losses   = $total_gms - $wins;
        $win_pct  = $total_gms > 0 ? round($wins / $total_gms, 3) : 0;

        // Most-played team
        $primary_team_int = $wpdb->get_var(
            "SELECT team FROM `{$table}` WHERE team != '' AND team IS NOT NULL
             GROUP BY team ORDER BY COUNT(*) DESC LIMIT 1"
        );
        $primary_team_name = $primary_team_int
            ? $wpdb->get_var($wpdb->prepare(
                "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $primary_team_int
              ))
            : null;

        // Game streak
        $game_streak = (int) ($row['gamestreak'] ?? 0);

        // Awards (excluding HOF)
        $award_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT awardID, award, year FROM wp_awards
             WHERE pid = %s AND award != 'Hall of Fame Inductee'
             ORDER BY year",
            $pid
        ), ARRAY_A);
        $awards = [];
        foreach ($award_rows as $a) {
            $prefix   = substr($a['awardID'], 0, -4);
            $awards[] = [
                'id'    => $a['awardID'],
                'label' => $award_labels[$prefix] ?? $a['award'],
                'year'  => (int) $a['year'],
            ];
        }

        // Scoring titles
        $title_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT id, year, points, teams, pos FROM wp_number_ones
             WHERE playerid = %s ORDER BY year",
            $pid
        ), ARRAY_A);
        $scoring_titles = [];
        foreach ($title_rows as $t) {
            $team_name = $wpdb->get_var($wpdb->prepare(
                "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $t['teams']
            ));
            $scoring_titles[] = [
                'year'     => (int) $t['year'],
                'points'   => (int) $t['points'],
                'team'     => $t['teams'],
                'teamName' => $team_name ?? $t['teams'],
                'pos'      => $t['pos'],
            ];
        }

        // Player of the week count
        $potw = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM wp_player_of_week WHERE playerid = %s", $pid
        ));

        // Posse Bowl appearances
        $pb_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT p.year, p.team,
                    t.team as team_name,
                    IF(c.winTeam IS NOT NULL, 1, 0) as champion
             FROM wp_playoffs p
             JOIN wp_teams t ON t.team_int = p.team
             LEFT JOIN wp_champions c ON c.year = p.year AND c.winTeam = p.team
             WHERE p.playerid = %s AND p.week = 16
             ORDER BY p.year",
            $pid
        ), ARRAY_A);
        $posse_bowl_apps = [];
        foreach ($pb_rows as $pb) {
            $posse_bowl_apps[] = [
                'year'     => (int) $pb['year'],
                'team'     => $pb['team'],
                'teamName' => $pb['team_name'],
                'champion' => (bool) $pb['champion'],
            ];
        }

        $player = [
            'pid'           => $pid,
            'first'         => $row['first'] ?? '',
            'last'          => $row['last']  ?? '',
            'position'      => $row['position'],
            'inductionYear' => null,
            'img'           => $img,
            'totalPoints'   => $total_pts,
            'totalGames'    => $total_gms,
            'firstYear'     => $first_yr,
            'lastYear'      => $last_yr,
            'primaryTeam'   => $primary_team_name ?? $primary_team_int,
            'ppg'           => $ppg,
            'careerHigh'    => $high,
            'wins'          => $wins,
            'losses'        => $losses,
            'winPct'        => $win_pct,
            'gameStreak'    => $game_streak,
            'awards'        => $awards,
            'scoringTitles' => $scoring_titles,
            'potwCount'     => $potw,
            'posseBowlApps' => $posse_bowl_apps,
        ];

        if ($total_pts >= 1000 && $last_yr <= 2022) {
            $eligible[] = $player;
        } elseif ($total_pts >= 1000) {
            $needs_time[] = $player;
        } else {
            $on_bubble[] = $player;
        }
    }

    return rest_ensure_response([
        'eligible'  => $eligible,
        'needsTime' => $needs_time,
        'onBubble'  => $on_bubble,
    ]);
}


add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/all-awards', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_all_awards',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_all_awards() {
    global $wpdb;

    // All years with any data
    $years = $wpdb->get_col(
        "SELECT DISTINCT year FROM wp_champions ORDER BY year DESC"
    );

    // Pre-fetch awards indexed by [year][award]
    $award_rows = $wpdb->get_results(
        "SELECT year, award, playerFirst, playerLast, team, pid
         FROM wp_awards
         WHERE award != 'Hall of Fame Inductee'
         ORDER BY year, award",
        ARRAY_A
    );
    $awards_by_year = [];
    foreach ($award_rows as $r) {
        $awards_by_year[$r['year']][$r['award']][] = $r;
    }

    // Pre-fetch champions
    $champ_rows = $wpdb->get_results(
        "SELECT c.year, c.winTeam, t.team as teamName
         FROM wp_champions c
         JOIN wp_teams t ON t.team_int = c.winTeam",
        ARRAY_A
    );
    $champs = [];
    foreach ($champ_rows as $r) {
        $champs[$r['year']] = ['team' => $r['winTeam'], 'teamName' => $r['teamName']];
    }

    // Pre-fetch scoring titles
    $title_rows = $wpdb->get_results(
        "SELECT n.year, n.pos, n.playerid, n.points, n.teams,
                CONCAT(p.playerFirst, ' ', p.playerLast) as name
         FROM wp_number_ones n
         JOIN wp_players p ON p.p_id = n.playerid
         ORDER BY n.year, n.pos",
        ARRAY_A
    );
    $titles_by_year = [];
    foreach ($title_rows as $r) {
        $titles_by_year[$r['year']][$r['pos']] = [
            'name'   => $r['name'],
            'team'   => $r['teams'],
            'points' => (int) $r['points'],
            'pid'    => $r['playerid'],
        ];
    }

    // Pre-fetch Pro Bowl winners
    $probowl_rows = $wpdb->get_results(
        "SELECT year, winner FROM wp_probowl ORDER BY year",
        ARRAY_A
    );
    $probowl = [];
    foreach ($probowl_rows as $r) {
        $probowl[$r['year']] = $r['winner'];
    }

    $data = [];

    foreach ($years as $year) {
        $mvp   = $awards_by_year[$year]['Most Valuable Player'][0]  ?? null;
        $roty  = $awards_by_year[$year]['Rookie of the Year'][0]    ?? null;
        $ooty  = $awards_by_year[$year]['Owner of the Year'][0]     ?? null;
        $pbmvp = $awards_by_year[$year]['Posse Bowl MVP'][0]        ?? null;
        $promvp= $awards_by_year[$year]['Pro Bowl MVP'][0]          ?? null;

        $make_player = fn($row) => $row ? [
            'name' => trim($row['playerFirst'] . ' ' . $row['playerLast']),
            'team' => $row['team'],
            'pid'  => $row['pid'],
        ] : null;

        $data[] = [
            'year'       => (int) $year,
            'champion'   => $champs[$year] ?? null,
            'mvp'        => $make_player($mvp),
            'roty'       => $make_player($roty),
            'ooty'       => $ooty ? ['team' => $ooty['team']] : null,
            'posseBowlMvp' => $make_player($pbmvp),
            'proBowlMvp'   => $make_player($promvp),
            'proBowlWinner'=> $probowl[$year] ?? null,
            'titles'     => $titles_by_year[$year] ?? (object)[],
        ];
    }

    return rest_ensure_response($data);
}


add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/image-settings', [
        'methods'             => 'GET',
        'callback'            => 'pfl_get_image_settings',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/image-settings', [
        'methods'             => 'POST',
        'callback'            => 'pfl_save_image_setting',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_get_image_settings() {
    $settings = get_option('pfl_image_settings', []);
    return rest_ensure_response((object) $settings);
}

function pfl_save_image_setting(WP_REST_Request $request) {
    $pid   = sanitize_text_field($request->get_param('pid'));
    $scale = (float) $request->get_param('scale');
    $x     = (float) $request->get_param('x');
    $y     = (float) $request->get_param('y');

    if (empty($pid)) {
        return new WP_Error('missing_pid', 'Player ID required', ['status' => 400]);
    }

    $settings       = get_option('pfl_image_settings', []);
    $settings[$pid] = ['scale' => $scale, 'x' => $x, 'y' => $y];
    update_option('pfl_image_settings', $settings);

    return rest_ensure_response(['success' => true]);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/upload-player-image', [
        'methods'             => 'POST',
        'callback'            => 'pfl_rest_upload_player_image',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_rest_upload_player_image(WP_REST_Request $request) {
    $pid = sanitize_text_field($request->get_param('pid'));
    if (empty($pid)) {
        return new WP_Error('missing_pid', 'Player ID required', ['status' => 400]);
    }

    if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $err = $_FILES['image']['error'] ?? 'No file received';
        return new WP_Error('upload_error', "File upload error: {$err}", ['status' => 400]);
    }

    $file    = $_FILES['image'];
    $allowed = ['image/jpeg', 'image/jpg', 'image/webp'];
    $mime    = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) {
        return new WP_Error('invalid_type', "Unsupported file type: {$mime}. Use JPG or WebP.", ['status' => 400]);
    }

    $info = getimagesize($file['tmp_name']);
    if (!$info) {
        return new WP_Error('invalid_image', 'Could not read image dimensions.', ['status' => 400]);
    }
    [, $img_h] = $info;
    if ($img_h < 400) {
        return new WP_Error('too_small', "Image is only {$img_h}px tall. Minimum 400px required.", ['status' => 400]);
    }

    $ext      = $mime === 'image/webp' ? 'webp' : 'jpg';
    $tmp_path = sys_get_temp_dir() . '/' . uniqid('pfl_upload_') . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $tmp_path)) {
        return new WP_Error('save_failed', 'Failed to save uploaded file.', ['status' => 500]);
    }

    $script         = escapeshellarg(get_stylesheet_directory() . '/pythonscripts/upload_player_image.py');
    $tmp_escaped    = escapeshellarg($tmp_path);
    $player_escaped = escapeshellarg($pid);
    $cmd = "arch -arm64 /usr/local/bin/python3 {$script} --file {$tmp_escaped} --player {$player_escaped} --no-normalize 2>&1";
    exec($cmd, $output, $exit_code);

    @unlink($tmp_path);

    $output_text = implode("\n", array_filter($output, function ($line) {
        return !preg_match('/^(I0|W0|INFO:)/', $line);
    }));

    if ($exit_code !== 0) {
        return new WP_Error('script_failed', $output_text ?: 'Script failed with no output.', ['status' => 500]);
    }

    // Return updated image URL so the client can refresh immediately
    $attachment = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'meta_query'     => [[
            'key'     => '_wp_attached_file',
            'value'   => $pid,
            'compare' => 'LIKE',
        ]],
    ]);
    $new_img = !empty($attachment) ? wp_get_attachment_url($attachment[0]->ID) : null;

    return rest_ensure_response([
        'success' => true,
        'message' => $output_text,
        'img'     => $new_img,
    ]);
}

// ── Draft meta (ACF fields: date, location, attendees, notes, analysis) ──────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/draft-meta', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_draft_meta',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_draft_meta(WP_REST_Request $request) {
    $year = (int) $request->get_param('year');
    if (!$year) {
        return new WP_Error('missing_year', 'Year required', ['status' => 400]);
    }

    $all = get_fields(195);
    if (!$all) return rest_ensure_response(null);

    $info    = null;
    $details = null;

    if (!empty($all['draft_info'])) {
        foreach ($all['draft_info'] as $row) {
            if ((int) ($row['year'] ?? 0) === $year) {
                $info = $row;
                break;
            }
        }
    }

    if (!empty($all['draft_details'])) {
        foreach ($all['draft_details'] as $row) {
            if ((int) ($row['year'] ?? 0) === $year) {
                $details = $row;
                break;
            }
        }
    }

    if (!$info && !$details) return rest_ensure_response(null);

    return rest_ensure_response([
        'year'          => $year,
        'date'          => $info['date']          ?? null,
        'location'      => $info['location']      ?? null,
        'address'       => $info['address']       ?? null,
        'attendees'     => $details['attendees']  ?? $info['attendees'] ?? null,
        'notes'         => $details['notes']      ?? null,
        'bestPickNotes' => $details['best_pick_notes'] ?? null,
    ]);
}

// ── Draft years list ─────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/draft-years', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_draft_years',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_draft_years() {
    global $wpdb;
    $rows = $wpdb->get_col("SELECT DISTINCT year FROM wp_drafts ORDER BY year DESC");
    return rest_ensure_response(array_map('intval', $rows));
}

// ── Draft by year ─────────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/draft-by-year', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_draft_by_year',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_draft_by_year(WP_REST_Request $request) {
    global $wpdb;

    $year = (int) $request->get_param('year');
    if (!$year) {
        return new WP_Error('missing_year', 'Year required', ['status' => 400]);
    }

    // wp_drafts columns: id, year, round, roundnum, picknum, pickord, team,
    //                    playerfirst, playerlast, pos, playerid, tradeid
    $picks = $wpdb->get_results($wpdb->prepare(
        "SELECT
             d.round,
             d.picknum,
             d.pickord,
             d.playerid,
             d.playerfirst,
             d.playerlast,
             d.pos         AS position,
             d.team,
             t.team        AS team_name,
             pv.valuescore
         FROM wp_drafts d
         LEFT JOIN wp_teams t  ON t.team_int = d.team
         LEFT JOIN wp_drafts_pick_value pv
                ON pv.year = %d
               AND CAST(pv.round AS UNSIGNED) = CAST(d.round AS UNSIGNED)
               AND CAST(pv.picknum AS UNSIGNED) = CAST(d.picknum AS UNSIGNED)
         WHERE d.year = %d
         ORDER BY CAST(d.round AS UNSIGNED), CAST(d.picknum AS UNSIGNED)",
        $year, $year
    ), ARRAY_A);

    // Build helmet number lookup (carry-forward logic matching get_helmet() in results.php)
    $teams_in_draft = array_unique(array_column($picks, 'team'));
    $helmet_by_team = [];
    foreach ($teams_in_draft as $team) {
        $history = $wpdb->get_results($wpdb->prepare(
            "SELECT yearstart, helmet FROM wp_helmet_history WHERE team = %s ORDER BY yearstart ASC",
            $team
        ), ARRAY_A);
        $by_year = [];
        foreach ($history as $row) {
            $by_year[(int) $row['yearstart']] = (int) $row['helmet'];
        }
        $active = 1;
        for ($y = 1991; $y <= $year; $y++) {
            if (isset($by_year[$y])) $active = $by_year[$y];
        }
        $helmet_by_team[$team] = $active;
    }

    $out = [];
    foreach ($picks as $p) {
        $pid = $p['playerid'];
        $img = $pid ? (get_attachment_url_by_slug($pid) ?: null) : null;

        $season_pts = null;
        if ($pid) {
            $sl = $wpdb->get_var($wpdb->prepare(
                "SELECT points FROM wp_season_leaders WHERE playerid = %s AND season = %d LIMIT 1",
                $pid, $year
            ));
            $season_pts = $sl !== null ? (int) $sl : null;
        }

        $out[] = [
            'round'      => (int) ltrim($p['round'], '0') ?: 1,
            'pick'       => (int) ltrim($p['picknum'], '0') ?: 1,
            'overall'    => (int) $p['pickord'],
            'pid'        => $pid ?: null,
            'first'      => $p['playerfirst'],
            'last'       => $p['playerlast'],
            'position'   => $p['position'],
            'team'       => $p['team'],
            'teamName'   => $p['team_name'] ?? $p['team'],
            'img'        => $img,
            'seasonPts'  => $season_pts,
            'valueScore' => $p['valuescore'] !== null ? round((float) $p['valuescore'], 2) : null,
            'helmetNum'  => $helmet_by_team[$p['team']] ?? 1,
        ];
    }

    return rest_ensure_response($out);
}

// ── Draft round distribution (position frequency by round, recency-weighted) ───
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/draft-round-distribution', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_draft_round_distribution',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_draft_round_distribution() {
    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT year, round, pos, COUNT(*) AS picks
         FROM {$wpdb->prefix}drafts
         WHERE pos IN ('QB','RB','WR','PK')
           AND round IS NOT NULL AND round > 0
           AND year IS NOT NULL
         GROUP BY year, round, pos
         ORDER BY year ASC, round ASC, pos ASC",
        ARRAY_A
    );

    $result = [];
    foreach ($rows as $row) {
        $yr = (int) $row['year'];
        $r  = (int) $row['round'];
        $p  = $row['pos'];
        if (!isset($result[$r])) $result[$r] = [];
        if (!isset($result[$r][$p])) $result[$r][$p] = [];
        $result[$r][$p][] = [ 'year' => $yr, 'picks' => (int) $row['picks'] ];
    }

    return rest_ensure_response($result);
}

// ── Draft tendencies (position counts by team/year, for suggestion algorithm) ──
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/draft-tendencies', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_draft_tendencies',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_draft_tendencies() {
    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT year, team, pos, COUNT(*) AS picks
         FROM {$wpdb->prefix}drafts
         WHERE pos IN ('QB','RB','WR','PK') AND team != '' AND team IS NOT NULL
         GROUP BY year, team, pos
         ORDER BY year ASC",
        ARRAY_A
    );

    $result = [];
    foreach ($rows as $row) {
        $tid = $row['team'];
        if (!isset($result[$tid])) $result[$tid] = [];
        $result[$tid][] = [
            'year'  => (int) $row['year'],
            'pos'   => $row['pos'],
            'picks' => (int) $row['picks'],
        ];
    }

    return rest_ensure_response($result);
}

// ── Player list (all players) ─────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/players', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_players_list',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_players_list() {
    global $wpdb;
    $rows = $wpdb->get_results(
        "SELECT p_id AS pid, playerFirst AS first, playerLast AS last, position
         FROM wp_players
         ORDER BY playerLast, playerFirst",
        ARRAY_A
    );
    return rest_ensure_response($rows);
}

// ── Single player card data ───────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-card', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_card',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_player_card(WP_REST_Request $request) {
    global $wpdb;

    $pid = sanitize_text_field($request->get_param('pid'));
    if (empty($pid)) {
        return new WP_Error('missing_pid', 'Player ID required', ['status' => 400]);
    }

    $award_labels = [
        'mvp'    => 'Most Valuable Player',
        'pflmvp' => 'Most Valuable Player',
        'pbm'    => 'Posse Bowl MVP',
        'pbmvp'  => 'Posse Bowl MVP',
        'pro'    => 'Pro Bowl MVP',
        'promvp' => 'Pro Bowl MVP',
        'roty'   => 'Rookie of the Year',
        'ooty'   => 'Owner of the Year',
    ];

    // Basic player info
    $player = $wpdb->get_row($wpdb->prepare(
        "SELECT p_id, playerFirst, playerLast, position FROM wp_players WHERE p_id = %s LIMIT 1",
        $pid
    ), ARRAY_A);

    if (!$player) {
        return new WP_Error('not_found', 'Player not found', ['status' => 404]);
    }

    $img = get_attachment_url_by_slug($pid) ?: null;

    // Career stats from season leaders
    $sl = $wpdb->get_row($wpdb->prepare(
        "SELECT SUM(points) as pts, SUM(games) as gms,
                MIN(season) as first_yr, MAX(season) as last_yr
         FROM wp_season_leaders WHERE playerid = %s",
        $pid
    ), ARRAY_A);

    // Career high + W-L from individual player table
    $table = preg_replace('/[^a-zA-Z0-9]/', '', $pid);

    $primary_team_int = $wpdb->get_var(
        "SELECT team FROM `{$table}` WHERE team != '' AND team IS NOT NULL
         GROUP BY team ORDER BY COUNT(*) DESC LIMIT 1"
    );
    $primary_team_name = $primary_team_int
        ? $wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $primary_team_int
          ))
        : null;

    $hl = $wpdb->get_row(
        "SELECT MAX(points) as high, SUM(win_loss) as wins, COUNT(*) as games
         FROM `{$table}` WHERE team != ''",
        ARRAY_A
    );

    $total_pts = (int)   ($sl['pts']      ?? 0);
    $total_gms = (int)   ($sl['gms']      ?? 0);
    $first_yr  = (int)   ($sl['first_yr'] ?? 0);
    $last_yr   = (int)   ($sl['last_yr']  ?? 0);
    $high      = (int)   ($hl['high']     ?? 0);
    $wins      = (int)   ($hl['wins']     ?? 0);
    $losses    = $total_gms - $wins;
    $ppg       = $total_gms > 0 ? round($total_pts / $total_gms, 1) : 0;
    $win_pct   = $total_gms > 0 ? round($wins / $total_gms, 3)      : 0;

    // Awards (excluding HOF)
    $award_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT awardID, award, year FROM wp_awards
         WHERE pid = %s AND award != 'Hall of Fame Inductee'
         ORDER BY year",
        $pid
    ), ARRAY_A);

    $awards = [];
    foreach ($award_rows as $a) {
        $prefix   = substr($a['awardID'], 0, -4);
        $awards[] = [
            'id'    => $a['awardID'],
            'label' => $award_labels[$prefix] ?? $a['award'],
            'year'  => (int) $a['year'],
        ];
    }

    // Scoring titles
    $title_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, year, points, teams, pos FROM wp_number_ones
         WHERE playerid = %s ORDER BY year",
        $pid
    ), ARRAY_A);

    $scoring_titles = [];
    foreach ($title_rows as $t) {
        $team_name        = $wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $t['teams']
        ));
        $scoring_titles[] = [
            'year'     => (int) $t['year'],
            'points'   => (int) $t['points'],
            'team'     => $t['teams'],
            'teamName' => $team_name ?? $t['teams'],
            'pos'      => $t['pos'],
        ];
    }

    $game_streak = (int) get_player_game_streak($pid);

    $potw = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM wp_player_of_week WHERE playerid = %s", $pid
    ));

    $pb_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT p.year, p.team,
                t.team as team_name,
                IF(c.winTeam IS NOT NULL, 1, 0) as champion
         FROM wp_playoffs p
         JOIN wp_teams t ON t.team_int = p.team
         LEFT JOIN wp_champions c ON c.year = p.year AND c.winTeam = p.team
         WHERE p.playerid = %s AND p.week = 16
         ORDER BY p.year",
        $pid
    ), ARRAY_A);

    $posse_bowl_apps = [];
    foreach ($pb_rows as $pb) {
        $posse_bowl_apps[] = [
            'year'     => (int) $pb['year'],
            'team'     => $pb['team'],
            'teamName' => $pb['team_name'],
            'champion' => (bool) $pb['champion'],
        ];
    }

    // HOF induction year (null if not inducted)
    $induction_year = $wpdb->get_var($wpdb->prepare(
        "SELECT year FROM wp_awards WHERE pid = %s AND award = 'Hall of Fame Inductee' LIMIT 1",
        $pid
    ));

    return rest_ensure_response([
        'pid'           => $pid,
        'first'         => $player['playerFirst'],
        'last'          => $player['playerLast'],
        'position'      => $player['position'],
        'inductionYear' => $induction_year ? (int) $induction_year : null,
        'img'           => $img,
        'totalPoints'   => $total_pts,
        'totalGames'    => $total_gms,
        'firstYear'     => $first_yr,
        'lastYear'      => $last_yr,
        'primaryTeam'   => $primary_team_name ?? $primary_team_int,
        'ppg'           => $ppg,
        'careerHigh'    => $high,
        'wins'          => $wins,
        'losses'        => $losses,
        'winPct'        => $win_pct,
        'gameStreak'    => $game_streak,
        'awards'        => $awards,
        'scoringTitles' => $scoring_titles,
        'potwCount'     => $potw,
        'posseBowlApps' => $posse_bowl_apps,
    ]);
}

// ── All-time leaders by position ──────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/leaders', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_leaders',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_leaders(WP_REST_Request $request) {
    global $wpdb;

    $position = strtoupper($request->get_param('position') ?? '');
    $current_season = (int) $wpdb->get_var("SELECT MAX(season) FROM wp_season_leaders");

    $where = $position ? $wpdb->prepare("WHERE al.position = %s", $position) : "";

    $rows = $wpdb->get_results("
        SELECT
            al.pid,
            al.points,
            al.games,
            al.seasons,
            al.high,
            al.lastyear,
            al.position,
            p.playerFirst  AS first,
            p.playerLast   AS last,
            (SELECT COUNT(*) FROM wp_awards a WHERE a.pid = al.pid AND a.award = 'Hall of Fame Inductee') AS in_hof,
            (SELECT COUNT(*) FROM wp_awards a WHERE a.pid = al.pid AND a.award = 'Most Valuable Player')  AS mvps,
            (SELECT COUNT(*) FROM wp_awards a WHERE a.pid = al.pid AND a.award = 'Rookie of the Year')    AS rotys,
            (SELECT COUNT(*) FROM wp_awards a WHERE a.pid = al.pid AND a.award = 'Posse Bowl MVP')        AS pbmvps,
            (SELECT COUNT(*) FROM wp_awards a WHERE a.pid = al.pid AND a.award = 'Pro Bowl MVP')          AS promvps
        FROM wp_allleaders al
        LEFT JOIN wp_players p ON p.p_id = al.pid
        $where
        ORDER BY al.points DESC
    ", ARRAY_A);

    // Fetch Posse Bowl appearances (week 16) for all players in one query
    $pids = array_column($rows, 'pid');
    $appearances_by_pid = [];
    if (!empty($pids)) {
        $placeholders = implode(',', array_fill(0, count($pids), '%s'));
        $app_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT playerid AS pid, year, result AS won
             FROM wp_playoffs
             WHERE week = 16 AND playerid IN ($placeholders)
             ORDER BY year ASC",
            ...$pids
        ), ARRAY_A);
        foreach ($app_rows as $a) {
            $appearances_by_pid[$a['pid']][] = [
                'year' => (int) $a['year'],
                'won'  => (bool) (int) $a['won'],
            ];
        }
    }

    $data = [];
    foreach ($rows as $i => $row) {
        $games  = (int) $row['games'];
        $points = (int) $row['points'];
        $pid    = $row['pid'];
        $data[] = [
            'rank'             => $i + 1,
            'pid'              => $pid,
            'img'              => pfl_player_img_url($pid),
            'first'            => $row['first'],
            'last'             => $row['last'],
            'position'         => $row['position'],
            'points'           => $points,
            'games'            => $games,
            'seasons'          => (int) $row['seasons'],
            'high'             => (int) $row['high'],
            'ppg'              => $games > 0 ? round($points / $games, 1) : 0,
            'lastyear'         => (int) $row['lastyear'],
            'active'           => (int) $row['lastyear'] >= $current_season,
            'inHof'            => (int) $row['in_hof'] > 0,
            'mvps'             => (int) $row['mvps'],
            'rotys'            => (int) $row['rotys'],
            'pbmvps'           => (int) $row['pbmvps'],
            'promvps'          => (int) $row['promvps'],
            'champAppearances' => $appearances_by_pid[$pid] ?? [],
        ];
    }

    return rest_ensure_response($data);
}

// ── Player profile card ───────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-profile', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_profile',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_player_profile(WP_REST_Request $request) {
    global $wpdb;

    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return new WP_Error('missing_pid', 'pid required', ['status' => 400]);

    $player = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM wp_players WHERE p_id = %s", $pid
    ), ARRAY_A);
    if (!$player) return new WP_Error('not_found', 'Player not found', ['status' => 404]);

    // Image
    $img = get_attachment_url_by_slug($pid) ?: null;

    // HOF induction
    $hof_year = $wpdb->get_var($wpdb->prepare(
        "SELECT year FROM wp_awards WHERE pid = %s AND award = 'Hall of Fame Inductee' LIMIT 1", $pid
    ));

    // Ring of Honor team
    $roh_team = $wpdb->get_var($wpdb->prepare(
        "SELECT pm_team.meta_value
         FROM wp_postmeta pm_pid
         JOIN wp_postmeta pm_team
           ON pm_team.post_id = pm_pid.post_id
           AND REPLACE(pm_pid.meta_key, '_player_id', '_team') = pm_team.meta_key
         WHERE pm_pid.meta_key LIKE 'honored_player_%_player_id'
           AND pm_pid.meta_value = %s
         LIMIT 1",
        $pid
    ));
    $roh_team_name = null;
    if ($roh_team) {
        $roh_team_name = $wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $roh_team
        ));
    }

    // First draft
    $first_draft = $wpdb->get_row($wpdb->prepare(
        "SELECT d.year AS season, d.round, d.picknum AS pick, COALESCE(t.team, d.team) AS teamName
         FROM wp_drafts d
         LEFT JOIN wp_teams t ON t.team_int = d.team
         WHERE d.playerid = %s
         ORDER BY CAST(d.year AS UNSIGNED) ASC LIMIT 1",
        $pid
    ), ARRAY_A);

    // Teams (distinct, ordered by first year on that team)
    $teams = $wpdb->get_results($wpdb->prepare(
        "SELECT t.team AS teamName, MIN(r.year) AS firstYear
         FROM wp_rosters r
         LEFT JOIN wp_teams t ON t.team_int = r.team
         WHERE r.pid = %s AND r.team != '' AND t.team IS NOT NULL
         GROUP BY r.team
         ORDER BY MIN(r.year) ASC",
        $pid
    ), ARRAY_A);

    $all_settings    = get_option('pfl_image_settings', []);
    $player_settings = $all_settings[$pid] ?? null;

    // Posse Bowl appearances (week 16) and wins
    $playoffs = playerplayoffs($pid) ?? [];
    $just_champs = get_just_champions();
    $pb_appearances = [];
    $pb_wins = [];
    foreach ($playoffs as $game) {
        if ($game['week'] == '16') {
            $year = (int) $game['year'];
            $pb_appearances[] = $year;
            if (isset($just_champs[$year]) && $just_champs[$year] === $game['team']) {
                $pb_wins[] = $year;
            }
        }
    }
    $posse_bowl_data = (!empty($pb_appearances) || !empty($pb_wins)) ? [
        'appearances' => $pb_appearances,
        'wins'        => $pb_wins,
    ] : null;

    // Position scoring titles (#1 in position group for a season)
    $number_ones_raw = $wpdb->get_results($wpdb->prepare(
        "SELECT year, points, teams AS team, pos FROM wp_number_ones WHERE playerid = %s ORDER BY year ASC",
        $pid
    ), ARRAY_A);
    $scoring_titles = array_values(array_map(fn($r) => [
        'year'   => (int) $r['year'],
        'points' => (int) $r['points'],
        'team'   => $r['team'],
        'pos'    => $r['pos'],
    ], $number_ones_raw));

    // PVQ leader seasons (pvq = 1.000)
    $pvq_leader_raw = $wpdb->get_results($wpdb->prepare(
        "SELECT year, pvq FROM wp_player_pvqs WHERE playerid = %s AND CAST(pvq AS DECIMAL(10,8)) >= 0.99999999 ORDER BY year ASC",
        $pid
    ), ARRAY_A);
    $pvq_leader = array_values(array_map(fn($r) => [
        'year' => (int) $r['year'],
        'pvq'  => round((float) $r['pvq'], 3),
    ], $pvq_leader_raw));

    // Pro Bowl selections
    $probowl_raw = probowl_boxscores_player($pid) ?? [];
    $probowl_data = [];
    $starter_map = [0 => 'Starter', 1 => 'Backup', 2 => 'Alternate'];
    foreach ($probowl_raw as $r) {
        $probowl_data[] = [
            'year'    => (int) $r['year'],
            'league'  => $r['league'],
            'starter' => $starter_map[(int) $r['starter']] ?? 'Backup',
        ];
    }
    usort($probowl_data, fn($a, $b) => $a['year'] <=> $b['year']);

    // Player of the Week
    $potw_raw = get_player_of_week_player($pid) ?? [];
    $potw_data = [];
    foreach ($potw_raw as $weekid) {
        $year = (int) substr($weekid, 0, 4);
        $week = (int) substr($weekid, -2);
        $potw_data[] = [
            'year'    => $year,
            'week'    => $week,
            'playoff' => $week === 15,
        ];
    }
    usort($potw_data, fn($a, $b) => $a['year'] <=> $b['year'] ?: $a['week'] <=> $b['week']);

    // Awards
    $raw_awards = get_player_award($pid) ?? [];
    $awards_data = array_values(array_map(fn($a) => [
        'year'  => (int) $a['year'],
        'award' => $a['award'],
    ], $raw_awards));

    // Active status — player is active if on a roster in the most recent season
    $max_roster_year = (int) $wpdb->get_var("SELECT MAX(CAST(year AS UNSIGNED)) FROM wp_rosters");
    $is_active = $max_roster_year > 0 && (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM wp_rosters WHERE pid = %s AND year = %d", $pid, $max_roster_year
    )) > 0;

    return rest_ensure_response([
        'pid'           => $pid,
        'first'         => $player['playerFirst'],
        'last'          => $player['playerLast'],
        'position'      => $player['position'],
        'rookie'        => $player['rookie'] ? (int) $player['rookie'] : null,
        'mflid'         => $player['mflid'],
        'pfruri'        => $player['pfruri'],
        'height'        => $player['height'],
        'weight'        => $player['weight'] ? (int) $player['weight'] : null,
        'college'       => $player['college'],
        'number'        => $player['number'] ? (int) $player['number'] : null,
        'nickname'      => $player['nickname'],
        'img'           => $img,
        'imageSettings' => $player_settings ? [
            'scale' => (float) $player_settings['scale'],
            'x'     => (float) $player_settings['x'],
            'y'     => (float) $player_settings['y'],
        ] : null,
        'active'     => $is_active,
        'hofYear'    => $hof_year ? (int) $hof_year : null,
        'rohTeam'    => $roh_team_name,
        'firstDraft' => $first_draft ? [
            'season'   => (int) $first_draft['season'],
            'round'    => ltrim($first_draft['round'], '0') ?: '1',
            'pick'     => ltrim($first_draft['pick'], '0') ?: '1',
            'teamName' => $first_draft['teamName'],
        ] : null,
        'teams'      => array_values(array_map(fn($t) => $t['teamName'], $teams)),
        'posseBowl'     => $posse_bowl_data,
        'awards'        => $awards_data,
        'scoringTitles' => $scoring_titles,
        'pvqLeader'     => $pvq_leader,
        'potw'          => $potw_data,
        'proBowl'       => $probowl_data,
    ]);
}

// ── Career stat records (overall + by position) ───────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/career-records', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_career_records',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_career_records() {
    global $wpdb;

    $positions = ['QB', 'RB', 'WR', 'PK'];

    $row_to_record = function($row) {
        return [
            'points'  => (float) ($row['points']  ?? 0),
            'games'   => (float) ($row['games']   ?? 0),
            'ppg'     => round((float) ($row['ppg'] ?? 0), 1),
            'seasons' => (float) ($row['seasons']  ?? 0),
            'high'    => (float) ($row['high']     ?? 0),
            'streak'  => (float) ($row['streak']   ?? 0),
        ];
    };

    // ── Regular season (from wp_allleaders cache) ────────────────────────────
    $overall = $wpdb->get_row(
        "SELECT MAX(points) AS points, MAX(games) AS games,
                MAX(seasons) AS seasons, MAX(high) AS high,
                MAX(gamestreak) AS streak,
                MAX(points / NULLIF(games, 0)) AS ppg
         FROM wp_allleaders WHERE games > 0",
        ARRAY_A
    );

    $by_position = [];
    foreach ($positions as $pos) {
        $r = $wpdb->get_row($wpdb->prepare(
            "SELECT MAX(points) AS points, MAX(games) AS games,
                    MAX(seasons) AS seasons, MAX(high) AS high,
                    MAX(gamestreak) AS streak,
                    MAX(points / NULLIF(games, 0)) AS ppg
             FROM wp_allleaders WHERE games > 0 AND position = %s",
            $pos
        ), ARRAY_A);
        if ($r) $by_position[$pos] = $row_to_record($r);
    }

    // ── Postseason (from wp_playoffs) ────────────────────────────────────────
    $ps_rows = $wpdb->get_results(
        "SELECT al.position,
                SUM(pl.points)                             AS pts,
                COUNT(*)                                   AS games,
                SUM(pl.points) / NULLIF(COUNT(*), 0)       AS ppg
         FROM wp_playoffs pl
         JOIN wp_allleaders al ON al.pid = pl.playerid
         GROUP BY pl.playerid, al.position",
        ARRAY_A
    );

    $ps_ov  = ['points' => 0.0, 'games' => 0.0, 'ppg' => 0.0];
    $ps_pos = [];
    foreach ($ps_rows as $row) {
        $pts   = (float) $row['pts'];
        $games = (float) $row['games'];
        $ppg   = (float) $row['ppg'];
        $pos   = $row['position'];
        $ppg_r = round($ppg, 1);
        if ($pts   > $ps_ov['points']) $ps_ov['points'] = $pts;
        if ($games > $ps_ov['games'])  $ps_ov['games']  = $games;
        if ($ppg_r > $ps_ov['ppg'])    $ps_ov['ppg']    = $ppg_r;
        if (!isset($ps_pos[$pos])) $ps_pos[$pos] = ['points' => 0.0, 'games' => 0.0, 'ppg' => 0.0];
        if ($pts   > $ps_pos[$pos]['points']) $ps_pos[$pos]['points'] = $pts;
        if ($games > $ps_pos[$pos]['games'])  $ps_pos[$pos]['games']  = $games;
        if ($ppg_r > $ps_pos[$pos]['ppg'])    $ps_pos[$pos]['ppg']    = $ppg_r;
    }

    // ── NFL career records (mirrors tables-nfl.php logic exactly) ────────────
    $nfl_records = get_transient('pfl_nfl_career_records_v4');
    if ($nfl_records === false) {
        $all_pids = just_player_ids();
        $stat_map = [
            'pass_yds'  => 'passingyards',
            'pass_tds'  => 'passingtds',
            'pass_int'  => 'passingint',
            'rush_yds'  => 'rushyrds',
            'rush_tds'  => 'rushtds',
            'rec_yds'   => 'recyrds',
            'rec_tds'   => 'rectds',
            'fgm'       => 'fgm',
            'xpm'       => 'xpm',
        ];
        $nfl_records = ['overall' => [], 'byPosition' => []];
        foreach ($all_pids as $pid) {
            $pos = substr($pid, -2);
            $deets = get_player_career_stats($pid);
            if (empty($deets)) continue;
            foreach ($stat_map as $key => $field) {
                $v = (float) ($deets[$field] ?? 0);
                if (!isset($nfl_records['overall'][$key]) || $v > $nfl_records['overall'][$key])
                    $nfl_records['overall'][$key] = $v;
                if (!isset($nfl_records['byPosition'][$pos][$key]) || $v > $nfl_records['byPosition'][$pos][$key])
                    $nfl_records['byPosition'][$pos][$key] = $v;
            }
        }
        set_transient('pfl_nfl_career_records_v4', $nfl_records, DAY_IN_SECONDS);
    }

    return rest_ensure_response([
        'overall'    => $row_to_record($overall),
        'byPosition' => $by_position,
        'postseason' => [
            'overall'    => $ps_ov,
            'byPosition' => $ps_pos,
        ],
        'nfl' => $nfl_records,
    ]);
}

// ── Player career stats ───────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-career-stats', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_career_stats',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_player_career_stats(WP_REST_Request $request) {
    global $wpdb;

    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return new WP_Error('missing_pid', 'pid required', ['status' => 400]);

    $position = $wpdb->get_var($wpdb->prepare(
        "SELECT position FROM wp_players WHERE p_id = %s", $pid
    ));
    if (!$position) return new WP_Error('not_found', 'Player not found', ['status' => 404]);

    $stats  = get_player_career_stats($pid);
    if (!$stats) return new WP_Error('no_stats', 'No stats found', ['status' => 404]);

    $streak  = (int) get_player_game_streak($pid);
    $wins    = (int) ($stats['wins']   ?? 0);
    $losses  = (int) ($stats['loss']   ?? 0);
    $games   = $wins + $losses;
    $winpct  = $games > 0 ? round($wins / $games, 3) : 0;

    // Postseason stats from wp_playoffs
    $playoff_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT points, result FROM wp_playoffs WHERE playerid = %s", $pid
    ), ARRAY_A);

    $playoff_pts    = 0;
    $playoff_games  = 0;
    $playoff_wins   = 0;
    foreach ($playoff_rows as $row) {
        $playoff_pts   += (float) $row['points'];
        $playoff_games++;
        $playoff_wins  += (int) $row['result'];
    }
    $playoff_losses = $playoff_games - $playoff_wins;
    $playoff_ppg    = $playoff_games > 0 ? round($playoff_pts / $playoff_games, 1) : 0;

    // Position-specific NFL career stats
    $nfl = [];
    switch ($position) {
        case 'QB':
            $nfl = [
                ['label' => 'Pass Yds', 'value' => number_format((int)($stats['passingyards'] ?? 0)), 'raw' => (int)($stats['passingyards'] ?? 0), 'recordKey' => 'pass_yds'],
                ['label' => 'Pass TDs', 'value' => (int)($stats['passingtds'] ?? 0),                  'raw' => (int)($stats['passingtds'] ?? 0),   'recordKey' => 'pass_tds'],
                ['label' => 'Int',      'value' => (int)($stats['passingint'] ?? 0),                  'raw' => (int)($stats['passingint'] ?? 0),   'recordKey' => 'pass_int'],
                ['label' => 'Rush Yds', 'value' => number_format((int)($stats['rushyrds'] ?? 0)),     'raw' => (int)($stats['rushyrds'] ?? 0),     'recordKey' => 'rush_yds'],
            ];
            break;
        case 'RB':
            $nfl = [
                ['label' => 'Rush Yds', 'value' => number_format((int)($stats['rushyrds'] ?? 0)), 'raw' => (int)($stats['rushyrds'] ?? 0), 'recordKey' => 'rush_yds'],
                ['label' => 'Rush TDs', 'value' => (int)($stats['rushtds'] ?? 0),                 'raw' => (int)($stats['rushtds'] ?? 0), 'recordKey' => 'rush_tds'],
                ['label' => 'Rec Yds',  'value' => number_format((int)($stats['recyrds'] ?? 0)),  'raw' => (int)($stats['recyrds'] ?? 0), 'recordKey' => 'rec_yds'],
                ['label' => 'Rec TDs',  'value' => (int)($stats['rectds'] ?? 0),                  'raw' => (int)($stats['rectds'] ?? 0),  'recordKey' => 'rec_tds'],
            ];
            break;
        case 'WR':
            $nfl = [
                ['label' => 'Rec Yds',  'value' => number_format((int)($stats['recyrds'] ?? 0)),  'raw' => (int)($stats['recyrds'] ?? 0), 'recordKey' => 'rec_yds'],
                ['label' => 'Rec TDs',  'value' => (int)($stats['rectds'] ?? 0),                  'raw' => (int)($stats['rectds'] ?? 0),  'recordKey' => 'rec_tds'],
                ['label' => 'Rush Yds', 'value' => number_format((int)($stats['rushyrds'] ?? 0)), 'raw' => (int)($stats['rushyrds'] ?? 0), 'recordKey' => 'rush_yds'],
                ['label' => 'Rush TDs', 'value' => (int)($stats['rushtds'] ?? 0),                 'raw' => (int)($stats['rushtds'] ?? 0), 'recordKey' => 'rush_tds'],
            ];
            break;
        case 'PK':
            $fgm = (int)($stats['fgm'] ?? 0);
            $fga = (int)($stats['fga'] ?? 0);
            $xpm = (int)($stats['xpm'] ?? 0);
            $xpa = (int)($stats['xpa'] ?? 0);
            $nfl = [
                ['label' => 'FGM/FGA', 'value' => "{$fgm}/{$fga}", 'raw' => $fgm, 'recordKey' => 'fgm'],
                ['label' => 'FG%',     'value' => $fga > 0 ? round($fgm / $fga, 3) : 0, 'raw' => null, 'recordKey' => null],
                ['label' => 'XPM/XPA', 'value' => "{$xpm}/{$xpa}", 'raw' => $xpm, 'recordKey' => 'xpm'],
                ['label' => 'XP%',     'value' => $xpa > 0 ? round($xpm / $xpa, 3) : 0, 'raw' => null, 'recordKey' => null],
            ];
            break;
    }

    return rest_ensure_response([
        'points'  => (float) ($stats['points']  ?? 0),
        'games'   => (int)   ($stats['games']   ?? 0),
        'ppg'     => (float) ($stats['ppg']     ?? 0),
        'seasons' => (int)   ($stats['seasons'] ?? 0),
        'wins'    => $wins,
        'losses'  => $losses,
        'winPct'  => $winpct,
        'high'    => (float) ($stats['high'] ?? 0),
        'streak'  => $streak,
        'postseason' => [
            'points'  => $playoff_pts,
            'games'   => $playoff_games,
            'ppg'     => $playoff_ppg,
            'wins'    => $playoff_wins,
            'losses'  => $playoff_losses,
        ],
        'nfl'     => $nfl,
    ]);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-season-stats', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_season_stats',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_player_season_stats(WP_REST_Request $request) {
    global $wpdb;

    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return new WP_Error('missing_pid', 'Missing pid', ['status' => 400]);

    // Verify player table exists
    $table_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
        DB_NAME, $pid
    ));
    if (!$table_exists) return new WP_Error('not_found', 'Player not found', ['status' => 404]);

    // Get first/last year from wp_allleaders so we can fill in empty seasons
    $career = $wpdb->get_row($wpdb->prepare(
        "SELECT firstyear, lastyear FROM wp_allleaders WHERE pid = %s LIMIT 1", $pid
    ), ARRAY_A);
    if (!$career) return rest_ensure_response([]);

    $first_year = (int) $career['firstyear'];
    $last_year  = (int) $career['lastyear'];

    // Aggregate per-year from the player's individual table
    $rows = $wpdb->get_results(
        "SELECT year, team, SUM(points) AS points, COUNT(*) AS games, MAX(points) AS high
         FROM `{$pid}`
         GROUP BY year, team
         ORDER BY year ASC, COUNT(*) DESC",
        ARRAY_A
    );

    // Group by year (primary team = most-games team, listed first due to ORDER BY)
    $by_year = [];
    foreach ($rows as $r) {
        $year = (int) $r['year'];
        if (!isset($by_year[$year])) {
            $by_year[$year] = [
                'team_int' => $r['team'],
                'points'   => 0,
                'games'    => 0,
                'high'     => 0,
            ];
        }
        $by_year[$year]['points'] += (float) $r['points'];
        $by_year[$year]['games']  += (int)   $r['games'];
        $by_year[$year]['high']    = max($by_year[$year]['high'], (float) $r['high']);
    }

    // Protections keyed by year
    $protected_years = [];
    $protections = get_protections_player($pid) ?? [];
    foreach ($protections as $p) {
        $protected_years[(int) $p['year']] = true;
    }

    // PVQs keyed by year
    $pvq_rows = $wpdb->get_results(
        $wpdb->prepare("SELECT year, pvq FROM wp_player_pvqs WHERE playerid = %s", $pid),
        ARRAY_A
    );
    $pvqs = [];
    foreach ($pvq_rows as $r) {
        $pvqs[(int) $r['year']] = round((float) $r['pvq'], 3);
    }

    // Build season list from first to last year
    $seasons = [];
    for ($year = $first_year; $year <= $last_year; $year++) {
        if (!isset($by_year[$year])) {
            $seasons[] = ['year' => $year, 'team' => null, 'team_abbr' => null, 'points' => null, 'games' => null, 'ppg' => null, 'high' => null, 'rank' => null, 'pvq' => null, 'protected' => isset($protected_years[$year])];
            continue;
        }

        $d      = $by_year[$year];
        $games  = $d['games'];
        $points = $d['points'];

        $team_name = $wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $d['team_int']
        ));

        $rank = get_player_season_rank($pid, $year);

        $seasons[] = [
            'year'      => $year,
            'team'      => $team_name ?? $d['team_int'],
            'team_abbr' => $d['team_int'],
            'points'    => $points,
            'games'     => $games,
            'ppg'       => $games > 0 ? round($points / $games, 1) : null,
            'high'      => $d['high'],
            'rank'      => $rank > 0 ? $rank : null,
            'pvq'       => $pvqs[$year] ?? null,
            'protected' => isset($protected_years[$year]),
        ];
    }

    return rest_ensure_response($seasons);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-game-stats', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_game_stats',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_player_game_stats(WP_REST_Request $request) {
    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return new WP_Error('missing_pid', 'Missing pid', ['status' => 400]);

    $weekly = get_player_data($pid);
    if (empty($weekly)) return rest_ensure_response([]);

    $player_ot = get_player_overtime($pid) ?? [];

    // Build a stadium lookup keyed by uppercase team abbreviation
    $teams = get_teams();
    $stadiums = [];
    foreach ($teams as $key => $val) {
        $stadiums[strtoupper($key)] = $val['stadium'] ?? '';
    }

    // Helper: resolve stadium name with year-based overrides
    $resolve_stadium = function($team_abbr, $year) use ($stadiums) {
        $name = $stadiums[strtoupper($team_abbr)] ?? '';
        if ($name === 'Spankoni Center' && $year <= 2004) $name = 'The Gonad Bowl';
        if ($name === 'The Woodshed'    && $year <= 2017) $name = 'Hutchence Field';
        return $name;
    };

    $games = [];
    foreach ($weekly as $row) {
        $weekids  = $row['weekids'];
        $year     = (int) $row['year'];
        $week     = (int) $row['week'];
        $points   = (float) $row['points'];
        $team     = $row['team'];
        $versus   = $row['versus'];
        $win_loss = (int) $row['win_loss'];
        $home_away = $row['home_away'];

        $is_home  = ($home_away === 'H');
        $location = $is_home
            ? $resolve_stadium($team, $year)
            : $resolve_stadium($versus, $year);

        $games[] = [
            'year'     => $year,
            'week'     => $week,
            'team'     => $team,
            'points'   => $points,
            'versus'   => $versus,
            'result'   => $win_loss === 1 ? 'Win' : 'Loss',
            'location' => $location,
            'home'     => $is_home,
            'ot'       => isset($player_ot[$weekids]) ? true : false,
        ];
    }

    return rest_ensure_response($games);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-season-chart', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_season_chart',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_player_season_chart(WP_REST_Request $request) {
    global $wpdb;
    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return new WP_Error('missing_pid', 'Missing pid', ['status' => 400]);

    $pos = strtoupper(substr($pid, -2));

    // All seasons 1991 → current
    $all_seasons = the_seasons();

    // Player points by season (sum in case of multi-team years)
    $player_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT season, SUM(points) AS points FROM wp_season_leaders WHERE playerid = %s GROUP BY season",
        $pid
    ), ARRAY_A);
    $player_pts = [];
    foreach ($player_rows as $r) {
        $player_pts[(int) $r['season']] = round((float) $r['points'], 1);
    }

    // Per-season position high and average (aggregate per player first, then derive stats)
    $pos_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT season, SUM(points) AS total_pts FROM wp_season_leaders WHERE playerid LIKE %s GROUP BY season, playerid",
        '%' . $pos
    ), ARRAY_A);
    $pos_by_season = [];
    foreach ($pos_rows as $r) {
        $yr = (int) $r['season'];
        $pts = (float) $r['total_pts'];
        if (!isset($pos_by_season[$yr])) $pos_by_season[$yr] = [];
        $pos_by_season[$yr][] = $pts;
    }

    $data = [];
    foreach ($all_seasons as $year) {
        $entry = ['year' => $year, 'points' => null, 'posHigh' => null, 'posAvg' => null];
        if (isset($player_pts[$year])) {
            $entry['points'] = $player_pts[$year];
        }
        if (!empty($pos_by_season[$year])) {
            $entry['posHigh'] = round(max($pos_by_season[$year]), 1);
            $entry['posAvg']  = round(array_sum($pos_by_season[$year]) / count($pos_by_season[$year]), 1);
        }
        $data[] = $entry;
    }

    return rest_ensure_response($data);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-playoff-stats', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_playoff_stats',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_player_playoff_stats(WP_REST_Request $request) {
    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return new WP_Error('missing_pid', 'Missing pid', ['status' => 400]);

    $playoffs = playerplayoffs($pid);
    if (empty($playoffs)) return rest_ensure_response([]);

    $just_champs = get_just_champions();

    $rows = [];
    foreach ($playoffs as $g) {
        $year   = (int) $g['year'];
        $week   = (int) $g['week'];
        $won    = (int) $g['result'] === 1;
        $week_label = $week === 16 ? 'Posse Bowl' : 'Playoffs';

        if ($week === 16 && $won) {
            $result = 'Champion';
        } elseif ($won) {
            $result = 'Advanced';
        } else {
            $result = 'Lost';
        }

        $rows[] = [
            'year'   => $year,
            'week'   => $week_label,
            'team'   => $g['team'],
            'points' => (float) $g['points'],
            'versus' => $g['versus'],
            'result' => $result,
        ];
    }

    // Sort by year asc, then week asc (15 before 16)
    usort($rows, fn($a, $b) => $a['year'] <=> $b['year'] ?: ($a['week'] === 'Playoffs' ? -1 : 1));

    return rest_ensure_response($rows);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-nfl-boxscores', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_nfl_boxscores',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('pfl/v1', '/player-transactions', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_transactions',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('pfl/v1', '/player-trades', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_trades',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('pfl/v1', '/player-jerseys', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_jerseys',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_player_nfl_boxscores(WP_REST_Request $request) {
    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return new WP_Error('missing_pid', 'Missing pid', ['status' => 400]);

    $weekly = get_player_data($pid);
    if (empty($weekly)) return rest_ensure_response([]);

    // Determine position from pid suffix (e.g. "2015WinsQB" → "QB")
    $position = strtoupper(substr($pid, -2));

    $rows = [];
    foreach ($weekly as $row) {
        $year      = (int) $row['year'];
        $week      = (int) $row['week'];
        $game_date = $row['game_date'] ?? '';
        $date_fmt  = $game_date ? date('m/d', strtotime($game_date)) : '';
        $game      = trim(($row['nflteam'] ?? '') . ' ' . ($row['game_location'] ?? '') . ' ' . ($row['nflopp'] ?? ''));

        $entry = [
            'year'      => $year,
            'week'      => $week,
            'date'      => $date_fmt,
            'game'      => $game,
            'twopt'     => (int) ($row['twopt'] ?? 0),
            'nflscore'  => (float) ($row['nflscore'] ?? 0),
            'points'    => (float) ($row['points'] ?? 0),
            'scorediff' => (float) ($row['scorediff'] ?? 0),
            'position'  => $position,
        ];

        if ($position === 'PK') {
            $entry['xpm'] = (int) ($row['xpm'] ?? 0);
            $entry['xpa'] = (int) ($row['xpa'] ?? 0);
            $entry['fgm'] = (int) ($row['fgm'] ?? 0);
            $entry['fga'] = (int) ($row['fga'] ?? 0);
        } else {
            $entry['pass_yds'] = (int) ($row['pass_yds'] ?? 0);
            $entry['pass_td']  = (int) ($row['pass_td']  ?? 0);
            $entry['pass_int'] = (int) ($row['pass_int'] ?? 0);
            $entry['rush_yds'] = (int) ($row['rush_yds'] ?? 0);
            $entry['rush_td']  = (int) ($row['rush_td']  ?? 0);
            $entry['rec_yds']  = (int) ($row['rec_yds']  ?? 0);
            $entry['rec_td']   = (int) ($row['rec_td']   ?? 0);
        }

        $rows[] = $entry;
    }

    return rest_ensure_response($rows);
}


function pfl_api_player_transactions(WP_REST_Request $request) {
    global $wpdb;

    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return new WP_Error('missing_pid', 'Missing pid', ['status' => 400]);

    // Get mflid for draft timestamp lookup
    $mflid = $wpdb->get_var($wpdb->prepare(
        "SELECT mflid FROM wp_players WHERE p_id = %s LIMIT 1", $pid
    ));

    // MFL transactions (TRADE, WAIVER, FREE_AGENT, IR)
    $printit = new_mfl_transactions($pid);
    if (isset($printit['error'])) return rest_ensure_response([]);

    // RELEASED: player on roster year N, not protected year N+1
    $released_events = get_released_player($pid);
    foreach ($released_events as $released) {
        $yr = (int) $released['year'];
        if ($yr >= 2026) continue;
        $date_str = get_draft_date_for_player($yr, $mflid);
        if (!isset($printit[$yr])) $printit[$yr] = [];
        array_unshift($printit[$yr], [
            'type'      => 'RELEASED',
            'realtime'  => $date_str . ' 00:00:00',
            'franchise' => $released['team'],
            'action'    => 'Released',
        ]);
    }

    // DRAFT: from wp_drafts
    $draft_events = get_drafts_player($pid);
    if (!empty($draft_events)) {
        foreach ($draft_events as $draft) {
            $yr = (int) $draft['season'];
            $pick_fmt = 'R' . intval($draft['round']) . '-' . str_pad(intval($draft['pick']), 2, '0', STR_PAD_LEFT);
            $ts = get_mfl_draft_timestamp($yr, $mflid);
            $realtime = $ts ?: (get_draft_date_for_player($yr, $mflid) . ' 00:00:00');
            if (!isset($printit[$yr])) $printit[$yr] = [];
            array_unshift($printit[$yr], [
                'type'      => 'DRAFT',
                'realtime'  => $realtime,
                'franchise' => $draft['acteam'],
                'action'    => 'Drafted ' . $pick_fmt,
            ]);
        }
    }

    // PROTECTED: from wp_protections
    $protection_events = get_protections_player($pid);
    if (!empty($protection_events)) {
        foreach ($protection_events as $protection) {
            $yr = (int) $protection['year'];
            if ($yr >= 2026) continue;
            $date_str = get_draft_date_for_player($yr, $mflid);
            if (!isset($printit[$yr])) $printit[$yr] = [];
            array_unshift($printit[$yr], [
                'type'      => 'PROTECTED',
                'realtime'  => $date_str . ' 00:00:00',
                'franchise' => $protection['team'],
                'action'    => 'Protected',
            ]);
        }
    }

    // Remove empty years, sort descending
    $printit = array_filter($printit);
    krsort($printit);

    // Within each year, sort descending (newest first) using raw timestamp when available
    foreach ($printit as $yr => &$trans) {
        usort($trans, function($a, $b) {
            $ts_a = !empty($a['timestamp']) ? (int) $a['timestamp'] : (int) strtotime($a['realtime'] ?? '');
            $ts_b = !empty($b['timestamp']) ? (int) $b['timestamp'] : (int) strtotime($b['realtime'] ?? '');
            return $ts_b - $ts_a;
        });
    }
    unset($trans);

    // Flatten to rows
    $rows = [];
    foreach ($printit as $yr => $trans) {
        foreach ($trans as $t) {
            $type = $t['type'] ?? '';
            $realtime  = $t['realtime']  ?? '';
            $raw_ts    = $t['timestamp'] ?? 0;
            // Use raw Unix timestamp (MFL events) when available, else parse realtime string (DB events)
            if ($raw_ts) {
                $ts_parsed = (int) $raw_ts;
                $date_fmt  = date('m/d', $ts_parsed);
                $time_fmt  = date('H:i:s', $ts_parsed);
            } else {
                $ts_parsed = strtotime($realtime);
                $date_fmt  = $ts_parsed ? date('m/d', $ts_parsed) : '-';
                $time_fmt  = '-'; // DB-generated events have no meaningful time
            }

            // Determine display type and action for MFL transaction types
            if (in_array($type, ['DRAFT', 'PROTECTED', 'RELEASED'])) {
                $display_type = $type;
                $action_str   = $t['action'] ?? '';
                $team         = $t['franchise'] ?? '';
            } elseif ($type === 'TRADE') {
                $gave1 = array_values($t['franchise_1_gave_up'] ?? []);
                $gave2 = array_values($t['franchise2_gave_up'] ?? []);
                $team1 = $t['franchise1'] ?? '';
                $team2 = $t['franchise2'] ?? '';
                if (in_array($pid, $gave1)) {
                    $team = $team2; $action_str = "Traded from $team1";
                } elseif (in_array($pid, $gave2)) {
                    $team = $team1; $action_str = "Traded from $team2";
                } else {
                    continue;
                }
                $display_type = 'TRADE';
            } elseif ($type === 'IR') {
                $activated   = array_values($t['activated']   ?? []);
                $deactivated = array_values($t['deactivated'] ?? []);
                if (in_array($pid, $activated))        { $action_str = 'Activated'; }
                elseif (in_array($pid, $deactivated))  { $action_str = 'Deactivated'; }
                else { continue; }
                $display_type = 'IR';
                $team         = $t['franchise'] ?? '';
            } elseif (in_array($type, ['WAIVER', 'FREE_AGENT'])) {
                $added   = array_values($t['added']   ?? []);
                $dropped = array_values($t['dropped'] ?? []);
                $trans_p = array_values($t['transaction'] ?? []);
                $all     = array_merge($added, $dropped, $trans_p);
                if (!in_array($pid, $all)) continue;
                $action_str   = in_array($pid, $dropped) ? 'Released' : 'Added';
                $display_type = 'RELEASED';
                $team         = $t['franchise'] ?? '';
            } else {
                continue;
            }

            $rows[] = [
                'type'   => $display_type,
                'year'   => (int) $yr,
                'date'   => $date_fmt,
                'time'   => $time_fmt,
                'team'   => $team,
                'action' => $action_str,
            ];
        }
    }

    return rest_ensure_response($rows);
}

function pfl_api_player_trades(WP_REST_Request $request) {
    global $wpdb;
    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return rest_ensure_response([]);

    $like = '%' . $wpdb->esc_like($pid) . '%';
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM wp_trades WHERE players1 LIKE %s OR players2 LIKE %s ORDER BY year ASC",
        $like, $like
    ), ARRAY_N);

    if (empty($rows)) return rest_ensure_response([]);

    $result = [];

    foreach ($rows as $row) {
        $year     = $row[1];
        $team1    = $row[2];
        $players1 = array_values(array_filter(array_map('trim', explode(',', $row[3] ?? ''))));
        $picks1   = array_values(array_filter(array_map('trim', explode(',', $row[4] ?? ''))));
        $team2    = $row[6];
        $players2 = array_values(array_filter(array_map('trim', explode(',', $row[7] ?? ''))));
        $picks2   = array_values(array_filter(array_map('trim', explode(',', $row[8] ?? ''))));
        $notes    = $row[10] ?? null;
        $when     = $row[11] ?? null;

        if (in_array($pid, $players1)) {
            $from_team_int = $team2;
            $to_team_int   = $team1;
            $comp_players  = $players2;
            $comp_picks    = $picks2;
        } else {
            $from_team_int = $team1;
            $to_team_int   = $team2;
            $comp_players  = $players1;
            $comp_picks    = $picks1;
        }

        // Resolve full team names
        $from_team = $wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $from_team_int
        )) ?: $from_team_int;
        $to_team = $wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $to_team_int
        )) ?: $to_team_int;

        // Resolve compensation player names (exclude self)
        $comp_player_names = [];
        foreach ($comp_players as $cpid) {
            if (empty($cpid) || $cpid === $pid) continue;
            $p = $wpdb->get_row($wpdb->prepare(
                "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s LIMIT 1", $cpid
            ), ARRAY_A);
            if ($p) $comp_player_names[] = trim($p['playerFirst'] . ' ' . $p['playerLast']);
        }

        // Format picks: "YEAR.ROUND.PICK" → "YEAR Rd R"
        $formatted_picks = [];
        foreach ($comp_picks as $pick) {
            if (empty($pick)) continue;
            $parts = explode('.', $pick);
            if (count($parts) === 3) {
                $formatted_picks[] = $parts[0] . ' Rd ' . ltrim($parts[1], '0');
            } else {
                $formatted_picks[] = $pick;
            }
        }

        $exchange = array_merge($comp_player_names, $formatted_picks);

        $result[] = [
            'year'      => (int) $year,
            'from_team' => $from_team,
            'to_team'   => $to_team,
            'exchange'  => $exchange,
            'when'      => $when ?: null,
            'notes'     => $notes ?: null,
        ];
    }

    return rest_ensure_response($result);
}

function pfl_api_player_jerseys(WP_REST_Request $request) {
    global $wpdb;
    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return rest_ensure_response([]);

    // Get player's default number and per-season numberarray
    $player = $wpdb->get_row($wpdb->prepare(
        "SELECT number, numberarray FROM wp_players WHERE p_id = %s LIMIT 1", $pid
    ), ARRAY_A);
    if (!$player) return rest_ensure_response([]);

    $default_number = (int) ($player['number'] ?? 0);
    $numberarray    = !empty($player['numberarray']) ? json_decode($player['numberarray']) : null;

    // Get team by year from rosters
    $roster_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT year, team FROM wp_rosters WHERE pid = %s ORDER BY CAST(year AS UNSIGNED) ASC", $pid
    ), ARRAY_A);

    $teamByYear = [];
    foreach ($roster_rows as $row) {
        $teamByYear[(int) $row['year']] = $row['team'];
    }

    // Emit a new jersey entry only when team OR number changes year-over-year
    $prev_team   = null;
    $prev_number = null;
    $prev_year   = null;
    $changes     = [];

    foreach ($teamByYear as $year => $team) {
        $number = ($numberarray && isset($numberarray->$year))
            ? (int) $numberarray->$year
            : $default_number;

        if ($team !== $prev_team || $number !== $prev_number) {
            if (!empty($changes)) {
                $changes[count($changes) - 1]['year_end'] = $prev_year;
            }
            $changes[] = ['year_start' => $year, 'year_end' => $year, 'team' => $team, 'number' => $number];
        } else {
            $changes[count($changes) - 1]['year_end'] = $year;
        }

        $prev_team   = $team;
        $prev_number = $number;
        $prev_year   = $year;
    }

    $theme_uri = get_stylesheet_directory_uri();
    $result    = [];

    foreach ($changes as $i => $change) {
        $year   = $change['year_start'];
        $team   = $change['team'];
        $number = $change['number'];

        // Get jersey version for this team/year
        $uni_info    = get_uni_info_by_team($team);
        $jerseyvalue = isset($uni_info[$year]) ? (int) $uni_info[$year] : 1;
        if ($jerseyvalue < 1) $jerseyvalue = 1;

        $result[] = [
            'year_start' => $change['year_start'],
            'year_end'   => $change['year_end'],
            'team'       => $team,
            'number'     => $number,
            'home_url' => $theme_uri . show_jersey_svg($team, 'H', $jerseyvalue, $number),
            'road_url' => $theme_uri . show_jersey_svg($team, 'R', $jerseyvalue, $number),
            'alt_url'  => $theme_uri . show_jersey_svg($team, 'A', $jerseyvalue, $number),
        ];
    }

    return rest_ensure_response($result);
}

// ── Season API Endpoints ───────────────────────────────────────────────────────

// Resolve a player image URL via the WP media library, falling back to direct upload path.
function pfl_player_img_url($pid) {
    if (!$pid) return null;

    // Try WP media library by slug
    $url = get_attachment_url_by_slug($pid);
    if ($url) return $url;

    // Try finding attachment by filename in postmeta (handles year/month subdirs and any extension)
    global $wpdb;
    $attachment_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
        '%' . $wpdb->esc_like($pid) . '.%'
    ));
    if ($attachment_id) return wp_get_attachment_url($attachment_id);

    // Filesystem fallback (flat uploads dir)
    $upload_dir = wp_upload_dir();
    foreach (['.jpg', '.jpeg', '.png'] as $ext) {
        if (file_exists($upload_dir['basedir'] . '/' . $pid . $ext)) {
            return $upload_dir['baseurl'] . '/' . $pid . $ext;
        }
    }
    return $upload_dir['baseurl'] . '/' . $pid . '.jpg';
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/season-years', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_years',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/season-overview', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_overview',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/season-standings', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_standings',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/season-awards', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_awards',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/season-leaders', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_leaders',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/season-potw', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_potw',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_season_years(WP_REST_Request $request) {
    $years = the_seasons();
    rsort($years);
    return rest_ensure_response($years);
}

function pfl_api_season_overview(WP_REST_Request $request) {
    global $wpdb;
    $year = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) return new WP_Error('missing_year', 'Missing year', ['status' => 400]);

    $champs   = get_just_champions();
    $team_int = $champs[$year] ?? null;

    if (!$team_int) return rest_ensure_response(['year' => $year, 'champion' => null]);

    $team_name = $wpdb->get_var($wpdb->prepare(
        "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $team_int
    )) ?: $team_int;

    return rest_ensure_response([
        'year'     => $year,
        'champion' => [
            'team_int'   => $team_int,
            'team_name'  => $team_name,
            'helmet_url' => get_site_url() . '/wp-content/uploads/' . $team_int . '-helmet-full-250x250.png',
        ],
    ]);
}

function pfl_api_season_standings(WP_REST_Request $request) {
    $year = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) return new WP_Error('missing_year', 'Missing year', ['status' => 400]);

    $standing = get_standings($year);
    if (empty($standing)) return rest_ensure_response([]);

    $divisions = [];
    foreach ($standing as $item) {
        $div = $item['division'];
        $divisions[$div][] = [
            'seed'    => (int)   $item['seed'],
            'teamid'  =>         $item['teamid'],
            'name'    =>         $item['teamname'],
            'win'     => (int)   $item['win'],
            'loss'    => (int)   $item['loss'],
            'gb'      => (float) $item['gb'],
            'pts'     => (float) $item['pts'],
            'ptsvs'   => (float) $item['ptsvs'],
            'divwin'  => (int)   $item['divwin'],
            'divloss' => (int)   $item['divloss'],
        ];
    }

    foreach ($divisions as $div => &$teams) {
        usort($teams, fn($a, $b) => $b['win'] <=> $a['win'] ?: $b['pts'] <=> $a['pts']);
    }
    unset($teams);

    $result = [];
    foreach ($divisions as $div => $teams) {
        $result[] = ['division' => $div, 'teams' => $teams];
    }

    return rest_ensure_response($result);
}

function pfl_api_season_awards(WP_REST_Request $request) {
    global $wpdb;
    $year = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) return new WP_Error('missing_year', 'Missing year', ['status' => 400]);

    $awards    = get_season_award($year);
    $theme_uri = get_stylesheet_directory_uri();

    if (empty($awards)) return rest_ensure_response([]);

    $result = [];
    foreach ($awards as $award) {
        $team_name = $wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $award['team']
        )) ?: $award['team'];

        if ($award['award'] === 'Owner of the Year') {
            $result[] = [
                'award'      => $award['award'],
                'pid'        => null,
                'first'      => null,
                'last'       => null,
                'owner'      => $award['owner'],
                'team_int'   => $award['team'],
                'team_name'  => $team_name,
                'img'        => null,
                'helmet_url' => get_site_url() . '/wp-content/uploads/' . $award['team'] . '-helmet-full-250x250.png',
            ];
        } else {
            $result[] = [
                'award'      => $award['award'],
                'pid'        => $award['pid'],
                'first'      => $award['first'],
                'last'       => $award['last'],
                'owner'      => null,
                'team_int'   => $award['team'],
                'team_name'  => $team_name,
                'img'        => pfl_player_img_url($award['pid']),
                'helmet_url' => null,
            ];
        }
    }

    usort($result, fn($a, $b) => strcmp($a['award'], $b['award']));
    return rest_ensure_response($result);
}

function pfl_api_season_leaders(WP_REST_Request $request) {
    global $wpdb;
    $year      = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) return new WP_Error('missing_year', 'Missing year', ['status' => 400]);

    $theme_uri   = get_stylesheet_directory_uri();
    $number_ones = get_number_ones();
    $result      = [];

    $pos_labels = ['QB' => 'Passing Title', 'RB' => 'Rushing Title', 'WR' => 'Receiving Title', 'PK' => 'Kicking Title'];

    foreach (['QB', 'RB', 'WR', 'PK'] as $pos) {
        $winners = [];
        foreach ($number_ones as $item) {
            if ((int) $item['year'] !== $year || $item['pos'] !== $pos) continue;
            $pid  = $item['playerid'];
            $name = get_player_name($pid);
            $team_int  = $wpdb->get_var($wpdb->prepare(
                "SELECT team FROM wp_rosters WHERE pid = %s AND year = %d LIMIT 1", $pid, $year
            ));
            $team_name = $team_int ? ($wpdb->get_var($wpdb->prepare(
                "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $team_int
            )) ?: $team_int) : ($item['teams'] ?? '');

            $winners[] = [
                'pid'   => $pid,
                'first' => $name['first'],
                'last'  => $name['last'],
                'value' => (float) $item['points'],
                'label' => number_format((float) $item['points'], 0) . ' pts',
                'team'  => $team_name,
                'img'   => pfl_player_img_url($pid),
            ];
        }
        if (!empty($winners)) {
            $result[] = ['category' => $pos_labels[$pos], 'pos' => $pos, 'winners' => $winners];
        }
    }

    // PPG leader (min 7 games, any position)
    $season_leaders = get_season_leaders($year);
    if (!empty($season_leaders)) {
        $allppg = [];
        foreach ($season_leaders as $item) {
            if ($item['games'] >= 7) {
                $allppg[$item['playerid']] = $item['points'] / $item['games'];
            }
        }
        if (!empty($allppg)) {
            arsort($allppg);
            $pid = key($allppg);
            $val = reset($allppg);
            $name = get_player_name($pid);
            $team_int  = $wpdb->get_var($wpdb->prepare(
                "SELECT team FROM wp_rosters WHERE pid = %s AND year = %d LIMIT 1", $pid, $year
            ));
            $team_name = $team_int ? ($wpdb->get_var($wpdb->prepare(
                "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $team_int
            )) ?: $team_int) : '';
            $result[] = [
                'category' => 'Points Per Game',
                'pos'      => null,
                'winners'  => [[
                    'pid'   => $pid,
                    'first' => $name['first'],
                    'last'  => $name['last'],
                    'value' => round($val, 2),
                    'label' => number_format($val, 1) . ' PPG',
                    'team'  => $team_name,
                    'img'   => pfl_player_img_url($pid),
                ]],
            ];
        }
    }

    // PVQ leader
    $pvq_leaders = get_season_pvq_leader();
    if (isset($pvq_leaders[$year])) {
        $pvq  = $pvq_leaders[$year];
        $pid  = $pvq['playerid'];
        $name = get_player_name($pid);
        $team_int  = $wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_rosters WHERE pid = %s AND year = %d LIMIT 1", $pid, $year
        ));
        $team_name = $team_int ? ($wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $team_int
        )) ?: $team_int) : '';
        $result[] = [
            'category' => 'PVQ Leader',
            'pos'      => null,
            'winners'  => [[
                'pid'   => $pid,
                'first' => $name['first'],
                'last'  => $name['last'],
                'value' => (float) $pvq['pvq'],
                'label' => number_format((float) $pvq['pvq'], 3) . ' PVQ',
                'team'  => $team_name,
                'img'   => pfl_player_img_url($pid),
            ]],
        ];
    }

    // Top Game Score: highest individual game score in the season
    if (!empty($season_leaders)) {
        $top_game_pid   = null;
        $top_game_score = 0;

        $top_game_week = null;
        foreach ($season_leaders as $item) {
            $p   = $item['playerid'];
            $tbl = preg_replace('/[^a-zA-Z0-9]/', '', $p);
            $row = $wpdb->get_row(
                $wpdb->prepare("SELECT points, week FROM `{$tbl}` WHERE year = %d ORDER BY points DESC LIMIT 1", $year),
                ARRAY_A
            );
            if ($row && (int) $row['points'] > $top_game_score) {
                $top_game_score = (int) $row['points'];
                $top_game_week  = (int) $row['week'];
                $top_game_pid   = $p;
            }
        }

        if ($top_game_pid && $top_game_score > 0) {
            $name = get_player_name($top_game_pid);
            $team_int  = $wpdb->get_var($wpdb->prepare(
                "SELECT team FROM wp_rosters WHERE pid = %s AND year = %d LIMIT 1", $top_game_pid, $year
            ));
            $team_name = $team_int ? ($wpdb->get_var($wpdb->prepare(
                "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $team_int
            )) ?: $team_int) : '';
            $week_label = $top_game_week ? ' · Wk ' . $top_game_week : '';
            $result[] = [
                'category' => 'Top Game Score',
                'pos'      => null,
                'winners'  => [[
                    'pid'   => $top_game_pid,
                    'first' => $name['first'],
                    'last'  => $name['last'],
                    'value' => $top_game_score,
                    'label' => $top_game_score . ' pts' . $week_label,
                    'team'  => $team_name,
                    'img'   => pfl_player_img_url($top_game_pid),
                ]],
            ];
        }
    }

    return rest_ensure_response($result);
}

function pfl_api_season_potw(WP_REST_Request $request) {
    global $wpdb;
    $year      = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) return new WP_Error('missing_year', 'Missing year', ['status' => 400]);

    $all_potw  = get_player_of_week();
    $theme_uri = get_stylesheet_directory_uri();
    $result    = [];

    foreach ($all_potw as $weekid => $pid) {
        if ((int) substr($weekid, 0, 4) !== $year) continue;

        $week       = (int) substr($weekid, 4, 2);
        $week_label = $week === 15 ? 'Playoffs' : 'Week ' . $week;
        $name       = get_player_name($pid);

        if ($week === 15) {
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT points, team FROM wp_playoffs WHERE playerid = %s AND year = %d AND week = 15 LIMIT 1",
                $pid, $year
            ), ARRAY_A);
        } else {
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT points, team FROM `$pid` WHERE week_id = %s LIMIT 1", $weekid
            ), ARRAY_A);
        }

        $points    = $row ? (float) $row['points'] : null;
        $team_int  = $row['team'] ?? null;
        $team_name = $team_int ? ($wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $team_int
        )) ?: $team_int) : null;

        $result[$week] = [
            'week'       => $week,
            'week_label' => $week_label,
            'pid'        => $pid,
            'first'      => $name['first'],
            'last'       => $name['last'],
            'team'       => $team_name,
            'points'     => $points,
            'img'        => pfl_player_img_url($pid),
        ];
    }

    ksort($result);
    return rest_ensure_response(array_values($result));
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/season-schedule', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_schedule',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_season_schedule(WP_REST_Request $request) {
    global $wpdb;
    $year = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) return new WP_Error('missing_year', 'Missing year', ['status' => 400]);

    $standing = get_standings($year);
    if (empty($standing)) return rest_ensure_response([]);

    $by_week = [];
    $seen    = [];

    foreach ($standing as $row) {
        $team = $row['teamid'];
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `wp_team_$team` WHERE season = %d AND week BETWEEN 1 AND 14",
            $year
        ), ARRAY_N);

        foreach ($rows as $r) {
            // [0]=id [1]=season [2]=week [3]=team_int [4]=points [5]=versus [6]=versus_pts [7]=home_away [8]=stadium [9]=result
            $w   = (int) $r[2];
            $t1  = $r[3];
            $t2  = $r[5];
            $key = min($t1, $t2) . '_' . max($t1, $t2);
            if (isset($seen[$w][$key])) continue;
            $seen[$w][$key] = true;
            $is_home = ($r[7] === 'H');
            $by_week[$w][] = [
                'away'       => $is_home ? $t2 : $t1,
                'away_score' => $is_home ? (int) $r[6] : (int) $r[4],
                'home'       => $is_home ? $t1 : $t2,
                'home_score' => $is_home ? (int) $r[4] : (int) $r[6],
            ];
        }
    }

    ksort($by_week);
    $result = [];
    foreach ($by_week as $week => $games) {
        $result[] = ['week' => $week, 'label' => (string)$week, 'games' => $games];
    }

    // Playoffs (week 15) and Posse Bowl (week 16)
    foreach ([15 => 'PL', 16 => 'PB'] as $pw => $label) {
        $po_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT team, versus, SUM(points) as pts FROM wp_playoffs WHERE year = %d AND week = %d GROUP BY team, versus",
            $year, $pw
        ), ARRAY_A);

        if (empty($po_rows)) continue;

        // Build lookup: team -> pts for each side
        $totals = [];
        foreach ($po_rows as $r) {
            $totals[$r['team']][$r['versus']] = (int) $r['pts'];
        }

        $po_games = [];
        $po_seen  = [];
        foreach ($po_rows as $r) {
            $t1  = $r['team'];
            $t2  = $r['versus'];
            $key = min($t1, $t2) . '_' . max($t1, $t2);
            if (isset($po_seen[$key])) continue;
            $po_seen[$key] = true;
            $t1_pts = $totals[$t1][$t2] ?? 0;
            $t2_pts = $totals[$t2][$t1] ?? 0;
            $po_games[] = [
                'away'       => $t1,
                'away_score' => $t1_pts,
                'home'       => $t2,
                'home_score' => $t2_pts,
            ];
        }

        if (!empty($po_games)) {
            $result[] = ['week' => $pw, 'label' => $label, 'games' => $po_games];
        }
    }

    return rest_ensure_response($result);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/schedules', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_schedules',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_schedules(WP_REST_Request $request) {
    global $wpdb;
    $year = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) return new WP_Error('missing_year', 'Missing year', ['status' => 400]);

    // Build team name cache for this year
    $team_names = [];
    $all_teams = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    foreach ($all_teams as $t) {
        $team_names[$t['team_int']] = $t['team'];
    }

    // BS Win of the Year notes keyed by weekid (YYYYWW)
    // wp_bswoty: [0]=year [1]=week [2]=winner [3]=loser
    $all_bsw = $wpdb->get_results("SELECT * FROM wp_bswoty", ARRAY_N);
    $bswins = [];
    foreach ($all_bsw as $b) {
        if ((int) $b[0] !== $year) continue;
        $key = (string) $b[0] . str_pad((string) $b[1], 2, '0', STR_PAD_LEFT);
        $bswins[$key] = [
            'winner'     => $b[2],
            'winnerName' => $team_names[$b[2]] ?? $b[2],
            'loser'      => $b[3],
            'loserName'  => $team_names[$b[3]]  ?? $b[3],
        ];
    }

    // Regular season games from team tables (weeks 1–14)
    $standing = get_standings($year);
    if (empty($standing)) return rest_ensure_response([]);

    $by_week = [];
    $seen    = [];

    foreach ($standing as $row) {
        $team = $row['teamid'];
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `wp_team_$team` WHERE season = %d AND week BETWEEN 1 AND 14",
            $year
        ), ARRAY_N);

        foreach ($rows as $r) {
            // [0]=id [1]=season [2]=week [3]=team_int [4]=points [5]=versus [6]=versus_pts [7]=home_away [8]=stadium [9]=result
            $w   = (int) $r[2];
            $t1  = $r[3];
            $t2  = $r[5];
            $key = min($t1, $t2) . '_' . max($t1, $t2);
            if (isset($seen[$w][$key])) continue;
            $seen[$w][$key] = true;
            $is_home = ($r[7] === 'H');
            $by_week[$w][] = [
                'away'       => $is_home ? $t2 : $t1,
                'awayName'   => $team_names[$is_home ? $t2 : $t1] ?? ($is_home ? $t2 : $t1),
                'away_score' => $is_home ? (int) $r[6] : (int) $r[4],
                'home'       => $is_home ? $t1 : $t2,
                'homeName'   => $team_names[$is_home ? $t1 : $t2] ?? ($is_home ? $t1 : $t2),
                'home_score' => $is_home ? (int) $r[4] : (int) $r[6],
            ];
        }
    }

    ksort($by_week);
    $result = [];
    foreach ($by_week as $week => $games) {
        $weekid = $year . str_pad($week, 2, '0', STR_PAD_LEFT);
        $bs     = $bswins[$weekid] ?? null;
        $result[] = [
            'week'  => $week,
            'label' => 'Week ' . $week,
            'note'  => $bs ? $bs['winnerName'] . ' — BS Win of the Year' : null,
            'games' => $games,
        ];
    }

    // Playoffs (week 15) and Posse Bowl (week 16)
    foreach ([15 => 'Playoffs', 16 => 'Posse Bowl'] as $pw => $label) {
        $po_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT team, versus, SUM(points) as pts FROM wp_playoffs WHERE year = %d AND week = %d GROUP BY team, versus",
            $year, $pw
        ), ARRAY_A);

        if (empty($po_rows)) continue;

        $totals = [];
        foreach ($po_rows as $r) {
            $totals[$r['team']][$r['versus']] = (int) $r['pts'];
        }

        $po_games = [];
        $po_seen  = [];
        foreach ($po_rows as $r) {
            $t1  = $r['team'];
            $t2  = $r['versus'];
            $key = min($t1, $t2) . '_' . max($t1, $t2);
            if (isset($po_seen[$key])) continue;
            $po_seen[$key] = true;
            $t1_pts = $totals[$t1][$t2] ?? 0;
            $t2_pts = $totals[$t2][$t1] ?? 0;
            $po_games[] = [
                'away'       => $t1,
                'awayName'   => $team_names[$t1] ?? $t1,
                'away_score' => $t1_pts,
                'home'       => $t2,
                'homeName'   => $team_names[$t2] ?? $t2,
                'home_score' => $t2_pts,
            ];
        }

        if (!empty($po_games)) {
            $result[] = ['week' => $pw, 'label' => $label, 'note' => null, 'games' => $po_games];
        }
    }

    return rest_ensure_response($result);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/season-ironmen', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_ironmen',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_season_ironmen(WP_REST_Request $request) {
    global $wpdb;
    $year = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) return new WP_Error('missing_year', 'Missing year', ['status' => 400]);

    $ironmen = get_iron_men($year);
    if (empty($ironmen)) return rest_ensure_response([]);

    $pos_order = ['QB' => 0, 'RB' => 1, 'WR' => 2, 'PK' => 3];
    usort($ironmen, function($a, $b) use ($pos_order) {
        $ap = $pos_order[$a['position']] ?? 4;
        $bp = $pos_order[$b['position']] ?? 4;
        return $ap !== $bp ? $ap - $bp : strcmp($a['last'], $b['last']);
    });

    $result = [];
    foreach ($ironmen as $p) {
        $team_name = $wpdb->get_var($wpdb->prepare(
            "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $p['team']
        )) ?: $p['team'];
        $result[] = [
            'pid'      => $p['pid'],
            'first'    => $p['first'],
            'last'     => $p['last'],
            'position' => $p['position'],
            'team'     => $team_name,
            'games'    => (int) $p['games'],
        ];
    }

    return rest_ensure_response($result);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/season-fifties', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_fifties',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_season_fifties(WP_REST_Request $request) {
    global $wpdb;
    $year = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) return new WP_Error('missing_year', 'Missing year', ['status' => 400]);

    $weeks      = ['01','02','03','04','05','06','07','08','09','10','11','12','13','14'];
    $games      = [];
    $counts     = [];
    $team_names = [];

    foreach ($weeks as $w) {
        $scores = get_team_score_by_week($year . $w);
        if (empty($scores)) continue;
        foreach ($scores as $team_int => $pts) {
            if ($pts < 50) continue;
            if (!isset($team_names[$team_int])) {
                $team_names[$team_int] = $wpdb->get_var($wpdb->prepare(
                    "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $team_int
                )) ?: $team_int;
            }
            $games[] = [
                'week'      => (int) $w,
                'team_name' => $team_names[$team_int],
                'points'    => (int) $pts,
            ];
            $counts[$team_int] = ($counts[$team_int] ?? 0) + 1;
        }
    }

    arsort($counts);
    $totals = [];
    foreach ($counts as $team_int => $c) {
        $totals[] = ['team_name' => $team_names[$team_int], 'count' => $c];
    }

    return rest_ensure_response(['games' => $games, 'totals' => $totals]);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/season-weekhighs', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_weekhighs',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_season_weekhighs(WP_REST_Request $request) {
    global $wpdb;
    $year = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) return new WP_Error('missing_year', 'Missing year', ['status' => 400]);

    $weeks  = ['01','02','03','04','05','06','07','08','09','10','11','12','13','14'];
    $result = [];

    // Cache team names to avoid repeated queries
    $team_names = [];

    foreach ($weeks as $w) {
        $weekid    = $year . $w;
        $scores    = get_team_score_by_week($weekid);
        if (empty($scores)) continue;

        $max = max($scores);
        $winners = [];
        foreach ($scores as $team_int => $pts) {
            if ($pts == $max) {
                if (!isset($team_names[$team_int])) {
                    $team_names[$team_int] = $wpdb->get_var($wpdb->prepare(
                        "SELECT team FROM wp_teams WHERE team_int = %s LIMIT 1", $team_int
                    )) ?: $team_int;
                }
                $winners[] = $team_names[$team_int];
            }
        }

        $result[] = [
            'week'   => (int) $w,
            'teams'  => $winners,
            'points' => (int) $max,
        ];
    }

    return rest_ensure_response($result);
}

// ── Draft Planning: Player Search ────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-search', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_search',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_player_search(WP_REST_Request $request) {
    global $wpdb;
    $q = sanitize_text_field($request->get_param('q') ?? '');
    if (strlen($q) < 2) return rest_ensure_response([]);

    $like = '%' . $wpdb->esc_like($q) . '%';
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT al.pid, p.playerFirst AS first, p.playerLast AS last, al.position
         FROM wp_allleaders al
         LEFT JOIN wp_players p ON p.p_id = al.pid
         WHERE p.playerFirst LIKE %s OR p.playerLast LIKE %s
         ORDER BY p.playerLast, p.playerFirst
         LIMIT 20",
        $like, $like
    ), ARRAY_A);

    return rest_ensure_response($rows);
}

// ── Draft Planning: Protected Players ────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/protected-players', [
        ['methods' => 'GET',    'callback' => 'pfl_api_get_protected',    'permission_callback' => '__return_true'],
        ['methods' => 'POST',   'callback' => 'pfl_api_add_protected',    'permission_callback' => '__return_true'],
        ['methods' => 'DELETE', 'callback' => 'pfl_api_remove_protected', 'permission_callback' => '__return_true'],
    ]);
});

function pfl_api_get_protected(WP_REST_Request $request) {
    $year = (int) $request->get_param('year');
    $data = get_option("pfl_protected_{$year}", (object)[]);
    return rest_ensure_response($data);
}

function pfl_api_add_protected(WP_REST_Request $request) {
    global $wpdb;
    $year   = (int) sanitize_text_field($request->get_param('year'));
    $teamid = sanitize_text_field($request->get_param('teamid'));
    $pid    = sanitize_text_field($request->get_param('pid'));

    // Accept player details directly from the request body (e.g. from Sleeper-sourced pool)
    $body_first    = sanitize_text_field($request->get_param('first') ?? '');
    $body_last     = sanitize_text_field($request->get_param('last') ?? '');
    $body_position = sanitize_text_field($request->get_param('position') ?? '');

    if ($body_first && $body_last && $body_position) {
        $player = ['first' => $body_first, 'last' => $body_last, 'position' => $body_position];
    } else {
        $player = $wpdb->get_row($wpdb->prepare(
            "SELECT al.pid, p.playerFirst AS first, p.playerLast AS last, al.position
             FROM wp_allleaders al LEFT JOIN wp_players p ON p.p_id = al.pid
             WHERE al.pid = %s LIMIT 1",
            $pid
        ), ARRAY_A);
        if (!$player) return new WP_Error('not_found', 'Player not found', ['status' => 404]);
    }

    $data = (array) get_option("pfl_protected_{$year}", []);
    if (!isset($data[$teamid])) $data[$teamid] = [];
    foreach ($data[$teamid] as $p) {
        if ($p['pid'] === $pid) return rest_ensure_response($data);
    }
    $data[$teamid][] = ['pid' => $pid, 'first' => $player['first'], 'last' => $player['last'], 'position' => $player['position']];
    update_option("pfl_protected_{$year}", $data);
    return rest_ensure_response($data);
}

function pfl_api_remove_protected(WP_REST_Request $request) {
    $year   = (int) sanitize_text_field($request->get_param('year'));
    $teamid = sanitize_text_field($request->get_param('teamid'));
    $pid    = sanitize_text_field($request->get_param('pid'));

    $data = (array) get_option("pfl_protected_{$year}", []);
    if (isset($data[$teamid])) {
        $data[$teamid] = array_values(array_filter($data[$teamid], fn($p) => $p['pid'] !== $pid));
    }
    update_option("pfl_protected_{$year}", $data);
    return rest_ensure_response($data);
}

// ── MFL Actual Scores ────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/mfl-actual-scores', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_mfl_actual_scores',
        'permission_callback' => '__return_true',
    ]);
    // DELETE cached data for a year so it can be re-fetched
    register_rest_route('pfl/v1', '/mfl-actual-scores', [
        'methods'             => 'DELETE',
        'callback'            => 'pfl_api_mfl_clear_scores_cache',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_mfl_actual_scores(WP_REST_Request $request) {
    $year  = (int) $request->get_param('year');
    $debug = (bool) $request->get_param('debug');
    if (!$year) return new WP_Error('missing_year', 'year is required', ['status' => 400]);

    // Return cached data if available (historical data never changes)
    $cache_key = "pfl_mfl_scores_{$year}";
    $cached = get_option($cache_key);
    if ($cached && !$debug) return rest_ensure_response($cached);

    $league_ids = get_mfl_league_id();
    $league_id  = $league_ids[$year] ?? null;
    if (!$league_id) return new WP_Error('no_league', "No MFL league ID for year {$year}", ['status' => 404]);

    $apikey  = 'aRNp1sySvuWqx0CmO1HIZDYeFbox';
    $cookie  = 'MFL_PW_SEQ=ah9q2M6Ss%2Bis3Q29; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D';
    $base    = "https://www58.myfantasyleague.com/{$year}/export";
    $headers = ['Cookie' => $cookie];
    $args    = ['timeout' => 30, 'headers' => $headers];

    // 1. Fetch all players (ID → name/position)
    $players_url  = "{$base}?TYPE=players&L={$league_id}&APIKEY={$apikey}&JSON=1";
    $players_res  = wp_remote_get($players_url, $args);
    $players_body = is_wp_error($players_res) ? '' : wp_remote_retrieve_body($players_res);
    $players_data = json_decode($players_body, true);
    $players_list = $players_data['players']['player'] ?? [];

    if ($debug) {
        $w1_url  = "{$base}?TYPE=playerScores&L={$league_id}&W=1&JSON=1";
        $w1_res  = wp_remote_get($w1_url, $args);
        $w1_body = is_wp_error($w1_res) ? $w1_res->get_error_message() : wp_remote_retrieve_body($w1_res);
        $w1_data = json_decode($w1_body, true);
        $w1_list = $w1_data['playerScores']['playerScore'] ?? [];
        return rest_ensure_response([
            'debug'              => true,
            'league_id'          => $league_id,
            'players_list_count' => count($players_list),
            'week1_scores_count' => count($w1_list),
            'week1_sample'       => array_slice($w1_list, 0, 3),
        ]);
    }

    // Build MFL ID → "First Last" name map (MFL names are "Last, First")
    $id_to_name = [];
    foreach ($players_list as $p) {
        $id = $p['id'] ?? '';
        if (!$id) continue;
        $raw = $p['name'] ?? '';
        // Convert "Brady, Tom" → "Tom Brady"
        if (strpos($raw, ',') !== false) {
            [$last, $first] = array_map('trim', explode(',', $raw, 2));
            $name = "{$first} {$last}";
        } else {
            $name = $raw;
        }
        $id_to_name[$id] = $name;
    }

    // 2. Fetch player scores for weeks 1–14 and sum (PFL regular season only)
    // Note: cookie auth only — APIKEY in URL conflicts with cookie and causes validation failure
    $totals = [];
    $gp     = [];
    for ($week = 1; $week <= 14; $week++) {
        $scores_url  = "{$base}?TYPE=playerScores&L={$league_id}&W={$week}&JSON=1";
        $scores_res  = wp_remote_get($scores_url, $args);
        if (is_wp_error($scores_res)) continue;
        $scores_data = json_decode(wp_remote_retrieve_body($scores_res), true);
        $scores_list = $scores_data['playerScores']['playerScore'] ?? [];
        foreach ($scores_list as $s) {
            $id    = $s['id']    ?? '';
            $score = floatval($s['score'] ?? 0);
            if (!$id || $score <= 0) continue;
            $totals[$id] = ($totals[$id] ?? 0) + $score;
            $gp[$id]     = ($gp[$id]     ?? 0) + 1;
        }
    }

    // 3. Build "First Last" → score map and "First Last" → games played map
    $result    = [];
    $gp_result = [];
    foreach ($totals as $id => $score) {
        $name = $id_to_name[$id] ?? null;
        if (!$name) continue;
        $result[$name]    = round($score, 1);
        $gp_result[$name] = $gp[$id] ?? 0;
    }

    // Cache permanently since historical data won't change
    update_option($cache_key, $result, false);
    update_option("pfl_mfl_gp_{$year}", $gp_result, false);

    return rest_ensure_response($result);
}

// ── MFL Games Played ─────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/mfl-games-played', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_mfl_games_played',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_mfl_games_played(WP_REST_Request $request) {
    $year = (int) $request->get_param('year');
    if (!$year) return new WP_Error('missing_year', 'year is required', ['status' => 400]);

    $cached = get_option("pfl_mfl_gp_{$year}");
    if ($cached) return rest_ensure_response($cached);

    // GP cache missing — clear the scores cache too so the full re-fetch runs
    // and rebuilds both caches together.
    delete_option("pfl_mfl_scores_{$year}");

    $scores_request = new WP_REST_Request('GET', '/pfl/v1/mfl-actual-scores');
    $scores_request->set_param('year', $year);
    pfl_api_mfl_actual_scores($scores_request);

    $cached = get_option("pfl_mfl_gp_{$year}");
    return rest_ensure_response($cached ?: (object)[]);
}

function pfl_api_mfl_clear_scores_cache(WP_REST_Request $request) {
    $year = (int) $request->get_param('year');
    if (!$year) return new WP_Error('missing_year', 'year is required', ['status' => 400]);
    delete_option("pfl_mfl_scores_{$year}");
    delete_option("pfl_mfl_gp_{$year}");
    return rest_ensure_response(['cleared' => true, 'year' => $year]);
}

// ── Migrate historical protections from wp_protections → pfl_protected_{year} ─
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/migrate-protections', [
        'methods'             => 'POST',
        'callback'            => 'pfl_api_migrate_protections',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_migrate_protections(WP_REST_Request $request) {
    global $wpdb;

    $years = [2022, 2023, 2024, 2025];

    // Debug: show sample rows and distinct years
    if ($request->get_param('debug')) {
        $sample = $wpdb->get_results("SELECT * FROM wp_protections LIMIT 3", ARRAY_A);
        $dist   = $wpdb->get_results("SELECT DISTINCT year FROM wp_protections ORDER BY year DESC", ARRAY_A);
        $total  = $wpdb->get_var("SELECT COUNT(*) FROM wp_protections");
        return rest_ensure_response(['total' => $total, 'distinct_years' => $dist, 'sample' => $sample]);
    }

    $rows = $wpdb->get_results(
        "SELECT year, playerFirst, playerLast, team, position, playerId
         FROM wp_protections
         WHERE year IN (2022, 2023, 2024, 2025)",
        ARRAY_A
    );

    // Group by year → teamid → [players]
    $by_year = [];
    foreach ($rows as $r) {
        $year   = (int) $r['year'];
        $teamid = $r['team'];
        $by_year[$year][$teamid][] = [
            'pid'      => $r['playerId'],
            'first'    => $r['playerFirst'],
            'last'     => $r['playerLast'],
            'position' => $r['position'],
        ];
    }

    $summary = [];
    foreach ($years as $year) {
        $data  = $by_year[$year] ?? [];
        $count = array_sum(array_map('count', $data));
        update_option("pfl_protected_{$year}", $data);
        $summary[$year] = $count;
    }

    return rest_ensure_response(['migrated' => $summary]);
}

// ── Kicker Draft page data ─────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/kicker-draft', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_kicker_draft',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_kicker_draft(WP_REST_Request $request) {
    global $wpdb;

    $year = (int) $request->get_param('year');
    if (!$year) {
        return new WP_Error('missing_year', 'Year required', ['status' => 400]);
    }

    // Debug mode: return raw sample rows to inspect schema
    if ($request->get_param('debug')) {
        $sample = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM wp_drafts WHERE year = %d LIMIT 3", $year
        ), ARRAY_A);
        $pk_sample = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM wp_drafts WHERE year = %d AND pos = 'PK' LIMIT 3", $year
        ), ARRAY_A);
        $sl_sample = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM wp_season_leaders WHERE season = %d LIMIT 5", $year
        ), ARRAY_A);
        $sl_pk_raw = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM wp_season_leaders WHERE season = %d AND playerid LIKE '%%PK' LIMIT 5", $year
        ), ARRAY_A);
        $sl_pk_str = $wpdb->get_results(
            "SELECT * FROM wp_season_leaders WHERE season = '2025' AND playerid LIKE '%PK' LIMIT 5", ARRAY_A
        );
        return rest_ensure_response([
            'sample'       => $sample,
            'pk_sample'    => $pk_sample,
            'sl_sample'    => $sl_sample,
            'sl_pk_raw'    => $sl_pk_raw,
            'sl_pk_str'    => $sl_pk_str,
            'last_error'   => $wpdb->last_error,
            'last_query'   => $wpdb->last_query,
        ]);
    }

    // Kickers drafted in the selected year — use named columns like draft-by-year endpoint
    // Schema: round=round#, roundnum=pick-within-round, picknum=overall pick#, pickord=original team (orteam), team=actual team
    $raw = $wpdb->get_results($wpdb->prepare(
        "SELECT round, roundnum, picknum, pickord AS orteam, team, playerfirst, playerlast, pos, playerid
         FROM wp_drafts
         WHERE year = %d AND pos = 'PK'
         ORDER BY CAST(round AS UNSIGNED), CAST(picknum AS UNSIGNED)",
        $year
    ), ARRAY_A);

    $picks = [];
    foreach ($raw as $r) {
        $first = $r['playerfirst'];
        $last  = $r['playerlast'];
        $pid   = $r['playerid'];
        if ((empty($first) || empty($last)) && $pid) {
            $name = $wpdb->get_row($wpdb->prepare(
                "SELECT playerfirst AS first, playerlast AS last FROM wp_players WHERE playerid = %s LIMIT 1", $pid
            ), ARRAY_A);
            if ($name) { $first = $name['first']; $last = $name['last']; }
        }
        $picks[] = [
            'round'   => (int) $r['round'],
            'pickInRound' => (int) $r['roundnum'],
            'overall' => (int) $r['picknum'],
            'pid'     => $pid,
            'first'   => $first,
            'last'    => $last,
            'team'    => $r['team'],
        ];
    }

    // All kicker points leaders for the selected season — playerid ends in 'PK'
    $leader_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT playerid AS pid, points, games
         FROM wp_season_leaders
         WHERE season = %d AND playerid LIKE '%%PK'
         ORDER BY CAST(points AS DECIMAL(10,2)) DESC",
        $year
    ), ARRAY_A);

    $leaders = [];
    foreach ($leader_rows as $l) {
        $pid   = $l['pid'];
        $first = '';
        $last  = '';
        // Look up name from wp_drafts (most reliable source for PFL players)
        $name = $wpdb->get_row($wpdb->prepare(
            "SELECT playerfirst, playerlast FROM wp_drafts WHERE playerid = %s AND playerfirst != '' LIMIT 1", $pid
        ), ARRAY_A);
        if ($name) {
            $first = $name['playerfirst'];
            $last  = $name['playerlast'];
        } else {
            $name = $wpdb->get_row($wpdb->prepare(
                "SELECT playerfirst, playerlast FROM wp_players WHERE playerid = %s LIMIT 1", $pid
            ), ARRAY_A);
            if ($name) { $first = $name['playerfirst']; $last = $name['playerlast']; }
        }
        $leaders[] = [
            'pid'    => $pid,
            'first'  => $first,
            'last'   => $last,
            'points' => (int) $l['points'],
            'games'  => (int) $l['games'],
        ];
    }

    return rest_ensure_response([
        'drafted' => $picks,
        'leaders' => $leaders,
    ]);
}

// ── Kicker Draft Tendencies (all-time aggregated) ──────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/kicker-draft-tendencies', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_kicker_draft_tendencies',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_kicker_draft_tendencies() {
    global $wpdb;

    $known_teams = ['BST','TSG','BUL','CMN','WRZ','ETS','DST','HAT','SNR','PEP'];

    // Fetch every PK pick across all years: first kicker per team per year
    // We get all PK rows, then aggregate in PHP
    $rows = $wpdb->get_results(
        "SELECT team, year,
                CAST(round   AS UNSIGNED) AS round,
                CAST(picknum AS UNSIGNED) AS overall
         FROM wp_drafts
         WHERE pos = 'PK' AND team != '' AND year != ''
         ORDER BY team, CAST(year AS UNSIGNED), CAST(picknum AS UNSIGNED)",
        ARRAY_A
    );

    // Group by team+year, keep only first pick (lowest overall) per team per year
    $first_by_team_year = []; // [team][year] = {round, overall}
    foreach ($rows as $r) {
        $team    = $r['team'];
        $year    = (int) $r['year'];
        $round   = (int) $r['round'];
        $overall = (int) $r['overall'];
        if (!isset($first_by_team_year[$team][$year])) {
            $first_by_team_year[$team][$year] = ['round' => $round, 'overall' => $overall];
        }
        // already ordered by picknum ASC so first row is first pick
    }

    // Determine year range
    $all_years = array_unique(array_map('intval', array_column($rows, 'year')));
    sort($all_years);
    $min_year = min($all_years);
    $max_year = max($all_years);
    $years = range($min_year, $max_year);

    // Build per-team stats and heatmap
    $team_stats = [];
    $heatmap    = [];

    foreach ($known_teams as $team) {
        $picks = $first_by_team_year[$team] ?? [];
        $heatmap[$team] = [];
        foreach ($years as $yr) {
            $heatmap[$team][$yr] = isset($picks[$yr]) ? $picks[$yr] : null;
        }

        if (empty($picks)) {
            $team_stats[] = [
                'team'         => $team,
                'n'            => 0,
                'avgOverall'   => null,
                'minOverall'   => null,
                'maxOverall'   => null,
                'avgRound'     => null,
                'skippedYears' => count($years),
            ];
            continue;
        }

        $overalls = array_column($picks, 'overall');
        $rounds   = array_column($picks, 'round');
        $n        = count($picks);

        $team_stats[] = [
            'team'         => $team,
            'n'            => $n,
            'avgOverall'   => round(array_sum($overalls) / $n, 1),
            'minOverall'   => min($overalls),
            'maxOverall'   => max($overalls),
            'avgRound'     => round(array_sum($rounds) / $n, 2),
            'skippedYears' => count($years) - $n,
        ];
    }

    // Sort team_stats by avgOverall ascending (earliest drafters first)
    usort($team_stats, function($a, $b) {
        if ($a['avgOverall'] === null) return 1;
        if ($b['avgOverall'] === null) return -1;
        return $a['avgOverall'] <=> $b['avgOverall'];
    });

    return rest_ensure_response([
        'teamStats' => $team_stats,
        'heatmap'   => $heatmap,
        'years'     => $years,
    ]);
}

// ═══════════════════════════════════════════════════════════════════════════════
// TABLES — PLAYER STATS ENDPOINTS
// ═══════════════════════════════════════════════════════════════════════════════

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/player-game-scores', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_player_game_scores',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/top-game-scores', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_top_game_scores',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/player-season-records', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_player_season_records',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/player-ppg-season', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_player_ppg_season',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/player-games-played', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_player_games_played',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/player-game-streak', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_player_game_streak',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/player-championships', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_player_championships',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/player-tight-ends', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_player_tight_ends',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/player-two-pt', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_player_two_pt',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/player-potw', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_player_potw',
        'permission_callback' => '__return_true',
    ]);
});

// ── Shared helper: all game scores cached ────────────────────────────────────
// Scans all player tables and caches every game score >= 13 pts.
// Filtered by pos/min at query time (cheap PHP filter on cached array).
function pfl_tables_all_game_scores_cached() {
    global $wpdb;
    $cache = get_transient('pfl_tables_all_game_scores_v2');
    if ($cache !== false) return $cache;

    $players = $wpdb->get_results(
        "SELECT p.p_id, p.playerFirst AS first, p.playerLast AS last
         FROM wp_players p
         ORDER BY p.playerLast",
        ARRAY_A
    );

    $results = [];
    foreach ($players as $p) {
        $pid = $p['p_id'];
        $pos = substr($pid, -2);

        // Only process valid position pids
        if (!in_array($pos, ['QB','RB','WR','PK'])) continue;

        // Quick existence check via allleaders
        $high = $wpdb->get_var($wpdb->prepare(
            "SELECT high FROM wp_allleaders WHERE pid = %s", $pid
        ));
        if (!$high || (float)$high < 13) continue;

        // Fetch games >= 13 pts from this player's individual table
        $tbl = "`{$pid}`";
        $games = $wpdb->get_results(
            "SELECT year, week, points, team FROM {$tbl} WHERE points >= 13 ORDER BY points DESC",
            ARRAY_A
        );
        if (!$games) continue;

        foreach ($games as $g) {
            $results[] = [
                'pid'   => $pid,
                'first' => $p['first'],
                'last'  => $p['last'],
                'pos'   => $pos,
                'year'  => (int)   $g['year'],
                'week'  => (int)   $g['week'],
                'pts'   => (float) $g['points'],
                'team'  => $g['team'],
            ];
        }
    }

    // Sort descending by points
    usort($results, fn($a, $b) => $b['pts'] <=> $a['pts']);

    set_transient('pfl_tables_all_game_scores_v2', $results, DAY_IN_SECONDS);
    return $results;
}

// ── Shared helper: all season records cached ─────────────────────────────────
function pfl_tables_all_season_records_cached() {
    global $wpdb;
    $cache = get_transient('pfl_tables_all_season_records_v2');
    if ($cache !== false) return $cache;

    $players = $wpdb->get_results(
        "SELECT p.p_id, p.playerFirst AS first, p.playerLast AS last
         FROM wp_players p",
        ARRAY_A
    );

    $results = [];
    foreach ($players as $p) {
        $pid = $p['p_id'];
        $pos = substr($pid, -2);
        if (!in_array($pos, ['QB','RB','WR','PK'])) continue;

        $tbl = "`{$pid}`";
        $rows = $wpdb->get_results(
            "SELECT year, SUM(points) AS pts, COUNT(*) AS games
             FROM {$tbl}
             GROUP BY year",
            ARRAY_A
        );
        if (!$rows) continue;

        foreach ($rows as $r) {
            $pts   = (float) $r['pts'];
            $games = (int)   $r['games'];
            $results[] = [
                'pid'   => $pid,
                'first' => $p['first'],
                'last'  => $p['last'],
                'pos'   => $pos,
                'year'  => (int) $r['year'],
                'pts'   => $pts,
                'games' => $games,
                'ppg'   => $games > 0 ? round($pts / $games, 1) : 0,
            ];
        }
    }

    usort($results, fn($a, $b) => $b['pts'] <=> $a['pts']);

    set_transient('pfl_tables_all_season_records_v2', $results, DAY_IN_SECONDS);
    return $results;
}

// ── /tables/player-game-scores?pos=QB&min=26 ─────────────────────────────────
function pfl_api_tables_player_game_scores(WP_REST_Request $request) {
    $pos = strtoupper(sanitize_text_field($request->get_param('pos') ?? 'ALL'));
    $min = (float) ($request->get_param('min') ?? 13);

    $all = pfl_tables_all_game_scores_cached();

    $filtered = array_values(array_filter($all, function($r) use ($pos, $min) {
        if ($r['pts'] < $min) return false;
        if ($pos !== 'ALL' && $r['pos'] !== $pos) return false;
        return true;
    }));

    return rest_ensure_response($filtered);
}

// ── /tables/top-game-scores ───────────────────────────────────────────────────
function pfl_api_tables_top_game_scores() {
    $all = pfl_tables_all_game_scores_cached();
    return rest_ensure_response(array_slice($all, 0, 25));
}

// ── /tables/player-season-records?pos=QB ─────────────────────────────────────
function pfl_api_tables_player_season_records(WP_REST_Request $request) {
    $pos = strtoupper(sanitize_text_field($request->get_param('pos') ?? 'QB'));

    // Threshold by position
    $min = ($pos === 'PK') ? 75 : 100;

    $all = pfl_tables_all_season_records_cached();

    $filtered = array_values(array_filter($all, function($r) use ($pos, $min) {
        return $r['pos'] === $pos && $r['pts'] >= $min;
    }));

    return rest_ensure_response($filtered);
}

// ── /tables/player-ppg-season ────────────────────────────────────────────────
function pfl_api_tables_player_ppg_season() {
    $all = pfl_tables_all_season_records_cached();

    // Min 8 games, sort by PPG desc
    $filtered = array_filter($all, fn($r) => $r['games'] >= 8);
    usort($filtered, fn($a, $b) => $b['ppg'] <=> $a['ppg']);

    return rest_ensure_response(array_values($filtered));
}

// ── /tables/player-games-played ──────────────────────────────────────────────
function pfl_api_tables_player_games_played() {
    global $wpdb;
    $cache = get_transient('pfl_tables_games_played_v2');
    if ($cache !== false) return rest_ensure_response($cache);

    $rows = $wpdb->get_results(
        "SELECT al.pid, p.playerFirst AS first, p.playerLast AS last,
                al.position AS pos, al.games, al.seasons, al.points
         FROM wp_allleaders al
         JOIN wp_players p ON p.p_id = al.pid
         WHERE al.games >= 100
         ORDER BY al.games DESC",
        ARRAY_A
    );

    $result = array_map(fn($r) => [
        'pid'     => $r['pid'],
        'first'   => $r['first'],
        'last'    => $r['last'],
        'pos'     => $r['pos'],
        'games'   => (int)   $r['games'],
        'seasons' => (int)   $r['seasons'],
        'points'  => (float) $r['points'],
    ], $rows);

    set_transient('pfl_tables_games_played_v2', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/player-game-streak ───────────────────────────────────────────────
function pfl_api_tables_player_game_streak() {
    global $wpdb;
    $cache = get_transient('pfl_tables_game_streak_v2');
    if ($cache !== false) return rest_ensure_response($cache);

    $rows = $wpdb->get_results(
        "SELECT al.pid, p.playerFirst AS first, p.playerLast AS last,
                al.position AS pos, al.gamestreak AS streak,
                al.games, al.points
         FROM wp_allleaders al
         JOIN wp_players p ON p.p_id = al.pid
         WHERE al.gamestreak > 0
         ORDER BY al.gamestreak DESC
         LIMIT 25",
        ARRAY_A
    );

    $result = array_map(fn($r) => [
        'pid'    => $r['pid'],
        'first'  => $r['first'],
        'last'   => $r['last'],
        'pos'    => $r['pos'],
        'streak' => (int)   $r['streak'],
        'games'  => (int)   $r['games'],
        'points' => (float) $r['points'],
    ], $rows);

    set_transient('pfl_tables_game_streak_v2', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/player-championships ─────────────────────────────────────────────
function pfl_api_tables_player_championships() {
    global $wpdb;
    $cache = get_transient('pfl_tables_championships_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    // Championship players: week 16 Posse Bowl winners in wp_playoffs
    // (mirrors get_player_champions() logic)
    $champ_rows = $wpdb->get_results(
        "SELECT playerid, year FROM wp_playoffs WHERE week = '16' AND result = 1",
        ARRAY_A
    );

    // Aggregate by player
    $by_player = [];
    foreach ($champ_rows as $r) {
        $pid = $r['playerid'];
        if (!$pid) continue;
        if (!isset($by_player[$pid])) {
            $by_player[$pid] = ['years' => []];
        }
        if (!in_array((int) $r['year'], $by_player[$pid]['years'])) {
            $by_player[$pid]['years'][] = (int) $r['year'];
        }
    }

    // Fetch names and positions
    $result = [];
    foreach ($by_player as $pid => $data) {
        $player = $wpdb->get_row($wpdb->prepare(
            "SELECT p.playerFirst AS first, p.playerLast AS last,
                    al.position AS pos
             FROM wp_players p
             LEFT JOIN wp_allleaders al ON al.pid = p.p_id
             WHERE p.p_id = %s LIMIT 1",
            $pid
        ), ARRAY_A);
        if (!$player) continue;

        sort($data['years']);
        $result[] = [
            'pid'           => $pid,
            'first'         => $player['first'],
            'last'          => $player['last'],
            'pos'           => $player['pos'] ?? substr($pid, -2),
            'championships' => count($data['years']),
            'years'         => $data['years'],
        ];
    }

    usort($result, fn($a, $b) => $b['championships'] <=> $a['championships']);

    set_transient('pfl_tables_championships_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/player-tight-ends ────────────────────────────────────────────────
function pfl_api_tables_player_tight_ends() {
    global $wpdb;
    $cache = get_transient('pfl_tables_tight_ends_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    // Get all TEs from wp_tightends (PID is at index 1, matching get_tightends())
    $te_rows = $wpdb->get_results("SELECT * FROM wp_tightends", ARRAY_N);

    $result = [];
    foreach ($te_rows as $r) {
        // PID is at numeric index 1 (same as existing get_tightends function)
        $pid = $r[1] ?? null;
        if (!$pid) continue;

        // Get career stats from allleaders
        $al = $wpdb->get_row($wpdb->prepare(
            "SELECT al.points, al.games, al.seasons,
                    p.playerFirst AS first, p.playerLast AS last
             FROM wp_allleaders al
             JOIN wp_players p ON p.p_id = al.pid
             WHERE al.pid = %s LIMIT 1",
            $pid
        ), ARRAY_A);
        if (!$al) continue;

        $pts   = (float) $al['points'];
        $games = (int)   $al['games'];
        $result[] = [
            'pid'     => $pid,
            'first'   => $al['first'],
            'last'    => $al['last'],
            'points'  => $pts,
            'games'   => $games,
            'ppg'     => $games > 0 ? round($pts / $games, 1) : 0,
            'seasons' => (int) $al['seasons'],
        ];
    }

    usort($result, fn($a, $b) => $b['points'] <=> $a['points']);

    set_transient('pfl_tables_tight_ends_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/player-two-pt ────────────────────────────────────────────────────
function pfl_api_tables_player_two_pt() {
    global $wpdb;
    $cache = get_transient('pfl_tables_two_pt_v2');
    if ($cache !== false) return rest_ensure_response($cache);

    $players = $wpdb->get_results(
        "SELECT p.p_id, p.playerFirst AS first, p.playerLast AS last
         FROM wp_players p",
        ARRAY_A
    );

    $result = [];
    foreach ($players as $p) {
        $pid = $p['p_id'];
        $pos = substr($pid, -2);
        if (!in_array($pos, ['QB','RB','WR','PK'])) continue;

        $tbl   = "`{$pid}`";
        $total = $wpdb->get_var("SELECT SUM(twopt) FROM {$tbl}");
        $total = (int) $total;
        if ($total < 5) continue;

        $result[] = [
            'pid'   => $pid,
            'first' => $p['first'],
            'last'  => $p['last'],
            'pos'   => $pos,
            'twopt' => $total,
        ];
    }

    usort($result, fn($a, $b) => $b['twopt'] <=> $a['twopt']);

    set_transient('pfl_tables_two_pt_v2', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/player-potw ──────────────────────────────────────────────────────
function pfl_api_tables_player_potw() {
    global $wpdb;
    $cache = get_transient('pfl_tables_potw_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $rows = $wpdb->get_results(
        "SELECT w.playerid, COUNT(*) AS cnt
         FROM wp_player_of_week w
         GROUP BY w.playerid
         HAVING COUNT(*) >= 5
         ORDER BY cnt DESC",
        ARRAY_A
    );

    $result = [];
    foreach ($rows as $r) {
        $pid    = $r['playerid'];
        $player = $wpdb->get_row($wpdb->prepare(
            "SELECT p.playerFirst AS first, p.playerLast AS last,
                    al.position AS pos
             FROM wp_players p
             LEFT JOIN wp_allleaders al ON al.pid = p.p_id
             WHERE p.p_id = %s LIMIT 1",
            $pid
        ), ARRAY_A);
        if (!$player) continue;

        $result[] = [
            'pid'   => $pid,
            'first' => $player['first'],
            'last'  => $player['last'],
            'pos'   => $player['pos'] ?? substr($pid, -2),
            'count' => (int) $r['cnt'],
        ];
    }

    set_transient('pfl_tables_potw_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ══════════════════════════════════════════════════════════════════════════════
// TABLES — TEAM STATS ENDPOINTS
// ══════════════════════════════════════════════════════════════════════════════

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/team-career-stats', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_team_career_stats',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/team-season-highs', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_team_season_highs',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/team-game-highs', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_team_game_highs',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/team-blowouts', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_team_blowouts',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/team-fifty-stats', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_team_fifty_stats',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/team-yearly-leaders', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_team_yearly_leaders',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/team-division-records', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_team_division_records',
        'permission_callback' => '__return_true',
    ]);
});

// ── Team name map ─────────────────────────────────────────────────────────────
function pfl_team_names() {
    return [
        'RBS' => 'Red Barons',    'ETS' => 'Euro-Trashers',
        'PEP' => 'Peppers',       'WRZ' => 'Space Warriorz',
        'CMN' => 'C-Men',         'BUL' => 'Raging Bulls',
        'SNR' => 'Sixty Niners',  'TSG' => 'Tsongas',
        'BST' => 'Booty Bustas',  'SON' => 'Rising Sons',
        'PHR' => 'Paraphernalia', 'HAT' => 'Jimmys Hats',
        'ATK' => 'Melmac Attack', 'MAX' => 'Mad Max',
        'DST' => 'Destruction',
    ];
}

// ── Shared helper: all team games cached ─────────────────────────────────────
// Loops all 15 wp_team_{ABBR} tables, returns every game row.
// Columns by position: [0]=id [1]=season [2]=week [3]=team_int
//   [4]=points [5]=versus [6]=versus_pts [7]=home_away [8]=stadium [9]=result
// result = point differential (positive=win, negative=loss, 0=tie)
function pfl_tables_all_team_games_cached() {
    global $wpdb;
    $cache = get_transient('pfl_tables_all_team_games_v1');
    if ($cache !== false) return $cache;

    $teams = array_keys(pfl_team_names());
    $results = [];

    foreach ($teams as $team) {
        $tbl  = "wp_team_{$team}";
        $rows = $wpdb->get_results("SELECT * FROM `{$tbl}`", ARRAY_N);
        if (!$rows) continue;

        foreach ($rows as $r) {
            $results[] = [
                'team'       => $team,
                'season'     => (int)   $r[1],
                'week'       => (int)   $r[2],
                'points'     => (float) $r[4],
                'versus'     => (string)$r[5],
                'versus_pts' => (float) $r[6],
                'result'     => (float) $r[9],
            ];
        }
    }

    set_transient('pfl_tables_all_team_games_v1', $results, DAY_IN_SECONDS);
    return $results;
}

// ── Shared helper: all standings cached ──────────────────────────────────────
// Delegates to the existing get_standings() for each year so column mapping
// is always correct regardless of table structure differences between years.
function pfl_tables_all_standings_cached() {
    $cache = get_transient('pfl_tables_all_standings_v3');
    if ($cache !== false) return $cache;

    $results = [];
    for ($year = 1991; $year <= (int) date('Y'); $year++) {
        $standing = get_standings($year);
        if (!$standing) continue;
        foreach ($standing as $item) {
            $results[] = [
                'year'     => (int) $item['year'],
                'seed'     => (int) $item['seed'],
                'division' => $item['division'],
                'teamid'   => $item['teamid'],
                'win'      => (int) $item['win'],
                'loss'     => (int) $item['loss'],
                'pts'      => (int) $item['pts'],
                'ptsvs'    => (int) $item['ptsvs'],
                'divwin'   => (int) $item['divwin'],
                'divloss'  => (int) $item['divloss'],
            ];
        }
    }

    set_transient('pfl_tables_all_standings_v3', $results, DAY_IN_SECONDS);
    return $results;
}

// ── /tables/team-career-stats ─────────────────────────────────────────────────
// Returns all 15 teams with aggregated career stats.
function pfl_api_tables_team_career_stats() {
    $cache = get_transient('pfl_tables_team_career_stats_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $all   = pfl_tables_all_team_games_cached();
    $names = pfl_team_names();

    $agg = [];
    foreach ($all as $g) {
        $t = $g['team'];
        if (!isset($agg[$t])) {
            $agg[$t] = ['pts' => 0, 'wins' => 0, 'games' => 0, 'pts_allowed' => 0];
        }
        $agg[$t]['pts']         += $g['points'];
        $agg[$t]['pts_allowed'] += $g['versus_pts'];
        $agg[$t]['games']++;
        if ($g['result'] >= 0) $agg[$t]['wins']++;
    }

    $result = [];
    foreach ($agg as $team => $d) {
        $games   = $d['games'];
        $seasons = $games > 0 ? round($games / 14, 1) : 0;
        $result[] = [
            'team'            => $team,
            'name'            => $names[$team],
            'pts'             => (int)   $d['pts'],
            'ppg'             => $games > 0 ? round($d['pts']         / $games,   1) : 0,
            'wins'            => (int)   $d['wins'],
            'games'           => $games,
            'win_pct'         => $games > 0 ? round($d['wins']        / $games,   3) : 0,
            'pts_allowed'     => (int)   $d['pts_allowed'],
            'seasons'         => $seasons,
            'avg_pts_against' => $seasons >= 1 ? round($d['pts_allowed'] / $seasons, 1) : 0,
        ];
    }

    set_transient('pfl_tables_team_career_stats_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/team-season-highs ─────────────────────────────────────────────────
function pfl_api_tables_team_season_highs() {
    $cache = get_transient('pfl_tables_team_season_highs_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $all   = pfl_tables_all_team_games_cached();
    $names = pfl_team_names();

    $by_season = [];
    foreach ($all as $g) {
        $key = $g['team'] . '_' . $g['season'];
        if (!isset($by_season[$key])) {
            $by_season[$key] = ['team' => $g['team'], 'season' => $g['season'], 'pts' => 0];
        }
        $by_season[$key]['pts'] += $g['points'];
    }

    usort($by_season, fn($a, $b) => $b['pts'] <=> $a['pts']);
    $top = array_slice(array_values($by_season), 0, 15);

    $result = array_map(fn($r) => [
        'team'   => $r['team'],
        'name'   => $names[$r['team']],
        'season' => $r['season'],
        'pts'    => (int) $r['pts'],
    ], $top);

    set_transient('pfl_tables_team_season_highs_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/team-game-highs ───────────────────────────────────────────────────
function pfl_api_tables_team_game_highs() {
    $cache = get_transient('pfl_tables_team_game_highs_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $all   = pfl_tables_all_team_games_cached();
    $names = pfl_team_names();

    usort($all, fn($a, $b) => $b['points'] <=> $a['points']);
    $top = array_slice($all, 0, 15);

    $result = array_map(fn($g) => [
        'team'   => $g['team'],
        'name'   => $names[$g['team']],
        'season' => $g['season'],
        'week'   => $g['week'],
        'pts'    => (int) $g['points'],
        'versus' => $g['versus'],
    ], $top);

    set_transient('pfl_tables_team_game_highs_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/team-blowouts ─────────────────────────────────────────────────────
function pfl_api_tables_team_blowouts() {
    $cache = get_transient('pfl_tables_team_blowouts_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $all   = pfl_tables_all_team_games_cached();
    $names = pfl_team_names();

    $wins = array_values(array_filter($all, fn($g) => $g['result'] > 0));
    usort($wins, fn($a, $b) => $b['result'] <=> $a['result']);
    $top = array_slice($wins, 0, 15);

    $result = array_map(fn($g) => [
        'team'   => $g['team'],
        'name'   => $names[$g['team']],
        'season' => $g['season'],
        'week'   => $g['week'],
        'pts'    => (int) $g['points'],
        'versus' => $g['versus'],
        'margin' => (int) $g['result'],
    ], $top);

    set_transient('pfl_tables_team_blowouts_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/team-fifty-stats ──────────────────────────────────────────────────
// 50+ pt game count and max consecutive streak (regular season only, week 1–14)
function pfl_api_tables_team_fifty_stats() {
    $cache = get_transient('pfl_tables_team_fifty_stats_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $all   = pfl_tables_all_team_games_cached();
    $names = pfl_team_names();
    $teams = array_keys($names);

    // Build per-team sorted regular-season game list
    $by_team = [];
    foreach ($all as $g) {
        if ($g['week'] < 1 || $g['week'] > 14) continue;
        $by_team[$g['team']][] = $g;
    }

    $result = [];
    foreach ($teams as $team) {
        $games = $by_team[$team] ?? [];
        usort($games, fn($a, $b) =>
            $a['season'] !== $b['season']
                ? $a['season'] - $b['season']
                : $a['week'] - $b['week']
        );

        $count      = 0;
        $max_streak = 0;
        $cur_streak = 0;
        foreach ($games as $g) {
            if ($g['points'] >= 50) {
                $count++;
                $cur_streak++;
                if ($cur_streak > $max_streak) $max_streak = $cur_streak;
            } else {
                $cur_streak = 0;
            }
        }

        $result[] = [
            'team'       => $team,
            'name'       => $names[$team],
            'count'      => $count,
            'max_streak' => $max_streak,
        ];
    }

    set_transient('pfl_tables_team_fifty_stats_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/team-yearly-leaders ───────────────────────────────────────────────
// Per year: highest-scoring team (point winner) and lowest-pts-allowed team (defense winner)
function pfl_api_tables_team_yearly_leaders() {
    $cache = get_transient('pfl_tables_team_yearly_leaders_v4');
    if ($cache !== false) return rest_ensure_response($cache);

    $standings = pfl_tables_all_standings_cached();
    $names     = pfl_team_names();

    $by_year = [];
    foreach ($standings as $r) {
        $by_year[$r['year']][] = $r;
    }
    ksort($by_year);

    $result = [];
    foreach ($by_year as $year => $rows) {
        $max_pts  = PHP_INT_MIN;
        $min_ptsvs = PHP_INT_MAX;
        $point_winner   = null;
        $defense_winner = null;
        $point_pts      = 0;
        $defense_pts    = 0;

        foreach ($rows as $r) {
            if ($r['pts'] > 0 && $r['pts'] > $max_pts) {
                $max_pts      = $r['pts'];
                $point_winner = $r['teamid'];
                $point_pts    = $r['pts'];
            }
            if ($r['ptsvs'] > 0 && $r['ptsvs'] < $min_ptsvs) {
                $min_ptsvs      = $r['ptsvs'];
                $defense_winner = $r['teamid'];
                $defense_pts    = $r['ptsvs'];
            }
        }

        $result[] = [
            'year'                => $year,
            'point_winner'        => $point_winner,
            'point_winner_name'   => $point_winner ? ($names[$point_winner]   ?? $point_winner)   : '',
            'point_pts'           => (int) $point_pts,
            'defense_winner'      => $defense_winner,
            'defense_winner_name' => $defense_winner ? ($names[$defense_winner] ?? $defense_winner) : '',
            'defense_pts'         => (int) $defense_pts,
        ];
    }

    set_transient('pfl_tables_team_yearly_leaders_v4', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/team-division-records ─────────────────────────────────────────────
// Returns { division_winners: [...], best_records: [...] }
function pfl_api_tables_team_division_records() {
    $cache = get_transient('pfl_tables_team_division_records_v4');
    if ($cache !== false) return rest_ensure_response($cache);

    $standings = pfl_tables_all_standings_cached();
    $names     = pfl_team_names();

    // Division winners: team with the lowest overall seed (> 0) per division per year.
    // seed is the overall playoff seed, not within-division. The division winner is the
    // team with the minimum seed > 0 in that division (matching the old reset() logic).
    $div_seeds = [];
    foreach ($standings as $r) {
        if ($r['seed'] <= 0) continue;
        $y = $r['year'];
        $d = $r['division'];
        if (!isset($div_seeds[$y][$d]) || $r['seed'] < $div_seeds[$y][$d]['seed']) {
            $div_seeds[$y][$d] = ['seed' => $r['seed'], 'teamid' => $r['teamid']];
        }
    }

    $by_year = [];
    foreach ($div_seeds as $year => $divs) {
        foreach ($divs as $div => $entry) {
            $by_year[$year][$div] = $entry['teamid'];
        }
    }
    ksort($by_year);

    $division_winners = [];
    foreach ($by_year as $year => $divs) {
        $row = ['year' => $year];
        foreach (['PFL', 'EGAD', 'DGAS', 'MGAC'] as $div) {
            $tid = $divs[$div] ?? '';
            $row[$div]          = $tid;
            $row[$div . '_name'] = $tid ? ($names[$tid] ?? $tid) : '';
        }
        $division_winners[] = $row;
    }

    // Best division records (top 15 by win%)
    $div_records = [];
    foreach ($standings as $r) {
        $total = $r['divwin'] + $r['divloss'];
        if ($total === 0) continue;
        $div_records[] = [
            'year'     => $r['year'],
            'teamid'   => $r['teamid'],
            'name'     => $names[$r['teamid']] ?? $r['teamid'],
            'divwin'   => $r['divwin'],
            'divloss'  => $r['divloss'],
            'win_pct'  => round($r['divwin'] / $total, 3),
            'division' => $r['division'],
        ];
    }
    usort($div_records, fn($a, $b) => $b['win_pct'] <=> $a['win_pct']);

    $result = [
        'division_winners' => $division_winners,
        'best_records'     => array_slice($div_records, 0, 15),
    ];

    set_transient('pfl_tables_team_division_records_v4', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ══════════════════════════════════════════════════════════════════════════════
// TABLES — POSTSEASON ENDPOINTS
// ══════════════════════════════════════════════════════════════════════════════

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/post-team-stats', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_post_team_stats',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/post-player-scores', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_post_player_scores',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/post-player-career', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_post_player_career',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/post-only-players', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_post_only_players',
        'permission_callback' => '__return_true',
    ]);
});

// ── /tables/post-team-stats ────────────────────────────────────────────────
// Returns all team postseason aggregates in one payload:
//   championships, pb_appearances, playoff_appearances, playoff_records
function pfl_api_tables_post_team_stats() {
    global $wpdb;
    $cache = get_transient('pfl_tables_post_team_stats_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $names = pfl_team_names();
    $teams = array_keys($names);

    // ── Championships & Posse Bowl appearances from wp_champions (ARRAY_N)
    // [2] = winner team, [5] = loser team
    $champs_rows = $wpdb->get_results("SELECT * FROM wp_champions ORDER BY year", ARRAY_N);
    $champ_count = [];
    $pb_count    = [];
    foreach ($champs_rows as $r) {
        $winner = $r[2] ?? null;
        $loser  = $r[5] ?? null;
        if ($winner) {
            $champ_count[$winner] = ($champ_count[$winner] ?? 0) + 1;
            $pb_count[$winner]    = ($pb_count[$winner]    ?? 0) + 1;
        }
        if ($loser) {
            $pb_count[$loser] = ($pb_count[$loser] ?? 0) + 1;
        }
    }

    // ── Playoff appearances (week 15 = semifinal, one per team per year)
    $app_rows = $wpdb->get_results(
        "SELECT team, year FROM wp_playoffs WHERE week = 15 GROUP BY team, year",
        ARRAY_A
    );
    $app_count = [];
    foreach ($app_rows as $r) {
        $app_count[$r['team']] = ($app_count[$r['team']] ?? 0) + 1;
    }

    // ── Seasons played per team (from career stats helper)
    $all_games   = pfl_tables_all_team_games_cached();
    $seasons_map = [];
    foreach ($all_games as $g) {
        $seasons_map[$g['team']][$g['season']] = 1;
    }

    // ── Playoff win/loss record (weeks 15–16, one result per team per game)
    $record_rows = $wpdb->get_results(
        "SELECT team, year, week, MAX(result) AS result
         FROM wp_playoffs
         WHERE week >= 15
         GROUP BY team, year, week",
        ARRAY_A
    );
    $pl_wins   = [];
    $pl_losses = [];
    foreach ($record_rows as $r) {
        $t = $r['team'];
        if ((int)$r['result'] === 1) {
            $pl_wins[$t] = ($pl_wins[$t] ?? 0) + 1;
        } else {
            $pl_losses[$t] = ($pl_losses[$t] ?? 0) + 1;
        }
    }

    // ── Build per-team rows
    $championships     = [];
    $pb_appearances    = [];
    $playoff_app       = [];
    $playoff_records   = [];

    foreach ($teams as $t) {
        $seasons  = count($seasons_map[$t] ?? []);
        $apps     = $app_count[$t] ?? 0;
        $app_pct  = $seasons > 0 ? round($apps / $seasons, 3) : 0;
        $w        = $pl_wins[$t]   ?? 0;
        $l        = $pl_losses[$t] ?? 0;
        $total_pl = $w + $l;
        $pl_pct   = $total_pl > 0 ? round($w / $total_pl, 3) : 0;

        if (($champ_count[$t] ?? 0) > 0) {
            $championships[] = ['team' => $t, 'name' => $names[$t], 'count' => $champ_count[$t]];
        }
        if (($pb_count[$t] ?? 0) > 0) {
            $pb_appearances[] = ['team' => $t, 'name' => $names[$t], 'count' => $pb_count[$t]];
        }
        $playoff_app[] = [
            'team'    => $t, 'name' => $names[$t],
            'count'   => $apps,
            'seasons' => $seasons,
            'pct'     => $app_pct,
        ];
        if ($total_pl > 0) {
            $playoff_records[] = [
                'team'   => $t, 'name' => $names[$t],
                'wins'   => $w,
                'losses' => $l,
                'games'  => $total_pl,
                'pct'    => $pl_pct,
            ];
        }
    }

    usort($championships,   fn($a, $b) => $b['count']  <=> $a['count']);
    usort($pb_appearances,  fn($a, $b) => $b['count']  <=> $a['count']);
    usort($playoff_app,     fn($a, $b) => $b['count']  <=> $a['count']);
    usort($playoff_records, fn($a, $b) => $b['pct']    <=> $a['pct']);

    $result = compact('championships', 'pb_appearances', 'playoff_app', 'playoff_records');

    set_transient('pfl_tables_post_team_stats_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/post-player-scores ────────────────────────────────────────────────
// Top individual playoff game scores (25+ pts)
function pfl_api_tables_post_player_scores() {
    global $wpdb;
    $cache = get_transient('pfl_tables_post_player_scores_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $rows = $wpdb->get_results(
        "SELECT pl.playerid, pl.year, pl.week, pl.points, pl.team,
                p.playerFirst AS first, p.playerLast AS last
         FROM wp_playoffs pl
         JOIN wp_players p ON p.p_id = pl.playerid
         WHERE pl.points >= 25
         ORDER BY pl.points DESC",
        ARRAY_A
    );

    $result = array_map(fn($r) => [
        'pid'        => $r['playerid'],
        'first'      => $r['first'],
        'last'       => $r['last'],
        'pos'        => substr($r['playerid'], -2),
        'year'       => (int) $r['year'],
        'week_label' => (int)$r['week'] === 16 ? 'Posse Bowl' : 'Playoffs',
        'pts'        => (float) $r['points'],
        'team'       => $r['team'],
    ], $rows);

    set_transient('pfl_tables_post_player_scores_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/post-player-career ────────────────────────────────────────────────
// Returns { career_pts: [...top 20], titles: [...players with 2+ titles] }
function pfl_api_tables_post_player_career() {
    global $wpdb;
    $cache = get_transient('pfl_tables_post_player_career_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    // Career playoff points (top 20)
    $pts_rows = $wpdb->get_results(
        "SELECT pl.playerid, SUM(pl.points) AS total,
                p.playerFirst AS first, p.playerLast AS last
         FROM wp_playoffs pl
         JOIN wp_players p ON p.p_id = pl.playerid
         GROUP BY pl.playerid
         ORDER BY total DESC
         LIMIT 20",
        ARRAY_A
    );
    $career_pts = array_map(fn($r) => [
        'pid'   => $r['playerid'],
        'first' => $r['first'],
        'last'  => $r['last'],
        'pts'   => (float) $r['total'],
    ], $pts_rows);

    // Posse Bowl titles (week 16, result = 1) — players with 2+ titles
    $title_rows = $wpdb->get_results(
        "SELECT pl.playerid, COUNT(DISTINCT pl.year) AS cnt,
                GROUP_CONCAT(DISTINCT pl.year ORDER BY pl.year ASC SEPARATOR ',') AS years,
                p.playerFirst AS first, p.playerLast AS last
         FROM wp_playoffs pl
         JOIN wp_players p ON p.p_id = pl.playerid
         WHERE pl.week = 16 AND pl.result = 1
         GROUP BY pl.playerid
         HAVING cnt >= 2
         ORDER BY cnt DESC",
        ARRAY_A
    );
    $titles = array_map(fn($r) => [
        'pid'   => $r['playerid'],
        'first' => $r['first'],
        'last'  => $r['last'],
        'count' => (int) $r['cnt'],
        'years' => explode(',', $r['years']),
    ], $title_rows);

    $result = compact('career_pts', 'titles');
    set_transient('pfl_tables_post_player_career_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/post-only-players ─────────────────────────────────────────────────
// Players who appeared in wp_playoffs but never played a regular season game
function pfl_api_tables_post_only_players() {
    global $wpdb;
    $cache = get_transient('pfl_tables_post_only_players_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    // Get all distinct playerids in wp_playoffs that have no entry in wp_allleaders
    // (wp_allleaders only has players with regular season games)
    $rows = $wpdb->get_results(
        "SELECT pl.playerid,
                SUM(pl.points)         AS total_pts,
                COUNT(*)               AS games,
                GROUP_CONCAT(DISTINCT pl.team ORDER BY pl.team SEPARATOR ', ') AS teams,
                MAX(CASE WHEN pl.week = 16 AND pl.result = 1 THEN 1 ELSE 0 END) AS champion,
                p.playerFirst AS first, p.playerLast AS last
         FROM wp_playoffs pl
         JOIN wp_players p ON p.p_id = pl.playerid
         WHERE pl.playerid NOT IN (SELECT pid FROM wp_allleaders)
           AND pl.playerid != '' AND pl.playerid != 'None'
         GROUP BY pl.playerid
         ORDER BY total_pts DESC",
        ARRAY_A
    );

    $result = array_map(fn($r) => [
        'pid'      => $r['playerid'],
        'first'    => $r['first'],
        'last'     => $r['last'],
        'teams'    => $r['teams'],
        'games'    => (int)   $r['games'],
        'pts'      => (float) $r['total_pts'],
        'champion' => (int)   $r['champion'] === 1,
    ], $rows);

    set_transient('pfl_tables_post_only_players_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}


// ============================================================
// TABLES — OTHER STATS ENDPOINTS
// ============================================================

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/other-career-duration', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_career_duration',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/other-players-college', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_players_college',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/other-stats-tiles', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_stats_tiles',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/other-rematch-game', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_rematch_game',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/other-number-twoed', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_number_twoed',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/other-bswoty', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_bswoty',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/other-rookie-seasons', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_rookie_seasons',
        'permission_callback' => '__return_true',
    ]);
});

// ── /tables/other-career-duration ────────────────────────────────────────────
// Average career length (games + seasons) by position
function pfl_api_tables_other_career_duration() {
    $cache = get_transient('pfl_tables_other_career_duration_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $data = get_all_players_games_played();
    $result = [];
    foreach ($data as $pos => $val) {
        $result[] = [
            'pos'     => $pos,
            'avg'     => round((float) $val['avg'], 1),
            'season'  => round((float) $val['season'], 1),
        ];
    }
    set_transient('pfl_tables_other_career_duration_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/other-players-college ────────────────────────────────────────────
// Players grouped by college (stop when count <= 10)
function pfl_api_tables_other_players_college() {
    $cache = get_transient('pfl_tables_other_players_college_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $playersassoc = get_players_assoc();
    $college = [];
    foreach ($playersassoc as $key => $value) {
        $school = isset($value[7]) ? trim($value[7]) : '';
        if ($school !== '') {
            $college[$school][] = $key;
        }
    }

    $collegecount = [];
    foreach ($college as $school => $players) {
        $collegecount[$school] = count($players);
    }
    arsort($collegecount);

    $result = [];
    foreach ($collegecount as $school => $count) {
        $result[] = ['college' => $school, 'count' => $count];
        if ($count <= 10) break;
    }

    set_transient('pfl_tables_other_players_college_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/other-stats-tiles ─────────────────────────────────────────────────
// Total players, total games, OT count, 13-game season count
function pfl_api_tables_other_stats_tiles() {
    global $wpdb;
    $cache = get_transient('pfl_tables_other_stats_tiles_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    // Count players (one row per player in wp_allleaders)
    $count_players = (int) $wpdb->get_var("SELECT COUNT(*) FROM wp_allleaders");

    // Game count: sum((teams/2)*14) per year
    $teamsbyseason = get_all_teams_by_season();
    $game_count = 0;
    foreach ($teamsbyseason as $val) {
        $game_count += $val['games'];
    }

    // Overtime count
    $count_ots = count(get_overtime());

    // 13-game season count: players who played 13 or 14 games in any single season
    $playerids = just_player_ids();
    $count_thirteens = 0;
    foreach ($playerids as $pid) {
        $stats = get_player_career_stats($pid);
        $gamesbyseason = $stats['gamesbyseason'] ?? [];
        if (is_array($gamesbyseason)) {
            if (in_array(13, $gamesbyseason) || in_array(14, $gamesbyseason)) {
                $count_thirteens++;
            }
        }
    }

    $result = [
        'count_players'   => $count_players,
        'game_count'      => (int) $game_count,
        'count_ots'       => $count_ots,
        'count_thirteens' => $count_thirteens,
    ];
    set_transient('pfl_tables_other_stats_tiles_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/other-rematch-game ────────────────────────────────────────────────
// Week 1 rematch of previous Posse Bowl (starting 1996)
function pfl_api_tables_other_rematch_game() {
    $cache = get_transient('pfl_tables_other_rematch_game_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $pbgames = revenge_game();
    $result  = [];
    foreach ($pbgames as $year => $val) {
        if ($year < 1996) continue;
        if ($val['pb_winner'] === $val['next_win']) {
            $result[] = [
                'year'    => (int) $year,
                'winner'  => $val['next_win'],
                'loser'   => $val['next_loser'],
                'outcome' => '',
            ];
        } elseif ($val['pb_winner'] === $val['next_loser']) {
            $result[] = [
                'year'    => (int) $year,
                'winner'  => $val['next_win'],
                'loser'   => $val['next_loser'],
                'outcome' => 'REVENGE!',
            ];
        }
    }

    set_transient('pfl_tables_other_rematch_game_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/other-number-twoed ────────────────────────────────────────────────
// 2nd-highest score in a week that still lost
function pfl_api_tables_other_number_twoed() {
    $cache = get_transient('pfl_tables_other_number_twoed_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $twos   = get_number_twoed();
    $result = [];
    foreach ($twos as $weekid => $val) {
        $result[] = [
            'season'     => (int) $val['season'],
            'week'       => (int) $val['week'],
            'team'       => $val['team_int'],
            'score'      => (float) $val['points'],
            'versus_pts' => (float) $val['versus_pts'],
            'lost_by'    => abs((float) $val['result']),
            'versus'     => $val['versus'],
        ];
    }

    set_transient('pfl_tables_other_number_twoed_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/other-bswoty ─────────────────────────────────────────────────────
// Bullshit Win of the Year — winner had lower score but won
function pfl_api_tables_other_bswoty() {
    $cache = get_transient('pfl_tables_other_bswoty_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $bswoty_data = get_bswins();
    $names       = pfl_team_names();
    $rows        = [];
    $winner_counts = [];

    foreach ($bswoty_data as $key => $val) {
        $season = (int) substr($key, 0, 4);
        $week   = (int) ltrim(substr($key, 4), '0');

        $winner_results = get_team_results_by_week($val['winner'], $key);
        $winner_score   = isset($winner_results[$key]) ? (float) $winner_results[$key]['points']     : 0;
        $loser_score    = isset($winner_results[$key]) ? (float) $winner_results[$key]['versus_pts'] : 0;

        $winner_name = $names[$val['winner']] ?? $val['winner'];
        $loser_name  = $names[$val['loser']]  ?? $val['loser'];

        $rows[] = [
            'season'       => $season,
            'week'         => $week,
            'winner'       => $winner_name,
            'winner_abbr'  => $val['winner'],
            'loser'        => $loser_name,
            'loser_abbr'   => $val['loser'],
            'winner_score' => $winner_score,
            'loser_score'  => $loser_score,
        ];

        $winner_counts[$winner_name] = ($winner_counts[$winner_name] ?? 0) + 1;
    }

    arsort($winner_counts);
    $footer_parts = [];
    foreach ($winner_counts as $team => $count) {
        $footer_parts[] = "$team ($count)";
    }

    $result = [
        'rows'   => $rows,
        'footer' => implode(', ', $footer_parts),
    ];
    set_transient('pfl_tables_other_bswoty_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/other-rookie-seasons ─────────────────────────────────────────────
// Best rookie seasons (top 20), with ROTY indicator
function pfl_api_tables_other_rookie_seasons() {
    $cache = get_transient('pfl_tables_other_rookie_seasons_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $rookieyears = get_player_rookie_years();
    $ro          = get_award_rookie(); // pid => year they won ROTY

    $rookieseason = [];
    foreach ($rookieyears as $pid => $season) {
        $grab = get_player_season_stats($pid, $season);
        if (isset($grab)) {
            $rookieseason[$pid] = [
                'points' => $grab['points'],
                'season' => (int) $season,
                'games'  => $grab['games'],
                'high'   => $grab['high'],
                'team'   => $grab['teams'][0] ?? '',
            ];
        }
    }

    uasort($rookieseason, fn($a, $b) => $b['points'] <=> $a['points']);

    $result = [];
    $count  = 0;
    foreach ($rookieseason as $pid => $val) {
        $name = get_player_name($pid);
        $ppg  = $val['games'] > 0 ? round($val['points'] / $val['games'], 1) : 0;
        $result[] = [
            'pid'     => $pid,
            'first'   => $name['first'] ?? '',
            'last'    => $name['last']  ?? '',
            'season'  => $val['season'],
            'points'  => (float) $val['points'],
            'games'   => (int) $val['games'],
            'ppg'     => $ppg,
            'team'    => $val['team'],
            'is_roty' => (isset($ro[$pid]) && (int)$ro[$pid] === $val['season']),
        ];
        $count++;
        if ($count >= 20) break;
    }

    set_transient('pfl_tables_other_rookie_seasons_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}


// ============================================================
// TABLES — NFL STATS ENDPOINTS
// ============================================================

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/nfl-stats', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_nfl_stats',
        'permission_callback' => '__return_true',
        'args'                => [
            'type' => ['required' => true, 'type' => 'string'],
        ],
    ]);
});

// ── Shared cache helper ───────────────────────────────────────────────────────
function pfl_tables_nfl_all_cached() {
    $cache = get_transient('pfl_tables_nfl_all_v1');
    if ($cache !== false) return $cache;

    $playerids = just_player_ids();

    // Arrays for rankings
    $pass_yards = []; $rush_yards = []; $rec_yards = [];
    $pass_tds   = []; $rush_tds   = []; $rec_tds   = [];
    $qb_rush    = []; $rb_rec     = []; $wr_rush    = [];
    $all_purpose = [];
    $qb_ypg     = []; $rb_ypg     = []; $wr_ypg     = [];
    $fgm_arr    = []; $fgpct_arr  = [];
    $xpm_arr    = []; $xppct_arr  = [];
    $nfl_team_counts = [];

    foreach ($playerids as $pid) {
        $pos = strtoupper(substr($pid, -2));
        $d   = get_player_career_stats($pid);
        if (!$d) continue;

        $games = (int) ($d['games'] ?? 0);

        // ── Passing (QB only) ──────────────────────────────────────────────
        if ($pos === 'QB' && !empty($d['passingyards'])) {
            $pass_yards[$pid] = (int) $d['passingyards'];
            if ($games >= 50) {
                $qb_ypg[$pid] = $d['passingyards'] / $games;
            }
        }

        // ── Rushing ────────────────────────────────────────────────────────
        if (!empty($d['rushyrds'])) {
            $rush_yards[$pid] = (int) $d['rushyrds'];
            if ($pos === 'QB') {
                $qb_rush[$pid] = (int) $d['rushyrds'];
            }
            if ($pos === 'RB' && $games >= 50) {
                $rb_ypg[$pid] = $d['rushyrds'] / $games;
            }
            if ($pos === 'WR') {
                $wr_rush[$pid] = (int) $d['rushyrds'];
            }
            // All-purpose: rush + rec, 50+ games
            if ($games >= 50) {
                $all_purpose[$pid] = (int) $d['rushyrds'] + (int) $d['recyrds'];
            }
        }

        // ── Receiving ──────────────────────────────────────────────────────
        if (!empty($d['recyrds'])) {
            $rec_yards[$pid] = (int) $d['recyrds'];
            if ($pos === 'RB') {
                $rb_rec[$pid] = (int) $d['recyrds'];
            }
            if ($pos === 'WR' && $games >= 50) {
                $wr_ypg[$pid] = $d['recyrds'] / $games;
            }
        }

        // ── TDs ────────────────────────────────────────────────────────────
        if (!empty($d['passingtds'])) $pass_tds[$pid] = (int) $d['passingtds'];
        if (!empty($d['rushtds']))    $rush_tds[$pid] = (int) $d['rushtds'];
        if (!empty($d['rectds']))     $rec_tds[$pid]  = (int) $d['rectds'];

        // ── Kicking (PK only) ──────────────────────────────────────────────
        if ($pos === 'PK') {
            $xpa_val = (int) ($d['xpa'] ?? 0);
            $fga_val = (int) ($d['fga'] ?? 0);
            if ($xpa_val > 0) {
                $xpm_arr[$pid] = (int) $d['xpm'];
                if ($xpa_val >= 100) {
                    $xppct_arr[$pid] = $d['xpm'] / $xpa_val;
                }
            }
            if ($fga_val > 0) {
                $fgm_arr[$pid] = (int) $d['fgm'];
                if ($fga_val >= 50) {
                    $fgpct_arr[$pid] = $d['fgm'] / $fga_val;
                }
            }
        }

        // ── NFL team game counts ────────────────────────────────────────────
        $weekly = get_player_data($pid);
        if ($weekly) {
            foreach ($weekly as $game) {
                $team = trim($game['nflteam'] ?? '');
                if ($team !== '') {
                    $nfl_team_counts[$team] = ($nfl_team_counts[$team] ?? 0) + 1;
                }
            }
        }
    }

    // Sort all arrays
    arsort($pass_yards); arsort($rush_yards); arsort($rec_yards);
    arsort($pass_tds);   arsort($rush_tds);   arsort($rec_tds);
    arsort($qb_rush);    arsort($rb_rec);      arsort($wr_rush);
    arsort($all_purpose);
    arsort($qb_ypg);     arsort($rb_ypg);      arsort($wr_ypg);
    arsort($fgm_arr);    arsort($fgpct_arr);
    arsort($xpm_arr);    arsort($xppct_arr);
    arsort($nfl_team_counts);

    // Helper: build top-N ranked list with player names
    $build_ranked = function(array $arr, int $limit = 25, string $fmt = 'int') use ($playerids) {
        $result = [];
        $i = 1;
        foreach ($arr as $pid => $val) {
            if ($i > $limit) break;
            $name = get_player_name($pid);
            $result[] = [
                'rank'  => $i,
                'pid'   => $pid,
                'first' => $name['first'] ?? '',
                'last'  => $name['last']  ?? '',
                'value' => $fmt === 'int' ? (int) $val : round((float) $val, 3),
            ];
            $i++;
        }
        return $result;
    };

    // Combine Raiders / Rams / Chargers historical aliases
    $raiders  = ($nfl_team_counts['OAK'] ?? 0) + ($nfl_team_counts['LVR'] ?? 0) + ($nfl_team_counts['RAI'] ?? 0);
    $rams     = ($nfl_team_counts['STL'] ?? 0) + ($nfl_team_counts['LAR'] ?? 0) + ($nfl_team_counts['RAM'] ?? 0);
    $chargers = ($nfl_team_counts['SD']  ?? 0) + ($nfl_team_counts['LAC'] ?? 0) + ($nfl_team_counts['SDG'] ?? 0);
    foreach (['OAK','LVR','RAI','STL','LAR','RAM','SD','LAC','SDG'] as $k) unset($nfl_team_counts[$k]);
    $nfl_team_counts['OAK'] = $raiders;
    $nfl_team_counts['LAR'] = $rams;
    $nfl_team_counts['LAC'] = $chargers;
    arsort($nfl_team_counts);

    // Build named team rows
    $nfl_team_rows = [];
    foreach ($nfl_team_counts as $abbr => $cnt) {
        if ($cnt <= 0) continue;
        $full = get_nfl_full_team_name_from_id($abbr) ?: $abbr;
        $nfl_team_rows[] = ['team' => $full, 'abbr' => $abbr, 'games' => $cnt];
    }

    $result = [
        'pass-yards'     => $build_ranked($pass_yards),
        'rush-yards'     => $build_ranked($rush_yards),
        'rec-yards'      => $build_ranked($rec_yards),
        'pass-tds'       => $build_ranked($pass_tds),
        'rush-tds'       => $build_ranked($rush_tds),
        'rec-tds'        => $build_ranked($rec_tds),
        'qb-rush-yards'  => $build_ranked($qb_rush),
        'rb-rec-yards'   => $build_ranked($rb_rec),
        'wr-rush-yards'  => $build_ranked($wr_rush),
        'all-purpose'    => $build_ranked($all_purpose),
        'qb-pass-ypg'    => $build_ranked($qb_ypg,    25, 'float'),
        'rb-rush-ypg'    => $build_ranked($rb_ypg,    25, 'float'),
        'wr-rec-ypg'     => $build_ranked($wr_ypg,    25, 'float'),
        'fg-made'        => $build_ranked($fgm_arr),
        'fg-pct'         => $build_ranked($fgpct_arr, 25, 'float'),
        'xp-made'        => $build_ranked($xpm_arr),
        'xp-pct'         => $build_ranked($xppct_arr, 25, 'float'),
        'nfl-team-games' => $nfl_team_rows,
    ];

    set_transient('pfl_tables_nfl_all_v1', $result, DAY_IN_SECONDS);
    return $result;
}

// ── /tables/nfl-stats?type=<slug> ────────────────────────────────────────────
function pfl_api_tables_nfl_stats(WP_REST_Request $request) {
    $type = sanitize_key($request->get_param('type'));
    $all  = pfl_tables_nfl_all_cached();
    if (!isset($all[$type])) {
        return new WP_Error('not_found', 'Unknown stat type: ' . $type, ['status' => 400]);
    }
    return rest_ensure_response($all[$type]);
}


// ============================================================
// SCORING TITLES ENDPOINT
// ============================================================

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/scoring-titles', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_scoring_titles',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_scoring_titles() {
    $cache = get_transient('pfl_scoring_titles_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $number_ones = get_number_ones();

    // Group by position then year, deduplicating same player+year (keep highest points)
    $by_pos = ['QB' => [], 'RB' => [], 'WR' => [], 'PK' => []];
    foreach ($number_ones as $row) {
        $pos = $row['pos'];
        if (!isset($by_pos[$pos])) continue;
        $year = $row['year'];
        $pid  = $row['playerid'];
        // If this player already has an entry for this year, keep the higher-points one
        if (isset($by_pos[$pos][$year][$pid])) {
            if ((float) $row['points'] > (float) $by_pos[$pos][$year][$pid]['points']) {
                $by_pos[$pos][$year][$pid] = $row;
            }
        } else {
            $by_pos[$pos][$year][$pid] = $row;
        }
    }

    // Count total wins per player per position
    $win_counts = [];
    foreach ($by_pos as $pos => $years) {
        $win_counts[$pos] = [];
        foreach ($years as $year_rows) {
            foreach ($year_rows as $row) {
                $pid = $row['playerid'];
                $win_counts[$pos][$pid] = ($win_counts[$pos][$pid] ?? 0) + 1;
            }
        }
    }

    // Build output: flat rows per position with title count
    $result = [];
    foreach ($by_pos as $pos => $years) {
        ksort($years);
        $rows = [];
        foreach ($years as $year => $pid_rows) {
            // Sort tied winners within a year by points descending
            uasort($pid_rows, fn($a, $b) => (float)$b['points'] <=> (float)$a['points']);
            foreach ($pid_rows as $pid => $row) {
                $name   = get_player_name($pid);
                $titles = $win_counts[$pos][$pid] ?? 1;
                $rows[] = [
                    'year'   => (int) $year,
                    'pid'    => $pid,
                    'first'  => $name['first'] ?? '',
                    'last'   => $name['last']  ?? '',
                    'teams'  => $row['teams'],
                    'points' => (float) $row['points'],
                    'titles' => $titles,
                ];
            }
        }
        $result[$pos] = $rows;
    }

    set_transient('pfl_scoring_titles_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}


// ============================================================
// LEADERS BY SEASON ENDPOINT
// ============================================================

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/leaders-season', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_leaders_season',
        'permission_callback' => '__return_true',
        'args'                => [
            'year' => ['required' => true, 'type' => 'integer'],
        ],
    ]);
});

function pfl_api_leaders_season(WP_REST_Request $request) {
    global $wpdb;

    $year = (int) $request->get_param('year');
    if ($year < 1991 || $year > (int) date('Y')) {
        return new WP_Error('invalid_year', 'Invalid year', ['status' => 400]);
    }

    $cache_key = "pfl_leaders_season_{$year}_v1";
    $cache = get_transient($cache_key);
    if ($cache !== false) return rest_ensure_response($cache);

    // ── Season leaders (points + games) joined to player names ────────────────
    $leaders = $wpdb->get_results($wpdb->prepare(
        "SELECT sl.playerid, sl.points, sl.games,
                p.playerFirst AS first, p.playerLast AS last
         FROM wp_season_leaders sl
         JOIN wp_players p ON p.p_id = sl.playerid
         WHERE sl.season = %d
         ORDER BY sl.points DESC",
        $year
    ), ARRAY_A);

    if (empty($leaders)) {
        return rest_ensure_response([
            'year'    => $year,
            'seasons' => array_values(the_seasons()),
            'QB' => [], 'RB' => [], 'WR' => [], 'PK' => [],
            'overall' => [], 'pvq' => [],
        ]);
    }

    // ── Pro Bowl status ────────────────────────────────────────────────────────
    $pb_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT playerid, starter FROM wp_probowlbox WHERE year = %d", $year
    ), ARRAY_A);
    $probowl = [];
    foreach ($pb_rows as $r) {
        $probowl[$r['playerid']] = (int) $r['starter'];
    }

    // ── PVQ ───────────────────────────────────────────────────────────────────
    $pvq_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT playerid, pvq FROM wp_player_pvqs WHERE year = %d", $year
    ), ARRAY_A);
    $pvq_map = [];
    foreach ($pvq_rows as $r) {
        $pvq_map[$r['playerid']] = round((float) $r['pvq'], 3);
    }

    // ── Rookie years (batch) ──────────────────────────────────────────────────
    $pids = array_column($leaders, 'playerid');
    if (!empty($pids)) {
        $placeholders = implode(',', array_fill(0, count($pids), '%s'));
        $rookie_rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT playerid, MIN(year) AS rookie_year FROM wp_rosters
                 WHERE playerid IN ($placeholders) GROUP BY playerid",
                ...$pids
            ),
            ARRAY_A
        );
    } else {
        $rookie_rows = [];
    }
    $rookie_map = [];
    foreach ($rookie_rows as $r) {
        $rookie_map[$r['playerid']] = (int) $r['rookie_year'];
    }

    // ── Build per-player rows ─────────────────────────────────────────────────
    $by_pos     = ['QB' => [], 'RB' => [], 'WR' => [], 'PK' => []];
    $all_players = [];

    foreach ($leaders as $row) {
        $pid = $row['playerid'];
        $pos = strtoupper(substr($pid, -2));
        if (!isset($by_pos[$pos])) continue;

        // Validate PID is safe (alphanumeric only) before using as table name
        if (!preg_match('/^[A-Za-z0-9]+$/', $pid)) continue;

        // Teams played this season from the player's individual table
        $team_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT team FROM `{$pid}` WHERE year = %d AND team != '' AND team IS NOT NULL",
            $year
        ), ARRAY_A);
        $teams = array_values(array_column($team_rows, 'team'));

        // Pro Bowl
        $pro = null;
        if (isset($probowl[$pid])) {
            $pb = (int) $probowl[$pid];
            if ($pb === 0)      $pro = 'starter';
            elseif ($pb === 1)  $pro = 'reserve';
            else                $pro = 'alt';
        }

        // Rookie flag (suppress for 1991 since everyone was new)
        $rookie_year = $rookie_map[$pid] ?? null;
        $is_rookie   = ($rookie_year !== null && $rookie_year === $year && $year !== 1991);

        $player = [
            'pid'    => $pid,
            'first'  => $row['first'],
            'last'   => $row['last'],
            'pos'    => $pos,
            'points' => (float) $row['points'],
            'games'  => (int)   $row['games'],
            'teams'  => $teams,
            'pro'    => $pro,
            'rookie' => $is_rookie,
            'pvq'    => $pvq_map[$pid] ?? null,
        ];

        $by_pos[$pos][]  = $player;
        $all_players[]   = $player;
    }

    // ── Sort each position by points ──────────────────────────────────────────
    foreach ($by_pos as &$players) {
        usort($players, fn($a, $b) => $b['points'] <=> $a['points']);
    }
    unset($players);

    // ── Overall top 25 by points ──────────────────────────────────────────────
    usort($all_players, fn($a, $b) => $b['points'] <=> $a['points']);
    $overall = array_slice($all_players, 0, 25);

    // ── PVQ top 25 ────────────────────────────────────────────────────────────
    $pvq_players = array_filter($all_players, fn($p) => $p['pvq'] !== null);
    usort($pvq_players, fn($a, $b) => $b['pvq'] <=> $a['pvq']);
    $pvq_top25 = array_slice(array_values($pvq_players), 0, 25);

    $result = [
        'year'    => $year,
        'seasons' => array_values(the_seasons()),
        'QB'      => $by_pos['QB'],
        'RB'      => $by_pos['RB'],
        'WR'      => $by_pos['WR'],
        'PK'      => $by_pos['PK'],
        'overall' => $overall,
        'pvq'     => $pvq_top25,
    ];

    set_transient($cache_key, $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── PROTECTIONS ENDPOINT ──────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/protections', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_protections',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_protections(WP_REST_Request $request) {
    global $wpdb;

    $cache_key = 'pfl_protections_all_v1';
    $cache = get_transient($cache_key);
    if ($cache !== false) return rest_ensure_response($cache);

    // All protections joined to team names
    $rows = $wpdb->get_results(
        "SELECT p.year, p.team, t.team AS team_name,
                p.playerFirst AS first, p.playerLast AS last,
                p.position AS pos, p.playerId AS pid
         FROM wp_protections p
         LEFT JOIN wp_teams t ON t.team_int = p.team
         ORDER BY p.year ASC, p.team ASC, p.position ASC",
        ARRAY_A
    );

    // Distinct years (descending for dropdowns)
    $years = array_values(array_unique(array_map(fn($r) => (int) $r['year'], $rows)));
    rsort($years);

    // Distinct teams with names, sorted by abbreviation
    $team_map = [];
    foreach ($rows as $r) {
        if (!isset($team_map[$r['team']])) {
            $team_map[$r['team']] = $r['team_name'] ?? $r['team'];
        }
    }
    ksort($team_map);
    $teams = [];
    foreach ($team_map as $id => $name) {
        $teams[] = ['id' => $id, 'name' => $name];
    }

    // ── Season performance for every protected player in their protected year ──
    // Batch-fetch season_leaders for all (pid, year) combos in one query, then
    // compute per-year per-position ranks.
    $pids = array_values(array_unique(array_column($rows, 'pid')));
    $years_list = array_values(array_unique(array_map(fn($r) => (int)$r['year'], $rows)));

    $season_stats = [];
    if (!empty($pids) && !empty($years_list)) {
        $pid_ph  = implode(',', array_fill(0, count($pids),       '%s'));
        $year_ph = implode(',', array_fill(0, count($years_list), '%d'));
        $sl_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT playerid, season, points, games
                 FROM wp_season_leaders
                 WHERE playerid IN ($pid_ph) AND season IN ($year_ph)",
                ...[...$pids, ...$years_list]
            ),
            ARRAY_A
        );
        foreach ($sl_rows as $sl) {
            $season_stats[$sl['playerid']][(int)$sl['season']] = [
                'pts'   => (float) $sl['points'],
                'games' => (int)   $sl['games'],
            ];
        }
    }

    // Also fetch all players at each position for each protected year so we can rank
    // Positions present in protections
    $positions = ['QB', 'RB', 'WR', 'PK'];
    $pos_ranks = []; // $pos_ranks[$year][$pid] = rank (1 = best)

    foreach ($years_list as $yr) {
        // Get all season_leaders rows for that year, group by position derived from pid
        $yr_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT sl.playerid, sl.points
                 FROM wp_season_leaders sl
                 WHERE sl.season = %d
                 ORDER BY sl.points DESC",
                $yr
            ),
            ARRAY_A
        );

        // Group by position (last 2 chars of pid)
        $by_pos = [];
        foreach ($yr_rows as $yr_row) {
            $p = strtoupper(substr($yr_row['playerid'], -2));
            if (!in_array($p, $positions)) continue;
            $by_pos[$p][] = $yr_row['playerid'];
        }

        // Assign rank (already sorted by points DESC)
        foreach ($by_pos as $p => $player_ids) {
            foreach ($player_ids as $rank_idx => $player_id) {
                $pos_ranks[$yr][$player_id] = $rank_idx + 1;
            }
        }
    }

    // Cast year to int, enrich with season stats and position rank
    $clean_rows = [];
    foreach ($rows as $r) {
        $yr  = (int) $r['year'];
        $pid = $r['pid'];
        $stats = $season_stats[$pid][$yr] ?? null;
        $rank  = $pos_ranks[$yr][$pid] ?? null;

        // Total players at this position that year (for context)
        $pos = strtoupper(substr($pid, -2));
        // Count from pos_ranks how many players at this pos this year
        $pos_total = 0;
        if (isset($pos_ranks[$yr])) {
            foreach ($pos_ranks[$yr] as $rpid => $rrank) {
                if (strtoupper(substr($rpid, -2)) === $pos) $pos_total++;
            }
        }

        $clean_rows[] = [
            'year'      => $yr,
            'team'      => $r['team'],
            'first'     => $r['first'],
            'last'      => $r['last'],
            'pos'       => $r['pos'],
            'pid'       => $pid,
            'season_pts'=> $stats ? $stats['pts']   : null,
            'games'     => $stats ? $stats['games'] : null,
            'pos_rank'  => $rank,
            'pos_total' => $pos_total ?: null,
        ];
    }

    $result = [
        'years' => $years,
        'teams' => $teams,
        'rows'  => $clean_rows,
    ];

    set_transient($cache_key, $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ─── SEASON ROSTERS ENDPOINT ────────────────────────────────────────────────

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/season-rosters', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_rosters',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_season_rosters(WP_REST_Request $request) {
    global $wpdb;

    $year      = (int) ($request->get_param('year') ?: date('Y'));
    $cache_key = "pfl_season_rosters_{$year}_v6";

    $cached = get_transient($cache_key);
    if ($cached !== false) return rest_ensure_response($cached);

    // All seasons (for dropdown)
    $seasons = array_map('intval', $wpdb->get_col(
        "SELECT DISTINCT year FROM wp_rosters WHERE team != '' ORDER BY year DESC"
    ));

    // Roster rows for this year
    $roster_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT pid, team FROM wp_rosters WHERE year = %d AND team != '' AND pid != ''",
            $year
        ),
        ARRAY_A
    );

    // Supplement with protections and drafts — players may be protected/drafted
    // but never get a wp_rosters row if they were traded before playing
    $prot_supplement = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT playerId AS pid, team FROM wp_protections WHERE year = %d AND playerId != ''",
            $year
        ),
        ARRAY_A
    );
    $draft_supplement = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT playerid AS pid, team FROM wp_drafts WHERE year = %d AND playerid != ''",
            $year
        ),
        ARRAY_A
    );
    $roster_key_set = [];
    foreach ($roster_rows as $r) {
        $roster_key_set[trim($r['pid']) . '|' . trim($r['team'])] = true;
    }
    foreach (array_merge($prot_supplement, $draft_supplement) as $row) {
        $pid  = trim($row['pid']);
        $team = trim($row['team']);
        $key  = $pid . '|' . $team;
        if ($pid && $team && !isset($roster_key_set[$key])) {
            $roster_rows[]          = ['pid' => $pid, 'team' => $team];
            $roster_key_set[$key]   = true;
        }
    }

    if (empty($roster_rows)) {
        return rest_ensure_response(['year' => $year, 'seasons' => $seasons, 'teams' => []]);
    }

    $pos_order  = ['QB' => 0, 'RB' => 1, 'WR' => 2, 'PK' => 3];
    $valid_pos  = array_keys($pos_order);
    $pids       = [];
    $team_pids  = []; // team => pos => [pid, ...]

    foreach ($roster_rows as $row) {
        $pid  = trim($row['pid']);
        $team = trim($row['team']);
        $pos  = strtoupper(substr($pid, -2));
        if (!in_array($pos, $valid_pos)) continue;
        $pids[] = $pid;
        $team_pids[$team][$pos][] = $pid;
    }
    $pids = array_values(array_unique($pids));

    // Player names — wp_players for normal PIDs, wp_rosters_nopid for 0000-prefixed
    $names     = [];
    $nopid_ids = [];
    $normal_ids = [];
    foreach ($pids as $pid) {
        if (substr($pid, 0, 4) === '0000') {
            $nopid_ids[] = $pid;
        } else {
            $normal_ids[] = $pid;
        }
    }

    if (!empty($normal_ids)) {
        $ph = implode(',', array_fill(0, count($normal_ids), '%s'));
        $player_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ($ph)",
                ...$normal_ids
            ),
            ARRAY_A
        );
        foreach ($player_rows as $p) {
            $names[$p['p_id']] = ['first' => $p['playerFirst'], 'last' => $p['playerLast'], 'nopid' => false];
        }
    }

    if (!empty($nopid_ids)) {
        $ph = implode(',', array_fill(0, count($nopid_ids), '%s'));
        $nopid_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, firstname, lastname FROM wp_rosters_nopid WHERE id IN ($ph)",
                ...$nopid_ids
            ),
            ARRAY_A
        );
        foreach ($nopid_rows as $p) {
            $names[$p['id']] = ['first' => $p['firstname'], 'last' => $p['lastname'], 'nopid' => true];
        }
    }

    // Season stats — per (pid, team) from individual player tables so we only count
    // games the player actually played FOR that specific team, not their season total.
    $stats = []; // $stats[$pid][$team] = ['pts'=>x,'games'=>y]

    // Only query tables that actually exist in the database
    $all_pids = array_values(array_unique(array_filter($pids, fn($p) => substr($p, 0, 4) !== '0000')));

    if (!empty($all_pids)) {
        $pid_ph = implode(',', array_map(fn($p) => "'" . esc_sql(strtolower($p)) . "'", $all_pids));
        $existing_tables = $wpdb->get_col(
            "SELECT TABLE_NAME FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND LOWER(TABLE_NAME) IN ($pid_ph)"
        );
        // Normalize to lowercase for case-insensitive lookup (macOS returns mixed case)
        $existing_set = array_flip(array_map('strtolower', $existing_tables));

        // Build (pid, team) combos for tables that exist
        $pid_team_combos = [];
        foreach ($roster_rows as $row) {
            $pid  = trim($row['pid']);
            $team = trim($row['team']);
            if ($pid && $team && isset($existing_set[strtolower($pid)])) {
                $pid_team_combos[] = ['pid' => $pid, 'team' => $team];
            }
        }

        if (!empty($pid_team_combos)) {
            $union_parts = [];
            foreach ($pid_team_combos as $combo) {
                // Use raw pid for table name (backtick-quoted) — pids are trusted DB values
                // Only esc_sql on the string params used in WHERE clauses
                $tbl  = $combo['pid'];
                $spid = esc_sql($combo['pid']);
                $team = esc_sql($combo['team']);
                $union_parts[] = "SELECT '$spid' AS pid, '$team' AS team,
                    SUM(points) AS pts, COUNT(*) AS games
                    FROM `$tbl`
                    WHERE year = $year AND team = '$team'";
            }
            $stat_rows = $wpdb->get_results(implode(' UNION ALL ', $union_parts), ARRAY_A);
            foreach ($stat_rows as $s) {
                if ($s['pts'] !== null || (int)$s['games'] > 0) {
                    $stats[$s['pid']][$s['team']] = [
                        'pts'   => (float) $s['pts'],
                        'games' => (int)   $s['games'],
                    ];
                }
            }
        }
    }

    // Active teams this year = teams that participated in the draft
    $active_teams = $wpdb->get_col(
        $wpdb->prepare("SELECT DISTINCT team FROM wp_drafts WHERE year = %d AND team != ''", $year)
    );

    // Filter team_pids to only active teams
    foreach (array_keys($team_pids) as $t) {
        if (!in_array($t, $active_teams)) {
            unset($team_pids[$t]);
        }
    }

    // Acquisition data: drafts, protections, trades for this year
    // wp_drafts column is 'team' (not 'acteam')
    $draft_map = []; // pid => [team => ['round'=>N,'pick'=>N], ...]
    $draft_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT playerid, team, round, roundnum FROM wp_drafts WHERE year = %d AND playerid != ''",
            $year
        ),
        ARRAY_A
    );
    foreach ($draft_rows as $d) {
        $pid  = trim($d['playerid']);
        $team = trim($d['team']);
        $draft_map[$pid][$team] = [
            'round' => (int) $d['round'],
            'pick'  => (int) $d['roundnum'],
        ];
    }

    $prot_map = []; // pid => [team, ...]
    $prot_rows = $wpdb->get_results(
        $wpdb->prepare("SELECT playerid, team FROM wp_protections WHERE year = %d", $year),
        ARRAY_A
    );
    foreach ($prot_rows as $p) {
        $prot_map[trim($p['playerid'])][] = $p['team'];
    }

    $trade_map      = []; // pid => [team they were traded TO]
    $traded_from_map = []; // pid => [to_team => from_team]
    $trade_rows = $wpdb->get_results(
        $wpdb->prepare("SELECT team1, players1, team2, players2 FROM wp_trades WHERE year = %d", $year),
        ARRAY_A
    );
    foreach ($trade_rows as $t) {
        // players1 goes TO team1 (came FROM team2), players2 goes TO team2 (came FROM team1)
        $p1 = array_filter(array_map('trim', explode(',', $t['players1'] ?? '')));
        $p2 = array_filter(array_map('trim', explode(',', $t['players2'] ?? '')));
        foreach ($p1 as $pid) {
            if ($pid) {
                $trade_map[$pid][] = $t['team1'];
                $traded_from_map[$pid][$t['team1']] = $t['team2'];
            }
        }
        foreach ($p2 as $pid) {
            if ($pid) {
                $trade_map[$pid][] = $t['team2'];
                $traded_from_map[$pid][$t['team2']] = $t['team1'];
            }
        }
    }

    // Team name lookup
    $team_names = [];
    $tn_rows = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    foreach ($tn_rows as $tn) {
        $team_names[$tn['team_int']] = $tn['team'];
    }

    // Build output
    ksort($team_pids);
    $teams_out = [];

    foreach ($team_pids as $team => $positions) {
        $pos_sections = [];
        foreach ($pos_order as $pos => $idx) {
            if (!isset($positions[$pos])) continue;
            $players = [];
            foreach ($positions[$pos] as $pid) {
                $n    = $names[$pid] ?? ['first' => '', 'last' => $pid];
                $st   = $stats[$pid][$team] ?? null;

                $drafted   = isset($draft_map[$pid][$team]);
                $protected = isset($prot_map[$pid])  && in_array($team, $prot_map[$pid]);
                $traded    = isset($trade_map[$pid]) && in_array($team, $trade_map[$pid]);

                if ($traded && $protected) {
                    $acquired = 'Traded / Protected';
                } elseif ($traded) {
                    $acquired = 'Traded';
                } elseif ($protected) {
                    $acquired = 'Protected';
                } elseif ($drafted) {
                    $acquired = 'Drafted';
                } else {
                    $acquired = 'Free Agent';
                }

                $traded_from = ($traded && isset($traded_from_map[$pid][$team]))
                    ? $traded_from_map[$pid][$team]
                    : null;

                $draft_info  = $drafted ? $draft_map[$pid][$team] : null;

                $players[] = [
                    'pid'         => $pid,
                    'first'       => $n['first'],
                    'last'        => $n['last'],
                    'pos'         => $pos,
                    'acquired'    => $acquired,
                    'traded_from' => $traded_from,
                    'draft_round' => $draft_info ? $draft_info['round'] : null,
                    'draft_pick'  => $draft_info ? $draft_info['pick']  : null,
                    'pts'         => $st ? $st['pts']   : null,
                    'games'       => $st ? $st['games'] : null,
                    'nopid'       => !empty($n['nopid']),
                ];
            }
            usort($players, function($a, $b) {
                $ag = $a['games'] ?? 0;
                $bg = $b['games'] ?? 0;
                $ap = $a['pts']   ?? 0;
                $bp = $b['pts']   ?? 0;

                // Players with games come before those without
                $a_has = $ag > 0 ? 1 : 0;
                $b_has = $bg > 0 ? 1 : 0;
                if ($a_has !== $b_has) return $b_has - $a_has;

                if ($ag !== $bg) return $bg - $ag;   // more games first
                if ($ap !== $bp) return $bp <=> $ap; // more points first

                // No games: drafted players first (in pick order), then alphabetical
                if ($a_has === 0) {
                    $a_pick = $a['draft_round'] !== null ? ($a['draft_round'] * 100 + $a['draft_pick']) : PHP_INT_MAX;
                    $b_pick = $b['draft_round'] !== null ? ($b['draft_round'] * 100 + $b['draft_pick']) : PHP_INT_MAX;
                    if ($a_pick !== $b_pick) return $a_pick - $b_pick;
                    return strcmp($a['last'], $b['last']);
                }

                return 0;
            });
            $pos_sections[$pos] = $players;
        }
        $teams_out[] = [
            'team'     => $team,
            'teamName' => $team_names[$team] ?? $team,
            'players'  => $pos_sections,
        ];
    }

    $result = ['year' => $year, 'seasons' => $seasons, 'teams' => $teams_out];
    set_transient($cache_key, $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

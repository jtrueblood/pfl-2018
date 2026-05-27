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
if ( PHP_SAPI !== 'cli' ) session_start();
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


// REST endpoint: GET /pfl/v1/mfl-weekly-gamelogs?year=N&week=N
// Returns the cached MFL `weeklyResults` payload for a completed season-week,
// from the mfl-weekly-gamelogs/{year}{week}.json sidecar files. Used by the
// pfl-next fetcher as a fallback when MFL refuses APIKEY-only auth for past
// seasons (schedule + weeklyResults endpoints get locked once the site
// rolls over to a new season).
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/mfl-weekly-gamelogs', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_mfl_weekly_gamelogs',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_mfl_weekly_gamelogs(WP_REST_Request $request) {
    $year = (int) $request->get_param('year');
    $week = (int) $request->get_param('week');
    if ($year <= 0 || $week <= 0) {
        return new WP_Error('missing_params', 'year and week query params are required', ['status' => 400]);
    }
    $file = get_stylesheet_directory() . '/mfl-weekly-gamelogs/' . $year . $week . '.json';
    if (!file_exists($file)) {
        return new WP_Error('not_found', "No cached gamelog for year={$year} week={$week}", ['status' => 404]);
    }
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) {
        return new WP_Error('parse_error', 'Cached gamelog is not valid JSON', ['status' => 500]);
    }
    return rest_ensure_response($data);
}


// REST endpoint: GET /pfl/v1/bye-weeks?year=N
// Returns the NFL bye-week schedule for the given season, as captured in the
// nfl-bye-weeks/bye_weeks_<year>.json sidecar files. Used by pfl-next's
// Gameday page to flag rostered players whose team is on bye this week.
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/bye-weeks', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_bye_weeks',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_bye_weeks(WP_REST_Request $request) {
    $year = (int) $request->get_param('year');
    if ($year <= 0) {
        return new WP_Error('missing_year', 'year query param required', ['status' => 400]);
    }
    $json_file = get_stylesheet_directory() . '/nfl-bye-weeks/bye_weeks_' . $year . '.json';
    if (!file_exists($json_file)) {
        return rest_ensure_response([
            'season'    => $year,
            'bye_weeks' => [],
        ]);
    }
    $bye_data = json_decode(file_get_contents($json_file), true);
    if (!is_array($bye_data) || !isset($bye_data['bye_weeks'])) {
        return rest_ensure_response([
            'season'    => $year,
            'bye_weeks' => [],
        ]);
    }
    return rest_ensure_response($bye_data);
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



// Gets the longest consecutive games started streak for a player.
// Rules: BYE weeks don't break the streak. DNP (benched) weeks do. Any uncovered
// week gap within a season breaks it. Season-end gaps don't break it, but missing
// weeks at the START of a new season do.
function get_player_game_streak($pid) {
    global $wpdb;

    $weekly = get_player_data($pid);
    if (empty($weekly)) return 0;

    $pos = strtoupper(substr($pid, -2));

    // Build played-game rows
    $games = [];
    foreach ($weekly as $row) {
        $games[] = ['year' => (int)$row['year'], 'week' => (int)$row['week'], 'bye' => false, 'bench' => false];
    }

    // Inject NFL bye weeks
    $year_teams = [];
    foreach ($weekly as $row) {
        $yr = (int)$row['year'];
        $nt = $row['nflteam'] ?? '';
        if ($nt) $year_teams[$yr][] = $nt;
    }
    $theme_dir = get_stylesheet_directory();
    foreach ($year_teams as $yr => $team_list) {
        $counts = array_count_values($team_list);
        arsort($counts);
        $primary = array_key_first($counts);
        if (!$primary) continue;
        $bye_file = $theme_dir . '/nfl-bye-weeks/bye_weeks_' . $yr . '.json';
        if (!file_exists($bye_file)) continue;
        $bye_data = json_decode(file_get_contents($bye_file), true);
        if (empty($bye_data['bye_weeks'])) continue;
        $bye_week_num = null;
        foreach ($bye_data['bye_weeks'] as $bw) {
            if (in_array(strtoupper($primary), array_map('strtoupper', $bw['teams']))) {
                $bye_week_num = (int)$bw['week'];
                break;
            }
        }
        if (!$bye_week_num) continue;
        foreach ($games as $g) {
            if ($g['year'] === $yr && $g['week'] === $bye_week_num) { continue 2; }
        }
        $games[] = ['year' => $yr, 'week' => $bye_week_num, 'bye' => true, 'bench' => false];
    }

    // Inject benched/DNP weeks (2011+, single-team years only)
    if (in_array($pos, ['QB', 'RB', 'WR', 'PK'])) {
        $pos1 = $pos . '1';
        $pos2 = $pos . '2';
        $roster_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT year, team FROM wp_rosters WHERE pid = %s AND year >= 2011", $pid
        ), ARRAY_A);
        $roster_by_year = [];
        foreach ($roster_rows as $r) {
            $roster_by_year[(int)$r['year']][] = $r['team'];
        }
        $existing = [];
        foreach ($games as $g) { $existing[$g['year'] . '_' . $g['week']] = true; }
        foreach ($roster_by_year as $yr => $yr_teams) {
            if (count($yr_teams) > 1) continue;
            $pfl_team = $yr_teams[0];
            $team_table = 'wp_team_' . esc_sql($pfl_team);
            $team_weeks = $wpdb->get_results($wpdb->prepare(
                "SELECT week, `{$pos1}`, `{$pos2}` FROM `{$team_table}` WHERE season = %d ORDER BY week", $yr
            ), ARRAY_A);
            foreach ($team_weeks as $tw) {
                $wk = (int)$tw['week'];
                if ($tw[$pos1] === $pid || $tw[$pos2] === $pid) continue;
                if (isset($existing[$yr . '_' . $wk])) continue;
                $games[] = ['year' => $yr, 'week' => $wk, 'bye' => false, 'bench' => true];
                $existing[$yr . '_' . $wk] = true;
            }
        }
    }

    usort($games, fn($a, $b) => $a['year'] !== $b['year'] ? $a['year'] - $b['year'] : $a['week'] - $b['week']);

    // Build covered set for gap detection
    $covered = [];
    foreach ($games as $g) { $covered[$g['year'] . '_' . $g['week']] = true; }

    $next = 1;
    $max_streak = 0;
    $prev_year = null;
    $prev_week = null;

    foreach ($games as $g) {
        if ($g['bye']) {
            continue; // BYE: don't advance prev pointers
        }
        if ($g['bench']) {
            $next = 1;
            $prev_year = null;
            $prev_week = null;
            continue;
        }
        if ($prev_year !== null) {
            if ($prev_year === $g['year']) {
                // Same season: gap between last game and this one?
                for ($w = $prev_week + 1; $w < $g['week']; $w++) {
                    if (!isset($covered[$g['year'] . '_' . $w])) { $next = 1; break; }
                }
            } else {
                // New season: gap at the start of this season?
                for ($w = 1; $w < $g['week']; $w++) {
                    if (!isset($covered[$g['year'] . '_' . $w])) { $next = 1; break; }
                }
            }
        }
        $val = $next++;
        if ($val > $max_streak) $max_streak = $val;
        $prev_year = $g['year'];
        $prev_week = $g['week'];
    }

    return $max_streak;
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

// ── NFL active status check ───────────────────────────────────────────────────
// Returns true if the player is still active in the NFL.
// Primary: queries MFL players export for the current year (cached 1 week).
// Fallback: player's last PFL season within 3 calendar years of today.
// $allow_fetch = true  → player profile pages (one player, ok to call MFL)
// $allow_fetch = false → bulk pages like leaders (use cache only; fall back to year window)
// $retireyear  → if set, player is definitively retired (no API call needed)
function pfl_player_is_nfl_active( $mflid, $lastyear, $allow_fetch = true, $retireyear = null ) {
    if ( !empty( $retireyear ) ) return false;

    $fallback = (int) $lastyear >= (int) date('Y') - 3;

    if ( empty( $mflid ) ) {
        return $fallback;
    }

    $transient_key = 'pfl_nfl_active_' . $mflid;
    $cached = get_transient( $transient_key );

    if ( $cached !== false ) {
        return $cached === '1';
    }

    if ( ! $allow_fetch ) {
        return $fallback;
    }

    $year = (int) date('Y');
    $url  = "https://api.myfantasyleague.com/{$year}/export?TYPE=players&PLAYERS={$mflid}&DETAILS=1&JSON=1";
    $resp = wp_remote_get( $url, [ 'timeout' => 5 ] );

    if ( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) !== 200 ) {
        // API unavailable — don't cache, use fallback
        return $fallback;
    }

    $body = json_decode( wp_remote_retrieve_body( $resp ), true );
    $player_data = $body['players']['player'] ?? null;

    // MFL returns a single object (not array) when one PLAYERS id is given
    if ( isset( $player_data['id'] ) ) {
        $player_data = [ $player_data ];
    }

    $is_active = false;
    if ( is_array( $player_data ) ) {
        foreach ( $player_data as $p ) {
            if ( (string) ( $p['id'] ?? '' ) === (string) $mflid ) {
                // Any team assignment (including "FA") means still in the NFL
                $team   = strtoupper( trim( $p['team']   ?? '' ) );
                $status = strtoupper( trim( $p['status'] ?? '' ) );
                $is_active = ! empty( $team ) && $status !== 'R';
                break;
            }
        }
    }

    set_transient( $transient_key, $is_active ? '1' : '0', WEEK_IN_SECONDS );

    return $is_active;
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
        2025 => 38954,
        2026 => 38954
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

    $destination_folder = get_stylesheet_directory() . '/mfl-weekly-rosters';
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

    // "Most recent season" defaults for the season-scoped links. Pulled from
    // date() so admin rebuilds during a new season auto-track without code
    // edits. After the season ends you can bump these by re-running the
    // builder with ?build_sidebar_menu=1.
    $default_year      = (int) date('Y');
    $default_year_str  = (string) $default_year;
    $default_week_str  = $default_year_str . '01';

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
                'Leaders By Season' => '/leaders-season/?id=' . $default_year_str,
                'Supercards' => '/supercards/'
            )
        ),
        'Seasons' => array(
            'url' => '#',
            'children' => array(
                'Seasons' => '/seasons/?id=' . $default_year_str,
                'Drafts by Year' => '/drafts/?id=' . $default_year_str,
                'Standings By Year' => '/standings/?id=' . $default_year_str,
                'Playoff Brackets' => '/playoff-brackets',
                'Team Rosters' => '/team-rosters/?season=' . $default_year_str
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
                'Weekly Results' => '/results?Y=' . $default_year_str . '&W=01',
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
                'Kicker Drafts' => '/kicker-draft/?draft_year=' . $default_year_str . '/',
                'Scorigami' => '/scorigami/?W=' . $default_week_str,
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

        $all_team_ints = $wpdb->get_col(
            "SELECT DISTINCT team FROM `{$table}` WHERE team != '' AND team IS NOT NULL ORDER BY team"
        );
        $other_teams = array_values(array_filter($all_team_ints, fn($t) => $t !== $primary_team_int));

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

        $player_meta = $wpdb->get_row($wpdb->prepare(
            "SELECT mflid, retireyear FROM wp_players WHERE p_id = %s", $pid
        ), ARRAY_A);
        $retireyear = !empty($player_meta['retireyear']) ? (int) $player_meta['retireyear'] : null;
        $is_active  = pfl_player_is_nfl_active($player_meta['mflid'] ?? '', $last_yr, false, $player_meta['retireyear'] ?? null);

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
            'otherTeams'    => $other_teams,
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
            'retireyear'    => $retireyear,
            'active'        => $is_active,
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
                p.playerFirst AS first, p.playerLast AS last,
                p.retireyear, p.mflid
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

        $retireyear = !empty($row['retireyear']) ? (int) $row['retireyear'] : null;
        $is_active  = pfl_player_is_nfl_active($row['mflid'] ?? '', $last_yr, false, $row['retireyear'] ?? null);

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
            'retireyear'    => $retireyear,
            'active'        => $is_active,
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
    register_rest_route('pfl/v1', '/champions', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_champions',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_champions() {
    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT c.year, c.winTeam, t.team as teamName
         FROM wp_champions c
         JOIN wp_teams t ON t.team_int = c.winTeam
         ORDER BY c.year ASC",
        ARRAY_A
    );

    $theme_uri = get_stylesheet_directory_uri();
    $result    = [];

    foreach ($rows as $r) {
        $year     = (int) $r['year'];
        $team     = $r['winTeam'];
        $helm_num = pfl_get_helmet_num($team, $year);
        $result[] = [
            'year'      => $year,
            'team'      => $team,
            'teamName'  => $r['teamName'],
            'helmetUrl' => $theme_uri . '/img/helmets/final-renders/' . $team . '/helmet-' . $team . '-' . $helm_num . '-front.png',
        ];
    }

    return rest_ensure_response($result);
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
        "SELECT year, award, playerFirst, playerLast, team, pid, owner
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
            'ooty'       => $ooty ? [
                'team'  => $ooty['team'],
                'owner' => $ooty['owner'] ?? '',
            ] : null,
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
             d.roundnum,
             d.picknum,
             d.pickord,
             d.playerid,
             d.playerfirst,
             d.playerlast,
             d.pos         AS position,
             d.team,
             t.team        AS team_name,
             pv.valuescore,
             d.tradeid
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

    // Build traded-pick lookup from wp_trades for this year
    // Picks in wp_trades.picks1/picks2 are formatted "YYYY.RR.PP" (zero-padded)
    $traded_picks = []; // 'YYYY.RR.PP' => trade_id
    $like_start = $year . '.%';
    $like_mid   = '%,' . $year . '.%';
    $trade_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, picks1, picks2 FROM wp_trades WHERE picks1 LIKE %s OR picks1 LIKE %s OR picks2 LIKE %s OR picks2 LIKE %s",
        $like_start, $like_mid, $like_start, $like_mid
    ), ARRAY_A);
    foreach ($trade_rows as $tr) {
        foreach (['picks1', 'picks2'] as $col) {
            if (empty($tr[$col])) continue;
            foreach (array_map('trim', explode(',', $tr[$col])) as $pk) {
                if (!empty($pk) && strpos($pk, $year . '.') === 0) {
                    $traded_picks[$pk] = (int) $tr['id'];
                }
            }
        }
    }

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

        // Check if this pick was acquired via trade (cross-reference wp_trades)
        // Key format: YYYY.RR.PP where RR=round, PP=within-round pick number (roundnum)
        $pick_key = $year . '.' . $p['round'] . '.' . $p['roundnum'];
        $trade_id = $traded_picks[$pick_key] ?? null;

        $out[] = [
            'round'      => (int) ltrim($p['round'], '0') ?: 1,
            'pick'       => (int) ltrim($p['picknum'], '0') ?: 1,
            'pid'        => $pid ?: null,
            'first'      => $p['playerfirst'],
            'last'       => $p['playerlast'],
            'position'   => $p['position'],
            'team'       => $p['team'],
            'teamName'   => $p['team_name'] ?? $p['team'],
            'origTeam'   => $p['pickord'],
            'img'        => $img,
            'seasonPts'  => $season_pts,
            'valueScore' => $p['valuescore'] !== null ? round((float) $p['valuescore'], 2) : null,
            'helmetNum'  => $helmet_by_team[$p['team']] ?? 1,
            'traded'     => $trade_id !== null,
            'tradeId'    => $trade_id,
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

    $all_team_ints = $wpdb->get_col(
        "SELECT DISTINCT team FROM `{$table}` WHERE team != '' AND team IS NOT NULL ORDER BY team"
    );
    $other_teams = array_values(array_filter($all_team_ints, fn($t) => $t !== $primary_team_int));

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

    // Ring of Honor
    $roh_team_int = $wpdb->get_var($wpdb->prepare(
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
    $roh_helmet_url = null;
    if ($roh_team_int) {
        $theme_uri  = get_stylesheet_directory_uri();
        $helm_num   = pfl_get_helmet_num($roh_team_int, (int) date('Y'));
        $roh_helmet_url = $theme_uri . '/img/helmets/final-renders/' . $roh_team_int . '/helmet-' . $roh_team_int . '-' . $helm_num . '-front.png';
    }

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
        'otherTeams'    => $other_teams,
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
        'rohHelmetUrl'  => $roh_helmet_url,
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
            p.mflid        AS mflid,
            p.retireyear   AS retireyear,
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
            'retireyear'       => !empty($row['retireyear']) ? (int) $row['retireyear'] : null,
            'active'           => pfl_player_is_nfl_active( $row['mflid'] ?? '', (int) $row['lastyear'], false, $row['retireyear'] ?? null ),
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

    // All drafts (ordered by year)
    $all_drafts_raw = $wpdb->get_results($wpdb->prepare(
        "SELECT d.year AS season, d.round, d.picknum AS pick, COALESCE(t.team, d.team) AS teamName
         FROM wp_drafts d
         LEFT JOIN wp_teams t ON t.team_int = d.team
         WHERE d.playerid = %s
         ORDER BY CAST(d.year AS UNSIGNED) ASC",
        $pid
    ), ARRAY_A);
    $all_drafts = array_values(array_map(fn($d) => [
        'season'   => (int) $d['season'],
        'round'    => ltrim($d['round'], '0') ?: '1',
        'pick'     => ltrim($d['pick'], '0') ?: '1',
        'teamName' => $d['teamName'],
    ], $all_drafts_raw));
    $first_draft = !empty($all_drafts_raw) ? $all_drafts_raw[0] : null;

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

    // Active status — retireyear is definitive; fall back to MFL API / 3-year heuristic
    $retireyear = !empty($player['retireyear']) ? (int) $player['retireyear'] : null;
    $career_lastyear = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT lastyear FROM wp_allleaders WHERE pid = %s LIMIT 1", $pid
    ));
    $is_active = pfl_player_is_nfl_active( $player['mflid'] ?? '', $career_lastyear, true, $retireyear );

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
        'retireyear' => $retireyear,
        'hofYear'    => $hof_year ? (int) $hof_year : null,
        'rohTeam'    => $roh_team_name,
        'firstDraft' => $first_draft ? [
            'season'   => (int) $first_draft['season'],
            'round'    => ltrim($first_draft['round'], '0') ?: '1',
            'pick'     => ltrim($first_draft['pick'], '0') ?: '1',
            'teamName' => $first_draft['teamName'],
        ] : null,
        'allDrafts'  => $all_drafts,
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

    // Roster entries — used to fill in team for DNP years and extend the year range
    $roster_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT r.year, r.team, COALESCE(t.team, r.team) AS team_name
         FROM wp_rosters r
         LEFT JOIN wp_teams t ON t.team_int = r.team
         WHERE r.pid = %s
         ORDER BY CAST(r.year AS UNSIGNED) ASC",
        $pid
    ), ARRAY_A);
    $roster_by_year = [];
    foreach ($roster_rows as $r) {
        $y = (int) $r['year'];
        if (!isset($roster_by_year[$y])) {
            $roster_by_year[$y] = ['team_int' => $r['team'], 'team_name' => $r['team_name']];
        }
    }
    if (!empty($roster_by_year)) {
        $first_year = min($first_year, min(array_keys($roster_by_year)));
        $last_year  = max($last_year,  max(array_keys($roster_by_year)));
    }

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
            $roster = $roster_by_year[$year] ?? null;
            $seasons[] = [
                'year'      => $year,
                'team'      => $roster ? $roster['team_name'] : null,
                'team_abbr' => $roster ? $roster['team_int'] : null,
                'points'    => null,
                'games'     => null,
                'ppg'       => null,
                'high'      => null,
                'rank'      => null,
                'pvq'       => $pvqs[$year] ?? null,
                'protected' => isset($protected_years[$year]),
                'dnp'       => $roster !== null,
            ];
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
            'dnp'       => false,
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
    global $wpdb;
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

    // Inject bye week rows
    $year_teams = [];
    foreach ($weekly as $row) {
        $yr = (int) $row['year'];
        $nt = $row['nflteam'] ?? '';
        if ($nt) $year_teams[$yr][] = $nt;
    }
    $theme_dir = get_stylesheet_directory();
    foreach ($year_teams as $yr => $team_list) {
        $counts = array_count_values($team_list);
        arsort($counts);
        $primary = array_key_first($counts);
        if (!$primary) continue;
        $bye_file = $theme_dir . '/nfl-bye-weeks/bye_weeks_' . $yr . '.json';
        if (!file_exists($bye_file)) continue;
        $bye_data = json_decode(file_get_contents($bye_file), true);
        if (empty($bye_data['bye_weeks'])) continue;
        $bye_week_num = null;
        foreach ($bye_data['bye_weeks'] as $bw) {
            if (in_array(strtoupper($primary), array_map('strtoupper', $bw['teams']))) {
                $bye_week_num = (int) $bw['week'];
                break;
            }
        }
        if (!$bye_week_num) continue;
        foreach ($games as $g) {
            if ($g['year'] === $yr && $g['week'] === $bye_week_num) { continue 2; }
        }
        $games[] = [
            'year' => $yr, 'week' => $bye_week_num,
            'team' => '', 'points' => 0, 'versus' => '',
            'result' => '', 'location' => '', 'home' => false, 'ot' => false,
            'bye' => true, 'nflteam' => $primary,
        ];
    }
    // Inject benched weeks (2011+ only, single-team years)
    $pos = strtoupper(substr($pid, -2));
    if (in_array($pos, ['QB', 'RB', 'WR', 'PK'])) {
        $pos1 = $pos . '1';
        $pos2 = $pos . '2';
        $roster_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT year, team FROM wp_rosters WHERE pid = %s AND year >= 2011",
            $pid
        ), ARRAY_A);
        $roster_by_year = [];
        foreach ($roster_rows as $r) {
            $roster_by_year[(int)$r['year']][] = $r['team'];
        }
        $existing = [];
        foreach ($games as $g) {
            $existing[$g['year'] . '_' . $g['week']] = true;
        }
        foreach ($roster_by_year as $yr => $yr_teams) {
            if (count($yr_teams) > 1) continue; // skip traded years
            $pfl_team = $yr_teams[0];
            $team_table = 'wp_team_' . esc_sql($pfl_team);
            $team_weeks = $wpdb->get_results($wpdb->prepare(
                "SELECT week, `{$pos1}`, `{$pos2}` FROM `{$team_table}` WHERE season = %d ORDER BY week",
                $yr
            ), ARRAY_A);
            foreach ($team_weeks as $tw) {
                $wk = (int)$tw['week'];
                if ($tw[$pos1] === $pid || $tw[$pos2] === $pid) continue;
                if (isset($existing[$yr . '_' . $wk])) continue;
                $games[] = [
                    'year' => $yr, 'week' => $wk,
                    'team' => $pfl_team, 'points' => 0, 'versus' => '',
                    'result' => '', 'location' => '', 'home' => false, 'ot' => false,
                    'bench' => true,
                ];
                $existing[$yr . '_' . $wk] = true;
            }
        }
    }

    usort($games, fn($a, $b) => $a['year'] !== $b['year'] ? $a['year'] - $b['year'] : $a['week'] - $b['week']);

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

    register_rest_route('pfl/v1', '/transactions', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_transactions',
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
    global $wpdb;
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

    // Inject bye week rows from local JSON files
    $year_teams = [];
    foreach ($weekly as $row) {
        $yr = (int) $row['year'];
        $nt = $row['nflteam'] ?? '';
        if ($nt) $year_teams[$yr][] = $nt;
    }
    $theme_dir = get_stylesheet_directory();
    foreach ($year_teams as $yr => $team_list) {
        $counts = array_count_values($team_list);
        arsort($counts);
        $primary = array_key_first($counts);
        if (!$primary) continue;
        $bye_file = $theme_dir . '/nfl-bye-weeks/bye_weeks_' . $yr . '.json';
        if (!file_exists($bye_file)) continue;
        $bye_data = json_decode(file_get_contents($bye_file), true);
        if (empty($bye_data['bye_weeks'])) continue;
        $bye_week_num = null;
        foreach ($bye_data['bye_weeks'] as $bw) {
            if (in_array(strtoupper($primary), array_map('strtoupper', $bw['teams']))) {
                $bye_week_num = (int) $bw['week'];
                break;
            }
        }
        if (!$bye_week_num) continue;
        // Only insert if no row already exists for this year+week
        foreach ($rows as $r) {
            if ($r['year'] === $yr && $r['week'] === $bye_week_num) { continue 2; }
        }
        $bye_entry = [
            'year' => $yr, 'week' => $bye_week_num,
            'date' => '', 'game' => '', 'bye' => true, 'nflteam' => $primary,
            'twopt' => 0, 'nflscore' => 0, 'points' => 0, 'scorediff' => 0, 'position' => $position,
        ];
        if ($position === 'PK') {
            $bye_entry += ['xpm' => 0, 'xpa' => 0, 'fgm' => 0, 'fga' => 0];
        } else {
            $bye_entry += ['pass_yds' => 0, 'pass_td' => 0, 'pass_int' => 0,
                           'rush_yds' => 0, 'rush_td' => 0, 'rec_yds' => 0, 'rec_td' => 0];
        }
        $rows[] = $bye_entry;
    }

    // Inject benched weeks (2011+ only, single-team years)
    $pos_bench = strtoupper(substr($pid, -2));
    if (in_array($pos_bench, ['QB', 'RB', 'WR', 'PK'])) {
        $pos1 = $pos_bench . '1';
        $pos2 = $pos_bench . '2';
        $roster_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT year, team FROM wp_rosters WHERE pid = %s AND year >= 2011",
            $pid
        ), ARRAY_A);
        $roster_by_year = [];
        foreach ($roster_rows as $r) {
            $roster_by_year[(int)$r['year']][] = $r['team'];
        }
        $existing = [];
        foreach ($rows as $r2) {
            $existing[$r2['year'] . '_' . $r2['week']] = true;
        }
        foreach ($roster_by_year as $yr => $yr_teams) {
            if (count($yr_teams) > 1) continue;
            $pfl_team = $yr_teams[0];
            $team_table = 'wp_team_' . esc_sql($pfl_team);
            $team_weeks = $wpdb->get_results($wpdb->prepare(
                "SELECT week, `{$pos1}`, `{$pos2}` FROM `{$team_table}` WHERE season = %d ORDER BY week",
                $yr
            ), ARRAY_A);
            foreach ($team_weeks as $tw) {
                $wk = (int)$tw['week'];
                if ($tw[$pos1] === $pid || $tw[$pos2] === $pid) continue;
                if (isset($existing[$yr . '_' . $wk])) continue;
                $bench_entry = [
                    'year' => $yr, 'week' => $wk,
                    'date' => '', 'game' => '', 'bench' => true, 'nflteam' => null,
                    'twopt' => 0, 'nflscore' => 0, 'points' => 0, 'scorediff' => 0, 'position' => $position,
                ];
                if ($position === 'PK') {
                    $bench_entry += ['xpm' => 0, 'xpa' => 0, 'fgm' => 0, 'fga' => 0];
                } else {
                    $bench_entry += ['pass_yds' => 0, 'pass_td' => 0, 'pass_int' => 0,
                                     'rush_yds' => 0, 'rush_td' => 0, 'rec_yds' => 0, 'rec_td' => 0];
                }
                $rows[] = $bench_entry;
                $existing[$yr . '_' . $wk] = true;
            }
        }
    }

    usort($rows, fn($a, $b) => $a['year'] !== $b['year'] ? $a['year'] - $b['year'] : $a['week'] - $b['week']);

    return rest_ensure_response($rows);
}


function pfl_api_transactions(WP_REST_Request $request) {
    $year = (int) sanitize_text_field($request->get_param('year'));
    if (!$year) $year = (int) date('Y');

    $teambyid   = teams_for_mfl_history();
    $convertids = playerid_mfl_to_pfl();

    if (!isset($teambyid[$year])) {
        return new WP_Error('missing_year', "No team mapping for year $year", ['status' => 400]);
    }
    $teams = $teambyid[$year]; // mfl_franchise_id => team_int

    // Load player names keyed by PFL pid
    global $wpdb;
    $player_rows = $wpdb->get_results(
        "SELECT p_id, playerFirst, playerLast, position FROM wp_players", ARRAY_A
    );
    $player_names = [];
    foreach ($player_rows as $p) {
        $player_names[$p['p_id']] = trim($p['playerFirst'] . ' ' . $p['playerLast']);
    }

    // Load team full names
    $team_name_rows = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    $team_names = [];
    foreach ($team_name_rows as $t) {
        $team_names[$t['team_int']] = $t['team'];
    }

    // Helper: resolve MFL player ID to [pid, name]
    $resolve_player = function(string $mfl_id) use ($convertids, $player_names): array {
        $pid  = $convertids[$mfl_id] ?? null;
        $name = $pid ? ($player_names[$pid] ?? $mfl_id) : $mfl_id;
        return ['pid' => $pid, 'name' => $name, 'mflid' => $mfl_id];
    };

    // Helper: parse pick strings like FP_0007_2025_4 or DP_1_3
    $resolve_asset = function(string $asset) use ($teams, $team_names): array {
        if (str_starts_with($asset, 'FP_')) {
            // FP_FRANCHISE_YEAR_ROUND
            $parts  = explode('_', $asset);
            $fran   = $parts[1] ?? '';
            $yr     = $parts[2] ?? '';
            $round  = $parts[3] ?? '';
            $team   = $teams[$fran] ?? $fran;
            $tname  = $team_names[$team] ?? $team;
            return ['type' => 'pick', 'name' => "$yr Rd $round Pick ($tname)", 'asset' => $asset];
        }
        if (str_starts_with($asset, 'DP_')) {
            $parts = explode('_', $asset);
            $round = $parts[1] ?? '';
            return ['type' => 'pick', 'name' => "Draft Pick (Rd $round)", 'asset' => $asset];
        }
        return [];
    };

    $raw = get_mfl_transactions($year);
    if (!$raw || empty($raw->transactions->transaction)) {
        return rest_ensure_response([]);
    }

    $skip_types = ['WAIVER_REQUEST', 'TRADE_PROPOSAL', 'TRADE_OFFER_EXPIRED'];
    $result     = [];

    foreach ($raw->transactions->transaction as $t) {
        $type = $t->type ?? '';
        if (in_array($type, $skip_types)) continue;

        $ts        = (int) ($t->timestamp ?? 0);
        $date_fmt  = $ts ? date('Y-m-d', $ts) : null;
        $time_fmt  = $ts ? date('H:i', $ts)   : null;
        $franchise = $t->franchise ?? '';
        $team      = $teams[$franchise] ?? $franchise;
        $by_commish = !empty($t->by_commish);

        if ($type === 'TRADE') {
            $f2    = $t->franchise2 ?? '';
            $team2 = $teams[$f2] ?? $f2;

            $gave1 = array_filter(explode(',', $t->franchise1_gave_up ?? ''));
            $gave2 = array_filter(explode(',', $t->franchise2_gave_up ?? ''));

            $side1 = [];
            foreach ($gave1 as $asset) {
                $asset = trim($asset);
                if (!$asset) continue;
                if (str_starts_with($asset, 'FP_') || str_starts_with($asset, 'DP_')) {
                    $side1[] = $resolve_asset($asset);
                } else {
                    $p = $resolve_player($asset);
                    $side1[] = array_merge($p, ['type' => 'player']);
                }
            }
            $side2 = [];
            foreach ($gave2 as $asset) {
                $asset = trim($asset);
                if (!$asset) continue;
                if (str_starts_with($asset, 'FP_') || str_starts_with($asset, 'DP_')) {
                    $side2[] = $resolve_asset($asset);
                } else {
                    $p = $resolve_player($asset);
                    $side2[] = array_merge($p, ['type' => 'player']);
                }
            }

            $result[] = [
                'type'       => 'TRADE',
                'timestamp'  => $ts,
                'date'       => $date_fmt,
                'time'       => $time_fmt,
                'year'       => $year,
                'team1'      => $team,
                'team1Name'  => $team_names[$team] ?? $team,
                'team2'      => $team2,
                'team2Name'  => $team_names[$team2] ?? $team2,
                'team1_gave' => $side1,
                'team2_gave' => $side2,
                'by_commish' => $by_commish,
            ];
        } elseif ($type === 'IR') {
            $activated   = array_values(array_filter(explode(',', $t->activated   ?? '')));
            $deactivated = array_values(array_filter(explode(',', $t->deactivated ?? '')));
            if (empty($activated) && empty($deactivated)) continue;
            $result[] = [
                'type'        => 'IR',
                'timestamp'   => $ts,
                'date'        => $date_fmt,
                'time'        => $time_fmt,
                'year'        => $year,
                'team'        => $team,
                'teamName'    => $team_names[$team] ?? $team,
                'activated'   => array_map(fn($id) => $resolve_player(trim($id)), $activated),
                'deactivated' => array_map(fn($id) => $resolve_player(trim($id)), $deactivated),
                'by_commish'  => $by_commish,
            ];
        } elseif (in_array($type, ['WAIVER', 'FREE_AGENT'])) {
            // Parse added/dropped from dedicated fields or transaction string
            if ($type === 'FREE_AGENT' && !empty($t->transaction)) {
                $parts   = explode('|', $t->transaction);
                $dropped = array_values(array_filter(explode(',', $parts[0] ?? '')));
                $added   = array_values(array_filter(explode(',', $parts[1] ?? '')));
            } else {
                $added   = array_values(array_filter(explode(',', $t->added   ?? '')));
                $dropped = array_values(array_filter(explode(',', $t->dropped ?? '')));
            }
            if (empty($added) && empty($dropped)) continue;
            $result[] = [
                'type'       => $type,
                'timestamp'  => $ts,
                'date'       => $date_fmt,
                'time'       => $time_fmt,
                'year'       => $year,
                'team'       => $team,
                'teamName'   => $team_names[$team] ?? $team,
                'added'      => array_map(fn($id) => $resolve_player(trim($id)), $added),
                'dropped'    => array_map(fn($id) => $resolve_player(trim($id)), $dropped),
                'by_commish' => $by_commish,
            ];
        }
    }

    // Sort newest first
    usort($result, fn($a, $b) => $b['timestamp'] - $a['timestamp']);

    return rest_ensure_response($result);
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
    $career_event_cutoff = (int) date('Y'); // skip events for seasons that haven't happened yet
    foreach ($released_events as $released) {
        $yr = (int) $released['year'];
        if ($yr > $career_event_cutoff) continue;
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
            if ($yr > $career_event_cutoff) continue;
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

    // ── Consistency check: each PROTECTED row ────────────────────────────────
    // A player can only be protected by team T in year Y if they were either:
    //   (a) on team T's roster in year Y-1, OR
    //   (b) traded TO team T in year Y or Y-1
    // If neither is true, inject a DATA_GAP note at year Y-1.
    $flag_rows = [];
    foreach ($rows as $row) {
        if ($row['type'] !== 'PROTECTED') continue;

        $prot_team = $row['team'];
        $prot_year = (int) $row['year'];
        $prev_year = $prot_year - 1;

        // (a) On roster the prior year?
        $on_prev_roster = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM wp_rosters WHERE pid = %s AND team = %s AND year = %d",
            $pid, $prot_team, $prev_year
        ));
        if ($on_prev_roster) continue;

        // (b) Traded TO this team in year Y or Y-1?
        $like = '%' . $wpdb->esc_like($pid) . '%';
        $traded_to = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM wp_trades
             WHERE year IN (%d, %d)
               AND ((team1 = %s AND players1 LIKE %s)
                 OR (team2 = %s AND players2 LIKE %s))",
            $prot_year, $prev_year,
            $prot_team, $like,
            $prot_team, $like
        ));
        if ($traded_to) continue;

        // Neither found — flag it
        $flag_rows[] = [
            'type'   => 'DATA_GAP',
            'year'   => $prev_year,
            'date'   => '-',
            'time'   => '-',
            'team'   => $prot_team,
            'action' => "No {$prev_year} roster or trade record found before {$prot_year} protection by {$prot_team}",
        ];
    }

    if (!empty($flag_rows)) {
        $rows = array_merge($rows, $flag_rows);
    }

    // ── Estimated RELEASED: after each season, if no protection or trade-away ──
    // Rule: if a player appears in wp_rosters for team T in year Y, and in year Y+1
    // they are not protected by T and not traded away from T, they must have been
    // released prior to the draft. If a RELEASED row is already present for that
    // team/year it is left as-is; otherwise an estimated row is injected.
    $existing_released = [];
    foreach ($rows as $row) {
        if ($row['type'] === 'RELEASED') {
            $existing_released[$row['year'] . '|' . $row['team']] = true;
        }
    }

    $roster_entries = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT year, team FROM wp_rosters WHERE pid = %s",
        $pid
    ), ARRAY_A);

    $current_year = (int) date('Y');
    $estimated_rows = [];
    $seen_estimated  = [];

    foreach ($roster_entries as $entry) {
        $roster_year = (int) $entry['year'];
        $roster_team = trim($entry['team']);
        $next_year   = $roster_year + 1;

        if ($next_year > $current_year) continue;

        $key = $next_year . '|' . $roster_team;
        if (isset($seen_estimated[$key])) continue;

        // Already have a real or estimated RELEASED?
        if (isset($existing_released[$key])) continue;

        // Still on this team's roster in next year?
        $still_rostered = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM wp_rosters WHERE pid = %s AND team = %s AND year = %d",
            $pid, $roster_team, $next_year
        ));
        if ($still_rostered) continue;

        // Protected by this team in next year?
        $protected = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM wp_protections WHERE playerId = %s AND team = %s AND year = %d",
            $pid, $roster_team, $next_year
        ));
        if ($protected) continue;

        // Traded FROM this team in next_year or roster_year?
        $like = '%' . $wpdb->esc_like($pid) . '%';
        $traded_from = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM wp_trades
             WHERE year IN (%d, %d)
               AND ((team2 = %s AND players1 LIKE %s)
                 OR (team1 = %s AND players2 LIKE %s))",
            $next_year, $roster_year,
            $roster_team, $like,
            $roster_team, $like
        ));
        if ($traded_from) continue;

        // No release found — inject an estimated one dated at draft day
        $draft_date = get_draft_date_for_player($next_year, $mflid);
        $date_fmt   = ($draft_date && $draft_date !== '0000-00-00')
                        ? date('m/d', strtotime($draft_date))
                        : '-';

        $estimated_rows[]        = [
            'type'      => 'RELEASED',
            'year'      => $next_year,
            'date'      => $date_fmt,
            'time'      => '-',
            'team'      => $roster_team,
            'action'    => 'Released',
            'estimated' => true,
        ];
        $seen_estimated[$key]    = true;
        $existing_released[$key] = true;
    }

    if (!empty($estimated_rows)) {
        $rows = array_merge($rows, $estimated_rows);
    }

    // ── Estimated FREE AGENT: roster entry with no known arrival transaction ──
    // If a player is on team T's roster in year Y and no DRAFT, PROTECTED, TRADE-in,
    // or FREE AGENT row explains how they got there, inject an estimated FREE AGENT
    // with date '--/--' to indicate the arrival method is unknown.
    $explained = [];
    foreach ($rows as $row) {
        $rt = $row['type']   ?? '';
        $ry = (int) $row['year'];
        $tm = $row['team']   ?? '';
        if (!$ry || !$tm) continue;
        if ($rt === 'DRAFT')     $explained[$ry . '|' . $tm] = true;
        if ($rt === 'PROTECTED') $explained[$ry . '|' . $tm] = true;
        if ($rt === 'TRADE')     $explained[$ry . '|' . $tm] = true;
        if ($rt === 'RELEASED' && ($row['action'] ?? '') === 'Added') $explained[$ry . '|' . $tm] = true;
    }

    // Build roster key set for carry-over check (on same team in prior year = no new arrival needed)
    $roster_key_set = [];
    foreach ($roster_entries as $e) {
        $roster_key_set[(int)$e['year'] . '|' . trim($e['team'])] = true;
    }

    $fa_rows = [];
    $seen_fa = [];
    foreach ($roster_entries as $entry) {
        $ry  = (int) $entry['year'];
        $rt  = trim($entry['team']);
        $key = $ry . '|' . $rt;

        if (isset($seen_fa[$key]))   continue;
        if (isset($explained[$key])) continue;

        // Carry-over: was on this team the prior year — arrival was explained then
        if (isset($roster_key_set[($ry - 1) . '|' . $rt])) continue;

        $fa_rows[]    = [
            'type'      => 'RELEASED', // frontend maps action="Added" → "FREE AGENT" pill
            'year'      => $ry,
            'date'      => '--/--',
            'time'      => '-',
            'team'      => $rt,
            'action'    => 'Added',
            'estimated' => true,
        ];
        $seen_fa[$key] = true;
    }

    if (!empty($fa_rows)) {
        $rows = array_merge($rows, $fa_rows);
    }

    // Final sort: descending by year, then by date, then by type.
    // Within the same date, DRAFT must appear before RELEASED in the descending
    // output so that after the frontend reverses the list, RELEASED precedes DRAFT.
    // Type rank controls ordering within the same date in the descending PHP output.
    // Higher rank = appears first in descending = appears LAST after frontend reversal.
    // So on draft day: DRAFT (1) before RELEASED (0) in PHP → RELEASED before DRAFT in UI.
    // FREE AGENT (unknown date, --/--) sorts to start of year = appears first in UI.
    $type_rank = ['DRAFT' => 1, 'RELEASED' => 0, 'DATA_GAP' => -1];
    usort($rows, function($a, $b) use ($type_rank) {
        if ($b['year'] !== $a['year']) return $b['year'] - $a['year'];
        // '--/--' (unknown date) sorts to end in descending = start of year in UI
        $known_a = ($a['date'] !== '-' && $a['date'] !== '--/--');
        $known_b = ($b['date'] !== '-' && $b['date'] !== '--/--');
        if ($known_a !== $known_b) return $known_a ? 0 : 1; // unknown floats to end (descending)
        $da = $known_a ? (int) strtotime($a['date'] . '/' . $a['year']) : 0;
        $db = $known_b ? (int) strtotime($b['date'] . '/' . $b['year']) : 0;
        if ($db !== $da) return $db - $da;
        // Same date — rank by type
        $ra = $type_rank[$a['type']] ?? 5;
        $rb = $type_rank[$b['type']] ?? 5;
        return $rb - $ra;
    });

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

// ─── TRADES LIST ENDPOINT ────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/trades', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_trades',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_format_pick(string $pick): string {
    $parts = explode('.', trim($pick));
    if (count($parts) === 3) {
        $round   = (int) ltrim($parts[1], '0') ?: 1;
        $suffixes = [1 => 'st', 2 => 'nd', 3 => 'rd'];
        $suffix  = $suffixes[$round] ?? 'th';
        return $parts[0] . ' ' . $round . $suffix . ' Rd';
    }
    return $pick;
}

function pfl_api_trades() {
    global $wpdb;
    $cache_key = 'pfl_trades_list_v1';
    $cached    = get_transient($cache_key);
    if ($cached !== false) return rest_ensure_response($cached);

    $rows = $wpdb->get_results(
        "SELECT id, year, team1, players1, picks1, protection1,
                team2, players2, picks2, protection2,
                notes, `when`, tradewinner, tradeloser, tradewinpoints
         FROM wp_trades ORDER BY year ASC, id ASC",
        ARRAY_A
    );

    if (empty($rows)) {
        set_transient($cache_key, [], DAY_IN_SECONDS);
        return rest_ensure_response([]);
    }

    // Batch-load all player names
    $all_pids = [];
    foreach ($rows as $row) {
        foreach (['players1', 'players2', 'protection1', 'protection2'] as $col) {
            foreach (array_filter(array_map('trim', explode(',', $row[$col] ?? ''))) as $pid) {
                $all_pids[] = $pid;
            }
        }
    }
    $all_pids   = array_values(array_unique(array_filter($all_pids)));
    $player_map = [];
    if (!empty($all_pids)) {
        $ph = implode(',', array_fill(0, count($all_pids), '%s'));
        $player_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ($ph)",
                ...$all_pids
            ),
            ARRAY_A
        );
        foreach ($player_rows as $p) {
            $player_map[$p['p_id']] = [
                'pid'   => $p['p_id'],
                'first' => $p['playerFirst'],
                'last'  => $p['playerLast'],
                'pos'   => strtoupper(substr($p['p_id'], -2)),
            ];
        }
    }

    // Team name lookup
    $team_map = [];
    foreach ($wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A) as $t) {
        $team_map[$t['team_int']] = $t['team'];
    }

    $when_order = ['Preseason' => 1, 'Draft' => 2, 'Postseason' => 3];

    $result = [];
    foreach ($rows as $row) {
        $split = fn($v) => array_values(array_filter(array_map('trim', explode(',', $v ?? ''))));

        $players1     = $split($row['players1']);
        $picks1       = $split($row['picks1']);
        $protections1 = $split($row['protection1']);
        $players2     = $split($row['players2']);
        $picks2       = $split($row['picks2']);
        $protections2 = $split($row['protection2']);

        $w = $row['tradewinner'] ?: null;
        $l = $row['tradeloser']  ?: null;

        $result[] = [
            'id'               => (int) $row['id'],
            'year'             => (int) $row['year'],
            'when'             => $row['when'] ?: null,
            'when_order'       => $when_order[$row['when']] ?? 99,
            'team1'            => $row['team1'],
            'team1_name'       => $team_map[$row['team1']] ?? $row['team1'],
            'team2'            => $row['team2'],
            'team2_name'       => $team_map[$row['team2']] ?? $row['team2'],
            'side1'            => [
                'players'      => array_values(array_filter(array_map(fn($pid) => $player_map[$pid] ?? null, $players1))),
                'picks'        => array_map('pfl_format_pick', $picks1),
                'protections'  => $protections1,
            ],
            'side2'            => [
                'players'      => array_values(array_filter(array_map(fn($pid) => $player_map[$pid] ?? null, $players2))),
                'picks'        => array_map('pfl_format_pick', $picks2),
                'protections'  => $protections2,
            ],
            'notes'            => $row['notes'] ?: null,
            'tradewinner'      => $w,
            'tradewinner_name' => $w ? ($team_map[$w] ?? $w) : null,
            'tradeloser'       => $l,
            'tradeloser_name'  => $l ? ($team_map[$l] ?? $l) : null,
            'tradewinpoints'   => ($row['tradewinpoints'] !== null && $row['tradewinpoints'] !== '') ? (float) $row['tradewinpoints'] : null,
        ];
    }

    usort($result, function($a, $b) {
        if ($a['year'] !== $b['year']) return $a['year'] - $b['year'];
        if ($a['when_order'] !== $b['when_order']) return $a['when_order'] - $b['when_order'];
        return $a['id'] - $b['id'];
    });
    foreach ($result as &$r) unset($r['when_order']);

    set_transient($cache_key, $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ─── TRADE ANALYZER ENDPOINT ─────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/trade-analyzer', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_trade_analyzer',
        'permission_callback' => '__return_true',
    ]);
});

// Grade a player's acquisition: count points/games for $teamid from $startseason onward.
// Mirrors the original grade_acquisition() continuity logic: stop after the first season
// where the player had games for this team but NO games the following season (unless protected).
function pfl_ta_grade(string $playerid, int $startseason, string $teamid): array {
    global $wpdb;

    // Check table exists
    if (!$wpdb->get_var("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND LOWER(TABLE_NAME) = '" . esc_sql(strtolower($playerid)) . "'")) {
        return ['points' => 0, 'games' => 0, 'week_range' => null];
    }

    // Find explicit trade-away end_season (hard cap)
    $end_season = null;
    $like       = '%' . $wpdb->esc_like($playerid) . '%';
    $later = $wpdb->get_results($wpdb->prepare(
        "SELECT year, team1, players1, team2, players2 FROM wp_trades
         WHERE (players1 LIKE %s OR players2 LIKE %s) AND year > %d ORDER BY year ASC",
        $like, $like, $startseason
    ), ARRAY_A);
    foreach ($later as $lt) {
        $p1 = array_map('trim', explode(',', $lt['players1']));
        $p2 = array_map('trim', explode(',', $lt['players2']));
        if (in_array($playerid, $p1, true) && trim($lt['team2']) === $teamid) {
            $end_season = (int) $lt['year']; break;
        }
        if (in_array($playerid, $p2, true) && trim($lt['team1']) === $teamid) {
            $end_season = (int) $lt['year']; break;
        }
    }

    // Protections for this player on this team
    $protected_years = [];
    foreach ($wpdb->get_results($wpdb->prepare(
        "SELECT year FROM wp_protections WHERE playerId = %s AND team = %s AND year >= %d",
        $playerid, $teamid, $startseason
    ), ARRAY_A) as $pr) {
        $protected_years[(int) $pr['year']] = true;
    }

    // Fetch all game rows for this player from startseason (team-filtered later)
    $max_year = $end_season ?? (int) date('Y');
    $all_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT year, week, points, team FROM `{$playerid}` WHERE year >= %d AND year <= %d ORDER BY year ASC, week ASC",
        $startseason, $max_year
    ), ARRAY_A);

    // Group by year, keeping only rows for this team
    $by_year = [];
    foreach ($all_rows as $r) {
        if (trim($r['team']) === $teamid) {
            $by_year[(int) $r['year']][] = $r;
        }
    }

    // Continuity check (mirrors original PHP):
    // Iterate year-by-year from startseason. When a year has games, peek at next year.
    // If next year has no games AND player is not protected into it → stop.
    $counted_rows = [];
    for ($y = $startseason; $y <= $max_year; $y++) {
        if (!isset($by_year[$y])) continue; // no games this year — keep scanning

        $counted_rows = array_merge($counted_rows, $by_year[$y]);

        $next             = $y + 1;
        $protected_next   = isset($protected_years[$next]);
        $has_games_next   = isset($by_year[$next]);

        if (!$protected_next && !$has_games_next) {
            break; // gap in service — stop here
        }
    }

    if (empty($counted_rows)) return ['points' => 0, 'games' => 0, 'week_range' => null];

    $points = array_sum(array_column($counted_rows, 'points'));
    $first  = $counted_rows[0]; $last = end($counted_rows);
    $fy = $first['year']; $fw = ltrim($first['week'], '0') ?: '0';
    $ly = $last['year'];  $lw = ltrim($last['week'], '0') ?: '0';
    $week_range = ($fy == $ly) ? "{$fy} Wk {$fw}–{$lw}" : "{$fy} Wk {$fw} – {$ly} Wk {$lw}";

    return ['points' => (float) $points, 'games' => count($counted_rows), 'week_range' => $week_range];
}

// Resolve pick string "YYYY.RR.PP" → playerid (or null)
// wp_drafts columns: year, round, roundnum (= pick within round)
function pfl_ta_resolve_pick(string $pick): ?string {
    global $wpdb;
    $parts = explode('.', trim($pick));
    if (count($parts) !== 3) return null;
    $pid = $wpdb->get_var($wpdb->prepare(
        "SELECT playerid FROM wp_drafts WHERE year = %d AND round = %s AND roundnum = %s AND playerid != '' LIMIT 1",
        (int) $parts[0], $parts[1], $parts[2]
    ));
    return $pid ?: null;
}

// Get the draft player name for a pick "YYYY.RR.PP" regardless of whether playerid exists
function pfl_ta_draft_name(string $pick): array {
    global $wpdb;
    $parts = explode('.', trim($pick));
    if (count($parts) !== 3) return ['first' => '', 'last' => ''];
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT playerfirst, playerlast FROM wp_drafts WHERE year = %d AND round = %s AND roundnum = %s LIMIT 1",
        (int) $parts[0], $parts[1], $parts[2]
    ), ARRAY_A);
    return ['first' => $row['playerfirst'] ?? '', 'last' => $row['playerlast'] ?? ''];
}

// Find what $teamid received when they later traded $playerid away (one level deep)
function pfl_ta_later_traded(string $playerid, int $startseason, string $teamid): ?array {
    global $wpdb;
    $like  = '%' . $wpdb->esc_like($playerid) . '%';
    $later = $wpdb->get_results($wpdb->prepare(
        "SELECT year, team1, players1, picks1, team2, players2, picks2 FROM wp_trades
         WHERE (players1 LIKE %s OR players2 LIKE %s) AND year > %d ORDER BY year ASC",
        $like, $like, $startseason
    ), ARRAY_A);

    foreach ($later as $lt) {
        $p1 = array_map('trim', explode(',', $lt['players1']));
        $p2 = array_map('trim', explode(',', $lt['players2']));
        // team2 traded player away → teamid receives players2/picks2
        if (in_array($playerid, $p1, true) && trim($lt['team2']) === $teamid) {
            return [
                'year'    => (int) $lt['year'],
                'players' => array_values(array_filter($p2)),
                'picks'   => array_values(array_filter(array_map('trim', explode(',', $lt['picks2'] ?? '')))),
            ];
        }
        // team1 traded player away → teamid receives players1/picks1
        if (in_array($playerid, $p2, true) && trim($lt['team1']) === $teamid) {
            return [
                'year'    => (int) $lt['year'],
                'players' => array_values(array_filter($p1)),
                'picks'   => array_values(array_filter(array_map('trim', explode(',', $lt['picks1'] ?? '')))),
            ];
        }
    }
    return null;
}

function pfl_ta_player_entry(string $pid, int $trade_year, string $teamid): array {
    global $wpdb;
    $grade = pfl_ta_grade($pid, $trade_year, $teamid);
    $name  = $wpdb->get_row($wpdb->prepare(
        "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s LIMIT 1", $pid
    ), ARRAY_A);
    return [
        'pid'        => $pid,
        'first'      => $name['playerFirst'] ?? '',
        'last'       => $name['playerLast']  ?? $pid,
        'pos'        => strtoupper(substr($pid, -2)),
        'points'     => $grade['points'],
        'games'      => $grade['games'],
        'week_range' => $grade['week_range'],
    ];
}

function pfl_ta_process_side(array $pids, array $picks, string $teamid, int $trade_year): array {
    $total        = 0;
    $players_out  = [];
    $picks_out    = [];

    foreach ($pids as $pid) {
        if (!$pid) continue;
        $entry = pfl_ta_player_entry($pid, $trade_year, $teamid);
        $total += $entry['points'];
        $entry['later_traded'] = null;

        $later = pfl_ta_later_traded($pid, $trade_year, $teamid);
        if ($later) {
            $lt = ['year' => $later['year'], 'players' => [], 'picks' => []];
            foreach ($later['players'] as $lt_pid) {
                if (!$lt_pid) continue;
                $e = pfl_ta_player_entry($lt_pid, $trade_year, $teamid);
                $total += $e['points'];
                $lt['players'][] = $e;
            }
            foreach ($later['picks'] as $lt_pick) {
                if (!$lt_pick) continue;
                $lt_pid   = pfl_ta_resolve_pick($lt_pick);
                $lt_label = pfl_format_pick($lt_pick);
                if ($lt_pid) {
                    $e = pfl_ta_player_entry($lt_pid, $trade_year, $teamid);
                    $e['raw'] = $lt_pick; $e['label'] = $lt_label; $e['became_pid'] = $lt_pid;
                    $total += $e['points'];
                    $lt['picks'][] = $e;
                } else {
                    $draft_name = pfl_ta_draft_name($lt_pick);
                    $lt['picks'][] = ['raw' => $lt_pick, 'label' => $lt_label, 'became_pid' => null, 'first' => $draft_name['first'], 'last' => $draft_name['last'], 'pos' => null, 'points' => 0, 'games' => 0, 'week_range' => null];
                }
            }
            $entry['later_traded'] = $lt;
        }
        $players_out[] = $entry;
    }

    foreach ($picks as $pick) {
        if (!$pick) continue;
        $resolved = pfl_ta_resolve_pick($pick);
        $label    = pfl_format_pick($pick);
        if ($resolved) {
            $e = pfl_ta_player_entry($resolved, $trade_year, $teamid);
            $total += $e['points'];
            // Use became_pid so the shape is consistent with the unresolved case
            $picks_out[] = array_merge(['raw' => $pick, 'label' => $label, 'became_pid' => $resolved], $e);
        } else {
            $draft_name = pfl_ta_draft_name($pick);
            $picks_out[] = ['raw' => $pick, 'label' => $label, 'became_pid' => null, 'first' => $draft_name['first'], 'last' => $draft_name['last'], 'pos' => null, 'points' => 0, 'games' => 0, 'week_range' => null];
        }
    }

    return ['players' => $players_out, 'picks' => $picks_out, 'total' => (float) $total];
}

function pfl_api_trade_analyzer(WP_REST_Request $request) {
    global $wpdb;
    $trade_id = (int) $request->get_param('id');
    if (!$trade_id) return new WP_Error('missing_id', 'Missing trade ID', ['status' => 400]);

    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT id, year, team1, players1, picks1, protection1,
                team2, players2, picks2, protection2,
                notes, `when`, tradewinner, tradeloser, tradewinpoints
         FROM wp_trades WHERE id = %d", $trade_id
    ), ARRAY_A);
    if (!$row) return new WP_Error('not_found', 'Trade not found', ['status' => 404]);

    $team_map = [];
    foreach ($wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A) as $t) {
        $team_map[$t['team_int']] = $t['team'];
    }

    $split      = fn($v) => array_values(array_filter(array_map('trim', explode(',', $v ?? ''))));
    $trade_year = (int) $row['year'];

    $side1 = pfl_ta_process_side($split($row['players1']), $split($row['picks1']), $row['team1'], $trade_year);
    $side2 = pfl_ta_process_side($split($row['players2']), $split($row['picks2']), $row['team2'], $trade_year);

    // Determine winner and write back to DB
    $s1 = $side1['total']; $s2 = $side2['total'];
    $winner = $loser = null; $margin = 0.0;
    if ($s1 > $s2) {
        $winner = $row['team1']; $loser = $row['team2']; $margin = $s1 - $s2;
    } elseif ($s2 > $s1) {
        $winner = $row['team2']; $loser = $row['team1']; $margin = $s2 - $s1;
    }
    if ($winner) {
        $wpdb->update('wp_trades',
            ['tradewinner' => $winner, 'tradeloser' => $loser, 'tradewinpoints' => (int) $margin],
            ['id' => $trade_id]
        );
        // Bust the trades list cache so the updated winner shows immediately
        delete_transient('pfl_trades_list_v1');
    }

    $prev_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM wp_trades WHERE id < %d ORDER BY id DESC LIMIT 1", $trade_id));
    $next_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM wp_trades WHERE id > %d ORDER BY id ASC  LIMIT 1", $trade_id));

    // ── Possible missing picks ──────────────────────────────────────────────
    // Picks that changed hands between these two teams (unlinked or linked to
    // placeholder tradeid=1) but are NOT already in picks1/picks2 of this trade.
    $recorded_picks = array_flip(array_merge($split($row['picks1']), $split($row['picks2'])));

    $cand_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, year, `round`, roundnum, picknum, pickord, team,
                playerfirst, playerlast, pos, tradeid
         FROM wp_drafts
         WHERE pickord != team
           AND (tradeid = 0 OR tradeid = 1)
           AND (
               (pickord = %s AND team = %s) OR
               (pickord = %s AND team = %s)
           )
           AND ABS(year - %d) <= 2
         ORDER BY year, `round`, roundnum",
        $row['team1'], $row['team2'],
        $row['team2'], $row['team1'],
        $trade_year
    ), ARRAY_A);

    $possible_missing = [];
    foreach ($cand_rows as $c) {
        $pick_str = sprintf('%s.%02d.%02d', $c['year'], (int)$c['round'], (int)$c['roundnum']);
        if (isset($recorded_picks[$pick_str])) continue; // already in this trade

        $player = trim(($c['playerfirst'] ?? '') . ' ' . ($c['playerlast'] ?? '')) ?: '—';
        if ($c['pos']) $player .= ' (' . $c['pos'] . ')';

        // picks1 = received by team1 (pickord=team2, team=team1)
        // picks2 = received by team2 (pickord=team1, team=team2)
        $recv_side = ($c['team'] === $row['team1']) ? 'picks1' : 'picks2';

        $possible_missing[] = [
            'draft_id'        => $c['id'],
            'pick_str'        => $pick_str,
            'year'            => (int)$c['year'],
            'round'           => (int)$c['round'],
            'picknum'         => (int)$c['picknum'],
            'pickord'         => $c['pickord'],
            'team'            => $c['team'],
            'player'          => $player,
            'current_tradeid' => (int)$c['tradeid'],
            'recv_side'       => $recv_side,
            'year_diff'       => abs((int)$c['year'] - $trade_year),
        ];
    }

    return rest_ensure_response([
        'id'          => $trade_id,
        'year'        => $trade_year,
        'when'        => $row['when'] ?: null,
        'notes'       => $row['notes'] ?: null,
        'team1'       => $row['team1'],
        'team1_name'  => $team_map[$row['team1']] ?? $row['team1'],
        'team2'       => $row['team2'],
        'team2_name'  => $team_map[$row['team2']] ?? $row['team2'],
        'side1'       => $side1,
        'side2'       => $side2,
        'winner'      => $winner,
        'winner_name' => $winner ? ($team_map[$winner] ?? $winner) : null,
        'loser'       => $loser,
        'loser_name'  => $loser  ? ($team_map[$loser]  ?? $loser)  : null,
        'margin'      => (float) $margin,
        'prev_id'           => $prev_id ? (int) $prev_id : null,
        'next_id'           => $next_id ? (int) $next_id : null,
        'possible_missing'  => $possible_missing,
    ]);
}

// ─── TRADE STATS TABLE ENDPOINTS ─────────────────────────────────────────────

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/trades/multiple-traded', [
        'methods'             => 'GET',
        'callback'            => 'pfl_tables_trades_multiple_traded',
        'permission_callback' => '__return_true',
    ]);
});
function pfl_tables_trades_multiple_traded() {
    global $wpdb;
    $cached = get_transient('pfl_tables_trades_multi_v1');
    if ($cached !== false) return rest_ensure_response($cached);

    $trades = $wpdb->get_results(
        "SELECT id, year, team1, players1, team2, players2 FROM wp_trades ORDER BY year ASC", ARRAY_A
    );
    $player_trades = [];
    foreach ($trades as $t) {
        foreach (array_filter(array_map('trim', explode(',', $t['players1'] ?? ''))) as $pid) {
            if ($pid) $player_trades[$pid][] = ['year' => (int)$t['year'], 'from_team' => trim($t['team2']), 'to_team' => trim($t['team1']), 'trade_id' => (int)$t['id']];
        }
        foreach (array_filter(array_map('trim', explode(',', $t['players2'] ?? ''))) as $pid) {
            if ($pid) $player_trades[$pid][] = ['year' => (int)$t['year'], 'from_team' => trim($t['team1']), 'to_team' => trim($t['team2']), 'trade_id' => (int)$t['id']];
        }
    }
    $multi = array_filter($player_trades, fn($ts) => count($ts) >= 2);
    uasort($multi, fn($a, $b) => count($b) - count($a));
    $pids = array_keys($multi);
    $names = [];
    if ($pids) {
        $in = implode(',', array_fill(0, count($pids), '%s'));
        foreach ($wpdb->get_results($wpdb->prepare("SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ($in)", ...$pids), ARRAY_A) as $r) {
            $names[$r['p_id']] = trim($r['playerFirst'] . ' ' . $r['playerLast']);
        }
    }
    $out = [];
    foreach ($multi as $pid => $ts) {
        $out[] = ['pid' => $pid, 'name' => $names[$pid] ?? $pid, 'count' => count($ts), 'trades' => $ts];
    }
    set_transient('pfl_tables_trades_multi_v1', $out, HOUR_IN_SECONDS);
    return rest_ensure_response($out);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/trades/team-matrix', [
        'methods'             => 'GET',
        'callback'            => 'pfl_tables_trades_team_matrix',
        'permission_callback' => '__return_true',
    ]);
});
function pfl_tables_trades_team_matrix() {
    global $wpdb;
    $cached = get_transient('pfl_tables_trades_matrix_v1');
    if ($cached !== false) return rest_ensure_response($cached);

    $teams = $wpdb->get_col("SELECT team_int FROM wp_teams ORDER BY team_int ASC");
    $matrix = [];
    foreach ($teams as $t) { $matrix[$t] = array_fill_keys($teams, 0); }
    $trades = $wpdb->get_results("SELECT team1, team2 FROM wp_trades", ARRAY_A);
    foreach ($trades as $tr) {
        $t1 = trim($tr['team1']); $t2 = trim($tr['team2']);
        if (isset($matrix[$t1][$t2])) $matrix[$t1][$t2]++;
        if (isset($matrix[$t2][$t1])) $matrix[$t2][$t1]++;
    }
    $out = ['teams' => $teams, 'matrix' => $matrix];
    set_transient('pfl_tables_trades_matrix_v1', $out, HOUR_IN_SECONDS);
    return rest_ensure_response($out);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/trades/points-leaders', [
        'methods'             => 'GET',
        'callback'            => 'pfl_tables_trades_points_leaders',
        'permission_callback' => '__return_true',
    ]);
});
function pfl_tables_trades_points_leaders() {
    global $wpdb;
    $cached = get_transient('pfl_tables_trades_pts_v1');
    if ($cached !== false) return rest_ensure_response($cached);

    $rows = $wpdb->get_results(
        "SELECT tradewinner AS team, COUNT(*) AS wins, SUM(tradewinpoints) AS total_pts
         FROM wp_trades
         WHERE tradewinner IS NOT NULL AND tradewinner != '' AND tradewinpoints IS NOT NULL
         GROUP BY tradewinner
         ORDER BY AVG(tradewinpoints) DESC", ARRAY_A
    );
    $team_map = [];
    foreach ($wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A) as $t) {
        $team_map[$t['team_int']] = $t['team'];
    }
    $out = [];
    foreach ($rows as $r) {
        $wins  = (int)$r['wins'];
        $total = (float)$r['total_pts'];
        $out[] = [
            'team'      => $r['team'],
            'team_name' => $team_map[$r['team']] ?? $r['team'],
            'wins'      => $wins,
            'total_pts' => (int)$total,
            'avg_pts'   => round($total / $wins, 1),
        ];
    }
    set_transient('pfl_tables_trades_pts_v1', $out, HOUR_IN_SECONDS);
    return rest_ensure_response($out);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/trades/biggest-wins', [
        'methods'             => 'GET',
        'callback'            => 'pfl_tables_trades_biggest_wins',
        'permission_callback' => '__return_true',
    ]);
});
function pfl_tables_trades_biggest_wins() {
    global $wpdb;
    $cached = get_transient('pfl_tables_trades_biggest_v1');
    if ($cached !== false) return rest_ensure_response($cached);

    $rows = $wpdb->get_results(
        "SELECT id, year, team1, players1, picks1, team2, players2, picks2,
                tradewinner, tradeloser, tradewinpoints
         FROM wp_trades
         WHERE tradewinner IS NOT NULL AND tradewinpoints IS NOT NULL AND tradewinpoints > 0
         ORDER BY tradewinpoints DESC
         LIMIT 20", ARRAY_A
    );
    // Collect all PIDs for bulk name lookup
    $all_pids = [];
    foreach ($rows as $r) {
        foreach ([array_filter(array_map('trim', explode(',', $r['players1'] ?? ''))), array_filter(array_map('trim', explode(',', $r['players2'] ?? '')))] as $pids) {
            foreach ($pids as $pid) { if ($pid) $all_pids[$pid] = true; }
        }
    }
    $name_map = [];
    if ($all_pids) {
        $pids = array_keys($all_pids);
        $in = implode(',', array_fill(0, count($pids), '%s'));
        foreach ($wpdb->get_results($wpdb->prepare("SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ($in)", ...$pids), ARRAY_A) as $p) {
            $name_map[$p['p_id']] = trim($p['playerFirst'] . ' ' . $p['playerLast']);
        }
    }
    $team_map = [];
    foreach ($wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A) as $t) {
        $team_map[$t['team_int']] = $t['team'];
    }
    $format_items = function(string $players_csv, string $picks_csv) use ($name_map): array {
        $items = [];
        foreach (array_filter(array_map('trim', explode(',', $players_csv))) as $pid) {
            $items[] = $name_map[$pid] ?? $pid;
        }
        foreach (array_filter(array_map('trim', explode(',', $picks_csv))) as $pick) {
            $items[] = pfl_format_pick($pick);
        }
        return $items;
    };
    $out = [];
    foreach ($rows as $i => $r) {
        $winner         = trim($r['tradewinner']); $loser = trim($r['tradeloser']);
        $winner_is_team1 = ($winner === trim($r['team1']));
        $out[] = [
            'rank'        => $i + 1,
            'trade_id'    => (int)$r['id'],
            'year'        => (int)$r['year'],
            'winner'      => $winner,
            'winner_name' => $team_map[$winner] ?? $winner,
            'winner_gets' => $winner_is_team1 ? $format_items($r['players1'], $r['picks1']) : $format_items($r['players2'], $r['picks2']),
            'loser'       => $loser,
            'loser_name'  => $team_map[$loser] ?? $loser,
            'loser_gets'  => $winner_is_team1 ? $format_items($r['players2'], $r['picks2']) : $format_items($r['players1'], $r['picks1']),
            'points'      => (int)$r['tradewinpoints'],
        ];
    }
    set_transient('pfl_tables_trades_biggest_v1', $out, HOUR_IN_SECONDS);
    return rest_ensure_response($out);
}

// ─── DRAFT STATS TABLE ENDPOINTS ──────────────────────────────────────────────

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/drafts/best-picks', [
        'methods'             => 'GET',
        'callback'            => 'pfl_tables_drafts_best_picks',
        'permission_callback' => '__return_true',
    ]);
});
function pfl_tables_drafts_best_picks() {
    global $wpdb;
    $cached = get_transient('pfl_tables_drafts_best_v1');
    if ($cached !== false) return rest_ensure_response($cached);

    $rows = $wpdb->get_results(
        "SELECT d.year, d.round, d.roundnum, d.playerid, d.playerfirst, d.playerlast, d.pos, d.team,
                t.team AS team_name, pv.valuescore, d.picknum
         FROM wp_drafts d
         LEFT JOIN wp_teams t ON t.team_int = d.team
         LEFT JOIN wp_drafts_pick_value pv ON pv.year = d.year
             AND CAST(pv.round AS UNSIGNED) = CAST(d.round AS UNSIGNED)
             AND CAST(pv.picknum AS UNSIGNED) = CAST(d.picknum AS UNSIGNED)
         WHERE d.playerid != '' AND pv.valuescore IS NOT NULL
         ORDER BY pv.valuescore DESC
         LIMIT 25",
        ARRAY_A
    );

    $out = [];
    foreach ($rows as $r) {
        $season_pts = null;
        $pts_row = $wpdb->get_row($wpdb->prepare(
            "SELECT points FROM wp_season_leaders WHERE playerid = %s AND season = %d LIMIT 1",
            $r['playerid'],
            (int)$r['year']
        ), ARRAY_A);
        if ($pts_row) {
            $season_pts = (int)$pts_row['points'];
        }
        $out[] = [
            'year'         => (int)$r['year'],
            'round'        => (int)$r['round'],
            'pick_in_round'=> (int)$r['roundnum'],
            'player_name'  => trim($r['playerfirst'] . ' ' . $r['playerlast']),
            'pos'          => $r['pos'],
            'team'         => $r['team'],
            'team_name'    => $r['team_name'],
            'value_score'  => (float)$r['valuescore'],
            'season_pts'   => $season_pts,
        ];
    }

    set_transient('pfl_tables_drafts_best_v1', $out, HOUR_IN_SECONDS);
    return rest_ensure_response($out);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/drafts/biggest-busts', [
        'methods'             => 'GET',
        'callback'            => 'pfl_tables_drafts_biggest_busts',
        'permission_callback' => '__return_true',
    ]);
});
function pfl_tables_drafts_biggest_busts() {
    global $wpdb;
    $cached = get_transient('pfl_tables_drafts_busts_v1');
    if ($cached !== false) return rest_ensure_response($cached);

    $rows = $wpdb->get_results(
        "SELECT d.year, d.round, d.roundnum, d.playerid, d.playerfirst, d.playerlast, d.pos, d.team,
                t.team AS team_name, pv.valuescore, d.picknum
         FROM wp_drafts d
         LEFT JOIN wp_teams t ON t.team_int = d.team
         LEFT JOIN wp_drafts_pick_value pv ON pv.year = d.year
             AND CAST(pv.round AS UNSIGNED) = CAST(d.round AS UNSIGNED)
             AND CAST(pv.picknum AS UNSIGNED) = CAST(d.picknum AS UNSIGNED)
         WHERE d.playerid != '' AND pv.valuescore IS NOT NULL
             AND CAST(d.round AS UNSIGNED) <= 3
         ORDER BY pv.valuescore ASC
         LIMIT 25",
        ARRAY_A
    );

    $out = [];
    foreach ($rows as $r) {
        $season_pts = null;
        $pts_row = $wpdb->get_row($wpdb->prepare(
            "SELECT points FROM wp_season_leaders WHERE playerid = %s AND season = %d LIMIT 1",
            $r['playerid'],
            (int)$r['year']
        ), ARRAY_A);
        if ($pts_row) {
            $season_pts = (int)$pts_row['points'];
        }
        $out[] = [
            'year'         => (int)$r['year'],
            'round'        => (int)$r['round'],
            'pick_in_round'=> (int)$r['roundnum'],
            'player_name'  => trim($r['playerfirst'] . ' ' . $r['playerlast']),
            'pos'          => $r['pos'],
            'team'         => $r['team'],
            'team_name'    => $r['team_name'],
            'value_score'  => (float)$r['valuescore'],
            'season_pts'   => $season_pts,
        ];
    }

    set_transient('pfl_tables_drafts_busts_v1', $out, HOUR_IN_SECONDS);
    return rest_ensure_response($out);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/drafts/team-grades', [
        'methods'             => 'GET',
        'callback'            => 'pfl_tables_drafts_team_grades',
        'permission_callback' => '__return_true',
    ]);
});
function pfl_tables_drafts_team_grades() {
    global $wpdb;
    $cached = get_transient('pfl_tables_drafts_grades_v1');
    if ($cached !== false) return rest_ensure_response($cached);

    $rows = $wpdb->get_results(
        "SELECT d.team, t.team AS team_name, COUNT(*) AS picks,
                AVG(pv.valuescore) AS avg_value, SUM(pv.valuescore) AS total_value
         FROM wp_drafts d
         LEFT JOIN wp_teams t ON t.team_int = d.team
         LEFT JOIN wp_drafts_pick_value pv ON pv.year = d.year
             AND CAST(pv.round AS UNSIGNED) = CAST(d.round AS UNSIGNED)
             AND CAST(pv.picknum AS UNSIGNED) = CAST(d.picknum AS UNSIGNED)
         WHERE d.playerid != '' AND pv.valuescore IS NOT NULL
         GROUP BY d.team, t.team
         ORDER BY AVG(pv.valuescore) DESC",
        ARRAY_A
    );

    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'team'        => $r['team'],
            'team_name'   => $r['team_name'],
            'picks'       => (int)$r['picks'],
            'avg_value'   => round((float)$r['avg_value'], 2),
            'total_value' => round((float)$r['total_value'], 1),
        ];
    }

    set_transient('pfl_tables_drafts_grades_v1', $out, HOUR_IN_SECONDS);
    return rest_ensure_response($out);
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/tables/drafts/value-by-round', [
        'methods'             => 'GET',
        'callback'            => 'pfl_tables_drafts_value_by_round',
        'permission_callback' => '__return_true',
    ]);
});
function pfl_tables_drafts_value_by_round() {
    global $wpdb;
    $cached = get_transient('pfl_tables_drafts_round_v1');
    if ($cached !== false) return rest_ensure_response($cached);

    $rows = $wpdb->get_results(
        "SELECT CAST(d.round AS UNSIGNED) AS round_num, COUNT(*) AS picks,
                AVG(pv.valuescore) AS avg_value
         FROM wp_drafts d
         LEFT JOIN wp_drafts_pick_value pv ON pv.year = d.year
             AND CAST(pv.round AS UNSIGNED) = CAST(d.round AS UNSIGNED)
             AND CAST(pv.picknum AS UNSIGNED) = CAST(d.picknum AS UNSIGNED)
         WHERE d.playerid != '' AND pv.valuescore IS NOT NULL
         GROUP BY CAST(d.round AS UNSIGNED)
         ORDER BY round_num ASC",
        ARRAY_A
    );

    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'round'     => (int)$r['round_num'],
            'picks'     => (int)$r['picks'],
            'avg_value' => round((float)$r['avg_value'], 2),
        ];
    }

    set_transient('pfl_tables_drafts_round_v1', $out, HOUR_IN_SECONDS);
    return rest_ensure_response($out);
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
    register_rest_route('pfl/v1', '/season-week-dates', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_season_week_dates',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_season_week_dates(WP_REST_Request $request) {
    global $wpdb;
    $year = (int) $request->get_param('year');
    if (!$year) return new WP_Error('missing_year', 'year required', ['status' => 400]);

    $cache_key = "pfl_season_week_dates_{$year}_v1";
    $cached = get_transient($cache_key);
    if ($cached !== false) return rest_ensure_response($cached);

    // Same team list as weekly-results
    $all_teams = ['RBS','ETS','PEP','WRZ','CMN','BUL','SNR','TSG','BST','MAX','PHR','SON','ATK','HAT','DST'];

    // Step 1: Collect starter PIDs per week by querying every team's season rows
    $pids_by_weekid = [];
    foreach ($all_teams as $t) {
        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `wp_team_{$t}` WHERE id LIKE %s", $year . '%'),
            ARRAY_N
        );
        if (!$rows) continue;
        foreach ($rows as $row) {
            $weekid = $row[0]; // id column = YYYYWW
            for ($i = 10; $i <= 18; $i++) {
                if (!empty($row[$i])) $pids_by_weekid[$weekid][] = $row[$i];
            }
        }
    }

    // Step 2: Query each unique PID's game_dates for the year in one go
    $all_pids = array_unique(array_merge(...array_values($pids_by_weekid ?: [[]])));
    $dates_by_weekid = [];
    foreach ($all_pids as $pid) {
        $wpdb->suppress_errors(true);
        $date_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT week_id, game_date FROM `{$pid}` WHERE week_id LIKE %s AND game_date IS NOT NULL AND game_date != ''",
                $year . '%'
            ),
            ARRAY_A
        );
        $wpdb->suppress_errors(false);
        if (!$date_rows) continue;
        foreach ($date_rows as $dr) {
            $wid = $dr['week_id'];
            $d   = $dr['game_date'];
            if (!isset($dates_by_weekid[$wid])) {
                $dates_by_weekid[$wid] = ['min' => $d, 'max' => $d];
            } else {
                if ($d < $dates_by_weekid[$wid]['min']) $dates_by_weekid[$wid]['min'] = $d;
                if ($d > $dates_by_weekid[$wid]['max']) $dates_by_weekid[$wid]['max'] = $d;
            }
        }
    }

    // Step 3: Convert YYYYWW keys to integer week numbers
    $dates_by_week = [];
    foreach ($dates_by_weekid as $wid => $dr) {
        $w = (int) substr((string) $wid, 4);
        if ($w >= 1 && $w <= 16) $dates_by_week[$w] = $dr;
    }

    // Step 4: Thanksgiving week — use the authoritative SNR Thanksgiving table
    $thanksgiving_week = null;
    $tgiving_rows = pfl_get_snr_thanksgiving_data();
    foreach ($tgiving_rows as $tr) {
        if ((int) $tr['year'] === $year) {
            $thanksgiving_week = (int) $tr['pfl_week'];
            break;
        }
    }

    $result = ['dates' => $dates_by_week, 'thanksgiving_week' => $thanksgiving_week];
    set_transient($cache_key, $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

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

    // OT games keyed by "week_minTeam_maxTeam" for per-week lookup
    // wp_overtime id = YYYYWWNN; chars 4-5 = zero-padded week; [1]=winteam [2]=loseteam
    $ot_rows = $wpdb->get_results(
        $wpdb->prepare("SELECT id, winteam, loseteam FROM wp_overtime WHERE id LIKE %s", $year . '%'),
        ARRAY_A
    );
    $ot_games = [];
    foreach ($ot_rows as $o) {
        $week_num = (int) substr($o['id'], 4, 2);
        $key = $week_num . '_' . min($o['winteam'], $o['loseteam']) . '_' . max($o['winteam'], $o['loseteam']);
        $ot_games[$key] = true;
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
            $w      = (int) $r[2];
            $t1     = $r[3];
            $t2     = $r[5];
            $key    = min($t1, $t2) . '_' . max($t1, $t2);
            $ot_key = $w . '_' . $key;
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
                'is_ot'      => isset($ot_games[$ot_key]),
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
            $t1     = $r['team'];
            $t2     = $r['versus'];
            $key    = min($t1, $t2) . '_' . max($t1, $t2);
            $ot_key = $pw . '_' . $key;
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
                'is_ot'      => isset($ot_games[$ot_key]),
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
    register_rest_route('pfl/v1', '/players-by-mflid', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_players_by_mflid',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/mfl-franchises', [
        'methods'             => 'GET',
        'callback'            => function(WP_REST_Request $req) {
            $season = (int) ($req->get_param('season') ?: date('Y'));
            $all = get_pfl_mfl_ids_season();
            $map = $all[$season] ?? [];
            global $wpdb;
            $rows = $wpdb->get_results("SELECT team_int, team, owner FROM wp_teams", ARRAY_A);
            $by_team = [];
            foreach ($rows as $r) $by_team[$r['team_int']] = $r;
            $out = [];
            foreach ($map as $franchiseId => $teamAbbrev) {
                if (!$teamAbbrev) continue;
                $info = $by_team[$teamAbbrev] ?? [];
                $out[$franchiseId] = [
                    'team'     => $teamAbbrev,
                    'teamName' => $info['team'] ?? $teamAbbrev,
                    'owner'    => $info['owner'] ?? '',
                ];
            }
            return rest_ensure_response((object) $out);
        },
        'permission_callback' => '__return_true',
    ]);
});

/**
 * Look up PFL player records by MFL player id (comma-separated `ids` param).
 * Used by the /gameday view to translate MFL roster pids into real names.
 *
 * Response shape:
 *   { "13130": { "pid": "...", "first": "...", "last": "...", "position": "..." }, ... }
 */
function pfl_api_players_by_mflid(WP_REST_Request $request) {
    global $wpdb;
    $raw = (string) ($request->get_param('ids') ?? '');
    $ids = array_values(array_filter(array_map('trim', explode(',', $raw))));
    if (empty($ids)) return rest_ensure_response((object) []);
    $placeholders = implode(',', array_fill(0, count($ids), '%s'));
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT p_id, mflid, playerFirst, playerLast, position FROM wp_players WHERE mflid IN ($placeholders)",
        ...$ids
    ), ARRAY_A);
    $out = [];
    foreach ($rows as $r) {
        $out[(string) $r['mflid']] = [
            'pid'      => $r['p_id'],
            'first'    => $r['playerFirst'],
            'last'     => $r['playerLast'],
            'position' => $r['position'],
        ];
    }
    return rest_ensure_response((object) $out);
}

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

    $years = [2022, 2023, 2024, 2025, 2026];
    $year_list_sql = implode(',', $years);

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
         WHERE year IN ({$year_list_sql})",
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
                "SELECT playerFirst AS first, playerLast AS last FROM wp_players WHERE p_id = %s LIMIT 1", $pid
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
                "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s LIMIT 1", $pid
            ), ARRAY_A);
            if ($name) { $first = $name['playerFirst']; $last = $name['playerLast']; }
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

// ── QB Draft (by year) ────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/qb-draft', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_qb_draft',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_qb_draft(WP_REST_Request $request) {
    global $wpdb;

    $year = (int) $request->get_param('year');
    if (!$year) {
        return new WP_Error('missing_year', 'Year required', ['status' => 400]);
    }

    $raw = $wpdb->get_results($wpdb->prepare(
        "SELECT round, roundnum, picknum, pickord AS orteam, team, playerfirst, playerlast, pos, playerid
         FROM wp_drafts
         WHERE year = %d AND pos = 'QB'
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
                "SELECT playerFirst AS first, playerLast AS last FROM wp_players WHERE p_id = %s LIMIT 1", $pid
            ), ARRAY_A);
            if ($name) { $first = $name['first']; $last = $name['last']; }
        }
        $picks[] = [
            'round'       => (int) $r['round'],
            'pickInRound' => (int) $r['roundnum'],
            'overall'     => (int) $r['picknum'],
            'pid'         => $pid,
            'first'       => $first,
            'last'        => $last,
            'team'        => $r['team'],
        ];
    }

    // All QB points leaders for the selected season — playerid ends in 'QB'
    $leader_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT playerid AS pid, points, games
         FROM wp_season_leaders
         WHERE season = %d AND playerid LIKE '%%QB'
         ORDER BY CAST(points AS DECIMAL(10,2)) DESC",
        $year
    ), ARRAY_A);

    $leaders = [];
    foreach ($leader_rows as $l) {
        $pid   = $l['pid'];
        $first = '';
        $last  = '';
        $name = $wpdb->get_row($wpdb->prepare(
            "SELECT playerfirst, playerlast FROM wp_drafts WHERE playerid = %s AND playerfirst != '' LIMIT 1", $pid
        ), ARRAY_A);
        if ($name) {
            $first = $name['playerfirst'];
            $last  = $name['playerlast'];
        } else {
            $name = $wpdb->get_row($wpdb->prepare(
                "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s LIMIT 1", $pid
            ), ARRAY_A);
            if ($name) { $first = $name['playerFirst']; $last = $name['playerLast']; }
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

// ── QB Draft Tendencies (all-time aggregated) ─────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/qb-draft-tendencies', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_qb_draft_tendencies',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_qb_draft_tendencies() {
    global $wpdb;

    $known_teams = ['BST','TSG','BUL','CMN','WRZ','ETS','DST','HAT','SNR','PEP'];

    $rows = $wpdb->get_results(
        "SELECT team, year,
                CAST(round   AS UNSIGNED) AS round,
                CAST(picknum AS UNSIGNED) AS overall
         FROM wp_drafts
         WHERE pos = 'QB' AND team != '' AND year != ''
         ORDER BY team, CAST(year AS UNSIGNED), CAST(picknum AS UNSIGNED)",
        ARRAY_A
    );

    $first_by_team_year = [];
    foreach ($rows as $r) {
        $team    = $r['team'];
        $year    = (int) $r['year'];
        $round   = (int) $r['round'];
        $overall = (int) $r['overall'];
        if (!isset($first_by_team_year[$team][$year])) {
            $first_by_team_year[$team][$year] = ['round' => $round, 'overall' => $overall];
        }
    }

    $all_years = array_unique(array_map('intval', array_column($rows, 'year')));
    sort($all_years);
    $min_year = min($all_years);
    $max_year = max($all_years);
    $years = range($min_year, $max_year);

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

// ── RB Draft (by year) ────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/rb-draft', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_rb_draft',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_rb_draft(WP_REST_Request $request) {
    global $wpdb;

    $year = (int) $request->get_param('year');
    if (!$year) {
        return new WP_Error('missing_year', 'Year required', ['status' => 400]);
    }

    $raw = $wpdb->get_results($wpdb->prepare(
        "SELECT round, roundnum, picknum, pickord AS orteam, team, playerfirst, playerlast, pos, playerid
         FROM wp_drafts
         WHERE year = %d AND pos = 'RB'
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
                "SELECT playerFirst AS first, playerLast AS last FROM wp_players WHERE p_id = %s LIMIT 1", $pid
            ), ARRAY_A);
            if ($name) { $first = $name['first']; $last = $name['last']; }
        }
        $picks[] = [
            'round'       => (int) $r['round'],
            'pickInRound' => (int) $r['roundnum'],
            'overall'     => (int) $r['picknum'],
            'pid'         => $pid,
            'first'       => $first,
            'last'        => $last,
            'team'        => $r['team'],
        ];
    }

    $leader_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT playerid AS pid, points, games
         FROM wp_season_leaders
         WHERE season = %d AND playerid LIKE '%%RB'
         ORDER BY CAST(points AS DECIMAL(10,2)) DESC",
        $year
    ), ARRAY_A);

    $leaders = [];
    foreach ($leader_rows as $l) {
        $pid   = $l['pid'];
        $first = '';
        $last  = '';
        $name = $wpdb->get_row($wpdb->prepare(
            "SELECT playerfirst, playerlast FROM wp_drafts WHERE playerid = %s AND playerfirst != '' LIMIT 1", $pid
        ), ARRAY_A);
        if ($name) {
            $first = $name['playerfirst'];
            $last  = $name['playerlast'];
        } else {
            $name = $wpdb->get_row($wpdb->prepare(
                "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s LIMIT 1", $pid
            ), ARRAY_A);
            if ($name) { $first = $name['playerFirst']; $last = $name['playerLast']; }
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

// ── RB Draft Tendencies ───────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/rb-draft-tendencies', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_rb_draft_tendencies',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_rb_draft_tendencies() {
    global $wpdb;

    $known_teams = ['BST','TSG','BUL','CMN','WRZ','ETS','DST','HAT','SNR','PEP'];

    $rows = $wpdb->get_results(
        "SELECT team, year,
                CAST(round   AS UNSIGNED) AS round,
                CAST(picknum AS UNSIGNED) AS overall
         FROM wp_drafts
         WHERE pos = 'RB' AND team != '' AND year != ''
         ORDER BY team, CAST(year AS UNSIGNED), CAST(picknum AS UNSIGNED)",
        ARRAY_A
    );

    $first_by_team_year = [];
    foreach ($rows as $r) {
        $team    = $r['team'];
        $year    = (int) $r['year'];
        $round   = (int) $r['round'];
        $overall = (int) $r['overall'];
        if (!isset($first_by_team_year[$team][$year])) {
            $first_by_team_year[$team][$year] = ['round' => $round, 'overall' => $overall];
        }
    }

    $all_years = array_unique(array_map('intval', array_column($rows, 'year')));
    sort($all_years);
    $years = range(min($all_years), max($all_years));

    $team_stats = [];
    $heatmap    = [];

    foreach ($known_teams as $team) {
        $picks = $first_by_team_year[$team] ?? [];
        $heatmap[$team] = [];
        foreach ($years as $yr) {
            $heatmap[$team][$yr] = isset($picks[$yr]) ? $picks[$yr] : null;
        }

        if (empty($picks)) {
            $team_stats[] = ['team' => $team, 'n' => 0, 'avgOverall' => null, 'minOverall' => null, 'maxOverall' => null, 'avgRound' => null, 'skippedYears' => count($years)];
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

    usort($team_stats, function($a, $b) {
        if ($a['avgOverall'] === null) return 1;
        if ($b['avgOverall'] === null) return -1;
        return $a['avgOverall'] <=> $b['avgOverall'];
    });

    return rest_ensure_response(['teamStats' => $team_stats, 'heatmap' => $heatmap, 'years' => $years]);
}

// ── WR Draft (by year) ────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/wr-draft', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_wr_draft',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_wr_draft(WP_REST_Request $request) {
    global $wpdb;

    $year = (int) $request->get_param('year');
    if (!$year) {
        return new WP_Error('missing_year', 'Year required', ['status' => 400]);
    }

    $raw = $wpdb->get_results($wpdb->prepare(
        "SELECT round, roundnum, picknum, pickord AS orteam, team, playerfirst, playerlast, pos, playerid
         FROM wp_drafts
         WHERE year = %d AND pos = 'WR'
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
                "SELECT playerFirst AS first, playerLast AS last FROM wp_players WHERE p_id = %s LIMIT 1", $pid
            ), ARRAY_A);
            if ($name) { $first = $name['first']; $last = $name['last']; }
        }
        $picks[] = [
            'round'       => (int) $r['round'],
            'pickInRound' => (int) $r['roundnum'],
            'overall'     => (int) $r['picknum'],
            'pid'         => $pid,
            'first'       => $first,
            'last'        => $last,
            'team'        => $r['team'],
        ];
    }

    $leader_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT playerid AS pid, points, games
         FROM wp_season_leaders
         WHERE season = %d AND playerid LIKE '%%WR'
         ORDER BY CAST(points AS DECIMAL(10,2)) DESC",
        $year
    ), ARRAY_A);

    $leaders = [];
    foreach ($leader_rows as $l) {
        $pid   = $l['pid'];
        $first = '';
        $last  = '';
        $name = $wpdb->get_row($wpdb->prepare(
            "SELECT playerfirst, playerlast FROM wp_drafts WHERE playerid = %s AND playerfirst != '' LIMIT 1", $pid
        ), ARRAY_A);
        if ($name) {
            $first = $name['playerfirst'];
            $last  = $name['playerlast'];
        } else {
            $name = $wpdb->get_row($wpdb->prepare(
                "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s LIMIT 1", $pid
            ), ARRAY_A);
            if ($name) { $first = $name['playerFirst']; $last = $name['playerLast']; }
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

// ── WR Draft Tendencies ───────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/wr-draft-tendencies', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_wr_draft_tendencies',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_wr_draft_tendencies() {
    global $wpdb;

    $known_teams = ['BST','TSG','BUL','CMN','WRZ','ETS','DST','HAT','SNR','PEP'];

    $rows = $wpdb->get_results(
        "SELECT team, year,
                CAST(round   AS UNSIGNED) AS round,
                CAST(picknum AS UNSIGNED) AS overall
         FROM wp_drafts
         WHERE pos = 'WR' AND team != '' AND year != ''
         ORDER BY team, CAST(year AS UNSIGNED), CAST(picknum AS UNSIGNED)",
        ARRAY_A
    );

    $first_by_team_year = [];
    foreach ($rows as $r) {
        $team    = $r['team'];
        $year    = (int) $r['year'];
        $round   = (int) $r['round'];
        $overall = (int) $r['overall'];
        if (!isset($first_by_team_year[$team][$year])) {
            $first_by_team_year[$team][$year] = ['round' => $round, 'overall' => $overall];
        }
    }

    $all_years = array_unique(array_map('intval', array_column($rows, 'year')));
    sort($all_years);
    $years = range(min($all_years), max($all_years));

    $team_stats = [];
    $heatmap    = [];

    foreach ($known_teams as $team) {
        $picks = $first_by_team_year[$team] ?? [];
        $heatmap[$team] = [];
        foreach ($years as $yr) {
            $heatmap[$team][$yr] = isset($picks[$yr]) ? $picks[$yr] : null;
        }

        if (empty($picks)) {
            $team_stats[] = ['team' => $team, 'n' => 0, 'avgOverall' => null, 'minOverall' => null, 'maxOverall' => null, 'avgRound' => null, 'skippedYears' => count($years)];
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

    usort($team_stats, function($a, $b) {
        if ($a['avgOverall'] === null) return 1;
        if ($b['avgOverall'] === null) return -1;
        return $a['avgOverall'] <=> $b['avgOverall'];
    });

    return rest_ensure_response(['teamStats' => $team_stats, 'heatmap' => $heatmap, 'years' => $years]);
}

// ── Team Page ─────────────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/teams-list', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_teams_list',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/team', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_team',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/team-leaders', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_team_leaders',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadiums', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_stadiums',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-image-settings', [
        'methods'             => 'GET',
        'callback'            => function() {
            return rest_ensure_response((object) get_option('pfl_stadium_image_settings', []));
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-image-settings', [
        'methods'             => 'POST',
        'callback'            => function(WP_REST_Request $req) {
            $url   = esc_url_raw($req->get_param('url'));
            $scale = (float) $req->get_param('scale');
            $x     = (float) $req->get_param('x');
            $y     = (float) $req->get_param('y');
            if (empty($url)) return new WP_Error('missing_url', 'Image URL required', ['status' => 400]);
            $settings = get_option('pfl_stadium_image_settings', []);
            $settings[$url] = ['scale' => $scale, 'x' => $x, 'y' => $y];
            update_option('pfl_stadium_image_settings', $settings);
            return rest_ensure_response(['success' => true]);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-debug-stand', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            $cols = $wpdb->get_col("SHOW COLUMNS FROM stand2010");
            $err  = $wpdb->last_error;
            $rows = $wpdb->get_results("SELECT teamid, seed FROM stand2010", ARRAY_A);
            $err2 = $wpdb->last_error;
            return rest_ensure_response([
                'columns'  => $cols,
                'cols_err' => $err,
                'rows'     => array_slice($rows, 0, 3),
                'row_count' => count($rows),
                'rows_err' => $err2,
            ]);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-debug-show-tables', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            $r = $wpdb->get_var("SHOW TABLES LIKE 'stand2010'");
            return rest_ensure_response([
                'show_tables_result' => $r,
                'is_equal'           => ($r === 'stand2010'),
                'is_string'          => is_string($r),
                'len'                => $r ? strlen($r) : 0,
            ]);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-debug-internals', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            $rows = $wpdb->get_results("SELECT id, facility_names, team_owned, year_built, currently_occupied FROM wp_stadiums", ARRAY_A);
            $teams_with_current = [];
            foreach ($rows as $r) {
                if ((int) ($r['currently_occupied'] ?? 0) === 1) {
                    $teams_with_current[pfl_base_team($r['team_owned'])] = true;
                }
            }
            $defunct_teams = [];
            foreach ($rows as $r) {
                $bt = pfl_base_team($r['team_owned']);
                if (empty($teams_with_current[$bt]) || $r['team_owned'] !== $bt) {
                    $defunct_teams[$bt] = true;
                }
            }
            $team_years_active = [];
            for ($yr = 1991; $yr <= (int) date('Y'); $yr++) {
                $tbl = "stand{$yr}";
                if ($wpdb->get_var("SHOW TABLES LIKE '{$tbl}'") !== $tbl) continue;
                foreach ($wpdb->get_col("SELECT DISTINCT teamid FROM {$tbl}") as $t) {
                    if (isset($defunct_teams[$t])) $team_years_active[$t][] = $yr;
                }
            }
            $team_last_year = [];
            foreach ($team_years_active as $t => $years) {
                sort($years);
                $last = $years[0];
                for ($i = 1; $i < count($years); $i++) {
                    if ($years[$i] - $years[$i - 1] > 2) break;
                    $last = $years[$i];
                }
                $team_last_year[$t] = $last;
            }
            return rest_ensure_response([
                'teams_with_current' => $teams_with_current,
                'defunct_teams'      => $defunct_teams,
                'team_last_year'     => $team_last_year,
                'bst_years_active'   => $team_years_active['BST'] ?? null,
            ]);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-debug-team-years', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            $years = [];
            for ($yr = 1991; $yr <= (int) date('Y'); $yr++) {
                $tbl = "stand{$yr}";
                if ($wpdb->get_var("SHOW TABLES LIKE '{$tbl}'") !== $tbl) continue;
                $teams = $wpdb->get_col("SELECT DISTINCT teamid FROM {$tbl}");
                if (in_array('BST', $teams)) $years[] = $yr;
            }
            return rest_ensure_response(['bst_years_in_standings' => $years]);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-widen-team-col', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            $r = $wpdb->query("ALTER TABLE wp_stadiums MODIFY team_owned VARCHAR(16) NULL");
            return rest_ensure_response(['ret' => $r, 'error' => $wpdb->last_error]);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/wp-players-schema', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            return rest_ensure_response($wpdb->get_results("SHOW COLUMNS FROM wp_players", ARRAY_A));
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/wp-stadiums-schema', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            return rest_ensure_response($wpdb->get_results("SHOW COLUMNS FROM wp_stadiums", ARRAY_A));
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-update-raw', [
        'methods' => 'GET',
        'callback' => function(WP_REST_Request $req) {
            global $wpdb;
            $id    = (int) $req->get_param('id');
            $field = sanitize_text_field($req->get_param('field') ?? '');
            $value = sanitize_text_field($req->get_param('value') ?? '');
            $exec  = (bool) $req->get_param('execute');
            $allowed = ['team_owned', 'facility_names'];
            if (!$id || !in_array($field, $allowed, true)) {
                return new WP_Error('bad_params', 'bad params', ['status' => 400]);
            }
            $before = $wpdb->get_row($wpdb->prepare("SELECT id, facility_names, team_owned FROM wp_stadiums WHERE id = %d", $id), ARRAY_A);
            if (!$exec) return rest_ensure_response(['before' => $before, 'note' => 'add &execute=1']);

            $sql = $wpdb->prepare("UPDATE wp_stadiums SET `{$field}` = %s WHERE id = %d", $value, $id);
            $ret = $wpdb->query($sql);
            $err = $wpdb->last_error;
            $after = $wpdb->get_row($wpdb->prepare("SELECT id, facility_names, team_owned FROM wp_stadiums WHERE id = %d", $id), ARRAY_A);
            return rest_ensure_response(['sql' => $sql, 'ret' => $ret, 'error' => $err, 'before' => $before, 'after' => $after]);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-update', [
        'methods'  => 'GET',
        'callback' => function(WP_REST_Request $req) {
            global $wpdb;
            $id    = (int) $req->get_param('id');
            $field = sanitize_text_field($req->get_param('field') ?? '');
            $value = $req->get_param('value');
            $exec  = (bool) $req->get_param('execute');
            $allowed = ['team_owned', 'facility_names', 'year_built', 'currently_occupied'];
            if (!$id || !in_array($field, $allowed, true)) {
                return new WP_Error('bad_params', "id + field (one of: " . implode(',', $allowed) . ") required", ['status' => 400]);
            }
            $before = $wpdb->get_row($wpdb->prepare(
                "SELECT id, facility_names, team_owned, year_built, currently_occupied FROM wp_stadiums WHERE id = %d", $id
            ), ARRAY_A);
            if (!$before) return new WP_Error('not_found', "Stadium id $id not found", ['status' => 404]);

            $result = ['before' => $before, 'change' => [$field => $value]];
            if (!$exec) {
                $result['note'] = "Add &execute=1 to apply the update.";
                return rest_ensure_response($result);
            }
            $rows = $wpdb->update('wp_stadiums', [$field => $value], ['id' => $id]);
            $after = $wpdb->get_row($wpdb->prepare(
                "SELECT id, facility_names, team_owned, year_built, currently_occupied FROM wp_stadiums WHERE id = %d", $id
            ), ARRAY_A);
            $result['rows_affected'] = (int) $rows;
            $result['after']         = $after;
            return rest_ensure_response($result);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-raw', [
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;
            return rest_ensure_response($wpdb->get_results(
                "SELECT id, facility_names, team_owned, year_built, year_renovated, currently_occupied
                 FROM wp_stadiums ORDER BY team_owned, year_built", ARRAY_A
            ));
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/stadium-rename', [
        'methods'             => 'GET',
        'callback'            => function(WP_REST_Request $req) {
            global $wpdb;
            $team   = strtoupper(sanitize_text_field($req->get_param('team') ?? ''));
            $ystart = (int) $req->get_param('year_start');
            $yend   = (int) $req->get_param('year_end');
            $newnm  = $req->get_param('new_name');
            $exec   = (bool) $req->get_param('execute');
            if (!$team || !$ystart || !$yend || !$newnm) {
                return new WP_Error('missing_params', 'team, year_start, year_end, new_name required', ['status' => 400]);
            }
            $tbl = "wp_team_{$team}";
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tbl)) !== $tbl) {
                return new WP_Error('no_table', "Table {$tbl} not found", ['status' => 404]);
            }
            // Preview the current stadium values in the range
            $preview = $wpdb->get_results($wpdb->prepare(
                "SELECT season, stadium, COUNT(*) AS cnt
                 FROM `{$tbl}`
                 WHERE season BETWEEN %d AND %d AND home_away = 'H'
                 GROUP BY season, stadium
                 ORDER BY season, stadium",
                $ystart, $yend
            ), ARRAY_A);

            $result = ['preview' => $preview];
            if (!$exec) {
                $result['note'] = "Add &execute=1 to apply the update.";
                return rest_ensure_response($result);
            }

            // 1. Update wp_team_{TEAM} for those seasons
            $team_rows = $wpdb->query($wpdb->prepare(
                "UPDATE `{$tbl}` SET stadium = %s
                 WHERE season BETWEEN %d AND %d AND home_away = 'H' AND stadium <> %s",
                $newnm, $ystart, $yend, $newnm
            ));

            // 2. Look up canonical id for new name and update wp_attendance for matching rows
            $new_sid = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM wp_stadiums WHERE facility_names = %s LIMIT 1", $newnm
            ));
            $att_rows = 0;
            if ($new_sid && $wpdb->get_var("SHOW TABLES LIKE 'wp_attendance'") === 'wp_attendance') {
                $att_rows = $wpdb->query($wpdb->prepare(
                    "UPDATE wp_attendance
                     SET stadium_name = %s, stadium_id = %d
                     WHERE home_team = %s AND year BETWEEN %d AND %d",
                    $newnm, $new_sid, $team, $ystart, $yend
                ));
            }

            // Bust the weekly-results transient cache for the affected years/weeks
            $busted = 0;
            for ($y = $ystart; $y <= $yend; $y++) {
                for ($w = 1; $w <= 16; $w++) {
                    $key = "pfl_weekly_results_{$y}" . str_pad($w, 2, '0', STR_PAD_LEFT) . "_v37";
                    if (delete_transient($key)) $busted++;
                }
            }

            $result['executed'] = [
                'team_table_rows_updated'     => (int) $team_rows,
                'wp_attendance_rows_updated'  => (int) $att_rows,
                'new_facility_id'             => $new_sid ?: null,
                'transients_busted'           => $busted,
            ];
            return rest_ensure_response($result);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/attendance-repair', [
        'methods'             => 'GET',
        'callback'            => function() {
            global $wpdb;
            $aliases = [
                'The Cuckoos Nest'  => "The Cuckoo's Nest",
                'Strawberry Fields' => "Octopus' Garden at Strawberry Fields",
                'Kennedy Compound'  => 'The Kennedy Compound',
                'Gigalo Pits'       => 'The Gigalo Pits',
                'The Litter Pan'    => 'The Litter Box',
                'The Reseviour'     => 'The Reservoir',
                'Dog House'         => 'The Dog House',
            ];
            $updates = [];
            foreach ($aliases as $bad => $good) {
                $sid = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM wp_stadiums WHERE facility_names = %s LIMIT 1", $good
                ));
                if (!$sid) { $updates[$bad] = ['sid' => null, 'rows' => 0]; continue; }
                $rows = $wpdb->query($wpdb->prepare(
                    "UPDATE wp_attendance SET stadium_id = %d, stadium_name = %s WHERE stadium_name = %s",
                    $sid, $good, $bad
                ));
                $updates[$bad] = ['sid' => $sid, 'good' => $good, 'rows' => (int) $rows];
            }
            // Also catch any rows that have a stadium_name matching an existing facility_names but stadium_id missing
            $orphans = $wpdb->query(
                "UPDATE wp_attendance a
                 INNER JOIN wp_stadiums s ON s.facility_names = a.stadium_name
                 SET a.stadium_id = s.id
                 WHERE a.stadium_id IS NULL OR a.stadium_id = 0"
            );
            return rest_ensure_response(['aliases_updated' => $updates, 'orphans_fixed' => (int) $orphans]);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/attendance-debug', [
        'methods'             => 'GET',
        'callback'            => function() {
            global $wpdb;
            $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM wp_attendance");
            $null_sid = (int) $wpdb->get_var("SELECT COUNT(*) FROM wp_attendance WHERE stadium_id IS NULL");
            $by_year = $wpdb->get_results(
                "SELECT year, COUNT(*) AS games, ROUND(AVG(attendance_pct), 2) AS avg_pct
                 FROM wp_attendance GROUP BY year ORDER BY year",
                ARRAY_A
            );
            $unmatched = $wpdb->get_results(
                "SELECT DISTINCT home_team, stadium_name FROM wp_attendance WHERE stadium_id IS NULL LIMIT 30",
                ARRAY_A
            );
            $by_sid = $wpdb->get_results(
                "SELECT a.stadium_id, a.home_team, COUNT(*) AS games, s.facility_names,
                        MIN(a.stadium_name) AS sample_name
                 FROM wp_attendance a
                 LEFT JOIN wp_stadiums s ON s.id = a.stadium_id
                 GROUP BY a.stadium_id, a.home_team
                 ORDER BY a.home_team, a.stadium_id",
                ARRAY_A
            );
            $sample_names = $wpdb->get_results(
                "SELECT home_team, stadium_name, COUNT(*) AS cnt FROM wp_attendance
                 WHERE (stadium_id IS NULL OR stadium_id = 0)
                 GROUP BY home_team, stadium_name",
                ARRAY_A
            );
            return rest_ensure_response([
                'total' => $total,
                'null_stadium_id' => $null_sid,
                'unmatched_stadium_names' => $unmatched,
                'sample_unmatched' => $sample_names,
                'by_year' => $by_year,
                'by_stadium' => $by_sid,
                'all_facility_names' => $wpdb->get_col("SELECT facility_names FROM wp_stadiums ORDER BY facility_names"),
            ]);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/team-timeline', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_team_timeline',
        'permission_callback' => '__return_true',
    ]);
});


function pfl_api_teams_list(WP_REST_Request $request) {
    global $wpdb;
    $name_rows = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    $name_map  = [];
    foreach ($name_rows as $r) $name_map[$r['team_int']] = $r['team'];

    $all_teams = $wpdb->get_col("SELECT DISTINCT team FROM wp_drafts ORDER BY team ASC");
    $result = [];
    foreach ($all_teams as $t) {
        $result[] = ['team' => $t, 'name' => $name_map[$t] ?? $t];
    }
    return rest_ensure_response($result);
}

function pfl_api_team(WP_REST_Request $request) {
    global $wpdb;

    $team = strtoupper(sanitize_text_field($request->get_param('id') ?? ''));
    if (!$team) return new WP_Error('missing_id', 'Team id required', ['status' => 400]);

    $theme_uri = get_stylesheet_directory_uri();

    // ── Team info ──────────────────────────────────────────────────────────────
    $team_row = $wpdb->get_row($wpdb->prepare(
        "SELECT team_int, team, owner FROM wp_teams WHERE team_int = %s LIMIT 1", $team
    ), ARRAY_A);

    $helm_num   = pfl_get_helmet_num($team, (int) date('Y'));
    $helmet_url = $theme_uri . '/img/helmets/final-renders/' . $team . '/helmet-' . $team . '-' . $helm_num . '-front.png';

    // ── Season standings + career record ──────────────────────────────────────
    $standings    = [];
    $total_wins   = 0;
    $total_losses = 0;

    foreach (range(1991, (int) date('Y')) as $yr) {
        $tbl = "stand{$yr}";
        if ($wpdb->get_var("SHOW TABLES LIKE '{$tbl}'") !== $tbl) continue;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$tbl} WHERE teamid = %s LIMIT 1", $team
        ), ARRAY_N);
        if (!$row) continue;

        $win  = (int) $row[6];
        $loss = (int) $row[7];
        $total_wins   += $win;
        $total_losses += $loss;

        $standings[] = [
            'year'      => $yr,
            'win'       => $win,
            'loss'      => $loss,
            'seed'      => (int) $row[2],
            'division'  => $row[3] ?? '',
            'pts'       => (float) $row[10],
            'ptsvs'     => (float) $row[12],
            'ptsDiff'   => round((float) $row[10] - (float) $row[12], 1),
            'gb'        => (float) ($row[9] ?? 0),
            'divRecord' => ((int) ($row[14] ?? 0)) . '-' . ((int) ($row[15] ?? 0)),
        ];
    }

    $games_played = $total_wins + $total_losses;
    $win_pct      = $games_played > 0 ? round($total_wins / $games_played, 3) : 0;

    // ── Championships ─────────────────────────────────────────────────────────
    $champ_years = array_map('intval', $wpdb->get_col($wpdb->prepare(
        "SELECT year FROM wp_champions WHERE winTeam = %s ORDER BY year ASC", $team
    )));

    // ── Awards (all except HOF) ───────────────────────────────────────────────
    $award_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT award, year, playerFirst, playerLast, pid FROM wp_awards WHERE team = %s AND award != 'Hall of Fame Inductee' ORDER BY year ASC",
        $team
    ), ARRAY_A);

    $award_key_map = [
        'Most Valuable Player' => 'mvp',
        'Rookie of the Year'   => 'rookie',
        'Posse Bowl MVP'        => 'possebowlMvp',
        'Pro Bowl MVP'          => 'probowlMvp',
        'Owner of the Year'    => 'ownerOfYear',
    ];
    $awards = ['mvp' => [], 'rookie' => [], 'possebowlMvp' => [], 'probowlMvp' => [], 'ownerOfYear' => []];
    $owner_full = $team_row['owner'] ?? '';
    $owner_parts = explode(' ', trim($owner_full), 2);
    $owner_first = $owner_parts[0] ?? '';
    $owner_last  = $owner_parts[1] ?? '';
    foreach ($award_rows as $r) {
        $key = $award_key_map[$r['award']] ?? null;
        if (!$key) continue;
        if ($key === 'ownerOfYear' && empty($r['pid'])) {
            $awards[$key][] = ['year' => (int) $r['year'], 'pid' => 'owner_' . $team, 'first' => $owner_first, 'last' => $owner_last, 'img' => null];
        } else {
            $awards[$key][] = ['year' => (int) $r['year'], 'pid' => $r['pid'], 'first' => $r['playerFirst'], 'last' => $r['playerLast'], 'img' => pfl_player_img_url($r['pid'])];
        }
    }

    // ── Hall of Fame ─────────────────────────────────────────────────────────
    $hof_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT year, pid, playerFirst, playerLast FROM wp_awards WHERE award = 'Hall of Fame Inductee' AND team = %s ORDER BY year ASC",
        $team
    ), ARRAY_A);
    foreach ($hof_rows as &$h) {
        $h['year']  = (int) $h['year'];
        $h['first'] = $h['playerFirst'];
        $h['last']  = $h['playerLast'];
        $h['img']   = pfl_player_img_url($h['pid']);
        unset($h['playerFirst'], $h['playerLast']);
    }

    // ── Playoffs ─────────────────────────────────────────────────────────────
    $po_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT year, week, points, versus, result FROM wp_playoffs WHERE team = %s ORDER BY year ASC, week ASC",
        $team
    ), ARRAY_A);

    $po_seen = [];
    $po_wins = 0; $po_losses = 0;
    $po_games = [];
    foreach ($po_rows as $g) {
        $key = $g['year'] . '-' . $g['week'];
        if (isset($po_seen[$key])) continue;
        $po_seen[$key] = true;
        $result = (int) $g['result']; // 1 = win, 0 = loss
        if ($result === 1) $po_wins++;
        else $po_losses++;
        $po_games[] = ['year' => (int) $g['year'], 'week' => (int) $g['week'], 'versus' => $g['versus'], 'result' => $result === 1 ? 'W' : 'L'];
    }

    // ── Head-to-Head (regular season) ─────────────────────────────────────────
    $team_table = "wp_team_{$team}";
    $hth_data   = [];
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $team_table)) === $team_table) {
        $games = $wpdb->get_results(
            "SELECT * FROM {$team_table} WHERE week BETWEEN 1 AND 14",
            ARRAY_N
        );
        foreach ($games as $g) {
            // ARRAY_N: [4]=points [5]=versus [6]=versus_pts [9]=result
            $opp = $g[5];
            if (!isset($hth_data[$opp])) $hth_data[$opp] = ['wins' => 0, 'losses' => 0, 'pf' => 0.0, 'pa' => 0.0, 'games' => 0];
            $r = (float) $g[9];
            if ($r > 0) $hth_data[$opp]['wins']++;
            elseif ($r < 0) $hth_data[$opp]['losses']++;
            $hth_data[$opp]['pf'] += (float) $g[4];
            $hth_data[$opp]['pa'] += (float) $g[6];
            $hth_data[$opp]['games']++;
        }
    }
    $name_rows = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    $team_names = [];
    foreach ($name_rows as $r) $team_names[$r['team_int']] = $r['team'];
    $hth_list = [];
    foreach ($hth_data as $opp => $d) {
        $hth_list[] = ['opp' => $opp, 'oppName' => $team_names[$opp] ?? $opp, 'wins' => $d['wins'], 'losses' => $d['losses'], 'games' => $d['games'], 'ptsDiff' => round($d['pf'] - $d['pa'], 1), 'pf' => round($d['pf'], 1), 'pa' => round($d['pa'], 1)];
    }
    usort($hth_list, fn($a, $b) => strcmp($a['opp'], $b['opp']));

    // ── Pro Bowl ─────────────────────────────────────────────────────────────
    $pb_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT playerid, position, year, starter FROM wp_probowlbox WHERE team = %s ORDER BY year ASC",
        $team
    ), ARRAY_A);
    $pb_pids = array_values(array_unique(array_column($pb_rows, 'playerid')));
    $pb_names = [];
    if (!empty($pb_pids)) {
        $ph = implode(',', array_fill(0, count($pb_pids), '%s'));
        foreach ($wpdb->get_results($wpdb->prepare("SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ($ph)", ...$pb_pids), ARRAY_A) as $r) {
            $pb_names[$r['p_id']] = trim($r['playerFirst'] . ' ' . $r['playerLast']);
        }
    }
    $probowl_by_year = [];
    foreach ($pb_rows as $r) {
        $yr = (int) $r['year'];
        if (!isset($probowl_by_year[$yr])) $probowl_by_year[$yr] = [];
        $probowl_by_year[$yr][] = ['pid' => $r['playerid'], 'name' => $pb_names[$r['playerid']] ?? $r['playerid'], 'pos' => $r['position'], 'starter' => (bool) $r['starter']];
    }
    ksort($probowl_by_year);
    $probowl = [];
    foreach ($probowl_by_year as $yr => $players) {
        $probowl[] = ['year' => $yr, 'players' => $players];
    }

    // ── Overtime ─────────────────────────────────────────────────────────────
    $ot_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM wp_overtime WHERE winteam = %s OR loseteam = %s ORDER BY id ASC",
        $team, $team
    ), ARRAY_N);
    $ot_wins = 0; $ot_losses = 0; $ot_games = [];
    foreach ($ot_rows as $r) {
        $won = ($r[1] === $team);
        $won ? $ot_wins++ : $ot_losses++;
        $id = (string) $r[0];
        $ot_games[] = ['year' => (int) substr($id, 0, 4), 'week' => (int) floor((int) substr($id, 4) / 100), 'winner' => $r[1], 'loser' => $r[2], 'result' => $won ? 'W' : 'L', 'eot' => !empty($r[12])];
    }

    // ── Ring of Honor ─────────────────────────────────────────────────────────
    $roh_pids = $wpdb->get_col($wpdb->prepare(
        "SELECT pm_pid.meta_value
         FROM wp_postmeta pm_pid
         JOIN wp_postmeta pm_team
           ON pm_team.post_id = pm_pid.post_id
           AND REPLACE(pm_pid.meta_key, '_player_id', '_team') = pm_team.meta_key
         WHERE pm_pid.meta_key LIKE 'honored_player_%_player_id'
           AND pm_team.meta_value = %s",
        $team
    ));
    $ring_of_honor = [];
    if (!empty($roh_pids)) {
        $ph = implode(',', array_fill(0, count($roh_pids), '%s'));
        $roh_rows = $wpdb->get_results(
            $wpdb->prepare("SELECT p_id, playerFirst, playerLast, number FROM wp_players WHERE p_id IN ($ph)", ...$roh_pids),
            ARRAY_A
        );
        foreach ($roh_rows as $r) {
            $ring_of_honor[] = ['pid' => $r['p_id'], 'first' => $r['playerFirst'], 'last' => $r['playerLast'], 'number' => (int) $r['number']];
        }
        usort($ring_of_honor, fn($a, $b) => $a['number'] <=> $b['number']);
    }

    return rest_ensure_response([
        'info'          => ['team' => $team, 'name' => $team_row['team'] ?? $team, 'owner' => $team_row['owner'] ?? '', 'wins' => $total_wins, 'losses' => $total_losses, 'winPct' => $win_pct, 'helmetUrl' => $helmet_url],
        'championships' => $champ_years,
        'standings'     => $standings,
        'awards'        => $awards,
        'hof'           => $hof_rows,
        'playoffs'      => ['wins' => $po_wins, 'losses' => $po_losses, 'appearances' => count(array_unique(array_column($po_games, 'year'))), 'games' => $po_games],
        'headToHead'    => $hth_list,
        'probowl'       => $probowl,
        'overtime'      => ['wins' => $ot_wins, 'losses' => $ot_losses, 'games' => $ot_games],
        'stadium'       => pfl_get_stadium_data($team),
        'ringOfHonor'   => $ring_of_honor,
    ]);
}

function pfl_get_stadium_data($team) {
    global $wpdb;

    // Columns: [1]=facility_names [2]=team_owned [3]=year_built [5]=turf [6]=capacity [8]=lux_suites [9]=club_suites [12]=currently_occupied
    // Also match `{TEAM}_OLD` so historical rows (e.g. BST_OLD) show up under
    // the team's "Previous Facilities" list.
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM wp_stadiums WHERE team_owned = %s OR team_owned = %s ORDER BY id ASC",
        $team, $team . '_OLD'
    ), ARRAY_N);
    if (empty($rows)) return null;

    $current = null;
    $previous = [];
    foreach ($rows as $r) {
        $item = [
            'name'    => $r[1],
            'opened'  => $r[3],
            'surface' => $r[5],
            'seats'   => (int) ($r[6] ?? 0),
            'club'    => (int) ($r[9] ?? 0),
            'box'     => (int) ($r[8] ?? 0),
        ];
        if ((int) ($r[12] ?? 0) === 1) {
            $current = $item;
        } else {
            $previous[] = $item;
        }
    }

    // ACF stadium repeater lives on post 299 — find this team's row index
    $STADIUM_POST_ID = 299;
    $image_urls = [];
    $notes      = null;
    $idx_row = $wpdb->get_row($wpdb->prepare(
        "SELECT meta_key FROM wp_postmeta WHERE post_id = %d AND meta_key LIKE 'stadium_%%_team' AND meta_value = %s LIMIT 1",
        $STADIUM_POST_ID, $team
    ), ARRAY_A);
    if ($idx_row && preg_match('/stadium_(\d+)_team/', $idx_row['meta_key'], $m)) {
        $idx = $m[1];

        $raw = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM wp_postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $STADIUM_POST_ID, "stadium_{$idx}_image"
        ));
        if ($raw) {
            $val = maybe_unserialize($raw);
            if (is_array($val)) {
                foreach ($val as $aid) {
                    $u = wp_get_attachment_url((int) $aid);
                    if ($u) $image_urls[] = $u;
                }
            } elseif ((int) $val > 0) {
                $u = wp_get_attachment_url((int) $val);
                if ($u) $image_urls[] = $u;
            }
        }

        $notes = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM wp_postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $STADIUM_POST_ID, "stadium_{$idx}_notes"
        )) ?: null;
    }

    // ── Posse Bowls hosted at any of this team's stadiums ───────────────────
    $all_names = array_filter(array_merge(
        $current   ? [$current['name']]      : [],
        array_column($previous, 'name')
    ));
    $posse_bowls_hosted = [];
    if (!empty($all_names)) {
        $ph       = implode(',', array_fill(0, count($all_names), '%s'));
        $pb_rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT year, roman, stadium FROM wp_champions WHERE stadium IN ($ph) ORDER BY year ASC",
                ...$all_names
            ),
            ARRAY_A
        );
        foreach ($pb_rows as $pb) {
            $posse_bowls_hosted[] = [
                'year'    => (int) $pb['year'],
                'roman'   => $pb['roman'],
                'stadium' => $pb['stadium'],
            ];
        }
    }

    return [
        'current'          => $current,
        'previous'         => $previous,
        'imageUrls'        => $image_urls,
        'notes'            => $notes,
        'posseBowlsHosted' => $posse_bowls_hosted,
    ];
}

function pfl_api_team_leaders(WP_REST_Request $request) {
    global $wpdb;

    $team = strtoupper(sanitize_text_field($request->get_param('id') ?? ''));
    if (!$team) return new WP_Error('missing_id', 'Team id required', ['status' => 400]);

    $team_table = 'wp_team_' . $team;
    if (!$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $team_table))) {
        return rest_ensure_response(['QB' => [], 'RB' => [], 'WR' => [], 'PK' => []]);
    }

    // Get every distinct (season, playerid) from the team's weekly lineup.
    // Columns qb1/qb2/rb1/rb2/wr1/wr2/pk1/pk2 hold player IDs each week.
    $rows = $wpdb->get_results("
        SELECT DISTINCT season, qb1 AS pid FROM `{$team_table}` WHERE qb1 IS NOT NULL AND qb1 != ''
        UNION SELECT DISTINCT season, qb2 FROM `{$team_table}` WHERE qb2 IS NOT NULL AND qb2 != ''
        UNION SELECT DISTINCT season, rb1 FROM `{$team_table}` WHERE rb1 IS NOT NULL AND rb1 != ''
        UNION SELECT DISTINCT season, rb2 FROM `{$team_table}` WHERE rb2 IS NOT NULL AND rb2 != ''
        UNION SELECT DISTINCT season, wr1 FROM `{$team_table}` WHERE wr1 IS NOT NULL AND wr1 != ''
        UNION SELECT DISTINCT season, wr2 FROM `{$team_table}` WHERE wr2 IS NOT NULL AND wr2 != ''
        UNION SELECT DISTINCT season, pk1 FROM `{$team_table}` WHERE pk1 IS NOT NULL AND pk1 != ''
        UNION SELECT DISTINCT season, pk2 FROM `{$team_table}` WHERE pk2 IS NOT NULL AND pk2 != ''
    ", ARRAY_A);

    if (empty($rows)) return rest_ensure_response(['QB' => [], 'RB' => [], 'WR' => [], 'PK' => []]);

    // Group seasons by playerid (deduplicated)
    $player_seasons = [];
    foreach ($rows as $r) {
        $player_seasons[$r['pid']][(int) $r['season']] = true;
    }

    // For each player, sum career points/games from wp_season_leaders
    // for only the seasons they were in this team's lineup
    $leaders = [];
    foreach ($player_seasons as $pid => $seasons_map) {
        $seasons = array_keys($seasons_map);
        $ph      = implode(',', array_fill(0, count($seasons), '%d'));
        $stats   = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT SUM(points) AS pts, SUM(games) AS games, MAX(points) AS high
                 FROM wp_season_leaders WHERE playerid = %s AND season IN ($ph)",
                array_merge([$pid], $seasons)
            ),
            ARRAY_A
        );
        if (!$stats || (int) $stats['games'] === 0) continue;
        $pos = strtoupper(substr($pid, -2));
        if (!in_array($pos, ['QB', 'RB', 'WR', 'PK'])) continue;
        $leaders[] = [
            'pid'    => $pid,
            'pos'    => $pos,
            'points' => (float) $stats['pts'],
            'games'  => (int)   $stats['games'],
            'ppg'    => round((float) $stats['pts'] / max(1, (int) $stats['games']), 1),
            'high'   => (float) $stats['high'],
        ];
    }

    // Attach player names
    $all_pids = array_column($leaders, 'pid');
    $names    = [];
    if (!empty($all_pids)) {
        $ph = implode(',', array_fill(0, count($all_pids), '%s'));
        foreach ($wpdb->get_results(
            $wpdb->prepare("SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ($ph)", ...$all_pids),
            ARRAY_A
        ) as $r) {
            $names[$r['p_id']] = ['first' => $r['playerFirst'], 'last' => $r['playerLast']];
        }
    }
    foreach ($leaders as &$l) {
        $l['first'] = $names[$l['pid']]['first'] ?? '';
        $l['last']  = $names[$l['pid']]['last'] ?? '';
    }

    // Group by position, top 8 by total points
    $by_pos = ['QB' => [], 'RB' => [], 'WR' => [], 'PK' => []];
    foreach ($leaders as $l) {
        if (isset($by_pos[$l['pos']])) $by_pos[$l['pos']][] = $l;
    }
    foreach ($by_pos as &$group) {
        usort($group, fn($a, $b) => $b['points'] <=> $a['points']);
        $group = array_slice($group, 0, 8);
    }

    return rest_ensure_response($by_pos);
}

function pfl_api_stadiums() {
    global $wpdb;

    // All stadium rows joined with team name
    $rows = $wpdb->get_results("
        SELECT s.id, s.facility_names, s.team_owned, s.year_built, s.year_renovated,
               s.turf, s.capacity, s.capacity_before_reno, s.lux_suites, s.club_suites,
               s.parking, s.total_cost, s.currently_occupied, s.roof_type, s.region,
               t.team AS team_name
        FROM wp_stadiums s
        LEFT JOIN wp_teams t ON t.team_int = s.team_owned
        ORDER BY s.year_built DESC, s.id ASC
    ", ARRAY_A);

    // Build ACF image map: key → [url, ...]
    // Keys are either a team abbreviation (e.g. "ETS") for current stadiums
    // or a team abbreviation with _OLD suffix (e.g. "ETS_OLD") for former ones.
    $STADIUM_POST_ID = 299;
    $acf_meta = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = %d AND meta_key LIKE 'stadium_%_team'",
        $STADIUM_POST_ID
    ), ARRAY_A);
    $team_images = [];
    foreach ($acf_meta as $acf) {
        if (!preg_match('/stadium_(\d+)_team/', $acf['meta_key'], $m)) continue;
        $idx  = $m[1];
        $key  = $acf['meta_value']; // e.g. "ETS" or "ETS_OLD"
        $raw  = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM wp_postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $STADIUM_POST_ID, "stadium_{$idx}_image"
        ));
        if (!$raw) continue;
        $val  = maybe_unserialize($raw);
        $urls = [];
        if (is_array($val)) {
            foreach ($val as $aid) { $u = wp_get_attachment_url((int) $aid); if ($u) $urls[] = $u; }
        } elseif ((int) $val > 0) {
            $u = wp_get_attachment_url((int) $val); if ($u) $urls[] = $u;
        }
        $team_images[$key] = $urls;
    }

    // Build Posse Bowl map: stadium_name → [{year, roman}]
    $pb_rows = $wpdb->get_results(
        "SELECT year, roman, stadium FROM wp_champions WHERE stadium IS NOT NULL AND stadium != '' ORDER BY year ASC",
        ARRAY_A
    );
    $pb_map = [];
    foreach ($pb_rows as $pb) {
        $pb_map[$pb['stadium']][] = ['year' => (int) $pb['year'], 'roman' => $pb['roman']];
    }

    // Teams that have an active (current) stadium — used for image suppression
    // and to derive closed years for former stadiums. Keyed by base team (so
    // `BST_OLD` rows can correctly see that `BST` has a current stadium).
    $teams_with_current   = [];
    $current_stadium_year = []; // team → year_built of their current stadium
    foreach ($rows as $r) {
        if ((int) ($r['currently_occupied'] ?? 0) === 1) {
            $bt = pfl_base_team($r['team_owned']);
            $teams_with_current[$bt]   = true;
            $current_stadium_year[$bt] = (int) $r['year_built'];
        }
    }

    // Collect all base teams (for gap detection on hiatus teams like BST_OLD).
    // A team is "defunct" if no row with currently_occupied=1 exists for the
    // base team — that includes both fully-defunct teams AND base teams that
    // only have an _OLD row.
    $defunct_teams = [];
    foreach ($rows as $r) {
        $bt = pfl_base_team($r['team_owned']);
        if (empty($teams_with_current[$bt])) {
            $defunct_teams[$bt] = true;
        } elseif ($r['team_owned'] !== $bt) {
            // The row is itself _OLD even though the base team has a current
            // stadium — we still need year-list/gap data for it.
            $defunct_teams[$bt] = true;
        }
    }

    // Standings pass: collect defunct-team year lists AND per-year seeds for
    // all teams (seeds are needed to identify the home team in playoff games).
    $team_years_active = []; // defunct team → [years]
    $year_seeds        = []; // year → team → seed (int, lower = better)
    for ($yr = 1991; $yr <= (int) date('Y'); $yr++) {
        $tbl = "stand{$yr}";
        if ($wpdb->get_var("SHOW TABLES LIKE '{$tbl}'") !== $tbl) continue;
        $srows = $wpdb->get_results("SELECT teamID AS teamid, playoff_seed AS seed FROM {$tbl}", ARRAY_A);
        foreach ($srows as $sr) {
            $t = $sr['teamid'];
            $year_seeds[$yr][$t] = (int) $sr['seed'];
            if (isset($defunct_teams[$t])) {
                $team_years_active[$t][] = $yr;
            }
        }
    }

    // Closed year for defunct teams: last year before first gap > 2 years
    $team_last_year = [];
    foreach ($team_years_active as $t => $years) {
        sort($years);
        $last = $years[0];
        for ($i = 1; $i < count($years); $i++) {
            if ($years[$i] - $years[$i - 1] > 2) break;
            $last = $years[$i];
        }
        $team_last_year[$t] = $last;
    }

    // All team names for high-score lookups
    $all_team_names = [];
    foreach ($wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A) as $nr) {
        $all_team_names[$nr['team_int']] = $nr['team'];
    }

    // Home game counts + W-L + full game rows for high-score analysis.
    // Keyed by BASE team — wp_team_BST_OLD doesn't exist, but wp_team_BST does.
    $reg_games      = []; // team → season → ['games' => N, 'wins' => N]
    $all_home_games = []; // team → [[season, week, versus, points, versus_pts], ...]
    $all_teams = array_unique(array_map('pfl_base_team', array_column($rows, 'team_owned')));
    foreach ($all_teams as $t) {
        $tbl = "wp_team_{$t}";
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tbl)) !== $tbl) continue;
        $home_rows = $wpdb->get_results(
            "SELECT season,
                    COUNT(*) AS cnt,
                    SUM(CASE WHEN result > 0 THEN 1 ELSE 0 END) AS wins
             FROM `{$tbl}`
             WHERE home_away = 'H' AND week BETWEEN 1 AND 14
             GROUP BY season",
            ARRAY_A
        );
        foreach ($home_rows as $hr) {
            $reg_games[$t][(int) $hr['season']] = [
                'games' => (int) $hr['cnt'],
                'wins'  => (int) $hr['wins'],
            ];
        }
        $all_home_games[$t] = $wpdb->get_results(
            "SELECT season, week, vs, points, vs_points,
                    QB1, RB1, WR1, PK1, QB2, RB2, WR2, PK2 FROM `{$tbl}`
             WHERE home_away = 'H' AND week BETWEEN 1 AND 14",
            ARRAY_A
        );
    }

    // Pre-compute closed year for every stadium row so we can build the
    // year → team → stadium_id map needed for playoff host lookups.
    // For _OLD rows we always defer to gap-detected $team_last_year so a team
    // that took a hiatus (e.g. BST 2012-2018) closes at the last pre-gap year.
    $closed_by_id = []; // stadium id → closed year (null = still open)
    foreach ($rows as $r) {
        $is_cur     = (int) ($r['currently_occupied'] ?? 0) === 1;
        $base       = pfl_base_team($r['team_owned']);
        $is_old_row = ($r['team_owned'] !== $base);
        if ($is_cur) {
            $closed_by_id[(int) $r['id']] = null;
        } elseif ($is_old_row) {
            $closed_by_id[(int) $r['id']] = $team_last_year[$base] ?? null;
        } elseif (!empty($teams_with_current[$base])) {
            $closed_by_id[(int) $r['id']] = isset($current_stadium_year[$base])
                ? $current_stadium_year[$base] - 1 : null;
        } else {
            $closed_by_id[(int) $r['id']] = $team_last_year[$base] ?? null;
        }
    }

    // Build year → team → stadium_id from each stadium's active year range.
    // Keyed by BASE team so playoff/road-game lookups (which use base team)
    // find the correct historical stadium for that year.
    $year_team_stadium = []; // year → base_team → stadium_id
    foreach ($rows as $r) {
        $opened = max((int) ($r['year_built'] ?? 1991), 1991);
        $closed = $closed_by_id[(int) $r['id']] ?? (int) date('Y');
        $base   = pfl_base_team($r['team_owned']);
        for ($yr = $opened; $yr <= $closed; $yr++) {
            $year_team_stadium[$yr][$base] = (int) $r['id'];
        }
    }

    // Playoff scores: team → year → total points scored
    $po_scores = [];
    foreach ($wpdb->get_results(
        "SELECT team, year, SUM(points) AS pts FROM wp_playoffs WHERE week = 15 GROUP BY team, year",
        ARRAY_A
    ) as $sr) {
        $po_scores[$sr['team']][(int) $sr['year']] = (float) $sr['pts'];
    }

    // Playoff games hosted per stadium: home = better (lower) seed.
    // Tracks W-L and collects game details for high-score analysis.
    $playoff_by_stadium  = []; // stadium_id → ['games'=>N,'wins'=>N,'losses'=>N]
    $po_games_at_stadium = []; // stadium_id → [{year,home,away,home_pts,away_pts}]
    $po_pairs = $wpdb->get_results(
        "SELECT team, versus, year, MAX(result) AS result
         FROM wp_playoffs WHERE week = 15
         GROUP BY team, versus, year",
        ARRAY_A
    );
    $po_result_map = [];
    foreach ($po_pairs as $g) {
        $po_result_map[$g['year'] . '_' . $g['team'] . '_' . $g['versus']] = (int) $g['result'];
    }
    $seen_po = [];
    foreach ($po_pairs as $g) {
        $yr  = (int) $g['year'];
        $t1  = $g['team'];
        $t2  = $g['versus'];
        $key = $yr . '_' . min($t1, $t2) . '_' . max($t1, $t2);
        if (isset($seen_po[$key])) continue;
        $seen_po[$key] = true;
        $s1   = $year_seeds[$yr][$t1] ?? 99;
        $s2   = $year_seeds[$yr][$t2] ?? 99;
        $home = ($s1 <= $s2) ? $t1 : $t2;
        $away = ($s1 <= $s2) ? $t2 : $t1;
        $sid  = $year_team_stadium[$yr][$home] ?? null;
        if (!$sid) continue;
        $home_won = ($po_result_map[$yr . '_' . $home . '_' . $away] ?? 0) === 1;
        if (!isset($playoff_by_stadium[$sid])) {
            $playoff_by_stadium[$sid] = ['games' => 0, 'wins' => 0, 'losses' => 0];
        }
        $playoff_by_stadium[$sid]['games']++;
        if ($home_won) $playoff_by_stadium[$sid]['wins']++;
        else           $playoff_by_stadium[$sid]['losses']++;
        $po_games_at_stadium[$sid][] = [
            'year'      => $yr,
            'week'      => 15,
            'home'      => $home,
            'away'      => $away,
            'home_pts'  => $po_scores[$home][$yr] ?? 0,
            'away_pts'  => $po_scores[$away][$yr] ?? 0,
        ];
    }

    // Highest individual team score in a regular-season home game at each stadium.
    $high_score_by_stadium = []; // stadium_id → game record
    foreach ($rows as $r) {
        $sid    = (int) $r['id'];
        $team   = pfl_base_team($r['team_owned']);
        $opened = max((int) ($r['year_built'] ?? 1991), 1991);
        $closed = $closed_by_id[$sid] ?? (int) date('Y');
        $best   = null;

        foreach ($all_home_games[$team] ?? [] as $g) {
            $yr = (int) $g['season'];
            if ($yr < $opened || $yr > $closed) continue;
            foreach ([
                [$team, $g['vs'], (float) $g['points']],
                [$g['vs'], $team, (float) $g['vs_points']],
            ] as [$scorer, $opp, $pts]) {
                if (!$best || $pts > $best['points']) {
                    $best = ['team' => $scorer, 'teamName' => $all_team_names[$scorer] ?? $scorer,
                             'points' => $pts, 'versus' => $opp,
                             'versusName' => $all_team_names[$opp] ?? $opp,
                             'week' => (int) $g['week'], 'year' => $yr];
                }
            }
        }

        $high_score_by_stadium[$sid] = $best;
    }

    // Per-position high scores at each stadium (regular season, both teams).
    $rows_by_sid = [];
    foreach ($rows as $r) { $rows_by_sid[(int) $r['id']] = $r; }
    $stadium_id_by_name = [];
    foreach ($rows as $r) { $stadium_id_by_name[$r['facility_names']] = (int) $r['id']; }

    $player_apps = []; // sid → pos → pid → [week_id, ...]

    $pos_cols = ['QB1' => 'QB', 'RB1' => 'RB', 'WR1' => 'WR', 'PK1' => 'PK',
                 'QB2' => 'QB', 'RB2' => 'RB', 'WR2' => 'WR', 'PK2' => 'PK'];

    // Home team players
    foreach ($rows as $r) {
        $sid    = (int) $r['id'];
        $team   = pfl_base_team($r['team_owned']);
        $opened = max((int) ($r['year_built'] ?? 1991), 1991);
        $closed = $closed_by_id[$sid] ?? (int) date('Y');
        foreach ($all_home_games[$team] ?? [] as $g) {
            $yr = (int) $g['season'];
            if ($yr < $opened || $yr > $closed) continue;
            $wid = sprintf('%04d%02d', $yr, (int) $g['week']);
            foreach ($pos_cols as $col => $pos) {
                $pid = trim($g[$col] ?? '');
                if ($pid) $player_apps[$sid][$pos][$pid][] = $wid;
            }
        }
    }

    // Away (visiting) team players — collect (sid, away_team, season, week)
    // tuples from each stadium's home games, then look up the away team's
    // lineup by (season, week) instead of relying on the away row's stadium column.
    $away_lookups = []; // away_team → [['sid' => ..., 'season' => ..., 'week' => ...], ...]
    foreach ($rows as $r) {
        $sid    = (int) $r['id'];
        $team   = pfl_base_team($r['team_owned']);
        $opened = max((int) ($r['year_built'] ?? 1991), 1991);
        $closed = $closed_by_id[$sid] ?? (int) date('Y');
        foreach ($all_home_games[$team] ?? [] as $g) {
            $yr = (int) $g['season'];
            if ($yr < $opened || $yr > $closed) continue;
            $away = trim($g['vs'] ?? '');
            if (!$away) continue;
            $away_lookups[$away][] = ['sid' => $sid, 'season' => $yr, 'week' => (int) $g['week']];
        }
    }

    foreach ($away_lookups as $away_team => $games) {
        $tbl = "wp_team_{$away_team}";
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tbl)) !== $tbl) continue;

        // Index lookups by season+week so we can map rows back to stadium IDs
        $sid_by_sw = [];
        foreach ($games as $g) {
            $sid_by_sw[$g['season'] . '_' . $g['week']] = $g['sid'];
        }
        $conds = [];
        foreach ($games as $g) {
            $conds[] = '(season = ' . (int) $g['season'] . ' AND week = ' . (int) $g['week'] . ')';
        }
        $where = implode(' OR ', array_unique($conds));

        $away_rows = $wpdb->get_results(
            "SELECT season, week, QB1, RB1, WR1, PK1, QB2, RB2, WR2, PK2 FROM `{$tbl}`
             WHERE ({$where}) AND home_away <> 'H' AND week BETWEEN 1 AND 14",
            ARRAY_A
        );
        foreach ($away_rows as $g) {
            $key = $g['season'] . '_' . $g['week'];
            $sid = $sid_by_sw[$key] ?? null;
            if (!$sid) continue;
            $wid = sprintf('%04d%02d', (int) $g['season'], (int) $g['week']);
            foreach ($pos_cols as $col => $pos) {
                $pid = trim($g[$col] ?? '');
                if ($pid) $player_apps[$sid][$pos][$pid][] = $wid;
            }
        }
    }

    // For each (sid, pos), find the highest score across all players and
    // collect ALL (pid, week, year, team) entries tied at that score.
    $pos_bests    = []; // sid → pos → [['pid', 'points', 'year', 'week', 'team'], ...]
    $names_needed = [];
    foreach ($player_apps as $sid => $by_pos) {
        foreach ($by_pos as $pos => $pid_wids) {
            // First pass: per-player, get max score + all tied games at that player's max
            $per_player = []; // pid => ['max' => float, 'games' => [['year','week','team'], ...]]
            foreach ($pid_wids as $pid => $wids) {
                $safe_pid = esc_sql($pid);
                $in_list  = implode("','", array_map('esc_sql', array_unique($wids)));
                $games = $wpdb->get_results(
                    "SELECT year, week, team, points FROM `{$safe_pid}`
                     WHERE week_id IN ('{$in_list}') AND week BETWEEN 1 AND 14
                       AND points = (
                         SELECT MAX(points) FROM `{$safe_pid}`
                         WHERE week_id IN ('{$in_list}') AND week BETWEEN 1 AND 14
                       )",
                    ARRAY_A
                );
                if (empty($games)) continue;
                $per_player[$pid] = [
                    'max'   => (float) $games[0]['points'],
                    'games' => $games,
                ];
            }

            // Global max across all players for this (sid, pos)
            $global_max = null;
            foreach ($per_player as $d) {
                if ($global_max === null || $d['max'] > $global_max) $global_max = $d['max'];
            }
            if ($global_max === null) continue;

            $entries = [];
            foreach ($per_player as $pid => $d) {
                if (abs($d['max'] - $global_max) > 0.0001) continue;
                foreach ($d['games'] as $g) {
                    $entries[] = [
                        'pid'    => $pid,
                        'points' => $global_max,
                        'year'   => (int) $g['year'],
                        'week'   => (int) $g['week'],
                        'team'   => $g['team'] ?? '',
                    ];
                    $names_needed[$pid] = true;
                }
            }
            // Sort tied entries by year/week for stable display
            usort($entries, fn($a, $b) => [$a['year'], $a['week']] <=> [$b['year'], $b['week']]);
            $pos_bests[$sid][$pos] = $entries;
        }
    }

    // Batch-fetch player names
    $pos_player_names = [];
    if (!empty($names_needed)) {
        $pid_list    = array_keys($names_needed);
        $ph          = implode(',', array_fill(0, count($pid_list), '%s'));
        $name_rows   = $wpdb->get_results(
            $wpdb->prepare("SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ($ph)", ...$pid_list),
            ARRAY_A
        );
        foreach ($name_rows as $p) {
            $pos_player_names[$p['p_id']] = trim($p['playerFirst'] . ' ' . $p['playerLast']);
        }
    }

    // Build final per-position high-score array per stadium (flattened; tied
    // entries share the same `pos` and appear in order)
    $pos_high_by_stadium = [];
    foreach ($pos_bests as $sid => $by_pos) {
        $entries = [];
        foreach (['QB', 'RB', 'WR', 'PK'] as $pos) {
            if (empty($by_pos[$pos])) continue;
            foreach ($by_pos[$pos] as $b) {
                $entries[] = [
                    'pos'      => $pos,
                    'pid'      => $b['pid'],
                    'name'     => $pos_player_names[$b['pid']] ?? $b['pid'],
                    'team'     => $b['team'],
                    'teamName' => $all_team_names[$b['team']] ?? $b['team'],
                    'points'   => $b['points'],
                    'year'     => $b['year'],
                    'week'     => $b['week'],
                ];
            }
        }
        $pos_high_by_stadium[$sid] = $entries;
    }

    // Championship years per team (for plotting markers on the attendance chart).
    $champ_years_by_team = [];
    foreach ($wpdb->get_results("SELECT year, winTeam FROM wp_champions", ARRAY_A) as $cr) {
        $champ_years_by_team[$cr['winTeam']][] = (int) $cr['year'];
    }

    // Per-image zoom/position settings (option keyed by image URL)
    $img_settings_map = get_option('pfl_stadium_image_settings', []);

    // Aggregate attendance data per stadium (from wp_attendance, if it exists).
    $attendance_by_stadium = [];
    if ($wpdb->get_var("SHOW TABLES LIKE 'wp_attendance'") === 'wp_attendance') {
        $att_agg = $wpdb->get_results(
            "SELECT stadium_id,
                    COUNT(*)                                        AS games_count,
                    ROUND(AVG(attendance_pct), 2)                   AS avg_pct,
                    ROUND(AVG(attendance_count))                    AS avg_count,
                    SUM(attendance_count)                           AS total_count,
                    MAX(attendance_pct)                             AS peak_pct,
                    MIN(attendance_pct)                             AS low_pct
             FROM wp_attendance
             WHERE stadium_id IS NOT NULL
             GROUP BY stadium_id",
            ARRAY_A
        );
        // Per-year average attendance per stadium for the bar chart
        $yearly_rows = $wpdb->get_results(
            "SELECT stadium_id, year, ROUND(AVG(attendance_pct), 2) AS avg_pct, COUNT(*) AS games
             FROM wp_attendance
             WHERE stadium_id IS NOT NULL
             GROUP BY stadium_id, year
             ORDER BY stadium_id, year",
            ARRAY_A
        );
        $yearly_by_sid = [];
        foreach ($yearly_rows as $yr) {
            $yearly_by_sid[(int) $yr['stadium_id']][] = [
                'year'  => (int) $yr['year'],
                'pct'   => (float) $yr['avg_pct'],
                'games' => (int) $yr['games'],
            ];
        }
        $peak_rows = $wpdb->get_results(
            "SELECT a.stadium_id, a.year, a.week, a.home_team, a.away_team,
                    a.attendance_pct, a.attendance_count
             FROM wp_attendance a
             INNER JOIN (
                 SELECT stadium_id, MAX(attendance_pct) AS peak
                 FROM wp_attendance
                 WHERE stadium_id IS NOT NULL
                 GROUP BY stadium_id
             ) m ON a.stadium_id = m.stadium_id AND a.attendance_pct = m.peak
             ORDER BY a.year DESC, a.week DESC",
            ARRAY_A
        );
        $peak_by_sid = [];
        foreach ($peak_rows as $pr) {
            $sid = (int) $pr['stadium_id'];
            if (isset($peak_by_sid[$sid])) continue; // first row wins (most recent)
            $peak_by_sid[$sid] = [
                'year'              => (int) $pr['year'],
                'week'              => (int) $pr['week'],
                'homeTeam'          => $pr['home_team'],
                'homeTeamName'      => $all_team_names[$pr['home_team']] ?? $pr['home_team'],
                'awayTeam'          => $pr['away_team'],
                'awayTeamName'      => $all_team_names[$pr['away_team']] ?? $pr['away_team'],
                'attendancePct'     => (float) $pr['attendance_pct'],
                'attendanceCount'   => $pr['attendance_count'] !== null ? (int) $pr['attendance_count'] : null,
            ];
        }
        foreach ($att_agg as $row) {
            $sid = (int) $row['stadium_id'];
            // Find which row in $rows this sid belongs to, get team_owned + year range
            $stadium_row = null;
            foreach ($rows as $rr) { if ((int) $rr['id'] === $sid) { $stadium_row = $rr; break; } }
            $champ_years = [];
            if ($stadium_row) {
                $champ_team  = pfl_base_team($stadium_row['team_owned']);
                $opened_yr   = max((int) ($stadium_row['year_built'] ?? 1991), 1991);
                $closed_yr   = $closed_by_id[$sid] ?? (int) date('Y');
                foreach (($champ_years_by_team[$champ_team] ?? []) as $y) {
                    if ($y >= $opened_yr && $y <= $closed_yr) $champ_years[] = $y;
                }
                sort($champ_years);
            }

            $attendance_by_stadium[$sid] = [
                'games'             => (int) $row['games_count'],
                'avgPct'            => (float) $row['avg_pct'],
                'avgCount'          => $row['avg_count'] !== null ? (int) $row['avg_count'] : null,
                'totalCount'        => $row['total_count'] !== null ? (int) $row['total_count'] : null,
                'peakPct'           => (float) $row['peak_pct'],
                'lowPct'            => (float) $row['low_pct'],
                'peakGame'          => $peak_by_sid[$sid] ?? null,
                'yearly'            => $yearly_by_sid[$sid] ?? [],
                'championshipYears' => $champ_years,
            ];
        }
    }

    $result = [];
    foreach ($rows as $r) {
        $is_current       = (int) ($r['currently_occupied'] ?? 0) === 1;
        $base_team        = pfl_base_team($r['team_owned']);
        $is_old_row       = ($r['team_owned'] !== $base_team);
        $team_has_current = !empty($teams_with_current[$base_team]);
        // For former stadiums: prefer a {TEAM}_OLD ACF entry if present,
        // otherwise fall back to the team's main entry only when the team
        // no longer has an active venue (avoids showing current stadium photos).
        $old_key     = $base_team . '_OLD';
        $has_old_key = isset($team_images[$old_key]);
        $show_images = $is_current || $has_old_key || !$team_has_current;

        // Closed year for former stadiums:
        //   • _OLD row → gap-aware last-year-before-hiatus from standings
        //   • Team replaced it with a newer venue → year new venue opened - 1
        //   • Franchise folded / merged → last year seen in standings
        $closed = null;
        if (!$is_current) {
            if ($is_old_row) {
                $closed = $team_last_year[$base_team] ?? null;
            } elseif ($team_has_current) {
                $closed = isset($current_stadium_year[$base_team])
                    ? $current_stadium_year[$base_team] - 1
                    : null;
            } else {
                $closed = $team_last_year[$base_team] ?? null;
            }
        }

        // Count games hosted during this stadium's active years
        $opened_yr   = max((int) ($r['year_built'] ?? 1991), 1991);
        $closed_yr   = $closed ?? (int) date('Y');
        $reg_total   = 0;
        $reg_wins    = 0;
        $post_total  = 0;
        $post_wins   = 0;
        $post_losses = 0;
        $seasons     = 0;
        for ($yr = $opened_yr; $yr <= $closed_yr; $yr++) {
            $reg_yr = $reg_games[$base_team][$yr] ?? null;
            if (!$reg_yr) continue;
            $reg_total += $reg_yr['games'];
            $reg_wins  += $reg_yr['wins'];
            $seasons++;
        }
        $po_sid       = $playoff_by_stadium[(int) $r['id']] ?? null;
        $post_total   = $po_sid['games']   ?? 0;
        $post_wins    = $po_sid['wins']    ?? 0;
        $post_losses  = $po_sid['losses']  ?? 0;

        $result[] = [
            'id'             => (int) $r['id'],
            'name'           => $r['facility_names'],
            // 'team' exposes the BASE team identifier (e.g. "BST") so the
            // /team page link works the same for _OLD historical stadium rows.
            'team'           => $base_team,
            'teamName'       => $all_team_names[$base_team] ?? ($r['team_name'] ?? $base_team),
            'opened'         => $r['year_built'],
            'closed'         => $closed,
            'renovated'      => $r['year_renovated'] ?: null,
            'surface'        => $r['turf'],
            'capacity'       => $r['capacity']          ? (int) $r['capacity']          : null,
            'capacityPreReno'=> $r['capacity_before_reno'] ? (int) $r['capacity_before_reno'] : null,
            'luxBoxes'       => $r['lux_suites']         ? (int) $r['lux_suites']         : null,
            'clubSeats'      => $r['club_suites']        ? (int) $r['club_suites']        : null,
            'parking'        => $r['parking']            ? (int) $r['parking']            : null,
            'costMillions'   => $r['total_cost']         ? (int) $r['total_cost']         : null,
            'roofType'       => $r['roof_type'],
            'region'         => $r['region'],
            'current'        => $is_current,
            'images'         => (function() use ($show_images, $is_current, $base_team, $team_images, $old_key, $img_settings_map) {
                if (!$show_images) return [];
                $urls = $is_current
                    ? ($team_images[$base_team] ?? [])
                    : ($team_images[$old_key] ?? $team_images[$base_team] ?? []);
                $out = [];
                foreach ($urls as $u) {
                    $out[] = ['url' => $u, 'settings' => $img_settings_map[$u] ?? null];
                }
                return $out;
            })(),
            'posseBowls'     => $pb_map[$r['facility_names']] ?? [],
            'gamesHosted'    => [
                'regular'      => $reg_total,
                'regWins'      => $reg_wins,
                'regLosses'    => $reg_total - $reg_wins,
                'postseason'   => $post_total,
                'postWins'     => $post_wins,
                'postLosses'   => $post_losses,
                'seasons'      => $seasons,
            ],
            'highScore'      => $high_score_by_stadium[(int) $r['id']] ?? null,
            'posHighScores'  => $pos_high_by_stadium[(int) $r['id']] ?? [],
            'attendance'     => $attendance_by_stadium[(int) $r['id']] ?? null,
        ];
    }
    return rest_ensure_response($result);
}

function pfl_api_team_timeline(WP_REST_Request $request) {
    global $wpdb;

    $team = strtoupper(sanitize_text_field($request->get_param('id') ?? ''));
    if (!$team) return new WP_Error('missing_id', 'Team id required', ['status' => 400]);

    $theme_uri = get_stylesheet_directory_uri();

    // ── Helmet / name history ─────────────────────────────────────────────────
    $helm_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT yearstart, name, helmet FROM wp_helmet_history WHERE team = %s ORDER BY yearstart ASC",
        $team
    ), ARRAY_A);

    if (empty($helm_rows)) return rest_ensure_response([]);

    $founding_year = (int) $helm_rows[0]['yearstart'];
    $current_year  = (int) date('Y');

    // Carry-forward name and helmet number for every year
    $active_name   = '';
    $active_helmet = 1;
    $name_by_year   = [];
    $helmet_by_year = [];
    $helm_change_years = array_flip(array_column($helm_rows, 'yearstart'));
    foreach ($helm_rows as $r) { $helm_change_years[(int)$r['yearstart']] = ['name' => $r['name'], 'helmet' => (int)$r['helmet']]; }

    for ($y = $founding_year; $y <= $current_year; $y++) {
        if (isset($helm_change_years[$y])) {
            $active_name   = $helm_change_years[$y]['name'];
            $active_helmet = $helm_change_years[$y]['helmet'];
        }
        $name_by_year[$y]   = $active_name;
        $helmet_by_year[$y] = $active_helmet;
    }

    // ── Championships ─────────────────────────────────────────────────────────
    $champ_set = array_flip(array_map('intval', $wpdb->get_col($wpdb->prepare(
        "SELECT year FROM wp_champions WHERE winTeam = %s", $team
    ))));

    // ── Awards ────────────────────────────────────────────────────────────────
    $owner_full = $wpdb->get_var($wpdb->prepare("SELECT owner FROM wp_teams WHERE team_int = %s LIMIT 1", $team)) ?? '';
    $owner_parts = explode(' ', trim($owner_full), 2);
    $owner_first = $owner_parts[0] ?? '';
    $owner_last  = $owner_parts[1] ?? '';

    $award_key_map = [
        'Most Valuable Player' => 'mvp',
        'Rookie of the Year'   => 'rookie',
        'Posse Bowl MVP'       => 'possebowlMvp',
        'Pro Bowl MVP'         => 'probowlMvp',
        'Owner of the Year'    => 'ownerOfYear',
    ];

    $award_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT award, year, pid, playerFirst, playerLast FROM wp_awards
         WHERE team = %s AND award != 'Hall of Fame Inductee'
         ORDER BY year ASC",
        $team
    ), ARRAY_A);

    $events_by_year = [];
    foreach ($award_rows as $r) {
        $key = $award_key_map[$r['award']] ?? null;
        if (!$key) continue;
        $yr    = (int) $r['year'];
        $first = $r['playerFirst'];
        $last  = $r['playerLast'];
        $pid   = $r['pid'];
        if ($key === 'ownerOfYear' && empty($pid)) {
            $first = $owner_first; $last = $owner_last; $pid = 'owner_' . $team;
        }
        $events_by_year[$yr][] = ['type' => 'award', 'awardKey' => $key, 'label' => $r['award'], 'first' => $first, 'last' => $last, 'pid' => $pid];
    }

    // ── Hall of Fame ──────────────────────────────────────────────────────────
    $hof_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT year, pid, playerFirst, playerLast FROM wp_awards
         WHERE award = 'Hall of Fame Inductee' AND team = %s ORDER BY year ASC",
        $team
    ), ARRAY_A);
    foreach ($hof_rows as $r) {
        $events_by_year[(int)$r['year']][] = ['type' => 'hof', 'first' => $r['playerFirst'], 'last' => $r['playerLast'], 'pid' => $r['pid']];
    }

    // ── Scoring Titles ────────────────────────────────────────────────────────
    $title_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT n.year, n.playerid, n.points, n.pos,
                p.playerFirst AS first, p.playerLast AS last
         FROM wp_number_ones n
         LEFT JOIN wp_players p ON p.p_id = n.playerid
         WHERE n.teams = %s ORDER BY n.year ASC, n.pos ASC",
        $team
    ), ARRAY_A);
    foreach ($title_rows as $r) {
        $events_by_year[(int)$r['year']][] = ['type' => 'scoring_title', 'pos' => $r['pos'], 'label' => $r['pos'] . ' Scoring Title', 'first' => $r['first'] ?? '', 'last' => $r['last'] ?? '', 'pid' => $r['playerid'], 'points' => (float)$r['points']];
    }

    // ── Pro Bowl ──────────────────────────────────────────────────────────────
    $pb_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT pb.year, pb.playerid, pb.position, pb.starter,
                p.playerFirst AS first, p.playerLast AS last
         FROM wp_probowlbox pb
         LEFT JOIN wp_players p ON p.p_id = pb.playerid
         WHERE pb.team = %s ORDER BY pb.year ASC, pb.position ASC",
        $team
    ), ARRAY_A);
    $pb_by_year = [];
    foreach ($pb_rows as $r) {
        $pb_by_year[(int)$r['year']][] = ['pid' => $r['playerid'], 'first' => $r['first'] ?? '', 'last' => $r['last'] ?? '', 'pos' => $r['position'], 'starter' => (bool)$r['starter']];
    }
    foreach ($pb_by_year as $yr => $players) {
        $events_by_year[$yr][] = ['type' => 'probowl', 'players' => $players];
    }

    // ── Collect all notable years ─────────────────────────────────────────────
    $all_years = array_unique(array_merge(
        [$founding_year],
        array_keys($champ_set),
        array_keys($events_by_year)
    ));
    sort($all_years);

    // ── Build output ──────────────────────────────────────────────────────────
    $prev_name = null;
    $result = [];
    foreach ($all_years as $yr) {
        $name = $name_by_year[$yr] ?? '';
        $helm = $helmet_by_year[$yr] ?? 1;
        $result[] = [
            'year'         => $yr,
            'name'         => $name,
            'nameChanged'  => ($name !== $prev_name),
            'helmetNum'    => $helm,
            'helmetUrl'    => $theme_uri . '/img/helmets/final-renders/' . $team . '/helmet-' . $team . '-' . $helm . '-front.png',
            'championship' => isset($champ_set[$yr]),
            'founded'      => ($yr === $founding_year),
            'events'       => $events_by_year[$yr] ?? [],
        ];
        $prev_name = $name;
    }

    return rest_ensure_response($result);
}

// ── Helmets by Year ───────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/helmets', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_helmets',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/helmets-timeline', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_helmets_timeline',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/team-colors', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_team_colors',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_team_colors(WP_REST_Request $request) {
    global $wpdb;

    $team = strtoupper(sanitize_text_field($request->get_param('id')));
    if (!$team) return rest_ensure_response([]);

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT yearstart, name, helmet, color1, color2, color3
         FROM wp_helmet_history
         WHERE team = %s
         ORDER BY yearstart ASC",
        $team
    ), ARRAY_A);

    $theme_uri  = get_stylesheet_directory_uri();
    $theme_dir  = get_stylesheet_directory();
    $patch_file = $theme_dir . '/img/patches/FINAL-PATCH-' . $team . '.png';
    $patch_url  = file_exists($patch_file)
        ? $theme_uri . '/img/patches/FINAL-PATCH-' . $team . '.png'
        : null;

    $result = [];
    foreach ($rows as $r) {
        $colors = array_values(array_filter([
            $r['color1'], $r['color2'], $r['color3']
        ], fn($c) => !empty($c)));
        $result[] = [
            'yearstart' => (int) $r['yearstart'],
            'name'      => $r['name'],
            'helmet'    => (int) $r['helmet'],
            'colors'    => $colors,
            'patchUrl'  => $patch_url,
        ];
    }

    return rest_ensure_response($result);
}

function pfl_api_helmets(WP_REST_Request $request) {
    global $wpdb;

    $year = (int) $request->get_param('year');
    if (!$year) $year = (int) date('Y');

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    $teams_in_year = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT team FROM wp_drafts WHERE year = %d ORDER BY team ASC",
        $year
    ));

    $name_rows  = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    $team_names = [];
    foreach ($name_rows as $r) {
        $team_names[$r['team_int']] = $r['team'];
    }

    $result = [];
    foreach ($teams_in_year as $team_int) {
        $helm_num  = pfl_get_helmet_num($team_int, $year);
        $base_path = $theme_dir . '/img/helmets/final-renders/' . $team_int . '/helmet-' . $team_int . '-' . $helm_num;
        $variant   = file_exists($base_path . '-side.png') ? 'side' : 'front';
        $result[] = [
            'team'      => $team_int,
            'name'      => $team_names[$team_int] ?? $team_int,
            'helmetUrl' => $theme_uri . '/img/helmets/final-renders/' . $team_int . '/helmet-' . $team_int . '-' . $helm_num . '-' . $variant . '.png',
        ];
    }

    return rest_ensure_response($result);
}

function pfl_api_helmets_timeline(WP_REST_Request $request) {
    global $wpdb;

    $theme_uri = get_stylesheet_directory_uri();

    $all_years = array_map('intval', $wpdb->get_col(
        "SELECT DISTINCT year FROM wp_drafts ORDER BY year ASC"
    ));

    $all_teams = $wpdb->get_col(
        "SELECT DISTINCT team FROM wp_drafts ORDER BY team ASC"
    );

    // Which teams were active each year
    $active = [];
    foreach ($wpdb->get_results(
        "SELECT DISTINCT year, team FROM wp_drafts ORDER BY year ASC",
        ARRAY_A
    ) as $r) {
        $active[(int) $r['year']][$r['team']] = true;
    }

    $name_rows  = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    $team_names = [];
    foreach ($name_rows as $r) {
        $team_names[$r['team_int']] = $r['team'];
    }

    $first_year = $all_years[0] ?? 1991;
    $teams_data = [];

    foreach ($all_teams as $team_int) {
        // Load helmet change-points for this team
        $history = $wpdb->get_results($wpdb->prepare(
            "SELECT yearstart, helmet FROM wp_helmet_history WHERE team = %s ORDER BY yearstart ASC",
            $team_int
        ), ARRAY_A);
        $by_year = [];
        foreach ($history as $row) {
            $by_year[(int) $row['yearstart']] = (int) $row['helmet'];
        }

        // Carry-forward: compute current helmet number up to each active year
        $helmets    = [];
        $current    = 1;
        $prev_track = $first_year;

        foreach ($all_years as $year) {
            // Advance carry-forward from where we left off
            for ($y = $prev_track; $y <= $year; $y++) {
                if (isset($by_year[$y])) $current = $by_year[$y];
            }
            $prev_track = $year + 1;

            if (!isset($active[$year][$team_int])) continue;

            $helmets[$year] = [
                'helmetNum' => $current,
                'helmetUrl' => $theme_uri . '/img/helmets/weekly/' . $team_int . '-helm-right-' . $current . '.png',
            ];
        }

        $teams_data[] = [
            'team'    => $team_int,
            'name'    => $team_names[$team_int] ?? $team_int,
            'helmets' => $helmets,
        ];
    }

    return rest_ensure_response([
        'years' => $all_years,
        'teams' => $teams_data,
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
    register_rest_route('pfl/v1', '/tables/other-pep-wrz-week14', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_pep_wrz_week14',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/other-jersey-numbers', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_jersey_numbers',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/other-snr-thanksgiving', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_snr_thanksgiving',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/tables/other-brotherly-love', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_tables_other_brotherly_love',
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
    $cache = get_transient('pfl_tables_other_rematch_game_v2');
    if ($cache !== false) return rest_ensure_response($cache);

    $pbgames = revenge_game();
    $result  = [];
    foreach ($pbgames as $year => $val) {
        if ($year < 1996) continue;

        // Re-fetch the Week 1 result to get home_away
        $raw        = get_team_results_by_week($val['pb_winner'], $year . '01');
        $game       = $raw[ $year . '01' ] ?? null;
        $home_away  = $game ? $game['home_away'] : null;
        // H = pb_winner hosted, A = pb_loser hosted
        $host         = ($home_away === 'H') ? $val['pb_winner'] : $val['pb_loser'];
        $loser_hosted = ($host === $val['pb_loser']);

        if ($val['pb_winner'] === $val['next_win']) {
            $result[] = [
                'year'         => (int) $year,
                'winner'       => $val['next_win'],
                'loser'        => $val['next_loser'],
                'outcome'      => '',
                'host'         => $host,
                'loser_hosted' => $loser_hosted,
            ];
        } elseif ($val['pb_winner'] === $val['next_loser']) {
            $result[] = [
                'year'         => (int) $year,
                'winner'       => $val['next_win'],
                'loser'        => $val['next_loser'],
                'outcome'      => 'REVENGE!',
                'host'         => $host,
                'loser_hosted' => $loser_hosted,
            ];
        }
    }

    set_transient('pfl_tables_other_rematch_game_v2', $result, DAY_IN_SECONDS);
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

// ── /tables/other-pep-wrz-week14 ─────────────────────────────────────────────
// All-time Week 14 matchups between Peppers (PEP) and Space Warriorz (WRZ)
// wp_team_* column indices: [0]=id [1]=season [2]=week [3]=team_int [4]=points
//   [5]=versus [6]=versus_pts [7]=home_away [8]=stadium [9]=result [14]=overtime
function pfl_api_tables_other_pep_wrz_week14() {
    global $wpdb;

    $cache = get_transient('pfl_tables_pep_wrz_week14_v2');
    if ($cache !== false) return rest_ensure_response($cache);

    $seasons = $wpdb->get_col("SELECT DISTINCT season FROM wp_team_PEP ORDER BY season ASC");

    $result = [];
    foreach ($seasons as $season) {
        $weekid = $season . '14';
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM wp_team_PEP WHERE id = %s", $weekid),
            ARRAY_N
        );
        if (!$row || $row[5] !== 'WRZ') continue;

        $pep_score = (float) $row[4];
        $wrz_score = (float) $row[6];
        $result[] = [
            'year'      => (int)  $season,
            'pep_score' => $pep_score,
            'wrz_score' => $wrz_score,
            'pep_home'  => ($row[7] === 'H'),
            'winner'    => ($pep_score > $wrz_score) ? 'PEP' : 'WRZ',
            'margin'    => abs($pep_score - $wrz_score),
            'overtime'  => (bool) $row[14],
        ];
    }

    set_transient('pfl_tables_pep_wrz_week14_v2', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── SNR Thanksgiving helper ───────────────────────────────────────────────────
// Shared by the REST table endpoint and the weekly-results auto-note.
// Returns array of rows (cached in transient pfl_tables_snr_thanksgiving_v3).
// wp_team_* column indices: [0]=id [1]=season [2]=week [4]=points [5]=versus
//   [6]=versus_pts [7]=home_away [9]=result [14]=overtime
// Player table column indices: [10]=game_date
function pfl_get_snr_thanksgiving_data() {
    global $wpdb;

    $cached = get_transient('pfl_tables_snr_thanksgiving_v3');
    if ($cached !== false) return $cached;

    $team_names = [];
    foreach ($wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A) as $t) {
        $team_names[$t['team_int']] = $t['team'];
    }

    $snr_seasons = $wpdb->get_col("SELECT DISTINCT season FROM wp_team_SNR ORDER BY season ASC");
    $result = [];

    foreach ($snr_seasons as $season) {
        $year       = (int) $season;
        $nov1_ts    = mktime(0, 0, 0, 11, 1, $year);
        $nov1_dow   = (int) date('w', $nov1_ts);
        $tgiving_ts = $nov1_ts + ((4 - $nov1_dow + 7) % 7) * 86400 + 21 * 86400;
        $thanksgiving = date('Y-m-d', $tgiving_ts);

        $match_row = null; $match_game_date = null;

        for ($wk = 8; $wk <= 14; $wk++) {
            $wid = $season . str_pad($wk, 2, '0', STR_PAD_LEFT);
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_team_SNR WHERE id = %s", $wid), ARRAY_N);
            if (!$row) continue;
            foreach ([$row[10], $row[11], $row[12], $row[13]] as $pid) {
                if (empty($pid)) continue;
                $sp   = preg_replace('/[^A-Za-z0-9]/', '', $pid);
                $prow = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$sp}` WHERE week_id = %s", $wid), ARRAY_N);
                if (!$prow || empty($prow[10])) continue;
                if (abs(strtotime($prow[10]) - $tgiving_ts) <= 8 * 86400) {
                    $match_row = $row; $match_game_date = $prow[10];
                }
                break;
            }
            if ($match_row) break;
        }

        if (!$match_row) continue;

        $snr_score = (float) $match_row[4];
        $opp_score = (float) $match_row[6];
        $opp       = $match_row[5];

        $result[] = [
            'year'         => $year,
            'pfl_week'     => (int) $match_row[2],
            'thanksgiving' => $thanksgiving,
            'game_date'    => $match_game_date,
            'snr_score'    => $snr_score,
            'opp'          => $opp,
            'opp_name'     => $team_names[$opp] ?? $opp,
            'opp_score'    => $opp_score,
            'snr_home'     => ($match_row[7] === 'H'),
            'winner'       => ($snr_score > $opp_score) ? 'SNR' : $opp,
            'overtime'     => (bool) $match_row[14],
        ];
    }

    set_transient('pfl_tables_snr_thanksgiving_v3', $result, DAY_IN_SECONDS);
    return $result;
}

// ── /tables/other-snr-thanksgiving ───────────────────────────────────────────
function pfl_api_tables_other_snr_thanksgiving() {
    return rest_ensure_response(pfl_get_snr_thanksgiving_data());
}

// ── /tables/other-brotherly-love ─────────────────────────────────────────────
// Cumulative head-to-head records for family/brotherly rivalry matchups
// wp_team_* column indices: [0]=id [1]=season [2]=week [3]=team_int [4]=points
//   [5]=versus [6]=versus_pts [7]=home_away [9]=result [14]=overtime
function pfl_api_tables_other_brotherly_love() {
    global $wpdb;

    $cache = get_transient('pfl_tables_brotherly_love_v2');
    if ($cache !== false) return rest_ensure_response($cache);

    // Whitelist of valid team codes — prevents any SQL injection if logic is ever changed
    $valid_teams = ['CMN','HAT','WRZ','SON','PEP','DST','PHR','ATK','RBS','ETS','BUL','SNR','TSG','BST','MAX'];

    $rivalries = [
        [ 'id' => 'cmn-hat',     'title' => 'CMN vs. HAT',     'side_a' => ['CMN'],       'side_b' => ['HAT']        ],
        [ 'id' => 'wrz-son',     'title' => 'WRZ vs. SON',     'side_a' => ['WRZ'],       'side_b' => ['SON']        ],
        [ 'id' => 'pep-phr-dst', 'title' => 'PEP vs. PHR/DST', 'side_a' => ['PEP'],       'side_b' => ['PHR', 'DST'] ],
        [ 'id' => 'pep-atk',     'title' => 'PEP vs. ATK',     'side_a' => ['PEP'],       'side_b' => ['ATK']        ],
        [ 'id' => 'phr-atk',     'title' => 'PHR vs. ATK',     'side_a' => ['PHR'],       'side_b' => ['ATK']        ],
    ];

    $names = [];
    foreach ($wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A) as $t) {
        $names[$t['team_int']] = $t['team'];
    }

    $result = [];

    foreach ($rivalries as $rivalry) {
        $wins_a = 0;
        $wins_b = 0;
        $games  = [];

        foreach ($rivalry['side_a'] as $ta) {
            if (!in_array($ta, $valid_teams, true)) continue;
            if (!$wpdb->get_var("SHOW TABLES LIKE 'wp_team_{$ta}'")) continue;

            foreach ($rivalry['side_b'] as $tb) {
                if (!in_array($tb, $valid_teams, true)) continue;

                $rows = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM wp_team_{$ta} WHERE vs = %s ORDER BY season ASC, week ASC",
                        $tb
                    ),
                    ARRAY_N
                );

                foreach ($rows as $row) {
                    $pts_a  = (float) $row[4];
                    $pts_b  = (float) $row[6];
                    $won_a  = ((float) $row[9]) > 0;

                    if ($won_a) $wins_a++; else $wins_b++;

                    $games[] = [
                        'year'     => (int)  $row[1],
                        'week'     => (int)  $row[2],
                        'team_a'   => $ta,
                        'team_b'   => $tb,
                        'pts_a'    => $pts_a,
                        'pts_b'    => $pts_b,
                        'home_a'   => ($row[7] === 'H'),
                        'winner'   => $won_a ? $ta : $tb,
                        'overtime' => !empty($row[14]),
                    ];
                }
            }
        }

        usort($games, fn($a, $b) => $a['year'] <=> $b['year'] ?: $a['week'] <=> $b['week']);

        $result[] = [
            'id'     => $rivalry['id'],
            'title'  => $rivalry['title'],
            'side_a' => $rivalry['side_a'],
            'side_b' => $rivalry['side_b'],
            'wins_a' => $wins_a,
            'wins_b' => $wins_b,
            'total'  => $wins_a + $wins_b,
            'games'  => $games,
        ];
    }

    set_transient('pfl_tables_brotherly_love_v2', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── /tables/other-jersey-numbers ─────────────────────────────────────────────
function pfl_api_tables_other_jersey_numbers() {
    global $wpdb;

    $cache = get_transient('pfl_tables_jersey_numbers_v2');
    if ($cache !== false) return rest_ensure_response($cache);

    // All players with a numberarray
    $players = $wpdb->get_results(
        "SELECT p_id, playerFirst, playerLast, numberarray FROM wp_players
         WHERE numberarray IS NOT NULL AND numberarray != '' AND numberarray != 'null'",
        ARRAY_A
    );

    // Season points indexed by [playerid][season]
    $season_pts = [];
    foreach ($wpdb->get_results("SELECT playerid, season, points FROM wp_season_leaders", ARRAY_A) as $r) {
        $season_pts[$r['playerid']][(int) $r['season']] = (float) $r['points'];
    }

    // Initialise buckets 0-99
    $buckets = [];
    for ($n = 0; $n <= 99; $n++) {
        $buckets[$n] = ['count' => 0, 'best_pid' => null, 'best_name' => null, 'best_img' => null, 'best_pts' => -1];
    }

    foreach ($players as $p) {
        $pid  = $p['p_id'];
        $arr  = json_decode($p['numberarray'], true);
        if (!is_array($arr)) continue;

        $name = trim($p['playerFirst'] . ' ' . $p['playerLast']);
        $img  = pfl_player_img_url($pid);

        // Accumulate points per number worn, season by season
        $pts_per_num = [];
        foreach ($arr as $year_str => $num_val) {
            if (!is_numeric($num_val)) continue;
            $num = (int) $num_val;
            if ($num < 0 || $num > 99) continue;
            $pts_per_num[$num] = ($pts_per_num[$num] ?? 0) + ($season_pts[$pid][(int) $year_str] ?? 0);
        }

        // Register each unique number worn
        foreach (array_keys($pts_per_num) as $num) {
            $buckets[$num]['count']++;
            $pts = $pts_per_num[$num];
            if ($pts > $buckets[$num]['best_pts']) {
                $buckets[$num]['best_pts'] = $pts;
                $buckets[$num]['best_pid']  = $pid;
                $buckets[$num]['best_name'] = $name;
                $buckets[$num]['best_img']  = $img;
            }
        }
    }

    $result = [];
    for ($n = 0; $n <= 99; $n++) {
        $b = $buckets[$n];
        $result[] = [
            'number'    => $n,
            'count'     => $b['count'],
            'best_pid'  => $b['best_pid'],
            'best_name' => $b['best_name'],
            'best_img'  => $b['best_pid'] ? ($b['best_img'] ?? null) : null,
            'best_pts'  => $b['best_pts'] >= 0 ? $b['best_pts'] : null,
        ];
    }

    set_transient('pfl_tables_jersey_numbers_v2', $result, DAY_IN_SECONDS);
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
    register_rest_route('pfl/v1', '/tables/nfl-team-leaders', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_nfl_team_leaders',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_nfl_team_leaders() {
    $cache = get_transient('pfl_nfl_team_leaders_v2');
    if ($cache !== false) return rest_ensure_response($cache);

    $playerids = just_player_ids();

    // pid → [nfl_abbr → game_count]
    $player_team_games = [];
    foreach ($playerids as $pid) {
        $weekly = get_player_data($pid);
        if (!$weekly) continue;
        foreach ($weekly as $game) {
            $abbr = strtoupper(trim($game['nflteam'] ?? ''));
            if ($abbr === '') continue;
            $player_team_games[$pid][$abbr] = ($player_team_games[$pid][$abbr] ?? 0) + 1;
        }
    }

    // Invert to nfl_abbr → [pid → game_count]
    $team_player_games = [];
    foreach ($player_team_games as $pid => $team_counts) {
        foreach ($team_counts as $abbr => $cnt) {
            $team_player_games[$abbr][$pid] = ($team_player_games[$abbr][$pid] ?? 0) + $cnt;
        }
    }

    // Merge franchises that have relocated / rebranded
    $aliases = [
        'Raiders'   => ['OAK', 'LVR', 'RAI'],
        'Rams'      => ['STL', 'LAR', 'RAM'],
        'Chargers'  => ['SD',  'LAC', 'SDG'],
        'Cardinals' => ['ARI', 'PHO'],
    ];
    foreach ($aliases as $canonical => $abbrs) {
        $merged = [];
        foreach ($abbrs as $abbr) {
            foreach ($team_player_games[$abbr] ?? [] as $pid => $cnt) {
                $merged[$pid] = ($merged[$pid] ?? 0) + $cnt;
            }
            unset($team_player_games[$abbr]);
        }
        if (!empty($merged)) $team_player_games[$canonical] = $merged;
    }

    // Build result: each team → top-20 players sorted by games desc
    $result = [];
    foreach ($team_player_games as $abbr => $player_counts) {
        arsort($player_counts);
        $total   = array_sum($player_counts);
        $top     = array_slice($player_counts, 0, 20, true);
        $players = [];
        foreach ($top as $pid => $cnt) {
            $name      = get_player_name($pid);
            $players[] = [
                'pid'   => $pid,
                'first' => $name['first'] ?? '',
                'last'  => $name['last']  ?? '',
                'games' => (int) $cnt,
            ];
        }
        $team_name = get_nfl_full_team_name_from_id($abbr) ?: $abbr;
        $result[]  = ['team' => $team_name, 'abbr' => $abbr, 'total' => $total, 'players' => $players];
    }

    // Sort teams: most total games first
    usort($result, fn($a, $b) => $b['total'] - $a['total']);

    set_transient('pfl_nfl_team_leaders_v2', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── Draft Number Ones ─────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/drafts/number-ones', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_draft_number_ones',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_draft_number_ones() {
    $cache = get_transient('pfl_draft_number_ones_v2');
    if ($cache !== false) return rest_ensure_response($cache);

    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT d.year, d.pickord, d.team, d.playerid,
                t.team AS team_name
         FROM wp_drafts d
         LEFT JOIN wp_teams t ON t.team_int = d.team
         WHERE d.round = '01' AND d.picknum = '01'
         ORDER BY d.year ASC",
        ARRAY_A
    );

    $result = [];
    foreach ($rows as $row) {
        $pid = $row['playerid'] ?? '';

        $card = null;
        if ($pid) {
            $req = new WP_REST_Request('GET', '/pfl/v1/player-card');
            $req->set_param('pid', $pid);
            $resp = pfl_api_player_card($req);
            if (!is_wp_error($resp)) {
                $card = $resp->get_data();
            }
        }

        $result[] = [
            'year'          => (int) $row['year'],
            'draftTeam'     => $row['team'],
            'draftTeamName' => $row['team_name'] ?? $row['team'],
            'tradeTeam'     => ($row['pickord'] !== $row['team']) ? $row['pickord'] : null,
            'playerid'      => $pid,
            'card'          => $card,
        ];
    }

    set_transient('pfl_draft_number_ones_v2', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── MFL Team IDs ─────────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/mfl-team-ids', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_mfl_team_ids',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/mfl-team-ids/copy', [
        'methods'             => 'POST',
        'callback'            => 'pfl_api_copy_mfl_ids',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_get_all_mfl_ids() {
    // Merge hardcoded array with any years saved via the copy tool
    $all       = get_pfl_mfl_ids_season();
    $overrides = get_option('pfl_mfl_ids_overrides', []);
    return $all + $overrides; // overrides fill in years not in the hardcoded array
}

function pfl_api_mfl_team_ids(WP_REST_Request $request) {
    $all  = pfl_get_all_mfl_ids();
    $year = $request->get_param('year') ? (int) $request->get_param('year') : max(array_keys($all));
    $map  = isset($all[$year]) ? $all[$year] : $all[max(array_keys($all))];
    // Return as { teamAbbr: mflId } — skip blank entries
    $result = [];
    foreach ($map as $mfl_id => $abbr) {
        if ($abbr !== '') $result[$abbr] = $mfl_id;
    }
    return rest_ensure_response($result);
}

function pfl_api_copy_mfl_ids() {
    // Use the hardcoded array to determine "last year" so overrides don't shift the target
    $hardcoded = get_pfl_mfl_ids_season();
    $overrides = get_option('pfl_mfl_ids_overrides', []);
    $all       = pfl_get_all_mfl_ids();

    $last_year = max(array_keys($hardcoded));
    $next_year = $last_year + 1;

    $already_existed = isset($all[$next_year]);

    if (!$already_existed) {
        $overrides[$next_year] = $hardcoded[$last_year];
        update_option('pfl_mfl_ids_overrides', $overrides);
    }

    $map = isset($all[$next_year]) ? $all[$next_year] : $hardcoded[$last_year];
    $ids = [];
    foreach ($map as $mfl_id => $abbr) {
        if ($abbr !== '') $ids[$abbr] = $mfl_id;
    }
    return rest_ensure_response([
        'year'            => $next_year,
        'source_year'     => $last_year,
        'already_existed' => $already_existed,
        'ids'             => $ids,
    ]);
}

// ── Position Plus/Minus ───────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/stats/position-plus-minus', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_position_plus_minus',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_position_plus_minus() {
    $cache = get_transient('pfl_position_plus_minus_v4');
    if ($cache !== false) return rest_ensure_response($cache);

    global $wpdb;

    ob_start();
    $comps = get_or_set_comps();
    ob_end_clean();
    $teamlist = teamlist();

    // Table names on this server are lowercase; p_id in wp_players is mixed-case.
    // Normalize to lowercase for the existence check.
    $existing = array_flip(array_map('strtolower', $wpdb->get_col("SHOW TABLES")));

    $players_raw = $wpdb->get_results(
        "SELECT p_id, playerFirst, playerLast, position FROM wp_players WHERE position IN ('QB','RB','WR','PK')",
        ARRAY_A
    );

    $by_pos  = ['QB' => [], 'RB' => [], 'WR' => [], 'PK' => []];
    $names   = [];
    foreach ($players_raw as $p) {
        $pid = $p['p_id'];
        if (!isset($existing[strtolower($pid)])) continue;
        $by_pos[$p['position']][$pid] = true;
        $names[$pid] = substr($p['playerFirst'], 0, 1) . '. ' . $p['playerLast'];
    }

    $top_players = []; // [team][pos] => [{name, pts}, ...]
    foreach ($by_pos as $pos => $pids) {
        if (empty($pids)) continue;
        $parts = [];
        foreach (array_keys($pids) as $pid) {
            $safe = esc_sql($pid);
            $parts[] = "SELECT '{$safe}' AS pid, team, SUM(points) AS pts FROM `{$safe}` WHERE team != '' GROUP BY team";
        }
        $rows = $wpdb->get_results(implode(' UNION ALL ', $parts), ARRAY_A);
        foreach ($rows as $row) {
            $team = $row['team'];
            $pts  = (int) $row['pts'];
            $pid  = $row['pid'];
            if ($pts <= 0 || !isset($names[$pid])) continue;
            $top_players[$team][$pos][] = ['name' => $names[$pid], 'pts' => $pts];
        }
    }
    foreach ($top_players as $team => &$positions) {
        foreach ($positions as $pos => &$pl) {
            usort($pl, fn($a, $b) => $b['pts'] - $a['pts']);
            $pl = array_slice($pl, 0, 3);
        }
    }
    unset($positions, $pl);

    $result = [];
    foreach ($teamlist as $abbr => $name) {
        if (!isset($comps[$abbr])) continue;
        $pos = $comps[$abbr];
        $result[] = [
            'team'       => $abbr,
            'teamName'   => $name,
            'QB'         => (int) round($pos['QB'] ?? 0),
            'RB'         => (int) round($pos['RB'] ?? 0),
            'WR'         => (int) round($pos['WR'] ?? 0),
            'PK'         => (int) round($pos['PK'] ?? 0),
            'topPlayers' => [
                'QB' => $top_players[$abbr]['QB'] ?? [],
                'RB' => $top_players[$abbr]['RB'] ?? [],
                'WR' => $top_players[$abbr]['WR'] ?? [],
                'PK' => $top_players[$abbr]['PK'] ?? [],
            ],
        ];
    }

    set_transient('pfl_position_plus_minus_v4', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── Mr. Irrelevant (last pick each draft) ────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/drafts/mr-irrelevant', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_draft_mr_irrelevant',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_draft_mr_irrelevant() {
    $cache = get_transient('pfl_draft_mr_irrelevant_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT d.year, d.round, d.picknum, d.team, d.playerfirst, d.playerlast, d.pos,
                IFNULL(d.playerid, '') AS playerid,
                t.team AS team_name
         FROM wp_drafts d
         LEFT JOIN wp_teams t ON t.team_int = d.team
         WHERE d.id IN (SELECT MAX(id) FROM wp_drafts GROUP BY year)
         ORDER BY d.year ASC",
        ARRAY_A
    );

    $result = [];
    foreach ($rows as $row) {
        $pid = $row['playerid'] ?? '';

        $card = null;
        if ($pid) {
            $req = new WP_REST_Request('GET', '/pfl/v1/player-card');
            $req->set_param('pid', $pid);
            $resp = pfl_api_player_card($req);
            if (!is_wp_error($resp)) {
                $card = $resp->get_data();
            }
        }

        $result[] = [
            'year'         => (int) $row['year'],
            'round'        => $row['round'],
            'picknum'      => $row['picknum'],
            'draftTeam'    => $row['team'],
            'draftTeamName'=> $row['team_name'] ?? $row['team'],
            'playerid'     => $pid,
            'first'        => $row['playerfirst'],
            'last'         => $row['playerlast'],
            'pos'          => $row['pos'],
            'card'         => $card,
        ];
    }

    set_transient('pfl_draft_mr_irrelevant_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── Playoff Probability Matrix ────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/resources/playoff-probability', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_playoff_probability',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_playoff_probability() {
    $cache = get_transient('pfl_playoff_probability_v1');
    if ($cache !== false) return rest_ensure_response($cache);

    $getyears = the_seasons();
    $teamlist  = get_teams();
    $teams     = array_keys($teamlist);

    $cleanedstanding = [];
    foreach ($getyears as $y) {
        $seeds = get_seeds_by_year($y);
        foreach ($teams as $t) {
            $curteam      = isset($seeds[$t]) ? (int) $seeds[$t] : 0;
            $teamstanding = get_standings_weekly_by_team($t, $y, 14);
            if (!$teamstanding) continue;
            $w = 0;
            foreach ($teamstanding as $value) {
                if ($value['win'] == 1) $w++;
                $cleanedstanding[$y . $t][$value['week']] = [
                    'week'    => (int) $value['week'],
                    'wins'    => $w,
                    'made_pl' => $curteam > 0 ? 1 : 0,
                ];
            }
        }
    }

    $byweek = [];
    foreach ($cleanedstanding as $values) {
        foreach ($values as $v) {
            $byweek[$v['week']][$v['wins']][] = $v;
        }
    }

    $matrix = [];
    for ($wins = 0; $wins <= 14; $wins++) {
        $row = [];
        for ($week = 1; $week <= 14; $week++) {
            if ($week < $wins || empty($byweek[$week][$wins])) {
                $row[] = 'X';
                continue;
            }
            $entries       = $byweek[$week][$wins];
            $total         = count($entries);
            $made           = count(array_filter($entries, fn($e) => $e['made_pl'] === 1));
            $missed         = $total - $made;
            $row[] = $missed === 0 ? '100%' : number_format(($made / $total) * 100, 1) . '%';
        }
        $matrix[] = $row;
    }

    $result = [
        'matrix' => $matrix,
        'weeks'  => range(1, 14),
        'wins'   => range(0, 14),
    ];

    set_transient('pfl_playoff_probability_v1', $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

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

// ── GAME PLAYER NOTES — save AI-generated per-player notes to wp_game_notes ──
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/game-summary', [
        'methods'             => 'POST',
        'callback'            => 'pfl_api_save_game_summary',
        'permission_callback' => '__return_true',
        'args'                => [
            'year'         => ['required' => true, 'type' => 'integer'],
            'week'         => ['required' => true, 'type' => 'integer'],
            'home_team'    => ['required' => true, 'type' => 'string'],
            'player_notes' => ['required' => true],
        ],
    ]);
});

function pfl_api_save_game_summary(WP_REST_Request $request) {
    global $wpdb;
    $year         = (int) $request->get_param('year');
    $week         = (int) $request->get_param('week');
    $week_pad     = str_pad($week, 2, '0', STR_PAD_LEFT);
    $weekid       = (int) ($year . $week_pad);
    $home_team    = strtoupper(sanitize_text_field($request->get_param('home_team')));
    $player_notes = $request->get_param('player_notes');

    if (!is_array($player_notes)) {
        return new WP_Error('invalid_notes', 'player_notes must be an array', ['status' => 400]);
    }

    // Sanitize each note
    $clean = array_map(function($n) {
        return [
            'pid'  => sanitize_text_field($n['pid'] ?? ''),
            'name' => sanitize_text_field($n['name'] ?? ''),
            'note' => sanitize_textarea_field($n['note'] ?? ''),
        ];
    }, $player_notes);

    // Replace any existing AI notes for this game
    $wpdb->delete('wp_game_notes', ['weekid' => $weekid, 'hometeam' => $home_team, 'note_type' => 'ai_player_notes']);
    $wpdb->insert('wp_game_notes', [
        'weekid'    => $weekid,
        'hometeam'  => $home_team,
        'note'      => wp_json_encode($clean),
        'note_type' => 'ai_player_notes',
    ]);

    delete_transient("pfl_weekly_results_{$year}{$week_pad}_v15");

    return rest_ensure_response(['success' => true]);
}

// ── WEEK RESOURCES (links to external/local research assets) ─────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/week-resources', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_week_resources',
        'permission_callback' => '__return_true',
        'args'                => [
            'year' => ['required' => true, 'type' => 'integer'],
            'week' => ['required' => true, 'type' => 'integer'],
        ],
    ]);
});

function pfl_api_week_resources(WP_REST_Request $request) {
    $year = (int) $request->get_param('year');
    $week = (int) $request->get_param('week');

    if ($year < 1991 || $year > (int) date('Y') || $week < 1 || $week > 17) {
        return new WP_Error('invalid_params', 'Invalid year or week', ['status' => 400]);
    }

    $cache_key = "pfl_week_resources_{$year}_w{$week}_v1";
    $cached = get_transient($cache_key);
    if ($cached !== false) return rest_ensure_response($cached);

    $week_pad = sprintf('%02d', $week);
    $week_id  = $year . $week_pad;

    // ── Results Page ──────────────────────────────────────────────────────────
    $results_url = home_url('/results/?Y=' . $year . '&W=' . $week_pad);

    // ── Pro Football Reference ────────────────────────────────────────────────
    $pfr_url = 'https://www.pro-football-reference.com/years/' . $year . '/week_' . $week . '.htm';

    // ── NFL Dataset ───────────────────────────────────────────────────────────
    if ($year >= 2001) {
        $nfl_dataset = ['label' => 'ESPN API Dataset', 'url' => null];
    } else {
        $theme_path = get_stylesheet_directory();
        $positions  = ['QB', 'RB', 'WR', 'PK'];
        $available  = [];
        foreach ($positions as $pos) {
            if (file_exists($theme_path . '/pfr-raw-season/' . $year . '-' . $pos . '.csv')) {
                $available[] = $pos;
            }
        }
        $nfl_dataset = !empty($available)
            ? ['label' => 'PFR Dataset — ' . implode(', ', $available), 'url' => null]
            : ['label' => null, 'url' => null];
    }

    // ── MFL Results ───────────────────────────────────────────────────────────
    $mfl_ids = [2012 => '47001', 2013 => '23875', 2014 => '11521', 2015 => '47099'];
    $mfl_id  = isset($mfl_ids[$year]) ? $mfl_ids[$year] : ($year >= 2016 ? '38954' : '');
    $mfl_url = $mfl_id
        ? 'https://www47.myfantasyleague.com/' . $year . '/weekly?L=' . $mfl_id . '&W=' . $week
        : null;

    // ── PFL Update PDF & Raw Data — from ACF repeater stored as WP options ───
    $pdf_count      = (int) get_option('options_update_pdfs', 0);
    $pfl_update_url = null;
    $raw_data_url   = null;
    for ($i = 0; $i < $pdf_count; $i++) {
        $opt_week_id = get_option("options_update_pdfs_{$i}_week_id");
        if ($opt_week_id != $week_id) continue;

        $pdf_id = get_option("options_update_pdfs_{$i}_pdf_file");
        if ($pdf_id) {
            $url = wp_get_attachment_url($pdf_id);
            if ($url) $pfl_update_url = $url;
        }

        $raw_id = get_option("options_update_pdfs_{$i}_raw_data");
        if ($raw_id) {
            $url = wp_get_attachment_url($raw_id);
            if ($url) $raw_data_url = $url;
        }
        break;
    }

    $result = [
        'year'        => $year,
        'week'        => $week,
        'results_page' => $results_url,
        'pfr'          => $pfr_url,
        'nfl_dataset'  => $nfl_dataset,
        'mfl'          => $mfl_url,
        'pfl_update'   => $pfl_update_url,
        'raw_data'     => $raw_data_url,
    ];

    set_transient($cache_key, $result, DAY_IN_SECONDS);
    return rest_ensure_response($result);
}

// ── RESULTS SIDEBAR (week-specific leaders, standings, POTW) ─────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/results-sidebar', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_results_sidebar',
        'permission_callback' => '__return_true',
        'args'                => [
            'year' => ['required' => true, 'type' => 'integer'],
            'week' => ['required' => true, 'type' => 'integer'],
        ],
    ]);
});

function pfl_api_results_sidebar(WP_REST_Request $request) {
    global $wpdb;

    $year = (int) $request->get_param('year');
    $week = (int) $request->get_param('week');

    if ($year < 1991 || $year > (int) date('Y') || $week < 1 || $week > 16) {
        return new WP_Error('invalid_params', 'Invalid year or week', ['status' => 400]);
    }

    $cache_key = "pfl_results_sidebar_{$year}_w{$week}_v3";
    $cached = get_transient($cache_key);
    if ($cached !== false) return rest_ensure_response($cached);

    // ── Team name lookup ──────────────────────────────────────────────────────
    $team_name_map = [];
    foreach ($wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A) as $r) {
        $team_name_map[$r['team_int']] = $r['team'];
    }

    // ── Playoff weeks (15/16): return only POTW, no standings or leaders ──────
    if ($week === 15 || $week === 16) {
        $potw = null;

        if ($week === 15) {
            // Playoff POTW comes from wp_player_of_week (weekid ends in 15)
            $weekid   = sprintf('%04d%02d', $year, 15);
            $potw_pid = $wpdb->get_var($wpdb->prepare(
                "SELECT playerid FROM wp_player_of_week WHERE weekid = %s",
                $weekid
            ));
        } else {
            // Posse Bowl POTW is the Posse Bowl MVP from wp_awards
            $potw_pid = $wpdb->get_var($wpdb->prepare(
                "SELECT pid FROM wp_awards WHERE award = 'Posse Bowl MVP' AND year = %d LIMIT 1",
                $year
            ));
        }

        if ($potw_pid && preg_match('/^[A-Za-z0-9]+$/', $potw_pid)) {
            $pname = $wpdb->get_row($wpdb->prepare(
                "SELECT playerFirst AS first, playerLast AS last FROM wp_players WHERE p_id = %s",
                $potw_pid
            ), ARRAY_A);

            $prow = $wpdb->get_row($wpdb->prepare(
                "SELECT SUM(points) AS points, team FROM wp_playoffs
                 WHERE playerid = %s AND year = %d AND week = %d GROUP BY team LIMIT 1",
                $potw_pid, $year, $week
            ), ARRAY_A);

            $ppoints  = $prow ? (float) $prow['points'] : null;
            $team_int = $prow['team'] ?? null;
            $team_name = $team_int ? ($team_name_map[$team_int] ?? $team_int) : null;

            $potw = [
                'week'   => $week,
                'pid'    => $potw_pid,
                'first'  => $pname['first'] ?? '',
                'last'   => $pname['last'] ?? '',
                'team'   => $team_name,
                'points' => $ppoints,
                'img'    => pfl_player_img_url($potw_pid),
            ];
        }

        $result = [
            'year'      => $year,
            'week'      => $week,
            'standings' => [],
            'leaders'   => ['QB' => [], 'RB' => [], 'WR' => [], 'PK' => []],
            'potw'      => $potw,
        ];

        $ttl = ($year >= (int) date('Y')) ? HOUR_IN_SECONDS : DAY_IN_SECONDS;
        set_transient($cache_key, $result, $ttl);
        return rest_ensure_response($result);
    }

    // ── Standings: teams + divisions from stand{year}, game data from wp_team_* ──
    // Use ARRAY_N (positional): [0]=id [1]=year [2]=seed [3]=division [4]=teamid [5]=teamname
    $stand_rows = $wpdb->get_results("SELECT * FROM stand{$year}", ARRAY_N);
    $team_divisions = [];
    $team_fullnames = [];
    foreach ($stand_rows as $r) {
        $team_divisions[$r[4]] = $r[3];
        $team_fullnames[$r[4]] = $r[5];
    }

    $team_stats = [];
    foreach ($team_divisions as $team => $div) {
        if (!preg_match('/^[A-Za-z0-9]+$/', $team)) continue;

        // [0]=id [1]=season [2]=week [3]=team_int [4]=points [5]=versus [6]=versus_pts [7]=home_away [8]=stadium [9]=result
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `wp_team_{$team}` WHERE season = %d AND week <= %d",
            $year, $week
        ), ARRAY_N);

        $wins = $losses = $pts = $ptsvs = $divwin = $divloss = 0;
        foreach ($rows as $r) {
            $result   = (float) $r[9];
            $opp      = $r[5];
            $opp_div  = $team_divisions[$opp] ?? null;
            $is_win   = $result > 0;

            if ($is_win) { $wins++;   } else { $losses++; }
            $pts   += (float) $r[4];
            $ptsvs += (float) $r[6];

            if ($opp_div === $div) {
                if ($is_win) { $divwin++; } else { $divloss++; }
            }
        }

        $team_stats[$team] = [
            'teamid'  => $team,
            'name'    => $team_fullnames[$team] ?? ($team_name_map[$team] ?? $team),
            'div'     => $div,
            'win'     => $wins,
            'loss'    => $losses,
            'pts'     => round($pts, 1),
            'ptsvs'   => round($ptsvs, 1),
            'divwin'  => $divwin,
            'divloss' => $divloss,
            'gb'      => 0,
        ];
    }

    // Group by division, sort, compute GB
    $divs_grouped = [];
    foreach ($team_stats as $t) {
        $divs_grouped[$t['div']][] = $t;
    }
    $standings_result = [];
    foreach ($divs_grouped as $div => $teams) {
        usort($teams, fn($a, $b) => $b['win'] <=> $a['win'] ?: $b['pts'] <=> $a['pts']);
        $leader_wins = $teams[0]['win'];
        foreach ($teams as &$t) {
            $t['gb'] = ($t['win'] === $leader_wins) ? 0 : round(($leader_wins - $t['win']) / 1, 1);
        }
        unset($t);
        $standings_result[] = ['division' => $div, 'teams' => $teams];
    }

    // ── Leaders: sum player points up to $week from individual tables ─────────
    $leader_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT sl.playerid, p.playerFirst AS first, p.playerLast AS last
         FROM wp_season_leaders sl
         JOIN wp_players p ON p.p_id = sl.playerid
         WHERE sl.season = %d",
        $year
    ), ARRAY_A);

    $by_pos = ['QB' => [], 'RB' => [], 'WR' => [], 'PK' => []];

    foreach ($leader_rows as $row) {
        $pid = $row['playerid'];
        $pos = strtoupper(substr($pid, -2));
        if (!isset($by_pos[$pos])) continue;
        if (!preg_match('/^[A-Za-z0-9]+$/', $pid)) continue;

        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT SUM(points) AS total, COUNT(*) AS games
             FROM `{$pid}` WHERE year = %d AND week <= %d AND week < 15",
            $year, $week
        ), ARRAY_A);

        $total_pts = (float) ($stats['total'] ?? 0);
        $games     = (int)   ($stats['games'] ?? 0);

        // Teams this player played for through this week
        $team_rows2 = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT team FROM `{$pid}`
             WHERE year = %d AND week <= %d AND week < 15 AND team != '' AND team IS NOT NULL",
            $year, $week
        ), ARRAY_A);
        $teams = array_values(array_column($team_rows2, 'team'));

        $by_pos[$pos][] = [
            'pid'    => $pid,
            'first'  => $row['first'],
            'last'   => $row['last'],
            'points' => $total_pts,
            'games'  => $games,
            'teams'  => $teams,
        ];
    }

    foreach ($by_pos as &$players) {
        usort($players, fn($a, $b) => $b['points'] <=> $a['points']);
    }
    unset($players);

    // ── POTW: single entry for this specific week ─────────────────────────────
    $weekid = sprintf('%04d%02d', $year, $week);
    $potw_pid = $wpdb->get_var($wpdb->prepare(
        "SELECT playerid FROM wp_player_of_week WHERE weekid = %s",
        $weekid
    ));

    $potw = null;
    if ($potw_pid && preg_match('/^[A-Za-z0-9]+$/', $potw_pid)) {
        $pname = $wpdb->get_row($wpdb->prepare(
            "SELECT playerFirst AS first, playerLast AS last FROM wp_players WHERE p_id = %s",
            $potw_pid
        ), ARRAY_A);

        $prow = $wpdb->get_row($wpdb->prepare(
            "SELECT points, team FROM `{$potw_pid}` WHERE week_id = %s LIMIT 1",
            $weekid
        ), ARRAY_A);

        $ppoints   = $prow ? (float) $prow['points'] : null;
        $team_int  = $prow['team'] ?? null;
        $team_name = $team_int ? ($team_name_map[$team_int] ?? $team_int) : null;

        $potw = [
            'week'   => $week,
            'pid'    => $potw_pid,
            'first'  => $pname['first'] ?? '',
            'last'   => $pname['last'] ?? '',
            'team'   => $team_name,
            'points' => $ppoints,
            'img'    => pfl_player_img_url($potw_pid),
        ];
    }

    $result = [
        'year'      => $year,
        'week'      => $week,
        'standings' => $standings_result,
        'leaders'   => $by_pos,
        'potw'      => $potw,
    ];

    // Cache for 1 hour for current season, 24h for past seasons
    $ttl = ($year >= (int) date('Y')) ? HOUR_IN_SECONDS : DAY_IN_SECONDS;
    set_transient($cache_key, $result, $ttl);
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
    $cache_key = "pfl_season_rosters_{$year}_v8";

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

    // Supplement with playoff-only players — players who appeared in wp_playoffs for this
    // year/team but have no regular-season roster entry for that team
    $playoff_supplement = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DISTINCT playerid AS pid, team FROM wp_playoffs WHERE year = %d AND playerid != '' AND playerid != 'None'",
            $year
        ),
        ARRAY_A
    );
    foreach ($playoff_supplement as $row) {
        $pid  = trim($row['pid']);
        $team = trim($row['team']);
        $key  = $pid . '|' . $team;
        if ($pid && $team && !isset($roster_key_set[$key])) {
            $roster_rows[]        = ['pid' => $pid, 'team' => $team];
            $roster_key_set[$key] = true;
        }
    }

    // Deduplicate roster_rows — wp_rosters may contain duplicate (pid, team) entries
    $dedup = [];
    $roster_rows = array_values(array_filter($roster_rows, function($row) use (&$dedup) {
        $key = trim($row['pid']) . '|' . trim($row['team']);
        if (isset($dedup[$key])) return false;
        $dedup[$key] = true;
        return true;
    }));

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

// ─────────────────────────────────────────────────────────────────────────────
// ESPN BENCH SCORE HELPERS (mirrors getplayernfldata.py scoring logic)
// ─────────────────────────────────────────────────────────────────────────────

function pfl_get_week_espn_player_stats($year, $week) {
    if ($year < 2011) return []; // bench data not recorded before 2011
    $cache_key = "pfl_espn_stats_{$year}_" . str_pad($week, 2, '0', STR_PAD_LEFT);
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    // Fetch scoreboard for this week
    $sb = wp_remote_get(
        "http://site.api.espn.com/apis/site/v2/sports/football/nfl/scoreboard?week={$week}&seasontype=2&dates={$year}",
        ['timeout' => 30]
    );
    if (is_wp_error($sb)) return [];
    $scoreboard = json_decode(wp_remote_retrieve_body($sb), true);

    $all = []; // player displayName => raw stat buckets

    foreach ($scoreboard['events'] ?? [] as $event) {
        $game_id = $event['id'] ?? '';
        if (!$game_id) continue;

        $gs = wp_remote_get(
            "http://site.api.espn.com/apis/site/v2/sports/football/nfl/summary?event={$game_id}",
            ['timeout' => 30]
        );
        if (is_wp_error($gs)) continue;
        $summary = json_decode(wp_remote_retrieve_body($gs), true);

        foreach ($summary['boxscore']['players'] ?? [] as $team_data) {
            foreach ($team_data['statistics'] ?? [] as $stat_cat) {
                $cat     = strtolower($stat_cat['name'] ?? '');
                $labels  = $stat_cat['labels'] ?? [];
                foreach ($stat_cat['athletes'] ?? [] as $athlete) {
                    $name  = $athlete['athlete']['displayName'] ?? '';
                    $stats = $athlete['stats'] ?? [];
                    if (!$name) continue;
                    if (!isset($all[$name])) {
                        $all[$name] = ['pass_yds'=>0,'pass_td'=>0,'pass_int'=>0,'rush_yds'=>0,'rush_td'=>0,'rec_yds'=>0,'rec_td'=>0,'xpm'=>0,'fgm'=>0];
                    }
                    if (strpos($cat, 'passing') !== false) {
                        foreach ($labels as $i => $lbl) {
                            if (!array_key_exists($i, $stats)) break;
                            if ($lbl === 'YDS') $all[$name]['pass_yds'] += (int) $stats[$i];
                            elseif ($lbl === 'TD')  $all[$name]['pass_td']  += (int) $stats[$i];
                            elseif ($lbl === 'INT') $all[$name]['pass_int'] += (int) $stats[$i];
                        }
                    } elseif (strpos($cat, 'rushing') !== false) {
                        foreach ($labels as $i => $lbl) {
                            if (!array_key_exists($i, $stats)) break;
                            if ($lbl === 'YDS') $all[$name]['rush_yds'] += (int) $stats[$i];
                            elseif ($lbl === 'TD') $all[$name]['rush_td'] += (int) $stats[$i];
                        }
                    } elseif (strpos($cat, 'receiving') !== false) {
                        foreach ($labels as $i => $lbl) {
                            if (!array_key_exists($i, $stats)) break;
                            if ($lbl === 'YDS') $all[$name]['rec_yds'] += (int) $stats[$i];
                            elseif ($lbl === 'TD') $all[$name]['rec_td'] += (int) $stats[$i];
                        }
                    } elseif (strpos($cat, 'kicking') !== false) {
                        foreach ($labels as $i => $lbl) {
                            if (!array_key_exists($i, $stats)) break;
                            if ($lbl === 'FG') {
                                $parts = explode('/', (string) $stats[$i]);
                                $all[$name]['fgm'] += (int) ($parts[0] ?? 0);
                            } elseif ($lbl === 'XP') {
                                $parts = explode('/', (string) $stats[$i]);
                                $all[$name]['xpm'] += (int) ($parts[0] ?? 0);
                            }
                        }
                    }
                }
            }
        }
    }

    set_transient($cache_key, $all, 14 * DAY_IN_SECONDS);
    return $all;
}

function pfl_calc_bench_score($player_name, $position, $year, $espn_stats) {
    // Exact match first, then case-insensitive, then last-name partial
    $data = $espn_stats[$player_name] ?? null;
    if (!$data) {
        foreach ($espn_stats as $name => $d) {
            if (strcasecmp($name, $player_name) === 0) { $data = $d; break; }
        }
    }
    if (!$data) {
        $parts     = explode(' ', $player_name);
        $last      = end($parts);
        $first_ini = isset($parts[0]) ? strtoupper($parts[0][0]) : '';
        foreach ($espn_stats as $name => $d) {
            if (stripos($name, $last) !== false) {
                // Prefer match where first initial also matches
                $name_parts = explode(' ', $name);
                $name_ini   = isset($name_parts[0]) ? strtoupper($name_parts[0][0]) : '';
                if ($first_ini && $name_ini && $first_ini === $name_ini) { $data = $d; break; }
                if (!$data) $data = $d; // fallback: first last-name match
            }
        }
    }
    if (!$data) return null;

    $pos = strtoupper($position);
    if ($pos === 'PK') {
        return $data['xpm'] + ($data['fgm'] * 2);
    }
    $tds = $data['pass_td'] + $data['rush_td'] + $data['rec_td'];
    if ($year == 1991) {
        return intdiv($data['pass_yds'], 50) + intdiv($data['rush_yds'], 25) + ($tds * 2) + intdiv($data['rec_yds'], 25) - $data['pass_int'];
    }
    return intdiv($data['pass_yds'], 30) + intdiv($data['rush_yds'], 10) + ($tds * 2) + intdiv($data['rec_yds'], 10) - $data['pass_int'];
}

// ─────────────────────────────────────────────────────────────────────────────
// WEEKLY RESULTS — BOXSCORE ENDPOINT
// ─────────────────────────────────────────────────────────────────────────────

// Convert a game_date string (YYYY-MM-DD) to an approximate NFL season week (1–16).
// Sep(9)=wks1–4, Oct(10)=wks5–8, Nov(11)=wks9–12, Dec(12)=wks13–16, Jan(1)=wks15–16.
function pfl_game_date_to_season_week($date_str) {
    if (!$date_str) return null;
    $ts = strtotime($date_str);
    if (!$ts) return null;
    $month = (int) date('n', $ts);
    $day   = (int) date('j', $ts);
    if ($month === 1)           return $day > 7 ? 16 : 15;
    if ($month < 9 || $month > 12) return null;
    $week = (int)(($month - 9) * 4 + ($day - 1) / 7.7) + 1;
    return max(1, min(16, $week));
}

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/weekly-results', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_weekly_results',
        'permission_callback' => '__return_true',
    ]);
});

// Create the wp_attendance table on first use of the weekly-results endpoint.
function pfl_ensure_attendance_table() {
    global $wpdb;
    static $done = false;
    if ($done) return;
    $done = true;

    $wpdb->query("CREATE TABLE IF NOT EXISTS wp_attendance (
        id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        year             SMALLINT UNSIGNED NOT NULL,
        week             TINYINT UNSIGNED NOT NULL,
        home_team        VARCHAR(8) NOT NULL,
        away_team        VARCHAR(8) NOT NULL,
        stadium_name     VARCHAR(120) DEFAULT NULL,
        stadium_id       INT UNSIGNED DEFAULT NULL,
        capacity         MEDIUMINT UNSIGNED DEFAULT NULL,
        attendance_pct   DECIMAL(5,2) NOT NULL,
        attendance_count MEDIUMINT UNSIGNED DEFAULT NULL,
        factors_json     TEXT,
        computed_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_game (year, week, home_team),
        KEY ix_stadium_id (stadium_id),
        KEY ix_year (year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Resolve a stadium row's team_owned to the base team identifier used in
// wp_team_*, wp_attendance, standings, etc. (strips a trailing _OLD suffix).
// e.g. "BST_OLD" → "BST", "ETS" → "ETS".
function pfl_base_team($team_owned) {
    if (!$team_owned) return $team_owned;
    return preg_replace('/_OLD$/', '', $team_owned);
}

// Canonical stadium-name aliases — wp_team_*.stadium has spelling drift vs wp_stadiums.facility_names.
function pfl_canonical_stadium_name($name) {
    static $aliases = [
        'The Cuckoos Nest'  => "The Cuckoo's Nest",
        'Strawberry Fields' => "Octopus' Garden at Strawberry Fields",
        'Kennedy Compound'  => 'The Kennedy Compound',
        'Gigalo Pits'       => 'The Gigalo Pits',
        'The Litter Pan'    => 'The Litter Box',
        'The Reseviour'     => 'The Reservoir',
        'Dog House'         => 'The Dog House',
    ];
    if (!$name) return $name;
    $name = trim($name);
    return $aliases[$name] ?? $name;
}

// Persist a week's attendance rows into wp_attendance (UPSERT per home game).
function pfl_write_attendance_rows($year, $week, $games) {
    global $wpdb;
    static $sid_by_name = null;
    if ($sid_by_name === null) {
        $sid_by_name = [];
        foreach ($wpdb->get_results("SELECT id, facility_names FROM wp_stadiums", ARRAY_A) as $r) {
            $sid_by_name[$r['facility_names']] = (int) $r['id'];
        }
    }

    foreach ($games as $g) {
        $home    = $g['home']  ?? null;
        $away    = $g['away']  ?? null;
        $pct     = $g['attendance_pct'] ?? null;
        if (!$home || !$away || $pct === null) continue;

        $stadium_raw = $g['stadium'] ?? null;
        $stadium     = pfl_canonical_stadium_name($stadium_raw);
        $capacity    = $g['stadium_capacity'] ?? null;
        $sid         = $stadium ? ($sid_by_name[$stadium] ?? null) : null;
        $count       = ($capacity && $pct !== null) ? (int) round($capacity * $pct / 100) : null;
        $factors     = isset($g['attendance_factors']) ? wp_json_encode($g['attendance_factors']) : null;

        $wpdb->query($wpdb->prepare(
            "INSERT INTO wp_attendance
               (year, week, home_team, away_team, stadium_name, stadium_id, capacity, attendance_pct, attendance_count, factors_json)
             VALUES (%d, %d, %s, %s, %s, %s, %s, %f, %s, %s)
             ON DUPLICATE KEY UPDATE
               away_team        = VALUES(away_team),
               stadium_name     = VALUES(stadium_name),
               stadium_id       = VALUES(stadium_id),
               capacity         = VALUES(capacity),
               attendance_pct   = VALUES(attendance_pct),
               attendance_count = VALUES(attendance_count),
               factors_json     = VALUES(factors_json),
               computed_at      = CURRENT_TIMESTAMP",
            $year, $week, $home, $away,
            $stadium, $sid, $capacity, $pct, $count, $factors
        ));
    }
}

function pfl_api_weekly_results(WP_REST_Request $request) {
    global $wpdb;

    $year      = (int) ($request->get_param('year') ?: date('Y'));
    $week_raw  = $request->get_param('week');
    $week      = (int) ($week_raw ?: 1);
    $week_pad  = str_pad($week, 2, '0', STR_PAD_LEFT);
    $weekid    = $year . $week_pad;
    $cache_key = "pfl_weekly_results_{$year}{$week_pad}_v37";
    $force     = $request->get_param('force') ? true : false;

    pfl_ensure_attendance_table();

    if (!$force) {
        $cached = get_transient($cache_key);
        if ($cached !== false) return rest_ensure_response($cached);
    }

    $theme_uri = get_stylesheet_directory_uri();

    // ── All seasons (for dropdown) ──────────────────────────────────────────
    // Column is named 'season' not 'year' in wp_team_* tables
    $seasons = array_map('intval', $wpdb->get_col(
        "SELECT DISTINCT season FROM wp_team_WRZ ORDER BY season DESC"
    ));

    // ── Weeks available for this year ───────────────────────────────────────
    $weeks = array_map('intval', $wpdb->get_col(
        $wpdb->prepare("SELECT DISTINCT week FROM wp_team_WRZ WHERE season = %d ORDER BY week ASC", $year)
    ));

    // Append playoff weeks (15 = Playoffs, 16 = Posse Bowl) if data exists
    $playoff_weeks_db = array_map('intval', $wpdb->get_col(
        $wpdb->prepare("SELECT DISTINCT week FROM wp_playoffs WHERE year = %d ORDER BY week ASC", $year)
    ));
    foreach ($playoff_weeks_db as $pw) {
        if (!in_array($pw, $weeks)) $weeks[] = $pw;
    }
    sort($weeks);

    // ── Team names ──────────────────────────────────────────────────────────
    $team_name_rows = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    $team_names = [];
    foreach ($team_name_rows as $r) {
        $team_names[$r['team_int']] = $r['team'];
    }

    // ── All 15 teams (shared by regular-season and playoff paths) ───────────
    $all_teams = ['RBS','ETS','PEP','WRZ','CMN','BUL','SNR','TSG','BST','MAX','PHR','SON','ATK','HAT','DST'];

    // ── Stadium attribute maps ───────────────────────────────────────────────
    // team_owned → attrs for the team's CURRENT stadium (fallback for callers
    // that don't know the venue yet — e.g. the attendance pre-compute loop).
    // facility_names → attrs for ERA-correct lookups during game assembly.
    $stadium_capacity_map      = [];
    $stadium_roof_type_map     = [];
    $stadium_region_map        = [];
    $stadium_capacity_by_venue = [];
    $stadium_roof_by_venue     = [];
    $stadium_region_by_venue   = [];
    $stad_rows = $wpdb->get_results(
        "SELECT facility_names, team_owned, capacity, roof_type, region, currently_occupied FROM wp_stadiums ORDER BY id ASC",
        ARRAY_A
    );
    foreach ($stad_rows as $sr) {
        $tm  = strtoupper($sr['team_owned']);
        $fn  = $sr['facility_names'];
        $cap = (int) $sr['capacity'];
        $rf  = $sr['roof_type'] ?? 'Open';
        $rg  = $sr['region']    ?? null;
        // First pass: anything (overwritten by current row below if present)
        $stadium_capacity_map[$tm]  = $cap;
        $stadium_roof_type_map[$tm] = $rf;
        $stadium_region_map[$tm]    = $rg;
        if (!empty($fn)) {
            $stadium_capacity_by_venue[$fn] = $cap;
            $stadium_roof_by_venue[$fn]     = $rf;
            $stadium_region_by_venue[$fn]   = $rg;
        }
    }
    foreach ($stad_rows as $sr) {
        if ((int)($sr['currently_occupied'] ?? 0) === 1) {
            $tm = strtoupper($sr['team_owned']);
            $stadium_capacity_map[$tm]  = (int) $sr['capacity'];
            $stadium_roof_type_map[$tm] = $sr['roof_type'] ?? 'Open';
            $stadium_region_map[$tm]    = $sr['region']    ?? null;
        }
    }

    // ── Stadium image map: ACF repeater on post 299, one row per team ────────
    // Sub-field 'gallery' stores a serialized array of attachment IDs (ACF gallery type).
    // Legacy fallback: single 'image' field stores one attachment ID.
    $STADIUM_POST_ID = 299;
    $stadium_image_map = [];
    $img_team_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = %d AND meta_key LIKE 'stadium_%%_team'",
        $STADIUM_POST_ID
    ), ARRAY_A);
    foreach ($img_team_rows as $itr) {
        if (!preg_match('/stadium_(\d+)_team/', $itr['meta_key'], $m)) continue;
        $idx      = $m[1];
        $tm_upper = strtoupper($itr['meta_value']);
        $urls     = [];

        $raw = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM wp_postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $STADIUM_POST_ID, "stadium_{$idx}_image"
        ));
        if ($raw) {
            $val = maybe_unserialize($raw);
            // Gallery field: serialized array of attachment IDs
            if (is_array($val)) {
                foreach ($val as $aid) {
                    $u = wp_get_attachment_url((int) $aid);
                    if ($u) $urls[] = $u;
                }
            // Legacy single image field: plain integer ID
            } elseif ((int) $val > 0) {
                $u = wp_get_attachment_url((int) $val);
                if ($u) $urls[] = $u;
            }
        }

        if (!empty($urls)) $stadium_image_map[$tm_upper] = $urls;
    }

    // ── Facility/team mappings for era-aware stadium image lookups ───────────
    // Two patterns are supported for a team that changed stadiums:
    //   1) Old row has team_owned = "{TEAM}_OLD" (e.g. BST_OLD) — covered by
    //      a direct facility_name → team_owned lookup.
    //   2) Old row keeps team_owned = "{TEAM}" but the ACF gallery for that
    //      era is stored under "{TEAM}_OLD" — covered by a fallback below.
    $stadium_acf_key_by_facility = [];
    $current_facility_by_team    = [];
    foreach ($wpdb->get_results("SELECT facility_names, team_owned, currently_occupied FROM wp_stadiums", ARRAY_A) as $sr) {
        if (!empty($sr['facility_names']) && !empty($sr['team_owned'])) {
            $stadium_acf_key_by_facility[$sr['facility_names']] = $sr['team_owned'];
            if ((int) ($sr['currently_occupied'] ?? 0) === 1) {
                $current_facility_by_team[$sr['team_owned']] = $sr['facility_names'];
            }
        }
    }
    // Helper: given a venue name (as it appears in wp_team_*.stadium) and the
    // home team abbreviation, return the right gallery URLs.
    $get_stadium_images = function($venue_raw, $home_team_abbr) use (
        $stadium_image_map, $stadium_acf_key_by_facility, $current_facility_by_team
    ) {
        $venue   = pfl_canonical_stadium_name($venue_raw);
        $acf_key = $stadium_acf_key_by_facility[$venue] ?? $home_team_abbr;
        // Pattern (2): venue's team_owned is the base team, but the row isn't
        // the team's current facility → try the _OLD ACF key first.
        if (strtoupper($acf_key) === strtoupper($home_team_abbr)) {
            $current_facility = $current_facility_by_team[$home_team_abbr] ?? null;
            if ($current_facility && $current_facility !== $venue
                && isset($stadium_image_map[strtoupper($home_team_abbr) . '_OLD'])) {
                $acf_key = $home_team_abbr . '_OLD';
            }
        }
        return $stadium_image_map[strtoupper($acf_key)]
            ?? $stadium_image_map[strtoupper($home_team_abbr)]
            ?? [];
    };

    // ── Weather lookup: region × week → avg temp / wind / condition ──────────
    // Based on historical US climate averages. Week 1 ≈ early Sep, Week 16 ≈ early Jan.
    // Conditions: sunny | partly_cloudy | cloudy | rain | snow
    $weather_table = [
        'New England' => [
             1=>['t'=>65,'w'=> 8,'c'=>'sunny'],        2=>['t'=>63,'w'=> 8,'c'=>'sunny'],
             3=>['t'=>60,'w'=> 9,'c'=>'partly_cloudy'], 4=>['t'=>57,'w'=>10,'c'=>'partly_cloudy'],
             5=>['t'=>53,'w'=>11,'c'=>'cloudy'],        6=>['t'=>49,'w'=>12,'c'=>'cloudy'],
             7=>['t'=>45,'w'=>13,'c'=>'rain'],          8=>['t'=>41,'w'=>14,'c'=>'rain'],
             9=>['t'=>37,'w'=>15,'c'=>'rain'],         10=>['t'=>34,'w'=>16,'c'=>'rain'],
            11=>['t'=>31,'w'=>17,'c'=>'snow'],         12=>['t'=>29,'w'=>16,'c'=>'snow'],
            13=>['t'=>28,'w'=>15,'c'=>'snow'],         14=>['t'=>27,'w'=>15,'c'=>'snow'],
            15=>['t'=>26,'w'=>14,'c'=>'snow'],         16=>['t'=>28,'w'=>13,'c'=>'snow'],
        ],
        'Mid-Atlantic' => [
             1=>['t'=>72,'w'=> 7,'c'=>'sunny'],        2=>['t'=>70,'w'=> 7,'c'=>'sunny'],
             3=>['t'=>67,'w'=> 8,'c'=>'partly_cloudy'], 4=>['t'=>63,'w'=> 9,'c'=>'partly_cloudy'],
             5=>['t'=>58,'w'=>10,'c'=>'cloudy'],        6=>['t'=>53,'w'=>10,'c'=>'cloudy'],
             7=>['t'=>49,'w'=>11,'c'=>'rain'],          8=>['t'=>45,'w'=>12,'c'=>'rain'],
             9=>['t'=>41,'w'=>13,'c'=>'rain'],         10=>['t'=>37,'w'=>13,'c'=>'rain'],
            11=>['t'=>34,'w'=>14,'c'=>'snow'],         12=>['t'=>32,'w'=>13,'c'=>'snow'],
            13=>['t'=>31,'w'=>13,'c'=>'snow'],         14=>['t'=>30,'w'=>12,'c'=>'snow'],
            15=>['t'=>30,'w'=>12,'c'=>'snow'],         16=>['t'=>32,'w'=>11,'c'=>'snow'],
        ],
        'South East' => [
             1=>['t'=>81,'w'=> 6,'c'=>'sunny'],        2=>['t'=>79,'w'=> 6,'c'=>'sunny'],
             3=>['t'=>76,'w'=> 7,'c'=>'sunny'],        4=>['t'=>72,'w'=> 7,'c'=>'partly_cloudy'],
             5=>['t'=>67,'w'=> 7,'c'=>'partly_cloudy'], 6=>['t'=>62,'w'=> 8,'c'=>'partly_cloudy'],
             7=>['t'=>57,'w'=> 8,'c'=>'cloudy'],       8=>['t'=>53,'w'=> 8,'c'=>'cloudy'],
             9=>['t'=>49,'w'=> 9,'c'=>'rain'],        10=>['t'=>46,'w'=> 9,'c'=>'rain'],
            11=>['t'=>43,'w'=> 9,'c'=>'rain'],        12=>['t'=>41,'w'=> 8,'c'=>'rain'],
            13=>['t'=>40,'w'=> 8,'c'=>'cloudy'],      14=>['t'=>39,'w'=> 8,'c'=>'cloudy'],
            15=>['t'=>39,'w'=> 7,'c'=>'cloudy'],      16=>['t'=>42,'w'=> 7,'c'=>'partly_cloudy'],
        ],
        'South West' => [
             1=>['t'=>88,'w'=> 9,'c'=>'sunny'],        2=>['t'=>86,'w'=> 9,'c'=>'sunny'],
             3=>['t'=>83,'w'=> 9,'c'=>'sunny'],        4=>['t'=>78,'w'=>10,'c'=>'sunny'],
             5=>['t'=>72,'w'=>10,'c'=>'sunny'],        6=>['t'=>66,'w'=>10,'c'=>'partly_cloudy'],
             7=>['t'=>60,'w'=>10,'c'=>'partly_cloudy'], 8=>['t'=>55,'w'=>10,'c'=>'partly_cloudy'],
             9=>['t'=>51,'w'=>11,'c'=>'cloudy'],      10=>['t'=>47,'w'=>11,'c'=>'cloudy'],
            11=>['t'=>44,'w'=>11,'c'=>'rain'],        12=>['t'=>42,'w'=>10,'c'=>'rain'],
            13=>['t'=>41,'w'=>10,'c'=>'rain'],        14=>['t'=>41,'w'=>10,'c'=>'cloudy'],
            15=>['t'=>41,'w'=> 9,'c'=>'cloudy'],      16=>['t'=>45,'w'=> 9,'c'=>'partly_cloudy'],
        ],
        'Mid-West' => [
             1=>['t'=>67,'w'=>10,'c'=>'sunny'],        2=>['t'=>65,'w'=>10,'c'=>'sunny'],
             3=>['t'=>61,'w'=>11,'c'=>'partly_cloudy'], 4=>['t'=>56,'w'=>12,'c'=>'partly_cloudy'],
             5=>['t'=>50,'w'=>13,'c'=>'cloudy'],        6=>['t'=>44,'w'=>13,'c'=>'cloudy'],
             7=>['t'=>39,'w'=>14,'c'=>'rain'],          8=>['t'=>35,'w'=>15,'c'=>'rain'],
             9=>['t'=>31,'w'=>16,'c'=>'snow'],         10=>['t'=>27,'w'=>17,'c'=>'snow'],
            11=>['t'=>24,'w'=>17,'c'=>'snow'],         12=>['t'=>22,'w'=>16,'c'=>'snow'],
            13=>['t'=>20,'w'=>15,'c'=>'snow'],         14=>['t'=>19,'w'=>15,'c'=>'snow'],
            15=>['t'=>18,'w'=>14,'c'=>'snow'],         16=>['t'=>20,'w'=>13,'c'=>'snow'],
        ],
        'West' => [
             1=>['t'=>72,'w'=> 7,'c'=>'sunny'],        2=>['t'=>71,'w'=> 7,'c'=>'sunny'],
             3=>['t'=>69,'w'=> 8,'c'=>'sunny'],        4=>['t'=>65,'w'=> 8,'c'=>'partly_cloudy'],
             5=>['t'=>61,'w'=> 8,'c'=>'partly_cloudy'], 6=>['t'=>57,'w'=> 9,'c'=>'cloudy'],
             7=>['t'=>53,'w'=> 9,'c'=>'cloudy'],       8=>['t'=>50,'w'=>10,'c'=>'rain'],
             9=>['t'=>47,'w'=>10,'c'=>'rain'],        10=>['t'=>45,'w'=>10,'c'=>'rain'],
            11=>['t'=>43,'w'=>10,'c'=>'rain'],        12=>['t'=>42,'w'=> 9,'c'=>'rain'],
            13=>['t'=>42,'w'=> 9,'c'=>'cloudy'],      14=>['t'=>42,'w'=> 9,'c'=>'cloudy'],
            15=>['t'=>43,'w'=> 8,'c'=>'cloudy'],      16=>['t'=>46,'w'=> 8,'c'=>'partly_cloudy'],
        ],
    ];

    // Retractable roof closes when: temp < 55°F, temp > 85°F, rain, snow, or wind ≥ 18 mph (Windy)
    $weather_map = [];
    foreach ($all_teams as $tm) {
        $roof = $stadium_roof_type_map[$tm] ?? 'Open';
        if ($roof === 'Dome') {
            $weather_map[$tm] = ['temp' => 70, 'wind_mph' => 0, 'condition' => 'dome', 'roof_closed' => false];
            continue;
        }
        $region = $stadium_region_map[$tm] ?? null;
        $wdata  = ($region && isset($weather_table[$region][$week])) ? $weather_table[$region][$week] : null;
        $raw_temp = $wdata ? $wdata['t'] : 72;
        $raw_wind = $wdata ? $wdata['w'] : 8;
        $raw_cond = $wdata ? $wdata['c'] : 'sunny';
        if ($roof === 'Retractable') {
            $inclement = $raw_temp < 55 || $raw_temp > 85
                      || in_array($raw_cond, ['rain', 'snow'])
                      || $raw_wind >= 18;
            if ($inclement) {
                $weather_map[$tm] = ['temp' => 70, 'wind_mph' => 0, 'condition' => 'dome', 'roof_closed' => true];
                continue;
            }
        }
        $weather_map[$tm] = ['temp' => $raw_temp, 'wind_mph' => $raw_wind, 'condition' => $raw_cond, 'roof_closed' => false];
    }

    // ── Attendance algorithm: brand loyalty (career) × season form ───────────
    // One query per team: career win% + season record going into this week.

    // Defending champion: winning last year's Posse Bowl gets a buzz bonus
    $prev_champ_team = $wpdb->get_var($wpdb->prepare(
        "SELECT winTeam FROM wp_champions WHERE year = %d LIMIT 1", $year - 1
    ));
    $gotw_home = strtoupper(get_option("pfl_gotw_{$year}_{$week}", ''));

    $attendance_map         = [];
    $attendance_factors_map = [];

    // Read previously computed attendance from wp_attendance (single source of truth).
    // Stored values trump live computation; the live formula only runs for teams that
    // don't yet have a row (e.g., a never-fetched week or a fresh table).
    $stored_attendance = [];
    if ($wpdb->get_var("SHOW TABLES LIKE 'wp_attendance'") === 'wp_attendance') {
        $stored_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT home_team, attendance_pct, factors_json
             FROM wp_attendance WHERE year = %d AND week = %d",
            $year, $week
        ), ARRAY_A);
        foreach ($stored_rows as $sr) {
            $factors = $sr['factors_json'] ? json_decode($sr['factors_json'], true) : null;
            $stored_attendance[$sr['home_team']] = [
                'pct'     => (float) $sr['attendance_pct'],
                'factors' => $factors,
            ];
        }
    }

    foreach ($all_teams as $tm) {
        // Cache hit: reuse stored value from wp_attendance.
        if (isset($stored_attendance[$tm])) {
            $attendance_map[$tm]         = $stored_attendance[$tm]['pct'];
            $attendance_factors_map[$tm] = $stored_attendance[$tm]['factors'];
            continue;
        }

        $att_row = $wpdb->get_row($wpdb->prepare(
            "SELECT
                SUM(CASE WHEN result >= 0 THEN 1 ELSE 0 END)                                        AS career_wins,
                COUNT(*)                                                                              AS career_games,
                SUM(CASE WHEN season = %d AND week < %d AND result >= 0 THEN 1 ELSE 0 END)          AS season_wins,
                SUM(CASE WHEN season = %d AND week < %d THEN 1             ELSE 0 END)               AS season_games,
                SUM(CASE WHEN season = %d AND week < %d THEN points        ELSE 0 END)               AS season_pts,
                SUM(CASE WHEN season = %d AND week < %d THEN vs_points     ELSE 0 END)               AS season_ptsvs,
                SUM(CASE WHEN season = %d AND result >= 0 THEN 1 ELSE 0 END)                         AS prev_season_wins,
                SUM(CASE WHEN season = %d THEN 1 ELSE 0 END)                                         AS prev_season_games
            FROM wp_team_{$tm}",
            $year, $week, $year, $week, $year, $week, $year, $week, $year - 1, $year - 1
        ), ARRAY_A);
        if (!$att_row) {
            $attendance_map[$tm]         = 88.0;
            $attendance_factors_map[$tm] = null;
            continue;
        }

        // Brand loyalty: career win% — dominant, slow-moving factor
        $c_wins  = (int)($att_row['career_wins']  ?? 0);
        $c_games = (int)($att_row['career_games'] ?? 0);
        $c_pct   = $c_games > 0 ? $c_wins / $c_games : 0.5;
        // Ramp in over ~4 seasons (56 games) so early results don't over-index
        $brand_ramp   = min($c_games / 56.0, 1.0);
        // .600 ≈ +8%, .500 = 0%, .400 ≈ -8% — capped ±10%
        $brand_factor = max(-10.0, min(10.0, ($c_pct - 0.5) * 120.0 * $brand_ramp));

        // Current season form — ramps up to full weight after 7 games
        $s_wins   = (int)($att_row['season_wins']   ?? 0);
        $s_games  = (int)($att_row['season_games']  ?? 0);
        $s_pts    = (float)($att_row['season_pts']  ?? 0);
        $s_ptsvs  = (float)($att_row['season_ptsvs'] ?? 0);
        $s_ramp   = $s_games > 0 ? min($s_games / 7.0, 1.0) : 0.0;
        $s_pct    = $s_games > 0 ? $s_wins / $s_games : 0.5;
        // .800 ≈ +4.8%, .200 ≈ -4.8% — capped ±5%
        $season_factor = max(-5.0, min(5.0, ($s_pct - 0.5) * 16.0 * $s_ramp));
        // Points differential: competitiveness signal — capped ±2%
        $ppg_diff    = $s_games > 0 ? ($s_pts - $s_ptsvs) / $s_games : 0.0;
        $diff_factor = max(-2.0, min(2.0, ($ppg_diff / 15.0) * $s_ramp));

        // Brand recency: previous season win% — medium-weight bridge between career brand and current form
        $p_wins         = (int)($att_row['prev_season_wins']  ?? 0);
        $p_games        = (int)($att_row['prev_season_games'] ?? 0);
        $prev_s_pct     = $p_games > 0 ? $p_wins / $p_games : 0.5;
        // .786 (11-3) ≈ +3.4%, .857 (12-2) ≈ +5.1%, 1.000 → capped +6%, .250 (3-11) ≈ -4%, .000 → capped -6%
        $recency_factor = $p_games > 0 ? max(-6.0, min(6.0, ($prev_s_pct - 0.5) * 16.0)) : 0.0;

        // Defending champion buzz (previous year's Posse Bowl winner)
        $champ_bonus = ($prev_champ_team && $tm === $prev_champ_team) ? 6.0 : 0.0;

        // Game of the Week designation
        $gotw_bonus = ($gotw_home && $tm === $gotw_home) ? 5.0 : 0.0;

        // Weather factor — minor modifier based on game-day conditions (cap ±3%)
        // Dome / closed retractable = no impact (controlled climate)
        $w_data   = $weather_map[$tm] ?? [];
        $w_cond   = $w_data['condition'] ?? 'sunny';
        $w_temp   = (int)($w_data['temp']     ?? 70);
        $w_wind   = (int)($w_data['wind_mph'] ?? 8);
        $w_factor = 0.0;
        if ($w_cond !== 'dome') {
            // Condition base
            if      ($w_cond === 'sunny' && $w_temp >= 60 && $w_temp <= 85) $w_factor += 2.0; // perfect day
            elseif  ($w_cond === 'sunny')        $w_factor += 0.5;  // sunny but extreme temp
            elseif  ($w_cond === 'partly_cloudy') $w_factor += 0.5;
            elseif  ($w_cond === 'cloudy')        $w_factor -= 0.5;
            elseif  ($w_cond === 'rain')          $w_factor -= 2.5;
            elseif  ($w_cond === 'snow')          $w_factor += 2.0; // fans love snow games
            // Cold penalty
            if      ($w_temp < 25) $w_factor -= 2.0;
            elseif  ($w_temp < 35) $w_factor -= 1.5;
            elseif  ($w_temp < 45) $w_factor -= 1.0;
            // High wind penalty
            if      ($w_wind >= 25) $w_factor -= 1.0;
            elseif  ($w_wind >= 18) $w_factor -= 0.5;
            $w_factor = max(-4.0, min(3.0, $w_factor));
        }

        // Week context
        $wk_label  = 'regular';
        $wk_factor = 0.0;
        if ($week === 1)  { $wk_label = 'opener';    $wk_factor = 3.0;  }
        if ($week === 15) { $wk_label = 'playoffs';  $wk_factor = 6.0;  }
        if ($week === 16) { $wk_label = 'possebowl'; $wk_factor = 10.0; }

        $att_raw   = 88.0 + $brand_factor + $recency_factor + $season_factor + $diff_factor + $champ_bonus + $gotw_bonus + round($w_factor, 1) + $wk_factor;
        $att_final = round(max(72.0, min(102.0, $att_raw)), 1);

        $attendance_map[$tm] = $att_final;
        $attendance_factors_map[$tm] = [
            'base'               => 88.0,
            'brand_career_pct'   => round($c_pct * 100, 1),
            'brand_career_games' => $c_games,
            'brand_factor'       => round($brand_factor, 1),
            'prev_season_wins'   => $p_wins,
            'prev_season_games'  => $p_games,
            'prev_season_pct'    => $p_games > 0 ? round($prev_s_pct * 100, 1) : null,
            'recency_factor'     => round($recency_factor, 1),
            'season_wins'        => $s_wins,
            'season_games'       => $s_games,
            'season_pct'         => $s_games > 0 ? round($s_pct * 100, 1) : null,
            'season_factor'      => round($season_factor, 1),
            'ppg_diff'           => $s_games > 0 ? round($ppg_diff, 1) : null,
            'diff_factor'        => round($diff_factor, 1),
            'champ_bonus'        => $champ_bonus,
            'gotw_bonus'         => $gotw_bonus,
            'weather_condition'  => $w_cond,
            'weather_temp'       => $w_temp,
            'weather_factor'     => round($w_factor, 1),
            'week_label'         => $wk_label,
            'week_factor'        => $wk_factor,
            'raw_total'          => round($att_raw, 1),
            'final'              => $att_final,
        ];
    }

    // ── Handle playoff weeks (15 = Playoffs, 16 = Posse Bowl) ───────────────
    if ($week === 15 || $week === 16) {
        $po_games_raw = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM wp_playoffs WHERE year=%d AND week=%d ORDER BY id ASC",
            $year, $week
        ), ARRAY_N);
        // Cols: [0]=playoffid [1]=year [2]=week [3]=playerid [4]=score [5]=team [6]=versus [7]=overtime [8]=result

        if (empty($po_games_raw)) {
            return rest_ensure_response([
                'year' => $year, 'week' => $week,
                'seasons' => $seasons, 'weeks' => $weeks,
                'week_dates' => null, 'games' => [],
            ]);
        }

        // Seeds from stand{year}
        $seeds = [];
        if ($wpdb->get_var("SHOW TABLES LIKE 'stand{$year}'") === "stand{$year}") {
            foreach ($wpdb->get_results("SELECT * FROM stand{$year}", ARRAY_N) as $r) {
                if (!empty($r[2]) && !empty($r[4])) $seeds[$r[4]] = (int)$r[2];
            }
        }

        // Player names
        $po_pids = array_values(array_unique(array_filter(array_map(fn($r) => $r[3], $po_games_raw))));
        $po_names = [];
        if (!empty($po_pids)) {
            $ph = implode(',', array_fill(0, count($po_pids), '%s'));
            foreach ($wpdb->get_results(
                $wpdb->prepare("SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ($ph)", ...$po_pids),
                ARRAY_A
            ) as $r) {
                $po_names[$r['p_id']] = ['first' => $r['playerFirst'], 'last' => $r['playerLast']];
            }
        }

        // Pre-fetch jersey numbers + uniform/helmet info for involved teams
        $po_teams_list = array_values(array_unique(array_filter(array_map(fn($r) => $r[5], $po_games_raw))));
        $po_nums = []; $po_uni = []; $po_helm = [];
        foreach ($po_pids as $pid) { $po_nums[$pid] = (array)get_numbers_by_season($pid); }
        foreach ($po_teams_list as $ptm) {
            $po_uni[$ptm]  = get_uni_info_by_team($ptm);
            $po_helm[$ptm] = pfl_get_helmet_num($ptm, $year);
        }

        $make_po_jersey = function($pid, $team, $loc) use ($po_nums, $po_uni, $year, $theme_uri) {
            $uni_code = $po_uni[$team][$year] ?? 1;
            $nums = $po_nums[$pid] ?? [];
            $num  = empty($nums) ? 0 : (isset($nums[$year]) ? $nums[$year] : end($nums));
            $rel  = show_jersey_svg($team, $loc, $uni_code, $num);
            $disk = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/tif-child-bootstrap' . $rel;
            if (!file_exists($disk)) $rel = show_jersey_svg($team, 'H', $uni_code, $num);
            return $theme_uri . $rel;
        };

        // Group rows by game pair (alphabetically keyed)
        $pair_map = [];
        foreach ($po_games_raw as $r) {
            [$t, $v] = [$r[5], $r[6]];
            $key = strcmp($t, $v) < 0 ? "{$t}_{$v}" : "{$v}_{$t}";
            $pair_map[$key][] = $r;
        }

        $playoff_label = ($week === 16) ? 'Posse Bowl' : 'Playoffs';

        // Pre-fetch Posse Bowl MVP for this year (week 16 only)
        $pb_mvp_row = null;
        if ($week === 16) {
            $pb_mvp_row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT pid, playerFirst, playerLast, team FROM wp_awards WHERE award = 'Posse Bowl MVP' AND year = %d LIMIT 1",
                    $year
                ),
                ARRAY_A
            );
            // Ensure MVP pid has jersey info pre-fetched
            if ($pb_mvp_row && !empty($pb_mvp_row['pid']) && !isset($po_nums[$pb_mvp_row['pid']])) {
                $po_nums[$pb_mvp_row['pid']] = (array) get_numbers_by_season($pb_mvp_row['pid']);
            }
        }

        $po_built_games = [];

        foreach ($pair_map as $key => $rows) {
            [$t1, $t2] = explode('_', $key);
            $s1 = $seeds[$t1] ?? 99;
            $s2 = $seeds[$t2] ?? 99;
            $home_team = ($s1 <= $s2) ? $t1 : $t2;
            $away_team = ($s1 <= $s2) ? $t2 : $t1;
            $home_seed = ($s1 <= $s2) ? $s1 : $s2;
            $away_seed = ($s1 <= $s2) ? $s2 : $s1;

            $home_reg = $away_reg = $home_ot = $away_ot = [];
            foreach ($rows as $r) {
                $is_ot = (int)$r[7] === 1;
                if ($r[5] === $home_team) {
                    if ($is_ot) $home_ot[] = $r; else $home_reg[] = $r;
                } else {
                    if ($is_ot) $away_ot[] = $r; else $away_reg[] = $r;
                }
            }

            $home_score = (float)array_sum(array_map(fn($r) => $r[4], $home_reg));
            $away_score = (float)array_sum(array_map(fn($r) => $r[4], $away_reg));
            $is_overtime = !empty($home_ot) || !empty($away_ot);
            if ($is_overtime) {
                $ot_winner = null;
                foreach ($rows as $r) { if ((int)$r[8] === 1) { $ot_winner = $r[5]; break; } }
                if ($ot_winner === $home_team) {
                    $home_score += 1.0;
                } elseif ($ot_winner === $away_team) {
                    $away_score += 1.0;
                }
            }
            $winner = ($home_score >= $away_score) ? $home_team : $away_team;

            // wp_playoffs cols after ALTER: [9]=pass_yds [10]=pass_td [11]=pass_int
            //   [12]=rush_yds [13]=rush_td [14]=rec_yds [15]=rec_td
            //   [16]=xpm [17]=xpa [18]=fgm [19]=fga [20]=twopt [21]=nflteam
            $build_po_side = function($side_rows, $team, $loc) use ($po_names, $make_po_jersey) {
                $starters = [];
                foreach ($side_rows as $r) {
                    $pid = $r[3];
                    if (empty($pid) || $pid === 'None') continue;
                    $pos   = strtoupper(substr($pid, -2));
                    $names = $po_names[$pid] ?? ['first' => '?', 'last' => $pid];
                    $has_ls = isset($r[9]) && ($r[9] !== null || $r[12] !== null || $r[14] !== null);
                    $linescore = $has_ls ? [
                        'pass_yds' => (int)($r[9]  ?? 0),
                        'pass_td'  => (int)($r[10] ?? 0),
                        'pass_int' => (int)($r[11] ?? 0),
                        'rush_yds' => (int)($r[12] ?? 0),
                        'rush_td'  => (int)($r[13] ?? 0),
                        'rec_yds'  => (int)($r[14] ?? 0),
                        'rec_td'   => (int)($r[15] ?? 0),
                        'xpm'      => (int)($r[16] ?? 0),
                        'xpa'      => (int)($r[17] ?? 0),
                        'fgm'      => (int)($r[18] ?? 0),
                        'fga'      => (int)($r[19] ?? 0),
                        'twopt'    => (int)($r[20] ?? 0),
                    ] : null;
                    $starters[$pos] = [
                        'pid'          => $pid,
                        'first'        => $names['first'],
                        'last'         => $names['last'],
                        'points'       => (float)$r[4],
                        'linescore'    => $linescore,
                        'jersey_url'   => $make_po_jersey($pid, $team, $loc),
                        'headshot_url' => get_attachment_url_by_slug($pid),
                        'nfl_team'     => $r[21] ?? null,
                    ];
                }
                return $starters;
            };

            $home_helm_code = $po_helm[$home_team] ?? 1;
            $away_helm_code = $po_helm[$away_team] ?? 1;

            $auto_notes = [$playoff_label . ' · #' . $home_seed . ' seed vs #' . $away_seed . ' seed'];

            // Look up the actual stadium for that team in that season (handles
            // teams that changed venues). Fall back to current stadium if not found.
            $stadium_for_year = $wpdb->get_var($wpdb->prepare(
                "SELECT stadium FROM wp_team_{$home_team} WHERE season = %d AND home_away = 'H' LIMIT 1",
                $year
            ));
            $po_stadium = $stadium_for_year ?: (get_stadium_by_team($home_team) ?: '');

            $po_built_games[] = [
                'home'             => $home_team,
                'home_name'        => $team_names[$home_team] ?? $home_team,
                'away'             => $away_team,
                'away_name'        => $team_names[$away_team] ?? $away_team,
                'home_score'       => $home_score,
                'away_score'       => $away_score,
                'winner'           => $winner,
                'stadium'              => $po_stadium,
                'stadium_capacity'    => $stadium_capacity_by_venue[pfl_canonical_stadium_name($po_stadium ?? '')] ?? $stadium_capacity_map[$home_team]  ?? null,
                'stadium_roof_type'   => $stadium_roof_by_venue[pfl_canonical_stadium_name($po_stadium ?? '')]     ?? $stadium_roof_type_map[$home_team] ?? 'Open',
                'stadium_region'      => $stadium_region_by_venue[pfl_canonical_stadium_name($po_stadium ?? '')]   ?? $stadium_region_map[$home_team]    ?? null,
                'stadium_image_urls'  => $get_stadium_images($po_stadium, $home_team),
                'weather_temp'        => null,
                'weather_wind_mph'    => null,
                'weather_condition'   => null,
                'weather_roof_closed' => false,
                'attendance_pct'      => $attendance_map[$home_team] ?? null,
                'attendance_factors'  => $attendance_factors_map[$home_team] ?? null,
                'is_overtime'         => $is_overtime,
                'is_extra_ot'      => false,
                'home_helmet_url'  => $theme_uri . '/img/helmets/weekly/' . $home_team . '-helm-right-' . $home_helm_code . '.png',
                'away_helmet_url'  => $theme_uri . '/img/helmets/weekly/' . $away_team . '-helm-right-' . $away_helm_code . '.png',
                'home_starters'    => $build_po_side($home_reg, $home_team, 'H'),
                'away_starters'    => $build_po_side($away_reg, $away_team, 'R'),
                'home_ot'          => !empty($home_ot) ? $build_po_side($home_ot, $home_team, 'H') : null,
                'away_ot'          => !empty($away_ot) ? $build_po_side($away_ot, $away_team, 'R') : null,
                'bench'            => ['home' => ['roster' => [], 'injured' => []], 'away' => ['roster' => [], 'injured' => []]],
                'notes'            => [],
                'auto_notes'       => $auto_notes,
                'player_notes'     => [],
                'mvp'              => (function() use ($pb_mvp_row, $home_reg, $away_reg, $make_po_jersey, $home_team, $away_team) {
                    if (!$pb_mvp_row || empty($pb_mvp_row['pid'])) return null;
                    $mpid  = $pb_mvp_row['pid'];
                    $mteam = $pb_mvp_row['team'];
                    $mloc  = ($mteam === $home_team) ? 'H' : 'R';
                    $mpts  = 0.0;
                    foreach (array_merge($home_reg, $away_reg) as $r) {
                        if ($r[3] === $mpid) { $mpts = (float) $r[4]; break; }
                    }
                    return [
                        'pid'        => $mpid,
                        'first'      => $pb_mvp_row['playerFirst'],
                        'last'       => $pb_mvp_row['playerLast'],
                        'team'       => $mteam,
                        'points'     => $mpts,
                        'jersey_url' => $make_po_jersey($mpid, $mteam, $mloc),
                    ];
                })(),
                'is_playoff'       => true,
                'playoff_label'    => $playoff_label,
                'home_seed'        => $home_seed,
                'away_seed'        => $away_seed,
            ];
        }

        // Sort: lower home seed first (game 1: seed 1 vs seed 4)
        usort($po_built_games, fn($a, $b) => $a['home_seed'] <=> $b['home_seed']);

        $po_result = [
            'year'       => $year,
            'week'       => $week,
            'seasons'    => $seasons,
            'weeks'      => $weeks,
            'week_dates' => null,
            'games'      => $po_built_games,
        ];

        if (!empty($po_built_games)) {
            pfl_write_attendance_rows($year, $week, $po_built_games);
            set_transient($cache_key, $po_result, HOUR_IN_SECONDS);
        }
        return rest_ensure_response($po_result);
    }

    // ── Fetch each team's row for this weekid ───────────────────────────────
    $team_rows = [];
    foreach ($all_teams as $t) {
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM wp_team_{$t} WHERE id = %s", $weekid),
            ARRAY_N
        );
        if ($row) {
            $team_rows[$t] = $row;
        }
    }

    if (empty($team_rows)) {
        // Nothing for this week — return metadata only, don't cache
        return rest_ensure_response([
            'year'    => $year,
            'week'    => $week,
            'seasons' => $seasons,
            'weeks'   => $weeks,
            'games'   => [],
        ]);
    }

    // ── Find matchups (home teams drive the matchup) ─────────────────────────
    // home_away column = index 7: 'H' or 'R'
    $home_teams = [];
    foreach ($team_rows as $t => $row) {
        if ($row[7] === 'H') {
            $home_teams[$t] = $row;
        }
    }

    // ── Collect all PIDs used as starters ───────────────────────────────────
    $all_pids = [];
    foreach ($team_rows as $t => $row) {
        for ($i = 10; $i <= 18; $i++) {
            if (!empty($row[$i])) $all_pids[] = $row[$i];
        }
    }
    $all_pids = array_unique(array_filter($all_pids));

    // ── Fetch player names ───────────────────────────────────────────────────
    $player_names = [];
    if (!empty($all_pids)) {
        $placeholders = implode(',', array_fill(0, count($all_pids), '%s'));
        $player_rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ($placeholders)",
                ...$all_pids
            ),
            ARRAY_A
        );
        foreach ($player_rows as $pr) {
            $player_names[$pr['p_id']] = ['first' => $pr['playerFirst'], 'last' => $pr['playerLast']];
        }
    }

    // ── Fetch player stats — one query per player using numeric indices ────────
    // Matches get_player_data() column layout:
    //   [3]=points [14]=pass_yds [15]=pass_td [16]=pass_int
    //   [17]=rush_yds [18]=rush_td [19]=rec_yds [20]=rec_td
    //   [21]=xpm [22]=xpa [23]=fgm [24]=fga [27]=twopt
    $player_stats = [];
    foreach ($all_pids as $pid) {
        $stat_row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `{$pid}` WHERE week_id = %s LIMIT 1", $weekid),
            ARRAY_N
        );
        if ($stat_row) {
            $player_stats[$pid] = [
                'points'   => $stat_row[3],
                'pass_yds' => $stat_row[14],
                'pass_td'  => $stat_row[15],
                'pass_int' => $stat_row[16],
                'rush_yds' => $stat_row[17],
                'rush_td'  => $stat_row[18],
                'rec_yds'  => $stat_row[19],
                'rec_td'   => $stat_row[20],
                'xpm'      => $stat_row[21],
                'xpa'      => $stat_row[22],
                'fgm'      => $stat_row[23],
                'fga'      => $stat_row[24],
                'twopt'    => $stat_row[27] ?? 0,
                'game_date' => $stat_row[10] ?? null,
                'nflteam'   => $stat_row[11] ?? null,
            ];
        }
    }

    // ── Batch gametime lookup from wp_nfl_game_times (1999+) ─────────────────
    // Query by season+week (not game_date) so off-by-one dates in player tables
    // (e.g. MNF game entered as Tuesday instead of Monday) still match correctly.
    $gametime_map = [];
    if ($year >= 1999) {
        $gt_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT game_date, home_team, away_team, gametime, temp, wind, roof, surface FROM wp_nfl_game_times WHERE season = %d AND week = %d",
                $year, $week
            ),
            ARRAY_A
        );
        foreach ($gt_rows as $gt) {
            $info = [
                'game_date' => $gt['game_date'],
                'gametime'  => $gt['gametime'],
                'temp'      => $gt['temp'],
                'wind'      => $gt['wind'],
                'roof'      => $gt['roof'],
                'surface'   => $gt['surface'],
            ];
            $gametime_map[strtoupper($gt['home_team'])] = $info;
            $gametime_map[strtoupper($gt['away_team'])] = $info;
        }
        foreach ($player_stats as $pid => &$ps) {
            $nt   = strtoupper(trim($ps['nflteam'] ?? ''));
            $info = ($nt && isset($gametime_map[$nt])) ? $gametime_map[$nt] : [];
            $ps['game_date'] = $info['game_date'] ?? $ps['game_date'];
            $ps['gametime']  = $info['gametime']  ?? null;
            $ps['temp']      = isset($info['temp'])  ? (int) $info['temp']  : null;
            $ps['wind']      = isset($info['wind'])  ? (int) $info['wind']  : null;
            $ps['roof']      = $info['roof']    ?? null;
            $ps['surface']   = $info['surface'] ?? null;
        }
        unset($ps);
    }

    // ── Fetch jersey numbers for all players in one query ────────────────────
    $player_numbers = [];
    if (!empty($all_pids)) {
        $pids_in = "'" . implode("','", array_map('esc_sql', $all_pids)) . "'";
        $num_rows = $wpdb->get_results(
            "SELECT p_id, numberarray FROM wp_players WHERE p_id IN ({$pids_in})",
            ARRAY_A
        );
        foreach ($num_rows as $nr) {
            $arr = json_decode($nr['numberarray'], true);
            $player_numbers[$nr['p_id']] = is_array($arr) ? $arr : [];
        }
    }

    // ── Game notes from wp_game_notes ────────────────────────────────────────
    // note_type='note' → manual player notes; note_type='ai_player_notes' → JSON array of AI notes
    $notes_db        = [];
    $ai_notes_db     = []; // keyed by home team id → array of {pid, note}
    $all_notes_rows = $wpdb->get_results(
        $wpdb->prepare("SELECT hometeam, note, note_type FROM wp_game_notes WHERE weekid = %d", $weekid),
        ARRAY_A
    );
    foreach ($all_notes_rows as $nr) {
        $n_team = $nr['hometeam'] ?? '';
        $n_text = $nr['note'] ?? '';
        $n_type = $nr['note_type'] ?? 'note';
        if (!$n_text) continue;
        if ($n_type === 'ai_player_notes') {
            $decoded = json_decode($n_text, true);
            if (is_array($decoded)) $ai_notes_db[$n_team] = $decoded;
        } else {
            $notes_db[$n_team][] = $n_text;
        }
    }

    // ── ESPN player stats for bench score calculation (fetched once, cached) ───
    // Bench roster data was not recorded before 2011, so skip ESPN fetch for earlier years
    $espn_week_stats = ($year >= 2011) ? pfl_get_week_espn_player_stats($year, $week) : [];

    // ── ACF notes ────────────────────────────────────────────────────────────
    $acf_notes_raw = [];
    if (function_exists('get_field')) {
        $acf_notes_raw = get_field('week_notes', 'options') ?: [];
    }

    // ── PVQ multipliers ──────────────────────────────────────────────────────
    $pvq_mults = getpvqmultipliers($year) ?: [];

    // ── Helmet codes — use the existing pfl_get_helmet_num() which queries
    // wp_helmet_history with the correct column name 'yearstart' (not year_start)
    // Pre-build a map for all 15 teams so we only query once per team
    $helmet_code_map = [];
    foreach ($all_teams as $tm) {
        $helmet_code_map[$tm] = pfl_get_helmet_num($tm, $year);
    }

    // ── Pre-fetch uniform era info for all teams ─────────────────────────────
    $uni_info_map = [];
    foreach ($all_teams as $tm) {
        $uni_info_map[$tm] = get_uni_info_by_team($tm);
    }

    // ── Jersey URL builder closure (H and R only) ─────────────────────────────
    $make_jersey_url = function($pid, $team, $location) use ($player_numbers, $uni_info_map, $year, $theme_uri) {
        $uni_info = $uni_info_map[$team] ?? [];
        $uni_code = $uni_info[$year] ?? 1;
        $nums = $player_numbers[$pid] ?? [];
        $num  = 0;
        if (!empty($nums)) {
            $num = isset($nums[$year]) ? $nums[$year] : end($nums);
        }
        $jersey_rel  = show_jersey_svg($team, $location, $uni_code, $num);
        $jersey_disk = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/tif-child-bootstrap' . $jersey_rel;
        if (!file_exists($jersey_disk)) {
            $jersey_rel = show_jersey_svg($team, 'H', $uni_code, $num);
        }
        return $theme_uri . $jersey_rel;
    };

    // ── Build each game ──────────────────────────────────────────────────────
    $games = [];

    foreach ($home_teams as $home_team => $home_row) {
        $away_team = $home_row[5]; // vs column
        if (!isset($team_rows[$away_team])) continue;
        $away_row = $team_rows[$away_team];

        $home_score  = (float) $home_row[4];
        $away_score  = (float) $away_row[4];
        $is_overtime = (bool) $home_row[14];
        $is_extra_ot = (bool) $home_row[19];
        $stadium     = $home_row[8];
        $uniform_col = $home_row[20] ?? '';
        $away_uniform_col = $away_row[20] ?? '';

        // Stadium name fix
        if ($stadium === 'Spankoni Center' && $year <= 2004) {
            $stadium = 'The Gonad Bowl';
        }

        $winner = ($home_score >= $away_score) ? $home_team : $away_team;

        // ── Helmet URLs ───────────────────────────────────────────────────────
        $home_helm_code = $helmet_code_map[$home_team] ?? 1;
        $away_helm_code = $helmet_code_map[$away_team] ?? 1;
        $home_helmet_url = $theme_uri . '/img/helmets/weekly/' . $home_team . '-helm-right-' . $home_helm_code . '.png';
        $away_helmet_url = $theme_uri . '/img/helmets/weekly/' . $away_team . '-helm-right-' . $away_helm_code . '.png';

        // ── Starters builder ──────────────────────────────────────────────────
        $build_starters = function($row, $start_col) use ($player_names, $player_stats) {
            $positions = ['QB', 'RB', 'WR', 'PK'];
            $result = [];
            for ($i = 0; $i < 4; $i++) {
                $pid = $row[$start_col + $i] ?? '';
                if (empty($pid) || strtolower(trim($pid)) === 'none') {
                    $result[$positions[$i]] = [
                        'pid'       => null,
                        'first'     => '?',
                        'last'      => 'None',
                        'points'    => 0,
                        'linescore' => null,
                    ];
                    continue;
                }
                $names = $player_names[$pid] ?? ['first' => '?', 'last' => $pid];
                $stats = $player_stats[$pid] ?? null;
                $linescore = null;
                if ($stats) {
                    $linescore = [
                        'pass_yds' => (int)   $stats['pass_yds'],
                        'pass_td'  => (int)   $stats['pass_td'],
                        'pass_int' => (int)   $stats['pass_int'],
                        'rush_yds' => (int)   $stats['rush_yds'],
                        'rush_td'  => (int)   $stats['rush_td'],
                        'rec_yds'  => (int)   $stats['rec_yds'],
                        'rec_td'   => (int)   $stats['rec_td'],
                        'xpm'      => (int)   $stats['xpm'],
                        'xpa'      => (int)   $stats['xpa'],
                        'fgm'      => (int)   $stats['fgm'],
                        'fga'      => (int)   $stats['fga'],
                        'twopt'    => (int)   $stats['twopt'],
                    ];
                }
                $result[$positions[$i]] = [
                    'pid'       => $pid,
                    'first'     => $names['first'],
                    'last'      => $names['last'],
                    'points'    => $stats ? (float) $stats['points'] : null,
                    'linescore' => $linescore,
                    'game_date' => $stats ? ($stats['game_date'] ?? null) : null,
                    'gametime'  => $stats ? ($stats['gametime']  ?? null) : null,
                    'temp'      => $stats ? ($stats['temp']      ?? null) : null,
                    'wind'      => $stats ? ($stats['wind']      ?? null) : null,
                    'roof'      => $stats ? ($stats['roof']      ?? null) : null,
                    'surface'   => $stats ? ($stats['surface']   ?? null) : null,
                ];
            }
            return $result;
        };

        $home_starters = $build_starters($home_row, 10);
        $away_starters = $build_starters($away_row, 10);

        // Attach jersey, headshot, and NFL team to each starter
        $home_location = 'H';
        $away_location = 'R';
        foreach ($home_starters as &$s) {
            $s['jersey_url']   = $s['pid'] ? $make_jersey_url($s['pid'], $home_team, $home_location) : null;
            $s['headshot_url'] = $s['pid'] ? get_attachment_url_by_slug($s['pid']) : null;
            $s['nfl_team']     = null;
            if ($s['pid']) {
                $pdata = get_player_data($s['pid']);
                $s['nfl_team'] = $pdata[$weekid]['nflteam'] ?? null;
            }
        } unset($s);
        foreach ($away_starters as &$s) {
            $s['jersey_url']   = $s['pid'] ? $make_jersey_url($s['pid'], $away_team, $away_location) : null;
            $s['headshot_url'] = $s['pid'] ? get_attachment_url_by_slug($s['pid']) : null;
            $s['nfl_team']     = null;
            if ($s['pid']) {
                $pdata = get_player_data($s['pid']);
                $s['nfl_team'] = $pdata[$weekid]['nflteam'] ?? null;
            }
        } unset($s);

        // OT starters (cols 15-18)
        $home_ot = null;
        $away_ot = null;
        if ($is_overtime) {
            $home_ot = $build_starters($home_row, 15);
            $away_ot = $build_starters($away_row, 15);
            if (empty($home_ot)) $home_ot = null;
            if (empty($away_ot)) $away_ot = null;

            // Attach jersey, headshot, and NFL team — same as regulation starters above
            if ($home_ot) {
                foreach ($home_ot as &$s) {
                    $s['jersey_url']   = $s['pid'] ? $make_jersey_url($s['pid'], $home_team, $home_location) : null;
                    $s['headshot_url'] = $s['pid'] ? get_attachment_url_by_slug($s['pid']) : null;
                    $s['nfl_team']     = null;
                    if ($s['pid']) {
                        $pdata = get_player_data($s['pid']);
                        $s['nfl_team'] = $pdata[$weekid]['nflteam'] ?? null;
                    }
                } unset($s);
            }
            if ($away_ot) {
                foreach ($away_ot as &$s) {
                    $s['jersey_url']   = $s['pid'] ? $make_jersey_url($s['pid'], $away_team, $away_location) : null;
                    $s['headshot_url'] = $s['pid'] ? get_attachment_url_by_slug($s['pid']) : null;
                    $s['nfl_team']     = null;
                    if ($s['pid']) {
                        $pdata = get_player_data($s['pid']);
                        $s['nfl_team'] = $pdata[$weekid]['nflteam'] ?? null;
                    }
                } unset($s);
            }
        }

        // ── Bench ─────────────────────────────────────────────────────────────
        $home_starter_pids = array_column($home_starters, 'pid');
        $away_starter_pids = array_column($away_starters, 'pid');
        if ($home_ot) $home_starter_pids = array_merge($home_starter_pids, array_column($home_ot, 'pid'));
        if ($away_ot) $away_starter_pids = array_merge($away_starter_pids, array_column($away_ot, 'pid'));

        $home_bench_raw = @get_the_bench($year, $week, $home_team);
        $away_bench_raw = @get_the_bench($year, $week, $away_team);

        // ── Bye week teams ────────────────────────────────────────────────────
        $bye_teams = [];
        $bye_json_file = get_stylesheet_directory() . '/nfl-bye-weeks/bye_weeks_' . $year . '.json';
        if (file_exists($bye_json_file)) {
            $bye_json = json_decode(file_get_contents($bye_json_file), true);
            foreach (($bye_json['bye_weeks'] ?? []) as $bw) {
                if ((int)$bw['week'] === $week) {
                    $bye_teams = $bw['teams'];
                    break;
                }
            }
        }

        $format_bench = function($bench_raw, $starter_pids) use ($wpdb, $year, $week, $weekid, $espn_week_stats, $bye_teams) {
            $roster  = [];
            $injured = [];
            if (!$bench_raw) return ['roster' => $roster, 'injured' => $injured];
            $injured_statuses = ['IR', 'INJURED_RESERVE', 'INJURED RESERVE'];
            foreach ($bench_raw as $status => $players) {
                $status_up = strtoupper($status);
                foreach ($players as $pid => $info) {
                    if (!$pid) continue; // skip players whose MFL ID couldn't be mapped
                    if (in_array($pid, $starter_pids)) continue;
                    $name = $info['name'] ?? $pid;
                    $pos  = $info['position'] ?? '';
                    // Look up full name from wp_players for accurate ESPN matching
                    $full_name = $wpdb->get_var($wpdb->prepare(
                        "SELECT CONCAT(playerFirst, ' ', playerLast) FROM wp_players WHERE p_id = %s LIMIT 1",
                        $pid
                    ));
                    $pts = pfl_calc_bench_score($full_name ?: $name, $pos, $year, $espn_week_stats);

                    // Determine if player's NFL team is on bye this week
                    $on_bye = false;
                    if (!empty($bye_teams)) {
                        $nfl_team = $wpdb->get_var($wpdb->prepare(
                            "SELECT nflteam FROM `{$pid}` WHERE week_id = %s LIMIT 1", $weekid
                        ));
                        if (empty($nfl_team)) {
                            for ($i = 1; $i <= 3 && empty($nfl_team); $i++) {
                                foreach ([sprintf('%04d%02d', $year, $week - $i), sprintf('%04d%02d', $year, $week + $i)] as $try_wk) {
                                    $nfl_team = $wpdb->get_var($wpdb->prepare(
                                        "SELECT nflteam FROM `{$pid}` WHERE week_id = %s AND nflteam IS NOT NULL AND nflteam != '' LIMIT 1",
                                        $try_wk
                                    ));
                                    if ($nfl_team) break;
                                }
                            }
                        }
                        $on_bye = !empty($nfl_team) && in_array($nfl_team, $bye_teams);
                    }

                    $entry = ['pid' => $pid, 'name' => $name, 'pos' => $pos, 'points' => $pts, 'on_bye' => $on_bye];
                    if (in_array($status_up, $injured_statuses)) {
                        $injured[] = $entry;
                    } else {
                        $roster[] = $entry;
                    }
                }
            }
            return ['roster' => $roster, 'injured' => $injured];
        };

        $home_bench = $format_bench($home_bench_raw, $home_starter_pids);
        $away_bench = $format_bench($away_bench_raw, $away_starter_pids);

        // ── Notes ─────────────────────────────────────────────────────────────
        $notes = [];
        // DB notes for either team
        foreach ([$home_team, $away_team] as $nt) {
            if (!empty($notes_db[$nt])) {
                foreach ($notes_db[$nt] as $note) $notes[] = $note;
            }
        }
        // ACF notes
        foreach ($acf_notes_raw as $acf_note) {
            $acf_weekid = $acf_note['week_id'] ?? '';
            $acf_team   = $acf_note['team_reference'] ?? '';
            $acf_text   = $acf_note['weekly_note'] ?? '';
            if ($acf_weekid == $weekid && in_array($acf_team, [$home_team, $away_team]) && $acf_text) {
                $notes[] = $acf_text;
            }
        }

        // ── Auto notes ────────────────────────────────────────────────────────
        $home_name = $team_names[$home_team] ?? $home_team;
        $away_name = $team_names[$away_team] ?? $away_team;
        $winner_name = ($winner === $home_team) ? $home_name : $away_name;
        $diff  = abs($home_score - $away_score);
        $total = $home_score + $away_score;

        $auto_notes = [];
        if ($diff > 20) {
            $auto_notes[] = "{$winner_name} wins in a Blowout";
        }
        if ($total > 99) {
            $auto_notes[] = "Barnburner!";
        }
        if ($total < 40 && $year > 1991) {
            $auto_notes[] = "BS Win";
        }
        if ($home_score >= 70) {
            $auto_notes[] = "{$home_name} with 70+ points!";
        }
        if ($away_score >= 70) {
            $auto_notes[] = "{$away_name} with 70+ points!";
        }
        // Grand slam: all 4 starters scored 10+
        foreach ([
            [$home_starters, $home_name],
            [$away_starters, $away_name],
        ] as [$starters, $team_name]) {
            if (count($starters) === 4) {
                $grand_slam = true;
                foreach ($starters as $s) {
                    if (($s['points'] ?? 0) < 10) { $grand_slam = false; break; }
                }
                if ($grand_slam) $auto_notes[] = "GRANDSLAM for the {$team_name}!";
            }
        }

        // ── Week 14 Tradition: PEP vs WRZ ────────────────────────────────────
        $is_pep_wrz = ($week === 14) &&
            (($home_team === 'PEP' && $away_team === 'WRZ') ||
             ($home_team === 'WRZ' && $away_team === 'PEP'));
        if ($is_pep_wrz) {
            $pep_w = 0;
            $wrz_w = 0;
            $all_seasons = $wpdb->get_col(
                "SELECT DISTINCT season FROM wp_team_PEP WHERE season <= {$year} ORDER BY season ASC"
            );
            foreach ($all_seasons as $s) {
                $wid = $s . '14';
                $tr = $wpdb->get_row(
                    $wpdb->prepare("SELECT * FROM wp_team_PEP WHERE id = %s", $wid),
                    ARRAY_N
                );
                if (!$tr || $tr[5] !== 'WRZ') continue;
                if ((float)$tr[4] > (float)$tr[6]) $pep_w++; else $wrz_w++;
            }
            if ($pep_w > 0 || $wrz_w > 0) {
                if ($pep_w > $wrz_w) {
                    $auto_notes[] = "Week 14 Tradition · Peppers lead series {$pep_w}–{$wrz_w}";
                } elseif ($wrz_w > $pep_w) {
                    $auto_notes[] = "Week 14 Tradition · Warriorz lead series {$wrz_w}–{$pep_w}";
                } else {
                    $auto_notes[] = "Week 14 Tradition · Series tied {$pep_w}–{$wrz_w}";
                }
            }
        }

        // ── Thanksgiving Tradition: SNR ───────────────────────────────────────
        $is_snr_game = ($home_team === 'SNR' || $away_team === 'SNR');
        if ($is_snr_game) {
            $nov1_ts    = mktime(0, 0, 0, 11, 1, $year);
            $nov1_dow   = (int) date('w', $nov1_ts);
            $tgiving_ts = $nov1_ts + ((4 - $nov1_dow + 7) % 7) * 86400 + 21 * 86400;

            $snr_starters = ($home_team === 'SNR') ? $home_starters : $away_starters;
            $is_tgiving_week = false;
            foreach ($snr_starters as $s) {
                $spid = $s['pid'] ?? '';
                if (!$spid || empty($player_stats[$spid]['game_date'])) continue;
                if (abs(strtotime($player_stats[$spid]['game_date']) - $tgiving_ts) <= 8 * 86400) {
                    $is_tgiving_week = true;
                    break;
                }
            }

            if ($is_tgiving_week) {
                $tgiving_rows = pfl_get_snr_thanksgiving_data();
                $snr_w = 0; $snr_l = 0;
                foreach ($tgiving_rows as $tr) {
                    if ($tr['year'] > $year) continue;
                    if ($tr['winner'] === 'SNR') $snr_w++; else $snr_l++;
                }
                if ($snr_w > 0 || $snr_l > 0) {
                    if ($snr_w > $snr_l) {
                        $auto_notes[] = "Thanksgiving Tradition · Sixty Niners lead {$snr_w}–{$snr_l}";
                    } elseif ($snr_l > $snr_w) {
                        $auto_notes[] = "Thanksgiving Tradition · Sixty Niners trail {$snr_w}–{$snr_l}";
                    } else {
                        $auto_notes[] = "Thanksgiving Tradition · Sixty Niners {$snr_w}–{$snr_l} all-time";
                    }
                }
            }
        }

        // ── MVP ───────────────────────────────────────────────────────────────
        $winning_starters = ($winner === $home_team) ? $home_starters : $away_starters;
        $winning_location = ($winner === $home_team) ? 'H' : 'R';
        $winning_uniform  = ($winner === $home_team) ? $uniform_col : $away_uniform_col;

        $best_pid   = null;
        $best_pvq   = -1;
        $best_pos   = null;
        foreach ($winning_starters as $pos => $starter) {
            if (empty($starter['pid'])) continue;
            $mult_key = $pos . '_Mult';
            $mult     = isset($pvq_mults[$mult_key]) ? (float) $pvq_mults[$mult_key] : 1.0;
            $pts      = (float) ($starter['points'] ?? 0);
            $pvq      = $mult * $pts;
            if ($pvq > $best_pvq) {
                $best_pvq = $pvq;
                $best_pid = $starter['pid'];
                $best_pos = $pos;
            }
        }

        // ── Date-based weather (use actual game_date for accuracy) ───────────
        // Find the game_date from the first home starter that has one.
        $game_date_str = null;
        foreach ($home_starters as $s) {
            if (!empty($s['game_date'])) { $game_date_str = $s['game_date']; break; }
        }
        $game_weather = null;
        if ($game_date_str) {
            $gw_season_week = pfl_game_date_to_season_week($game_date_str);
            if ($gw_season_week) {
                // Prefer venue-based attrs so historical stadiums (e.g. the
                // domed A.P. Compfortdome 1994-2011) get the correct roof.
                $gw_venue  = pfl_canonical_stadium_name($stadium ?? '');
                $gw_region = $stadium_region_by_venue[$gw_venue] ?? $stadium_region_map[$home_team] ?? null;
                $gw_roof   = $stadium_roof_by_venue[$gw_venue]   ?? $stadium_roof_type_map[$home_team] ?? 'Open';
                if ($gw_roof === 'Dome') {
                    $game_weather = ['temp' => 70, 'wind_mph' => 0, 'condition' => 'dome', 'roof_closed' => false];
                } else {
                    $gw_data     = ($gw_region && isset($weather_table[$gw_region][$gw_season_week])) ? $weather_table[$gw_region][$gw_season_week] : null;
                    $gw_temp     = $gw_data ? $gw_data['t'] : 72;
                    $gw_wind     = $gw_data ? $gw_data['w'] : 8;
                    $gw_cond     = $gw_data ? $gw_data['c'] : 'sunny';
                    if ($gw_roof === 'Retractable' && ($gw_temp < 55 || $gw_temp > 85 || in_array($gw_cond, ['rain','snow']) || $gw_wind >= 18)) {
                        $game_weather = ['temp' => 70, 'wind_mph' => 0, 'condition' => 'dome', 'roof_closed' => true];
                    } else {
                        $game_weather = ['temp' => $gw_temp, 'wind_mph' => $gw_wind, 'condition' => $gw_cond, 'roof_closed' => false];
                    }
                }
            }
        }

        $mvp = null;
        if ($best_pid) {
            $mvp_names = $player_names[$best_pid] ?? ['first' => '?', 'last' => $best_pid];
            $mvp_pts   = (float) ($winning_starters[$best_pos]['points'] ?? 0);

            // Jersey URL (H/R only, fallback to H if R missing)
            $uni_info   = $uni_info_map[$winner] ?? [];
            $uni_code   = $uni_info[$year] ?? 1;
            $jersey_loc = $winning_location;

            $player_nums = get_numbers_by_season($best_pid);
            $num = 0;
            if ($player_nums) {
                $nums_arr = (array) $player_nums;
                if (isset($nums_arr[$year])) {
                    $num = $nums_arr[$year];
                } elseif (!empty($nums_arr)) {
                    $num = end($nums_arr);
                }
            }

            $jersey_rel  = show_jersey_svg($winner, $jersey_loc, $uni_code, $num);
            $jersey_disk = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/tif-child-bootstrap' . $jersey_rel;
            if (!file_exists($jersey_disk)) {
                $jersey_rel = show_jersey_svg($winner, 'H', $uni_code, $num);
            }
            $jersey_url = $theme_uri . $jersey_rel;

            $mvp = [
                'pid'        => $best_pid,
                'first'      => $mvp_names['first'],
                'last'       => $mvp_names['last'],
                'team'       => $winner,
                'points'     => $mvp_pts,
                'jersey_url' => $jersey_url,
            ];
        }

        $games[] = [
            'home'            => $home_team,
            'home_name'       => $home_name,
            'away'            => $away_team,
            'away_name'       => $away_name,
            'home_score'      => $home_score,
            'away_score'      => $away_score,
            'winner'          => $winner,
            'stadium'             => $stadium,
            'stadium_capacity'    => $stadium_capacity_by_venue[pfl_canonical_stadium_name($stadium ?? '')] ?? $stadium_capacity_map[$home_team]  ?? null,
            'stadium_roof_type'   => $stadium_roof_by_venue[pfl_canonical_stadium_name($stadium ?? '')]     ?? $stadium_roof_type_map[$home_team] ?? 'Open',
            'stadium_region'      => $stadium_region_by_venue[pfl_canonical_stadium_name($stadium ?? '')]   ?? $stadium_region_map[$home_team]    ?? null,
            'stadium_image_urls'  => $get_stadium_images($stadium, $home_team),
            'weather_temp'        => $game_weather['temp']        ?? null,
            'weather_wind_mph'    => $game_weather['wind_mph']    ?? null,
            'weather_condition'   => $game_weather['condition']   ?? null,
            'weather_roof_closed' => $game_weather['roof_closed'] ?? false,
            'attendance_pct'      => $attendance_map[$home_team] ?? null,
            'attendance_factors'  => $attendance_factors_map[$home_team] ?? null,
            'is_overtime'         => $is_overtime,
            'is_extra_ot'     => $is_extra_ot,
            'home_helmet_url' => $home_helmet_url,
            'away_helmet_url' => $away_helmet_url,
            'home_starters'   => $home_starters,
            'away_starters'   => $away_starters,
            'home_ot'         => $home_ot,
            'away_ot'         => $away_ot,
            'bench'           => [
                'home' => $home_bench,
                'away' => $away_bench,
            ],
            'notes'           => $notes,
            'auto_notes'      => $auto_notes,
            'player_notes'    => $ai_notes_db[$home_team] ?? [],
            'mvp'             => $mvp,
        ];
    }

    // ── Week date range from game_date in player tables ──────────────────────
    $all_dates = array_filter(array_column($player_stats, 'game_date'));
    $week_dates = null;
    if (!empty($all_dates)) {
        sort($all_dates);
        $week_dates = ['min' => reset($all_dates), 'max' => end($all_dates)];
    }

    $result = [
        'year'       => $year,
        'week'       => $week,
        'seasons'    => $seasons,
        'weeks'      => $weeks,
        'week_dates' => $week_dates,
        'games'      => $games,
    ];

    // Only cache if we have actual games
    if (!empty($games)) {
        pfl_write_attendance_rows($year, $week, $games);
        set_transient($cache_key, $result, HOUR_IN_SECONDS);
    }

    return rest_ensure_response($result);
}

// ─────────────────────────────────────────────────────────────────────────────
// SCORIGAMI ENDPOINT
// ─────────────────────────────────────────────────────────────────────────────

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/scorigami', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_scorigami',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_scorigami() {
    global $wpdb;

    $cache_key = 'pfl_scorigami_v1';
    $cached = get_transient($cache_key);
    if ($cached !== false) return rest_ensure_response($cached);

    $teams = ['ATK','BST','BUL','CMN','DST','ETS','HAT','MAX','PEP','PHR','RBS','SNR','SON','TSG','WRZ'];

    // cells["{winner}_{loser}"] = { count, weeks[], postseason_weeks[] }
    $cells = [];

    // ── Regular season — home games only (avoids double-counting) ────────────
    foreach ($teams as $team) {
        $table = "wp_team_{$team}";
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) continue;

        // ARRAY_N: [0]=weekid [4]=points [6]=versus_pts [7]=home_away
        $rows = $wpdb->get_results("SELECT * FROM {$table} WHERE week BETWEEN 1 AND 14", ARRAY_N);
        foreach ($rows as $r) {
            if ($r[7] !== 'H') continue;
            $s1 = (float) $r[4];
            $s2 = (float) $r[6];
            if ($s1 == $s2) continue; // ties don't count
            $winner = (int) max($s1, $s2);
            $loser  = (int) min($s1, $s2);
            $weekid = $r[0]; // e.g. "199201"
            $key = "{$winner}_{$loser}";
            if (!isset($cells[$key])) {
                $cells[$key] = ['count' => 0, 'weeks' => [], 'postseason_weeks' => []];
            }
            $cells[$key]['count']++;
            $cells[$key]['weeks'][] = (string) $weekid;
        }
    }

    // ── Playoffs ─────────────────────────────────────────────────────────────
    // Get all playoff matchups (one row per winning team per game)
    $po_games = $wpdb->get_results(
        "SELECT year, week, team, versus FROM wp_playoffs WHERE result = 1 AND overtime = 0 GROUP BY year, week, team",
        ARRAY_A
    );

    foreach ($po_games as $g) {
        $year   = $g['year'];
        $week   = $g['week'];
        $winner = $g['team'];
        $loser  = $g['versus'];

        $win_pts = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points) FROM wp_playoffs WHERE year = %d AND week = %d AND team = %s AND overtime = 0",
            $year, $week, $winner
        ));
        $los_pts = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points) FROM wp_playoffs WHERE year = %d AND week = %d AND team = %s AND overtime = 0",
            $year, $week, $loser
        ));

        if ($win_pts <= 0 || $los_pts <= 0) continue;
        $w = (int) max($win_pts, $los_pts);
        $l = (int) min($win_pts, $los_pts);
        $weekid = $year . str_pad($week, 2, '0', STR_PAD_LEFT);
        $key = "{$w}_{$l}";
        if (!isset($cells[$key])) {
            $cells[$key] = ['count' => 0, 'weeks' => [], 'postseason_weeks' => []];
        }
        $cells[$key]['count']++;
        $cells[$key]['postseason_weeks'][] = (string) $weekid;
        $cells[$key]['weeks'][] = (string) $weekid;
    }

    // ── Seasons + weeks list for UI dropdowns ─────────────────────────────────
    $seasons = array_map('intval', $wpdb->get_col(
        "SELECT DISTINCT season FROM wp_team_WRZ ORDER BY season DESC"
    ));

    $all_weeks = [];
    foreach ($seasons as $yr) {
        for ($w = 1; $w <= 14; $w++) {
            $all_weeks[] = $yr . str_pad($w, 2, '0', STR_PAD_LEFT);
        }
        // add playoff weeks if they have data
        foreach ([15, 16] as $pw) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM wp_playoffs WHERE year = %d AND week = %d", $yr, $pw
            ));
            if ($exists) $all_weeks[] = $yr . str_pad($pw, 2, '0', STR_PAD_LEFT);
        }
    }

    // Determine max score for grid size
    $max_score = 75;
    foreach (array_keys($cells) as $key) {
        [$w] = explode('_', $key);
        if ((int)$w > $max_score) $max_score = (int)$w;
    }

    $result = [
        'cells'      => $cells,
        'seasons'    => $seasons,
        'all_weeks'  => $all_weeks,
        'max_score'  => $max_score,
    ];

    set_transient($cache_key, $result, 6 * HOUR_IN_SECONDS);
    return rest_ensure_response($result);
}

// ─────────────────────────────────────────────────────────────────────────────
// HEAD-TO-HEAD MATRIX ENDPOINT
// ─────────────────────────────────────────────────────────────────────────────

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/head-to-head-matrix', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_head_to_head_matrix',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_head_to_head_matrix() {
    global $wpdb;

    $cache_key = 'pfl_head_to_head_matrix_v1';
    $cached = get_transient($cache_key);
    if ($cached !== false) return rest_ensure_response($cached);

    $teams = ['ATK','BST','BUL','CMN','DST','ETS','HAT','MAX','PEP','PHR','RBS','SNR','SON','TSG','WRZ'];

    // Build team name map
    $team_names = [];
    $rows = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_N);
    foreach ($rows as $r) {
        $team_names[$r[0]] = $r[1];
    }

    // Initialise empty matrix
    $matrix = [];
    foreach ($teams as $home) {
        foreach ($teams as $away) {
            $matrix[$home][$away] = ['wins' => 0, 'losses' => 0, 'pf' => 0.0, 'pa' => 0.0];
        }
    }

    // Scan each team's game table — regular season only (week 1-14)
    foreach ($teams as $team) {
        $table = "wp_team_{$team}";
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) continue;

        // ARRAY_N indices: [4]=points [5]=versus [6]=versus_pts [9]=result
        $games = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE week BETWEEN 1 AND 14",
            ARRAY_N
        );

        foreach ($games as $g) {
            $opp    = $g[5];
            $pf     = (float) $g[4];
            $pa     = (float) $g[6];
            $result = (float) $g[9];

            if (!in_array($opp, $teams)) continue;

            if ($result > 0) {
                $matrix[$team][$opp]['wins']++;
            } elseif ($result < 0) {
                $matrix[$team][$opp]['losses']++;
            }
            // Ties (result === 0) count as neither — PFL doesn't have ties,
            // but guard against it anyway
            $matrix[$team][$opp]['pf'] += $pf;
            $matrix[$team][$opp]['pa'] += $pa;
        }
    }

    // Round point totals
    foreach ($teams as $home) {
        foreach ($teams as $away) {
            $matrix[$home][$away]['pf'] = round($matrix[$home][$away]['pf'], 1);
            $matrix[$home][$away]['pa'] = round($matrix[$home][$away]['pa'], 1);
        }
    }

    $result = ['teams' => $teams, 'team_names' => $team_names, 'matrix' => $matrix];
    set_transient($cache_key, $result, 6 * HOUR_IN_SECONDS);
    return rest_ensure_response($result);
}

// ─────────────────────────────────────────────────────────────────────────────
// PLAYER RETIREMENT — SET / CLEAR retireyear
// ─────────────────────────────────────────────────────────────────────────────

// One-time migration: add retireyear column to wp_players if absent
add_action('init', function () {
    global $wpdb;
    $cols = $wpdb->get_col("SHOW COLUMNS FROM wp_players LIKE 'retireyear'");
    if (empty($cols)) {
        $wpdb->query("ALTER TABLE wp_players ADD COLUMN retireyear SMALLINT NULL DEFAULT NULL");
    }
}, 1);

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-retireyear', [
        'methods'             => 'POST',
        'callback'            => 'pfl_api_set_retireyear',
        'permission_callback' => '__return_true',
        'args'                => [
            'pid'        => ['required' => true,  'sanitize_callback' => 'sanitize_text_field'],
            'retireyear' => ['required' => false, 'sanitize_callback' => 'absint'],
        ],
    ]);
});

function pfl_api_set_retireyear(WP_REST_Request $request) {
    global $wpdb;

    $pid        = $request->get_param('pid');
    $retireyear = $request->get_param('retireyear'); // null/absent → clear

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM wp_players WHERE p_id = %s", $pid
    ));
    if (!$exists) return new WP_Error('not_found', 'Player not found', ['status' => 404]);

    $wpdb->update(
        'wp_players',
        ['retireyear' => $retireyear ?: null],
        ['p_id' => $pid],
        [$retireyear ? '%d' : 'NULL'],
        ['%s']
    );

    // Bust the MFL-active transient so the change is reflected immediately
    $mflid = $wpdb->get_var($wpdb->prepare(
        "SELECT mflid FROM wp_players WHERE p_id = %s LIMIT 1", $pid
    ));
    if ($mflid) delete_transient("pfl_nfl_active_{$mflid}");

    return rest_ensure_response([
        'pid'        => $pid,
        'retireyear' => $retireyear ?: null,
    ]);
}

// ─────────────────────────────────────────────────────────────────────────────
// CACHE MANAGEMENT — CLEAR TRANSIENTS ENDPOINT
// ─────────────────────────────────────────────────────────────────────────────

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/clear-cache', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_clear_cache',
        'permission_callback' => '__return_true',
        'args'                => [
            'year' => ['required' => false, 'sanitize_callback' => 'absint'],
            'week' => ['required' => false, 'sanitize_callback' => 'absint'],
        ],
    ]);
    register_rest_route('pfl/v1', '/game-of-week', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_get_game_of_week',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/game-of-week', [
        'methods'             => 'POST',
        'callback'            => 'pfl_api_set_game_of_week',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/game-of-week-year', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_get_game_of_week_year',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_get_game_of_week_year(WP_REST_Request $request) {
    global $wpdb;
    $year = (int) $request->get_param('year');
    if (!$year) return new WP_Error('missing_params', 'year required', ['status' => 400]);
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
        "pfl_gotw_{$year}_%"
    ), ARRAY_A);
    $result = [];
    foreach ($rows as $r) {
        // key is pfl_gotw_{year}_{week}
        $parts = explode('_', $r['option_name']);
        $week = (int) end($parts);
        if ($week && $r['option_value']) $result[$week] = $r['option_value'];
    }
    return rest_ensure_response($result);
}

function pfl_api_get_game_of_week(WP_REST_Request $request) {
    $year = (int) $request->get_param('year');
    $week = (int) $request->get_param('week');
    if (!$year || !$week) return new WP_Error('missing_params', 'year and week required', ['status' => 400]);
    $home = get_option("pfl_gotw_{$year}_{$week}", '');
    return rest_ensure_response(['home' => $home ?: null]);
}

function pfl_api_set_game_of_week(WP_REST_Request $request) {
    $body = $request->get_json_params();
    $year = (int) ($body['year'] ?? 0);
    $week = (int) ($body['week'] ?? 0);
    $home = sanitize_text_field($body['home'] ?? '');
    if (!$year || !$week) return new WP_Error('missing_params', 'year and week required', ['status' => 400]);
    $key = "pfl_gotw_{$year}_{$week}";
    if ($home) {
        update_option($key, strtoupper($home));
    } else {
        delete_option($key);
    }
    // Bust the weekly results cache so the GOTW attendance bonus takes effect immediately
    $week_pad = str_pad($week, 2, '0', STR_PAD_LEFT);
    delete_transient("pfl_weekly_results_{$year}{$week_pad}_v36");
    return rest_ensure_response(['home' => $home ?: null]);
}

function pfl_api_clear_cache(WP_REST_Request $request) {
    global $wpdb;

    $year = (int) $request->get_param('year');
    $week = (int) $request->get_param('week');

    if ($year && $week) {
        $week_pad = str_pad($week, 2, '0', STR_PAD_LEFT);
        $keys = [
            "pfl_weekly_results_{$year}{$week_pad}_v36",
            "pfl_results_sidebar_{$year}{$week_pad}_v3",
        ];
        foreach ($keys as $key) {
            delete_transient($key);
        }
        // Re-compute lineup efficiency for this week so new data is stored
        apply_filters('pfl_after_cache_clear', $year, $week);
        return rest_ensure_response(['cleared' => $keys, 'scope' => "{$year} week {$week}"]);
    }

    // Clear all PFL transients
    $deleted = $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pfl_%' OR option_name LIKE '_transient_timeout_pfl_%'"
    );
    return rest_ensure_response(['cleared' => $deleted, 'scope' => 'all']);
}

// ═════════════════════════════════════════════════════════════════════════════
// LINEUP EFFICIENCY — persistent storage & aggregate analysis
// ─────────────────────────────────────────────────────────────────────────────
// Table: wp_lineup_efficiency
//   year  week  team  pos  actual_pts  optimal_pts
//   One row per (year, week, team, pos).  ~13 k rows for 2011–2025.
// ═════════════════════════════════════════════════════════════════════════════

// ── Create table if missing ───────────────────────────────────────────────────
function pfl_ensure_lineup_efficiency_table() {
    global $wpdb;
    $table   = $wpdb->prefix . 'lineup_efficiency';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
        id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
        year        SMALLINT(4)     NOT NULL,
        week        TINYINT(2)      NOT NULL,
        team        VARCHAR(3)      NOT NULL,
        pos         VARCHAR(2)      NOT NULL,
        actual_pts  DECIMAL(6,1)    NOT NULL DEFAULT 0,
        optimal_pts DECIMAL(6,1)    NOT NULL DEFAULT 0,
        bench_pid   VARCHAR(20)     NULL,
        bench_pts   DECIMAL(6,1)    NULL,
        bench_count TINYINT         NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uk_game_pos (year, week, team, pos),
        KEY idx_year      (year),
        KEY idx_team      (team),
        KEY idx_year_team (year, team)
    ) {$charset};";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
add_action('admin_init', 'pfl_ensure_lineup_efficiency_table');

// ── Core compute function — one week, all teams ───────────────────────────────
// Logic mirrors GamePanel.tsx: for each position (QB/RB/WR/PK), compare the
// one regular starter (cols 10-13) against the best bench player (incl. IR).
// optimal = max(starter_pts, best_bench_pts)
// Returns [ 'written' => N, 'skipped' => N, 'errors' => [] ]
function pfl_compute_lineup_efficiency_for_week($year, $week) {
    global $wpdb;
    $table    = $wpdb->prefix . 'lineup_efficiency';
    $week_pad = str_pad($week, 2, '0', STR_PAD_LEFT);
    $weekid   = $year . $week_pad;

    $all_teams  = ['RBS','ETS','PEP','WRZ','CMN','BUL','SNR','TSG','BST','MAX','PHR','SON','ATK','HAT','DST'];
    $espn_stats = pfl_get_week_espn_player_stats($year, $week);

    $written = 0; $skipped = 0; $errors = [];

    foreach ($all_teams as $team) {
        // cols: QB=10, RB=11, WR=12, PK=13 (regular starters only; 15-18 are OT)
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM wp_team_{$team} WHERE id = %s LIMIT 1", $weekid),
            ARRAY_N
        );
        if (!$row) { $skipped++; continue; }

        $starter_pids = [
            'QB' => $row[10] ?? '',
            'RB' => $row[11] ?? '',
            'WR' => $row[12] ?? '',
            'PK' => $row[13] ?? '',
        ];
        // OT starters excluded from bench pool too
        $all_starter_pids = array_values(array_filter(array_merge(
            array_values($starter_pids),
            [$row[15] ?? '', $row[16] ?? '', $row[17] ?? '', $row[18] ?? '']
        )));

        // Starter actual points — col 3 in individual player table
        $starter_pts = [];
        foreach ($starter_pids as $pos => $pid) {
            if (!$pid || strtolower(trim($pid)) === 'none') { $starter_pts[$pos] = 0.0; continue; }
            $safe = preg_replace('/[^a-zA-Z0-9_]/', '', $pid);
            $r    = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$safe}` WHERE week_id = %s LIMIT 1", $weekid), ARRAY_N);
            $starter_pts[$pos] = $r ? (float) $r[3] : 0.0;
        }

        // Best bench player per position (all statuses including IR for scoring).
        // bench_count tracks active players only — excludes IR and Inactive.
        // get_the_bench() uses get_stylesheet_directory() — works in CLI
        $excluded_statuses = ['ir', 'injured_reserve', 'inactive', 'dnp'];
        $best_bench     = ['QB' => 0.0,  'RB' => 0.0,  'WR' => 0.0,  'PK' => 0.0];
        $best_bench_pid = ['QB' => null, 'RB' => null, 'WR' => null, 'PK' => null];
        $bench_count    = ['QB' => 0,    'RB' => 0,    'WR' => 0,    'PK' => 0];
        $bench_raw  = @get_the_bench($year, $week, $team);
        if ($bench_raw) {
            foreach ($bench_raw as $status => $players) {
                $is_excluded = in_array(strtolower($status), $excluded_statuses);
                foreach ($players as $pid => $info) {
                    if (!$pid || in_array($pid, $all_starter_pids)) continue;
                    $pos_raw = strtoupper($info['position'] ?? '');
                    if (!array_key_exists($pos_raw, $best_bench)) continue;
                    // Count active bench players (all statuses score; only non-excluded count)
                    if (!$is_excluded) {
                        $bench_count[$pos_raw]++;
                    }
                    $full_name = $wpdb->get_var($wpdb->prepare(
                        "SELECT CONCAT(playerFirst,' ',playerLast) FROM wp_players WHERE p_id = %s LIMIT 1", $pid
                    ));
                    $pts = pfl_calc_bench_score($full_name ?: ($info['name'] ?? ''), $pos_raw, $year, $espn_stats);
                    if ($pts !== null && (float) $pts > $best_bench[$pos_raw]) {
                        $best_bench[$pos_raw]     = (float) $pts;
                        $best_bench_pid[$pos_raw] = $pid;
                    }
                }
            }
        }

        // Persist
        foreach (['QB', 'RB', 'WR', 'PK'] as $pos) {
            $actual    = round($starter_pts[$pos], 1);
            $optimal   = round(max($actual, $best_bench[$pos]), 1);
            $b_pid     = $best_bench[$pos] > $actual ? $best_bench_pid[$pos] : null;
            $b_pts     = $best_bench[$pos] > $actual ? round($best_bench[$pos], 1) : null;
            $b_count   = $bench_count[$pos];
            $ok = $wpdb->query($wpdb->prepare(
                "INSERT INTO {$table} (year, week, team, pos, actual_pts, optimal_pts, bench_pid, bench_pts, bench_count)
                 VALUES (%d, %d, %s, %s, %f, %f, %s, %s, %d)
                 ON DUPLICATE KEY UPDATE actual_pts = VALUES(actual_pts), optimal_pts = VALUES(optimal_pts),
                                         bench_pid = VALUES(bench_pid), bench_pts = VALUES(bench_pts),
                                         bench_count = VALUES(bench_count)",
                $year, $week, $team, $pos, $actual, $optimal, $b_pid, $b_pts, $b_count
            ));
            if ($ok === false) $errors[] = "{$year} w{$week} {$team} {$pos}";
            else $written++;
        }
    }

    return compact('written', 'skipped', 'errors');
}

// ── REST: sync missing weeks (idempotent, safe to call anytime) ───────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/lineup-efficiency/sync', [
        'methods'             => 'POST',
        'callback'            => 'pfl_api_lineup_efficiency_sync',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/lineup-efficiency/summary', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_lineup_efficiency_summary',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/lineup-efficiency', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_lineup_efficiency_get',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_lineup_efficiency_sync(WP_REST_Request $request) {
    pfl_ensure_lineup_efficiency_table();
    global $wpdb;
    $table = $wpdb->prefix . 'lineup_efficiency';

    // Optional: limit to a specific year/week passed in the request body
    $only_year = (int) ($request->get_param('year') ?: 0);
    $only_week = (int) ($request->get_param('week') ?: 0);

    // Find all (year, week) pairs that exist in team data
    $all_week_ids = $wpdb->get_col(
        "SELECT DISTINCT id FROM wp_team_WRZ WHERE season >= 2011 ORDER BY id ASC"
    );

    $total_written = 0; $total_skipped = 0; $processed = [];

    foreach ($all_week_ids as $weekid) {
        $yr  = (int) substr($weekid, 0, 4);
        $wk  = (int) substr($weekid, 4);
        if ($wk < 1 || $wk > 14) continue; // regular season only
        if ($only_year && $yr !== $only_year) continue;
        if ($only_week && $wk !== $only_week) continue;

        // Check if already fully populated (60 rows = 15 teams × 4 pos)
        $existing = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE year = %d AND week = %d", $yr, $wk
        ));
        if ($existing >= 60 && !$only_year) { $total_skipped++; continue; }

        $result = pfl_compute_lineup_efficiency_for_week($yr, $wk);
        $total_written += $result['written'];
        $processed[] = "{$yr} w{$wk} ({$result['written']} rows)";
    }

    return rest_ensure_response([
        'processed' => count($processed),
        'written'   => $total_written,
        'skipped'   => $total_skipped,
        'detail'    => $processed,
    ]);
}

// ── REST: query stored efficiency data ───────────────────────────────────────
function pfl_api_lineup_efficiency_get(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . 'lineup_efficiency';

    $year  = (int) ($request->get_param('year')  ?: 0);
    $team  =       ($request->get_param('team')  ?: '');
    $pos   =       ($request->get_param('pos')   ?: '');

    $where = ['1=1'];
    $vals  = [];
    if ($year) { $where[] = 'year = %d'; $vals[] = $year; }
    if ($team) { $where[] = 'team = %s'; $vals[] = strtoupper($team); }
    if ($pos)  { $where[] = 'pos = %s';  $vals[] = strtoupper($pos);  }

    $sql = "SELECT year, week, team, pos, actual_pts, optimal_pts,
                   ROUND((actual_pts / NULLIF(optimal_pts,0)) * 100, 1) AS efficiency_pct
            FROM {$table}
            WHERE " . implode(' AND ', $where) . "
            ORDER BY year ASC, week ASC, team ASC, pos ASC";

    $rows = $vals ? $wpdb->get_results($wpdb->prepare($sql, ...$vals), ARRAY_A)
                  : $wpdb->get_results($sql, ARRAY_A);

    return rest_ensure_response($rows ?: []);
}

// ── REST: aggregate summary for the UI page ──────────────────────────────────
function pfl_api_lineup_efficiency_summary() {
    global $wpdb;
    $t = $wpdb->prefix . 'lineup_efficiency';

    // Per-team overall + per-position efficiency
    $by_team = $wpdb->get_results("
        SELECT team,
            ROUND(SUM(actual_pts)  / NULLIF(SUM(optimal_pts), 0) * 100, 1) AS eff_pct,
            ROUND(SUM(optimal_pts - actual_pts), 1)                         AS left_on_bench,
            COUNT(DISTINCT CONCAT(year,'-',week))                            AS games,
            ROUND(SUM(CASE WHEN pos='QB' THEN actual_pts  ELSE 0 END) / NULLIF(SUM(CASE WHEN pos='QB' THEN optimal_pts ELSE 0 END),0)*100,1) AS qb_eff,
            ROUND(SUM(CASE WHEN pos='RB' THEN actual_pts  ELSE 0 END) / NULLIF(SUM(CASE WHEN pos='RB' THEN optimal_pts ELSE 0 END),0)*100,1) AS rb_eff,
            ROUND(SUM(CASE WHEN pos='WR' THEN actual_pts  ELSE 0 END) / NULLIF(SUM(CASE WHEN pos='WR' THEN optimal_pts ELSE 0 END),0)*100,1) AS wr_eff,
            ROUND(SUM(CASE WHEN pos='PK' THEN actual_pts  ELSE 0 END) / NULLIF(SUM(CASE WHEN pos='PK' THEN optimal_pts ELSE 0 END),0)*100,1) AS pk_eff,
            ROUND(SUM(optimal_pts - actual_pts) / NULLIF(COUNT(DISTINCT CONCAT(year,'-',week)), 0), 1) AS avg_left_per_game
        FROM {$t}
        GROUP BY team
        ORDER BY eff_pct DESC
    ", ARRAY_A);

    // Per-season league-wide efficiency
    $by_season = $wpdb->get_results("
        SELECT year,
            ROUND(SUM(actual_pts) / NULLIF(SUM(optimal_pts), 0) * 100, 1) AS eff_pct,
            ROUND(SUM(optimal_pts - actual_pts) / NULLIF(COUNT(DISTINCT CONCAT(team,week)), 0), 1) AS avg_left_per_game
        FROM {$t}
        GROUP BY year
        ORDER BY year ASC
    ", ARRAY_A);

    // Per-position league-wide efficiency
    $by_pos = $wpdb->get_results("
        SELECT pos,
            ROUND(SUM(actual_pts) / NULLIF(SUM(optimal_pts), 0) * 100, 1) AS eff_pct,
            ROUND(SUM(optimal_pts - actual_pts), 1) AS total_left,
            ROUND(SUM(optimal_pts - actual_pts) / NULLIF(COUNT(*), 0), 2) AS avg_left_per_game
        FROM {$t}
        GROUP BY pos
        ORDER BY eff_pct ASC
    ", ARRAY_A);

    // Worst individual team-weeks (most points left on bench)
    $worst_weeks = $wpdb->get_results("
        SELECT year, week, team,
            ROUND(SUM(actual_pts), 1)  AS actual,
            ROUND(SUM(optimal_pts), 1) AS optimal,
            ROUND(SUM(actual_pts) / NULLIF(SUM(optimal_pts), 0) * 100, 1) AS eff_pct,
            ROUND(SUM(optimal_pts - actual_pts), 1) AS left_on_bench
        FROM {$t}
        GROUP BY year, week, team
        ORDER BY left_on_bench DESC
        LIMIT 20
    ", ARRAY_A);

    // Attach W/L result for each worst week from the team's own table
    foreach ($worst_weeks as &$row) {
        $team    = preg_replace('/[^a-zA-Z0-9_]/', '', $row['team']);
        $week_id = $row['year'] . str_pad($row['week'], 2, '0', STR_PAD_LEFT);
        $result  = $wpdb->get_var($wpdb->prepare(
            "SELECT result FROM wp_team_{$team} WHERE id = %s LIMIT 1", $week_id
        ));
        $row['result'] = is_numeric($result) ? ((int)$result > 0 ? 'W' : 'L') : null;
    }
    unset($row);

    // Best team-seasons by average efficiency
    $best_seasons = $wpdb->get_results("
        SELECT team, year,
            ROUND(SUM(actual_pts) / NULLIF(SUM(optimal_pts), 0) * 100, 1) AS eff_pct,
            ROUND(SUM(optimal_pts - actual_pts), 1) AS left_on_bench,
            COUNT(DISTINCT week) AS weeks,
            ROUND(SUM(bench_count) / NULLIF(COUNT(DISTINCT week), 0), 1) AS avg_bench_per_week
        FROM {$t}
        GROUP BY team, year
        HAVING avg_bench_per_week >= 5.0
        ORDER BY eff_pct DESC
        LIMIT 20
    ", ARRAY_A);

    // Top 20 individual bench player performances
    $bench_performances = $wpdb->get_results("
        SELECT le.year, le.week, le.team, le.bench_pid, le.bench_pts,
            CONCAT(p.playerFirst, ' ', p.playerLast) AS player_name
        FROM {$t} le
        LEFT JOIN wp_players p ON p.p_id = le.bench_pid
        WHERE le.bench_pid IS NOT NULL AND le.bench_pts IS NOT NULL
        ORDER BY le.bench_pts DESC
        LIMIT 20
    ", ARRAY_A);

    return rest_ensure_response([
        'by_team'            => $by_team,
        'by_season'          => $by_season,
        'by_pos'             => $by_pos,
        'worst_weeks'        => $worst_weeks,
        'best_seasons'       => $best_seasons,
        'bench_performances' => $bench_performances,
    ]);
}

// ── Auto-sync after cache clear (picks up new 2026+ weeks) ───────────────────
add_filter('pfl_after_cache_clear', function ($year, $week) {
    if ($year >= 2011 && $week >= 1 && $week <= 14) {
        pfl_ensure_lineup_efficiency_table();
        pfl_compute_lineup_efficiency_for_week($year, $week);
    }
    return true;
}, 10, 2);

// ── Trade Audit Endpoint ──────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/trade-audit', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_trade_audit',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_trade_audit(WP_REST_Request $request) {
    global $wpdb;

    // All picks that changed hands (pickord != team)
    $drafts = $wpdb->get_results("
        SELECT id, year, `round`, roundnum, picknum, pickord, team,
               playerfirst, playerlast, pos, tradeid
        FROM wp_drafts
        WHERE pickord != team
        ORDER BY year, `round`, roundnum
    ", ARRAY_A);

    $trades_raw = $wpdb->get_results("
        SELECT id, year, team1, picks1, team2, picks2, notes
        FROM wp_trades
        ORDER BY year, id
    ", ARRAY_A);

    // Build trade lookup, pick→trade(s) index, and team-pair→trades index
    $trades          = [];
    $pick_to_trades  = [];
    $pair_to_trades  = []; // "A:B" (alphabetical) → [trade, ...]
    foreach ($trades_raw as $t) {
        $picks1 = array_values(array_filter(array_map('trim', explode(',', $t['picks1'] ?? ''))));
        $picks2 = array_values(array_filter(array_map('trim', explode(',', $t['picks2'] ?? ''))));
        $trades[(int)$t['id']] = [
            'id'    => (int)$t['id'],
            'year'  => (int)$t['year'],
            'team1' => $t['team1'],
            'team2' => $t['team2'],
            'picks1'=> $picks1,
            'picks2'=> $picks2,
            'notes' => trim($t['notes'] ?? ''),
        ];
        foreach ($picks1 as $p) { $pick_to_trades[$p][] = ['tid' => (int)$t['id'], 'recv' => $t['team1']]; }
        foreach ($picks2 as $p) { $pick_to_trades[$p][] = ['tid' => (int)$t['id'], 'recv' => $t['team2']]; }

        // Normalise key so A:B == B:A
        $pair = implode(':', array_unique([min($t['team1'], $t['team2']), max($t['team1'], $t['team2'])]));
        $pair_to_trades[$pair][] = (int)$t['id'];
    }

    $confirmed_count = 0;
    $ambiguous       = [];
    $gaps            = [];

    foreach ($drafts as $d) {
        $tradeid  = (int)($d['tradeid'] ?? 0);
        $pick_str = sprintf('%s.%02d.%02d', $d['year'], (int)$d['round'], (int)$d['roundnum']);
        $matches  = $pick_to_trades[$pick_str] ?? [];
        $player   = trim(($d['playerfirst'] ?? '') . ' ' . ($d['playerlast'] ?? '')) ?: '—';
        if ($d['pos']) $player .= ' (' . $d['pos'] . ')';

        $base = [
            'draft_id'        => $d['id'],
            'pick_str'        => $pick_str,
            'year'            => (int)$d['year'],
            'round'           => (int)$d['round'],
            'roundnum'        => (int)$d['roundnum'],
            'picknum'         => (int)$d['picknum'],
            'pickord'         => $d['pickord'],
            'team'            => $d['team'],
            'player'          => $player,
            'current_tradeid' => $tradeid,
        ];

        if (empty($matches)) {
            // Skip picks already correctly linked to a specific trade
            if ($tradeid > 1) continue;

            // Look for existing trades between this same team pair
            $pair     = implode(':', [min($d['pickord'], $d['team']), max($d['pickord'], $d['team'])]);
            $pair_ids = $pair_to_trades[$pair] ?? [];
            $candidates = [];
            foreach ($pair_ids as $tid) {
                $t        = $trades[$tid];
                $year_diff = abs($t['year'] - (int)$d['year']);
                if ($year_diff > 3) continue; // only show reasonably close trades
                $all_picks = array_merge($t['picks1'], $t['picks2']);
                $candidates[] = [
                    'id'        => $tid,
                    'year'      => $t['year'],
                    'team1'     => $t['team1'],
                    'team2'     => $t['team2'],
                    'picks1'    => $t['picks1'],
                    'picks2'    => $t['picks2'],
                    'notes'     => $t['notes'],
                    'year_diff' => $year_diff,
                    'pick_count'=> count($all_picks),
                ];
            }
            // Sort: same year first, then by proximity
            usort($candidates, fn($a, $b) => $a['year_diff'] - $b['year_diff']);

            $gaps[] = array_merge($base, [
                'candidates' => $candidates,
                'has_same_year_trade' => !empty(array_filter($candidates, fn($c) => $c['year_diff'] === 0)),
            ]);
        } elseif (count($matches) === 1) {
            $confirmed_count++;
        } else {
            // Ambiguous: multiple trades contain this pick string
            $trade_options = [];
            foreach ($matches as $m) {
                $t = $trades[$m['tid']] ?? [];
                $trade_options[] = [
                    'id'    => $m['tid'],
                    'recv'  => $m['recv'],
                    'year'  => $t['year']  ?? null,
                    'team1' => $t['team1'] ?? '',
                    'team2' => $t['team2'] ?? '',
                    'picks1'=> $t['picks1'] ?? [],
                    'picks2'=> $t['picks2'] ?? [],
                    'notes' => $t['notes'] ?? '',
                    'recv_matches' => ($m['recv'] === $d['team']),
                ];
            }
            $ambiguous[] = array_merge($base, ['trade_options' => $trade_options]);
        }
    }

    return rest_ensure_response([
        'summary' => [
            'correctly_linked' => $confirmed_count,
            'ambiguous'        => count($ambiguous),
            'gaps'             => count($gaps),
        ],
        'ambiguous' => $ambiguous,
        'gaps'      => $gaps,
    ]);
}

// ── All Posse Bowls ───────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/posse-bowls', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_posse_bowls',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_posse_bowls() {
    global $wpdb;

    $champs = $wpdb->get_results(
        "SELECT year, winTeam FROM wp_champions ORDER BY year ASC",
        ARRAY_A
    );

    $team_rows = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    $team_names = [];
    foreach ($team_rows as $t) $team_names[$t['team_int']] = $t['team'];

    $mvp_rows = $wpdb->get_results(
        "SELECT year, playerFirst, playerLast, pid, team
         FROM wp_awards WHERE award = 'Posse Bowl MVP' ORDER BY year ASC",
        ARRAY_A
    );
    $mvps = [];
    foreach ($mvp_rows as $m) $mvps[(int) $m['year']] = $m;

    // Aggregate all week-16 playoff rows: scores split by OT flag
    $po_rows = $wpdb->get_results(
        "SELECT year, team, versus, overtime, SUM(points) as total
         FROM wp_playoffs WHERE week = 16
         GROUP BY year, team, versus, overtime
         ORDER BY year ASC",
        ARRAY_A
    );

    // Build [year][team][ot|reg] = score
    $scores = [];
    foreach ($po_rows as $r) {
        $yr  = (int) $r['year'];
        $key = (int) $r['overtime'] ? 'ot' : 'reg';
        $scores[$yr][$r['team']][$key]  = (int) $r['total'];
        $scores[$yr][$r['team']]['vs']  = $r['versus'];
    }

    $theme_uri = get_stylesheet_directory_uri();
    $result    = [];

    foreach ($champs as $c) {
        $year   = (int) $c['year'];
        $winner = $c['winTeam'];

        if (!isset($scores[$year])) continue;

        // Find the opponent (not the winner)
        $loser = null;
        foreach (array_keys($scores[$year]) as $team) {
            if ($team !== $winner) { $loser = $team; break; }
        }
        if (!$loser) continue;

        $isOT = isset($scores[$year][$winner]['ot']);

        // Winner's score = reg starters + 1 (OT tiebreaker); loser's score = reg starters only
        $winnerScore = ($scores[$year][$winner]['reg'] ?? 0) + ($isOT ? 1 : 0);
        $loserScore  =  $scores[$year][$loser]['reg']  ?? 0;

        $winHelm  = pfl_get_helmet_num($winner, $year);
        $loseHelm = pfl_get_helmet_num($loser,  $year);
        $mvp      = $mvps[$year] ?? null;

        $result[] = [
            'year'         => $year,
            'champion'     => $winner,
            'championName' => $team_names[$winner] ?? $winner,
            'championScore'=> $winnerScore,
            'runner'       => $loser,
            'runnerName'   => $team_names[$loser] ?? $loser,
            'runnerScore'  => $loserScore,
            'isOvertime'   => $isOT,
            'margin'       => $winnerScore - $loserScore,
            'championHelmetUrl' => "{$theme_uri}/img/helmets/final-renders/{$winner}/helmet-{$winner}-{$winHelm}-front.png",
            'runnerHelmetUrl'   => "{$theme_uri}/img/helmets/final-renders/{$loser}/helmet-{$loser}-{$loseHelm}-front.png",
            'mvp'          => $mvp ? [
                'name' => trim($mvp['playerFirst'] . ' ' . $mvp['playerLast']),
                'pid'  => $mvp['pid'],
                'team' => $mvp['team'],
            ] : null,
        ];
    }

    return rest_ensure_response($result);
}

// ── Home & Away Series ────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/home-and-away', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_home_and_away',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_home_and_away() {
    global $wpdb;

    $cached = get_transient('pfl_home_and_away');
    if ($cached !== false) return rest_ensure_response($cached);

    $all_teams = ['RBS','ETS','PEP','WRZ','CMN','BUL','SNR','TSG','BST','MAX','PHR','SON','ATK','HAT','DST'];

    $team_rows = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    $team_names = [];
    foreach ($team_rows as $t) $team_names[$t['team_int']] = $t['team'];

    // Build full schedule indexed by weekid — only home games (avoids duplicates)
    $schedule = [];
    foreach ($all_teams as $team) {
        $safe = esc_sql($team);
        $rows = $wpdb->get_results(
            "SELECT id, season, week, team_int, points, vs, vs_points
             FROM wp_team_{$safe} WHERE home_away = 'H'",
            ARRAY_A
        );
        foreach ($rows as $r) {
            $schedule[(int)$r['id']][] = [
                'weekid'    => (int) $r['id'],
                'season'    => (int) $r['season'],
                'week'      => (int) $r['week'],
                'homeTeam'  => $r['team_int'],
                'homeScore' => (int) $r['points'],
                'awayTeam'  => $r['vs'],
                'awayScore' => (int) $r['vs_points'],
            ];
        }
    }

    ksort($schedule);
    $weekids = array_keys($schedule);
    $series  = [];

    for ($i = 1; $i < count($weekids); $i++) {
        $curId  = $weekids[$i];
        $prevId = $weekids[$i - 1];

        // Must be same season
        if ((int)substr((string)$curId, 0, 4) !== (int)substr((string)$prevId, 0, 4)) continue;

        // Build lookup: prevWeek homeTeam → game record
        $prevMap = [];
        foreach ($schedule[$prevId] as $g) $prevMap[$g['homeTeam']] = $g;

        foreach ($schedule[$curId] as $g2) {
            // Series if: curWeek A hosted B, and prevWeek B hosted A
            $prevGame = $prevMap[$g2['awayTeam']] ?? null;
            if (!$prevGame || $prevGame['awayTeam'] !== $g2['homeTeam']) continue;

            $teamA = $g2['homeTeam'];  // home in game 2
            $teamB = $g2['awayTeam']; // home in game 1

            $aWins = 0;
            $bWins = 0;

            // Game 1: teamB hosted teamA
            if ($prevGame['homeScore'] > $prevGame['awayScore']) $bWins++;
            else $aWins++;

            // Game 2: teamA hosted teamB
            if ($g2['homeScore'] > $g2['awayScore']) $aWins++;
            else $bWins++;

            $series[] = [
                'year'      => $g2['season'],
                'week1'     => $prevGame['week'],
                'week2'     => $g2['week'],
                'teamA'     => $teamA,
                'teamAName' => $team_names[$teamA] ?? $teamA,
                'teamB'     => $teamB,
                'teamBName' => $team_names[$teamB] ?? $teamB,
                'game1'     => [
                    'week'      => $prevGame['week'],
                    'homeTeam'  => $teamB,
                    'homeName'  => $team_names[$teamB] ?? $teamB,
                    'homeScore' => $prevGame['homeScore'],
                    'awayTeam'  => $teamA,
                    'awayName'  => $team_names[$teamA] ?? $teamA,
                    'awayScore' => $prevGame['awayScore'],
                ],
                'game2'     => [
                    'week'      => $g2['week'],
                    'homeTeam'  => $teamA,
                    'homeName'  => $team_names[$teamA] ?? $teamA,
                    'homeScore' => $g2['homeScore'],
                    'awayTeam'  => $teamB,
                    'awayName'  => $team_names[$teamB] ?? $teamB,
                    'awayScore' => $g2['awayScore'],
                ],
                'teamAWins' => $aWins,
                'teamBWins' => $bWins,
                'winner'    => $aWins === 2 ? $teamA : ($bWins === 2 ? $teamB : 'split'),
            ];
        }
    }

    // Newest first
    usort($series, fn($a, $b) => $b['year'] <=> $a['year'] ?: $b['week2'] <=> $a['week2']);

    set_transient('pfl_home_and_away', $series, HOUR_IN_SECONDS);
    return rest_ensure_response($series);
}

// ── Grand Slams ───────────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/grandslams', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_grandslams',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_grandslams() {
    global $wpdb;

    // All slams, newest first
    $slams = $wpdb->get_results(
        "SELECT * FROM wp_grandslams ORDER BY weekid DESC",
        ARRAY_N
    );

    // Team name lookup via wp_teams
    $team_rows = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    $team_names = [];
    foreach ($team_rows as $t) {
        $team_names[$t['team_int']] = $t['team'];
    }

    // Collect all player IDs across all slams so we can batch-fetch names
    $all_pids = [];
    $slam_weeks = [];
    foreach ($slams as $slam) {
        $weekid = $slam[1];
        $teamid = $slam[2];
        $safe_team = esc_sql($teamid);
        $safe_week = esc_sql($weekid);
        $week = $wpdb->get_row(
            "SELECT season, week, team_int, points, vs, home_away, stadium, result, QB1, RB1, WR1, PK1
             FROM wp_team_{$safe_team} WHERE id = {$safe_week}",
            ARRAY_A
        );
        if (!$week) continue;
        $slam_weeks[] = ['weekid' => $weekid, 'teamid' => $teamid, 'week' => $week];
        foreach (['QB1','RB1','WR1','PK1'] as $col) {
            if (!empty($week[$col])) $all_pids[$week[$col]] = true;
        }
    }

    // Batch fetch player names
    $player_names = [];
    if (!empty($all_pids)) {
        $pid_list = array_keys($all_pids);
        $placeholders = implode(',', array_fill(0, count($pid_list), '%s'));
        $name_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ($placeholders)",
            ...$pid_list
        ), ARRAY_A);
        foreach ($name_rows as $p) {
            $player_names[$p['p_id']] = trim($p['playerFirst'] . ' ' . $p['playerLast']);
        }
    }

    $result = [];
    foreach ($slam_weeks as $entry) {
        $weekid = $entry['weekid'];
        $teamid = $entry['teamid'];
        $week   = $entry['week'];

        $pos_cols = ['QB' => 'QB1', 'RB' => 'RB1', 'WR' => 'WR1', 'PK' => 'PK1'];
        $players = [];
        foreach ($pos_cols as $pos => $col) {
            $pid = $week[$col] ?? '';
            if (empty($pid)) continue;
            $safe_pid  = esc_sql($pid);
            $safe_week = esc_sql($weekid);
            $score = $wpdb->get_var(
                "SELECT points FROM `{$safe_pid}` WHERE week_id = '{$safe_week}'"
            );
            $players[] = [
                'pos'   => $pos,
                'pid'   => $pid,
                'name'  => $player_names[$pid] ?? $pid,
                'score' => $score !== null ? (float) $score : null,
            ];
        }

        $differential = (float) $week['result'];
        $result[] = [
            'year'         => (int) $week['season'],
            'week'         => (int) $week['week'],
            'team'         => $teamid,
            'teamName'     => $team_names[$week['team_int']] ?? $teamid,
            'result'       => $differential >= 0 ? 'W' : 'L',
            'differential' => $differential,
            'points'       => (float) $week['points'],
            'versus'       => $week['vs'],
            'versusName'   => $team_names[$week['vs']] ?? $week['vs'],
            'homeAway'     => $week['home_away'],
            'stadium'      => $week['stadium'],
            'players'      => $players,
        ];
    }

    return rest_ensure_response($result);
}

// TEMP DEBUG
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/debug/pm-leaders', [
        'methods' => 'GET', 'callback' => 'pfl_debug_pm_leaders', 'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/timeline', [
        'methods' => 'GET', 'callback' => 'pfl_get_timeline_events', 'permission_callback' => '__return_true',
    ]);
});

// Returns [(weekid, streak_val), ...] for each time a player set a new personal streak high.
// Uses the same algorithm as get_player_game_streak.
function pfl_player_streak_milestones($pid) {
    global $wpdb;

    $weekly = get_player_data($pid);
    if (empty($weekly)) return [];

    $pos = strtoupper(substr($pid, -2));

    $games = [];
    foreach ($weekly as $row) {
        $games[] = ['year' => (int)$row['year'], 'week' => (int)$row['week'], 'bye' => false, 'bench' => false];
    }

    // Bye weeks
    $year_teams = [];
    foreach ($weekly as $row) {
        $yr = (int)$row['year'];
        $nt = $row['nflteam'] ?? '';
        if ($nt) $year_teams[$yr][] = $nt;
    }
    $theme_dir = get_stylesheet_directory();
    foreach ($year_teams as $yr => $team_list) {
        $counts = array_count_values($team_list);
        arsort($counts);
        $primary = array_key_first($counts);
        if (!$primary) continue;
        $bye_file = $theme_dir . '/nfl-bye-weeks/bye_weeks_' . $yr . '.json';
        if (!file_exists($bye_file)) continue;
        $bye_data = json_decode(file_get_contents($bye_file), true);
        if (empty($bye_data['bye_weeks'])) continue;
        $bye_week_num = null;
        foreach ($bye_data['bye_weeks'] as $bw) {
            if (in_array(strtoupper($primary), array_map('strtoupper', $bw['teams']))) {
                $bye_week_num = (int)$bw['week'];
                break;
            }
        }
        if (!$bye_week_num) continue;
        foreach ($games as $g) {
            if ($g['year'] === $yr && $g['week'] === $bye_week_num) { continue 2; }
        }
        $games[] = ['year' => $yr, 'week' => $bye_week_num, 'bye' => true, 'bench' => false];
    }

    // Bench/DNP weeks (2011+)
    if (in_array($pos, ['QB', 'RB', 'WR', 'PK'])) {
        $pos1 = $pos . '1';
        $pos2 = $pos . '2';
        $roster_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT year, team FROM wp_rosters WHERE pid = %s AND year >= 2011", $pid
        ), ARRAY_A);
        $roster_by_year = [];
        foreach ($roster_rows as $r) { $roster_by_year[(int)$r['year']][] = $r['team']; }
        $existing = [];
        foreach ($games as $g) { $existing[$g['year'] . '_' . $g['week']] = true; }
        foreach ($roster_by_year as $yr => $yr_teams) {
            if (count($yr_teams) > 1) continue;
            $pfl_team = $yr_teams[0];
            $team_table = 'wp_team_' . esc_sql($pfl_team);
            $team_weeks = $wpdb->get_results($wpdb->prepare(
                "SELECT week, `{$pos1}`, `{$pos2}` FROM `{$team_table}` WHERE season = %d ORDER BY week", $yr
            ), ARRAY_A);
            foreach ($team_weeks as $tw) {
                $wk = (int)$tw['week'];
                if ($tw[$pos1] === $pid || $tw[$pos2] === $pid) continue;
                if (isset($existing[$yr . '_' . $wk])) continue;
                $games[] = ['year' => $yr, 'week' => $wk, 'bye' => false, 'bench' => true];
                $existing[$yr . '_' . $wk] = true;
            }
        }
    }

    usort($games, fn($a, $b) => $a['year'] !== $b['year'] ? $a['year'] - $b['year'] : $a['week'] - $b['week']);

    $covered = [];
    foreach ($games as $g) { $covered[$g['year'] . '_' . $g['week']] = true; }

    $next = 1;
    $max_so_far = 0;
    $prev_year = $prev_week = null;
    $milestones = [];

    foreach ($games as $g) {
        if ($g['bye']) continue;
        if ($g['bench']) { $next = 1; $prev_year = $prev_week = null; continue; }
        if ($prev_year !== null) {
            if ($prev_year === $g['year']) {
                for ($w = $prev_week + 1; $w < $g['week']; $w++) {
                    if (!isset($covered[$g['year'] . '_' . $w])) { $next = 1; break; }
                }
            } else {
                for ($w = 1; $w < $g['week']; $w++) {
                    if (!isset($covered[$g['year'] . '_' . $w])) { $next = 1; break; }
                }
            }
        }
        $val = $next++;
        if ($val > $max_so_far) {
            $milestones[] = [
                'weekid' => $g['year'] . str_pad($g['week'], 2, '0', STR_PAD_LEFT),
                'streak' => $val,
            ];
            $max_so_far = $val;
        }
        $prev_year = $g['year'];
        $prev_week = $g['week'];
    }

    return $milestones;
}

// Builds league-record streak progression: for each record-holder player, emits at the
// weekid of their career-high game showing their career-high streak value.
// Cached in 'pfl_streak_records_v1' transient.
function pfl_build_streak_record_events() {
    global $wpdb;

    $pids = $wpdb->get_col("SELECT pid FROM wp_allleaders ORDER BY gamestreak DESC");

    // Gather all personal-high moments for all players, keyed by pid
    $all = [];
    $player_milestones = [];
    foreach ($pids as $pid) {
        $milestones = pfl_player_streak_milestones($pid);
        $player_milestones[$pid] = $milestones;
        foreach ($milestones as $m) {
            $all[] = ['weekid' => $m['weekid'], 'pid' => $pid, 'streak' => $m['streak']];
        }
    }

    // Sort chronologically (weekid is zero-padded so string sort works)
    usort($all, fn($a, $b) => strcmp($a['weekid'], $b['weekid']));

    // Pass 1: identify which players held the league record (threshold >= 35, year >= 1992)
    $record_holders = [];
    $league_high = 0;
    $record_holder = null;
    foreach ($all as $m) {
        if ((int)substr($m['weekid'], 0, 4) < 1992) continue;
        if ($m['streak'] > $league_high) {
            $league_high = $m['streak'];
            if ($m['pid'] !== $record_holder && $m['streak'] >= 35) {
                $record_holder = $m['pid'];
                if (!in_array($m['pid'], $record_holders)) {
                    $record_holders[] = $m['pid'];
                }
            }
        }
    }

    // Pass 2: for each record holder, emit at their career-high weekid with career-high value
    $events = [];
    foreach ($record_holders as $pid) {
        $milestones = $player_milestones[$pid] ?? [];
        if (empty($milestones)) continue;
        $peak = end($milestones); // last milestone = career high
        if ((int)substr($peak['weekid'], 0, 4) < 1992) continue;
        $events[$peak['weekid']][] = ['pid' => $pid, 'streak' => $peak['streak']];
    }

    return $events;
}
function pfl_debug_pm_leaders() {
    global $wpdb;

    // Check via SHOW TABLES LIKE pattern
    $show_brees   = $wpdb->get_col("SHOW TABLES LIKE '%BreeQB%'");
    $show_allen   = $wpdb->get_col("SHOW TABLES LIKE '%AlleQB%'");
    $show_garcia  = $wpdb->get_col("SHOW TABLES LIKE '%GarcQB%'");

    // Try direct queries with error suppression
    $wpdb->suppress_errors(true);
    $direct = [];
    foreach (['2004BreeQB', '2018AlleQB', '2000GarcQB', '1991HostQB'] as $pid) {
        $rows = $wpdb->get_results("SELECT team, SUM(points) as pts FROM `{$pid}` WHERE team != '' GROUP BY team ORDER BY pts DESC LIMIT 3", ARRAY_A);
        $direct[$pid] = ['rows' => $rows, 'error' => $wpdb->last_error];
    }
    $wpdb->suppress_errors(false);

    // Total SHOW TABLES count
    $total_all = count($wpdb->get_col("SHOW TABLES"));

    return rest_ensure_response([
        'show_brees'  => $show_brees,
        'show_allen'  => $show_allen,
        'show_garcia' => $show_garcia,
        'direct'      => $direct,
        'total_tables_show' => $total_all,
    ]);
}

function pfl_get_timeline_events() {
    $cached = get_transient('pfl_timeline_v1');
    if (!empty($cached)) return rest_ensure_response($cached);

    $weeks   = the_weeks_with_post();
    $teams   = get_teams();

    // Custom notes from ACF repeater on the timeline page
    $notes = [];
    $tl_page = get_page_by_path('timeline');
    if ($tl_page) {
        $custom = get_field('custom_timeline', $tl_page->ID);
        if (is_array($custom)) {
            foreach ($custom as $n) $notes[$n['week_id']][] = $n['note'];
        }
    }

    // Champions keyed by weekid (year . '16')
    $champ_by_week = [];
    foreach (get_champions() as $year => $c) $champ_by_week[$year . '16'] = $c;

    // Pro Bowl keyed by weekid (year . '17')
    $pro_by_week = [];
    foreach (probowl_details() as $year => $pro) {
        $e = intval($pro['egad']); $d = intval($pro['dgas']);
        $pro_by_week[$year . '17'] = [
            'score'  => $e > $d ? "EGAD $e – DGAS $d" : "DGAS $d – EGAD $e",
            'winner' => $pro['winner'],
        ];
    }

    // Awards split by week
    $roty = $ooty = $mvp = $pbmvp = $promvp = $hall = [];
    foreach (get_awards() as $year => $val) {
        if (!empty($val['roty'  . $year]['pid']))   $roty  [$year . '14'] = $val['roty'  . $year]['pid'];
        if (!empty($val['ooty'  . $year]['owner'])) $ooty  [$year . '15'] = $val['ooty'  . $year]['owner'];
        if (!empty($val['mvp'   . $year]['pid']))   $mvp   [$year . '14'] = $val['mvp'   . $year]['pid'];
        if (!empty($val['pbm'   . $year]['pid']))   $pbmvp [$year . '16'] = $val['pbm'   . $year]['pid'];
        if (!empty($val['pro'   . $year]['pid']))   $promvp[$year . '17'] = $val['pro'   . $year]['pid'];
        elseif (!empty($val['promvp' . $year]['pid'])) $promvp[$year . '17'] = $val['promvp' . $year]['pid'];
        if (!empty($val['hall'  . $year]['pid']))   $hall  [$year . '17'] = $val['hall'  . $year]['pid'];
    }

    $bswoty = get_bswins();
    $grandslams = get_grandslams();
    $gs_by_week = [];
    foreach ($grandslams as $gs) $gs_by_week[$gs['weekid']][] = $gs['teamid'];

    // Player career milestones (reuse or rebuild transients)
    $ppc = get_transient('player_pts_milestone');
    $pwc = get_transient('player_win_milestone');
    if (empty($ppc)) {
        $ppc = [];
        foreach (get_players_assoc() as $pid => $v) {
            $d = get_player_career_stats($pid);
            if (!empty($d['pointsmilestone'])) $ppc[$pid] = $d['pointsmilestone'];
        }
        set_transient('player_pts_milestone', $ppc, WEEK_IN_SECONDS);
    }
    if (empty($pwc)) {
        $pwc = [];
        foreach (get_players_assoc() as $pid => $v) {
            $d = get_player_career_stats($pid);
            if (!empty($d['winmilestone'])) $pwc[$pid] = $d['winmilestone'];
        }
        set_transient('player_win_milestone', $pwc, WEEK_IN_SECONDS);
    }

    // Player week highs + team game data (reuse or rebuild transients)
    $playerpts        = get_transient('player_high_weeks');
    $pid_week_team    = get_transient('player_high_weeks_team');
    $team_week_pts    = get_transient('team_week_pts');
    $team_week_margins = get_transient('team_week_margins');
    if (empty($playerpts) || empty($pid_week_team) || empty($team_week_pts) || empty($team_week_margins)) {
        $playerpts = $pid_week_team = $team_week_pts = $team_week_margins = [];
        foreach (the_weeks() as $week) {
            $box = get_boxscore_by_week($week);
            if ($box) {
                foreach ($box as $team_int => $game) {
                    $playerpts[$week][] = [
                        $game['qb1']['pid'] => $game['qb1']['points'],
                        $game['rb1']['pid'] => $game['rb1']['points'],
                        $game['wr1']['pid'] => $game['wr1']['points'],
                        $game['pk1']['pid'] => $game['pk1']['points'],
                        $game['qb2']['pid'] => $game['qb2']['points'],
                        $game['rb2']['pid'] => $game['rb2']['points'],
                        $game['wr2']['pid'] => $game['wr2']['points'],
                        $game['pk2']['pid'] => $game['pk2']['points'],
                    ];
                    foreach (['qb1','rb1','wr1','pk1','qb2','rb2','wr2','pk2'] as $slot) {
                        $p = $game[$slot]['pid'] ?? '';
                        if ($p) $pid_week_team[$week][$p] = $team_int;
                    }
                    if (isset($game['points'])) {
                        $team_week_pts[$week][$team_int] = floatval($game['points']);
                        $team_week_margins[$week][$team_int] = [
                            'pts'    => floatval($game['points']),
                            'pts_vs' => floatval($game['versus_pts']),
                            'versus' => $game['versus'],
                        ];
                    }
                }
            }
        }
        set_transient('player_high_weeks',      $playerpts,         WEEK_IN_SECONDS);
        set_transient('player_high_weeks_team', $pid_week_team,     WEEK_IN_SECONDS);
        set_transient('team_week_pts',          $team_week_pts,     WEEK_IN_SECONDS);
        set_transient('team_week_margins',      $team_week_margins, WEEK_IN_SECONDS);
    }

    // Pre-compute player season totals (regular season only) for position season highs
    $player_season_totals = []; // [year][pid] => pts
    foreach ($playerpts as $weekid => $games) {
        $yr = intval(substr($weekid, 0, 4));
        $wk = intval(substr($weekid, 4, 2));
        if ($wk > 14) continue;
        foreach ($games as $game_arr) {
            foreach ($game_arr as $pid => $pts) {
                if (!$pid || floatval($pts) <= 0) continue;
                $player_season_totals[$yr][$pid] = ($player_season_totals[$yr][$pid] ?? 0) + floatval($pts);
            }
        }
    }

    // Reduce to per-week max per position
    $maxqb = $maxrb = $maxwr = $maxpk = [];
    foreach ($playerpts as $week => $games) {
        $theqb = $therb = $thewr = $thepk = [];
        foreach ($games as $game) {
            foreach ($game as $pid => $pts) {
                if (!$pid) continue;
                $pos = substr($pid, -2);
                if ($pos === 'QB') $theqb[$pid] = max($theqb[$pid] ?? 0, floatval($pts));
                elseif ($pos === 'RB') $therb[$pid] = max($therb[$pid] ?? 0, floatval($pts));
                elseif ($pos === 'WR') $thewr[$pid] = max($thewr[$pid] ?? 0, floatval($pts));
                elseif ($pos === 'PK') $thepk[$pid] = max($thepk[$pid] ?? 0, floatval($pts));
            }
        }
        if ($theqb) { $m = max($theqb); $maxqb[$week] = [array_search($m, $theqb) => $m]; }
        if ($therb) { $m = max($therb); $maxrb[$week] = [array_search($m, $therb) => $m]; }
        if ($thewr) { $m = max($thewr); $maxwr[$week] = [array_search($m, $thewr) => $m]; }
        if ($thepk) { $m = max($thepk); $maxpk[$week] = [array_search($m, $thepk) => $m]; }
    }

    // Postseason records
    $post_scores = []; $post_player = [];
    foreach (get_postseason() as $game) {
        $wid = $game['year'] . str_pad($game['week'], 2, '0', STR_PAD_LEFT);
        $tp = get_playoff_points_by_team_year($game['year'], $game['team'], $game['week']);
        if ($tp > 0 && !isset($post_scores[$wid][$game['team']])) $post_scores[$wid][$game['team']] = $tp;
        $post_player[$wid][] = ['pid' => $game['playerid'], 'score' => floatval($game['score'])];
    }

    // Consecutive games streak records (pre-computed, cached separately due to cost)
    $streak_record_events = get_transient('pfl_streak_records_v1');
    if (empty($streak_record_events)) {
        $streak_record_events = pfl_build_streak_record_events();
        set_transient('pfl_streak_records_v1', $streak_record_events, WEEK_IN_SECONDS);
    }

    // Stateful running records
    $q = $r = $in = $k = 0;
    $qbw = $rbw = $wrw = $pkw = 0;
    $post_high = 0; $post_player_high = 0;
    $league_score_high = 0;
    $blowout_high      = 0;
    $pos_season_high   = ['qb' => 0, 'rb' => 0, 'wr' => 0, 'pk' => 0];

    $result_weeks = [];

    foreach ($weeks as $week) {
        $year    = intval(substr($week, 0, 4));
        $weeknum = intval(substr($week, 4, 2));
        $evts    = [];

        // Custom notes
        if (!empty($notes[$week])) {
            foreach ($notes[$week] as $note) $evts[] = ['type' => 'note', 'label' => $note];
        }

        // Posse Bowl champion
        if (!empty($champ_by_week[$week])) {
            $c          = $champ_by_week[$week];
            $helm_num   = pfl_get_helmet_num($c['winner'], $year);
            $theme_uri  = get_stylesheet_directory_uri();
            $helmet_url = $theme_uri . '/img/helmets/final-renders/' . $c['winner']
                        . '/helmet-' . $c['winner'] . '-' . $helm_num . '-front.png';
            $evts[] = [
                'type'       => 'championship',
                'label'      => 'Posse Bowl ' . $c['numeral'],
                'winner'     => $c['winner'],
                'winnerName' => $teams[$c['winner']]['team'] ?? $c['winner'],
                'loser'      => $c['loser'],
                'loserName'  => $teams[$c['loser']]['team'] ?? $c['loser'],
                'score'      => $c['winner'] . ' ' . $c['win_pts'] . ' – ' . $c['loser'] . ' ' . $c['lose_pts'],
                'location'   => $c['location'],
                'numeral'    => $c['numeral'],
                'helmetUrl'  => $helmet_url,
            ];
        }

        // Awards (all keyed to their week)
        if (!empty($pbmvp[$week]))  $evts[] = ['type' => 'award', 'subtype' => 'pbmvp',  'label' => 'PB MVP',            'detail' => pid_to_name($pbmvp[$week],  0), 'pid' => $pbmvp[$week]];
        if (!empty($hall[$week]))   $evts[] = ['type' => 'award', 'subtype' => 'hof',    'label' => 'Hall of Fame',       'detail' => pid_to_name($hall[$week],   0), 'pid' => $hall[$week]];
        if (!empty($ooty[$week]))   $evts[] = ['type' => 'award', 'subtype' => 'ooty',   'label' => 'Owner of the Year',  'detail' => $ooty[$week]];
        if (!empty($pro_by_week[$week])) $evts[] = ['type' => 'probowl', 'label' => 'Pro Bowl', 'detail' => $pro_by_week[$week]['score']];
        if (!empty($promvp[$week])) $evts[] = ['type' => 'award', 'subtype' => 'promvp', 'label' => 'Pro Bowl MVP',       'detail' => pid_to_name($promvp[$week], 0), 'pid' => $promvp[$week]];
        if (!empty($mvp[$week]))    $evts[] = ['type' => 'award', 'subtype' => 'mvp',    'label' => 'Season MVP',         'detail' => pid_to_name($mvp[$week],    0), 'pid' => $mvp[$week]];
        if (!empty($roty[$week]))   $evts[] = ['type' => 'award', 'subtype' => 'roty',   'label' => 'Rookie of the Year', 'detail' => pid_to_name($roty[$week],   0), 'pid' => $roty[$week]];

        // BS WIN
        if (!empty($bswoty[$week])) $evts[] = ['type' => 'team', 'subtype' => 'bswin', 'label' => 'BS Win of the Year', 'detail' => $teams[$bswoty[$week]['winner']]['team'] ?? $bswoty[$week]['winner']];

        // Position week highs (running records)
        if ($week !== '199101') {
            foreach ([
                'qb' => [&$maxqb, &$q, &$qbw],
                'rb' => [&$maxrb, &$r, &$rbw],
                'wr' => [&$maxwr, &$in, &$wrw],
                'pk' => [&$maxpk, &$k, &$pkw],
            ] as $pos => [$map, &$high, &$wks]) {
                if (!empty($map[$week])) {
                    foreach ($map[$week] as $pid => $pts) {
                        if ($high <= $pts) {
                            $team_abbr = $pid_week_team[$week][$pid] ?? '';
                            $evts[] = ['type' => 'record', 'subtype' => $pos, 'label' => strtoupper($pos) . ' Week High', 'detail' => pid_to_name($pid, 0) . ($team_abbr ? ' (' . $team_abbr . ')' : '') . ' · ' . $pts . ' pts', 'tooltip' => 'New record after ' . $wks . ' weeks', 'pid' => $pid, 'pts' => floatval($pts)];
                            $high = $pts; $wks = 0;
                        }
                        $wks++;
                    }
                }
            }
        }

        // League single-game score record
        if (!empty($team_week_pts[$week])) {
            $week_max_pts  = max($team_week_pts[$week]);
            $week_max_team = array_search($week_max_pts, $team_week_pts[$week]);
            if ($week_max_pts > $league_score_high) {
                $league_score_high = $week_max_pts;
                $helm_num   = pfl_get_helmet_num($week_max_team, $year);
                $theme_uri  = get_stylesheet_directory_uri();
                $helmet_url = $theme_uri . '/img/helmets/final-renders/' . $week_max_team
                            . '/helmet-' . $week_max_team . '-' . $helm_num . '-front.png';
                $evts[] = [
                    'type'      => 'record',
                    'subtype'   => 'teamhigh',
                    'label'     => 'League Score Record',
                    'detail'    => ($teams[$week_max_team]['team'] ?? $week_max_team) . ' · ' . $week_max_pts . ' pts',
                    'team'      => $week_max_team,
                    'pts'       => $week_max_pts,
                    'helmetUrl' => $helmet_url,
                ];
            }
        }

        // Blowout record (largest single-game margin)
        if (!empty($team_week_margins[$week])) {
            $week_max_margin = 0; $blow_winner = $blow_loser = null; $blow_w_pts = $blow_l_pts = 0;
            foreach ($team_week_margins[$week] as $team_int => $g) {
                $margin = $g['pts'] - $g['pts_vs'];
                if ($margin > $week_max_margin) {
                    $week_max_margin = $margin;
                    $blow_winner = $team_int; $blow_loser = $g['versus'];
                    $blow_w_pts  = $g['pts']; $blow_l_pts  = $g['pts_vs'];
                }
            }
            if ($week_max_margin > $blowout_high) {
                $blowout_high = $week_max_margin;
                $helm_num   = pfl_get_helmet_num($blow_winner, $year);
                $theme_uri  = get_stylesheet_directory_uri();
                $helmet_url = $theme_uri . '/img/helmets/final-renders/' . $blow_winner
                            . '/helmet-' . $blow_winner . '-' . $helm_num . '-front.png';
                $evts[] = [
                    'type'      => 'record',
                    'subtype'   => 'blowout',
                    'label'     => 'Blowout Record',
                    'detail'    => ($teams[$blow_winner]['team'] ?? $blow_winner) . ' def. ' . ($teams[$blow_loser]['team'] ?? $blow_loser) . ' · +' . $week_max_margin . ' pts',
                    'team'      => $blow_winner,
                    'pts'       => $week_max_margin,
                    'helmetUrl' => $helmet_url,
                ];
            }
        }

        // Position season highs — emit at end of regular season (week 14)
        if ($weeknum === 14) {
            foreach (['qb' => 'QB', 'rb' => 'RB', 'wr' => 'WR', 'pk' => 'PK'] as $pos => $POS) {
                $best_pid = null; $best_pts = 0;
                foreach (($player_season_totals[$year] ?? []) as $pid => $total) {
                    if (strtoupper(substr($pid, -2)) !== $POS) continue;
                    if ($total > $best_pts) { $best_pts = $total; $best_pid = $pid; }
                }
                if ($best_pid && $best_pts > $pos_season_high[$pos]) {
                    $pos_season_high[$pos] = $best_pts;
                    $team_abbr = $pid_week_team[$week][$best_pid] ?? '';
                    $evts[] = [
                        'type'    => 'record',
                        'subtype' => 'season_' . $pos,
                        'label'   => $POS . ' Season High',
                        'detail'  => pid_to_name($best_pid, 0) . ($team_abbr ? ' (' . $team_abbr . ')' : '') . ' · ' . $best_pts . ' pts',
                        'pid'     => $best_pid,
                    ];
                }
            }
        }

        // Consecutive games streak records
        if (!empty($streak_record_events[$week])) {
            foreach ($streak_record_events[$week] as $se) {
                $evts[] = [
                    'type'   => 'record',
                    'subtype'=> 'streak',
                    'label'  => 'Consecutive Games Record',
                    'detail' => pid_to_name($se['pid'], 0) . ' · ' . $se['streak'] . ' straight games',
                    'pid'    => $se['pid'],
                ];
            }
        }

        // Grand Slams
        if (!empty($gs_by_week[$week])) {
            foreach ($gs_by_week[$week] as $tid) $evts[] = ['type' => 'team', 'subtype' => 'grandslam', 'label' => 'Grand Slam', 'detail' => $teams[$tid]['team'] ?? $tid];
        }

        // Postseason team record
        if ($year >= 1992 && ($weeknum === 15 || $weeknum === 16) && !empty($post_scores[$week])) {
            foreach ($post_scores[$week] as $tid => $score) {
                if ($post_high <= $score) {
                    $wtype = $weeknum === 15 ? 'Semifinal' : 'Posse Bowl';
                    $evts[] = ['type' => 'postseason', 'label' => 'Postseason Team Record', 'detail' => ($teams[$tid]['team'] ?? $tid) . ' · ' . $score . ' pts (' . $wtype . ')'];
                    $post_high = $score;
                }
            }
        }

        // Postseason player record
        if ($year >= 1992 && ($weeknum === 15 || $weeknum === 16) && !empty($post_player[$week])) {
            foreach ($post_player[$week] as $pd) {
                if ($post_player_high <= $pd['score']) {
                    $wtype = $weeknum === 15 ? 'Semifinal' : 'Posse Bowl';
                    $evts[] = ['type' => 'postseason', 'label' => 'Postseason Player Record', 'detail' => pid_to_name($pd['pid'], 0) . ' · ' . $pd['score'] . ' pts (' . $wtype . ')'];
                    $post_player_high = $pd['score'];
                }
            }
        }

        // Career point milestones
        foreach ($ppc as $pid => $ms) {
            foreach ($ms as $w => $pts) {
                if ($week == $w) $evts[] = ['type' => 'milestone', 'subtype' => 'pts', 'label' => number_format($pts) . ' Career Pts', 'detail' => pid_to_name($pid, 0), 'pid' => $pid];
            }
        }

        // Career win milestones
        foreach ($pwc as $pid => $ms) {
            foreach ($ms as $w => $wins) {
                if ($week == $w) $evts[] = ['type' => 'milestone', 'subtype' => 'wins', 'label' => $wins . ' Career Wins', 'detail' => pid_to_name($pid, 0), 'pid' => $pid];
            }
        }

        // Attach img URLs for any event that carries a pid
        foreach ($evts as &$evt) {
            if (!empty($evt['pid'])) $evt['img'] = pfl_player_img_url($evt['pid']);
        }
        unset($evt);

        if (!empty($evts)) {
            $result_weeks[] = ['weekid' => $week, 'year' => $year, 'weeknum' => $weeknum, 'events' => $evts];
        }
    }

    $out = ['weeks' => $result_weeks];
    set_transient('pfl_timeline_v1', $out, DAY_IN_SECONDS);
    return rest_ensure_response($out);
}

// ── Team player seasons ────────────────────────────────────────────────────────
// Returns per-player, per-season game counts for a given PFL franchise.
// Used by the /eras Sankey visualization.

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/team-player-seasons', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_team_player_seasons',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_team_player_seasons(WP_REST_Request $request) {
    global $wpdb;

    $team = strtoupper(sanitize_text_field($request->get_param('id')));
    if (!$team) return new WP_Error('missing_id', 'Missing id', ['status' => 400]);

    $table = 'wp_team_' . esc_sql($team);
    if (!$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table))) {
        return new WP_Error('invalid_team', 'Team not found', ['status' => 404]);
    }

    // Regular season only (weeks 1–14); count roster appearances per player per year.
    $rows = $wpdb->get_results(
        "SELECT season, week, qb1, qb2, rb1, rb2, wr1, wr2, pk1, pk2
         FROM {$table}
         WHERE week BETWEEN 1 AND 14
         ORDER BY season, week",
        ARRAY_A
    );

    $slot_pos = [
        'qb1' => 'QB', 'qb2' => 'QB',
        'rb1' => 'RB', 'rb2' => 'RB',
        'wr1' => 'WR', 'wr2' => 'WR',
        'pk1' => 'PK', 'pk2' => 'PK',
    ];

    $player_data  = []; // [pid] => ['seasons' => [year => count]]
    $player_slots = []; // [pid] => ['QB' => n, 'RB' => n, 'WR' => n, 'PK' => n]

    foreach ($rows as $row) {
        $year      = (int) $row['season'];
        $seen_week = [];
        foreach ($slot_pos as $slot => $pos) {
            $pid = trim($row[$slot] ?? '');
            if (empty($pid) || isset($seen_week[$pid])) continue;
            $seen_week[$pid] = true;
            if (!isset($player_data[$pid])) {
                $player_data[$pid]  = ['seasons' => []];
                $player_slots[$pid] = ['QB' => 0, 'RB' => 0, 'WR' => 0, 'PK' => 0];
            }
            $player_slots[$pid][$pos]++;
            $player_data[$pid]['seasons'][$year] = ($player_data[$pid]['seasons'][$year] ?? 0) + 1;
        }
    }

    // Assign position by most-used slot type so a single bad data entry doesn't misclassify a player.
    foreach ($player_data as $pid => &$data) {
        arsort($player_slots[$pid]);
        $data['pos'] = array_key_first($player_slots[$pid]);
    }
    unset($data);

    // Championship years for this team
    $champ_rows    = $wpdb->get_results(
        $wpdb->prepare("SELECT year FROM wp_champions WHERE winTeam = %s ORDER BY year ASC", $team),
        ARRAY_A
    );
    $championships = array_map(fn($r) => (int) $r['year'], $champ_rows);

    if (empty($player_data)) return rest_ensure_response(['championships' => $championships, 'players' => []]);

    // Fetch player names from wp_players.
    $pids         = array_keys($player_data);
    $placeholders = implode(',', array_fill(0, count($pids), '%s'));
    $name_rows    = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p_id, playerFirst, playerLast FROM wp_players WHERE p_id IN ({$placeholders})",
            ...$pids
        ),
        ARRAY_A
    );

    $names = [];
    foreach ($name_rows as $r) {
        $names[$r['p_id']] = trim($r['playerFirst'] . ' ' . $r['playerLast']);
    }

    // Fetch points per player per season from wp_season_leaders
    $points_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT playerid, season, SUM(points) as points FROM wp_season_leaders WHERE playerid IN ({$placeholders}) GROUP BY playerid, season",
            ...$pids
        ),
        ARRAY_A
    );
    $points_map = [];
    foreach ($points_rows as $r) {
        $points_map[$r['playerid']][(int) $r['season']] = round((float) $r['points'], 1);
    }

    $result = [];
    foreach ($player_data as $pid => $data) {
        $seasons = [];
        foreach ($data['seasons'] as $year => $games) {
            $seasons[] = [
                'year'   => $year,
                'games'  => $games,
                'points' => $points_map[$pid][$year] ?? 0,
            ];
        }
        usort($seasons, fn($a, $b) => $a['year'] - $b['year']);
        $result[] = [
            'pid'     => $pid,
            'name'    => $names[$pid] ?? $pid,
            'pos'     => $data['pos'],
            'img'     => pfl_player_img_url($pid),
            'seasons' => $seasons,
        ];
    }

    return rest_ensure_response(['championships' => $championships, 'players' => $result]);
}

// ── Eras: Player career across all teams ─────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/player-career-teams', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_player_career_teams',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_player_career_teams(WP_REST_Request $request) {
    global $wpdb;

    $pid = sanitize_text_field($request->get_param('pid'));
    if (!$pid) return new WP_Error('missing_pid', 'Missing pid', ['status' => 400]);

    // team_int = abbreviation (e.g. 'ATK'), team = full name (e.g. 'Melmac Attack')
    $name_rows  = $wpdb->get_results("SELECT team_int, team FROM wp_teams", ARRAY_A);
    $name_map   = [];
    foreach ($name_rows as $r) $name_map[$r['team_int']] = $r['team'];

    $all_abbrs  = $wpdb->get_col("SELECT DISTINCT team FROM wp_drafts ORDER BY team");
    $teams_list = [];
    foreach ($all_abbrs as $abbr) $teams_list[] = ['team' => $abbr, 'name' => $name_map[$abbr] ?? $abbr];

    // Season points for this player
    $pts_rows  = $wpdb->get_results(
        $wpdb->prepare("SELECT season, SUM(points) as pts FROM wp_season_leaders WHERE playerid = %s GROUP BY season", $pid),
        ARRAY_A
    );
    $season_pts = [];
    foreach ($pts_rows as $r) $season_pts[(int)$r['season']] = (float)$r['pts'];

    // For each team table, find weeks where this player appeared
    $team_seasons = []; // [team] => [year => games]
    foreach ($teams_list as $t) {
        $tbl = 'wp_team_' . esc_sql($t['team']);
        if (!$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tbl))) continue;

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT season, COUNT(DISTINCT week) AS games FROM {$tbl}
             WHERE week BETWEEN 1 AND 14
             AND (qb1=%s OR qb2=%s OR rb1=%s OR rb2=%s OR wr1=%s OR wr2=%s OR pk1=%s OR pk2=%s)
             GROUP BY season ORDER BY season",
            $pid,$pid,$pid,$pid,$pid,$pid,$pid,$pid
        ), ARRAY_A);

        if (empty($rows)) continue;
        foreach ($rows as $r) {
            $team_seasons[$t['team']]['name']                        = $t['name'];
            $team_seasons[$t['team']]['seasons'][(int)$r['season']] = (int)$r['games'];
        }
    }

    if (empty($team_seasons)) return rest_ensure_response(null);

    // Total games per season across all teams (for proportional points allocation)
    $total_games = [];
    foreach ($team_seasons as $data) {
        foreach ($data['seasons'] as $year => $g) {
            $total_games[$year] = ($total_games[$year] ?? 0) + $g;
        }
    }

    $result_teams = [];
    foreach ($team_seasons as $team => $data) {
        $seasons = [];
        foreach ($data['seasons'] as $year => $games) {
            $pts = $season_pts[$year] ?? 0;
            $allocated = ($total_games[$year] ?? 1) > 0
                ? round($pts * $games / $total_games[$year], 1)
                : 0;
            $seasons[] = ['year' => $year, 'games' => $games, 'points' => $allocated];
        }
        usort($seasons, fn($a, $b) => $a['year'] - $b['year']);
        $result_teams[] = ['team' => $team, 'name' => $data['name'], 'seasons' => $seasons];
    }
    usort($result_teams, fn($a, $b) => $a['seasons'][0]['year'] - $b['seasons'][0]['year']);

    $player = $wpdb->get_row($wpdb->prepare(
        "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s", $pid
    ), ARRAY_A);

    return rest_ensure_response([
        'pid'   => $pid,
        'name'  => $player ? trim($player['playerFirst'] . ' ' . $player['playerLast']) : $pid,
        'img'   => pfl_player_img_url($pid),
        'teams' => $result_teams,
    ]);
}

// ── Homepage: Summary + Roster News ──────────────────────────────────────────

add_action('rest_api_init', function () {
    register_rest_route('pfl/v1', '/home-summary', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_home_summary',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('pfl/v1', '/roster-news', [
        'methods'             => 'GET',
        'callback'            => 'pfl_api_roster_news',
        'permission_callback' => '__return_true',
    ]);
});

function pfl_api_home_summary() {
    global $wpdb;

    $cached = get_transient('pfl_home_summary_v1');
    if ($cached !== false) return rest_ensure_response($cached);

    $stats = $wpdb->get_row(
        "SELECT COUNT(DISTINCT season)  AS seasons,
                COUNT(DISTINCT playerid) AS uniquePlayers,
                ROUND(SUM(points))       AS totalPoints,
                SUM(games)               AS totalGames
         FROM wp_season_leaders",
        ARRAY_A
    );

    $champ = $wpdb->get_row(
        "SELECT c.year, c.winTeam AS team, t.team AS teamName
         FROM wp_champions c
         LEFT JOIN wp_teams t ON t.team_int = c.winTeam
         ORDER BY c.year DESC LIMIT 1",
        ARRAY_A
    );

    $data = [
        'seasons'       => (int)   ($stats['seasons']       ?? 0),
        'uniquePlayers' => (int)   ($stats['uniquePlayers']  ?? 0),
        'totalPoints'   => (int)   ($stats['totalPoints']    ?? 0),
        'totalGames'    => (int)   ($stats['totalGames']     ?? 0),
        'firstYear'     => 1991,
        'champion'      => $champ ? [
            'year'     => (int) $champ['year'],
            'team'     => $champ['team'],
            'teamName' => $champ['teamName'],
        ] : null,
    ];

    set_transient('pfl_home_summary_v1', $data, 3600);
    return rest_ensure_response($data);
}

function pfl_api_roster_news() {
    global $wpdb;

    $cache_key = 'pfl_roster_news_v6';
    $cached    = get_transient($cache_key);
    if ($cached !== false) return rest_ensure_response($cached);

    // NFL seasons run Sep–Feb, so Jan–Aug of year N still belong to season N-1
    $mfl_year = (int) date('m') < 9 ? (int) date('Y') - 1 : (int) date('Y');
    $mfl_lid  = 38954;
    $mfl_key  = 'aRNp1sySvuWqx1KmO1HIZDYeF7ox';

    // ── Live MFL rosters (cached separately, 15 min) ───────────────────────
    // mflid → { team: PFL abbr, teamName: full name }
    $live = get_transient('pfl_mfl_live_rosters_v2');
    if ($live === false) {
        $live = [];

        $team_rows = $wpdb->get_results(
            "SELECT team_int, team, mfl_team_id FROM wp_teams WHERE mfl_team_id != ''",
            ARRAY_A
        );
        $fid_map = [];  // MFL franchise_id → { team, teamName }
        foreach ($team_rows as $t) {
            $fid_map[$t['mfl_team_id']] = ['team' => $t['team_int'], 'teamName' => $t['team']];
        }

        $roster_res = wp_remote_get(
            "https://www48.myfantasyleague.com/{$mfl_year}/export?TYPE=rosters&L={$mfl_lid}&APIKEY={$mfl_key}&FRANCHISE=&W=&JSON=1",
            ['timeout' => 8, 'sslverify' => false, 'redirection' => 5]
        );
        if (!is_wp_error($roster_res) && wp_remote_retrieve_response_code($roster_res) === 200) {
            $rdata = json_decode(wp_remote_retrieve_body($roster_res), true);
            foreach ($rdata['rosters']['franchise'] ?? [] as $franchise) {
                $fid      = $franchise['id'] ?? '';
                $team_inf = $fid_map[$fid] ?? null;
                if (!$team_inf) continue;
                $players = $franchise['player'] ?? [];
                if (!is_array($players)) $players = [$players];
                foreach ($players as $pl) {
                    if (!empty($pl['id'])) $live[trim($pl['id'])] = $team_inf;
                }
            }
        }

        set_transient('pfl_mfl_live_rosters_v2', $live, 900); // 15 min
    }

    if (empty($live)) {
        return rest_ensure_response(['news' => [], 'injuries' => [], 'rosterYear' => $mfl_year]);
    }

    // ── Player lookup by mflid ─────────────────────────────────────────────
    $mflids = array_keys($live);
    $ph     = implode(',', array_fill(0, count($mflids), '%s'));
    $player_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p_id AS pid, playerFirst AS first, playerLast AS last, mflid
             FROM wp_players WHERE mflid IN ($ph)",
            ...$mflids
        ),
        ARRAY_A
    );

    $name_map  = [];   // normalized name → entry
    $mflid_map = [];   // mflid           → entry
    foreach ($player_rows as $p) {
        $mid = trim($p['mflid']);
        if (!isset($live[$mid])) continue;
        $team_inf = $live[$mid];
        $entry    = [
            'pid'         => $p['pid'],
            'first'       => $p['first'],
            'last'        => $p['last'],
            'pflTeam'     => $team_inf['team'],
            'pflTeamName' => $team_inf['teamName'],
        ];
        $norm = strtolower(preg_replace('/[^a-z0-9 ]/i', '', $p['first'] . ' ' . $p['last']));
        $name_map[$norm] = $entry;
        $mflid_map[$mid] = $entry;
    }

    // ── ESPN news ──────────────────────────────────────────────────────────
    $news     = [];
    $espn_res = wp_remote_get(
        'https://site.api.espn.com/apis/site/v2/sports/football/nfl/news?limit=100',
        ['timeout' => 8, 'sslverify' => false]
    );
    if (!is_wp_error($espn_res) && wp_remote_retrieve_response_code($espn_res) === 200) {
        $espn = json_decode(wp_remote_retrieve_body($espn_res), true);
        foreach ($espn['articles'] ?? [] as $a) {
            $matched = [];
            $seen    = [];
            foreach ($a['categories'] ?? [] as $cat) {
                if (($cat['type'] ?? '') !== 'athlete' || empty($cat['description'])) continue;
                $norm = strtolower(preg_replace('/[^a-z0-9 ]/i', '', $cat['description']));
                if (isset($name_map[$norm]) && !in_array($name_map[$norm]['pid'], $seen)) {
                    $matched[] = $name_map[$norm];
                    $seen[]    = $name_map[$norm]['pid'];
                }
            }
            if (empty($matched)) continue;
            $news[] = [
                'id'          => (string) ($a['id'] ?? uniqid()),
                'headline'    => $a['headline']    ?? '',
                'description' => $a['description'] ?? '',
                'published'   => $a['published']   ?? '',
                'link'        => $a['links']['web']['href'] ?? '',
                'players'     => $matched,
            ];
        }
    }

    // ── MFL injuries ───────────────────────────────────────────────────────
    $injuries = [];
    $mfl_res  = wp_remote_get(
        "https://api.myfantasyleague.com/{$mfl_year}/export?TYPE=injuries&JSON=1",
        ['timeout' => 8, 'sslverify' => false]
    );
    if (!is_wp_error($mfl_res) && wp_remote_retrieve_response_code($mfl_res) === 200) {
        $mfl = json_decode(wp_remote_retrieve_body($mfl_res), true);
        foreach ($mfl['injuries']['injury'] ?? [] as $inj) {
            $mid = trim($inj['id'] ?? '');
            if (!isset($mflid_map[$mid])) continue;
            $p          = $mflid_map[$mid];
            $injuries[] = [
                'pid'         => $p['pid'],
                'first'       => $p['first'],
                'last'        => $p['last'],
                'pflTeam'     => $p['pflTeam'],
                'pflTeamName' => $p['pflTeamName'],
                'status'      => $inj['status']     ?? '',
                'details'     => $inj['details']    ?? '',
                'expReturn'   => $inj['exp_return'] ?? '',
            ];
        }
        $priority = ['Out' => 0, 'IR' => 1, 'Doubtful' => 2, 'Questionable' => 3, 'Probable' => 4];
        usort($injuries, fn($a, $b) =>
            ($priority[$a['status']] ?? 9) - ($priority[$b['status']] ?? 9)
        );
    }

    // ── Helmet URLs for each rostered team ────────────────────────────────
    $theme_uri   = get_stylesheet_directory_uri();
    $helm_base   = $theme_uri . '/img/helmets/final-renders';
    $team_helmets = [];
    foreach (array_values($live) as $t) {
        $abbr = $t['team'];
        if (isset($team_helmets[$abbr])) continue;
        $num = pfl_get_helmet_num($abbr, $mfl_year);
        $team_helmets[$abbr] = "{$helm_base}/{$abbr}/helmet-{$abbr}-{$num}-front.png";
    }

    $result = ['news' => $news, 'injuries' => $injuries, 'rosterYear' => $mfl_year, 'teamHelmets' => $team_helmets];
    set_transient($cache_key, $result, 900); // 15 min — matches live roster freshness
    return rest_ensure_response($result);
}


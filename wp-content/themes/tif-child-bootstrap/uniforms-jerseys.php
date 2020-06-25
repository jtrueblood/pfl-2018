<?php
/*
 * Template Name: Uniforms Jerseys
 * Description: Template for Team Jerseys, Helmets
 */


/*
$url1=$_SERVER['REQUEST_URI'];
header("Refresh: 5; URL=$url1");
*/

$getplayer = $_GET['id'];

if($_GET['v']):
	$version = $_GET['v'];
else:
	$version = 'home';
endif;
 
$playersassoc = get_players_assoc();
$i = 0;
foreach ($playersassoc as $key => $value){
	$playersid[] = $key;
}

$randomize = array_rand($playersid);


$jusplayerids = just_player_ids();
$currentid = array_search($getplayer, $jusplayerids);
$nextplayer = $jusplayerids[$currentid + 1];

//$randomplayer = '2009AverWR';

if(isset($getplayer)){
	$randomplayer = $_GET['id'];
} else {
	$randomplayer = $playersid[$randomize];
}

$featuredplayer = $playersassoc[$randomplayer];


$yearsplayed = get_player_years_played($randomplayer);
//printr($yearsplayed, 1);

$stylesheet_uri = get_stylesheet_directory_uri();

$info = get_player_basic_info($randomplayer);
$player_number = $info[0]['number'];


$teamall = get_player_record($randomplayer);
//printr($teamall, 0);
if(isset($teamall)){
	$teams = array_unique($teamall);
	foreach ($teams as $printteams) { 
		$teamList .= $prefix . '' . $teamids[$printteams];
		$prefix = ', ';
	} 
}

if($teamall):
	foreach ($teamall as $key => $value){
		if($check != $value):
			$check = $value;
			$teamall_no_change[$key] = $check; 
		else:
			$teamall_no_change[$key] = '';
		endif;		
	}
endif;	

if($teamall_no_change):
	$team_switches = array_filter($teamall_no_change);
endif;	

get_header(); 
?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">

				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php the_title();?></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
				
				<!-- THE ROW -->
				<div class="row">
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">	
						
						<div class="panel panel-bordered panel-light">
							<div class="panel-heading">
								<h3 class="panel-title">Select A Player</h3>
							</div>
							<div class="panel-body">
							<div class="col-xs-24 col-sm-18">	
								<select data-placeholder="Select an Existing Player" class="chzn-select" style="width:100%;" tabindex="1" id="playerSelectUnisDrop">
								<option value=""></option>
								
								<?php 	
								foreach ($playersassoc as $key => $selectplayer){
									$firsto = $selectplayer[0];
									$lasto = $selectplayer[1];
									$printselect .= '<option value="/?id='.$key.'">'.$firsto.' '.$lasto.'</option>';
								}
								echo $printselect;
								?>
							</select>
							</div>
							<div class="col-xs-24 col-sm-4">
								<button class="btn btn-warning" id="playerSelectUnis">Select</button>
							</div>
						</div>
					
						</div>
					</div>		
					
					<div class="col-xs-24 col-sm-6 left-column">
						<div class="panel widget" >
							<div class="widget-body text-center">
					
								<?php 
									//printr($team_switches, 0);
									foreach($team_switches as $id => $team){
										$year = substr($id, 0, 4);
										$uni_hist[] = get_helmet_name_history_by_team($team, $year);
									}
									printr($uni_hist, 0);
								?>		
					
							</div>
						</div>
					</div>
						
					<!-- PLAYER SPOTLIGHT -->
					<div class="col-xs-24 col-sm-4 left-column">
						<div class="panel widget" >
							<div class="widget-body text-center">
								<?php 
								
									//echo '<h2>'.$player_number.'</h2>'; 
									//printr($yearsplayed, 0);
								foreach($uni_hist as $uni){	
									if($player_number > 9):
										$leftarm = $player_number[1];
										$rightarm = $player_number[0];
									else: 
										$leftarm = $player_number;
										$rightarm = $player_number;	
									endif;
									
									$team = $uni['team'];
									$helmet_num = $uni['helmet'];
								
									$helmeturl = $stylesheet_uri.'/img/helmets/weekly/'.$team.'-helm-right-'.$helmet_num.'.png';
									$jerseyurl = $stylesheet_uri.'/uniforms/'.$team.'_'.$version.'_'.$helmet_num.'.png';
									
									
									
									//echo '<img src="'.$stylesheet_uri.'/uniforms/WRZ_uni_test_l.png"/>';
								?>
								
								<div class="jersey" style="background-image:url(<?php echo $jerseyurl; ?>)" >
								<img class="helm-jersey" src="<?php echo $helmeturl;?>"/>	
								<?php echo '<div class="'.$team.'-'.$version.'-'.$helmet_num.'">'; ?>
										<h3 class="left-arm"><?php echo $leftarm; ?></h2>
										<h2><?php echo $player_number; ?></h2>
										<h3 class="right-arm"><?php echo $rightarm; ?></h2>
									</div>
								</div>
								
								<?php } ?>
								
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
						<div class="panel panel-bordered panel-light">
							<div class="panel-body">
								<?php while (have_posts()) : the_post(); ?>
								<p><?php the_content();?></p>
								<?php endwhile; wp_reset_query(); ?>
							</div>
						</div>
					</div>
				</div>
				</div>
			</div>
</div>


		
<?php get_footer(); ?>
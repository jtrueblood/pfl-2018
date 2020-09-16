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

<style>
@import url('https://fonts.googleapis.com/css2?family=Alfa+Slab+One&family=Black+Ops+One&family=Bungee&family=Bungee+Shade&family=Coda:wght@800&family=Exo+2:ital,wght@0,900;1,900&family=Montserrat:ital,wght@0,900;1,900&family=Neuton:wght@700&family=Open+Sans:ital,wght@0,200;1,200&family=Russo+One&family=Saira+Stencil+One&family=Staatliches&family=Teko:wght@700&family=Tienne:wght@900&family=Tomorrow:wght@700&Arvo:wght@700&family=Holtwood+One+SC&display=swap');

.number-samples {
	h2 {
		font-size: 50px;
	}
}


// jersey fonts
.alfa-slab-one {
	font-family: 'Alfa Slab One', cursive;
}
.black-ops {
	font-family: 'Black Ops One', cursive;
}
.bungee {
	font-family: 'Bungee', cursive;
}
.bungee-shade {
	font-family: 'Bungee Shade', cursive;
}
.coda {
	font-family: 'Coda', cursive;
}
.exo {
	font-family: 'Exo 2', sans-serif;
}
.montserrat {
	font-family: 'Montserrat', sans-serif;
}
.neuton {
	font-family: 'Neuton', serif;
}
.open-sans {
	font-family: 'Open Sans', sans-serif;
}
.teko {
	font-family: 'Teko', sans-serif;
}
.russo-one {
	font-family: 'Russo One', sans-serif;
}
.staatliches {
	font-family: 'Staatliches', cursive;
}
.saira {
	font-family: 'Saira Stencil One', cursive;
}
.tienne {
	font-family: 'Tienne', serif;
}
.tomorrow {
	font-family: 'Tomorrow', sans-serif;   // Most like SPORTS 
} 
.arvo {
	font-family: 'Arvo', serif;
}
.holtwood {
	font-family: 'Holtwood One SC', serif;
}

.sholder {
	h3.left-arm {
		position: absolute;
		top:-6px;
		left: 44px;
		transform: rotate(70deg);
	}
	h3.right-arm {
		position: absolute;
		top:-6px;
		right: 40px;
		transform: rotate(-70deg);
	}
}

.jersey {
	width: 150px;
	height: 150px;
	background-color: #ccc;
	background-position: center center;
	background-repeat: no-repeat;
	position: relative;
	display: block;
	border: none;
	h2 {
		font-family: 'Tomorrow', sans-serif; 
		font-size: 50px;
		-webkit-text-fill-color: #D61379; /* Will override color (regardless of order) */
		-webkit-text-stroke-width: 1px;
		-webkit-text-stroke-color: #110F42;
		text-align: center;
		display: inline-block;
		padding-top: 45px;
		margin: 0;
	}
	h3 {
		font-family: 'Tomorrow', sans-serif; 
		font-size: 20px;
		-webkit-text-fill-color: white; /* Will override color (regardless of order) */
		-webkit-text-stroke-width: 1px;
		-webkit-text-stroke-color: #110F42;	
		top:23px;
	}
	h3.left-arm {
		position: absolute;
		left: 29px;
		transform: rotate(15deg);
	}
	h3.right-arm {
		position: absolute;
		right: 27px;
		transform: rotate(-15deg);
	}
	
	// RBS JERSEYS
	.RBS-home-1, .RBS-road-1 {
		h2, h3 {
			font-family: 'Open Sans', sans-serif;
		}
		h2 {
			font-size: 50px;
			-webkit-text-fill-color: #CF5528; /* Will override color (regardless of order) */
			text-align: center;
			display: inline-block;
			padding-top: 40px;
			margin: 0;
			-webkit-text-stroke-width: 0.03em;	
		}
		h3 {
			font-size: 18px;
			-webkit-text-fill-color: #CF5528; /* Will override color (regardless of order) */
			-webkit-text-stroke-width: 0;
			top:25px;
		}
		h3.left-arm {
			position: absolute;
			left: 29px;
			transform: rotate(15deg);
		}
		h3.right-arm {
			position: absolute;
			right: 27px;
			transform: rotate(-15deg);
		}
	}
	.RBS-home-1 {
		h2, h3 {
			-webkit-text-fill-color: none;
			-webkit-text-fill-color: #fff;
			-webkit-text-stroke-color: #000	
		}
	}

	
	
	//main number
	.WRZ-road-3 {
		h2 {
			font-family: 'Teko', sans-serif;
			font-size: 50px;
			-webkit-text-fill-color: #D61379; /* Will override color (regardless of order) */
			-webkit-text-stroke-width: 1px;
			-webkit-text-stroke-color: #110F42;
			text-align: center;
			display: inline-block;
			padding-top: 45px;
			margin: 0;
		}
		h3 {
			font-family: 'Teko', sans-serif;
			font-size: 22px;
			-webkit-text-fill-color: white; /* Will override color (regardless of order) */
			-webkit-text-stroke-width: 1px;
			-webkit-text-stroke-color: #110F42;	
			top:23px;
		}
		h3.left-arm {
			position: absolute;
			left: 29px;
			transform: rotate(15deg);
		}
		h3.right-arm {
			position: absolute;
			right: 27px;
			transform: rotate(-15deg);
		}
	}
	
	// MAX JERSEYS
	.MAX-home-1, .MAX-road-1 {
		h2 {
			font-family: 'Staatliches', cursive;
			font-size: 55px;
			-webkit-text-fill-color: #1B2460; /* Will override color (regardless of order) */
			text-align: center;
			display: inline-block;
			padding-top: 40px;
			margin: 0;
			letter-spacing: 2px;
		}
		h3 {
			font-family: 'Staatliches', cursive;
			font-size: 22px;
			-webkit-text-fill-color: #1B2460; /* Will override color (regardless of order) */
			top:-7px;
		}
		h3.left-arm {
			position: absolute;
			left: 43px;
			transform: rotate(65deg);
		}
		h3.right-arm {
			position: absolute;
			right: 43px;
			transform: rotate(-65deg);
		}
	}
	.MAX-home-1 {
		h2, h3 {
			-webkit-text-fill-color: none;
			-webkit-text-stroke-width: 1px;
			-webkit-text-stroke-color: #fff;	
		}
	}
	
	// ATK JERSEYS
	.ATK-home-1, .ATK-road-1 {
		h2, h3 {
			font-family: 'Russo One', sans-serif;	
		}
		h2 {
			font-size: 50px;
			-webkit-text-fill-color: #CF5528; /* Will override color (regardless of order) */
			text-align: center;
			display: inline-block;
			padding-top: 40px;
			margin: 0;
			-webkit-text-stroke-width: 0.001em;
			-webkit-text-stroke-color: #fff;	
		}
		h3 {
			font-size: 18px;
			-webkit-text-fill-color: #CF5528; /* Will override color (regardless of order) */
			-webkit-text-stroke-width: 0;
			top:25px;
		}
		h3.left-arm {
			position: absolute;
			left: 29px;
			transform: rotate(15deg);
		}
		h3.right-arm {
			position: absolute;
			right: 27px;
			transform: rotate(-15deg);
		}
	}
	.ATK-home-1 {
		h2, h3 {
			-webkit-text-fill-color: none;
			-webkit-text-fill-color: #fff;
			-webkit-text-stroke-color: #CF5528;	
		}
	}
	// ETS JERSEYS 
	.ETS-home-1, .ETS-road-1 {
		
	}
	.ETS-home-2, .ETS-road-2 {
		
	}
	.ETS-home-3, .ETS-road-3 {
		
		h2 {
			-webkit-text-fill-color: #fff;
			padding-top: 40px;
			.coda();
		}
		h3 {
			top: 37px;
			.coda();
		}
		h3.right-arm {
			right: 25px;
		}
		h3.left-arm {
			left: 25px;
		}
	}
	.ETS-home-4, .ETS-road-4 {
		.sholder();
		h2 {
			-webkit-text-stroke-color: #fff;
			-webkit-text-stroke-color: none;	
		}
	}
	.ETS-home-5, .ETS-road-5 {
		
	}


	img.helm-jersey {
		width: 75px;
		position: absolute;
		bottom: -10px;
		left: -10px;
	}	
}

/*
font-family: 'Alfa Slab One', cursive;
font-family: 'Black Ops One', cursive;
font-family: 'Bungee', cursive;
font-family: 'Bungee Shade', cursive;
font-family: 'Coda', cursive;
font-family: 'Exo 2', sans-serif;
font-family: 'Montserrat', sans-serif;
font-family: 'Neuton', serif;
font-family: 'Open Sans', sans-serif;
font-family: 'Teko', sans-serif;
font-family: 'Russo One', sans-serif;
font-family: 'Staatliches', cursive;
font-family: 'Saira Stencil One', cursive;
font-family: 'Tienne', serif;
font-family: 'Tomorrow', sans-serif;   // Most like SPORTS 

*/

</style>


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
								
								<div class="number-samples">
									<h2 class="alfa-slab-one"><?php echo $player_number; ?></h2>
									<p>font-family: 'Alfa Slab One', cursive;</p>
								</div>
								<div class="number-samples">
									<h2 class="black-ops"><?php echo $player_number; ?></h2>
									<p>font-family: 'Black Ops One', cursive;</p>
								</div>
								<div class="number-samples">
									<h2 class="bungee"><?php echo $player_number; ?></h2>
									<p>font-family: 'Bungee', cursive;</p>
								</div>
									<div class="number-samples">
									<h2 class="bungee-shade"><?php echo $player_number; ?></h2>
									<p>font-family: 'Bungee Shade', cursive;</p>
								</div>
									<div class="number-samples">
									<h2 class="coda"><?php echo $player_number; ?></h2>
									<p>font-family: 'Coda', cursive;</p>
								</div>
									<div class="number-samples">
									<h2 class="exo"><?php echo $player_number; ?></h2>
									<p>font-family: 'Exo 2', sans-serif;</p>
								</div>
									<div class="number-samples">
									<h2 class="montserrat"><?php echo $player_number; ?></h2>
									<p>font-family: 'Montserrat', sans-serif;</p>
								</div>
									<div class="number-samples">
									<h2 class="neuton"><?php echo $player_number; ?></h2>
									<p>font-family: 'Neuton', serif;</p>
								</div>
									<div class="number-samples">
									<h2 class="open-sans"><?php echo $player_number; ?></h2>
									<p>font-family: 'Open Sans', sans-serif;</p>
								</div>
									<div class="number-samples">
									<h2 class="teko"><?php echo $player_number; ?></h2>
									<p>font-family: 'Teko', sans-serif;</p>
								</div>
									<div class="number-samples">
									<h2 class="russo-one"><?php echo $player_number; ?></h2>
									<p>font-family: 'Russo One', sans-serif;</p>
								</div>
									<div class="number-samples">
									<h2 class="staatliches"><?php echo $player_number; ?></h2>
									<p>font-family: 'Staatliches', cursive;</p>
								</div>
									<div class="number-samples">
									<h2 class="saira"><?php echo $player_number; ?></h2>
									<p>font-family: 'Saira Stencil One', cursive;</p>
								</div>
								<div class="number-samples">
									<h2 class="tienne"><?php echo $player_number; ?></h2>
									<p>font-family: 'Tienne', serif;</p>
								</div>
								<div class="number-samples">
									<h2 class="tomorrow"><?php echo $player_number; ?></h2>
									<p>font-family: 'Tomorrow', sans-serif; </p>
								</div>
								<div class="number-samples">
									<h2 class="holtwoood"><?php echo $player_number; ?></h2>
									<p>font-family: 'Holtwood One SC', serif; </p>
								</div>
								<div class="number-samples">
									<h2 class="arvo"><?php echo $player_number; ?></h2>
									<p>font-family: 'Arvo', serif; </p>
								</div>
								
								
								

								
							
						</div>
					</div>
				</div>
				</div>
			</div>
</div>


		
<?php get_footer(); ?>
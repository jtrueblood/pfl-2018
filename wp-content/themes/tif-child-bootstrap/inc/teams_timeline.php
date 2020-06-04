<?php
// all seasons that team played
//printr($stand, 0);

if(isset($teamawards)){
foreach($teamawards as $key => $item){
   $team_award_year[$item['year']][$key] = $item;
}

	ksort($team_award_year, SORT_NUMERIC);
}


$number_ones = get_number_ones();

if(isset($number_ones)){
	foreach($number_ones as $key => $item){
	   $newkey = substr($key, 2, -1); 
	   if($item['teams'] == $teamid){	
	   		$ones[$newkey][] = $item;
	   	}
	}
}

if(isset($ones)){
	ksort($ones, SORT_NUMERIC);
}


// get notes from Teams page
if( have_rows('timeline_notes') ):
    while ( have_rows('timeline_notes') ) : the_row();
    	$repteam = get_sub_field('teamid');
		if($repteam == $teamid){
        	$notes[get_sub_field('year')] = get_sub_field('note');
        }
    endwhile;
else :
    $notes = array();
endif;

$helmethist = get_helmet_name_history();
foreach ($helmethist as $value){
	$helmet[$value['team']][$value['year']] = $value;	
}


$teamhelmet = $helmet[$teamid];
//printr($teamhelmet, 0);

foreach ($stand as $key => $value){
	$team_timeline[$key] = array(
		'standings' => $value[0],
		'champions' => $teamchamps[$key],
		'awards' => $team_award_year[$key],
		'number_ones' => $ones[$key],
		'notes' => $notes[$key],
		'helmets' => $teamhelmet[$key]
	);
}


foreach($team_timeline as $key => $val){
	if(!empty($val['standings'])){
		$timelineyears[$key] = $val;
	}
}


?>

<div class="panel">
	<div class="panel-heading">
		<h3 class="panel-title">Team History Timeline</h3>
	</div>
	<div class="panel-body">
		<!-- Timeline -->
		<!--===================================================-->
		
	
        <div class="timeline">
		    
		    	<?php 
			    	 $setval = 0;
			   		 foreach ($timelineyears as $key => $value){
				?>
				<!-- post the years -->
				<div class="timeline-entry">
					<div class="timeline-stat">
				        <div class="timeline-icon <?php echo 'val'.$setval; ?>"></div>
				        <div class="timeline-time"><?php echo $key; ?></div> 
			        </div>
					
				</div>
				
				<!-- team didn't play this year -->
					<?php if(empty($value['stand'])){
					 echo '<div class="timeline-entry"></div>';
					 } 
					 
					 
					 if(isset($value['champions'])){
					 echo '<div class="timeline-entry">
				        <div class="timeline-stat">
				            <div class="timeline-icon bg-success">
					            <img class="" src="https:/wp-content/themes/tif-child-bootstrap/img/award-trophy.jpg" />
				            </div>
				        </div>
				        <div class="timeline-label">'
				            .$key.' <span class="text-bold">PFL CHAMPION </span>
				        </div>
			    	</div>';
					}
					
					
					if(isset($value['number_ones'])){
					 	echo '<div class="timeline-element">';
					        foreach ($value['number_ones'] as $unos){
						        if(isset($unos['playerid'])){
							        $n = get_player_name($unos['playerid']);
							        $name = $n['first'].' '.$n['last'];
						        }
					        	echo '<p class="tl-note">'.$name.' <span class="text-bold">'.$n['pos'].' Scoring Title - </span>'.$unos['points'].'</p>';
					        }
						echo '</div>';
					}
					
					
					if(isset($value['awards'])){
					 	echo '<div class="timeline-element">';
					        foreach ($value['awards'] as $award){
						        if(isset($award['first'])){
							        $name = $award['first'].' '.$award['last'];
						        }
						        if(isset($award['owner'])){
							        $name = $award['owner'];
						        }
						        if(isset($award['pid'])){
							        $n = get_player_name($award['pid']);
							        $name = $n['first'].' '.$n['last'];
						        }
					        	echo '<p class="tl-note">'.$name.' <span class="text-bold">'.$award['award'].'</span></p>';
					        }
						echo '</div>';
					}
					
							
					if(isset($value['notes'])){
						echo '<div class="timeline-element">';
					        	echo '<p class="tl-note font-italic">'.$value['notes'].'</span></p>';
						echo '</div>';
					}
					
					if(isset($value['helmets'])){
						echo '<div class="timeline-element">';
					        	echo '<p class="tl-note">Team Name: <span class="text-bold">'.$value['helmets']['name'].'</span></p>';
						echo '</div>';
						echo '<img src="'.get_stylesheet_directory_uri().'/img/helmets/weekly/'.$teamid.'-helm-right-'.$value['helmets']['helmet'].'.png" class="timeline-helmet" />';
					}
					
					$setval++;
				} 
				
				?>
				 
        </div>
<!--         <?php printr($team_timeline, 0); ?> -->
	</div>
</div>

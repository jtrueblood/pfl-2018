<?php
/*
* Template Name: Transactions
* Description: Lists all transactions for teams since My Fantasy League Website data exsisted.
* Json files for rosters and transactions are downloaded at the end of the season and stored locally on the 'mfl-rosters' and 'mfl-transactions' directoies
*/
?>

<?php
//printr($teambyid, 0);
$teams = get_teams();
get_header();
$playerid = $_GET['pid'];
$name = get_player_name($playerid);

$mfl_team_id_history = teams_for_mfl_history();
//printr($mfl_team_id_history, 0);

?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold">Transactions</h1>

					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
                    <?php
                    // gets json mfl transactions file that merges all of the files above into a name -> id format
                    //$yearid = 2011;
                    // data availible from 2011 on.
                    //$gettwenty = get_mfl_transactions(2020);
                    //printr($gettwenty, 0);

                    //$playerid = '2015DiggWR';
                    $printit = new_mfl_transactions($playerid);
                    $printit = array_filter($printit);
                    $protections = get_protections_player($playerid);
                    $trades = get_trade_by_player($playerid);

                    if($protections):
                        foreach ($protections as $key => $value):
                            $yearprotections[$value['year']] = $value;
                        endforeach;
                    endif;

                    $draft_info = get_draft_info();
                    foreach($draft_info['draft_info'] as $key => $value):
                        $draft_date[$value['year']] = $value['date'];
                    endforeach;

                    foreach ($printit as $k => $v):
                        foreach($v as  $key => $value):
                            $newprint[$value['timestamp']] = $value;
                        endforeach;
                    endforeach;
                    ksort($newprint);
                    printr($newprint, 0);

                    $playerdraft = get_drafts_player($playerid);
                    foreach ($playerdraft as $key => $value):
                        $newplayerdraft[$value['season']] = array(
                                'team' => $value['acteam'],
                                'date' => $draft_date[$value['season']]
                        );
                    endforeach;

                    if($trades):
                        foreach ($trades as $key => $value):
                            $newtrade[$key] = array(
                                'to_team' => $value[0]['traded_to_team'],
                                'from_team' => $value[0]['traded_from_team']
                             );
                        endforeach;
                    endif;
                    //printr($newtrade, 0);
                    ?>

                    <div class="col-xs-24 col-sm-12">

                        <?php

                        echo '<h3>'.$name['first'].' '.$name['last'].'</h3>';
                        $labels = array('Year', 'Player', 'Team', 'Date', 'Action');
                        tablehead('', $labels);
                            foreach($newprint as  $key => $value):
                                $gmdate = gmdate("Y", $value['timestamp']);


                                    if($yearprotections[$gmdate]):
                                        $tableprint .='<tr><td>'.$gmdate.'</td>';
                                        $tableprint .='<td>'.$name['first'].' '.$name['last'].', '.$name['pos'].'</td>';
                                        $tableprint .='<td>'.$yearprotections[$gmdate]['team'].'</td>';
                                        $tableprint .='<td>'.$draft_date[$gmdate].'</td>';
                                        $tableprint .='<td>Protected</td></tr>';
                                    endif;

                                    if($newplayerdraft[$gmdate]):
                                        $tableprint .='<tr><td>'.$gmdate.'</td>';
                                        $tableprint .='<td>'.$name['first'].' '.$name['last'].', '.$name['pos'].'</td>';
                                        $tableprint .='<td>'.$newplayerdraft[$gmdate]['team'].'</td>';
                                        $tableprint .='<td>'.$newplayerdraft[$gmdate]['date'].'</td>';
                                        $tableprint .='<td>Drafted</td></tr>';
                                    endif;


                                    if($value['activated']):
                                        $keys = array_keys($value['activated']);
                                        $activated = $keys[0];
                                        $activated1 = $keys[1];
                                        $activated2 = $keys[2];
                                        if($playerid == $value['activated'][$activated] OR $playerid == $value['activated'][$activated1] OR $playerid == $value['activated'][$activated2]):
                                            $activated = 'Activated (IR)';
                                        else:
                                            $activated = '';
                                        endif;
                                    endif;
                                    if($value['deactivated']):
                                        $keys = array_keys($value['deactivated']);
                                        $deactivated = $keys[0];
                                        $deactivated1 = $keys[1];
                                        $deactivated2 = $keys[2];
                                        if($playerid == $value['deactivated'][$deactivated] OR $playerid == $value['deactivated'][$deactivated1] OR $playerid == $value['deactivated'][$deactivated2]):
                                            $deactivated = 'Deactivated (IR)';
                                        else:
                                            $deactivated = '';
                                        endif;
                                    endif;
                                    if($value['added']):
                                        $keys = array_keys($value['added']);
                                        $added = $keys[0];
                                        $added1 = $keys[1];
                                        $added2 = $keys[2];
                                        if($playerid == $value['added'][$added] OR $playerid == $value['added'][$added1] OR $playerid == $value['added'][$added2]):
                                            $added = 'Added';
                                        else:
                                            $added = '';
                                        endif;
                                    endif;
                                    if($value['dropped']):
                                        $keys = array_keys($value['dropped']);
                                        $dropped = $keys[0];
                                        $dropped1 = $keys[1];
                                        $dropped2 = $keys[2];
                                        if($playerid == $value['dropped'][$dropped] OR $playerid == $value['dropped'][$dropped1] OR $playerid == $value['dropped'][$dropped2]):
                                            $dropped = 'Dropped';
                                        else:
                                            $dropped = '';
                                        endif;
                                    endif;

                                    if($value['type'] == 'TRADE'):
                                        $traded = 'Traded';
                                        $franchise = $newtrade[$gmdate]['from_team'].' > '.$newtrade[$gmdate]['to_team'];
                                    else:
                                        $franchise = $value['franchise'];
                                    endif;

                                    $tableprint .='<tr><td>'.$gmdate.'</td>';
                                    $tableprint .='<td>'.$name['first'].' '.$name['last'].', '.$name['pos'].'</td>';
                                    $tableprint .='<td>'.$franchise.'</td>';
                                    $tableprint .='<td>'.$value['realtime'].'</td>';
                                    $tableprint .='<td>'.$activated.$deactivated.$added.$dropped.$traded.'</td></tr>';

                                $check = $gmdate;
                                $activated = '';
                                $deactivated = '';
                                $added = '';
                                $dropped = '';
                                $traded = '';
                            endforeach;
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Transaction Data from MFL starting in 2011');
                        ?>
                    </div>
                    <?php
//                  $rosteryear = 2021;
//                    echo '<h2>'.$rosteryear.' MFL Rosters</h2>';
//
//                    foreach ($yearslist as $year):
//                        $getjson = get_mfl_year_rosters($year);
//                        $players = $getjson->players->player;
//                            foreach ($players as $key => $value):
//                                if($value->position == 'QB' OR $value->position == 'RB' OR $value->position == 'WR' OR $value->position == 'TE' OR $value->position == 'PK'):
//                                    $player_arr_name[$value->name] = $value->id;
//                                    $player_arr_id[$value->id] = $value->name;
//                                endif;
//                            endforeach;
//                    endforeach;
//
//                    $encode = json_encode($player_arr_name);
//                    echo $encode;
//                    printr($player_arr_name, 0);
                    ?>
											
				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->


		<?php include_once('main-nav.php'); ?>
		<?php include_once('aside.php'); ?>

		</div>
</div> 

<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>
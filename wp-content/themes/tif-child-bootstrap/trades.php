<?php
/*
 * Template Name: Trades
 * Description: Page for displaying all of the Trades by Year
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->

<?php get_header(); 

$gettheseasons = the_seasons();
$theseasons = get_or_set($gettheseasons, 'theseasons', 1200);
$teamlist = teamlist();

$gettrades = get_trades();
$trades = get_or_set($gettrades, 'trades', 1200);
?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold">Trades</h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">	
						<div class="col-xs-24">
						
						<?php

							$c = 0;
							foreach($trades as $value){
								
								$players1 = explode(',', $value['players1']);
								$picks1 = explode(',', $value['picks1']);
								$protections1 = explode(',', $value['protections1']);
								$players2 = explode(',', $value['players2']);
								$picks2 = explode(',', $value['picks2']);
								$protections2 = explode(',', $value['protections2']);
								
								$newtrades[] = array(
									'id' => $value['id'],
                                    'year' => $value['year'],
									'team1' => $value['team1'],
									'players1' => $players1,
									'picks1' => $picks1,
									'protections1' => $protections1,
									'team2' => $value['team2'],
									'players2' => $players2,
									'picks2' => $picks2,
									'protections2' => $protections2,
									'notes' => $value['notes'],
									'when' => $value['when'],
									'tradewinner' => $value['tradewinner'],
									'tradeloser' => $value['tradeloser'],
									'tradewinpoints' => $value['tradewinpoints']
								);
								$c++;
							}

							// Sort by year ascending (oldest first), then by when (Preseason, Draft, Postseason), then by id
							usort($newtrades, function($a, $b) {
								// First sort by year
								if ($a['year'] != $b['year']) {
									return $a['year'] - $b['year'];
								}
								
								// Within same year, sort by trade timing
								$order = array('Preseason' => 1, 'Draft' => 2, 'Postseason' => 3);
								$a_order = isset($order[$a['when']]) ? $order[$a['when']] : 999;
								$b_order = isset($order[$b['when']]) ? $order[$b['when']] : 999;
								
								if ($a_order != $b_order) {
									return $a_order - $b_order;
								}
								
								// If same timing, sort by id
								return $a['id'] - $b['id'];
							});
							//get or set transient
                            $newtradestrans = get_or_set($newtrades, 'newtrades', 1200);

                            //printr($newtradestrans, 1);

                            function get_trades_by_team($rawarray, $teamid){
                                foreach ($rawarray as $key => $value):
                                    if($value['team1'] == $teamid):
                                        $teamtrade[$value['id']] = $value['team2'];
                                    endif;
                                    if($value['team2'] == $teamid):
                                        $teamtrade[$value['id']] = $value['team1'];
                                    endif;
                                endforeach;

                                foreach ($teamtrade as $key => $value):
                                    $newarray[$value][] = $key;
                                endforeach;
                                return $newarray;
                            }

                            foreach($teamlist as $key => $value):
                                if(get_trades_by_team($newtradestrans, $key)):
                                    $printteam[$key] = get_trades_by_team($newtradestrans, $key);
                                else:
                                    $printteam[$key] = '0';
                                endif;
                            endforeach;
                            //printr($printteam, 0);

							$x = 0;
							$current_year = null;
							foreach ($newtradestrans as $key => $value){
							
							// Check if year has changed
							if ($current_year !== $value['year']) :
								// Close previous row if it exists
								if ($current_year !== null && $x % 3 != 0) :
									echo '</div><!-- close previous row -->';
								endif;
								
							// Year section break
							echo '<div class="clearfix"></div>';
							echo '<div class="col-xs-24"><hr><h2 class="text-bold">'.$value['year'].'</h2></div>';
							echo '<div class="clearfix"></div>';
								
								$current_year = $value['year'];
								$x = 0; // Reset counter for new year
							endif;
							
							if ($x % 3 == 0) : 
								echo '<div class="row">';
							endif;
							?>
																								
								<div class="col-xs-8">		
									<div class="panel protections">
										<div class="panel-body">
											<h5><?php echo $value['year'].' / '.$value['when']; ?></h5>
											<div class="col-xs-12">	
											<p>
												<?php echo '<h4><span class="text-bold">'.$teamids[$value['team1']].'</span> get:</h4>';
													//printr($value['players1'], 0);
													if(!empty($value['players1'][0])){
														foreach($value['players1'] as $player){
															$playername = get_player_name($player);
															echo $playername['first'].' '.$playername['last'].', '.$playername['pos'].'<br>';
                                                            $storevalues[] = $player.$value['team2'].$value['team1'].$value['year'];
														}

													}
													if(!empty($value['picks1'][0])){														
														foreach($value['picks1'] as $pick){
															format_draft_pick($pick).' ';
														}
													}
													if(!empty($value['protections1'][0])){
														foreach($value['protections1'] as $protection){
															echo pid_to_name($protection, 0).' was protected';
														} 
													}
													?> 
											</p>
											</div>
											<div class="col-xs-12">	
											<p>
												<?php echo '<h4><span class="text-bold">'.$teamids[$value['team2']].'</span> get:</h4>';
													if(!empty($value['players2'][0])){
														foreach($value['players2'] as $player){
															$playername = get_player_name($player);
															echo $playername['first'].' '.$playername['last'].', '.$playername['pos'].'<br>';
                                                            $storevalues[] = $player.$value['team1'].$value['team2'].$value['year'];
														}
													}
													if(!empty($value['picks2'][0])){
														foreach($value['picks2'] as $pick){
															format_draft_pick($pick).' ';
														}
													}
													if(!empty($value['protections2'][0])){
														foreach($value['protections2'] as $protection){
                                                            echo pid_to_name($protection, 0).' was protected';
														} 
													}
													?>
											</p>
											</div>


                                        </div>


										<div class="panel-footer">
											<?php if($value['notes']): ?>
												<p><?php echo 'Note: '. $value['notes']; ?></p>
											<?php endif; ?>
											<?php 
												// Debug for trade 163
												if($value['id'] == 163):
													echo '<script>console.log("Trade 163 - Winner: '.($value['tradewinner'] ? $value['tradewinner'] : 'empty').', Points: '.($value['tradewinpoints'] ? $value['tradewinpoints'] : 'empty').'");</script>';
												endif;
												if(!empty($value['tradewinner']) && isset($value['tradewinpoints'])):
													$winner_team = $teamlist[$value['tradewinner']];
													echo '<p class="text-bold"><span class="text-success">'.$winner_team.'</span> won by '.$value['tradewinpoints'].' points</p>';
												endif;
											?>
                                            <?php echo '<a href="/trade-analyzer/?TRADE='.$value['id'].'">Trade Analyzer - '.$value['id'].'</a>'; ?>
										</div>
									</div>
								</div>
							
								<?php 
								if ($x % 3 == 2) : 
									echo '</div>';
								endif;
								$x++;
							} ?>
                            <?php
                            //printr($newtrades, 0);

                            foreach ($storevalues as $key => $value):
                                $pid = substr($value, 0, 10);
                                $fromteam = substr($value, 10, 3);
                                $toteam = substr($value, 13, 3);
                                $pyear = substr($value, -4);;
                                // array for counting
                                $playertraded[$pid][] = $pyear;
                                //different array with teams
                                $playerwithteams[$pid][$pyear][] = array(
                                    $fromteam => $toteam
                                );
                            endforeach;

                            foreach($playertraded as $k => $v):
                                $count = count($v);
                                $counttrades[$k] = $count;
                            endforeach;
                            arsort($counttrades);

                            $counttradestrans = get_or_set($counttrades, 'counttrades', 1200);
                            //printr($playerwithteams, 0);
                            ?>

                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-xs-24 col-sm-12">
                            <?php
                            // a player that has been traded more than once
                            $labels = array('Player', 'Times Traded', 'Year - FR>T0');
                            tablehead('Multiple Times Traded', $labels);

                            $a = 1;
                            foreach ($counttradestrans as $key => $value){
                                if($value > 1):
                                    $playername = get_player_name($key);
                                        $tradeprint .='<tr><td>'.$playername['first'].' '.$playername['last'].'</td>';
                                        $tradeprint .='<td class="min-width text-center">'.$value.'</td>';
                                        $tradeprint .='<td class="min-width">';
                                            foreach($playerwithteams[$key] as $k => $v):
                                                foreach($v as $a => $b):
                                                    foreach($b as $fr => $to):
                                                        $tradeprint .= $k.' - '.$fr.' > <strong>'.$to.'</strong>';
                                                    endforeach;
                                                    $tradeprint .= ' | ';
                                                endforeach;
                                            endforeach;
                                        $tradeprint .='</td>';
                                    $a++;
                                endif;
                            }
                            echo $tradeprint;

                            tablefoot('');
                            ?>
                        </div>
                    </div>

                        <!-- times teams have traded between each other -->
                    <div class="col-xs-24 col-sm-12">
                            <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title">Times teams have traded between each other.</h3>
                            </div>
                            <div class="panel-body text-center">

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th class="text-center"></th>
                                        <?php
                                        ksort($printteam);
                                        foreach($printteam as $key => $value):
                                            echo '<th class="text-center">'.$key.'</th>';
                                        endforeach;
                                        ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    //printr($printteam, 0);

                                    foreach($printteam as $key => $value):
                                        //arsort($value);
                                        echo '<tr>';
                                        echo '<td class="text-bold">'.$key.'</td>';

                                        if($value['ATK']):
                                            $thecount = count($value['ATK']);
                                            $sumATK[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['BST']):
                                            $thecount = count($value['BST']);
                                            $sumBST[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['BUL']):
                                            $thecount = count($value['BUL']);
                                            $sumBUL[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['CMN']):
                                            $thecount = count($value['CMN']);
                                            $sumCMN[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['DST']):
                                            $thecount = count($value['DST']);
                                            $sumDST[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['ETS']):
                                            $thecount = count($value['ETS']);
                                            $sumETS[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['HAT']):
                                            $thecount = count($value['HAT']);
                                            $sumHAT[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['MAX']):
                                            $thecount = count($value['MAX']);
                                            $sumMAX[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['PEP']):
                                            $thecount = count($value['PEP']);
                                            $sumPEP[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['PHR']):
                                            $thecount = count($value['PHR']);
                                            $sumPHR[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['RBS']):
                                            $thecount = count($value['RBS']);
                                            $sumRBS[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['SNR']):
                                            $thecount = count($value['SNR']);
                                            $sumSNR[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['SON']):
                                            $thecount = count($value['SON']);
                                            $sumSON[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['TSG']):
                                            $thecount = count($value['TSG']);
                                            $sumTSG[] = $thecount;
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['WRZ']):
                                            $thecount = count($value['WRZ']);
                                            $sumWRZ[] = count($value['WRZ']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        echo '</tr>';
                                    endforeach;

                                        echo '<tr>';
                                            echo '<td class="text-bold">Total</td>';
                                                $sum = array_sum($sumATK);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumBST);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumBUL);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumCMN);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumDST);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumETS);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumHAT);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumMAX);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumPEP);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumPHR);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumRBS);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumSNR);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumSON);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumTSG);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                                $sum = array_sum($sumWRZ);
                                            echo '<td class="text-center">'.$sum.'</td>';
                                        echo '</tr>';

                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    </div>
                    <div class="row">
                            <?php
                            foreach($gettrades as $key => $value):
                            $analyze[$key] = array(
                                'id' => $value['id'],
                                'winner' => $value['tradewinner'],
                                'loser' => $value['tradeloser'],
                                'points' => $value['tradewinpoints'],
                                'team1' => $value['team1'],
                                'team2' => $value['team2'],
                                'players1' => $value['players1'],
                                'players2' => $value['players2'],
                                'picks1' => $value['picks1'],
                                'picks2' => $value['picks2']
                            );
                            endforeach;
                            foreach ($analyze as $key => $value):
                                $winnerbyteam[$value['winner']][$value['id']] = $value['points'];
                                $losersbyteam[$value['loser']][$value['id']] = $value['points'];
                            endforeach;
                            echo '<div class="col-xs-24 col-sm-6">';
                                //printr($analyze, 0);
                            echo '</div>';
                            echo '<div class="col-xs-24 col-sm-6">';
                                //printr($winnerbyteam, 0);
                            echo '</div>';
                            echo '<div class="col-xs-24 col-sm-6">';
                                // Calculate totals, counts, and averages
                                // First, get total trade count per team (both wins and losses)
                                $total_trades_by_team = array();
                                foreach ($analyze as $trade):
                                    if(!empty($trade['team1'])):
                                        if(!isset($total_trades_by_team[$trade['team1']])):
                                            $total_trades_by_team[$trade['team1']] = 0;
                                        endif;
                                        $total_trades_by_team[$trade['team1']]++;
                                    endif;
                                    if(!empty($trade['team2'])):
                                        if(!isset($total_trades_by_team[$trade['team2']])):
                                            $total_trades_by_team[$trade['team2']] = 0;
                                        endif;
                                        $total_trades_by_team[$trade['team2']]++;
                                    endif;
                                endforeach;
                                
                                $winner_stats = array();
                                foreach ($winnerbyteam as $team => $points):
                                    $total = array_sum($points);
                                    $count = isset($total_trades_by_team[$team]) ? $total_trades_by_team[$team] : count($points);
                                    $average = $count > 0 ? round($total / $count, 1) : 0;
                                    $winner_stats[$team] = array(
                                        'total' => $total,
                                        'count' => $count,
                                        'average' => $average
                                    );
                                endforeach;
                                
                                // Sort by average descending
                                uasort($winner_stats, function($a, $b) {
                                    return $b['average'] <=> $a['average'];
                                });
                                
                                echo '<h4>Winners</h4>';
                                $labels = array('Team', 'Total', 'Trades', 'Avg');
                                tablehead('Total Trade Points Winners', $labels);
                                foreach ($winner_stats as $team => $stats):
                                    echo '<tr><td>'.$team.'</td>';
                                    echo '<td class="text-center">'.$stats['total'].'</td>';
                                    echo '<td class="text-center">'.$stats['count'].'</td>';
                                    echo '<td class="text-center"><strong>'.$stats['average'].'</strong></td></tr>';
                                endforeach;
                                tablefoot('Sorted by average points per winning trade');

                                //winners minus losers
//                                foreach ($losersbyteam as $team => $points):
//                                    $losewinners[$team] = array_sum($points);
//                                endforeach;
//                                asort($losewinners);
//                                echo '<h4>Losers</h4>';
                                //printr($losewinners, 0);

//                                foreach($addwinners as $key => $value):
//                                    $getit[$key] = $value - $losewinners[$key];
//                                endforeach;
//                                arsort($getit);
//                                echo '<h4>Total Plus/Minus</h4>';
                                //printr($getit, 0);


                            echo '</div>';
                            echo '<div class="col-xs-24 col-sm-6">';
                                usort($analyze, function ($a, $b) {
                                    return $b['points'] - $a['points'];
                                });
                                //printr($analyze, 0);
                            echo '</div>';

                                ?>

                        </div>
                    </div>
                    
                    <!-- Top 20 Biggest Trade Wins -->
                    <div class="row">
                        <div class="col-xs-24">
                            <?php
                            // Filter out trades without winner data and sort by points descending
                            $biggest_wins = array();
                            foreach($analyze as $trade):
                                if(!empty($trade['winner']) && isset($trade['points']) && $trade['points'] > 0):
                                    $biggest_wins[] = $trade;
                                endif;
                            endforeach;
                            
                            // Already sorted from usort above, just take top 20
                            $top_20 = array_slice($biggest_wins, 0, 20);
                            
                            $labels = array('Rank', 'Trade ID', 'Winner', 'Winner Gets', 'Loser', 'Loser Gets', 'Points');
                            tablehead('Top 20 Biggest Trade Wins', $labels);
                            
                            $rank = 1;
                            foreach($top_20 as $trade):
                                // Determine which side won
                                $winner_gets_players = ($trade['winner'] == $trade['team1']) ? $trade['players1'] : $trade['players2'];
                                $winner_gets_picks = ($trade['winner'] == $trade['team1']) ? $trade['picks1'] : $trade['picks2'];
                                $loser_gets_players = ($trade['loser'] == $trade['team1']) ? $trade['players1'] : $trade['players2'];
                                $loser_gets_picks = ($trade['loser'] == $trade['team1']) ? $trade['picks1'] : $trade['picks2'];
                                
                                // Format winner's assets
                                $winner_assets = '';
                                if(!empty($winner_gets_players)):
                                    $players_array = explode(',', $winner_gets_players);
                                    foreach($players_array as $pid):
                                        $pid = trim($pid);
                                        if($pid):
                                            $pname = get_player_name($pid);
                                            $winner_assets .= $pname['first'].' '.$pname['last'].'<br>';
                                        endif;
                                    endforeach;
                                endif;
                                if(!empty($winner_gets_picks)):
                                    $picks_array = explode(',', $winner_gets_picks);
                                    foreach($picks_array as $pick):
                                        $pick = trim($pick);
                                        if($pick):
                                            $winner_assets .= format_draft_pick_return($pick);
                                        endif;
                                    endforeach;
                                endif;
                                
                                // Format loser's assets
                                $loser_assets = '';
                                if(!empty($loser_gets_players)):
                                    $players_array = explode(',', $loser_gets_players);
                                    foreach($players_array as $pid):
                                        $pid = trim($pid);
                                        if($pid):
                                            $pname = get_player_name($pid);
                                            $loser_assets .= $pname['first'].' '.$pname['last'].'<br>';
                                        endif;
                                    endforeach;
                                endif;
                                if(!empty($loser_gets_picks)):
                                    $picks_array = explode(',', $loser_gets_picks);
                                    foreach($picks_array as $pick):
                                        $pick = trim($pick);
                                        if($pick):
                                            $loser_assets .= format_draft_pick_return($pick);
                                        endif;
                                    endforeach;
                                endif;
                                
                                echo '<tr>';
                                echo '<td class="text-center">'.$rank.'</td>';
                                echo '<td class="text-center"><a href="/trade-analyzer/?TRADE='.$trade['id'].'">'.$trade['id'].'</a></td>';
                                echo '<td class="text-bold">'.$teamlist[$trade['winner']].'</td>';
                                echo '<td><small>'.$winner_assets.'</small></td>';
                                echo '<td>'.$teamlist[$trade['loser']].'</td>';
                                echo '<td><small>'.$loser_assets.'</small></td>';
                                echo '<td class="text-center text-success text-bold">'.$trade['points'].'</td>';
                                echo '</tr>';
                                $rank++;
                            endforeach;
                            
                            tablefoot('');
                            ?>
                        </div>
                    </div>

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
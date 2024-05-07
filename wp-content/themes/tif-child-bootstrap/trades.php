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
								
								$newtrades[$value['year'].$value['team1'].$value['team2'].$c] = array(
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
									'when' => $value['when']
								);
								$c++;
							}

							asort($newtrades);
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
							foreach ($newtradestrans as $key => $value){
							
							if ($x % 3 == 2) : 
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
											<p><?php echo 'Note: '. $value['notes']; ?></p>
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
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['BST']):
                                            $thecount = count($value['BST']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['BUL']):
                                            $thecount = count($value['BUL']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['CMN']):
                                            $thecount = count($value['CMN']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['DST']):
                                            $thecount = count($value['DST']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['ETS']):
                                            $thecount = count($value['ETS']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['HAT']):
                                            $thecount = count($value['HAT']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['MAX']):
                                            $thecount = count($value['MAX']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['PEP']):
                                            $thecount = count($value['PEP']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['PHR']):
                                            $thecount = count($value['PHR']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['RBS']):
                                            $thecount = count($value['RBS']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['SNR']):
                                            $thecount = count($value['SNR']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['SON']):
                                            $thecount = count($value['SON']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['TSG']):
                                            $thecount = count($value['TSG']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        if($value['WRZ']):
                                            $thecount = count($value['WRZ']);
                                            echo '<td class="text-center">'.$thecount.'</td>';
                                        else :
                                            echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                        endif;

                                        echo '</tr>';
                                    endforeach;
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
                                'points' => $value['tradewinpoints']
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
                                foreach ($winnerbyteam as $team => $points):
                                    $addwinners[$team] = array_sum($points);
                                endforeach;
                                arsort($addwinners);
                                echo '<h4>Winners</h4>';
                                //printr($addwinners, 0);
                                $labels = array('Team', 'Points');
                                tablehead('Total Trade Points Winners', $labels);
                                foreach ($addwinners as $key => $value):
                                    echo '<tr><td>'.$key.'</td>';
                                    echo '<td>'.$value.'</td></tr>';
                                endforeach;
                                tablefoot('');

                                //winners minus losers
                                foreach ($losersbyteam as $team => $points):
                                    $losewinners[$team] = array_sum($points);
                                endforeach;
                                asort($losewinners);
                                echo '<h4>Losers</h4>';
                                //printr($losewinners, 0);

                                foreach($addwinners as $key => $value):
                                    $getit[$key] = $value - $losewinners[$key];
                                endforeach;
                                arsort($getit);
                                echo '<h4>Total Plus/Minus</h4>';
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
<?php
/*
 * Template Name: Trade Analyzer
 * Description: Stuff Goes Here
 */
 ?>

<?php get_header(); ?>

<?php

 ?>
<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <div id="page-content">
                <H3>Build Something Ideas</H3>
                <p>2. Trade Analizer - how many trades have happened between each franchise.  Analyize all trades to see who won - Points by player gained, Players Aquired with future picks.  This probably lives on the /trades page </p>
                <p>3. Rebuild / Fix Head to Head Matrix</p>

                <?php
                $drafts = get_drafts();
                $getyears = the_seasons();
                $trades = get_trades();
                $teamlist = get_teams();
                foreach($teamlist as $key => $value):
                    $teams[] = $key;
                endforeach;

                //printr($drafts, 0 );

                $tradeid = $_GET['TRADE'];
                //$tradeid = 140;
                printr($trades[$tradeid], 0);
                $thetrade = $trades[$tradeid];

                $players1 = $thetrade['players1'];
                $expplayers1 = explode(',', $players1);
                $picks1 = $thetrade['picks1'];
                $exppicks1 = explode(',', $picks1);
                $protections1 = $thetrade['protections1'];
                $expprotections1 = explode(',', $protections1);

                $players2 = $thetrade['players2'];
                $expplayers2 = explode(',', $players2);
                $picks2 = $thetrade['picks2'];
                $exppicks2 = explode(',', $picks2);
                $protections2 = $thetrade['protections2'];
                $expprotections2 = explode(',', $protections2);

                //$picktest = get_player_by_pick('2000.02.08'); // RON DANE Issue not in DB but looks like he was drafted and PLayed
                //printr($trades, 0);
                //$picktest1 = get_player_by_pick('2022.03.01');
                //printr($picktest1, 0);

                function grade_acquisition($playerid, $startseason, $teamid){
                    $getyears = the_seasons();
                    foreach($getyears as $year):
                        if($startseason <= $year):
                            $poten_seasons[] = $year;
                        endif;
                    endforeach;
                    foreach ($poten_seasons as $season):
                        $player_tenure[$season] = get_player_season_stats($playerid, $season);
                    endforeach;
                    foreach ($player_tenure as $key => $year):
                        if(is_array($year)):
                        foreach ($year as $week => $value):
                                if(is_array($value)):
                                    $checkteam = $value['team'];
                                    $theseason = $value['season'];
                                    $theweek = $value['week'];
                                    $zeroweek = str_pad($theweek, 2, '0', STR_PAD_LEFT);;
                                if($checkteam == $teamid):
                                    $playeronteam[$theseason.$zeroweek] = $value;
                                endif;
                            endif;
                        endforeach;
                        endif;
                    endforeach;

                    return $playeronteam;
                }
                //$grade = grade_acquisition('2018JackQB', 2021, 'BUL');
                //printr($grade, 0);

                ?>

                <div class="row">
                    <div class="col-xs-24 col-sm-12">
                        <?php echo '<h3>'.$thetrade['year'].' / '.$thetrade['when'].' - Trade ID:'.$tradeid.'</h3>'; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-24 col-sm-8">
                        <?php echo '<h4>'.$thetrade['team1'].'</h4>'; ?>
                        <h5>Players:</h5>
                        <?php
                        foreach($expplayers1 as $key => $value):
                            $trimplay = ltrim($value);
                            if($trimplay):
                                echo '<h3>'.$trimplay.'</h3>';
                            endif;
                        endforeach;
                        ?>
                        <h5>Picks:</h5>
                        <?php
                        printr($exppicks1, 0);
                        foreach($exppicks1 as $key => $value):
                            $trimmed = ltrim($value);
                            echo $trimmed;
                            if($trimmed):
                                $playerpp1 = get_player_by_pick($trimmed);
                                echo '<h3>Became: '.$playerpp1.'</h3>';
                                $grade_pick_player1 = grade_acquisition($playerpp1, $thetrade['year'], $thetrade['team2'] );
                                printr($grade_pick_player1, 0);
                            endif;
                        endforeach;
                        ?>
                        <h5>Protections:</h5>
                        <?php echo '<h3>'.$protections1.'</h3>';?>
                    </div>
                    <div class="col-xs-24 col-sm-8">
                        <?php echo '<h4>'.$thetrade['team2'].'</h4>'; ?>
                        <h5>Players:</h5>
                        <?php
                        foreach($expplayers2 as $key => $value):
                            $trimplay = ltrim($value);
                            if($trimplay):
                                echo '<h3>'.$trimplay.'</h3>';
                            endif;
                        endforeach;
                        ?>
                        <h5>Picks:</h5>
                        <?php
                            printr($exppicks2, 0);
                            foreach($exppicks2 as $key => $value):
                                $trimmed = ltrim($value);
                                echo $trimmed;
                                if($trimmed):
                                    $playerpp2 = get_player_by_pick($trimmed);
                                    echo '<h3>Became: '.$playerpp2.'</h3>';
                                    $grade_pick_player2 = grade_acquisition($playerpp2, $thetrade['year'], $thetrade['team2'] );
                                    printr($grade_pick_player2, 0);
                                endif;
                            endforeach;
                        ?>
                        <h5>Protections:</h5>
                        <?php echo '<h3>'.$protections2.'</h3>';

                            $grade_prot_2 = grade_acquisition($protections2, $thetrade['year'], $thetrade['team2']);
                            printr($grade_prot_2, 0);
                        ?>
                    </div>
                </div>

            </div>
        </div>
</div>



    </div><!--End page content-->

</div><!--END CONTENT CONTAINER-->


<?php include_once('main-nav.php'); ?>
<?php include_once('aside.php'); ?>

</div>

<?php get_footer(); ?>
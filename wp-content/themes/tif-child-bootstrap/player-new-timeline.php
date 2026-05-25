<?php
/*
 * Template Name: Player New Timeline
 * Description: Build out new visual of a complete player timeline in the spectrum of the entire PFL history.
 */

 ?>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<style>
    #content-container {

    }
    .col-xs-21, .col-xs-3 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .wrapper {
        background-color: #fff;
        padding: 10px;
    }
    .wrapper.scroll {
        overflow-x: auto;
        white-space: nowrap;
    }
    .wrapper.nameplates {
        flex-direction: column;
        margin:0px;
        padding-left: 3px;
    }
    .fill-height-or-more {
        display: flex;
        flex-direction: column;
    }

    .fill-height-or-more > div {
        flex: 1;

        display: flex;
        justify-content: center;
        flex-direction: column;
    }
    .playername {
        h5 {
            font-size: 14px;
        }
    }

    .flex-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        white-space: nowrap;
        /* flex-flow: row nowrap; */
        flex-direction: row;
        flex-wrap: nowrap;
        align-content: stretch;
        padding-top: 20px;
        gap: 1px;

    }
    .flex-container::-webkit-scrollbar {
        display: none;
    }

    .flex-container > div{
        background-color: #fff;
        border: 1px solid #ccc;
        padding-top: 7px;
        padding-bottom: 7px;
        width: 100%;
        border-radius: 4px;
    }

    .flex-container > div.posyear {
        width: 1px;
        height: 1px;
        overflow: visible;
        margin-top: -20px;
        padding: 0;
    }

    .flex-container > div.spacer {
        width: 100px;
        position: relative;
        display: block;
    }

    .flex-container > div.weekbox {
        min-width: 19px;
    }

    p.points, p.result, p.awards {
        font-size: 9px;
        margin: 0;
        padding: 0;
        color: #fff;
        text-align: center;
        font-weight: bold;
    }
    p.awards {
        margin-bottom: 3px;
    }
    p.points.weeklight {
        color: #ccc;
        font-weight: normal;
    }
    .team-wrz p {
        color: #000;
    }
    .flex-container > div.add-tooltip {
        background-color: red;
    }
    .weeknum15, .weeknum16, .weeknum17 {
        border: 1px solid #8c8c8c !important;
    }
    .weeknum15 {
        margin-left: 7px;
    }
    .weeknum17 {
        margin-right: 10px;
    }
    .halldiamond .hydrated {
        font-size: 15px;
        color: #000;
        position: relative;
        float: left;
    }
    .fixed {
        position: fixed;
    }


</style>


<?php get_header();

$seasons = the_seasons();
$teams = get_teams();

//$playerid = $_GET['id'];
//$playerid = '1994FaulRB';
$playerlist = array('1991KellQB', '1991RiceWR', '1991SmitRB', '1991MariQB');

//$playerlist = get_player_ids_by_team('ETS');

$season = $_GET['season'];
$weeks = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14);

$theweeks = the_weeks_with_post();

$team_rbs = get_helmet_name_history_by_team('RBS', 1992);
$rbs_color = $team_rbs['color1'];

$team_ets = get_helmet_name_history_by_team('ETS', 2024);
$ets_color = $team_ets['color1'];

$team_pep = get_helmet_name_history_by_team('PEP', 2024);
$pep_color = $team_pep['color1'];

$team_wrz = get_helmet_name_history_by_team('WRZ', 1991);
$wrz_color = $team_wrz['color1'];

$team_bul = get_helmet_name_history_by_team('BUL', 2024);
$bul_color = $team_bul['color1'];

$team_cmn = get_helmet_name_history_by_team('CMN', 2024);
$cmn_color = $team_cmn['color1'];

$team_snr = get_helmet_name_history_by_team('SNR', 2024);
$snr_color = $team_snr['color1'];

$team_tsg = get_helmet_name_history_by_team('TSG', 2024);
$tsg_color = $team_tsg['color1'];

$team_son = get_helmet_name_history_by_team('SON', 1995);
$son_color = $team_son['color2'];

$team_rbs = get_helmet_name_history_by_team('RBS', 1991);
$rbs_color = $team_rbs['color2'];

$team_phr = get_helmet_name_history_by_team('PHR', 1995);
$phr_color = $team_phr['color1'];

$team_max = get_helmet_name_history_by_team('MAX', 2012);
$max_color = $team_max['color2'];

$team_bst = get_helmet_name_history_by_team('BST', 2024);
$bst_color = $team_bst['color2'];

$team_dst = get_helmet_name_history_by_team('DST', 2016);
$dst_color = $team_dst['color1'];

 ?>
    <!--CONTENT CONTAINER-->
    <div id="content-container">


    <div id="page-title">
        <h1 class="page-header text-bold"><?php the_title();?></h1>
    </div>

    <!--Page content-->
    <div id="page-content">

    <!-- THE ROW -->
        <div class="row">
        <div class="col-xs-24">

            <div class="wrapper scroll" >
                <?php foreach ($playerlist as $key => $playerid): ?>
                    <div class="wrapper nameplates">
                        <div class="playername"><h5 class=""><?php echo pid_to_name($playerid, 2); ?></h5>
                        </div>
                    </div>
                    <div class="flex-container " >
                    <?php
                        $playoffsplayed = array();
                        $probowlget = array();
                        $awardssort = array();
                        $player_history = get_player_complete_history($playerid);
                        $weeksplayed = $player_history['weeksplayed'];
                        if($player_history['playoffs']):
                            $playoffsplayed = $player_history['playoffs'];
                        endif;
                        if($player_history['probowl']):
                            $probowlget = $player_history['probowl'];
                        endif;

                        $mergeweeks = $weeksplayed + $playoffsplayed + $probowlget;
                        $probowldetails = probowl_details();
                        $awards = $player_history['awards'];
                        if($awards):
                            foreach ($awards as $key => $value):
                                $type = substr($value['awardid'], 0, 3);
                                $awardssort[$value['year']][$type] = $value;
                                $inhall = 0;
                                if($awardssort[$value['year']]['hal']):
                                    $inhall = 1;
                                endif;
                            endforeach;
                        endif;
                        $i = 0;
                        foreach($theweeks as $key => $value):
                            $result = ($mergeweeks[$value]['result'] == '1') ? 'W' : 'L';
                            $team = $mergeweeks[$value]['team'];
                            $lcteam = strtolower($team);
                            $colorvar = ${$lcteam.'_color'};
                            $teamvar = 'team-'.$lcteam;

                            $getyear = substr($value, 0, 4);
                            if($i == 0):
                                echo '<div class="posyear">'.$getyear.'</div>';
                            endif;
                            $i++;
                            $playoffweek = $playoffsplayed[$value]['year'].$playoffsplayed[$value]['week'];
                            $probowlweek = $probowlget[$value]['year'].'17';

                            if($weeksplayed[$value]['weekid'] == $value OR $playoffweek == $value OR $probowlweek == $value):
                                    //check if probowl points were used
                                    $points = $mergeweeks[$value]['points'];
                                    $probowlresult = $probowldetails[$getyear]['winner'];
                                    $playerprobowlleague = $probowlget[$getyear.'17']['league'];
                                    if($playerprobowlleague == $probowlresult):
                                        $proresult = 'W';
                                    else:
                                        $proresult = 'L';
                                    endif;
                                    echo '<div class="weekbox '.$teamvar.' tint weeknum'.$i.'" style="background-color: '.$colorvar.';">
                                        <p class="points">'.$points.'</p>';
                                        if($i != 17):
                                            echo '<p class="result">'.$result.'</p>';
                                        else:
                                            echo '<p class="result">'.$proresult.'</p>';
                                        endif;
                                        echo '<p class="awards"></p>';
                                    // echo '<div class="dot-indicate">X</div>';
                                    // add space for playoff start
                                    // Week 1
                                    if($i == 1):
//                                            echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="Traded">
//                                            <p class="awards"><ion-icon name="person"></ion-icon></p>
//                                            </a>';
//
//                                            echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="Protected">
//                                            <p class="awards"><ion-icon name="lock-closed"></ion-icon></p>
//                                            </a>';
//
//                                            echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="Grand Slam">
//                                            <p class="awards"><ion-icon name="baseball"></ion-icon></p>
//                                            </a>';

                                    endif;
                                    // end of regular season
                                    if($i == 14):
                                        echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="">
                                        <p class="awards"></p>
                                        </a>';
                                        if($awardssort[$getyear]['mvp']):
                                            echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="PFL MVP">
                                            <p class="awards"><ion-icon name="star"></ion-icon></p>
                                            </a>';
                                        endif;
                                        if($awardssort[$getyear]['rot']):
                                            echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="ROTY">
                                            <p class="awards"><ion-icon name="star"></ion-icon></p>
                                            </a>';
                                        endif;
                                    endif;
                                    if($i == 15):
                                        echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="">
                                            <p class="awards"></p>
                                            </a>';
                                    endif;
                                    if($i == 16):
                                        if($player_history['championships'][$getyear]):
                                            echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="PFL Champion">
                                            <p class="awards"><ion-icon name="trophy"></ion-icon></p>
                                            </a>';
                                        endif;
                                        if($awardssort[$getyear]['pbm']):
                                            echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="Posse Bowl MVP">
                                            <p class="awards"><ion-icon name="star"></ion-icon></p>
                                            </a>';
                                        endif;
                                    endif;
                                    if($i == 17):
                                        if($player_history['probowl'][$getyear.'17']):
                                        echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="Probowl">
                                            <p class="awards"><ion-icon name="shield"></ion-icon></p>
                                            </a>';
                                        endif;
                                        if($awardssort[$getyear]['pro']):
                                            echo '<a href="#" class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="Pro Bowl MVP">
                                            <p class="awards"><ion-icon name="star"></ion-icon></p>
                                            </a>';
                                        endif;

                                        $i = 0;
                                    endif;
                                    // playoffs
                                    echo '</div>';

                                else: // hold the blank space - player did not play
                                    if($i == 0):
                                        echo '<div class="posyear">'.$getyear.'</div>';
                                    endif;
                                    echo '<div class="weekbox team-wrz tint weeknum'.$i.'" style="background-color: #fff;">
                                            <p class="points weeklight">'.$i.'</p>
                                            <p class="result">&nbsp</p>
                                            <p class="awards"></p>
                                        </div>';
                                    if($i == 17):
                                        $i = 0;
                                    endif;
                                endif;
                        endforeach;
                        if($inhall == 1):
                            echo '<a href="#" class="add-tooltip halldiamond" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="PFL Hall of Fame">
                            <p class="awards"><ion-icon name="diamond"></ion-icon></p>
                            </a>';
                        endif;
                    ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php printr($playerteam, 0); ?>
    <!-- END THE ROW -->

        </div>
    </div>

    </div><!--End page content-->

</div><!--END CONTENT CONTAINER-->


<?php include_once('main-nav.php'); ?>
<?php include_once('aside.php'); ?>

</div>

    <?php
    $jusplayerids = just_player_ids();
    $currentid = array_search($playerid, $jusplayerids);
    $nextplayer = $jusplayerids[$currentid + 1];
    $holeplayer = $jusplayerids[$currentid + 2];
    ?>

    <script>

        // DISABLE TO STOP AUTO RELOAD

        //setTimeout(function(){
        //	var reloadpage = '/build-something/?id=<?php //echo $nextplayer; ?>//';
        //    window.location.href = reloadpage;
        // }, 3000);

    </script>


    <script>
        var reloadpage = '/build-something/?id=<?php echo $nextplayer; ?>';
        console.log(reloadpage);
    </script>

<?php get_footer(); ?>
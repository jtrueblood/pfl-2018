<?php
/*
 * Template Name: Scrape PFR for Two Point Conversions
 * Description: Script to Scrape Player Numbers From Pro Football Reference
 */


/*
$url1=$_SERVER['REQUEST_URI'];
header("Refresh: 5; URL=$url1");
*/

$getplayer = $_GET['id'];

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
$first = $featuredplayer[0];
$last = $featuredplayer[1];
$position = $featuredplayer[2];
$rookie = $featuredplayer[3];
$mflid = $featuredplayer[4];

insert_wp_career_leaders($randomplayer);
$testprint = insert_wp_season_leaders($randomplayer);

$yearsplayed = get_player_years_played($randomplayer);
//printr($yearsplayed, 1);

$stylesheet_uri = get_stylesheet_directory_uri();

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
                                    <select data-placeholder="Select an Existing Player" class="chzn-select" style="width:100%;" tabindex="1" id="playerDropScrapeTwoPt">
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
                                    <button class="btn btn-warning" id="playerSelectScrapeTwoPt">Select</button>
                                </div>
                            </div>
                            <?php echo $nextplayer ; ?>


                        </div>
                    </div>
                    <!-- PLAYER SPOTLIGHT -->
                    <div class="col-xs-24 col-sm-4 left-column">
                        <div class="panel widget" >

                            <div class="widget-header" style="min-height: 200px;" >

                                <?php
                                $ifimage = check_if_image($randomplayer);

                                if($ifimage == 1){
                                    $playerimgobj = get_attachment_url_by_slug($randomplayer);
                                    $imgid =  attachment_url_to_postid( $playerimgobj );
                                    $image_attributes = wp_get_attachment_image_src($imgid);

                                    echo '<img src="'.$image_attributes[0].'" class="widget-bg img-responsive">';
                                } else {
                                    echo '<img src="'.$stylesheet_uri.'/img/players/'.$randomplayer.'.jpg" class="widget-bg img-responsive">';
                                }
                                ?>

                            </div>
                            <div class="widget-body text-center">
                                <?php
                                if($ifimage == 1){
                                    echo $ifimage.' source: /wp-content/uploads/';
                                } else {
                                    echo $ifimage.' source: /img/players/';
                                }
                                ?>
                                <img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $position; ?>.jpg">
                                <h3 class="mar-no"><a href="/player/?id=<?php echo $randomplayer;?>"><?php echo $first.' '.$last; ?></a></h3>
                                <p></p>
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

                    <!-- GET BASIC PLAYER INFO -->
                    <div class="col-xs-24 col-sm-6 left-column">
                        <div class="panel widget">
                            <div class="widget-body text-center">
                                <?php


                                $firstInitCap = strtoupper($last[0]);
                                $pfr_id = $featuredplayer[10];


                                include_once('simplehtmldom/simple_html_dom.php');
                                $html = file_get_html('https://www.pro-football-reference.com/players/'.$firstInitCap.'/'.$pfr_id.'.htm');

                                ?>

                                <div class="panel-heading">
                                    <h3 class="panel-title">Player Info Scraped from Pro Football Reference</h3>

                                </div>
                                <div class="panel-body">
                                    <p>Pro Football Ref ID: <?php echo $firstInit.'/'.$pfr_id; ?></p>
                                    <?php printr($info,0); ?>
                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- GET BASIC PLAYER INFO -->
                    <div class="col-xs-12 left-column">
                        <div class="panel widget">
                            <div class="widget-body text-center">
                                <?php



                                // Toggle this value here to set to either all years player played or a simple array of one or a few years.
                                $yearclean = array_values($yearsplayed);
                                //$yearclean = array(2020, 2021);
                                printr($yearclean, 0);

                                $passyards = array();

                                foreach($yearclean as $y){
                                    // Set Boxscore Table from Page	and Year
                                    //$y = 2020;

                                    $htmlgame = '';

                                    $htmlgame = file_get_html('https://www.pro-football-reference.com/players/'.$firstInitCap.'/'.$pfr_id.'/gamelog/'.$y.'/');


                                    // Scrape the Dom
                                    if($pfr_id):
                                        if($htmlgame):
                                            $tablehead = $htmlgame->getElementById("stats thead");
                                            $gamescore = $htmlgame->getElementById("stats tbody");

                                            //$passyards = $htmlgame->find('td');

                                        endif;
                                    endif;


                                    foreach($htmlgame->find('tbody tr [data-stat=week_num]') as $e){
                                        $week_num[$y] .= $e->plaintext.',';
                                    }

                                    foreach($htmlgame->find('tbody tr [data-stat=game_date]') as $e){
                                        $game_date[$y] .= $e->plaintext.',';
                                    }

                                    // age == used to check if there is a line for INACTIVE
                                    foreach($htmlgame->find('tbody tr [data-stat=age]') as $e){
                                        $age[$y] .= $e->plaintext.',';
                                    }

                                    foreach($htmlgame->find('tbody tr [data-stat=two_pt_md]') as $e){
                                        $twopt[$y] .= $e->plaintext.',';
                                    }

                                }

                                foreach ($week_num as $key => $value):
                                    $weeks[$key] = explode(',',$value);
                                endforeach;
                                foreach ($game_date as $key => $value):
                                    $gdate[$key] = explode(',',$value);
                                endforeach;
                                foreach ($age as $key => $value):
                                    $ages[$key] = explode(',',$value);
                                endforeach;
                                foreach ($twopt as $key => $value):
                                    $twos[$key] = explode(',',$value);
                                endforeach;

                                foreach ($ages as $key => $value):
                                    foreach($value as $k => $y):
                                        $newarr[$key][$k] = array(
                                            'week' => $weeks[$key][$k],
                                            'gdate' => $gdate[$key][$k],
                                            'age' => $ages[$key][$k],
                                            'twos' => $twos[$key][$k]
                                        );
                                    endforeach;
                                endforeach;

                                foreach ($ages as $key => $value):
                                    foreach ($value as $k => $v):

                                            $alltwos[$key][$v['gdate']] = $v['twos'];

                                    endforeach;
                                endforeach;

//                                $playerstats = array(
//                                    'week_num' => explode(',',$week_num),
//                                    'tdate' => explode(',',$game_date),
//                                    'twopt' => explode(',',$twopt),
//                                    'age' => explode(',',$age)
//                                );

//                                foreach ($playerstats[0]['age'] as $key => $value):
//                                    $age[$key] = explode(',', $value);
//                                endforeach;
//
//                                foreach ($playerstats[0]['week_num'] as $key => $value):
//                                    $rweeks[$key] = explode(',', $value);
//                                endforeach;
//
//
//
//                                if($playerstats[0]['twopt']):
//                                    foreach ($playerstats[0]['week_num'] as $key => $value):
//                                        if($value):
//                                            $thweeks[$key] = explode(',', $value);
//                                        endif;
//                                    endforeach;
//
//                                    foreach ($playerstats[0]['twopt'] as $key => $value):
//                                        if($value):
//                                            $thpoints[$key] = explode(',', $value);
//                                        endif;
//                                    endforeach;
//
//                                    foreach ($thpoints as $key => $value):
//                                        foreach($value as $k => $y):
//                                            $weeknum = $thweeks[$key][$k];
//                                            if($y):
//                                                $newarr[$key][$weeknum] = $y;
//                                            endif;
//                                        endforeach;
//                                    endforeach;
//
//                                else:
//                                    echo 'NO TWO POINTERS FOUND';
//                                endif;

                                printr($alltwos, 0);

                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-12 left-column">
                        <div class="panel widget">
                            <div class="widget-body text-center">
                                <?php
                                //printr($dataarray[0], 0);
                                //printr($yearsplayed, 0);
                                //printr($alldata, 0);
                                ?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <?php include_once('main-nav.php'); ?>
        </div>

    </div>

<?php
$log_file = $destination_folder.'/file.log';
error_log($report_message, 3, $log_file);
?>


    <script>

        // DISABLE TO STOP AUTO RELOAD

        //setTimeout(function(){
        //	var scrapeclick = '/scrape-pro-football-ref/?id=--><?php //echo $nextplayer; ?>//';
        //    window.location.href = scrapeclick;
        // }, 7000);

    </script>


    <script>
        var scrapeclick = '/scrape-pro-football-ref/?id=<?php echo $nextplayer; ?>';
            console.log(scrapeclick);
    </script>



<?php get_footer(); ?>
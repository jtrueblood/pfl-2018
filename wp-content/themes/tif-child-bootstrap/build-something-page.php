<?php
/*
 * Template Name: PFL Scorigami
 * Description: Used for creating and testing new ideas
 */
 ?>

<?php 
$season = date("Y");
$theweeks = the_weeks();
$playerassoc = get_players_assoc();

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

function theweekvalue($array)
{
    foreach ($array as $key => $value):

            $output[$value[0]] = $value[4];

    endforeach;
    return $output;
}

$rbs_score = theweekvalue($RBS);
$pep_score = theweekvalue($PEP);
$ets_score = theweekvalue($ETS);
$wrz_score = theweekvalue($WRZ);
$cmn_score = theweekvalue($CMN);
$bul_score = theweekvalue($BUL);
$snr_score = theweekvalue($SNR);
$tsg_score = theweekvalue($TSG);
$bst_score = theweekvalue($BST);
$max_score = theweekvalue($MAX);
$phr_score = theweekvalue($PHR);
$son_score = theweekvalue($SON);
$atk_score = theweekvalue($ATK);
$hat_score = theweekvalue($HAT);
$dst_score = theweekvalue($DST);

foreach ($theweeks as $week):
    $allscores[$week] = array(
        $rbs_score[$week],
        $pep_score[$week],
        $ets_score[$week],
        $wrz_score[$week],
        $cmn_score[$week],
        $bul_score[$week],
        $snr_score[$week],
        $tsg_score[$week],
        $bst_score[$week],
        $max_score[$week],
        $phr_score[$week],
        $son_score[$week],
        $atk_score[$week],
        $hat_score[$week],
        $dst_score[$week]
    );
endforeach;

printr($allscores, 1);

$firstweek = $allscores[202103];
sort($firstweek);

foreach ($firstweek as $key => $value):
    if($value !== NULL):
        $newarray[$key] = $value;
    endif;
endforeach;

//$dice = array(4,5,2,6,7);
function checkConsec($d) {
    for($i=0;$i<count($d);$i++) {
        if(isset($d[$i+1]) && $d[$i]+1 != $d[$i+1]) {
            return false;
        }
    }
    return true;
}

//var_dump(checkConsec(array(4,5,6,7))); //returns true
//var_dump(checkConsec(array(2,4,6,7,8))); //returns true
//var_dump(checkConsec(array(1,2,5,7))); //returns false

//$check = HasConsec($newarray);


$first = reset($newarray);
$second = array_slice($newarray, 1, 1, true);
//if ($first == $first):
//    echo $first;
//endif;

//echo $first;

$champs = get_champions();

?>


<?php get_header(); ?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
					
					<div class="panel panel-bordered panel-light">
						<div class="panel-heading">
							<h3 class="panel-title">Title</h3>
                                <?php
                                printr($champs, 0);

                                ?>
						</div>
						<div class="panel-body">										     
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
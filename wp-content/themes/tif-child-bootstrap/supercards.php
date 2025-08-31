<?php
/*
 * Template Name: Supercards
 * Description: Display all player Supercards
 */
 ?>
<?php get_header(); ?>

*/ Players without images
    ['1991BarnWR', '1991ByerRB', '1991SShaWR', '1992DunbRB', '1992PritWR', '1993DawkWR', '1993GreeRB', '1993PottRB', '1994CoatWR', '2015BlueRB', '2017BreiRB', '2020DillRB', '2020ReagWR', '2020RohrPK', '2020ScotRB', '2020SlomPK', '2021HerbRB', '2021HubbRB', '2021MitcRB', '2021MoonWR', '2021RenfWR', '2022AndrWR', '2022BurkWR', '2022DaviWR', '2022DotsWR', '2022GainRB', '2022HuntQB', '2023CharRB', '2023EdwaRB', '2023JohnWR', '2023MimsWR', '2023RobBRB', '2023WarrRB', '2024BensRB', '2024DellWR', '2024DowdRB', '2024GrupPK', '2024LeviQB', '2024MasoRB', '2024MayeQB', '2024MitcWR', '2024NarvPK', '2024ReicPK', '2024RiceWR', '2024ShahWR', '2024TracRB', '2024WhitRB', '2024WrigRB', '2025GoldWR', '2025HarvRB', '2025HendRB', '2025HuntWR', '2025JohnRB', '2025LittPK', '2025LoopPK', '2025McCoWR', '2025McMiWR', '2025NixBQB', '2025SandQB', '2025WardQB', '2025WikKWR', '2025WillWR']
*/

<div class="boxed add-to-top">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php the_title();?></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
					
						<?php
						$getids = just_player_ids();
						$rows = 1;
                        $yearval = 1991;
                        foreach ($getids as $value):
                            $result = substr($value, 0, 4);
                            if($result >= $yearval):
                                $newarr[] = $value;
                            endif;
                        endforeach;

                        //printr($newarr, 0);

						foreach ($newarr as $id):
                            if($rows % 3 == 0):
                                echo '<div class="row">';
                            endif;
                            echo '<div class="col-xs-8">';
                                $getcard = supercard($id);
                                echo $getcard;
                            echo '</div>';
                            if($rows % 3 == 0):
                                echo '</div>';
                            endif;
						$rows++;	
						
						/*
						if($rows == 21):
							break;
						endif;	
						*/
							
						endforeach;
						?>
						
					</div>
				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->


			<?php include_once('main-nav.php'); ?>
			<?php include_once('aside.php'); ?>

		</div>
		
</div> 

		
</div>
</div>



<?php get_footer(); ?>
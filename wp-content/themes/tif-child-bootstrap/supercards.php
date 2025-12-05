<?php
/*
 * Template Name: Supercards
 * Description: Display all player Supercards
 */
 ?>
<?php get_header(); ?>

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
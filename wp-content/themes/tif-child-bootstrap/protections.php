<?php
/*
 * Template Name: Protections
 * Description: Page for displaying all of the Protections by Year
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->

<?php get_header(); ?>



<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold">Team Protections</h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">	
						<div class="col-xs-24">
						
						<?php
                        $protections = get_protections();
                        $loop = 0;

                        foreach ($protections as $key => $value) {
                            $team = $value['team'];
                            $first = $value['first'];
                            $last = $value['last'];
                            $position = $value['position'];
                            $playerid = $value['playerid'];
                            $year = $value['year'];

                            $printprotections[$year][$team][] = $value;
                        }

                        //printr($printprotections, 1);

                        foreach ($printprotections as $key => $val) {

                                ?>
                                <div class="col-xs-24">

                                    <h4 class="text-center protection-season"><?php echo $key; ?> Season</h4>

                                </div>



                                <?php
                                //asort($val);
                                foreach ($val as $k => $value):
                                ?>
                                <div class="row">
                                    <div class="col-xs-2">

                                        <h3 class="text-bold text-center"><?php echo $k; ?></h3>
    <!-- 										<div class="panel-body" style="background-image:url(<?php echo get_stylesheet_directory_uri().'/img/'.$k?>-bar.png); background-position-x: -20px; background-position-y: -20px; background-color: #efefef; opacity: 0.5;"></div> -->
                                    </div>
		
								<?php foreach ($value as $info): ?>
                                    <?php if($loop == 3): ?>
                                        <div class="col-xs-2"></div>
                                    <?php endif; ?>

                                    <div class="col-xs-7">
                                        <div class="panel protections">
                                            <div class="panel-body <?php echo $info['position'];?>">
                                            <?php
                                            if ($first == 'No Protection'){
                                                echo 'No Protection';
                                             } else {
                                                $playerimgobj = get_attachment_url_by_slug($info['playerid']);
                                                $imgid =  attachment_url_to_postid( $playerimgobj );
                                                $image_attributes = wp_get_attachment_image_src($imgid, array( 100, 100 ));
                                                $playerimg = $image_attributes[0];

                                                 echo '<img src="'.$playerimg.'" class="leaders-image"><h4 class="text-bold"><a href="/player/?id='.$info['playerid'].'">'.$info['first'].' '.$info['last'].'</a></h4>, '.$info['position'];
                                             }?>
                                            </div>
                                        </div>
                                    </div>


							
								<?php
                                    $loop++;
                                    endforeach; ?>
                                    </div>
								<?php
                                $loop = 0;
								endforeach;
							}

							?>
							
							

						</div>

										
						
						<!-- start sidebar -->
						
<!--
						<div class="col-xs-24 col-sm-6">
					
								<div class="panel">
									<div class="panel-body">
										
										<?php echo '<h3>A Title</h3>';?>
										
							
									</div>
								</div>
							</div>
											
						</div>
-->
						
						<!-- end sidebar -->
						
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
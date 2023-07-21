<?php
/*
 * Template Name: Protections Team
 * Description: Page for displaying all of the Protections by Team
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->

<?php get_header(); ?>
<?php
$getyear = $_GET['Y'];
$getteam = $_GET['TEAM'];
?>


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

							foreach($protections as $key => $item){
							   $arr_resort[$item['team']][$key] = $item;
							}
							
							ksort($arr_resort, SORT_NUMERIC);

							//printr($arr_resort, 1);

							foreach ($arr_resort as $key => $value):
                                foreach ($value as $k => $v):
                                    $teamprotect[$key][$v['year']][] = $v;
                                endforeach;
                            endforeach;

                            if($getteam != ''):
                                $printprotect[$getteam] = $teamprotect[$getteam];
                            else:
                                if($getyear != ''):
                                    foreach($teamprotect as $team => $years):
                                        if($years[$getyear]):
                                            $printprotect[$team][$getyear] = $years[$getyear];
                                        endif;
                                    endforeach;
                                else:
                                    $printprotect = $teamprotect;
                                endif;
                            endif;



                            //printr($printprotect, 0);

                            foreach ($printprotect as $key => $team):
                            echo '<div class="row">';
                            echo '<h3>'.$key.'</h3>';
							foreach ($team as $year => $value):
                                $i = 0;
                                    echo '<div class="row">';
                                        echo '<div class="col-xs-2">';
                                            echo '<h4>'.$year.'</h4>';
                                        echo '</div>';
                                        foreach ($value as $k => $v):

                                        $team = $v['team'];
                                        $first = $v['first'];
                                        $last = $v['last'];
                                        $position = $v['position'];
                                        $playerid = $v['playerid'];
                                        if($i == 3):
                                            echo '<div class="col-xs-2"></div>';
                                        endif;
                                        ?>

                                            <div class="col-xs-7">
                                                <div class="panel protections">
                                                    <div class="panel-body <?php echo $position;?>">
                                                    <?php
                                                        $playerimgobj = get_attachment_url_by_slug($playerid);
                                                        $imgid =  attachment_url_to_postid( $playerimgobj );
                                                        $image_attributes = wp_get_attachment_image_src($imgid, array( 100, 100 ));
                                                        $playerimg = $image_attributes[0];
                                                        echo '<img src="'.$playerimg.'" class="leaders-image"><h4 class="text-bold"><a href="/player/?id='.$playerid.'">'.$first.' '.$last.'</a></h4>, '.$position;
                                                     ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php
                                            $i++;
                                            endforeach;
                                        echo '</div>';
							    endforeach;
							    echo '</div>';
                            endforeach;
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
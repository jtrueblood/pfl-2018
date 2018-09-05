<?php
/*
 * Template Name: Awards Rookie 
 * Description: Used for individual player awards where the season stats are displayed (ROTY, roty)
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
	$season = date("Y");
	
	$awardid = get_field('award_id' );
			
	$roty = get_award('Rookie of the Year', 2);
	
?>

<?php get_header(); ?>

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
					
				<?php foreach ($roty as $theroty){ 
					
					$rotyyear = $theroty['year'];
					$rotyteam = $theroty['team'];
					$rotyfirst = $theroty['first'];
					$rotylast = $theroty['last'];
					$rotyid = $theroty['pid'];
					$rotypos = $theroty['position'];
					$playerstats = get_player_data($rotyid);
					
				?>	
										
				<div class="col-xs-24 col-sm-12 col-md-6 eq-box-sm">	
					<div class="panel widget">
						<div class="widget-header bg-light">
							
							<?php
							$playerimgobj = get_attachment_url_by_slug($rotyid);
							$imgid =  attachment_url_to_postid( $playerimgobj );
							$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
							$playerimg = $image_attributes[0];
							?>
							
							<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image">
						</div>
						<div class="widget-body text-center bg-primary">
							<img alt="Profile Picture" class="widget-img img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $rotypos; ?>.jpg">
							<h4 class="mar-no text-center"><?php echo $rotyfirst.' '.$rotylast; ?></h4>
							<p class="text-light text-center mar-btm"><?php echo $rotyteam; ?></p>
							<div class="">
								<span class="text-lg"><?php echo $rotyyear; ?> </span>
								<?php 
									$season_data = get_player_season_stats($rotyid, $rotyyear);
								?>
								<h5>Points: <span class="text-bold text-light"><?php echo $season_data['points']; ?></span></h5>
								<h5>Games: <span class="text-bold text-light"><?php echo $season_data['games']; ?></span></h5>
								<h5>PPG: <span class="text-bold text-light"><?php echo $season_data['ppg']; ?></span></h5>
								<h5>High: <span class="text-bold text-light"><?php echo $season_data['high']; ?></span></h5>
							</div>
							</p>
						</div>
					</div>
				</div>
				
				<?php } ?>
				
				</div>
				<!--End page content-->

			</div><!--END CONTENT CONTAINER-->


		<?php include_once('main-nav.php'); ?>
		<?php include_once('aside.php'); ?>

		</div>
</div> 

<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>
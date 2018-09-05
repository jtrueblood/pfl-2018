<?php
/*
 * Template Name: Awards Probowl
 * Description: Used for individual player awards where only one game is relevant (Posse Bowl MVP)
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
	$season = date("Y");
	
	$awardid = get_field('award_id' );
	
	$pbmvp = get_award('Pro Bowl MVP', 2);	
	
// 	printr($pbmvp, 1);
	
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
					
					<?php foreach ($pbmvp as $get){ ?>
					<div class="col-xs-24 col-sm-6 col-md-4 eq-box-sm">
						
					<div class="panel widget">
						<div class="widget-header">
							
							<?php
							$playerimgobj = get_attachment_url_by_slug($get['pid']);
							$imgid =  attachment_url_to_postid( $playerimgobj );
							$image_attributes = wp_get_attachment_image_src($imgid, array( 200, 200 ));	
							$playerimg = $image_attributes[0];
							?>
							
							<img class="widget-bg" src="<?php echo $playerimg; ?>" alt="Image">
						</div>
						<div class="widget-body text-center bg-dark">
							<img alt="Profile Picture" class="widget-img img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $get['position']; ?>.jpg">
							<h4 class="mar-no text-center"><?php echo $get['first'].' '.$get['last']; ?></h4>
							<p class="text-light text-center mar-btm"><?php echo $get['team']; ?></p>
							<span class="text-lg"><?php 
								echo $get['year']; 
							?> </span>
							<p class="text-light text-uppercase"><?php 
									echo 'Points Scored: <strong>'.$get['gamepoints'].'</strong>'; 
							?>
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
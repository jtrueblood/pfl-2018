<?php
/*
 * Template Name: Awards Hall
 * Description: Used for individual player awards where the career stats are displayed (Hall of Fame)
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
	<?php 
	
	$season = date("Y");
	
	$awardid = get_field('award_id' );
	
	$award = get_award('Hall of Fame Inductee', 2);	

/*	
	$get = get_player_career_stats('2000GarcQB');
 	printr($get, 1);
*/	

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
			<?php foreach ($award as $hall){
				
				$hallyear = $hall['year'];
				$hallfirst = $hall['first'];
				$halllast = $hall['last'];
				$hallid = $hall['pid'];
				$hallpos = $hall['position'];
				$playerstats = get_player_data($hallid);
				
			?>		
			<div class="col-xs-24 col-sm-12 col-md-6 eq-box-sm">	
				<div class="panel widget">
					<div class="widget-header bg-light">
						<img class="widget-bg img-responsive" src="<?php echo get_stylesheet_directory_uri();?>/img/players/<?php echo $hallid; ?>.jpg" alt="Image">
					</div>
					<div class="widget-body text-center bg-grey">
						<img alt="Profile Picture" class="widget-img img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $hallpos; ?>.jpg">
						<h4 class="mar-no text-center"><?php echo '<a href="/player?id='.$hallid.'">'.$hallfirst.' '.$halllast.'</a>'; ?></h4>
						<p class="text-muted text-center mar-btm"><?php echo $teamfull; ?></p>
						<span class="text-lg"><?php 
							echo $hallyear.' Hall of Fame Inductee'; 
						?> </span>
								<div class="table-responsive mar-top">

								<?php $career = get_player_career_stats($hallid); ?>	

									
								<table class="table table-striped">
								<tbody>
								<tr>
									<td class="text-left">Points Scored</td>
									<td><span class="text-bold"><?php echo number_format($career['points'],0); ?></span></td>
								</tr>
								<tr>
									<td class="text-left">Games Played</td>
									<td><span class="text-bold"><?php echo $career['games']; ?></span></td>
								</tr>
								<tr>
									<td class="text-left">Seasons</td>
									<td><span class="text-bold"><?php echo $career['years'][0].' - '.end($career['years']); ?></span></td>
								</tr>
								<tr>
									<td class="text-left">Points Per Game</td>
									<td><span class="text-bold"><?php echo $career['ppg']; ?></span></td>
								</tr>
								<tr>
									<td class="text-left">Career High</td>
									<td><span class="text-bold"><?php echo $career['high']; ?></span></td>
								</tr>
								</tbody>
								</table>
								</div>
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
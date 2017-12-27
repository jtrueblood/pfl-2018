<?php
/*
 * Template Name: Single Page
 * Description: Simple Singe Page
 */
 ?>

<?php get_header(); 
//start the loop
while (have_posts()) : the_post();
?>

<div class="container">
	<h2>TIF Bootstrap Theme</h2>
	<div class="col">
		<div class="col-md-8">.col-md-8</div>
		<div class="col-md-8">.col-md-8</div>
		<div class="col-md-8">.col-md-8</div>	
	</div>
	<div class="row">
	  <div class="col-md-12">.col-md-12</div>
	  <div class="col-md-12">.col-md-12</div>
	</div>
	
	<p><?php the_content();?></p>
</div>


<?php
// end the loop
endwhile; wp_reset_query();
?>
<?php get_footer(); ?>
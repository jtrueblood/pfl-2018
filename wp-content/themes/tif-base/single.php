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
	<h2>TIF Base Theme</h2>
	<?php the_content();?>
</div>


<?php
// end the loop
endwhile; wp_reset_query();
?>
<?php get_footer(); ?>
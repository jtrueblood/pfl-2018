<?php
/*
 * Template Name: Nifty Setup
 * Description: Setting up the merge of the Nifty theme
 */
 ?>
 <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
 <?php get_header(); ?>

		<div class="boxed">


			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<!--Page Title-->
				<div id="page-title" class-"">
					
					<h1 class="page-header text-overflow">Revealing Navigation</h1>
				</div>
				<!--End page title-->


				<!--Breadcrumb-->
				<ol class="breadcrumb">
					<li><a href="#">Home</a></li>
					<li><a href="#">Library</a></li>
					<li class="active">Data</li>
				</ol>
				<!--End breadcrumb-->


				<!--Page content-->
				<div id="page-content">
					
					<?php while (have_posts()) : the_post(); ?>
					<?php the_content(); ?>
					<?php endwhile; wp_reset_query(); ?>						
				
				</div><!--End page content-->


			</div><!--END CONTENT CONTAINER-->


			<?php include_once('main-nav.php'); ?>
			<?php include_once('aside.php'); ?>

		</div>
</div> 
 <?php get_footer(); ?>
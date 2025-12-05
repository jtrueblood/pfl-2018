<?php
/*
 * Template Name: Mr Irrelevant
 * Description: Displays the last draft pick from each season
 */
 ?>

<?php 
	$mrirrelevant = mr_irrelevant_table();
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
            <div class="col-xs-24 col-sm-12">
                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Mr. Irrelevant - Last Pick of Each Season</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Year</th>
                                        <th>Round</th>
                                        <th>Pick #</th>
                                        <th>Team</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Pos</th>
                                        <th>Player ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mrirrelevant as $pick): ?>
                                        <tr>
                                            <td><?php echo $pick['year']; ?></td>
                                            <td><?php echo $pick['round']; ?></td>
                                            <td><?php echo $pick['picknum']; ?></td>
                                            <td><?php echo $pick['team']; ?></td>
                                            <td><?php echo $pick['playerfirst']; ?></td>
                                            <td><?php echo $pick['playerlast']; ?></td>
                                            <td><?php echo $pick['pos']; ?></td>
                                            <td><?php echo $pick['playerid']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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

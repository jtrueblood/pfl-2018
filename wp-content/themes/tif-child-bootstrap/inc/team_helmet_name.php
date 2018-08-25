<div class="panel team-hel-panel">
	<div class="team-helmet widget-header">
		<img src="<?php echo get_stylesheet_directory_uri();?>/img/<?php echo $teamid;?>-bar.png" class="widget-bg team-helmet-image">
	
	</div>
	<div class="text-center">
		<?php 
			echo '<h2>'.$team_all_ids[$teamid]['team'].'</h2>';
			echo '<h5>'.$team_all_ids[$teamid]['owner'].'</h5>'; 
			echo '<h4><span class="text-thin">Record:</span> '.$totalwins.' - '.$totalloss.'</h4>'; 
			echo '<h4><span class="text-thin">Win %:</span> '.number_format($totalwinper, 3).'</h4>'; 
		?>
	</div>
<!--
	<div class="panel-footer">
	</div>
-->
</div>
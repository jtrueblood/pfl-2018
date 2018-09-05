<?php $teamnamelong = $team_all_ids[$teamid]['team']; ?>

<div class="panel team-hel-panel">
	<div class="team-helmet widget-header">
		<img src="/wp-content/uploads/<?php echo $teamid;?>-helmet-full.png" class="widget-bg team-helmet-image">
	    <div class="team-name <?php if ($teamnamelong == 'Destruction'){ echo 'wide-team-name'; } ?>">
	    	<?php
				
				if ($teamnamelong == 'Paraphernalia'){
					$teamnamelong = 'Pherns';
				}
				
		    	echo '<h2>'.$teamnamelong.'</h2>'; 
		    	?>
	    </div>
	</div>
	<div class="text-center">
		<?php 
			echo '<h5>'.$team_all_ids[$teamid]['owner'].'</h5>'; 
			echo '<h4><span class="text-thin">Record:</span> '.$totalwins.' - '.$totalloss.'</h4>'; 
			echo '<h4><span class="text-thin">Win %:</span> '.number_format($totalwinper, 3).'</h4>'; 
		?>
	</div>
</div>
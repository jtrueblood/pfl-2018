<?php if(isset($award_hall)){ ?>
<div class="panel">
	<div class="panel-heading">
    	<h3 class="panel-title">PFL Hall of Famers</h3>
	</div>
	<div class="panel-body text-center">
		
		<?php 
		foreach ($award_hall as $key => $val){
			echo '<div class="row hall-for-teams">';
				echo '<div class="col-xs-24 col-sm-6">';
					echo '<a href="/player/?id='.$val['pid'].'"><img src="'.get_stylesheet_directory_uri().'/img/players/'.$val['pid'].'.jpg" class="img-responsive"/></a>';
				echo '</div>';
				echo '<div class="col-xs-24 col-sm-16">';
					echo '<h4><a href="/player/?id='.$val['pid'].'">'.$val['first'].' '.$val['last'].'</a></h4>';
					echo '<p>Inducted Class of <span class="text-bold">'.$val['year'].'</span></p>';
				echo '</div>';
			echo '</div>';	
		}
		//printr($award_hall, 0);
		?>
		
	</div>
</div>
<?php } ?>
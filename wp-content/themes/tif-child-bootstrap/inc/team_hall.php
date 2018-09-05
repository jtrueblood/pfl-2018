<?php if(isset($award_hall)){ ?>
<div class="panel">
	<div class="panel-heading">
    	<h3 class="panel-title">PFL Hall of Famers</h3>
	</div>
	<div class="panel-body text-center">
		
		<?php 
		foreach ($award_hall as $key => $val){
			
			$playerimgobj = get_attachment_url_by_slug($val['pid']);
			$imgid =  attachment_url_to_postid( $playerimgobj );
			$image_attributes = wp_get_attachment_image_src($imgid, array( 100, 100 ));	
			$playerimg = $image_attributes[0];

			echo '<div class="row hall-for-teams">';
				echo '<div class="col-xs-24 col-sm-6">';
					echo '<a href="/player/?id='.$val['pid'].'"><img src="'.$playerimg.'" class="img-responsive"/></a>';
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
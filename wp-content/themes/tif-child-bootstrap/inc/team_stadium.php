<div class="panel">
	<?php 
	$getstadium = get_table('wp_stadiums');
	
	foreach($getstadium as $item){
		if ($item[2] == $teamid){
			if($item[12] == 1){
				$stadium[] = $item;
			} else {
				$prev_stadium[] = $item;
			}
		}
	}

	$currentstad = $stadium[0][1];
	$opened = $stadium[0][3];
	$surface = $stadium[0][5];
	$seats = number_format($stadium[0][6]);
	
	if(isset($stadium[0][9])){
		$club = number_format($stadium[0][9]);
	} else {
		$club = '--';
	}
	if(isset($stadium[0][8])){
		$box = number_format($stadium[0][8]);
	} else {
		$box = '--'; 	
	}
	
	$prev_stad = $prev_stadium[0][1];
	$prev_opened = $prev_stadium[0][3];
	$prev_surface = $prev_stadium[0][5];
	$prev_seats = number_format($prev_stadium[0][6]);
	
	if(isset($prev_stadium[0][9])){
		$prev_club = number_format($prev_stadium[0][9]);
	} else {
		$prev_club = '--';
	}
	if(isset($prev_stadium[0][8])){
		$prev_box = number_format($prev_stadium[0][8]);
	} else {
		$prev_box = '--';
	}
	
	// check if the repeater field has rows of data
	if( have_rows('stadium') ):
	
	    while ( have_rows('stadium') ) : the_row();

	      	$stadteam = get_sub_field('team');
	      	$stad_img = get_sub_field('image');
	      	$stad_notes = get_sub_field('notes');
	      	
	      	$stad_arr[$stadteam] = array(
		      	'image' => $stad_img,
		      	'notes' => $stad_notes
	      	);
	
	    endwhile;
	
	else :
	
	    // no rows found
	
	endif;

	$stadiumimage = $stad_arr[$teamid]['image'];

	?>
	<div class="panel-body stadium <?php if(isset($stadiumimage)){ echo 'stadium-img-class'; }?>" style="background-image: url(<?php echo $stadiumimage; ?>);">
		<?php if (isset($stadium)){ ?>
	
		<h4><?php echo $currentstad; ?></h4>
		<h4>
		<h5><span class="text-thin">Facility Opened:</span> <?php echo $opened; ?></h5>
		<h5><span class="text-thin">Playing Surface:</span> <?php echo $surface; ?></h5>
		<h5><span class="text-thin">Seats:</span> <?php echo $seats; ?> / <span class="text-thin">Club:</span> <?php echo $club; ?> / <span class="text-thin">Box:</span> <?php echo $box; ?></h5>
		<?php if(isset($prev_stadium)){ ?>
			<h5><span class="text-thin">Previous Facilities:</span> <?php echo $prev_stadium[0][1]; ?></h5>
		<?php } ?>
		
		
		<?php } else { ?>

			<h4><?php echo $prev_stad; ?></h4>
			<hr>
			<h5><span class="text-thin">Facility Opened:</span> <?php echo $prev_opened; ?></h5>
			<h5><span class="text-thin">Playing Surface:</span> <?php echo $prev_surface; ?></h5>
			<h5><span class="text-thin">Seats:</span> <?php echo $prev_seats; ?> / <span class="text-thin">Club:</span> <?php echo $prev_club; ?> / <span class="text-thin">Box:</span> <?php echo $prev_box; ?></h5>
		<?php } ?>
		
		<hr/>
		<p></p>
		
		<p><?php echo $stad_arr[$teamid]['notes']; ?></p>
		
		
		
	</div>
</div>
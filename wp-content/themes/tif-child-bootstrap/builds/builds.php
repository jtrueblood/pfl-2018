<?php
/*
 * Template Name: Builds 
 * Description: This serves as the parent page for all build pages.  From here you can see a link to all build pages.  Instructions on building cached arrays.  And samples of each array type used in the website.  */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php  get_header(); 	

	
//start the loop
while (have_posts()) : the_post();

?>


<div class="container add-to-top">
	
	<h2>TIF Bootstrap Theme</h2>
	<?php echo $content; ?>
	<div class="row">
		
		<div class="col-xs-12">
		</div>
		
		<div class="col-xs-12">
		</div>
		
		<div class="col-xs-12">
			<div class="panel">
				<div class="panel-heading">
					<h3 class="panel-title">End of Season Procedure</h3>
				</div>
				<div class="panel-body">
					
						<?php
						if( have_rows('the_list') ):
						    while ( have_rows('the_list') ) : the_row();	
						    	echo '<li class="list-group-item">';    
						        the_sub_field('step');
								echo '<li/>';
						    endwhile;
						else :
						endif;
						?>	
				
				</div>
			</div>
		</div>
		
		<div class="col-xs-12">
			<div class="panel">
				<div class="panel-heading">
					<h3 class="panel-title">Link To Child Build Pages</h3>
					
				</div>
				<div class="panel-body">
		<?php 
			$pages = get_pages(array( 'child_of' => $post->ID)); 
			foreach ($pages as $page){
				$title = $page->post_title;
				$thecontent = $page->page_content;
				$url = $page->guid;
				echo '<a href="'.$url.'"/><h5>'.$title.'</h5></a>';
			}  
				?></div>
			</div>
		
		<div class="panel">
				<div class="panel-heading">
					<h3 class="panel-title">Player ID Conversion</h3>
				</div>
				<div class="panel-body">
				<p>Requires linkedidcache.txt file is updated</p>
				
				<div class="input-group mar-btm">
					<span class="input-group-btn">
						<button class="btn btn-warning" type="button">Submit</button>
					</span>
					<input type="email" placeholder="Get MFL ID" class="form-control">
				</div>
				
					
				</div>
		</div>
		
		</div>
		
		<div class="col-xs-12">
		<p><?php echo $thecontent; ?></p>
		
		</div>
	
	</div>
	
	<hr/>
	
	<div class="row">
		<?php
		get_cache('players', 0);	
		$players = $_SESSION['players'];
		?>
		
		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>players.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Associative by player id</a>
				<a class="list-group-item list-item-sm" href="#">/cache/playerdata</a>
				<a class="list-group-item list-item-sm" href="#">Derived: playersid.txt, playersassoc.txt (same as player but by ID associative</a>
			</div>
			<!--===================================================-->
			<pre>
			<?php print_r($players['1991AikmQB']); ?>
			</pre>
		</div>
		</div>
		
		<!-- change -->
		
		<?php
		get_cache('drafts', 0);	
		$drafts = $_SESSION['drafts'];
		?>
		
		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>drafts.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Associative by draft pick id</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
			</div>
			<!--===================================================-->
			<pre>
			<?php print_r($drafts['1991010101']); ?>
			</pre>
		</div>
		</div>
		
		<!-- change -->
		
		<?php
		get_cache('protections', 0);	
		$protections = $_SESSION['protections'];
		?>
		
		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>protections.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
			</div>
	
			<pre>
			<?php print_r($protections[0]); ?>
			</pre>
		</div>
		</div>
		
		
		</div>
		<!-- row -->
		<div class="row">
		
		<!-- change -->
		
		<?php
		get_cache('playoffall', 0);	
		$playoffall = $_SESSION['playoffall'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>playoffall.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Associative by playoffgame id</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
			</div>
			<pre>
			<?php print_r($playoffall['19910110']); ?>
			</pre>
		</div>
		</div>
		
		<!-- change -->
		
		<?php
		get_cache('awards', 0);	
		$awards = $_SESSION['awards'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>awards.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
				<a class="list-group-item list-item-sm" href="#">Derived: hall.txt, ooty.txt, roty.txt, bowlmvp.txt, mvp.txt, promvp.txt</a>
				<a class="list-group-item list-item-sm" href="#">Note: 'Owner' used for OOTY.  'Game Points' used for Bowl MVP and Pro MVP.</a>
			</div>
			<pre>
			<?php print_r($awards[0]); ?>
			</pre>
		</div>
		</div>
		
		
		<!-- change -->
		
		<?php
		get_cache('BULversus', 0);	
		$bulversus = $_SESSION['BULversus'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>BULversus.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Associative by week id</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
				<a class="list-group-item list-item-sm" href="#">Required: BUL.txt</a>
				<a class="list-group-item list-item-sm" href="#">Note: Requires that all team text files be created first then will add versus score and home/away added.  The 8 positions are there for future use and are intended to be blank for now.</a>
			</div>
			<pre>
			<?php print_r($bulversus['199101']); ?>
			</pre>
		</div>
		</div>
		
		
		</div>
			
		<!-- row -->
		<div class="row">
		
		<!-- change -->
		
		<?php
		get_cache('overtime', 0);	
		$overtime = $_SESSION['overtime'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>overtime.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Associative by overitme id</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
				<a class="list-group-item list-item-sm" href="#">Note: id is year-week-gm format.  If there are multiple OT games in a given week the id is ...01, ...02, ...03</a>
			</div>
			<pre>
			<?php print_r($overtime['19910801']); ?>
			</pre>
		</div>
		</div>
		
		
		<!-- change -->
		
		<?php
		get_cache('allweekids', 0);	
		$allweekids = $_SESSION['allweekids'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>allweekids.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
				<a class="list-group-item list-item-sm" href="#">Derived: theyears.txt</a>
				<a class="list-group-item list-item-sm" href="#">Note: This is not linked to a database table.  Just a looped array that starts in 199101 and ends on current year ....14</a>
			</div>
			<pre>
			<?php print_r($allweekids[0]); ?>
			</pre>
		</div>
		</div>
		
		<!-- change -->
		
		<?php
		get_cache('teaminfo', 0);	
		$teaminfo = $_SESSION['teaminfo'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>teaminfo.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Associative by team ID</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
			</div>
			<pre>
			<?php print_r($teaminfo['BUL']); ?>
			</pre>
		</div>
		</div>


		</div>		
		
		<!-- row -->
		<div class="row">
		
		<!-- change -->
		
		<?php
		get_cache('probowl', 0);	
		$probowl = $_SESSION['probowl'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>probowl.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
			</div>
			<pre>
			<?php print_r($probowl[0]); ?>
			</pre>
		</div>
		</div>
		
		
		<!-- change -->
		
		<?php
		get_cache('2014probox', 0);	
		$probox2014 = $_SESSION['2014probox'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>2014probox.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
			</div>
			<pre>
			<?php print_r($probox2014[0]); ?>
			</pre>
		</div>
		</div>
		
		
		<!-- change -->
		
		<?php
		get_cache('2014pbbox', 0);	
		$pbbox2014 = $_SESSION['2014pbbox'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>2014pbbox.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
			</div>
			<pre>
			<?php print_r($pbbox2014[0]); ?>
			</pre>
		</div>
		</div>
		
		
		</div>
		<!-- row -->
		<div class="row">
		
		<!-- change -->
		
		<?php
		get_cache('2014plbox', 0);	
		$plbox2014 = $_SESSION['2014plbox'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>2014plbox.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
			</div>
			<pre>
			<?php print_r($plbox2014[0]); ?>
			</pre>
		</div>
		</div>
		
		<!-- change -->
		
		<?php
		get_cache('champions', 0);	
		$champions = $_SESSION['champions'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>champions.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Associative by year</a>
				<a class="list-group-item list-item-sm" href="#">/cache</a>
			</div>
			<pre>
			<?php print_r($champions[2000]); ?>
			</pre>
		</div>
		</div>
		
		<!-- change -->
		
		<?php
		get_cache('standings/stand2014', 0);	
		$stand2014 = $_SESSION['standings/stand2014'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>stand2014.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed, then associative by category</a>
				<a class="list-group-item list-item-sm" href="#">/cache/standings</a>
				<a class="list-group-item list-item-sm" href="#">Note: home / away record for 2013 and 2014 have not been added to database yet.  </a>
			</div>
			<pre>
			<?php print_r($stand2014[0]); ?>
			</pre>
		</div>
		</div>

		</div>
		
		<!-- row -->
		<div class="row">
		
		<!-- change -->
		
		<?php
		get_cache('playerdata/1991AikmQB', 0);	
		$playerdata = $_SESSION['playerdata/1991AikmQB'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>1991AikmQB.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache/players</a>
				<a class="list-group-item list-item-sm" href="#">Note: All players 'boxscore' data by week id.</a>
				<a class="list-group-item list-item-sm" href="#">Derived:  allplayerdata.txt stitches all of the players individual boxscore data into one big array.</a>
			</div>
			<pre>
			<?php print_r($playerdata[0]); ?>
			</pre>
		</div>
		</div>
		
			<!-- change -->
		
		
		<?php
		get_cache('boxscores/201414box', 0);	
		$boxscore = $_SESSION['boxscores/201414box'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>201414box.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache/boxscores</a>
				<a class="list-group-item list-item-sm" href="#">Note: All players 'boxscore' data by week id.</a>
			</div>
			<pre>
			<?php print_r($boxscore[0]); ?>
			</pre>
		</div>
		</div>
		
			<!-- change -->
		
		
		<?php
		get_cache('rankyears/alltimeleaders_WR', 0);	
		$bleaders = $_SESSION['rankyears/alltimeleaders_WR'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>201414box.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache/rankyears</a>
				<a class="list-group-item list-item-sm" href="#">Note: Builds an alltimeleaders_XX.txt file for each position.  Files for wr2014.txt were built in old build scripts and need to be updated to new build scripts.  These files show all player leaders up to that year (1991 - that year's file).  Still need to produce leader arrays for each individual season.  That should also be in the same build script.</a>
			</div>
			<pre>
			<?php print_r($boxscore[0]); ?>
			</pre>
		</div>
		</div>
		
		</div>
		
		<!-- row -->
		<div class="row">
		
		<!-- change -->
		
		<?php
		get_cache('mfl/linkidcache', 0);	
		$linkid = $_SESSION['mfl/linkidcache'];
		?>

		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>linkidcache.txt</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed</a>
				<a class="list-group-item list-item-sm" href="#">/cache/mfl</a>
				<a class="list-group-item list-item-sm" href="#">Pulls player IDs from the MFL API and matches names of current players with the list of all PFL players, then creates an array with both IDs.</a>
				
			</div>
			<pre>
			<?php print_r($linkid['2842']); ?>
			</pre>
		</div>
		</div>
		
		

		
		<!-- change -->
		
		<?php

		get_cache('fantasydata/fantasydataplayers', 0);	
		$fantasydataplayers = $_SESSION['fantasydata/fantasydataplayers'];

		?>


		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>fantasydataplayers.txt, fantasydataids.txt, player details </h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">Indexed & Assoc</a>
				<a class="list-group-item list-item-sm" href="#">/cache/fantasydata</a>
				<a class="list-group-item list-item-sm" href="#">Gets players and player details from fantasy data API.  Only current nfl players. fantasyplayers.txt is a list of all players indexed. fantasydataids.txt is accociated by player id.  Individual player files are named by playerid and have all available player details cached.</a>
				
			</div>
			<?php printr($fantasydataplayers[0], 0); ?>
		</div>
		</div>
		
		<?php
		get_cache('mfl/allstarters', 0);	
		$starters = $_SESSION['mfl/allstarters'];
		?>
		
		
		<div class="col-md-8">
		<div class="panel-body panel-colorful panel-success">
			<h3>allstaters.txt, mflteamids.txt, mfl-every-player.txt, rookie insert statement</h3>		
			<div class="list-group">
				<a class="list-group-item list-item-sm" href="#">CSV format in txt file</a>
				<a class="list-group-item list-item-sm" href="#">cache/mfl/</a>
				<a class="list-group-item list-item-sm" href="#">Lists the mfl player IDs of all players who scored points in a given season.  Also prints out an INSERT statement on page that includes lists all rookies.  This insert statement will add these new players to 'players' table.  It will also build a small assoc array that matches MFL team IDs with PFL Team IDS.</a>
				
			</div>
			<pre>
				<p>10297,10708,10261,11947,9099...</p>
			</pre>
		</div>
		</div>

	
	
	
	
	
	
		
		</div>


		
		
	</div>
	
	
	
</div>


<?php
// end the loop
endwhile; wp_reset_query();
?>
<?php get_footer(); ?>
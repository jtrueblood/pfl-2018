<?php
/*
 * Template Name: Data Lookup 
 * Description: A place to print out arrays for lookup
 */
 ?>


<?php get_header(); ?>


<?php 


$year = 2015;
$lid = 47099;


get_cache('mfl/linkidcache', 0);	
$linkidcache = $_SESSION['mfl/linkidcache'];

get_cache('players', 0);	
$players = $_SESSION['players'];

get_cache('standings/stand2015', 0);	
$stand2015 = $_SESSION['standings/stand2015'];

get_cache('playerdata/1991RiceWR', 0);	
$playersample = $_SESSION['playerdata/1991RiceWR'];

get_cache('playersid', 0);	
$playersid = $_SESSION['playersid'];

get_cache('playersassoc', 0);	
$playersassoc = $_SESSION['playersassoc'];

?>




<div id="page-content" class="add-to-top">
	<div class="row">
	<div class="col-xs-12">

		<div class="tab-base tab-stacked-left">
							
			<!--Nav tabs-->
			<ul class="nav nav-tabs">
				<li class="">
					<a data-toggle="tab" href="#demo-stk-lft-tab-5" aria-expanded="true">Push to Production</a>
				</li>
				<li class="active">
					<a data-toggle="tab" href="#demo-stk-lft-tab-1" aria-expanded="false">Players</a>
				</li>
				<li class="">
					<a data-toggle="tab" href="#demo-stk-lft-tab-2" aria-expanded="false">ID Links</a>
				</li>
				<li class="">
					<a data-toggle="tab" href="#demo-stk-lft-tab-3" aria-expanded="true">Standings</a>
				</li>
				<li class="">
					<a data-toggle="tab" href="#demo-stk-lft-tab-4" aria-expanded="true">Sample Player</a>
				</li>
				
			</ul>
		
			<!--Tabs Content-->
			<div class="tab-content">
				<div id="demo-stk-lft-tab-5" class="tab-pane fade active in">
					<h4 class="text-thin">Push from Dev to Production</h4>
					<?php 
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post(); 
							the_content();
						} 
					} 
					?>
				</div>
				<div id="demo-stk-lft-tab-1" class="tab-pane fade">
					<h4 class="text-thin">All Players</h4>
					<?php printr($players, 0);?>
				</div>
				<div id="demo-stk-lft-tab-2" class="tab-pane fade">
					<h4 class="text-thin">PFL/MFL Linked IDs</h4>
					<?php printr($linkidcache, 0);?>
				</div>
				<div id="demo-stk-lft-tab-3" class="tab-pane fade">
					<h4 class="text-thin">2015 Standings</h4>
					<?php printr($stand2015, 0);?>
				</div>
				<div id="demo-stk-lft-tab-4" class="tab-pane fade">
					<h4 class="text-thin">1991RiceWR</h4>
					<?php printr($playersample, 0);?>
				</div>
			</div>
		
		</div>
	
	</div>
	</div>
</div>



<?php get_footer(); ?>
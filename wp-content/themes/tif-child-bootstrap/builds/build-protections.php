<?php
/*
 * Template Name: Build Protections 
 * Description: Master Build for generating a master 'protections.txt'  */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 


$protectcache = 'http://posse-football.dev/wp-content/themes/tif-child-bootstrap/cache/protections.txt';

/*
$deletecache = 0;
if ($deletecache == 1){
	fopen($protectcache,"wa+");
}
*/

if (file_exists($protectcache)){
	
	$protectget = file_get_contents($protectcache, FILE_USE_INCLUDE_PATH);
	$protectdata = unserialize($protectget);
	$protectcount = count(array_keys($protectdata));	

				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');
	$protectquery = $mydb->get_results("select * from protections", ARRAY_N);
	
	$buildnew = array();
	foreach ($protectquery as $revisequery){
		$buildnew[] = array(
			$revisequery[6], 
			$revisequery[1], 
			$revisequery[4], 
			$revisequery[5],  
			$revisequery[0]);
	}
	
	$putprotect = serialize($buildnew);
	file_put_contents($protectcache, $putprotect);
	

	
}
?>
<!--CONTENT CONTAINER-->
<div class="boxed">

	<div id="content-container">
	
		<div id="page-content">
		
			<div class="row">
		
<?php


echo '<div class="col-xs-8"><pre>';
	echo '<h5>File Exists -- protections.txt From Cache...</h5>';
	print_r($protectdata);
echo '</pre></div>';


echo '<div class="col-xs-8"><pre>';
	echo '<h5>protections.txt Written From MySQL...</h5>';
	print_r($buildnew);
echo '</pre></div>';

?>

			</div>
		</div>
	</div>
</div>


<?php get_footer(); ?>
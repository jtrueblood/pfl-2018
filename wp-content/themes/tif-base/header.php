<!DOCTYPE html>
	<html lang="en">
	<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
	<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
	<!--[if IE 8]> <html class="no-js lt-ie9"> <![endif]-->
	<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->

	<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel='icon' href='favicon.ico'>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title><?php wp_title(' | ', true, 'right'); ?></title>
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
		
	<!--[if IE 8]>
	<script type="text/javascript">
	    window.location = "/older.html";
	</script>
	<![endif]-->
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="<?php bloginfo('template_directory');?>/js/html5shiv/html5shiv.min.js" type="text/javascript"></script>
	<script src="<?php bloginfo('template_directory');?>/js/respond/respond.js" type="text/javascript"></script>
	<![endif]-->

	<?php wp_head(); ?>  
	
	
	<!-- Analytics Code -->


	<!-- provides page name and appends as body class -->
	<?php 
		$page = strtolower(get_the_title()); 
		$path = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
		$path_clean = basename($path[0], '.php');
		date_default_timezone_set('America/New_York');
		$year = date("Y");
	?>  
	
</head>

<body <?php body_class($page); ?> style="height:auto;">
<?php $classes = get_body_class(); ?>	

<div class="container">
<?php wp_nav_menu( array( 'theme_location' => 'header_navigation' ) ); ?>
</div>

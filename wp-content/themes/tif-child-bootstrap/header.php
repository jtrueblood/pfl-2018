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

	<!-- provides page name and appends as body class -->
	<?php 
	$page = strtolower(get_the_title()); 
	$path = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
	$path_clean = basename($path[0], '.php');
	date_default_timezone_set('America/New_York');
	$year = date("Y");
	$mflleagueid = 49077;
	
	//NEW Google Cloud Database used with ASO Hybrid
	//$mydb = new wpdb('root','eur0TRASH','pflmicro','173.194.240.57');
		
	?>
	
	
</head>

<body <?php body_class($page); ?> class="nifty-ready pace-done">
<?php $classes = get_body_class(); ?>	
<div id="container" class="effect slide mainnav-out">
 <!--NAVBAR-->
		<!--===================================================-->
		<header id="navbar">
			<div id="navbar-container" class="boxed">

				<!--Brand logo & name-->
				<!--================================-->
				<div class="navbar-header">
					<a href="/" class="navbar-brand">
						<img src="<?php echo get_stylesheet_directory_uri();?>/img/pfl-mini.png" alt="Nifty Logo" class="brand-icon">
						<div class="brand-title">
							<span class="brand-text mar-lft">Posse Football League</span>
						</div>
					</a>
				</div>
				<!--================================-->
				<!--End brand logo & name-->


				<!--Navbar Dropdown-->
				<!--================================-->
				<div class="navbar-content clearfix">
					<ul class="nav navbar-top-links pull-left">

						<!--Navigation toogle button-->
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<li class="tgl-menu-btn">
							<a class="mainnav-toggle slide" href="#">
								<i class="fa fa-navicon fa-lg"></i>
							</a>
						</li>
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<!--End Navigation toogle button-->
					</ul>
					
					<div class="min-nav hidden-xs hidden-sm">
						<a href="/leaders">Leaders</a>
						<a href="/drafts/?id=1991">Drafts</a>
						<a href="/champions/">Champions</a>
					</div>
					
					<ul class="nav navbar-top-links pull-right">

						<!--User dropdown-->
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<?php  $current_user = wp_get_current_user(); ?>
						
						<li id="dropdown-user" class="dropdown">
							<a href="#" data-toggle="dropdown" class="dropdown-toggle text-right">
								<span class="pull-right">
								<?php if ( is_user_logged_in() ) { ?>
									<img class="img-circle img-user media-object" src="
										<?php echo get_stylesheet_directory_uri();?>/img/mini-icons/<?php echo $current_user->user_lastname;?>.png" alt="Profile Picture">
								</span>
							
								<?php
									} 
								?>
								
								

								<div class="username hidden-xs"><?php echo $current_user->user_firstname;?></div>
							</a>


							<div class="dropdown-menu dropdown-menu-md dropdown-menu-right with-arrow panel-default">

								<!-- Dropdown heading  -->
								<div class="pad-all bord-btm">
									<p class="text-lg text-muted text-thin mar-btm">750Gb of 1,000Gb Used</p>
									<div class="progress progress-sm">
										<div class="progress-bar" style="width: 70%;">
											<span class="sr-only">70%</span>
										</div>
									</div>
								</div>


								<!-- User dropdown menu -->
								<ul class="head-list">
								<li>
								<a href="#">
								<i class="fa fa-user fa-fw fa-lg"></i> Profile
								</a>
								</li>
								<li>
								<a href="#">
								<span class="badge badge-danger pull-right">9</span>
								<i class="fa fa-envelope fa-fw fa-lg"></i> Messages
								</a>
								</li>
								<li>
								<a href="#">
								<span class="label label-success pull-right">New</span>
								<i class="fa fa-gear fa-fw fa-lg"></i> Settings
								</a>
								</li>
								</ul>

								<!-- Dropdown footer -->
								<div class="pad-all text-right">
									<a href="pages-login.html" class="btn btn-primary">
										<i class="fa fa-sign-out fa-fw"></i> Logout
									</a>
								</div>
							</div>
						</li>
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
						<!--End user dropdown-->

					</ul>
				</div>
				<!--================================-->
				<!--End Navbar Dropdown-->
			</div>
		
		</header>
		<!--===================================================-->
		<!--END NAVBAR-->
	



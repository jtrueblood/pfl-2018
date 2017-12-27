<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WordPress_Plugin_Template {

	/**
	 * The single instance of WordPress_Plugin_Template.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'wordpress_plugin_template';
		$this->base = 'wpt_';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new WordPress_Plugin_Template_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		add_action('template_redirect', array(&$this, 'template_redirect'));

		add_action('wp_logout',array(&$this, 'go_home'));

		remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );

		add_action('login_head', array(&$this,'custom_login_logo'));

		add_filter( 'login_headertitle',array(&$this,'my_login_logo_url_title') );
		
		//add_action('add_menu_classes', array(&$this,'notification_bubble_in_admin_menu') );

		// wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		
		// wp_enqueue_script( $this->_token . '-frontend' );


	} // End __construct ()




function custom_login_logo() {
	$this->base = 'wpt_';
	$image_id = get_option( $this->base.'text_field_minilogo' );
	$image_url = wp_get_attachment_url( $image_id );
    echo '<style type="text/css">
        h1 a { background-image:url('.$image_url.') !important;
       margin-top: 30px;

     }

    </style>';
}

function my_login_logo_url_title() {
    return 'Logo';
}




	function go_home(){
	  wp_redirect( site_url() );
	  exit();
	}
	

	public function apply_custom_template( $tag, $replace_with, $content ){
		$found = false;
		if ( $replace_with ){
			if ( strlen($replace_with) > 0 ){
				$found = true;
			}
		}
		if ( $found ){
			$content = str_replace("%$tag%", $replace_with , $content);
		}else{
			$content = str_replace("%$tag%", "" , $content);
		}
		return $content;
	}
	
	function css_maker(){
		$content = file_get_contents($this->assets_dir.'/css/loginpagev2.css');
		// print $this->assets_dir.'css/loginpage.css';
		header("Content-type: text/css", true);
		header("X-Content-Type-Options: nosniff");
		$content = apply_filters($this->_token.'whitelist_login_styles',$content);
		print $content;
	}

	function login_page($type){
		if ($type==1)
			$file = "login_page.html";
		else
			$file = "login_page_full.html";
		$content = file_get_contents(plugin_dir_path( __FILE__ ).$file);
		$this->base = 'wpt_';
		$image_url = get_option( $this->base.'text_field' );
		$image='';
		if (strlen($image_url)>0){
			$image = wp_get_attachment_image( $image_url,'full' );
		}
		// $captcha_secret = get_option( $this->base.'text_field_captcha_secret' );
		$captcha_site = get_option( $this->base.'text_field_captcha_site' );
		$lost_pass_link =  get_option( $this->base.'text_field_redirect' );
		$captcha_placeholder = '<p><center><div id="vf"></div></center></p>';
		$titlesite = get_option( $this->base.'text_field_titlesite' );
		$content = str_replace("%image%", $image, $content);
		$enable_captcha = get_option( $this->base.'checkbox_captcha' );
		
		if ($enable_captcha == "on"){
			$this->add_captcha();
					// print_r($enable_captcha);
		// exit();
			$captcha = $_SESSION['captcha'];
			$img = $captcha['image_src'];
			
			$content = str_replace("%captchaplaceholder6%", '		<center><img src="%captchaplaceholder5%" class="captcha" alt="CAPTCHA code" style="display:none;"></center>	<p>
		<label for="user_captcha" id="captcha_label" %labelstyle%>Captcha<br />
		<input type="text" name="user_captcha" id="user_captcha" class="input" value=""  /></label>
	</p>' , $content);
			$content = str_replace("%captchaplaceholder5%", $img, $content);
		}else{
			$content = str_replace("%captchaplaceholder5%", "" , $content);
			$content = str_replace("%captchaplaceholder6%", "" , $content);
		}

			// print_r($captcha);
			// exit();
		
		// $content = str_replace("%captcha_secret%", $captcha_secret, $content);
		$content = str_replace("%captcha_site%", $captcha_site, $content);		
		$content = str_replace("%titlesite%", $titlesite, $content);
		$sitemessage = get_option( $this->base.'text_field_roadblockmessage' );
		if (strlen($sitemessage)> 0){
			$content= str_replace("%sitemessage%", $sitemessage, $content);
		}else{
			$content= str_replace("%sitemessage%", "", $content);
		}
		$css_url = esc_url( trailingslashit( site_url( '/') ) );;
		$css_url = "<link rel='stylesheet' type='text/css' href='$css_url?FSEG-Roadblock&loadstyles'>";
		$content = apply_filters($this->_token.'whitelist_login_page',$content);
		$content = $this->apply_custom_template("stylesheeturl", $css_url, $content);
		$content = $this->apply_custom_template("custombodystyletag", 'style=""', $content);
		$content = $this->apply_custom_template("custombodystyle", '', $content);
		$content = $this->apply_custom_template("labelstyle", '', $content);
		$content = $this->apply_custom_template("labellostpassstyle", '', $content);

		$content = $this->apply_custom_template("loginbtnstyle", '', $content);


		if ((get_option($this->base."checkbox_enableaccounts")!="on")){
			$content = str_replace("%lost_pass_link%", $lost_pass_link, $content);
		}else{
			$content = str_replace("Lost your password?", "", $content);
		}
		print $content;
		// print "Login Page to load here ";
	}

	function user_validator(){
		return is_user_logged_in() ;
	}

	function is_on_list(){
		global $wpdb;
		$table_name = $wpdb->prefix . "authusers"; 
		$form_email = $_POST["user_email"];

		$form_email = filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL); 
		$form_email = strtolower($form_email);

		$enable_hardmode = get_option( $this->base.'checkbox_enablehardmode' );

		if ( $enable_hardmode == "on" ){
			if( email_exists( $form_email )) {
				return true;
			}else{
				return false;
			}
		}

		$db_email = $wpdb->get_var( $wpdb->prepare( 
		 "
		  SELECT email 
		  FROM $table_name 
		  WHERE LOWER(email) = %s
		 ", 
		 	$form_email
		 ));

		if ($db_email==$form_email){
			return true;
		}



		return false;
	}

	function custom_login($data) {
		$creds = array();
		$creds['user_login'] = $data['user_login'];
		$creds['user_password'] = $data['user_password'];
		$creds['remember'] = false;
		$user = wp_signon( $creds, false );
		if ( is_wp_error($user) )
			return $user->get_error_message();
	}

	function verify_captcha(){
		$this->base = 'wpt_';
		// include_once('lib/recaptchalib.php');
		$secret = get_option( $this->base.'text_field_secret' );
		if (strlen($secret) == 0)
			return true;
		// print $secret;
		$input = $_POST["g-recaptcha-response"];
		$post_data = array('response'=>$input, 'secret'=>$secret);
	    $curlPost = http_build_query($post_data, '', '&');

	    $ch = curl_init();

	    //Set the URL of the page or file to download.
	    $url = 'https://www.google.com/recaptcha/api/siteverify';

	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	    

	    $data_json = curl_exec($ch);


        // print ($data_json);
        // var_dump($data_json);
        $res = json_decode($data_json, TRUE);
        if($res['success'] == 'true') 
            return TRUE;
        else
            return FALSE;
	}

	function verify_captcha_local(){
		session_start();
		$enable_captcha = get_option( $this->base.'checkbox_captcha' );


		if ($enable_captcha != "on"){
			return true;
		}
		if ($this->is_on_list()){
			if ($_POST["step"]==2){
				if (isset($_POST["user_password"]) && strlen($_POST["user_password"])> 0){
					// if( !email_exists( $_POST["user_email"] )) {
					$user = get_user_by( 'email', $_POST["user_email"]);
					// print_r($user);
					// exit();
					if (!$user ){
						return true;
					}
				}
			}
		}
		// return false;
		$captcha = $_SESSION['captcha'];
		// print "a";
		$code = $captcha['code'];
		$user_input = $_POST['user_captcha'];
		// print_r($_SESSION);
		if ($user_input == $code){
			return true;
		}
		return false;
	}

	function add_captcha(){
		session_start();
		include_once('lib/simple-php-captcha/simple-php-captcha.php');
		$_SESSION['captcha'] = simple_php_captcha();
	}

	function login_without_email(){
		$this->base = 'wpt_';
		$user_name = "guest930489_whitelist";
		$password = get_option($this->base . "generate_sec");
		$email = "guest930489_whitelist@guest930489whitelist.bot";
	   	$user = wp_create_user( $user_name, $password, $email );
	   	if ( is_wp_error($user) ){
			$user = get_user_by( 'email', $email );
	        $data = array('user_login' => $user->user_login, 'user_password' => $password);
	        $msg = $this->custom_login($data);
			// rgeteturn $user->get_error_message();
	   	}else{
	   		$user_default_role = "client";
			$user_id = wp_update_user( array( 'ID' => $user, 'role' => $user_default_role ) );
			$data = array('user_login' => $user_name, 'user_password' => $password);
    		$msg = $this->custom_login($data);
	   	}
	   	return $msg;

	}

	function reload_captcha(){
		$this->add_captcha();
		$captcha = $_SESSION['captcha'];
		$img = $captcha['image_src'];
		print $img;

	}

	function prepare_page($content, $msg=""){
		$this->base = 'wpt_';
		$image_url = get_option( $this->base.'text_field' );
		$image='';
		if (strlen($image_url)>0){
			$image = wp_get_attachment_image( $image_url,'full' );
		}
		if (strlen($msg)>0){
			$f = '<p id="message" class="message">'.$msg.'</p>';
			$content = str_replace("%form_notification_area%", $f, $content);
		}else{
			$content = str_replace("%form_notification_area%", "", $content);
		}
		$lost_pass_link =  get_option( $this->base.'text_field_redirect' );
		$titlesite = get_option( $this->base.'text_field_titlesite' );
		$request_message = get_option( $this->base.'text_field_request_message' );
		$content = str_replace("%image%", $image, $content);
		$content = str_replace("%lost_pass_link%", $lost_pass_link, $content);
		$content = str_replace("%titlesite%", $titlesite, $content);
		// $settings_array = $this->settings;
		if (strlen($request_message)==0){

			$request_message = $this->settings->settings['standard']["fields"][6]['default'];

		}
		$content = str_replace("%request_message%", $request_message, $content);
		return $content;
	}



	function template_redirect(){
		$this->base = 'wpt_';
		$this->handle_updates();


		// print $this->is_on_list();
		// wp_logout_url( get_permalink() ); 

		if (isset($_GET["FSEG-Roadblock"])){
			if (isset($_GET["loadstyles"])){
				$this->css_maker();
				exit();
			};
			$data = "";
			$data = apply_filters($this->_token.'whitelist_teredirect',$data);
			if ($data=="exit"){
				exit();
			}
			if (isset($_GET["captchareload"])){
				$this->reload_captcha();
				exit();
			}
			if (isset($_POST["step"])){
				$step = $_POST["step"];
				if ($step == 1){

					if ($this->is_on_list()){
						// Redirect to password page
						$enableaccounts = get_option( $this->base.'checkbox_enableaccounts' );
						$arr = array();
						$email = $_POST["user_email"];
						if ($enableaccounts){
							// if( email_exists( $email )) {
								$res = $this->login_without_email();
								if (!$res){
									$url = get_site_url(); 
									$arr ["message"]="<a href='$url'>Logged in! Click here to continue...</a>";
					        		$arr ["user_message"]="Redirecting you to our homepage...";
								}else{
									$arr ["message"]=$res;
									$arr ["user_message"]=$res;

								}

					        	
							// }

						}else{

							if( email_exists( $email )) {
					        	$arr ["message"]="ONLISTANDUSER";
					        	$arr ["user_message"]="Please enter your existing password to login.";


							}else{
					        	$url = get_site_url(); 
					        	$arr ["message"]="ONLIST";
					        	$arr ["user_message"]="Please enter your new password";
					        	// $arr ["url"] = $url;
							}

						}

					}else{
						$arr ["message"]="Your email is not permitted on this network";
						// No cookie for you redirect this user to block 
						// page with error message
					}
					header('Content-Type: application/json');
					echo json_encode($arr);
				}else if ($step == 2){
					$email = $_POST["user_email"];
					$password = $_POST["user_password"];
					if (isset($_POST["user_captcha"])){
						if (!$this->verify_captcha_local()){
							header('Content-Type: application/json');
						        	$url = get_site_url(); 
						        	$arr = array(
						        		'message' => 'Incorrect Captcha', 
						          		'user_message' => 'Incorrect Captcha'
						        	);
									echo json_encode($arr);

							exit();
						}
					}
					
					// exit();
					if( email_exists( $email )) {
						// print "Email Exists";
				        // Login user into wordpress then send redirect url
				        if ($this->is_on_list()){
					        $user = get_user_by( 'email', $email );
					        $data = array('user_login' => $user->user_login, 'user_password' => $password);
					        $msg = $this->custom_login($data);
					        if (!$msg){
					        	header('Content-Type: application/json');
					        	$url = get_site_url(); 
					        	$arr = array(
					        		'message' => 'SUCCESS', 
					        		'url' => $url, 
					        		'user_message' => 'Redirecting you to our homepage...'
					        	);
								echo json_encode($arr);
					        	// print $url;
					        }else{
					        	$findme = "Lost your password";
					        	$pos = strpos($msg, $findme);
					        	$link = get_option( $this->base.'text_field_redirect' );
								if ($pos === false) {
								} else {
								    $msg= "Incorrect Password. <a href='".$link."'>Lost your password?</a>";
								}
					        	header('Content-Type: application/json');
					        	$arr = array(
					        		'message' => $msg, 
					        		
					        	);
					        	echo json_encode($arr);
					        }
				    	}else{
				    		$msg = "Your email is not permitted on this network";
				    		header('Content-Type: application/json');
					        	$arr = array(
					        		'message' => $msg, 
					        		
					        	);
					        echo json_encode($arr);
				    	}
				    }else{
				    	$email = $_POST["user_email"];
						$password = $_POST["user_password"];
						// $user_name = $_POST["user_login"];
						$result = '';
						$string = $email;
						for($i=0; $i<strlen($string); $i++){
							$cur = $string[$i];
							if (ctype_alnum($cur) || $cur == '-'){
								$result .= $cur;
							}
						}
						$user_name = $result;
						// print $result;
						$user_id = username_exists( $user_name );
				    	if (!$user_id){
				    		if(!email_exists( $email )) {
				    			if ($this->is_on_list()){
						    		$user = wp_create_user( $user_name, $password, $email );
						    		$user_default_role = "client";
						    		$user_id = wp_update_user( array( 'ID' => $user, 'role' => $user_default_role ) );
						    		$data = array('user_login' => $user_name, 'user_password' => $password);
						        	$msg = $this->custom_login($data);
						        	if (!$msg){
							        	header('Content-Type: application/json');
							        	$url = get_site_url(); 
							        	$user_message="Account Created. Logging you in...";
							        	$arr = array(
							        		'message' => 'SUCCESS', 
							        		'url' => $url, 
							        		'user_message' => $user_message,
							        	);
										echo json_encode($arr);
							        	// print $url;
							        }else{
							        	header('Content-Type: application/json');
							        	$arr = array(
							        		'message' => $msg, 
							        		
							        	);
							        	echo json_encode($arr);
							        }
							    }else{
							    	$msg = "Your email is not permitted on this network";
							    	header('Content-Type: application/json');
							        	$arr = array(
							        		'message' => $msg, 
							        		
							        	);
							        echo json_encode($arr);
							    }
					    	}else{
					    		$msg = "The Email 've entered already belongs to another user. Kindly enter a different Email.";
				    			header('Content-Type: application/json');

					        	$arr = array(
					        		'message' => $msg, 
					        		
					        	);

					        	echo json_encode($arr);
					    	}
				    	}else{
				    		$msg = "The Username you've entered already belongs to another user. Kindly enter a different username.";
				    		header('Content-Type: application/json');
					        	$arr = array(
					        		'message' => $msg, 
					        		
					        	);
					        	echo json_encode($arr);
				    		// Inform user that their selected username already exists.
				    	}
				    	// Create Wordpress account for email
				    }
					// Verify credentials 
					// Next check if email has account already
					// Yes:
					// Login
					// No:
					// Create account for email
					// Login
				}
			}else{
				$this->login_page(1);
				
			}
			exit();
		}
		if ( $this->user_validator() ) {
			// echo 'Welcome, registered user!';
		} else {
			$url = get_site_url().'/?FSEG-Roadblock';
			print $url;
			wp_redirect( $url );
		}

		
		   
  			// exit();
	}
	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '' ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new WordPress_Plugin_Template_Post_Type( $post_type, $plural, $single, $description );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new WordPress_Plugin_Template_Taxonomy( $taxonomy, $plural, $single, $post_types );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		$user = wp_get_current_user();
		$roles = $user->allcaps ;

		if ( isset ( $roles["client"] ) ){

			$logout_link =  wp_logout_url();
			$js = "<script>var whitelist_logout_link = '$logout_link'</script>";
			print $js;
			// show_admin_bar( true );
			wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
			wp_enqueue_script( $this->_token . '-frontend' );
		}

	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'wordpress-plugin-template', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'wordpress-plugin-template';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main WordPress_Plugin_Template Instance
	 *
	 * Ensures only one instance of WordPress_Plugin_Template is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WordPress_Plugin_Template()
	 * @return Main WordPress_Plugin_Template instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */

	public function generate_sec(){
		$key = "4583745";
		$time = time();
		$hash = md5($key . $time);
		$hash = substr($hash, -50);
		return $hash;
	}

	public function handle_updates(){
		$version = get_option( $this->_token . '_version' );
		if ( $version ){
			if ( floatval($version) < floatval($this->_version) ){
				$this->update_stuff();
			}
		}else{
			$this->update_stuff();
		}

	}

	public function update_stuff(){
		$this->base = "wpt_";
		$sec = $this->generate_sec();
		update_option( $this->base . "generate_sec", $sec );
		$user_default_role = "client";
		$whitelistusers = get_users( 'role='.$user_default_role );
		// Array of WP_User objects.
		foreach ( $whitelistusers as $wuser ) {
			// print $sec;
			// exit();
			$user_id = $wuser->ID;
			$a = wp_set_password( $sec,  $user_id  );
			// print $a;
			// exit();
		}
		$this->_log_version_number();
		// Add logging code here
	}

	public function install () {
		global $wpdb;
		$this->base = "wpt_";

		$this->handle_updates();
		$val = "Please use the form below to request access to this site";
		update_option( $this->base.'text_field_roadblockmessage',  $val);
		$this->_log_version_number();
		$table_name = $wpdb->prefix . "authusers"; 
		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  email varchar(500) DEFAULT '' NOT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		$table_name = $wpdb->prefix . "authusers_pending"; 
		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  email varchar(500) DEFAULT '' NOT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";
		dbDelta( $sql );
		
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
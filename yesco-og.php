<?php
/*
Plugin Name: Yes-co ORES
Plugin URI: http://yes-co.nl
Description: Publiceert uw onroerend goed op uw Wordpress Blog
Version: 1.0.1
Author: Karst Lok | Motivo
Author URI: http://motivo.nl
License: GPL2
*/
	// start sessie
	session_start();
	
	// Determine plugin directory
	if (!defined('YOG_PLUGIN_DIR'))
		define('YOG_PLUGIN_DIR', dirname(__FILE__));
	
	if (!defined('YOG_PLUGIN_URL'))
		define('YOG_PLUGIN_URL', plugins_url(null, __FILE__));
	
	// variabelen
	$GLOBALS['yesco_og']['imagepath'] = YOG_PLUGIN_URL .'/media/images/';
	
	// Functies includes
	include('yesco-og-functions.php');
	include('yesco-og-functions-extra.php');
	add_theme_support('post-thumbnails');
	
	/*
	admin_action_update
	*/ 
	// Opties van plugin registreren
	add_action('admin_menu', 'yesco_OG_optiemenu');
	
	// Meta boxes toevoegen
	//add_action('admin_menu', 'add_yog_meta_boxes');
	// Widgets toevoegen
	add_action('widgets_init', 'yog_registerWidgets');
	// Registreer Custom posttype voor Huis
	add_action( 'init', 'yog_registerPT' );
	// Categorieen
	add_action( 'init', 'yog_registerCategories' );
	// Openhuizen updaten
	yog_update_openhuizen();
	// Single-huis toevoegen als dat nodig blijkt
	yog_install_singleTemplate();
	// Publiceren op home-page als dat nodig blijkt
	add_filter( 'pre_get_posts', 'yog_publish_homepage' );
	// Altijd opnemen in feed
	add_filter('pre_get_posts', 'yog_registerPTFeed');

	
	// Controleer of we moeten installeren
	if(!yesco_OG_pluginInstalled())
		include('yesco-og-install.php');
	
	// Controleer of we moeten activeren
	if(isset($_GET['action']) && $_GET['action'] == 'activate_yesco_og')
		include('yesco-og-activate.php');
	
	// Controleer of we moeten syncen
	if(isset($_GET['action']) && $_GET['action'] == 'sync_yesco_og')
		include('yesco-og-sync.php');	
	
	// JavaScript yesco_og.js en jquery-1.4.1 toevoegen
	wp_enqueue_script('jquery', YOG_PLUGIN_URL . '/javascript/' .'jquery-1.4.1' .'.js');
	wp_enqueue_script('yesco_og', YOG_PLUGIN_URL .'/javascript/' .'yesco_og' .'.js');
	if (!is_admin())
	{
		// Add js files needed for site (not admin)
		wp_enqueue_script('yog-image-slider', YOG_PLUGIN_URL .'/inc/js/image_slider.js');
	}else{
		// Add js files neede for admin
		
	}
	
	// Ajax functies toevoegen
	include('yesco-og-ajax.php');
?>

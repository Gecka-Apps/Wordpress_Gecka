<?php
/*
 * Plugin Name: Gecka Library
 * Plugin URI: http://gecka-apps.com/wordpress-plugins/gecka-library
 * Description: A usefull library of classes and additions for plugin developpement
 * Version: 1.0-beta1
 * Author: Gecka
 * Author URI: http://gecka.nc
 * License: GPL2+
 * Text Domain: gecka
 * Domain Path: /Languages/
 */

// version
define( 'GK_VERSION', '1.0-beta1');

// absolute path
define( 'GK_PATH', dirname(__FILE__) );

// url
define( 'GK_URL', plugins_url('', __FILE__) );

// Load the textdomain
load_plugin_textdomain('gecka', false, dirname(plugin_basename(__FILE__)) . '/Languages');

// require PHP 5
function gecka_activation_check() {
	
    if (version_compare(PHP_VERSION, '5.2.4', '<')) {
        deactivate_plugins( basename(dirname(__FILE__)) . '/' . basename(__FILE__) ); // Deactivate ourself
        wp_die( sprintf( __("Sorry, the Gecka Library requires PHP 5.2.4 or higher. You use PHP %s. Ask your host how to enable PHP 5.2.4 or higher as the default on your servers.", 'gecka'), PHP_VERSION) );
    }
    
}
register_activation_hook(__FILE__, 'gecka_activation_check');

// ensure PHP5 code doesn't throw an error on PHP4 installs
if ( ! version_compare(PHP_VERSION, '5.2.4', '<') ) {

	/**
	 * Gecka library init hook
	 * You can use the gecka-init action to check that the Gecka Library is 
	 * installed and active
	 */
	function gecka_init() {
    	do_action( 'gecka-init' );
	}
	add_action( 'plugins_loaded', 'gecka_init', -50 );

	require_once 'gecka.class.php';
	$Gecka = Gecka::instance(GK_PATH);

}

function gecka_version () {
	    return GK_VERSION;
}

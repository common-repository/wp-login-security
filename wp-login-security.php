<?php
/*
 Plugin Name: WP Login Security
 Plugin URI: http://www.joshuascott.net/projects/wploginsecurity/
 Description: Allows users to setup IP whitelists for logging in and sends them an email with an activation key if the IP address is not on the whitelist.
 Version: 0.1.2
 Author: Joshua Scott
 Author URI: http://www.joshuascott.net
 License: GPL2
 */

/*  Copyright 2010  Joshua Scott  (email : joshua@joshuascott.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
define('WPLS_VERSION', '0.1.2');
define('WPLS_DATE_FORMAT', 'm-d-Y h:i');

$wpls_options['whitelist'] = "wploginsecurity_ip_whitelist";
$wpls_options['options']   = "wploginsecurity";

// Include necessary libraries
include( dirname(__FILE__) . '/wpls-options-page.php' );

register_activation_hook(__FILE__,'wpls_activate_plugin');

// Add relevant actions
add_action( 'admin_head', 'wpls_main' );
add_action( 'login_form', 'wpls_login_form' );
add_action( 'admin_init', 'wpls_options_init' );
add_action( 'admin_menu', 'wpls_options_add_page');

// Custom wp-login actions
add_action( 'login_form_unknownip' ,  'wpls_login_action' );
add_action( 'login_form_registerip' , 'wpls_login_action' );
add_action( 'login_form_invalidkey' , 'wpls_login_action' );

function wpls_activate_plugin() {
	global $wpls_options, $current_user;
	session_start();
	unset( $_SESSION['wpls_ipkey'] );

	// Set default values
	$activate_options['enabled'] = 1;
	$activate_options['notify_both'] = 0;
	update_option( $wpls_options['options'] , $activate_options);

	$user = $current_user->user_login;
	$ip = $_SERVER['REMOTE_ADDR'];
	$known = get_option( $wpls_options['whitelist'] );
	$known[ $user ][ $ip ]['date_requested'] = date(WPLS_DATE_FORMAT);
	$known[ $user ][ $ip ]['date_activated'] = date(WPLS_DATE_FORMAT);
	$known[ $user ][ $ip ]['activated'] = true;
	update_option( $wpls_options['whitelist'] , $known );

}

function wpls_main() {
	global $wpls_options;
	$_options = get_option( $wpls_options['options'] );

	// Check if WP Login Security is enabled
	if ( !isset( $_options['enabled'] ) ) {
		wpls_activate_plugin();
		return false;

	} elseif ( $_options['enabled'] == 0) {
		return false;
	}

	wpls_new_ip_check();
}

function wpls_new_ip_check() {
	global $wpls_options, $current_user;
	session_start();
	$known = get_option( $wpls_options['whitelist'] );
	$user = $current_user->user_login;
	$ip = $_SERVER['REMOTE_ADDR'];

	// First check for known and activated IP
	if ( is_array($known) && $known[ $user ][ $ip ]['activated'] == true ) {
		return false; // This is a known IP
	}
	// Then check for IPKEY indicating registration
	elseif ( isset($_SESSION['wpls_ipkey']) ) {

		$ipkey = $_SESSION['wpls_ipkey'];

		if ( $known[ $user ][ $ip ][ 'ipkey' ] == $ipkey ) {
			// Key valid.  Login.
			// TODO Add check to verify key expired keys
			$known[ $user ][ $ip ]['date_activated'] = date(WPLS_DATE_FORMAT);
			$known[ $user ][ $ip ]['activated'] = true;
			unset( $_SESSION['wpls_ipkey'] );
			update_option( $wpls_options['whitelist'] , $known );

			if ( $_options['notify_both'] ) {
				wpls_notify_blog_admin();
			}
			return false;

		} else {
			wp_logout();
			wp_redirect( site_url('wp-login.php?action=invalidkey') );
			exit;
		}
	}
	// If not known or registering, notify of new IP
	else {
		wpls_send_activation();
		wp_logout();
		wp_redirect( site_url('wp-login.php?action=unknownip') );
		exit;
	}
}

function wpls_send_activation() {
	global $current_user,$wpls_options;
	$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$ipkey = md5( mt_rand($rand) );
	$message = "Someone has logged in with the below information from an IP we haven't seen before.\n\nUser: $current_user->user_login\nIP: {$_SERVER['REMOTE_ADDR']}\nURL: $url";
	$message .= "\n\nTo authorize this IP address, please click the following link: ".site_url('wp-login.php?action=registerip&wpls_ipkey='.$ipkey);
	$subject = sprintf( __('[%s] WP Login Security Alert'), get_option('blogname') );
	wp_mail( $current_user->user_email, $subject, $message );

	// Set activation key
	$_data = get_option( $wpls_options['whitelist'] );
	$_data[ $current_user->user_login ][ $_SERVER['REMOTE_ADDR'] ][ 'date_requested' ] = date(WPLS_DATE_FORMAT);
	$_data[ $current_user->user_login ][ $_SERVER['REMOTE_ADDR'] ][ 'activated' ] = false;
	$_data[ $current_user->user_login ][ $_SERVER['REMOTE_ADDR'] ][ 'ipkey' ] = $ipkey;
	update_option( $wpls_options['whitelist'] , $_data );
}

function wpls_notify_blog_admin() {
	global $current_user;
	$message = "New IP Registration\nUser: $current_user->user_login\nIP: {$_SERVER['REMOTE_ADDR']}";
	$subject = sprintf( __('[%s] WP Login Security Alert'), get_option('blogname') );
	wp_mail( get_option('admin_email'), $subject, $message );
}

function wpls_login_action() {
	global $error, $action;

	switch($action) {
		case "registerip":
			$error = "Please login to validate IP address.";
			break;
		case "unknownip":
			$error = "Login from an unrecognized IP address.  A one time password has been sent to the email address on record for your accont.";
			break;
		case "invalidkey":
			$error = "Invalid Key!";
			break;
	}

}

function wpls_login_form() {
	global $action;
	if ($action == "registerip") {
		//echo '<input type="hidden" name="wpls_ipkey" value="'.$_GET['wpls_ipkey'].'">';
		session_start();
		$_SESSION['wpls_ipkey'] = $_GET['wpls_ipkey'];
	}
}


?>

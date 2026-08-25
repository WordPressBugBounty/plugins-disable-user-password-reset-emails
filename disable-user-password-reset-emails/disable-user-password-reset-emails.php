<?php
/**
 * @package Disable User Password Reset Admin Notifications
 */
/*
Plugin Name: Disable User Password Reset Admin Notifications
Plugin URI: https://chris-cook.net/
Description: Disable admin email notifications when a user changes their password. Simply activate the plugin and you will no longer receive a email notification when a user resets their password.
Version: 2.1
Author: Chris Cook
Author URI: https://chris-cook.net/
Tested up to: 7.1
License: GPL v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: disable-user-password-reset-emails
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'wp_password_change_notification' ) ) {
	function wp_password_change_notification( $user ) {
		return;
	}
}

/**
 * Get the current time and set it as an option when the plugin is activated.
 *
 * @return null
 */
function winwar_set_activation_date() {
	add_option( 'myplugin_activation_date', time() );
}
register_activation_hook( __FILE__, 'winwar_set_activation_date' );

/**
 * Check date on admin initiation and add to admin notice if it was over 7 days ago.
 *
 * @return null
 */
function winwar_check_installation_date() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$nobug = get_option( 'winwar_no_bug' );
	if ( ! $nobug ) {
		$install_date = get_option( 'myplugin_activation_date' );
		$past_date = strtotime( '+7 days', $install_date );
		if ( time() >= $past_date ) {
			add_action( 'admin_notices', 'winwar_display_admin_notice' );
		}
	}
}
add_action( 'admin_init', 'winwar_check_installation_date' );

/**
 * Display Admin Notice, asking for a review.
 *
 * @return null
 */
function winwar_display_admin_notice() {
	$reviewurl = 'https://wordpress.org/support/plugin/disable-user-password-reset-emails/reviews/';
	$nobugurl  = wp_nonce_url( add_query_arg( 'winwarnobug', '1', admin_url() ), 'winwar_no_bug' );

	echo '<div class="updated"><p>';
	/* translators: %1$s: review URL, %2$s: dismiss URL */
	printf(
		__(
			'You have been using the <strong>Disable User Password Reset Admin Notifications</strong> plugin for a week now, do you like it? If so, please leave us a review with your feedback! <br /><br /> <a href="%1$s" target="_blank">Leave A Review</a> / <a href="%2$s">Leave Me Alone</a>',
			'disable-user-password-reset-emails'
		),
		esc_url( $reviewurl ),
		esc_url( $nobugurl )
	);
	echo '</p></div>';
}

/**
 * Set the plugin to no longer bug users if user asks not to be.
 *
 * @return null
 */
function winwar_set_no_bug() {
	if ( isset( $_GET['winwarnobug'] )
		&& current_user_can( 'manage_options' )
		&& check_admin_referer( 'winwar_no_bug' ) ) {
		update_option( 'winwar_no_bug', true );
	}
}
add_action( 'admin_init', 'winwar_set_no_bug', 5 );
<?php
/**
 * PDP Uninstall
 *
 * Uninstalling PDP deletes pdp_cart , pdp_guest_design.
 *
 * @author      PDP
 * @category    Core
 * @package     PDP_Integration/Uninstaller
 * @version     0.1.0
 */
 
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb, $wp_version;

	// Tables.
	//$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pdp_cart" );
	//$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pdp_guest_design" );
	//delete options config data
	//delete_option( 'pdpinteg_settings' );
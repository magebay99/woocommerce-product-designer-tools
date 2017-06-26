<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDP_Install Class.
 */
class PDP_Install {
	
	/**
	 * Get Table schema.
	 *
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;
		
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
		
		$tables = "
			CREATE TABLE {$wpdb->prefix}pdp_guest_design (
			  entity_id BIGINT UNSIGNED NOT NULL auto_increment,
			  user_id bigint(20) unsigned NULL,
			  is_active int(1) NOT NULL DEFAULT 1,
			  customer_is_guest int(1) NOT NULL DEFAULT 0,
			  item_value longtext NULL,
			  PRIMARY KEY  (entity_id),
			  KEY user_id (user_id)
			) $collate;
		";
		return $tables;
	}
	
	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *		pdp_cart
	 *		pdp_guest_design
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( self::get_schema() );
	}
	
	/**
	 * Install PDP.
	 */
	public static function install() {
		
		if ( ! is_blog_installed() ) {
			return;
		}
		
		if ( ! defined( 'PDP_INSTALLING' ) ) {
			define( 'PDP_INSTALLING', true );
		}

		// Ensure needed classes are loaded
		include_once( dirname( __FILE__ ) . '/admin/class-pdp-admin-notices.php' );
		self::create_tables();
	}	
}
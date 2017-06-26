<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDP_Custommer Class
 */
class PDP_Custommer {
	
	/**			 
	 * Custom endpoint name.			 
	 *			 
	 * @var string			 
	 */			
	public static $endpoint = 'pdp-customzied';
	
	protected static $_instance = null;
	
	public function __construct() {
		
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );		
		// Change the My Accout page title.
		add_filter( 'the_title', array( $this, 'endpoint_title' ) );
		add_action( 'init', array( $this, 'add_endpoints' ) );
		register_activation_hook( __FILE__, array( $this, 'install' ) );
	}
	
	/**
	 * Main Plugin Instance
	 *
	 * Ensures only one instance of plugin is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Add new query var.
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = self::$endpoint;
		return $vars;
	}
	
	/**
	 * Set endpoint title.
	 * @param string $title
	 * @return string
	 */
	public function endpoint_title( $title ) {
		global $wp_query;
		$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );
		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			$title = __( 'My Customzied Products', 'pdpinteg' );
			remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
		}
		return $title;
	}
	
	/**
	 * Plugin install action.
	 * Flush rewrite rules to make our custom endpoint available.
	 */
	public static function install() {
		flush_rewrite_rules();
	}
	
	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 */
	public function add_endpoints() {
		add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
	}
}
PDP_Custommer::instance();
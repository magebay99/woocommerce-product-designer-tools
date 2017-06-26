<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDP_API Class
 */
class PDP_API extends WC_Legacy_API {
	
	/**
	 * Setup class.
	 * @since 2.0
	 */
	public function __construct() {
		parent::__construct();
		// Add query vars.
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Register API endpoints.
		add_action( 'init', array( $this, 'add_endpoint' ), 0 );
		
		// Handle wc-api endpoint requests.
		add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );
		
		// WP REST API.
		$this->pdp_rest_api_init();
	}
	
	/**
	 * Add new query vars.
	 *
	 * @since 2.0
	 * @param array $vars
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars   = parent::add_query_vars( $vars );
		$vars[] = 'wc-api';
		return $vars;
	}

	/**
	 * WC API for payment gateway IPNs, etc.
	 * @since 2.0
	 */
	public static function add_endpoint() {
		parent::add_endpoint();
		add_rewrite_endpoint( 'wc-api', EP_ALL );
	}
	
	/**
	 * API request - Trigger any API requests.
	 *
	 * @since   2.0
	 * @version 2.4
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET['wc-api'] ) ) {
			$wp->query_vars['wc-api'] = $_GET['wc-api'];
		}

		// wc-api endpoint requests.
		if ( ! empty( $wp->query_vars['wc-api'] ) ) {

			// Buffer, we won't want any output here.
			ob_start();

			// No cache headers.
			nocache_headers();

			// Clean the API request.
			$api_request = strtolower( wc_clean( $wp->query_vars['wc-api'] ) );

			// Trigger generic action before request hook.
			do_action( 'woocommerce_api_request', $api_request );

			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			status_header( has_action( 'woocommerce_api_' . $api_request ) ? 200 : 400 );

			// Trigger an action which plugins can hook into to fulfill the request.
			do_action( 'woocommerce_api_' . $api_request );

			// Done, clear buffer and exit.
			ob_end_clean();
			die( '-1' );
		}
	}
	
	/**
	 * Init WP REST API.
	 * @since 2.6.0
	 */
	private function pdp_rest_api_init() {
		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		$this->pdp_rest_api_includes();

		// Init REST API routes.
		add_action( 'rest_api_init', array( $this, 'pdp_register_rest_routes' ), 10 );
	}
	
	/**
	 * Include REST API classes.
	 *
	 * @since 2.6.0
	 */
	private function pdp_rest_api_includes() {
		
		include_once( dirname( __FILE__ ) . '/api/v1/class-wc-rest-pcart-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-wc-rest-pcart-controller.php' );
	}
	
	/**
	 * Register REST API routes.
	 * @since 2.6.0
	 */
	public function pdp_register_rest_routes() {
		$controllers = array(
			'WC_REST_PCart_V1_Controller',
			'WC_REST_PCart_Controller',
		);
		
		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
	}
}
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class PDP_API_Cart extends WC_API_Resource {
	
	/** @var string $base the route base */
	protected $base = '/cart';
	
	/**
	 * Setup class, overridden to provide customer data to order response
	 *
	 * @since 2.1
	 * @param WC_API_Server $server
	 * @return PDP_API_Cart
	 */
	public function __construct( WC_API_Server $server ) {

		parent::__construct( $server );
	}
	
	/**
	 * Register the routes for this class
	 *
	 * POST /cart
	 *
	 * @since 2.1
	 * @param array $routes
	 * @return array
	 */
	public function register_routes( $routes ) {
		# POST /cart
		$routes[ $this->base ] = array(
			array( array( $this, 'create_cart' ), WC_API_SERVER::CREATABLE | WC_API_Server::ACCEPT_DATA ),
		);
		
		return $routes;
	}
	
	/**
	 * Add item to cart
	 *
	 * @since 2.2
	 * @param array $data posted data
	 * @return array
	 */
	public function create_cart( $data ) {
		
	}	
}
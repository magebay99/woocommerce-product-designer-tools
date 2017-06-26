<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API PCart controller class.
 *
 * @extends WC_REST_Controller
 */
class WC_REST_PCart_V1_Controller extends WC_REST_Controller {
	
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'pcart';
	
	/**
	 * Register the routes for pcart.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}
	
	/**
	 * Check if a given request has access create pcart.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return boolean
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'settings', 'create' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_create', __( 'Sorry, you are not allowed to create resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
	
	/**
	 * Add item to cart.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $_request ) {
		$status_api = PDP_Helper::instance()->status_pdp_integration();
		$data = array();
		if($status_api && isset($_request['pdpItem'])) {
			$request = $_request['pdpItem'];
			if(isset($request['entity_id']) && isset($request['sku'])) {
				$product_sku = wc_clean( $request['sku'] );
				$product_id  = wc_get_product_id_by_sku( $product_sku );
				if ( $product_id ) {
					$product_status = get_post_status( $product_id );
					if('publish' === $product_status) {
						if(isset($request['qty']) && $request['qty']) {
							$quantity = $request['qty'];
						} else {
							$quantity = 1;
						}
						//$cart_item_data = (array) apply_filters('pdp_api_add_item_cart', $request, $product_id);
						$cart_item_data = PDP_Cart::prepare_cart_item_data( $request, $product_id);
						if(isset($request['item_id'])) {
							$cart_item_key = sanitize_text_field( $request['item_id'] );
							if ( $cart_item = WC()->cart->get_cart_item( $cart_item_key ) ) {
								WC()->cart->remove_cart_item( $cart_item_key );
							}
						}
						if( false !== WC()->cart->add_to_cart( $product_id, $quantity, 0, array(),  $cart_item_data) ) {
							$data = array('status' => true, 'message' => __('Add product to cart Woocommerce success', 'pdpinteg'), 'url' => WC()->cart->get_cart_url());
							do_action( 'pdp_api_added_to_cart', $product_id );
						} else {
							$data['status'] = false;
							$data['message'] = __('can not add product to cart', 'pdpinteg');
						}
					} else {
						$data['status'] = false;
						$data['message'] = __('can not add product to cart. Product not publish', 'pdpinteg');
					}
				} else {
					$data['status'] = false;
					$data['message'] = __('can not add product to cart. Product not exists', 'pdpinteg');
				}
			} else {
				$data['status'] = false;
				$data['message'] = __('can not add product to cart. Product not exists', 'pdpinteg');
			}
		} else {
			$data['status'] = false;
			$data['message'] = __('can not add product to cart. Please enable api ajax cart', 'pdpinteg');
		}
		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );
		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, 0 ) ) );
		return $response;
	}
}
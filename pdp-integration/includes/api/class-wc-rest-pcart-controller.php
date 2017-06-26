<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API PCart controller class.
 *
 * @extends WC_REST_PCart_V1_Controller
 */
class WC_REST_PCart_Controller extends WC_REST_PCart_V1_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v2';
}
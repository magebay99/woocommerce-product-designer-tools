<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *  Product pdpro Class
 * @class WC_Product_Pdpro
 */
class WC_Product_Pdpro extends WC_Product_Simple {
	
	public function __construct( $product ) {

		//$this->product_type = 'pdpro';

		parent::__construct( $product );
	}
	
	/**
	 * Get internal type.
	 * @return string
	 */
	public function get_type() {
		return 'pdpro';
	}
	
	/**
	 * @param array $type
	 * @return array
	 */
	public static function add_pdpro_product( $types ) {
		// Key should be exactly the same as in the class product_type parameter
		$types[ 'pdpro' ] = __( 'PDP', 'pdpinteg');

		return $types;
	}
	
	/**
	 * @param array @data
	 * @return array
	 */		
	public static function custom_product_data_option($data) {
		if( isset($data['virtual']) ) {
			if( isset($data['virtual']['wrapper_class'])) {
				$data['virtual']['wrapper_class'] .= ' show_if_pdpro';
			}
		}
		if( isset($data['downloadable']) ) {
			if( isset($data['downloadable']['wrapper_class']) ) {
				$data['downloadable']['wrapper_class'] .= ' show_if_pdpro';
			}
		}
		return $data;
	}
	
	public static function add_option_product_data_tabs($data) {
		if(isset($data['inventory'])) {
			if(isset($data['inventory']['class'])) {
				array_push($data['inventory']['class'], 'show_if_pdpro');
			}
		}
		return $data;
	}
	
	/**
	 * Show pricing fields for pdpro product.
	 */
	public static function pdpro_custom_js() {
		if ( 'product' != get_post_type() ) {
			return;
		}
		include_once('admin/view/pdpro_custom_js.php');
	}
}
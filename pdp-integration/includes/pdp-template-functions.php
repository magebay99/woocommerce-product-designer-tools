<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'woocommerce_pdpro_add_to_cart', 'woocommerce_pdpro_add_to_cart' );
if ( ! function_exists( 'woocommerce_pdpro_add_to_cart' ) ) {
	function woocommerce_pdpro_add_to_cart() {
		wc_get_template('single-product/add-to-cart/pdpro.php', array(),'',PDPINTEG()->plugin_path().'/templates/');
	}
}

add_filter('woocommerce_loop_add_to_cart_link', 'pdp_custom_link_product',1,3);
if ( ! function_exists( 'pdp_custom_link_product' ) ) {
	function pdp_custom_link_product($link, $product) {
		if($product->get_type() == PDP_Helper::PRODUCT_PDP) {
			$pdp_helper = PDP_Helper::instance();
			
			return 	sprintf( '<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>',
				esc_url( $pdp_helper->get_link_design_pdp($product->get_sku()) ),
				esc_attr( isset( $quantity ) ? $quantity : 1 ),
				esc_attr( $product->get_id() ),
				esc_attr( $product->get_sku() ),
				esc_attr( isset( $class ) ? $class : 'button' ),
				esc_html( $pdp_helper->get_label_custom() )
			);
		}
		return $link;
	}
}

add_filter('woocommerce_account_menu_items', 'pdp_add_tem_account_menu');

if( ! function_exists( 'pdp_add_tem_account_menu' ) ) {
	function pdp_add_tem_account_menu($items) {
		// Remove the logout menu item.
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );
		
		// Insert your custom endpoint.
		$items['pdp-customzied'] = __('My Customized Products', 'pdpinteg');
		
		// Insert back the logout item.
		$items['customer-logout'] = $logout;
		
		return $items;
	}
}

add_action( 'woocommerce_account_pdp-customzied_endpoint', 'woocommerce_account_pdp_customzied' );

if( ! function_exists( 'woocommerce_account_pdp_customzied' ) ) {
	function woocommerce_account_pdp_customzied() {
		$current_page = 1;
		$customer_orders = wc_get_orders(array('customer' => get_current_user_id(), 'page' => $current_page, 'paginate' => true));
		wc_get_template('myaccount/my-customzied.php', array(
			'customer_orders' => $customer_orders,
		),'',PDPINTEG()->plugin_path().'/templates/');
	}
}

add_action( 'wp_login', 'refresh_guest_design', 20, 2 );

if( ! function_exists( 'refresh_guest_design' )) {
	function refresh_guest_design( $user_login, $user ) {
		session_start();
		//$pdp_guest_design_id = WC()->session->get('pdp_integration_guest_design', null);
		$pdp_guest_design_id = isset($_SESSION['pdp_integration_guest_design'])?$_SESSION['pdp_integration_guest_design']:null;
		$guest_design = PDP_Guest_Design::instance();
		$user_id = $user->ID;
		$_data_guest_design = $guest_design->load_design_by_customer_id( $user_id );
		if( !is_null($pdp_guest_design_id) ) {
			$data_guest_design = $guest_design->load_guest_design_by_id($pdp_guest_design_id);
			if( !empty($data_guest_design) ) {
				if( $data_guest_design[0]->entity_id ) {
					$_dataitemval = maybe_unserialize($data_guest_design[0]->item_value);
				}
			}
			if( empty($_data_guest_design) ) {
				$dataguestdesign = array(
					'customer_is_guest' => 0,
					'user_id' => $user_id
				);
				$guest_design->update_guest_design_by_id( $dataguestdesign, $pdp_guest_design_id);
			} else {
				if( isset($_dataitemval) ) {
					$dataitemval = maybe_unserialize($_data_guest_design[0]->item_value);
					$guest_design->delete_guest_design( $pdp_guest_design_id );
					foreach($_dataitemval as $_item) {
						$dataitemval[] = $_item;
					}
					$dataguestdesign = array(
						'item_value' => maybe_serialize( $dataitemval ),
					);
					$guest_design->update_guest_design_by_user_id($dataguestdesign, $user_id);
				}
			}
		}
		//WC()->session->set('pdp_integration_guest_design', null);
		session_unset('pdp_integration_guest_design');
	}
}

add_action( 'woocommerce_checkout_update_order_meta', 'update_guest_design_after_create_order', 10, 2 );

if( ! function_exists( 'update_guest_design_after_create_order' ) ) {
	function update_guest_design_after_create_order( $order_id, $data ) {
		session_start();
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		$saveOrderInfoPdp = false;
		$guest_design = PDP_Guest_Design::instance();
		$_dataOrderItem = array();
		foreach( $items as $key => $item ) {
			$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
			$product = $item->get_product();
			if( $product->get_type() == PDP_Helper::PRODUCT_PDP) {
				$saveOrderInfoPdp = true;
				$dataOrderItem = array();
				$data = maybe_unserialize( wc_get_order_item_meta($key, 'pdpData' ) );
				$dataOrderItem['product_id'] = $product_id;
				if(isset($data['design_id'])) {
					$dataOrderItem['design_id'] = $data['design_id'];
				}
				$_dataOrderItem[] = $dataOrderItem;
			}
		}
		if( $saveOrderInfoPdp ) {
			if(is_user_logged_in()) {
				$user_id = get_current_user_id();
				$_data_guest_design = $guest_design->load_design_by_customer_id( $user_id );
				if(!empty($_data_guest_design)) {
					$dataItemValue = maybe_unserialize($_data_guest_design[0]->item_value);
					foreach( $dataItemValue as $__key => $__itemValue ) {
						foreach( $_dataOrderItem as $__orderItem) {
							if($__orderItem['design_id'] == $__itemValue['design_id'] && $__orderItem['product_id'] == $__itemValue['product_id']) {
								unset($dataItemValue[$__key]);
								break;
							}
						}
					}
					if(count($dataItemValue)) {
						$dataguestdesign = array(
							'item_value' => maybe_serialize( $dataItemValue ),
						);
						$guest_design->update_guest_design_by_user_id($dataguestdesign, $user_id);
					} else {
						$dataguestdesign = array(
							'is_active' => 0,
						);
						$guest_design->update_guest_design_by_user_id($dataguestdesign, $user_id);						
					}
				}
			} else {
				$pdp_guest_design_id = isset($_SESSION['pdp_integration_guest_design'])?$_SESSION['pdp_integration_guest_design']:null;
				if(!is_null($pdp_guest_design_id)) {
					$data_guest_design = $guest_design->load_guest_design_by_id($pdp_guest_design_id);
					$dataItemValue = maybe_unserialize($data_guest_design[0]->item_value);
					foreach( $dataItemValue as $__key => $__itemValue ) {
						foreach( $_dataOrderItem as $__orderItem) {
							if($__orderItem['design_id'] == $__itemValue['design_id'] && $__orderItem['product_id'] == $__itemValue['product_id']) {
								unset($dataItemValue[$__key]);
								break;
							}
						}
					}
					if(count($dataItemValue)) {
						$dataguestdesign = array(
							'item_value' => maybe_serialize( $dataItemValue ),
						);
						$guest_design->update_guest_design_by_user_id($dataguestdesign, $user_id);
					} else {
						$dataguestdesign = array(
							'is_active' => 0,
						);
						$guest_design->update_guest_design_by_user_id($dataguestdesign, $user_id);						
					}
				}
			}
		}
	}
}
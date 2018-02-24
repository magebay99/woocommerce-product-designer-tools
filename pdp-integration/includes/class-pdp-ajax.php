<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class PDP_AJAX {
	
	protected static $_instance = null;
	
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
	 * Hook in ajax handlers.
	 */
	public function init() {
		//add_action( 'init', array( $this, 'register_routes' ) );
//		add_action( 'send_headers', 'pdp_send_cors_headers' );
		add_action( 'template_redirect', array( __CLASS__, 'do_pdp_ajax' ), 1 );
		self::add_ajax_events();
	}
        
        

    /**
	 * Send headers for WC Ajax Requests.
	 *
	 * @since 2.5.0
	 */
	private static function pdp_ajax_headers() {
//		send_origin_headers();
//		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
                self::setOriginResponseHeader();
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}
        
         /**
            * Get all header which is sent by client
            * @return array
            */
           public static function getRequestHeaders() {
               $missed = array('CONTENT_LENGTH');
               $headers = array();
               foreach ($_SERVER as $key => $value) {
                   if (substr($key, 0, 5) <> 'HTTP_' && !in_array($key, $missed)) {
                       continue;
                   }
                   $orgHeader = in_array($key, $missed) ? $key : substr($key, 5);
                   $orgHeader = strtolower($orgHeader);
                   $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', $orgHeader)));
                   $headers[$header] = $value;
               }
               return $headers;
           }

           public static function setOriginResponseHeader() {
               header('Content-Type: application/json');
               header('Access-Control-Allow-Origin: ' . get_http_origin());
               header('Access-Control-Allow-Headers: Content-Type,' . implode(', ', array_keys(self::getRequestHeaders())));
               header('Access-Control-Allow-Methods: POST, PUT, DELETE, GET, OPTIONS');
               header('Access-Control-Allow-Credentials: true');
           }

    /**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
                
		// woocommerce_EVENT => nopriv
		$ajax_events = array(
			'add_to_cart'                                      => true,
			'add_guest_design'                                      => true,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			if ( $nopriv ) {
				// WC AJAX can be used for frontend ajax requests.
				add_action( 'pdp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}
	
	/**
	 * Check for PDP Ajax request and fire action.
	 */
	public static function do_pdp_ajax() {
		global $wp_query;
		if ( ! empty( $_GET['pdp-ajax'] ) ) {
			$wp_query->set( 'pdp-ajax', sanitize_text_field( $_GET['pdp-ajax'] ) );
		}
		
		if ( $action = $wp_query->get( 'pdp-ajax' ) ) {
			self::pdp_ajax_headers();
			do_action( 'pdp_ajax_' . sanitize_text_field( $action ) );
			wp_die();
		}
	}
	
	/**
	 * AJAX add guest design.
	 */
	public static function add_guest_design() {
		ob_start();
		session_start();
		$data = array();
		$_request = json_decode(file_get_contents('php://input'), true);
		if(isset($_request['pdpDesignItem'])) {
			$request = $_request['pdpDesignItem'];
			if(isset($request['design_id']) && isset($request['product_sku'])) {
				$product_sku = wc_clean( $request['product_sku'] );
				$product_id  = wc_get_product_id_by_sku( $product_sku );
				$item_value = array(
					'product_id' => $product_id,
					'pdp_product_id' => $request['product_id']?$request['product_id']:$product_id,
					'design_id' => $request['design_id']
				);
				if($product_id) {
					$guest_design = PDP_Guest_Design::instance();
					if(is_user_logged_in()) {
						$user_id = get_current_user_id();
						//$pdp_guest_design_id = WC()->session->get('pdp_integration_guest_design', null);
						$pdp_guest_design_id = isset($_SESSION['pdp_integration_guest_design'])?$_SESSION['pdp_integration_guest_design']:null;
						
						$dataguestdesign = array('user_id' => $user_id);
						if(is_null($pdp_guest_design_id)) {
							$data_guest_design = $guest_design->load_design_by_customer_id( $user_id );
							if(!empty($data_guest_design)) {
								if($data_guest_design[0]->entity_id) {
									$data_item_value = maybe_unserialize($data_guest_design[0]->item_value);
									if(is_array($data_item_value)) {
										$update = false;
										foreach($data_item_value as $__item) {
											if($__item['product_id'] == $item_value['product_id'] && $__item['pdp_product_id'] == $item_value['pdp_product_id'] && $__item['design_id'] == $item_value['design_id']) {
												$update = true;
												break;
											}
										}
										if(!$update) {
											$data_item_value[] = $item_value;
										}
										$dataguestdesign['item_value'] = maybe_serialize($data_item_value);
									} else {
										$dataguestdesign['item_value'] = maybe_serialize([$item_value]);
									}
									$guest_design->update_guest_design_by_user_id( $dataguestdesign, $user_id );
								}
							} else {
								$dataguestdesign['customer_is_guest'] = 0; 
								$dataguestdesign['item_value'] = maybe_serialize([$item_value]);
								$guest_design->insert_guest_design( $dataguestdesign );
							}
						} else {
							//WC()->session->set('pdp_integration_guest_design', null);
							session_unset('pdp_integration_guest_design');
						}
					} else {
						//$pdp_guest_design_id = WC()->session->get('pdp_integration_guest_design', null);
						$pdp_guest_design_id = isset($_SESSION['pdp_integration_guest_design'])?$_SESSION['pdp_integration_guest_design']:null;
						if( is_null($pdp_guest_design_id) ) {
							$dataguestdesign = array(
								'customer_is_guest' => 1,
								'item_value' => maybe_serialize([$item_value]),
							);
							$guest_design_id = $guest_design->insert_guest_design($dataguestdesign);
							//WC()->session->set('pdp_integration_guest_design', $guest_design_id);
							$_SESSION['pdp_integration_guest_design'] = $guest_design_id;
						} else {
							$data_guest_design = $guest_design->load_guest_design_by_id($pdp_guest_design_id);
							$dataguestdesign = array(
								'entity_id' => $pdp_guest_design_id,
								'customer_is_guest' => 1,
							);
							if(!empty($data_guest_design)) {
								if($data_guest_design[0]->entity_id) {
									$data_item_value = maybe_unserialize($data_guest_design[0]->item_value);
									if(is_array($data_item_value)) {
										$update = false;
										foreach($data_item_value as $__item) {
											if($__item['product_id'] == $item_value['product_id'] && $__item['pdp_product_id'] == $item_value['pdp_product_id'] && $__item['design_id'] == $item_value['design_id']) {
												$update = true;
												break;
											}
										}
										if(!$update) {
											$data_item_value[] = $item_value;
										}
										$dataguestdesign['item_value'] = maybe_serialize($data_item_value);
									} else {
										$dataguestdesign['item_value'] = maybe_serialize([$item_value]);
									}
									$guest_design->update_guest_design_by_id($dataguestdesign, $pdp_guest_design_id);
								}
							}
						}
					} 
					$data['status'] = true;
					$data['message'] = __('Add guest design success!', 'pdpinteg');
				} else {
					$data['status'] = false;
					$data['message'] = __('can not add product to cart. Product not exists', 'pdpinteg');
				}
			} else {
				$data['status'] = false;
				$data['message'] = __('can not add guest design. Product not exists', 'pdpinteg');
			}
		} else {
			$data['status'] = false;
			$data['message'] = __('can not add guest design. Something when wrong', 'pdpinteg');
		}
		wp_send_json( $data );
	}
        
	
	/**
	 * AJAX add to cart. 
	 */ 
	public static function add_to_cart() {
		ob_start();
		$status_api = PDP_Helper::instance()->status_pdp_integration();
		$data = array();
		if($status_api) {
			$_request = json_decode(file_get_contents('php://input'), true);
			if(isset($_request['pdpItem'])) {
				$request = $_request['pdpItem'];
				if((isset($request['entity_id']) && isset($request['sku']) && isset($request['design_id'])) || (isset($request['sku']) && isset($request['custom_size']))) {
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
                                                        if(isset($request['multi_size']) && $request['multi_size']) {
                                                            if(isset($request['pdp_print_type'])) {
                                                                $pdp_print_type = $request['pdp_print_type'];
                                                                if(isset($request['pdp_print_type']['price_multi_size'])) {
                                                                    $__price_multi_size = array();
                                                                    foreach($request['pdp_print_type']['price_multi_size'] as $_val) {
                                                                            if(isset($_val['size']) && isset($_val['price'])) {
                                                                                    if(isset($__price_multi_size[$_val['size']])) {
                                                                                            $__price_multi_size[$_val['size']] += $_val['price'];
                                                                                    } else {
                                                                                            $__price_multi_size[$_val['size']] = $_val['price'];
                                                                                    }
                                                                            }
                                                                    }
                                                                    $pdp_print_type['price_multi_size'] = $__price_multi_size;
                                                                }
                                                                $request['pdp_print_type']=$pdp_print_type;
                                                            }
                                                            $multi_size = array();
                                                            $price_multi_size = array();
                                                            foreach($request['multi_size'] as $sz_key => $sz_val) {
                                                                    $multi_size_item = array(
                                                                            //'name' => isset($sz_val['name']) ? $sz_val['name'] : '', 
                                                                            //'num' => isset($sz_val['num']) ? $sz_val['num'] : '',
                                                                            'qty' => isset($sz_val['qty']) ? $sz_val['qty'] : 1,
                                                                            'size' => isset($sz_val['size']) ? ucfirst($sz_val['size']) : '',
                                                                            'price' => isset($sz_val['price']) ? $sz_val['price'] : 0
                                                                    );
                                                                    if (isset($sz_val['name']) && isset($sz_val['num'])) {
                                                                            $usedNameNum = true;
                                                                            $multi_size_item['name'] = $sz_val['name'];
                                                                            $multi_size_item['num'] = $sz_val['num'];
                                                                    }
                                                                    if(isset($sz_val['size'])) {
                                                                            if (isset($multi_size[$sz_val['size']]) && count($multi_size[$sz_val['size']])) {
                                                                                    $flag_exist = true;
                                                                                    foreach($multi_size[$sz_val['size']] as $size_key => $item_name_size) {
                                                                                            if (!$usedNameNum) {
                                                                                                    break;
                                                                                            }
                                                                                            if (isset($item_name_size['name']) && isset($item_name_size['num']) ) {
                                                                                                    if ($item_name_size['name'] === $multi_size_item['name'] && $item_name_size['num'] === $multi_size_item['num']) {
                                                                                                            $multi_size[$sz_val['size']][$size_key]['qty'] = $item_name_size['qty'] + $multi_size_item['qty'];
                                                                                                            $flag_exist = false;
                                                                                                            break;
                                                                                                    }
                                                                                            }
                                                                                    }
                                                                                    if ($flag_exist) {
                                                                                            $multi_size[$sz_val['size']][] = $multi_size_item;
                                                                                    }
                                                                            } else {
                                                                                    $multi_size[$sz_val['size']][] = $multi_size_item;
                                                                            }

                                                                            if(!isset($price_multi_size[$sz_val['size']])) {
                                                                                    if(isset($sz_val['price']) && $sz_val['price']) {
                                                                                            $price_multi_size[$sz_val['size']] = $sz_val['price'];
                                                                                    }
                                                                            }
                                                                    }
                                                            }
                                                            if(count($multi_size)) {
                                                                foreach($multi_size as $mtize_key => $mtize_val){
                                                                    $multi_size_price=0;
                                                                    if (isset($pdp_print_type['price_multi_size']) && count($pdp_print_type['price_multi_size'])) {
                                                                        if (isset($pdp_print_type['price_multi_size'][$mtize_key])) {
                                                                            $multi_size_price += $pdp_print_type['price_multi_size'][$mtize_key];
                                                                        }
                                                                    }
                                                                    $request['multi_size_size']=$mtize_key;
                                                                    $request['multi_size_price']=$multi_size_price;
                                                                    $cart_item_data = PDP_Cart::prepare_cart_item_data( $request, $product_id );
                                                                    $multi_size_qty = 0;
                                                                    if(count($mtize_val) > 1) {
                                                                            foreach($mtize_val as $mtize_val_val) {
                                                                                    if(isset($mtize_val_val['qty'])) {
                                                                                            $multi_size_qty = $multi_size_qty + $mtize_val_val['qty'];
                                                                                    } else {
                                                                                            $multi_size_qty = 1;
                                                                                    }
                                                                            }
                                                                    } else {
                                                                            $multi_size_qty = isset($mtize_val[0]['qty'])?$mtize_val[0]['qty']:1;
                                                                    }
                                                                    if(isset($request['item_id'])) {
                                                                        $cart_item_key = sanitize_text_field( $request['item_id'] );
                                                                        if ( $cart_item = WC()->cart->get_cart_item( $cart_item_key ) ) {
                                                                                WC()->cart->remove_cart_item( $cart_item_key );
                                                                        }
                                                                    }
                                                                    if( false !== WC()->cart->add_to_cart( $product_id, $multi_size_qty, 0, array(),  $cart_item_data) ) {
//                                                                        
                                                                    } else {
                                                                            $data['status'] = false;
                                                                            $data['message'] = __('can not add product to cart', 'pdpinteg');
                                                                    }	
                                                                };
                                                                $data = array('status' => true, 'message' => __('Add product to cart Woocommerce success!', 'pdpinteg'), 'url' => WC()->cart->get_cart_url());
                                                                do_action( 'pdp_api_added_to_cart', $product_id );
                                                            }
                                                        }else{
                                                            //$cart_item_data = (array) apply_filters('pdp_api_add_item_cart', $request, $product_id);
                                                            $cart_item_data = PDP_Cart::prepare_cart_item_data( $request, $product_id );
                                                            if(isset($request['item_id'])) {
                                                                    $cart_item_key = sanitize_text_field( $request['item_id'] );
                                                                    if ( $cart_item = WC()->cart->get_cart_item( $cart_item_key ) ) {
                                                                            WC()->cart->remove_cart_item( $cart_item_key );
                                                                    }
                                                            }
                                                            if( false !== WC()->cart->add_to_cart( $product_id, $quantity, 0, array(),  $cart_item_data) ) {
                                                                    $data = array('status' => true, 'message' => __('Add product to cart Woocommerce success!', 'pdpinteg'), 'url' => WC()->cart->get_cart_url());
                                                                    do_action( 'pdp_api_added_to_cart', $product_id );
                                                            } else {
                                                                    $data['status'] = false;
                                                                    $data['message'] = __('can not add product to cart', 'pdpinteg');
                                                            }	
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
					if( !isset($request['design_id'])) {
						$data['message'] = __('can not add product to cart. Design not exists', 'pdpinteg');
					}
				}
			} else {
				$data['status'] = false;
				$data['message'] = __('can not add product to cart. Something when wrong', 'pdpinteg');
			}
		} else {
			$data['status'] = false;
			$data['message'] = __('can not add product to cart. Please enable api ajax cart', 'pdpinteg');
		}
		wp_send_json( $data );
	}
}
PDP_AJAX::instance()->init();
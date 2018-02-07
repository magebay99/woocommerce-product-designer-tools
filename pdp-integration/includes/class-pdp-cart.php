<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDP_Cart Class
 */
class PDP_Cart {
	
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
	 * @param string $item_name
	 * @return void
	 */
	public static function pdp_add_values_to_order_item_meta($item_id, $values) {
        global $woocommerce,$wpdb;
		foreach( $values as $key => $value ) {
			if( strpos($key, 'pdpoptions') !== false || strpos($key, 'pdpData') !== false ) {
				wc_add_order_item_meta($item_id,$key,$value);
			}
		}
	}
	
	/**
	 * @param string $product_name
	 * @param array $values
	 * @param string $cart_item_key
	 * @return string
	 */
	public static function add_custom_option_from_session_into_cart($product_name, $values, $cart_item_key ) {
		//$html = include(PDPINTEG()->plugin_path().'/templates/cart/custom-option.php');
		$html = '';
		$tag = true;
		$pdpdata = 'pdpData';
		foreach($values as $key => $value) {
			if( strpos($key, 'pdpoptions_pdp_print_type') !== false || strpos($key, 'pdpoptions_product_color') !== false ) {
				$_item_values = maybe_unserialize($value);
				if($tag) {
					$html .= '<dl class=\'variation item-options\'>';
					$tag = false;
				}
				$html .= '<dt>'.$_item_values['label'].'</dt>';
				$html .= '<dd>'.$_item_values['value'].'</dd>';
				continue;
			}elseif( strpos($key, 'pdpoptions_custom_size_width') !== false || strpos($key, 'pdpoptions_custom_size_height') !== false || strpos($key, 'pdpoptions_custom_size_layout') !== false ) {
				$_item_values = maybe_unserialize($value);
				if($tag) {
					$html .= '<dl class=\'variation item-options\'>';
					$tag = false;
				}
				$html .= '<dt>'.$_item_values['label'].'</dt>';
				$html .= '<dd>'.$_item_values['value'].'</dd>';
				continue;
			} elseif( $key == 'pdpoptions' ) {
				$__item_values = maybe_unserialize($value);
				if($tag) {
					$html .= '<dl class=\'variation item-options\'>';
					$tag = false;
				}
				foreach($__item_values as $_item_values) {
					$html .= '<dt>'.$_item_values['label'].'</dt>';
					$html .= '<dd>'.$_item_values['value'].'</dd>';					
				}
				continue;
			}
			if(strpos($key, 'pdpData') !== false) {
				$pdpdata = $key;
			}
		}
		if(!$tag) {
			$html .= '</dl>';
		}
		if(isset($values[$pdpdata])) {
			$pdp_data = maybe_unserialize($values[$pdpdata]);
			if(isset($pdp_data['design_id'])) {
				$design_json = PDP_Design_Json::instance()->get_design_by_design_id($pdp_data['design_id']);
				$url_tool = PDP_Helper::instance()->get_url_tool_design();
				if(!empty($design_json)) {
					$side_thubms = maybe_unserialize($design_json[0]->side_thumb);
					$html .= '<div class="block-customdesign"><strong style="margin-bottom:5px;display:block;">'.__('Customized Design:').'</strong>';
					$html .= '<ul class="items">';
					$i=0;
					foreach($side_thubms as $side_thumb) {
						if($side_thumb->thumb) {
							$i++;
							$last = $i%2==0?'last':'';
							$html .= '<li style="display:inline-block;margin-right:5px;" class="item '.$last.'"><a href="'.$url_tool.'/'.$side_thumb->thumb.'" target="_blank"><img style="border:1px solid #C1C1C1;" width="66" src="'.$url_tool.'/'.$side_thumb->thumb.'" /></a></li>';
						}
					}
					$html .= '</ul></div>';
				}
			}
			if(isset($pdp_data['design_url'])) {
				$html .= '<div class="block-button"><a class="action action-edit edit-button" href="'.$pdp_data['design_url'].'&itemid='.$cart_item_key.'"><span>';
				$html .= __('Edit Design', 'pdpinteg');
				$html .= '</span></a></div>';
			}
		}
		return $product_name.$html;
	}
	
	/**
	 * @param array $request
	 * @param int $product_id
	 * @return array
	 */
	public static function prepare_cart_item_data($request, $product_id) {
		$cart_item_data = array();
		
		if(!empty($request)) {
			$new_value = array();
			$pdpData = array();
			if( isset($request['design_url']) ) {
				$pdpData['design_url'] = $request['design_url'];
			}
			$custom_price = 0;
			if(isset($request['pdp_print_type'])) {
				$print_type = $request['pdp_print_type'];
				if(isset($print_type['value']) && isset($print_type['title']) && $print_type['value'] && $print_type['title']) {
					$new_value['pdpoptions_pdp_print_type'] = maybe_serialize(array('label' => __('Print type', 'pdpinteg'), 'value' => __($print_type['title'], 'pdpinteg')));
				}
				if(isset($print_type['price'])) {
					$custom_price += $print_type['price'];
				}
			}
			if(isset($request['product_color'])) {
				$productColor = $request['product_color'];
				$new_value['pdpoptions_product_color'] = maybe_serialize(array('label' => __('Color', 'pdpinteg'), 'value' => __($productColor['color_name'], 'pdpinteg')));
				if( isset($productColor['color_price']) && $productColor['color_price'] ) {
					$custom_price += $productColor['color_price'];
				}
			}
                        
                        if(isset($request['custom_size'])) {
				$customSize = $request['custom_size'];
                                if($customSize['unit']){
                                    $unit = $customSize['unit'];
                                }else{
                                    $unit='';
                                }
                                if($customSize['width']){
                                    $new_value['pdpoptions_custom_size_width'] = maybe_serialize(array('label' => __('Width', 'pdpinteg'), 'value' => __($customSize['width'].$unit, 'pdpinteg')));
                                }
                                if($customSize['height']){
                                    $new_value['pdpoptions_custom_size_height'] = maybe_serialize(array('label' => __('Height', 'pdpinteg'), 'value' => __($customSize['height'].$unit, 'pdpinteg')));
                                }
                                if($customSize['size_layout']){
                                    $new_value['pdpoptions_custom_size_layout'] = maybe_serialize(array('label' => __('Size layout', 'pdpinteg'), 'value' => __($customSize['size_layout'], 'pdpinteg')));
                                }
			}
			
			if(isset($request['pdp_options'])) {
				$pdp_helper = PDP_Helper::instance();
				$_pdpOptSelect = $pdp_helper->get_options_select($request['pdp_options']);
				$pdpOptSelect = $_pdpOptSelect['options'];
				$infoRequest = $pdp_helper->get_optinfor_request($pdpOptSelect);
				$additionalOptions = $pdp_helper->get_addition_option($pdpOptSelect);
				if(isset($infoRequest['pdp_price'])) {
					$custom_price += $infoRequest['pdp_price'];
				}
				$new_value['pdpoptions'] = maybe_serialize($additionalOptions);
			}
			
			$pdpData['custom_price'] = $custom_price;
			if( isset($request['design_id']) ) {
				$pdpData['design_id'] = $request['design_id'];
				$new_value['pdpData'] = maybe_serialize($pdpData);
				$new_value['pdp_design'.$pdpData['design_id']] = $pdpData['design_id'];
			}
			return array_merge($cart_item_data,$new_value);
		}
		return $cart_item_data;
	}
	
	/**
	 * @param Object
	 * @return void
	 */
	public static function add_custom_price( $cart_object ) {
		$custom_price = 0;
		foreach ( $cart_object->cart_contents as $key => $value ) {
			if(is_array($value)) {
				foreach($value as $_key => $_val) {
					if(strpos($_key, 'pdpData') !== false) {
						$pdpData = maybe_unserialize($value[$_key]);
						$custom_price = $pdpData['custom_price'];
						if($custom_price) {
							$custom_price += $value['data']->get_price();
							$value['data']->set_price( $custom_price );
						}
					}
				}
			}
		}
	}
}
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDP_Order_Item Class
 */
class PDP_Order_Item {
	
	/**
	 * @param int $item_id
	 * @param object $item The item being displayed
	 * @param object $product
	 * @return void
	 */
	public static function pdp_after_add_order_itemmeta($item_id, $item, $product) {
		if($product->get_type() == PDP_Helper::PRODUCT_PDP) {
			echo '</div>';
			include('admin/view/html-order-item-meta.php');
		}
	}

	/**
	 * @param int $item_id
	 * @param object $item The item being displayed
	 * @param object $product
	 * @return void
	 */
	public static function pdp_before_add_order_itemmeta($item_id, $item, $product) {
		if($product->get_type() == PDP_Helper::PRODUCT_PDP) {
			echo '<div class="hide-block-meta" style="display:none">';
		}
	}
	
	/**
	 * @param string $html
	 * @param object $item
	 * @param array $args
	 * @return string
	 */
	public static function pdp_display_item_meta($html, $item, $args = array()) {
		$strings = array();
		$html    = '';
		
		$args    = wp_parse_args( $args, array(
			'before'    => '<ul class="wc-item-meta"><li>',
			'after'		=> '</li></ul>',
			'separator'	=> '</li><li>',
			'echo'		=> true,
			'autop'		=> false,
		) );
		foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
			if(strpos($meta->display_key, 'pdpoptions_pdp_print_type') !== false || strpos($meta->display_key, 'pdpoptions_product_color') !== false){
				$pdpValue = maybe_unserialize(strip_tags($meta->display_value));
				$strings[] = '<strong class="wc-item-meta-label">' .  $pdpValue['label']  . ':</strong> ' . $pdpValue['value'];
			} elseif( $meta->display_key == 'pdpoptions' ) {
				$_pdpValue = maybe_unserialize(strip_tags($meta->display_value));
				foreach( $_pdpValue as $pdpValue) {
					$strings[] = '<strong class="wc-item-meta-label">' .  $pdpValue['label']  . ':</strong> ' . $pdpValue['value'];
				}
			} elseif(strpos($meta->display_key, 'pdpData') !== false) { 
				$pdp_data = maybe_unserialize(strip_tags($meta->display_value));
				if(isset($pdp_data['design_id'])) {
					$design_json = PDP_Design_Json::instance()->get_design_by_design_id($pdp_data['design_id']);
					$url_tool = PDP_Helper::instance()->get_url_tool_design();
					if(!empty($design_json)) {
						$side_thubms = maybe_unserialize($design_json[0]->side_thumb);
						$content_html = '<strong style="margin-bottom:5px;display:block;">'.__('Customized Design:').'</strong>';
						$content_html .= '<ul class="items">';
						$i=0;
						foreach($side_thubms as $side_thumb) {
							if($side_thumb['thumb']) {
								$i++;
								$last = $i%2==0?'last':'';
								$content_html .= '<li style="display:inline-block;margin-right:5px;" class="item '.$last.'"><a href="'.$url_tool.'/'.$side_thumb['thumb'].'" target="_blank"><img style="border:1px solid #C1C1C1;" width="66" src="'.$url_tool.'/'.$side_thumb['thumb'].'" /></a></li>';
							}
						}
						$content_html .= '</ul>';
						$strings[] = $content_html;
					}
				}
			} else {
				$value = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( strip_tags( $meta->display_value ) ) ) );
				$strings[] = '<strong class="wc-item-meta-label">' . wp_kses_post( $meta->display_key ) . ':</strong> ' . $value;				
			}
		}
		if ( $strings ) {
			$html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
		}
		return $html;
	}
}
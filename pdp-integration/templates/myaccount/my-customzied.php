<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$pdp_helper = PDP_Helper::instance();
$design_json_model = PDP_Design_Json::instance();
$guest_design = PDP_Guest_Design::instance();
$user_id = get_current_user_id();
$url_tool = $pdp_helper->get_url_tool_design();
$data_guest_design = $guest_design->load_design_by_customer_id( $user_id );
?>
<section class="pdp-my-customzied">
	<h2 class="pdp-my-customzied__title"><?php _e( 'My Customized Products', 'pdpinteg' ); ?></h2>
 <?php if( 0 < $customer_orders->total ) {?>
 	<table class="woocommerce-table woocommerce-table--my-customzied shop_table my-customzied">
		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php _e( 'PRODUCT', 'pdpinteg' ); ?></th>
				<th class="woocommerce-table__product-name product-price"><?php _e( 'PRICE', 'pdpinteg' ); ?></th>
				<th class="woocommerce-table__product-table product-status"><?php _e( 'STATUS', 'pdpinteg' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach ( $customer_orders->orders as $customer_order ) {
					$order      = wc_get_order( $customer_order );
					foreach ( $order->get_items() as $item_id => $item ) {
						$product = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
						if( $product->get_type() == PDP_Helper::PRODUCT_PDP ) {
							$pdp_data = maybe_unserialize( wc_get_order_item_meta($item_id, 'pdpData' ) );
							$url_download_zip_design = '#';
							$link_edit_design = '#';
							$html_design = '';
							if( isset($pdp_data['design_id']) ) {
								$url_download_zip_design = $pdp_helper->get_link_download( $pdp_data['design_id'] );
							}
							if( isset( $pdp_data['design_url'] ) ) {
								$link_edit_design = $pdp_data['design_url'];
							}
							
							wc_get_template( 'myaccount/item.php', array(
								'order'			     => $order,
								'item_id'		     => $item_id,
								'item'			     => $item,
								'product'	         => $product,
								'url_download_zip_design'     => $url_download_zip_design,
								'link_edit_design'     => $link_edit_design,
							), '', PDPINTEG()->plugin_path().'/templates/' );							
						}
					}
				}
				
				if(!empty($data_guest_design)) {
					if($data_guest_design[0]->entity_id) {
						$data_item_value = maybe_unserialize($data_guest_design[0]->item_value);
						if(is_array($data_item_value)) {
							foreach($data_item_value as $__item) {
								$link_edit_design = '#';
								if( isset($__item['design_id']) && $__item['design_id'] && isset($__item['product_id']) && $__item['product_id'] ) {
									$design_json = $design_json_model->get_design_by_design_id($__item['design_id']);
									if(!empty($design_json)) {
										$url_tool = $pdp_helper->get_url_tool_design();
										if( $design_json[0]->side_thumb ) {
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
											$product = wc_get_product( $__item['product_id'] );
											$link_edit_design = PDP_Helper::instance()->getLinkEditDesignFrontend($__item['design_id'], $product->get_sku() );
											if( $product->get_type() == PDP_Helper::PRODUCT_PDP ) {
												wc_get_template( 'myaccount/default.php', array(
													'product'	         => $product,
													'link_edit_design'     => $link_edit_design,
													'content_html'     => $content_html,
												), '', PDPINTEG()->plugin_path().'/templates/' );
											}
										}
									}
								}
							}
						}
					}
				}
				
			?>
		</tbody>
 	</table>
 <?php }
 ?>
 </section>

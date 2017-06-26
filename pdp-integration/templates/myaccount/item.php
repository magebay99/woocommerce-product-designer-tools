<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
	return;
}
$order_data = $order->get_data();
?>
<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">
	<td class="woocommerce-table__product-name product-name">
		<?php
			$is_visible        = $product && $product->is_visible();
			$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

			echo apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible );
			echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item->get_quantity() ) . '</strong>', $item );

			do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );
			wc_display_item_meta( $item );
			wc_display_item_downloads( $item );

			do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );
			?>
			
	</td>
	<td>
		<?php echo $order->get_formatted_line_subtotal( $item ); ?>
	</td>
	<td>
		<span class="item-status"><?php echo __('Pending', 'pdpinteg') ?></span>
		<div class="block-button">
			<?php 
				if($order_data['status'] == 'completed') {
			?>
					<a class="zip-design" href="javascript:void(0)" data-href="<?php echo $url_download_zip_design ?>"><?php echo __('Zip Design') ?></a>
			<?php
				}
			?>
			<a class="edit-button" href="<?php echo $link_edit_design ?>"> <?php echo __('Edit Design', 'pdpinteg') ?></a>
		</div>
	</td>
</tr>
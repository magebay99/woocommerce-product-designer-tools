<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hidden_order_itemmeta = apply_filters( 'woocommerce_hidden_order_itemmeta', array(
	'_qty',
	'_tax_class',
	'_product_id',
	'_variation_id',
	'_line_subtotal',
	'_line_subtotal_tax',
	'_line_total',
	'_line_tax',
	'method_id',
	'cost',
) );
$link_edit_design = '';
$download_design = '';
?><div class="view">
	<?php if ( $meta_data = $item->get_formatted_meta_data( '' ) ) : ?>
		<table cellspacing="0" class="display_meta">
			<?php foreach ( $meta_data as $meta_id => $meta ) :
				if ( in_array( $meta->key, $hidden_order_itemmeta ) ) {
					continue;
				}
				?>
				<tr>
					<?php
						if(strpos($meta->display_key, 'pdpoptions_pdp_print_type') !== false || strpos($meta->display_key, 'pdpoptions_product_color') !== false){
							$_item_values = maybe_unserialize(strip_tags($meta->display_value));
							
							$html = '<th>'.$_item_values['label'].':</th>';
							$html .= '<td>'.$_item_values['value'].'</td>';
							echo $html;
						} elseif( $meta->display_key == 'pdpoptions' ) {
							$__item_values = maybe_unserialize(strip_tags($meta->display_value));
							
							foreach( $__item_values as $_item_values) {
								$html = '<tr><th>'.$_item_values['label'].':</th>';
								$html .= '<td>'.$_item_values['value'].'</td></tr>';
								echo $html;
							}
							
						} elseif(strpos($meta->display_key, 'pdpData') !== false) {
							$pdp_data = maybe_unserialize(strip_tags($meta->display_value));
							if(isset($pdp_data['design_id'])) {
								$sku_product = $item->get_product()->get_sku();
								$link_edit_design = PDP_Helper::instance()->getLinkEditDesignBackend($pdp_data['design_id'], $sku_product);
								$download_design = PDP_Helper::instance()->get_link_download($pdp_data['design_id']);
								$design_json = PDP_Design_Json::instance()->get_design_by_design_id($pdp_data['design_id']);
								$url_tool = PDP_Helper::instance()->get_url_tool_design();
								if(!empty($design_json)) {
									$side_thubms = maybe_unserialize($design_json[0]->side_thumb);
									$html = '<th>'.__('Customized Design:', 'pdpinteg').'</th>';
									$html .= '<td><ul class="items">';
									$i=0;
									foreach($side_thubms as $side_thumb) {
										if($side_thumb['thumb']) {
											$i++;
											$last = $i%2==0?'last':'';
											$html .= '<li style="display:inline-block;margin-right:5px;" class="item '.$last.'"><a href="'.$url_tool.'/'.$side_thumb['thumb'].'" target="_blank"><img style="border:1px solid #C1C1C1;" width="66" src="'.$url_tool.'/'.$side_thumb['thumb'].'" /></a></li>';
										}
									}
									$html .= '</ul></td>';
									echo $html;
								}
							}
						} else {
					?>
					<th><?php echo wp_kses_post( $meta->display_key ); ?>:</th>
					<td><?php echo wp_kses_post( force_balance_tags( $meta->display_value ) ); ?></td>
					<?php } ?>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php endif; ?>
</div>
<div class="block-button">
	<span class="spinner" style="float: left;margin-top:10px;"></span>
	<a class="zip-design" href="javascript:void(0)" data-href="<?php echo $download_design ?>"><?php echo __('Download', 'pdpinteg') ?></a>
	<a class="edit-button" target="_blank" href="<?php echo $link_edit_design ?>"><?php echo __('Open Editor', 'pdpinteg') ?></a>
</div>
<div class="edit" style="display: none;">
	<table class="meta" cellspacing="0">
		<tbody class="meta_items">
			<?php if ( $meta_data = $item->get_formatted_meta_data( '' ) ) : ?>
				<?php foreach ( $meta_data as $meta_id => $meta ) :
					if ( in_array( $meta->key, $hidden_order_itemmeta ) ) {
						continue;
					}
					if(strpos($meta->display_key, 'pdpoptions') !== false || strpos($meta->display_key, 'pdpData') !== false) {
						continue;
					}
					?>
					<tr data-meta_id="<?php echo esc_attr( $meta_id ); ?>">
						<td>
							<input type="text" placeholder="<?php esc_attr_e( 'Name (required)', 'woocommerce' ); ?>" name="meta_key[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $meta_id ); ?>]" value="<?php echo esc_attr( $meta->key ); ?>" />
							<textarea placeholder="<?php esc_attr_e( 'Value (required)', 'woocommerce' ); ?>" name="meta_value[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $meta_id ); ?>]"><?php echo esc_textarea( rawurldecode( $meta->value ) ); ?></textarea>
						</td>
						<td width="1%"><button class="remove_order_item_meta button">&times;</button></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4"><button class="add_order_item_meta button"><?php _e( 'Add&nbsp;meta', 'woocommerce' ); ?></button></td>
			</tr>
		</tfoot>
	</table>
</div>

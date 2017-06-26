<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<tr class="pdp-item">
	<td class="woocommerce-table__product-name product-name">
		<?php
			echo $product->get_name();
			echo $content_html;
			?>
			
	</td>
	<td>
		<?php echo $product->get_price_html(); ?>
	</td>
	<td>
		<span class="item-status"><?php echo __('Pending', 'pdpinteg') ?></span>
		<div class="block-button">
			<a class="edit-button" href="<?php echo $link_edit_design ?>"> <?php echo __('Edit Design', 'pdpinteg') ?></a>
		</div>
	</td>
</tr>
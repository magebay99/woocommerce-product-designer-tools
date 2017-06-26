<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product );

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" method="post" enctype='multipart/form-data'>
		<?php
			/**
			 * @since 2.1.0.
			 */
			do_action( 'woocommerce_before_add_to_cart_button' );

			/**
			 * @since 3.0.0.
			 */
			do_action( 'woocommerce_before_add_to_cart_quantity' );

			/**
			 * @since 3.0.0.
			 */
			do_action( 'woocommerce_after_add_to_cart_quantity' );
			$pdp_helper = PDP_Helper::instance();
			$link_edit = $pdp_helper->get_link_design_pdp($product->get_sku());
			$button_title = $pdp_helper->get_label_custom();
		?>

		<div class="box-tocart">
			<div class="fieldset">
				<div class="actions">
					<a href="<?php echo $link_edit ?>" style="text-align:center;"
							title="<?php /* @escapeNotVerified */ echo $button_title ?>"
							class="pdpro_add_to_cart_button button tocart">
						<span><?php /* @escapeNotVerified */ echo $button_title ?></span>
					</a>
				</div>
			</div>
		</div>

		<?php
			/**
			 * @since 2.1.0.
			 */
			do_action( 'woocommerce_after_add_to_cart_button' );
		?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>

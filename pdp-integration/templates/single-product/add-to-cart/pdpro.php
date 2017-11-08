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
			$use_popup_design = $pdp_helper->use_popup_design();
		?>

		<div class="box-tocart">
			<div class="fieldset">
				<div class="actions">
					<?php if ($use_popup_design){ ?>
						<a href="javascript:void(0);" style="text-align:center;"
								title="<?php /* @escapeNotVerified */ echo $button_title ?>"
								class="pdpro_add_to_cart_button button tocart openpopup">
							<span><?php /* @escapeNotVerified */ echo $button_title ?></span>
						</a>
					<?php } else { ?>
						<a href="<?php echo $link_edit ?>" style="text-align:center;"
								title="<?php /* @escapeNotVerified */ echo $button_title ?>"
								class="pdpro_add_to_cart_button button tocart">
							<span><?php /* @escapeNotVerified */ echo $button_title ?></span>
						</a>
					<?php } ?>

				</div>
			</div>
		</div>
		<?php if ($use_popup_design){ ?>
		<div class="background-orverlay" style="display: none">
			<span class="please-wait">
				<img src="<?php echo plugins_url( 'assets/', dirname(dirname(dirname(__FILE__))) ) . 'frontend/images/iframe-loader.gif'; ?>" alt="<?php echo 'Please wait...' ?>" title="<?php echo 'Please wait...' ?>" class="v-middle" /> <?php echo 'Please wait...' ?>
			</span>
		</div>
		<div id="wrapper_iframe">

		</div>
		<style type="text/css">
			#pdp_iframe {
				background: rgba(0, 0, 0, 0) none repeat scroll 0 0; 
				border: 0px solid transparent;
				margin: auto; 
				position: fixed; 
				top: -100000px;
				z-index: 100000;
			}
			.background-orverlay {
				position: fixed;
				width: 100%;
				height: 100%;
				background-color: #fff;
				opacity: 0.9;
				top: 0;
				left: 0;
				z-index: 1000;
			}
			.please-wait {
				display: block;
				margin: auto;
				position: absolute;
				top: 50%;
				left: 47%;
				z-index: 9999;
			}		
		</style>
		<script type="text/javascript">
			jQuery.noConflict();
			jQuery(document).ready(function($){
						setTimeout(function() {
					$('#wrapper_iframe').html("<iframe id=\"pdp_iframe\" data-checkoutcart-url=\" <?php echo WC()->cart->get_cart_url()?>\"></iframe>");
					var isOpen = false;
					$('a[class*="openpopup"]').on('click', function(evt){
						if (isOpen) {
							$('iframe[id=pdp_iframe]').show();
						} else {
							$('div[class*="background-orverlay"]').show();
							$('iframe[id=pdp_iframe]').attr('src', '<?php echo $link_edit ?>&iframe=1');
							isOpen = true;
						}
						
					});
					$('iframe[id=pdp_iframe]').load(function() {
						$('div[class*="background-orverlay"]').hide();
						$(this).css({
							"width": "100%",
							"height" : "100%",
							"left" : "0",
							"top" : "0",
							"right" : "0"
						});
						$(this).contents().find('body').css({
							'padding': '10px 3%'
						}).children('.toppage').css({
							'left': '3%',
							'width': '94%'
						});
					});			
				},400);
			});		
		</script>
		<?php } ?>
		<?php
			/**
			 * @since 2.1.0.
			 */
			do_action( 'woocommerce_after_add_to_cart_button' );
		?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php endif; ?>

<?php
/**
 * Plugin Name: PDP Integration 
 * Version: 0.1.0
 * Description: Pulgin to connect with tool P+ design
 * Author: PDP
 * Text Domain: pdpinteg
 */
defined( 'ABSPATH' ) or exit;

if ( !class_exists( 'PDP_Integration' ) ) {
	class PDP_Integration {
		
		public static $plugin_slug;
		public static $plugin_prefix;
		public static $plugin_url;
		public static $plugin_path;
		public static $plugin_basename;
		public static $version;
		public $settings;
		
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
		 * Construct.
		 */
		public function __construct() {
			self::$plugin_slug = basename(dirname(__FILE__));
			self::$plugin_prefix = 'pdpinteg_';
			self::$plugin_basename = plugin_basename(__FILE__);
			self::$plugin_url = plugin_dir_url(self::$plugin_basename);
			self::$plugin_path = trailingslashit(dirname(__FILE__));
			self::$version = '0.1.0';
			
			$this->define_constants();
			$this->init_hooks();
		}
		
		/**
		 * Hook into actions and filters.
		 * @since  2.3
		 */
		private function init_hooks() {
			add_action( 'wp_enqueue_scripts', array( $this, 'setup_styles' ) );	
			// load the localisation & classes
			add_action( 'init', array( $this, 'load_classes' ) );
			//add_action( 'plugins_loaded', array( $this, 'pdp_plugins_loaded' ) );
			include_once( PDP_ABSPATH . 'includes/class-pdp-customer.php' );
			include_once( PDP_ABSPATH . 'includes/pdp-template-functions.php' );
			include_once( PDP_ABSPATH . 'includes/class-pdp-install.php' );
			//add_action( 'plugins_loaded', array( 'PDP_Install', 'install' ) );
			
			register_activation_hook( __FILE__, array( 'PDP_Install', 'install' ) );
			//add_filter('woocommerce_add_cart_item_data',array($this, 'prepare_cart_item_data'),1,2);
		}
		
		public function pdp_plugins_loaded() {
			add_filter('wc_get_template',array( $this, 'get_template_part'));
		}
		
		/**
		 * Define PDP Constants.
		 */
		private function define_constants() {
			$this->define( 'PDP_ABSPATH', dirname( __FILE__ ) . '/' );
		}
		
		/**
		 * Define constant if not already set.
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}
		
		/**
		 * Load the main plugin classes and functions
		 */
		public function includes() {
			/**
			 * Core classes.
			 */
			include_once( PDP_ABSPATH . 'includes/class-pdp-settings.php' );
			include_once( PDP_ABSPATH . 'includes/class-pdp-design-json.php' );
			include_once( PDP_ABSPATH . 'includes/class-pdp-helper.php' );
			include_once( PDP_ABSPATH . 'includes/class-wc-product-pdpro.php' );
			include_once( PDP_ABSPATH . 'includes/class-pdp-cart.php' );
			include_once( PDP_ABSPATH . 'includes/class-pdp-ajax.php' );
			include_once( PDP_ABSPATH . 'includes/class-pdp-order-item.php' );
			include_once( PDP_ABSPATH . 'includes/class-pdp-product-type.php' );
			include_once( PDP_ABSPATH . 'includes/class-pdp-guest-design.php' );
			
			/**
			 * REST API.
			 */
			include_once( PDP_ABSPATH . 'includes/class-pdp-api.php' ); // API Class
		}
		
		/**
		 * Setup styles
		 */
		public function setup_styles() {
			wp_enqueue_style( 'nb-styles', plugins_url( '/assets/frontend/css/style.css', __FILE__ ) );
		}
		
		/**
		* Load lib js
		* 
		* @return void
		*/
		public function load_scripts() {
			wp_register_script( 'pdpinteg-pdp', plugins_url('/assets/frontend/js/pdp.js', __FILE__), array('jquery'), '0.1.0', true );
			wp_enqueue_script( 'pdpinteg-pdp' );
		}
		
		/**
		 * Load classes
		 * @return void
		 */
		public function load_classes() {
			if ( $this->is_woocommerce_activated() === false ) {
				add_action( 'admin_notices', array ( $this, 'need_woocommerce' ) );
				return;
			}
			// all systems ready - GO!
			$this->includes();
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_filter( 'product_type_selector', array( 'WC_Product_Pdpro', 'add_pdpro_product' ) );
			add_action( 'product_type_options', array( 'WC_Product_Pdpro', 'custom_product_data_option' ) );
			add_filter( 'woocommerce_product_data_tabs', array( 'WC_Product_Pdpro', 'add_option_product_data_tabs' ) );
			add_action( 'admin_footer', array( 'WC_Product_Pdpro', 'pdpro_custom_js' ) );
			add_filter( 'woocommerce_cart_item_name', array( 'PDP_Cart', 'add_custom_option_from_session_into_cart' ),1,3);
			add_action( 'woocommerce_add_order_item_meta', array( 'PDP_Cart', 'pdp_add_values_to_order_item_meta' ),1,2);
			add_action( 'woocommerce_before_calculate_totals', array( 'PDP_Cart', 'add_custom_price' ) );
			add_action( 'woocommerce_display_item_meta', array( 'PDP_Order_Item', 'pdp_display_item_meta' ),1,2);
			add_filter( 'woocommerce_before_order_itemmeta', array( 'PDP_Order_Item', 'pdp_before_add_order_itemmeta' ),1,3);
			add_filter( 'woocommerce_after_order_itemmeta', array( 'PDP_Order_Item', 'pdp_after_add_order_itemmeta' ),1,3);
			
			$this->settings = new PDP_Integration_Settings();
			$this->pdp_api   = new PDP_API();
		}
		
		/**
		 * Check if woocommerce is activated
		 */
		public function is_woocommerce_activated() {
			$blog_plugins = get_option( 'active_plugins', array() );
			$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();

			if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * WooCommerce not active notice.
		 *
		 * @return string Fallack notice.
		 */
		 
		public function need_woocommerce() {
			$error = sprintf( __( 'PDP Integration requires %sWooCommerce%s to be installed & activated!', 'pdpinteg' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );
			
			$message = '<div class="error"><p>' . $error . '</p></div>';
		
			echo $message;
		}
		
		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
	}
}

/**
 * Returns the main instance of PDP Integration to prevent the need to use globals.
 *
 * @return PDP_Integration
 */
function PDPINTEG() {
	return PDP_Integration::instance();
}

// Global for backwards compatibility.
$GLOBALS['pdpinteg'] = PDPINTEG();
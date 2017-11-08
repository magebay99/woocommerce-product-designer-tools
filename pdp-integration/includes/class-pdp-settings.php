<?php
defined( 'ABSPATH' ) or exit;

/**
 * Settings class
 */
if ( ! class_exists( 'PDP_Integration_Settings' ) ) {
	class PDP_Integration_Settings {
		
		public $options_page_hook;
		
		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'menu' ) ); // Add menu.
			add_action( 'admin_init', array( &$this, 'init_settings' ) ); // Registers settings
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_filter( 'plugin_action_links_'.PDP_Integration::$plugin_basename, array( &$this, 'pdpinteg_add_settings_link' ) );
		}
		
		/**
		 * Add menu page
		 */
		public function menu() {
			$parent_slug = 'woocommerce';
			
			$this->options_page_hook = add_submenu_page(
				$parent_slug,
				__( 'PDP Intergration', 'pdpinteg' ),
				__( 'PDP Intergration', 'pdpinteg' ),
				'manage_woocommerce',
				'pdpinteg_options_page',
				array( $this, 'settings_page' )
			);
		}
		
		/**
		 * scripts function.
		 */
		public function enqueue_admin_scripts() {
			wp_enqueue_style ('pdpinteg_admin_menu_styles', plugins_url('/assets/admin/css/style.css', dirname(__FILE__)));
			wp_enqueue_script('pdpinteg_admin', plugins_url('/assets/admin/js/pdp.js', dirname(__FILE__)), array('jquery'), '0.1.0', true);	
		}
		
		/**
		 * Build option page
		 */
		public function settings_page() {
			include('admin/view/html-admin-form-config.php');
		}
		
		/**
		 * User settings.
		 */
		public function init_settings() {
			
			$option = 'pdpinteg_settings';
		
			// Create option in wp_options.
			if ( false === get_option( $option ) ) {
				$this->default_settings( $option );
			}
			
			// Section.
			add_settings_section(
				'plugin_settings',
				__( 'Plugin settings', 'wpmenucart' ),
				array( &$this, 'section_options_callback' ),
				$option
			);
			
			add_settings_field(
				'api_ajaxcart',
				__( "Enabled Api Add Cart", 'pdpinteg' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'plugin_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'api_ajaxcart',
					'description'	=> __( 'Enable this option to use the built-in AJAX Api cart', 'pdpinteg' ),
				)
			);
			
			add_settings_field(
				'url_pdp',
				__( 'Url P+', 'pdpinteg' ),
				array( &$this, 'text_element_callback' ),
				$option,
				'plugin_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'url_pdp',
				)
			);
			
			add_settings_field(
				'label_custom',
				__( 'Label Button Custom', 'pdpinteg' ),
				array( &$this, 'text_element_callback' ),
				$option,
				'plugin_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'label_custom',
				)
			);

			add_settings_field(
				'use_popup_design',
				__( 'Use Popup for Design', 'pdpinteg' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'plugin_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'use_popup_design',
					'description'	=> __( 'Using Popup for design product', 'pdpinteg' ),
				)
			);
			
			// Register settings.
			register_setting( $option, $option, array( &$this, 'validate_options' ) );
			
		}
		
		/**
		 * Validate/sanitize options input
		 */
		public function validate_options( $input ) {
			// Create our array for storing the validated options.
			$output = array();

			// Loop through each of the incoming options.
			foreach ( $input as $key => $value ) {

				// Check to see if the current option has a value. If so, process it.
				if ( isset( $input[$key] ) ) {
					// Strip all HTML and PHP tags and properly handle quoted strings.
					if ( is_array( $input[$key] ) ) {
						foreach ( $input[$key] as $sub_key => $sub_value ) {
							$output[$key][$sub_key] = strip_tags( stripslashes( $input[$key][$sub_key] ) );
						}

					} else {
						$output[$key] = strip_tags( stripslashes( $input[$key] ) );
					}
				}
			}

			// Return the array processing any additional functions filtered by this action.
			return apply_filters( 'pdpinteg_validate_input', $output, $input );
		}
		
		/**
		 * Text field callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string	  Text field.
		 */
		public function text_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
			$size = isset( $args['size'] ) ? $args['size'] : '25';
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}

			$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
			$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"%5$s/>', $id, $menu, $current, $size, $disabled );
			
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
			
			echo $html;
		}
		
	/**
	 * Checkbox field callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Checkbox field.
	 */
	public function checkbox_element_callback( $args ) {
		$menu = $args['menu'];
		$id = $args['id'];
	
		$options = get_option( $menu );
	
		if ( isset( $options[$id] ) ) {
			$current = $options[$id];
		} else {
			$current = isset( $args['default'] ) ? $args['default'] : '';
		}
	
		$disabled = (isset( $args['disabled'] )) ? ' disabled' : '';
		$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s %4$s/>', $id, $menu, checked( 1, $current, false ), $disabled );
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}
			
		echo $html;
	}
		
		/**
		 * Set default settings.
		 */
		public function default_settings( $option ) {
			$default = array(
				'api_ajaxcart'	=> '1',
				'label_custom' => __('Customize It', 'pdpinteg'),
				'use_popup_design' => '0'
			);
			update_option( $option, $default );
		}
		
		/**
		 * Section null callback.
		 *
		 * @return void.
		 */
		public function section_options_callback() {
			
		}
		
		/**
		 * Add settings link to plugins page
		 */
		public function pdpinteg_add_settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=pdpinteg_options_page">'. __( 'Settings', 'pdpinteg' ) . '</a>';
			array_push( $links, $settings_link );
			return $links;
		}
	}
}
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDP_Helper Class
 */
class PDP_Helper {
	
	const PRODUCT_PDP = 'pdpro';
	
	protected $array_type_select;
	
	protected static $_instance = null;
	
	protected $options;
	
	public function __construct(){ 
		$this->array_type_select = array('drop_down','radio','checkbox','multiple','hidden');
		$this->options = get_option('pdpinteg_settings');
	}
	
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
     * Retrieve url PDP tool design
     * @return String
     */
	public function get_url_tool_design() {
		if(isset($this->options['url_pdp'])) {
			$url = $this->options['url_pdp'];
			if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
				$baseUrl = get_site_url();
				return $baseUrl.$url;
			} else {
				return $url;
			}
		}
		return '';
	}
	
	/**
     * Retrieve true if Using popup for design
     * @return boolean
	 */
	public function use_popup_design() {
		return  isset($this->options['use_popup_design'])?(bool)$this->options['use_popup_design']: false;
	}	
        
        /**
     * Retrieve true if Using same database with store
     * @return boolean
	 */
	public function check_is_same_db() {
		return  isset($this->options['separate_db'])?(bool)$this->options['separate_db']: false;
	}	
	
	/**
	 * Retrieve label button design
	 * @return string
	 */
	public function get_label_custom() {
        if(isset($this->options['label_custom'])) {
			return $this->options['label_custom'];
		}
		return '';
	}
	
	/**
	* Retrieve link download zip design.
	* 
	* @param int $design_id
	* @return string
	*/
	public function get_link_download($design_id) {
		$url_tool = $this->get_url_tool_design();
		$param = '';
		if($design_id) {
			$param .= 'rest/design-download?id='.$design_id.'&zip=1';
		} else {
			$param = '#';
		}
		if(substr($url_tool, -1) == '/') {
			$url_tool .= $param;
		} else {
			$url_tool .= '/'.$param;
		}
		return $url_tool;
	}
	
	/**
	 * @param string $sku
	 * @return String
	 */
	public function get_link_design_pdp($sku) {
		$product_type = PDP_Product_Type::instance()->get_product_with_sku($sku);
		$url = '#';
		if(!empty($product_type)) {
			$data_object = $product_type[0];
			if($data_object->type_id) {
				$url = $this->get_url_tool_design().'/?pid='.$data_object->type_id;
			}
		}
		return $url;
	}	
	
    /**
     * Retrieve true if PDP Integration is enabled
     * @return boolean
     */
    public function status_pdp_integration()
    {
        return (bool) $this->options['api_ajaxcart'];
    }
	
	/**
	 * @param array $value
	 * @return bolean
	 */
	protected function check_multiple_size( array $value) {
		if(isset($value['qnty_input']) && $value['qnty_input']) {
			if(isset($value['type']) && $value['type'] == 'checkbox') {
				return true;
			}
		}
		return false;
	}
	
	/**
	* 
	* @param int $design_id
	* @param string $sku
	* @return string
	*/
	public function getLinkEditDesignFrontend( $design_id, $sku ) {
		$url = $this->get_url_tool_design();
		$product_type = PDP_Product_Type::instance()->get_product_with_sku($sku);
		$pdp_product_id = $product_type[0]->type_id;
		$param = '';
		if(!$pdp_product_id || !$design_id) {
			return $url;
		}
		if($pdp_product_id) {
			$param .= '?pid='.$pdp_product_id;
		}
		if($design_id) {
			$param .= '&tid='.$design_id;
		}
		if(substr($url, -1) == '/') {
			$url .= $param;
		} else {
			$url .= '/'.$param;
		}
		return $url;
	}	
	
	/**
	* 
	* @param int $design_id
	* @param string $sku
	* @return string
	*/
	public function getLinkEditDesignBackend( $design_id, $sku ) {
		$url = $this->get_url_tool_design();
		$product_type = PDP_Product_Type::instance()->get_product_with_sku($sku);
		$pdp_product_id = $product_type[0]->type_id;
		$param = '';
		if(!$pdp_product_id || !$design_id) {
			return $url;
		}
		if($design_id) {
			$param .= '?export-design='.$design_id;
		}
		if($pdp_product_id) {
			$param .= '&pid='.$pdp_product_id;
		}

		if(substr($url, -1) == '/') {
			$url .= $param;
		} else {
			$url .= '/'.$param;
		}
		return $url;
	}
	
	/**
     * @param array $options
	 *
	 * @return array()
     */	
	public function get_addition_option(array $options) {
		$additionalOptions = array();
		foreach($options as $key => $val) {
			$item = array(
				'id' => $val['option_id'],
				'label' => __($val['title'], 'pdpinteg'),
				'value' => ''
			);
			if(in_array($val['type'],$this->array_type_select)) {
				$value = array();
				foreach($val['values'] as $_key => $_val) {
					if(intval($_val['checked']) && $_val['selected'] && !$_val['disabled']) {
						$value[] = __($_val['title'], 'pdpinteg');
					}
				}
				$item['value'] = implode(",", $value);
			} elseif($val['type'] == 'field' || $val['type'] == 'area') {
				if($val['default_text']) {
					$item['value'] = $val['default_text'];
				}
			} elseif($val['type'] == 'file') {
				
			}
			$additionalOptions[] = $item;
		}
		return $additionalOptions;
	}
	
	/**
     * @param array $options
	 *
	 * @return array()
     */		
	public function get_options_select( array $options) {
		$_result = array('multiSize'=>false, 'multiSizeOpt' => array(), 'options' => array());
		$result = array();
		$_key = 0;
		foreach($options as $key => $val) {
			if(!$val['disabled']) {
				if(in_array($val['type'],$this->array_type_select)) {
					$_result['multiSize'] = $this->check_multiple_size($val);
					$flag=false;
					$optVal = array();
					foreach($val['values'] as $opt_key => $opt_val) {
						if(intval($opt_val['checked']) && $opt_val['selected'] && !$opt_val['disabled']) {
							$optVal[] = $opt_val;
							$flag = true;
						}
					}
					if($flag) {
						if($_result['multiSize']) {
							$_result['multiSizeOpt'] = $val;
							$_result['multiSizeOpt']['values'] = $optVal;
						} else {
							$result[$_key] = $val;
							$result[$_key]['values'] = $optVal;
							$_key++;
						}
					} else {
						$_result['multiSize'] = false;
					}
				} elseif($val['type'] == 'field' || $val['type'] == 'area') {
					if($val['default_text']) {
						$result[$_key] = $val;
						$_key++;
					}
				} elseif($val['type'] == 'file') {
					
				}
			}
		}
		$_result['options'] = $result;
		return $_result;
	}
	
	/**
     * @param array $options
	 *
	 * @return array()
     */		
    public function get_optinfor_request( array $options) {
		$infoRequest = array(
			'pdp_options' => array(),
			'pdp_price' => 0
		);
		$pdpPrice = 0;
		foreach($options as $key => $val) {
			$optId = $val['option_id'];
			if(in_array($val['type'],$this->array_type_select)) {
				$qty_input = false;
				$value = array();
				if($val['qnty_input']) {
					$qty_input = true;
				}
				foreach($val['values'] as $_key => $_val) {
					if(intval($_val['checked']) && $_val['selected'] && !$_val['disabled']) {
						$value[] = $_val['option_type_id'];
						if(intval($_val['qty']) > 1 && $qty_input) {
							$pdpPrice = $pdpPrice + floatval($_val['price'])*intval($_val['qty']);
						} else {
							$pdpPrice += floatval($_val['price']);
						}
					}
				}
				$infoRequest['pdp_options'][$optId] = implode(",", $value);
			} elseif($val['type'] == 'field' || $val['type'] == 'area') {
				if($val['default_text']) {
					$infoRequest['pdp_options'][$optId] = $val['default_text'];
					$pdpPrice += floatval($val['price']);
				}
			} elseif($val['type'] == 'file') {
				
			}
			$infoRequest['pdp_price'] = $pdpPrice;
		}
		return $infoRequest;
	}
}
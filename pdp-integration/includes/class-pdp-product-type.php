<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * PDP_Product_Type Class
 */
class PDP_Product_Type {

    protected static $_instance = null;

    /**
     * Main Plugin Instance
     *
     * Ensures only one instance of plugin is loaded or can be loaded.
     */
    public static function instance() {
        if (is_null(self::$_instance)) { 
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param string $sku
     * @return array
     */
    public function get_product_with_sku($sku) {
        $isSameDb = PDP_Helper::instance()->check_is_same_db();
        if ($isSameDb) {
            $pdpUrl = PDP_Helper::instance()->get_url_tool_design();
            $request = wp_remote_get(rtrim($pdpUrl,'/').'/rest/commerce?product&sku='.$sku);
            if (is_wp_error($request)) {
                return false; // Bail early
            }
            $body = wp_remote_retrieve_body($request);
            $data = json_decode($body);
            if ($data) {
                if ($data && $data->status == 'success' && count($data->data) > 0) {
                    return $data->data;
                } else {
                    return [];
                }
            }
        } else {
            global $wpdb;
            $table_name = "pdp_product_type";
            $old_rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE sku = %s", $sku));
            return $old_rows;
        }
    }

}

<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * PDP_Desgin Class
 */
class PDP_Design_Json {

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
     * @param int $design_id
     * @return array|object
     */
    public function get_design_by_design_id($design_id) {
        $isSameDb = PDP_Helper::instance()->check_is_same_db();
        if ($isSameDb) {
            $pdpUrl = PDP_Helper::instance()->get_url_tool_design();
            $request = wp_remote_get(rtrim($pdpUrl,'/'). '/rest/commerce?design&params_id='.$design_id);
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
            $table_name = "pdp_design_json";
            $old_rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE design_id = %d", $design_id));
            return $old_rows;
        }
    }

}

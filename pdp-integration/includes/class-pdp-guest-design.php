<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDP_Guest_Design Class
 */
class PDP_Guest_Design {
	
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
	 * Update guest design
	 * @param array $guest_design
	 * @return void
	 */
	public function save_guest_design( $guest_design ) {
		global $wpdb;
		
		$table_name =  $wpdb->prefix."pdp_guest_design";
		if(isset($guest_design['user_id']) && $guest_design['user_id']) {
			$user_id = $guest_design['user_id'];
			unset($guest_design['user_id']);
			$wpdb->update(
				$table_name,
				$guest_design,
				array( 
					'user_id' => $user_id,
				)
			);
		} elseif(isset($guest_design['entity_id']) && $guest_design['entity_id']) {
			$entity_id = $guest_design['entity_id'];
			unset($guest_design['entity_id']);
			$wpdb->update(
				$table_name,
				$guest_design,
				array( 
					'entity_id' => $entity_id,
				)
			);
		}
	}
	
	/**
	 * Update guest design 
	 * @param array $guest_design
	 * @param int $user_id
	 * @return void
	 */
	public function update_guest_design_by_user_id( $guest_design, $user_id ) {
		global $wpdb;
		$table_name =  $wpdb->prefix."pdp_guest_design";
		if( isset($guest_design['user_id']) ) {
			unset($guest_design['user_id']);
		}
		$wpdb->update(
			$table_name,
			$guest_design,
			array( 
				'user_id' => $user_id,
			)
		);
	}
	
	/**
	 * Update guest design
	 * @param array $guest_design
	 * @param int $entity_id
	 * @return void
	 */
	public function update_guest_design_by_id($guest_design, $entity_id) {
		global $wpdb;
		$table_name =  $wpdb->prefix."pdp_guest_design";
		if( isset($guest_design['entity_id']) ) {
			unset($guest_design['entity_id']);
		}
		$wpdb->update(
			$table_name,
			$guest_design,
			array( 
				'entity_id' => $entity_id,
			)
		);
	}
	
	/**
	 * Insert a new guest design
	 * @param array $guest_design
	 * @return int
	 */
	public function insert_guest_design( $guest_design ) {
		global $wpdb;
		
		$table_name =  $wpdb->prefix."pdp_guest_design";
		$wpdb->insert( $table_name, $guest_design );
		return $wpdb->insert_id;
	}
	
	/**
	 * Load data guest design 
	 * @param int $id
	 * @return array
	 */
	public function load_guest_design_by_id( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix."pdp_guest_design";
		$old_rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_name WHERE entity_id = %d", $id));
		return $old_rows;
	}
	
	/**
	 * Delete guest design
	 * @param int $id
	 * @return void
	 */
	public function delete_guest_design( $id ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}pdp_guest_design WHERE entity_id = %d;", $id ) );
	}
	
	/**
	 * load design by customer id
	 * @return array|object
	 */
	public function load_design_by_customer_id( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix."pdp_guest_design";
		$old_rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $id));
		return $old_rows;
	}
}
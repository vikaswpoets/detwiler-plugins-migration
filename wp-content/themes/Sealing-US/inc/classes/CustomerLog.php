<?php
class CustomerLog {

	const LOG_DB_NAME = 'customer_change_logs';

	public function __construct() {
		add_action( 'init', array( $this, 'create_table' ) );
	}

	public function create_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::LOG_DB_NAME;

		// Check if the table already exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
		        id mediumint(9) NOT NULL AUTO_INCREMENT,
		        change_by_id mediumint(9) NOT NULL,
		        user_id mediumint(9) NOT NULL,
		        data text NOT NULL,
		        change_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		        PRIMARY KEY (id)
		    ) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

	public static function log( $user_by, $user_id, $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::LOG_DB_NAME;

		$wpdb->insert(
			$table_name,
			array(
				'change_by_id' => $user_by,
				'user_id'      => $user_id,
				'data'         => $data,
				'change_date'  => current_time( 'mysql' ),
			)
		);
	}

	public static function get( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::LOG_DB_NAME;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE user_id = %d ORDER BY change_date DESC",
				$user_id
			)
		);
	}
}

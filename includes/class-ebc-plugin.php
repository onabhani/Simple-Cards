<?php
/**
 * Main plugin bootstrap.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EBC_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var EBC_Plugin|null
	 */
	private static ?EBC_Plugin $instance = null;

	/**
	 * Get singleton.
	 *
	 * @return EBC_Plugin
	 */
	public static function instance(): EBC_Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

		EBC_Post_Type::instance();
		EBC_Meta_Boxes::instance();
		EBC_Settings::instance();
		EBC_Shortcodes::instance();
		EBC_VCard::instance();
		EBC_Assets::instance();
	}

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate(): void {
		EBC_Post_Type::register_post_type();
		flush_rewrite_rules();
	}

	/**
	 * Deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Load i18n files.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'employee-business-cards', false, dirname( plugin_basename( EBC_FILE ) ) . '/languages' );
	}
}

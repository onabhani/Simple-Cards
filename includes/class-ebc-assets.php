<?php
/**
 * Asset loading.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EBC_Assets {

	/** @var EBC_Assets|null */
	private static ?EBC_Assets $instance = null;

	/**
	 * @return EBC_Assets
	 */
	public static function instance(): EBC_Assets {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_single_assets' ) );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix Hook.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		global $post_type;
		if ( 'employee_card' !== $post_type ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'ebc-admin', EBC_URL . 'assets/css/admin.css', array(), EBC_VERSION );
		wp_enqueue_script( 'ebc-admin', EBC_URL . 'assets/js/admin.js', array( 'jquery' ), EBC_VERSION, true );

		wp_localize_script(
			'ebc-admin',
			'ebcAdmin',
			array(
				'title'  => __( 'Select Profile Photo', 'employee-business-cards' ),
				'button' => __( 'Use This Image', 'employee-business-cards' ),
			)
		);
	}

	/**
	 * Enqueue public assets on single employee cards.
	 *
	 * @return void
	 */
	public function maybe_enqueue_single_assets(): void {
		if ( is_singular( 'employee_card' ) ) {
			self::enqueue_public_assets();
		}
	}

	/**
	 * Register and enqueue public assets.
	 *
	 * @return void
	 */
	public static function enqueue_public_assets(): void {
		wp_enqueue_style(
			'ebc-fontawesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
			array(),
			'6.5.2'
		);
		wp_enqueue_style( 'ebc-public', EBC_URL . 'assets/css/public.css', array( 'ebc-fontawesome' ), EBC_VERSION );
		wp_enqueue_script( 'ebc-public', EBC_URL . 'assets/js/public.js', array(), EBC_VERSION, true );

		$settings     = ebc_get_settings();
		$primary      = isset( $settings['primary_color'] ) ? sanitize_hex_color( (string) $settings['primary_color'] ) : '#1d4ed8';
		$button_style = isset( $settings['button_style'] ) && 'square' === $settings['button_style'] ? '4px' : '999px';

		$css = ':root{--ebc-primary:' . esc_attr( $primary ? $primary : '#1d4ed8' ) . ';--ebc-button-radius:' . esc_attr( $button_style ) . ';}';
		wp_add_inline_style( 'ebc-public', $css );
	}
}

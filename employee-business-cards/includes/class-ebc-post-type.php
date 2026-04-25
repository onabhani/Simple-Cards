<?php
/**
 * Custom post type logic.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EBC_Post_Type {

	/**
	 * Instance.
	 *
	 * @var EBC_Post_Type|null
	 */
	private static ?EBC_Post_Type $instance = null;

	/**
	 * Get instance.
	 *
	 * @return EBC_Post_Type
	 */
	public static function instance(): EBC_Post_Type {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	private function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_filter( 'template_include', array( $this, 'load_single_template' ) );
		add_filter( 'manage_employee_card_posts_columns', array( $this, 'admin_columns' ) );
		add_action( 'manage_employee_card_posts_custom_column', array( $this, 'render_admin_column' ), 10, 2 );
	}

	/**
	 * Register employee_card post type.
	 *
	 * @return void
	 */
	public static function register_post_type(): void {
		$labels = array(
			'name'               => esc_html__( 'Employee Cards', 'employee-business-cards' ),
			'singular_name'      => esc_html__( 'Employee Card', 'employee-business-cards' ),
			'add_new_item'       => esc_html__( 'Add New Employee Card', 'employee-business-cards' ),
			'edit_item'          => esc_html__( 'Edit Employee Card', 'employee-business-cards' ),
			'add_new'            => esc_html__( 'Add New', 'employee-business-cards' ),
			'new_item'           => esc_html__( 'New Employee Card', 'employee-business-cards' ),
			'view_item'          => esc_html__( 'View Employee Card', 'employee-business-cards' ),
			'search_items'       => esc_html__( 'Search Employee Cards', 'employee-business-cards' ),
			'not_found'          => esc_html__( 'No employee cards found.', 'employee-business-cards' ),
			'not_found_in_trash' => esc_html__( 'No employee cards found in Trash.', 'employee-business-cards' ),
			'menu_name'          => esc_html__( 'Employee Cards', 'employee-business-cards' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-id-alt',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'        => false,
			'rewrite'            => array(
				'slug'       => 'employee-card',
				'with_front' => false,
			),
			'show_in_rest'       => false,
			'publicly_queryable' => true,
			'exclude_from_search'=> false,
		);

		register_post_type( 'employee_card', $args );
	}

	/**
	 * Load plugin single template.
	 *
	 * Renders a clean, theme-chrome-free page for both single employee cards
	 * and any post/page that embeds the [employee_business_card] shortcode.
	 *
	 * @param string $template Current template.
	 * @return string
	 */
	public function load_single_template( string $template ): string {
		if ( is_singular( 'employee_card' ) ) {
			$theme_template = locate_template( array( 'single-employee_card.php' ), false, false );
			if ( ! empty( $theme_template ) ) {
				return $template;
			}

			$settings = ebc_get_settings();
			if ( ! empty( $settings['hide_theme_chrome'] ) ) {
				$clean = EBC_PATH . 'templates/clean-page.php';
				if ( file_exists( $clean ) ) {
					return $clean;
				}
			}

			$plugin_template = EBC_PATH . 'templates/single-card.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}

			return $template;
		}

		if ( ! is_singular() ) {
			return $template;
		}

		$post = get_post();
		if ( ! $post instanceof WP_Post ) {
			return $template;
		}

		if ( false === strpos( (string) $post->post_content, '[employee_business_card' ) ) {
			return $template;
		}

		$settings = ebc_get_settings();
		if ( empty( $settings['hide_theme_chrome'] ) ) {
			return $template;
		}

		if ( ! has_shortcode( (string) $post->post_content, 'employee_business_card' ) ) {
			return $template;
		}

		$clean = EBC_PATH . 'templates/clean-page.php';
		if ( file_exists( $clean ) ) {
			return $clean;
		}

		return $template;
	}

	/**
	 * Admin list columns.
	 *
	 * @param array<string,string> $columns Columns.
	 * @return array<string,string>
	 */
	public function admin_columns( array $columns ): array {
		$new_columns = array(
			'cb'         => $columns['cb'] ?? '',
			'photo'      => esc_html__( 'Photo', 'employee-business-cards' ),
			'title'      => esc_html__( 'Name', 'employee-business-cards' ),
			'job_title'  => esc_html__( 'Job Title', 'employee-business-cards' ),
			'company'    => esc_html__( 'Company', 'employee-business-cards' ),
			'department' => esc_html__( 'Department', 'employee-business-cards' ),
			'public_url' => esc_html__( 'Public URL', 'employee-business-cards' ),
			'date'       => $columns['date'] ?? esc_html__( 'Date', 'employee-business-cards' ),
		);

		return $new_columns;
	}

	/**
	 * Render admin list custom column.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_admin_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'photo':
				$photo_url = ebc_get_card_photo_url( $post_id, 'thumbnail' );
				if ( $photo_url ) {
					echo '<img src="' . esc_url( $photo_url ) . '" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;" />';
				} else {
					echo '&mdash;';
				}
				break;
			case 'job_title':
				echo esc_html( (string) ebc_get_field_value( $post_id, 'job_title' ) ?: '—' );
				break;
			case 'company':
				echo esc_html( (string) ebc_get_field_value( $post_id, 'company_name' ) ?: '—' );
				break;
			case 'department':
				echo esc_html( (string) ebc_get_field_value( $post_id, 'department' ) ?: '—' );
				break;
			case 'public_url':
				$url = ebc_get_card_url( $post_id );
				if ( $url ) {
					echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View Card', 'employee-business-cards' ) . '</a>';
				}
				break;
		}
	}
}

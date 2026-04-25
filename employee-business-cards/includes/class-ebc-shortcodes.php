<?php
/**
 * Shortcodes.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EBC_Shortcodes {

	/** @var EBC_Shortcodes|null */
	private static ?EBC_Shortcodes $instance = null;

	/**
	 * @return EBC_Shortcodes
	 */
	public static function instance(): EBC_Shortcodes {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	private function __construct() {
		add_shortcode( 'employee_business_card', array( $this, 'single_card_shortcode' ) );
		add_shortcode( 'employee_business_cards', array( $this, 'cards_grid_shortcode' ) );
	}

	/**
	 * Single card shortcode.
	 */
	public function single_card_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'employee_business_card'
		);

		$post_id = absint( $atts['id'] );
		if ( $post_id <= 0 || 'employee_card' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
			return '';
		}

		EBC_Assets::enqueue_public_assets();
		return $this->render_card( $post_id );
	}

	/**
	 * Grid shortcode.
	 */
	public function cards_grid_shortcode( array $atts ): string {
		$atts = shortcode_atts(
			array(
				'company'    => '',
				'department' => '',
				'limit'      => 12,
				'columns'    => 3,
			),
			$atts,
			'employee_business_cards'
		);

		$limit   = max( 1, min( 100, absint( $atts['limit'] ) ) );
		$columns = max( 1, min( 4, absint( $atts['columns'] ) ) );

		$meta_query = array();
		if ( '' !== $atts['company'] ) {
			$meta_query[] = array(
				'key'     => '_ebc_company_name',
				'value'   => sanitize_text_field( $atts['company'] ),
				'compare' => '=',
			);
		}

		if ( '' !== $atts['department'] ) {
			$meta_query[] = array(
				'key'     => '_ebc_department',
				'value'   => sanitize_text_field( $atts['department'] ),
				'compare' => '=',
			);
		}

		$query_args = array(
			'post_type'           => 'employee_card',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'ignore_sticky_posts' => true,
		);

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $query_args );
		if ( ! $query->have_posts() ) {
			return '';
		}

		EBC_Assets::enqueue_public_assets();
		ob_start();
		echo '<div class="ebc-card-grid columns-' . esc_attr( (string) $columns ) . '">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();
			include EBC_PATH . 'templates/card-grid-item.php';
		}
		echo '</div>';
		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * Render single card template part.
	 */
	public function render_card( int $post_id ): string {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return '';
		}

		ob_start();
		include EBC_PATH . 'templates/single-card.php';
		return (string) ob_get_clean();
	}
}

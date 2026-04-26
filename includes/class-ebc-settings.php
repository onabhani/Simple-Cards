<?php
/**
 * Plugin settings page.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EBC_Settings {

	/** @var EBC_Settings|null */
	private static ?EBC_Settings $instance = null;

	/**
	 * Singleton.
	 *
	 * @return EBC_Settings
	 */
	public static function instance(): EBC_Settings {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings submenu.
	 *
	 * @return void
	 */
	public function register_settings_page(): void {
		add_submenu_page(
			'edit.php?post_type=employee_card',
			esc_html__( 'Employee Card Settings', 'employee-business-cards' ),
			esc_html__( 'Settings', 'employee-business-cards' ),
			'manage_options',
			'ebc-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings section and fields.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting( 'ebc_settings_group', 'ebc_settings', array( $this, 'sanitize_settings' ) );

		add_settings_section(
			'ebc_main_section',
			esc_html__( 'General Settings', 'employee-business-cards' ),
			'__return_false',
			'ebc-settings'
		);

		$this->add_field( 'default_company_name', esc_html__( 'Default company name', 'employee-business-cards' ), 'text' );
		$this->add_field( 'default_website_url', esc_html__( 'Default website URL', 'employee-business-cards' ), 'url' );
		$this->add_field( 'enable_qr_code', esc_html__( 'Enable QR code', 'employee-business-cards' ), 'checkbox' );
		$this->add_field( 'qr_provider_type', esc_html__( 'QR provider type', 'employee-business-cards' ), 'provider_type' );
		$this->add_field( 'qr_provider_template', esc_html__( 'QR code provider URL template', 'employee-business-cards' ), 'text' );
		$this->add_field( 'primary_color', esc_html__( 'Primary color', 'employee-business-cards' ), 'color' );
		$this->add_field( 'button_style', esc_html__( 'Button style', 'employee-business-cards' ), 'select' );
		$this->add_field( 'design_template', esc_html__( 'Design Template', 'employee-business-cards' ), 'template_select' );
		$this->add_field( 'hide_theme_chrome', esc_html__( 'Hide theme header & footer on card pages', 'employee-business-cards' ), 'checkbox' );
	}

	/**
	 * Add setting field.
	 */
	private function add_field( string $field_key, string $label, string $type ): void {
		add_settings_field(
			$field_key,
			$label,
			array( $this, 'render_field' ),
			'ebc-settings',
			'ebc_main_section',
			array(
				'field_key' => $field_key,
				'type'      => $type,
			)
		);
	}

	/**
	 * Render one setting field.
	 *
	 * @param array<string,string> $args Args.
	 * @return void
	 */
	public function render_field( array $args ): void {
		$settings = ebc_get_settings();
		$key      = $args['field_key'];
		$type     = $args['type'];
		$value    = $settings[ $key ] ?? '';

		switch ( $type ) {
			case 'checkbox':
				echo '<label><input type="checkbox" name="ebc_settings[' . esc_attr( $key ) . ']" value="1" ' . checked( (int) $value, 1, false ) . ' /> ' . esc_html__( 'Yes', 'employee-business-cards' ) . '</label>';
				break;
			case 'provider_type':
				echo '<select name="ebc_settings[qr_provider_type]">';
				echo '<option value="local" ' . selected( (string) $value, 'local', false ) . '>' . esc_html__( 'Local (server-side cached)', 'employee-business-cards' ) . '</option>';
				echo '<option value="external" ' . selected( (string) $value, 'external', false ) . '>' . esc_html__( 'External provider URL', 'employee-business-cards' ) . '</option>';
				echo '</select>';
				break;
			case 'select':
				echo '<select name="ebc_settings[' . esc_attr( $key ) . ']">';
				echo '<option value="rounded" ' . selected( (string) $value, 'rounded', false ) . '>' . esc_html__( 'Rounded', 'employee-business-cards' ) . '</option>';
				echo '<option value="square" ' . selected( (string) $value, 'square', false ) . '>' . esc_html__( 'Square', 'employee-business-cards' ) . '</option>';
				echo '</select>';
				break;
			case 'template_select':
				echo '<select name="ebc_settings[' . esc_attr( $key ) . ']">';
				echo '<option value="v1" ' . selected( (string) $value, 'v1', false ) . '>' . esc_html__( 'Version 1 (Orange Header Accent)', 'employee-business-cards' ) . '</option>';
				echo '<option value="v2" ' . selected( (string) $value, 'v2', false ) . '>' . esc_html__( 'Version 2 (Navy Blue Corporate)', 'employee-business-cards' ) . '</option>';
				echo '</select>';
				break;
			default:
				$disabled = '';
				if ( 'qr_provider_template' === $key && 'external' !== ( $settings['qr_provider_type'] ?? 'local' ) ) {
					$disabled = ' disabled';
				}
				echo '<input type="' . esc_attr( $type ) . '" class="regular-text" name="ebc_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $value ) . '"' . $disabled . ' />';
				if ( 'qr_provider_template' === $key ) {
					echo '<p class="description">' . esc_html__( 'Used only when provider type is External. Must include {url}.', 'employee-business-cards' ) . '</p>';
				}
		}
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array<string,mixed> $input Input.
	 * @return array<string,mixed>
	 */
	public function sanitize_settings( array $input ): array {
		$defaults  = ebc_get_default_settings();
		$sanitized = array();

		$sanitized['default_company_name'] = isset( $input['default_company_name'] ) ? sanitize_text_field( (string) $input['default_company_name'] ) : $defaults['default_company_name'];
		$sanitized['default_website_url']  = isset( $input['default_website_url'] ) ? esc_url_raw( (string) $input['default_website_url'] ) : $defaults['default_website_url'];
		$sanitized['enable_qr_code']       = isset( $input['enable_qr_code'] ) ? 1 : 0;
		$sanitized['hide_theme_chrome']    = isset( $input['hide_theme_chrome'] ) ? 1 : 0;
		$sanitized['qr_provider_type']     = isset( $input['qr_provider_type'] ) && in_array( $input['qr_provider_type'], array( 'local', 'external' ), true ) ? $input['qr_provider_type'] : $defaults['qr_provider_type'];
		$sanitized['qr_provider_template'] = $defaults['qr_provider_template'];

		if ( isset( $input['qr_provider_template'] ) ) {
			$raw_template = sanitize_text_field( (string) $input['qr_provider_template'] );
			if ( false !== strpos( $raw_template, '{url}' ) ) {
				$test_url = str_replace( '{url}', 'https://example.test', $raw_template );
				$clean    = esc_url_raw( $test_url );
				$scheme   = wp_parse_url( $clean, PHP_URL_SCHEME );
				if ( $clean && in_array( $scheme, array( 'http', 'https' ), true ) ) {
					$sanitized['qr_provider_template'] = sanitize_text_field( str_replace( 'https://example.test', '{url}', $clean ) );
				}
			}
		}

		$sanitized['primary_color'] = isset( $input['primary_color'] ) ? sanitize_hex_color( (string) $input['primary_color'] ) : $defaults['primary_color'];
		$sanitized['button_style']  = isset( $input['button_style'] ) && in_array( $input['button_style'], array( 'rounded', 'square' ), true ) ? $input['button_style'] : $defaults['button_style'];
		$sanitized['design_template'] = isset( $input['design_template'] ) && in_array( $input['design_template'], array( 'v1', 'v2' ), true ) ? $input['design_template'] : $defaults['design_template'];

		if ( empty( $sanitized['primary_color'] ) ) {
			$sanitized['primary_color'] = $defaults['primary_color'];
		}

		return $sanitized;
	}

	/**
	 * Output settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Employee Business Cards Settings', 'employee-business-cards' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ebc_settings_group' );
				do_settings_sections( 'ebc-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}

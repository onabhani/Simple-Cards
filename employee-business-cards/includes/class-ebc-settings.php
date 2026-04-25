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
			esc_html__( 'Employee Card Settings', EBC_TEXT_DOMAIN ),
			esc_html__( 'Settings', EBC_TEXT_DOMAIN ),
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
			esc_html__( 'General Settings', EBC_TEXT_DOMAIN ),
			'__return_false',
			'ebc-settings'
		);

		$this->add_field( 'default_company_name', esc_html__( 'Default company name', EBC_TEXT_DOMAIN ), 'text' );
		$this->add_field( 'default_website_url', esc_html__( 'Default website URL', EBC_TEXT_DOMAIN ), 'url' );
		$this->add_field( 'enable_qr_code', esc_html__( 'Enable QR code', EBC_TEXT_DOMAIN ), 'checkbox' );
		$this->add_field( 'qr_provider_template', esc_html__( 'QR code provider URL template', EBC_TEXT_DOMAIN ), 'text' );
		$this->add_field( 'primary_color', esc_html__( 'Primary color', EBC_TEXT_DOMAIN ), 'color' );
		$this->add_field( 'button_style', esc_html__( 'Button style', EBC_TEXT_DOMAIN ), 'select' );
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
				echo '<label><input type="checkbox" name="ebc_settings[' . esc_attr( $key ) . ']" value="1" ' . checked( (int) $value, 1, false ) . ' /> ' . esc_html__( 'Yes', EBC_TEXT_DOMAIN ) . '</label>';
				break;
			case 'select':
				echo '<select name="ebc_settings[' . esc_attr( $key ) . ']">';
				echo '<option value="rounded" ' . selected( (string) $value, 'rounded', false ) . '>' . esc_html__( 'Rounded', EBC_TEXT_DOMAIN ) . '</option>';
				echo '<option value="square" ' . selected( (string) $value, 'square', false ) . '>' . esc_html__( 'Square', EBC_TEXT_DOMAIN ) . '</option>';
				echo '</select>';
				break;
			default:
				echo '<input type="' . esc_attr( $type ) . '" class="regular-text" name="ebc_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $value ) . '" />';
				if ( 'qr_provider_template' === $key ) {
					echo '<p class="description">' . esc_html__( 'Use {url} placeholder for the public card URL.', EBC_TEXT_DOMAIN ) . '</p>';
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
		$sanitized['qr_provider_template'] = isset( $input['qr_provider_template'] ) ? sanitize_text_field( (string) $input['qr_provider_template'] ) : $defaults['qr_provider_template'];
		$sanitized['primary_color']        = isset( $input['primary_color'] ) ? sanitize_hex_color( (string) $input['primary_color'] ) : $defaults['primary_color'];
		$sanitized['button_style']         = isset( $input['button_style'] ) && in_array( $input['button_style'], array( 'rounded', 'square' ), true ) ? $input['button_style'] : $defaults['button_style'];

		if ( empty( $sanitized['primary_color'] ) ) {
			$sanitized['primary_color'] = $defaults['primary_color'];
		}

		if ( false === strpos( $sanitized['qr_provider_template'], '{url}' ) ) {
			$sanitized['qr_provider_template'] = $defaults['qr_provider_template'];
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
			<h1><?php echo esc_html__( 'Employee Business Cards Settings', EBC_TEXT_DOMAIN ); ?></h1>
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

<?php
/**
 * Meta boxes and save logic.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EBC_Meta_Boxes {

	/**
	 * Instance.
	 *
	 * @var EBC_Meta_Boxes|null
	 */
	private static ?EBC_Meta_Boxes $instance = null;

	/**
	 * Get instance.
	 *
	 * @return EBC_Meta_Boxes
	 */
	public static function instance(): EBC_Meta_Boxes {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_employee_card', array( $this, 'save_details' ) );
	}

	/**
	 * Register post type meta boxes.
	 *
	 * @return void
	 */
	public function register_meta_boxes(): void {
		add_meta_box(
			'ebc_business_card_details',
			esc_html__( 'Business Card Details', 'employee-business-cards' ),
			array( $this, 'render_details_meta_box' ),
			'employee_card',
			'normal',
			'high'
		);

		add_meta_box(
			'ebc_public_card_url',
			esc_html__( 'Public Card URL', 'employee-business-cards' ),
			array( $this, 'render_public_url_meta_box' ),
			'employee_card',
			'side',
			'default'
		);

		add_meta_box(
			'ebc_shortcode_usage',
			esc_html__( 'Shortcode Usage', 'employee-business-cards' ),
			array( $this, 'render_shortcode_meta_box' ),
			'employee_card',
			'side',
			'default'
		);
	}

	/**
	 * Render main details box.
	 *
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public function render_details_meta_box( WP_Post $post ): void {
		$fields   = ebc_get_meta_fields();
		$settings = ebc_get_settings();

		wp_nonce_field( 'ebc_save_meta', 'ebc_meta_nonce' );

		$values = array();
		foreach ( $fields as $name => $key ) {
			$values[ $name ] = get_post_meta( $post->ID, $key, true );
		}

		if ( empty( $values['company_name'] ) && ! empty( $settings['default_company_name'] ) ) {
			$values['company_name'] = (string) $settings['default_company_name'];
		}

		if ( empty( $values['website'] ) && ! empty( $settings['default_website_url'] ) ) {
			$values['website'] = (string) $settings['default_website_url'];
		}
		?>
		<div class="ebc-meta-grid">
			<?php $this->text_field( 'full_name', esc_html__( 'Full Name', 'employee-business-cards' ), (string) $values['full_name'], true ); ?>
			<?php $this->text_field( 'job_title', esc_html__( 'Job Title', 'employee-business-cards' ), (string) $values['job_title'], true ); ?>
			<?php $this->text_field( 'department', esc_html__( 'Department', 'employee-business-cards' ), (string) $values['department'], false ); ?>
			<?php $this->text_field( 'company_name', esc_html__( 'Company Name', 'employee-business-cards' ), (string) $values['company_name'], false ); ?>
			<?php $this->text_field( 'phone', esc_html__( 'Phone Number', 'employee-business-cards' ), (string) $values['phone'], false ); ?>
			<?php $this->text_field( 'whatsapp', esc_html__( 'WhatsApp Number', 'employee-business-cards' ), (string) $values['whatsapp'], false ); ?>
			<?php $this->email_field( 'email', esc_html__( 'Email Address', 'employee-business-cards' ), (string) $values['email'] ); ?>
			<?php $this->url_field( 'website', esc_html__( 'Website URL', 'employee-business-cards' ), (string) $values['website'] ); ?>
			<?php $this->url_field( 'linkedin', esc_html__( 'LinkedIn URL', 'employee-business-cards' ), (string) $values['linkedin'] ); ?>
			<?php $this->url_field( 'twitter', esc_html__( 'X/Twitter URL', 'employee-business-cards' ), (string) $values['twitter'] ); ?>
			<?php $this->url_field( 'instagram', esc_html__( 'Instagram URL', 'employee-business-cards' ), (string) $values['instagram'] ); ?>
			<?php $this->text_field( 'location', esc_html__( 'Location / Branch', 'employee-business-cards' ), (string) $values['location'], false ); ?>
			<?php $this->text_field( 'custom_slug', esc_html__( 'Optional Custom Slug', 'employee-business-cards' ), (string) $values['custom_slug'], false ); ?>
		</div>
		<div class="ebc-field">
			<label for="ebc_short_bio"><strong><?php echo esc_html__( 'Short Bio', 'employee-business-cards' ); ?></strong></label>
			<textarea id="ebc_short_bio" name="ebc_fields[short_bio]" rows="4" class="widefat"><?php echo esc_textarea( (string) $values['short_bio'] ); ?></textarea>
		</div>
		<div class="ebc-field">
			<label><strong><?php echo esc_html__( 'Profile Photo', 'employee-business-cards' ); ?></strong></label>
			<?php
			$photo_id  = (int) $values['profile_photo'];
			$photo_url = $photo_id ? wp_get_attachment_image_url( $photo_id, 'medium' ) : '';
			?>
			<input type="hidden" id="ebc_profile_photo" name="ebc_fields[profile_photo]" value="<?php echo esc_attr( (string) $photo_id ); ?>" />
			<div class="ebc-media-preview" id="ebc-media-preview">
				<?php if ( $photo_url ) : ?>
					<img src="<?php echo esc_url( $photo_url ); ?>" alt="" />
				<?php endif; ?>
			</div>
			<p>
				<button type="button" class="button" id="ebc-upload-photo"><?php echo esc_html__( 'Select Photo', 'employee-business-cards' ); ?></button>
				<button type="button" class="button" id="ebc-remove-photo"><?php echo esc_html__( 'Remove Photo', 'employee-business-cards' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Render side public URL box.
	 *
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public function render_public_url_meta_box( WP_Post $post ): void {
		$url = get_permalink( $post );
		if ( $url ) {
			echo '<p><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $url ) . '</a></p>';
		} else {
			echo '<p>' . esc_html__( 'Publish this card to generate a public URL.', 'employee-business-cards' ) . '</p>';
		}
	}

	/**
	 * Render shortcode side box.
	 *
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public function render_shortcode_meta_box( WP_Post $post ): void {
		echo '<p><code>[employee_business_card id="' . esc_html( (string) $post->ID ) . '"]</code></p>';
		echo '<p><code>[employee_business_cards]</code></p>';
	}

	/**
	 * Save post metadata.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_details( int $post_id ): void {
		if ( ! isset( $_POST['ebc_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ebc_meta_nonce'] ) ), 'ebc_save_meta' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['ebc_fields'] ) || ! is_array( $_POST['ebc_fields'] ) ) {
			return;
		}

		$raw_fields = wp_unslash( $_POST['ebc_fields'] );
		$fields     = ebc_get_meta_fields();

		$sanitizers = array(
			'full_name'     => 'sanitize_text_field',
			'job_title'     => 'sanitize_text_field',
			'department'    => 'sanitize_text_field',
			'company_name'  => 'sanitize_text_field',
			'profile_photo' => static function ( $value ) {
				return absint( $value );
			},
			'phone'         => 'sanitize_text_field',
			'whatsapp'      => 'sanitize_text_field',
			'email'         => 'sanitize_email',
			'website'       => 'esc_url_raw',
			'linkedin'      => 'esc_url_raw',
			'twitter'       => 'esc_url_raw',
			'instagram'     => 'esc_url_raw',
			'location'      => 'sanitize_text_field',
			'short_bio'     => 'wp_kses_post',
			'custom_slug'   => 'sanitize_title',
		);

		foreach ( $fields as $name => $meta_key ) {
			$value = $raw_fields[ $name ] ?? '';
			if ( isset( $sanitizers[ $name ] ) ) {
				$value = call_user_func( $sanitizers[ $name ], $value );
			}

			$is_empty = '' === $value || null === $value;
			if ( 'profile_photo' === $name && 0 === (int) $value ) {
				$is_empty = true;
			}

			if ( $is_empty ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}

		$full_name   = isset( $raw_fields['full_name'] ) ? sanitize_text_field( $raw_fields['full_name'] ) : '';
		$custom_slug = isset( $raw_fields['custom_slug'] ) ? sanitize_title( $raw_fields['custom_slug'] ) : '';
		$update_post = array(
			'ID' => $post_id,
		);

		if ( '' !== $full_name ) {
			$update_post['post_title'] = $full_name;
		}

		if ( '' !== $custom_slug ) {
			$update_post['post_name'] = $custom_slug;
		}

		if ( count( $update_post ) > 1 ) {
			remove_action( 'save_post_employee_card', array( $this, 'save_details' ) );
			wp_update_post( $update_post );
			add_action( 'save_post_employee_card', array( $this, 'save_details' ) );
		}
	}

	/**
	 * Render generic text field.
	 */
	private function text_field( string $name, string $label, string $value, bool $required = false ): void {
		?>
		<div class="ebc-field">
			<label for="ebc_<?php echo esc_attr( $name ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
			<input type="text" id="ebc_<?php echo esc_attr( $name ); ?>" name="ebc_fields[<?php echo esc_attr( $name ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="widefat" <?php echo $required ? 'required' : ''; ?> />
		</div>
		<?php
	}

	/**
	 * Render URL field.
	 */
	private function url_field( string $name, string $label, string $value ): void {
		?>
		<div class="ebc-field">
			<label for="ebc_<?php echo esc_attr( $name ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
			<input type="url" id="ebc_<?php echo esc_attr( $name ); ?>" name="ebc_fields[<?php echo esc_attr( $name ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="widefat" />
		</div>
		<?php
	}

	/**
	 * Render email field.
	 */
	private function email_field( string $name, string $label, string $value ): void {
		?>
		<div class="ebc-field">
			<label for="ebc_<?php echo esc_attr( $name ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
			<input type="email" id="ebc_<?php echo esc_attr( $name ); ?>" name="ebc_fields[<?php echo esc_attr( $name ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="widefat" />
		</div>
		<?php
	}
}

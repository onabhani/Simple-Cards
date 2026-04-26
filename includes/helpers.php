<?php
/**
 * Helper functions.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta keys.
 *
 * @return array<string,string>
 */
function ebc_get_meta_fields(): array {
	return array(
		'full_name'     => '_ebc_full_name',
		'job_title'     => '_ebc_job_title',
		'department'    => '_ebc_department',
		'company_name'  => '_ebc_company_name',
		'profile_photo' => '_ebc_profile_photo_id',
		'phone'         => '_ebc_phone',
		'whatsapp'      => '_ebc_whatsapp',
		'email'         => '_ebc_email',
		'website'       => '_ebc_website',
		'linkedin'      => '_ebc_linkedin',
		'twitter'       => '_ebc_twitter',
		'instagram'     => '_ebc_instagram',
		'location'      => '_ebc_location',
		'short_bio'     => '_ebc_short_bio',
		'custom_slug'   => '_ebc_custom_slug',
	);
}

/**
 * Plugin settings defaults.
 *
 * @return array<string,mixed>
 */
function ebc_get_default_settings(): array {
	return array(
		'default_company_name' => '',
		'default_website_url'  => '',
		'enable_qr_code'       => 1,
		'qr_provider_type'     => 'local',
		'qr_provider_template' => 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data={url}',
		'primary_color'        => '#1d4ed8',
		'button_style'         => 'rounded',
		'hide_theme_chrome'    => 1,
	);
}

/**
 * Get merged plugin settings.
 *
 * @return array<string,mixed>
 */
function ebc_get_settings(): array {
	$defaults = ebc_get_default_settings();
	$stored   = get_option( 'ebc_settings', array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	return wp_parse_args( $stored, $defaults );
}

/**
 * Get one card meta value.
 *
 * @param int    $post_id Post ID.
 * @param string $field   Field key.
 * @return mixed
 */
function ebc_get_field_value( int $post_id, string $field ) {
	$fields = ebc_get_meta_fields();
	$key    = $fields[ $field ] ?? '';

	if ( ! $key ) {
		return '';
	}

	return get_post_meta( $post_id, $key, true );
}

/**
 * Return best card full name.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ebc_get_card_name( int $post_id ): string {
	$name = (string) ebc_get_field_value( $post_id, 'full_name' );

	if ( '' === $name ) {
		$name = get_the_title( $post_id );
	}

	return $name;
}

/**
 * Build card URL.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ebc_get_card_url( int $post_id ): string {
	return get_permalink( $post_id ) ?: '';
}

/**
 * Build vCard URL.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ebc_get_vcard_url( int $post_id ): string {
	return add_query_arg(
		array(
			'employee_card_vcf' => $post_id,
		),
		home_url( '/' )
	);
}

/**
 * Get card photo URL.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Image size.
 * @return string
 */
function ebc_get_card_photo_url( int $post_id, string $size = 'medium' ): string {
	$attachment_id = (int) ebc_get_field_value( $post_id, 'profile_photo' );

	if ( $attachment_id > 0 ) {
		$url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( $url ) {
			return $url;
		}
	}

	$thumb_url = get_the_post_thumbnail_url( $post_id, $size );
	return $thumb_url ? $thumb_url : '';
}

/**
 * Build QR code URL.
 *
 * @param string $card_url Card URL.
 * @return string
 */
function ebc_get_qr_url( string $card_url ): string {
	$settings = ebc_get_settings();

	if ( empty( $settings['enable_qr_code'] ) ) {
		return '';
	}

	$provider_type = $settings['qr_provider_type'] ?? 'local';
	if ( 'external' === $provider_type ) {
		$template = isset( $settings['qr_provider_template'] ) ? (string) $settings['qr_provider_template'] : '';
		if ( '' === $template || false === strpos( $template, '{url}' ) ) {
			return '';
		}

		return str_replace( '{url}', rawurlencode( $card_url ), $template );
	}

	return ebc_get_local_qr_url( $card_url, $settings );
}

/**
 * Return locally cached QR image URL.
 *
 * @param string              $card_url Card URL.
 * @param array<string,mixed> $settings Plugin settings.
 * @return string
 */
function ebc_get_local_qr_url( string $card_url, array $settings ): string {
	$uploads = wp_upload_dir();
	if ( empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
		return '';
	}

	$dir_path = trailingslashit( $uploads['basedir'] ) . 'ebc-qr';
	$dir_url  = trailingslashit( $uploads['baseurl'] ) . 'ebc-qr';
	if ( ! wp_mkdir_p( $dir_path ) ) {
		return '';
	}

	$file_name = 'qr-' . md5( $card_url ) . '.png';
	$file_path = trailingslashit( $dir_path ) . $file_name;
	$file_url  = trailingslashit( $dir_url ) . $file_name;

	if ( file_exists( $file_path ) ) {
		return $file_url;
	}

	$template = isset( $settings['qr_provider_template'] ) ? (string) $settings['qr_provider_template'] : '';
	if ( '' === $template || false === strpos( $template, '{url}' ) ) {
		return '';
	}

	$lock_key       = 'ebc_qr_fetch_' . md5( $card_url );
	$use_obj_cache  = function_exists( 'wp_using_ext_object_cache' ) && wp_using_ext_object_cache();
	$lock_acquired  = false;

	if ( $use_obj_cache ) {
		$lock_acquired = (bool) wp_cache_add( $lock_key, 1, 'ebc', 30 );
	} else {
		if ( false === get_transient( $lock_key ) ) {
			set_transient( $lock_key, 1, 30 );
			$lock_acquired = true;
		}
	}

	if ( ! $lock_acquired ) {
		return '';
	}

	$release_lock = static function () use ( $lock_key, $use_obj_cache ): void {
		if ( $use_obj_cache ) {
			wp_cache_delete( $lock_key, 'ebc' );
		} else {
			delete_transient( $lock_key );
		}
	};

	$remote_qr = str_replace( '{url}', rawurlencode( $card_url ), $template );
	$response  = wp_remote_get( $remote_qr, array( 'timeout' => 5 ) );

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		$release_lock();
		return '';
	}

	$content_type = (string) wp_remote_retrieve_header( $response, 'content-type' );
	if ( '' !== $content_type && 0 !== stripos( $content_type, 'image/' ) ) {
		$release_lock();
		return '';
	}

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		$release_lock();
		return '';
	}

	if ( function_exists( 'getimagesizefromstring' ) ) {
		$info = @getimagesizefromstring( $body );
		if ( false === $info ) {
			$release_lock();
			return '';
		}
	}

	$tmp_path = $file_path . '.' . wp_generate_password( 8, false ) . '.tmp';
	$written  = file_put_contents( $tmp_path, $body );
	if ( false === $written ) {
		$release_lock();
		return '';
	}

	if ( ! @rename( $tmp_path, $file_path ) ) {
		@unlink( $tmp_path );
		$release_lock();
		return '';
	}

	$release_lock();
	return $file_url;
}

/**
 * Return icon markup for a card icon.
 *
 * For built-in functional icons (phone/whatsapp/email/website/qr/share/download/close),
 * returns an inline <svg> using currentColor. For brand icons (linkedin/twitter/instagram),
 * returns an <img> tag pointing to a bundled SVG file (preserves brand color/gradient).
 *
 * If `aria-label` is set in $attrs (even empty string), that exact value is used —
 * pass `'aria-label' => ''` to mark the icon decorative. If the key is omitted,
 * a sensible default label is generated.
 *
 * @param string $name  Icon slug.
 * @param array  $attrs Optional attributes (class, aria-label).
 * @return string
 */
function ebc_get_icon_svg( string $name, array $attrs = array() ): string {
	static $brand = array(
		'linkedin'  => 'linkedin.svg',
		'twitter'   => 'twitter-x.svg',
		'instagram' => 'instagram.svg',
	);
	static $paths = null;

	$class           = isset( $attrs['class'] ) ? (string) $attrs['class'] : 'ebc-icon';
	$has_aria_label  = array_key_exists( 'aria-label', $attrs );
	$aria_label      = $has_aria_label ? (string) $attrs['aria-label'] : '';

	if ( isset( $brand[ $name ] ) ) {
		$url = EBC_URL . 'assets/icons/' . $brand[ $name ];
		if ( $has_aria_label ) {
			$alt = $aria_label;
		} else {
			$labels = array(
				'linkedin'  => __( 'LinkedIn', 'employee-business-cards' ),
				'twitter'   => __( 'X / Twitter', 'employee-business-cards' ),
				'instagram' => __( 'Instagram', 'employee-business-cards' ),
			);
			$alt = $labels[ $name ];
		}
		return sprintf(
			'<img class="%s" src="%s" alt="%s" loading="lazy" decoding="async" width="24" height="24" />',
			esc_attr( $class ),
			esc_url( $url ),
			esc_attr( $alt )
		);
	}

	if ( null === $paths ) {
		$paths = array(
		'phone'     => '<path d="M3.6 5.5C3.6 4.12 4.72 3 6.1 3h2.06c.55 0 1.04.36 1.2.88l.95 3.04a1.25 1.25 0 0 1-.31 1.27l-1.32 1.32a13 13 0 0 0 6.6 6.6l1.33-1.32a1.25 1.25 0 0 1 1.27-.3l3.04.95c.52.16.88.65.88 1.2v2.05c0 1.39-1.12 2.51-2.5 2.51A16.4 16.4 0 0 1 3.6 5.5Z" fill="currentColor"/>',
		'whatsapp'  => '<path d="M19.05 4.91A10 10 0 0 0 4.4 18.31L3 22l3.78-1.39A10 10 0 1 0 19.05 4.9ZM12 20.13a8.13 8.13 0 0 1-4.13-1.13l-.3-.18-2.42.89.89-2.36-.2-.32A8.16 8.16 0 1 1 12 20.13Zm4.46-6.1c-.24-.12-1.44-.71-1.66-.79-.22-.08-.39-.12-.55.12-.16.24-.63.79-.78.96-.14.16-.29.18-.53.06a6.66 6.66 0 0 1-1.96-1.21 7.34 7.34 0 0 1-1.36-1.7c-.14-.24 0-.37.1-.49.1-.1.24-.27.36-.41.12-.14.16-.24.24-.4.08-.16.04-.31-.02-.43-.06-.12-.55-1.31-.75-1.79-.2-.47-.4-.41-.55-.41h-.47c-.16 0-.42.06-.64.31-.22.24-.83.81-.83 1.97 0 1.16.84 2.28.96 2.43.12.16 1.66 2.55 4.02 3.57.56.24 1 .39 1.34.5.56.18 1.07.16 1.47.1.45-.07 1.44-.59 1.65-1.16.2-.57.2-1.05.14-1.16-.06-.11-.22-.17-.46-.29Z" fill="currentColor"/>',
		'email'     => '<path d="M3.5 5.5h17a1.5 1.5 0 0 1 1.5 1.5v10a1.5 1.5 0 0 1-1.5 1.5h-17A1.5 1.5 0 0 1 2 17V7a1.5 1.5 0 0 1 1.5-1.5Zm.62 2 7.88 5.7 7.88-5.7H4.12Zm15.88 1.5-7.5 5.42a1 1 0 0 1-1.18 0L4 9V17h16V9Z" fill="currentColor"/>',
		'website'   => '<path d="M12 2a10 10 0 1 0 .01 20.01A10 10 0 0 0 12 2Zm6.93 6h-2.95a15.65 15.65 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.93 8ZM12 4.04c.94 1.36 1.68 2.86 2.18 4.46H9.82A14.7 14.7 0 0 1 12 4.04ZM4.26 14a7.93 7.93 0 0 1 0-4h3.38a16.5 16.5 0 0 0 0 4H4.26Zm.81 2h2.95c.32 1.25.78 2.45 1.38 3.56A8.03 8.03 0 0 1 5.07 16Zm2.95-8H5.07a8.03 8.03 0 0 1 4.33-3.56A15.66 15.66 0 0 0 8.02 8ZM12 19.96A14.7 14.7 0 0 1 9.82 15.5h4.36A14.7 14.7 0 0 1 12 19.96Zm2.6-6.46H9.4a14.5 14.5 0 0 1 0-3h5.2a14.5 14.5 0 0 1 0 3Zm.01 6.06A15.66 15.66 0 0 0 16 16h2.94a8.04 8.04 0 0 1-4.33 3.56ZM16.36 14a16.5 16.5 0 0 0 0-4h3.38a7.94 7.94 0 0 1 0 4h-3.38Z" fill="currentColor"/>',
		'qr'        => '<path d="M3 3h8v8H3V3Zm2 2v4h4V5H5Zm8-2h8v8h-8V3Zm2 2v4h4V5h-4ZM3 13h8v8H3v-8Zm2 2v4h4v-4H5Zm8 0h2v2h-2v-2Zm4 0h2v2h-2v-2Zm-4 4h2v2h-2v-2Zm4 0h2v2h-2v-2Zm-2-2h2v2h-2v-2Zm4-4h2v2h-2v-2Z" fill="currentColor"/>',
		'share'     => '<path d="M14 9V5l7 7-7 7v-4.1c-5 0-8.5 1.6-11 5.1 1-5 4-10 11-11Z" fill="currentColor"/>',
		'download'  => '<path d="M12 3a1 1 0 0 1 1 1v9.59l3.3-3.3a1 1 0 1 1 1.4 1.42l-5 5a1 1 0 0 1-1.4 0l-5-5a1 1 0 1 1 1.4-1.42L11 13.6V4a1 1 0 0 1 1-1ZM4 19a1 1 0 0 1 1-1h14a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1Z" fill="currentColor"/>',
		'close'     => '<path d="M6.4 5 5 6.4 10.6 12 5 17.6 6.4 19 12 13.4 17.6 19 19 17.6 13.4 12 19 6.4 17.6 5 12 10.6 6.4 5Z" fill="currentColor"/>',
		);
	}

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	if ( '' === $aria_label ) {
		$a11y = ' aria-hidden="true" focusable="false"';
	} else {
		$a11y = ' role="img" aria-label="' . esc_attr( $aria_label ) . '"';
	}

	return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"' . $a11y . '>' . $paths[ $name ] . '</svg>';
}

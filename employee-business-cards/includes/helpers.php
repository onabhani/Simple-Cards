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
		'full_name'      => '_ebc_full_name',
		'job_title'      => '_ebc_job_title',
		'department'     => '_ebc_department',
		'company_name'   => '_ebc_company_name',
		'profile_photo'  => '_ebc_profile_photo_id',
		'phone'          => '_ebc_phone',
		'whatsapp'       => '_ebc_whatsapp',
		'email'          => '_ebc_email',
		'website'        => '_ebc_website',
		'linkedin'       => '_ebc_linkedin',
		'twitter'        => '_ebc_twitter',
		'instagram'      => '_ebc_instagram',
		'location'       => '_ebc_location',
		'short_bio'      => '_ebc_short_bio',
		'custom_slug'    => '_ebc_custom_slug',
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
		'qr_provider_template' => 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={url}',
		'primary_color'        => '#1d4ed8',
		'button_style'         => 'rounded',
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
 * @param int $post_id Post ID.
 * @param string $size Image size.
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
 * Build QR code URL from template.
 *
 * @param string $card_url Card URL.
 * @return string
 */
function ebc_get_qr_url( string $card_url ): string {
	$settings = ebc_get_settings();

	if ( empty( $settings['enable_qr_code'] ) ) {
		return '';
	}

	$template = isset( $settings['qr_provider_template'] ) ? (string) $settings['qr_provider_template'] : '';
	if ( '' === $template || false === strpos( $template, '{url}' ) ) {
		return '';
	}

	return str_replace( '{url}', rawurlencode( $card_url ), $template );
}

<?php
/**
 * vCard endpoint.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EBC_VCard {

	/** @var EBC_VCard|null */
	private static ?EBC_VCard $instance = null;

	/**
	 * @return EBC_VCard
	 */
	public static function instance(): EBC_VCard {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'maybe_download_vcard' ) );
	}

	/**
	 * Output VCF when requested.
	 *
	 * @return void
	 */
	public function maybe_download_vcard(): void {
		if ( ! isset( $_GET['employee_card_vcf'] ) ) {
			return;
		}

		$post_id = absint( wp_unslash( $_GET['employee_card_vcf'] ) );
		if ( $post_id <= 0 ) {
			status_header( 404 );
			exit;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post || 'employee_card' !== $post->post_type || 'publish' !== $post->post_status ) {
			status_header( 404 );
			exit;
		}

		$name      = ebc_get_card_name( $post_id );
		$job_title = (string) ebc_get_field_value( $post_id, 'job_title' );
		$company   = (string) ebc_get_field_value( $post_id, 'company_name' );
		$phone     = (string) ebc_get_field_value( $post_id, 'phone' );
		$whatsapp  = (string) ebc_get_field_value( $post_id, 'whatsapp' );
		$email     = (string) ebc_get_field_value( $post_id, 'email' );
		$website   = (string) ebc_get_field_value( $post_id, 'website' );
		$linkedin  = (string) ebc_get_field_value( $post_id, 'linkedin' );
		$location  = (string) ebc_get_field_value( $post_id, 'location' );

		$lines = array(
			'BEGIN:VCARD',
			'VERSION:3.0',
		);

		if ( $name ) {
			$lines[] = 'FN:' . $this->escape_vcard( $name );
		}
		if ( $job_title ) {
			$lines[] = 'TITLE:' . $this->escape_vcard( $job_title );
		}
		if ( $company ) {
			$lines[] = 'ORG:' . $this->escape_vcard( $company );
		}
		if ( $phone ) {
			$lines[] = 'TEL;TYPE=WORK,VOICE:' . $this->escape_vcard( $phone );
		}
		if ( $whatsapp ) {
			$lines[] = 'TEL;TYPE=CELL,WHATSAPP:' . $this->escape_vcard( $whatsapp );
		}
		if ( $email ) {
			$lines[] = 'EMAIL;TYPE=INTERNET:' . $this->escape_vcard( $email );
		}
		if ( $website ) {
			$lines[] = 'URL;TYPE=WORK:' . $this->escape_vcard( $website );
		}
		if ( $linkedin ) {
			$lines[] = 'X-SOCIALPROFILE;TYPE=linkedin:' . $this->escape_vcard( $linkedin );
		}
		if ( $location ) {
			$lines[] = 'ADR;TYPE=WORK:;;' . $this->escape_vcard( $location ) . ';;;;';
		}

		$lines[] = 'END:VCARD';
		$content = implode( "\r\n", $lines ) . "\r\n";

		nocache_headers();
		header( 'Content-Type: text/vcard; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="employee-card-' . $post_id . '.vcf"' );
		header( 'Content-Length: ' . strlen( $content ) );
		echo $content;
		exit;
	}

	/**
	 * Escape vcard value.
	 */
	private function escape_vcard( string $value ): string {
		$value = wp_strip_all_tags( $value );
		$value = str_replace( array( "\r", "\n" ), '', $value );
		$value = str_replace( array( ';', ',' ), array( '\\;', '\\,' ), $value );
		return $value;
	}
}

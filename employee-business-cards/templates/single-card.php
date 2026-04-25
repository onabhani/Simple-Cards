<?php
/**
 * Single employee business card template.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$render_full_page = ! isset( $post_id );

if ( $render_full_page ) {
	get_header();
	$post_id = get_the_ID();
}

$post_id = isset( $post_id ) ? absint( $post_id ) : 0;

if ( $post_id <= 0 ) {
	if ( $render_full_page ) {
		get_footer();
	}
	return;
}

$name      = ebc_get_card_name( $post_id );
$job_title = (string) ebc_get_field_value( $post_id, 'job_title' );
$company   = (string) ebc_get_field_value( $post_id, 'company_name' );
$dept      = (string) ebc_get_field_value( $post_id, 'department' );
$phone     = (string) ebc_get_field_value( $post_id, 'phone' );
$whatsapp  = (string) ebc_get_field_value( $post_id, 'whatsapp' );
$email     = (string) ebc_get_field_value( $post_id, 'email' );
$website   = (string) ebc_get_field_value( $post_id, 'website' );
$linkedin  = (string) ebc_get_field_value( $post_id, 'linkedin' );
$twitter   = (string) ebc_get_field_value( $post_id, 'twitter' );
$instagram = (string) ebc_get_field_value( $post_id, 'instagram' );
$location  = (string) ebc_get_field_value( $post_id, 'location' );
$bio       = (string) ebc_get_field_value( $post_id, 'short_bio' );
$photo     = ebc_get_card_photo_url( $post_id, 'medium' );
$card_url  = ebc_get_card_url( $post_id );
$vcard_url = ebc_get_vcard_url( $post_id );
$qr_url    = ebc_get_qr_url( $card_url );
?>
<div class="ebc-card-wrap">
	<div class="ebc-card">
		<?php if ( $photo ) : ?>
			<div class="ebc-avatar"><img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" /></div>
		<?php endif; ?>

		<h1 class="ebc-name"><?php echo esc_html( $name ); ?></h1>
		<?php if ( $job_title ) : ?><p class="ebc-job-title"><?php echo esc_html( $job_title ); ?></p><?php endif; ?>
		<?php if ( $company ) : ?><p class="ebc-company"><?php echo esc_html( $company ); ?></p><?php endif; ?>
		<?php if ( $dept ) : ?><p class="ebc-dept"><?php echo esc_html( $dept ); ?></p><?php endif; ?>

		<div class="ebc-actions">
			<?php if ( $phone ) : ?><a class="ebc-btn" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html__( 'Phone', EBC_TEXT_DOMAIN ); ?></a><?php endif; ?>
			<?php if ( $whatsapp ) : ?><a class="ebc-btn" href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $whatsapp ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'WhatsApp', EBC_TEXT_DOMAIN ); ?></a><?php endif; ?>
			<?php if ( $email ) : ?><a class="ebc-btn" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html__( 'Email', EBC_TEXT_DOMAIN ); ?></a><?php endif; ?>
			<?php if ( $website ) : ?><a class="ebc-btn" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Website', EBC_TEXT_DOMAIN ); ?></a><?php endif; ?>
		</div>

		<?php if ( $linkedin || $twitter || $instagram ) : ?>
			<div class="ebc-social-links">
				<?php if ( $linkedin ) : ?><a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a><?php endif; ?>
				<?php if ( $twitter ) : ?><a href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer">X</a><?php endif; ?>
				<?php if ( $instagram ) : ?><a href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener noreferrer">Instagram</a><?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $location ) : ?><p class="ebc-location"><?php echo esc_html( $location ); ?></p><?php endif; ?>
		<?php if ( $bio ) : ?><div class="ebc-bio"><?php echo wp_kses_post( wpautop( $bio ) ); ?></div><?php endif; ?>

		<div class="ebc-bottom-actions">
			<a class="ebc-btn ebc-btn-primary" href="<?php echo esc_url( $vcard_url ); ?>"><?php echo esc_html__( 'Save Contact', EBC_TEXT_DOMAIN ); ?></a>
			<button class="ebc-btn" type="button" onclick="if(navigator.share){navigator.share({title:document.title,url:'<?php echo esc_js( $card_url ); ?>'});}else{window.prompt('<?php echo esc_js( __( 'Copy this URL:', EBC_TEXT_DOMAIN ) ); ?>','<?php echo esc_js( $card_url ); ?>');}"><?php echo esc_html__( 'Share Card', EBC_TEXT_DOMAIN ); ?></button>
		</div>

		<?php if ( $qr_url ) : ?>
			<div class="ebc-qr">
				<p><?php echo esc_html__( 'Scan to open this card', EBC_TEXT_DOMAIN ); ?></p>
				<img src="<?php echo esc_url( $qr_url ); ?>" alt="<?php echo esc_attr__( 'QR code for employee card', EBC_TEXT_DOMAIN ); ?>" loading="lazy" />
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
if ( $render_full_page ) {
	get_footer();
}

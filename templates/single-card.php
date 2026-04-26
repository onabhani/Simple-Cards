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
		<div class="ebc-card-top">
			<?php if ( $photo ) : ?>
				<div class="ebc-avatar"><img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" /></div>
			<?php endif; ?>
			<h1 class="ebc-name"><?php echo esc_html( $name ); ?></h1>
			<?php if ( $job_title ) : ?><p class="ebc-job-title"><?php echo esc_html( $job_title ); ?></p><?php endif; ?>
			<?php if ( $company ) : ?><p class="ebc-company"><?php echo esc_html( $company ); ?></p><?php endif; ?>
			<?php if ( $dept ) : ?><p class="ebc-dept"><?php echo esc_html( $dept ); ?></p><?php endif; ?>
		</div>

		<?php if ( $phone || $whatsapp || $email || $website ) : ?>
			<div class="ebc-actions">
				<?php if ( $whatsapp ) : ?>
					<a class="ebc-btn" href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $whatsapp ) ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo ebc_get_icon_svg( 'whatsapp' ); ?>
						<span><?php echo esc_html__( 'WhatsApp', 'employee-business-cards' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<a class="ebc-btn" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
						<?php echo ebc_get_icon_svg( 'phone' ); ?>
						<span><?php echo esc_html__( 'Phone', 'employee-business-cards' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $website ) : ?>
					<a class="ebc-btn" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo ebc_get_icon_svg( 'website' ); ?>
						<span><?php echo esc_html__( 'Website', 'employee-business-cards' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<a class="ebc-btn" href="mailto:<?php echo esc_attr( sanitize_email( $email ) ); ?>">
						<?php echo ebc_get_icon_svg( 'email' ); ?>
						<span><?php echo esc_html__( 'Email', 'employee-business-cards' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $location ) : ?>
			<p class="ebc-location"><?php echo esc_html( $location ); ?></p>
		<?php endif; ?>
		<?php if ( $bio ) : ?>
			<div class="ebc-bio"><?php echo wp_kses_post( wpautop( $bio ) ); ?></div>
		<?php endif; ?>

		<div class="ebc-bottom-actions">
			<a class="ebc-btn ebc-btn-primary" href="<?php echo esc_url( $vcard_url ); ?>">
				<?php echo ebc_get_icon_svg( 'download' ); ?>
				<span><?php echo esc_html__( 'Save Contact', 'employee-business-cards' ); ?></span>
			</a>
			<button
				class="ebc-btn ebc-share-btn"
				type="button"
				data-ebc-share-url="<?php echo esc_attr( $card_url ); ?>"
				data-ebc-share-prompt="<?php echo esc_attr__( 'Copy this URL:', 'employee-business-cards' ); ?>"
			>
				<?php echo ebc_get_icon_svg( 'share' ); ?>
				<span><?php echo esc_html__( 'Share Card', 'employee-business-cards' ); ?></span>
			</button>
			<?php if ( $qr_url ) : ?>
				<button
					class="ebc-btn ebc-qr-toggle"
					type="button"
					aria-expanded="false"
					aria-controls="ebc-qr-<?php echo esc_attr( (string) $post_id ); ?>"
				>
					<?php echo ebc_get_icon_svg( 'qr' ); ?>
					<span
						data-show-label="<?php echo esc_attr__( 'Show QR', 'employee-business-cards' ); ?>"
						data-hide-label="<?php echo esc_attr__( 'Hide QR', 'employee-business-cards' ); ?>"
					><?php echo esc_html__( 'Show QR', 'employee-business-cards' ); ?></span>
				</button>
			<?php endif; ?>
		</div>

		<?php if ( $qr_url ) : ?>
			<div class="ebc-qr" id="ebc-qr-<?php echo esc_attr( (string) $post_id ); ?>" hidden>
				<p class="ebc-qr-label"><?php echo esc_html__( 'Scan to open this card', 'employee-business-cards' ); ?></p>
				<img src="<?php echo esc_url( $qr_url ); ?>" alt="<?php echo esc_attr__( 'QR code for employee card', 'employee-business-cards' ); ?>" loading="lazy" />
			</div>
		<?php endif; ?>

		<?php if ( $linkedin || $twitter || $instagram ) : ?>
			<div class="ebc-social-icons">
				<?php if ( $linkedin ) : ?>
					<a class="ebc-social ebc-social-linkedin" href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'LinkedIn', 'employee-business-cards' ); ?>">
						<?php echo ebc_get_icon_svg( 'linkedin', array( 'class' => 'ebc-social-svg', 'aria-label' => '' ) ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $twitter ) : ?>
					<a class="ebc-social ebc-social-twitter" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'X / Twitter', 'employee-business-cards' ); ?>">
						<?php echo ebc_get_icon_svg( 'twitter', array( 'class' => 'ebc-social-svg', 'aria-label' => '' ) ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $instagram ) : ?>
					<a class="ebc-social ebc-social-instagram" href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr__( 'Instagram', 'employee-business-cards' ); ?>">
						<?php echo ebc_get_icon_svg( 'instagram', array( 'class' => 'ebc-social-svg', 'aria-label' => '' ) ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
if ( $render_full_page ) {
	get_footer();
}

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

$settings = ebc_get_settings();
$template = $settings['design_template'] ?? 'v1';

$svg_kses = array(
	'svg'  => array( 'class' => true, 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'fill' => true, 'role' => true, 'aria-label' => true, 'aria-hidden' => true, 'focusable' => true ),
	'path' => array( 'd' => true, 'fill' => true, 'fill-rule' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ),
	'img'  => array( 'src' => true, 'class' => true, 'alt' => true, 'width' => true, 'height' => true, 'loading' => true, 'decoding' => true ),
);

$has_socials = $linkedin || $twitter || $instagram;
?>
<div class="ebc-card-wrap">

<?php if ( 'v2' === $template ) : ?>
	<!-- Version 2: Navy Blue Corporate List -->
	<div class="ebc-card v2">
		<div class="ebc-header">
			<?php if ( $photo ) : ?>
				<div class="ebc-avatar-wrap"><img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" /></div>
			<?php endif; ?>
			<h1 class="ebc-name"><?php echo esc_html( $name ); ?></h1>
			<?php if ( $job_title ) : ?><p class="ebc-job"><?php echo esc_html( $job_title ); ?></p><?php endif; ?>
			<?php if ( $company || $dept ) : ?>
			<div class="ebc-meta">
				<?php if ( $company ) : ?><span><?php echo esc_html( $company ); ?></span><?php endif; ?>
				<?php if ( $company && $dept ) : ?><span class="dot"></span><?php endif; ?>
				<?php if ( $dept ) : ?><span><?php echo esc_html( $dept ); ?></span><?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		
		<div class="ebc-content">
			<div class="ebc-actions">
				<?php if ( $whatsapp ) : ?>
					<a class="ebc-btn-row" href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $whatsapp ) ); ?>" target="_blank" rel="noopener noreferrer">
						<div class="left"><span class="icon whatsapp"><i class="fab fa-whatsapp"></i></span> WhatsApp</div>
						<span class="arrow"><i class="fas fa-chevron-left"></i></span>
					</a>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<a class="ebc-btn-row" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
						<div class="left"><span class="icon"><i class="fas fa-phone"></i></span> <?php echo esc_html__( 'Phone', 'employee-business-cards' ); ?></div>
						<span class="arrow"><i class="fas fa-chevron-left"></i></span>
					</a>
				<?php endif; ?>
				<?php if ( $website || $email ) : ?>
				<div class="ebc-actions-grid">
					<?php if ( $website ) : ?>
						<a class="ebc-btn-grid" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
							<span class="icon"><i class="fas fa-globe"></i></span> <?php echo esc_html__( 'Website', 'employee-business-cards' ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<a class="ebc-btn-grid" href="mailto:<?php echo esc_attr( sanitize_email( $email ) ); ?>">
							<span class="icon"><i class="far fa-envelope"></i></span> <?php echo esc_html__( 'Email', 'employee-business-cards' ); ?>
						</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<?php if ( $location || $bio ) : ?>
			<div class="ebc-location-bio">
				<?php if ( $location ) : ?>
					<div class="ebc-location"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html( $location ); ?></div>
				<?php endif; ?>
				<?php if ( $bio ) : ?>
					<div class="ebc-bio"><?php echo wp_kses_post( wpautop( $bio ) ); ?></div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<a class="ebc-primary-btn" href="<?php echo esc_url( $vcard_url ); ?>">
				<i class="fas fa-download"></i> <?php echo esc_html__( 'Save Contact', 'employee-business-cards' ); ?>
			</a>

			<div class="ebc-secondary-actions">
				<button class="ebc-btn-text ebc-share-btn" type="button" data-ebc-share-url="<?php echo esc_attr( $card_url ); ?>" data-ebc-share-prompt="<?php echo esc_attr__( 'Copy this URL:', 'employee-business-cards' ); ?>">
					<i class="fas fa-share"></i> <?php echo esc_html__( 'Share', 'employee-business-cards' ); ?>
				</button>
				<?php if ( $qr_url ) : ?>
					<span class="separator">|</span>
					<button class="ebc-btn-text ebc-qr-toggle" type="button" aria-expanded="false" aria-controls="ebc-qr-<?php echo esc_attr( (string) $post_id ); ?>">
						<i class="fas fa-qrcode"></i> <span data-show-label="<?php echo esc_attr__( 'QR', 'employee-business-cards' ); ?>" data-hide-label="<?php echo esc_attr__( 'Hide QR', 'employee-business-cards' ); ?>"><?php echo esc_html__( 'QR', 'employee-business-cards' ); ?></span>
					</button>
				<?php endif; ?>
			</div>

			<?php if ( $qr_url ) : ?>
				<div class="ebc-qr-container" id="ebc-qr-<?php echo esc_attr( (string) $post_id ); ?>" hidden>
					<p><?php echo esc_html__( 'Scan to open this card', 'employee-business-cards' ); ?></p>
					<img src="<?php echo esc_url( $qr_url ); ?>" alt="<?php echo esc_attr__( 'QR code for employee card', 'employee-business-cards' ); ?>" loading="lazy" />
				</div>
			<?php endif; ?>

			<?php if ( $has_socials ) : ?>
				<div class="ebc-socials">
					<?php if ( $instagram ) : ?>
						<a class="ebc-social-icon" href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
					<?php endif; ?>
					<?php if ( $twitter ) : ?>
						<a class="ebc-social-icon" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-x-twitter"></i></a>
					<?php endif; ?>
					<?php if ( $linkedin ) : ?>
						<a class="ebc-social-icon" href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

<?php else : ?>
	<!-- Version 1: Orange Header Accent -->
	<div class="ebc-card v1">
		<div class="ebc-header">
			<div class="ebc-avatar-wrap">
				<?php if ( $photo ) : ?>
					<img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
				<?php endif; ?>
			</div>
		</div>
		
		<div class="ebc-content">
			<h1 class="ebc-name"><?php echo esc_html( $name ); ?></h1>
			<?php if ( $job_title ) : ?><p class="ebc-job"><?php echo esc_html( $job_title ); ?></p><?php endif; ?>
			
			<?php if ( $company || $dept ) : ?>
			<div class="ebc-meta">
				<?php if ( $company ) : ?><span><?php echo esc_html( $company ); ?></span><?php endif; ?>
				<?php if ( $company && $dept ) : ?><span class="dot"></span><?php endif; ?>
				<?php if ( $dept ) : ?><span><?php echo esc_html( $dept ); ?></span><?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="ebc-actions">
				<?php if ( $whatsapp ) : ?>
					<a class="ebc-btn-outline" href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $whatsapp ) ); ?>" target="_blank" rel="noopener noreferrer">
						<i class="fab fa-whatsapp"></i> WhatsApp
					</a>
				<?php endif; ?>
				<?php if ( $phone ) : ?>
					<a class="ebc-btn-outline" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
						<i class="fas fa-phone"></i> <?php echo esc_html__( 'Phone', 'employee-business-cards' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $website ) : ?>
					<a class="ebc-btn-outline" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
						<i class="fas fa-globe"></i> <?php echo esc_html__( 'Website', 'employee-business-cards' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<a class="ebc-btn-outline" href="mailto:<?php echo esc_attr( sanitize_email( $email ) ); ?>">
						<i class="far fa-envelope"></i> <?php echo esc_html__( 'Email', 'employee-business-cards' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $location || $bio ) : ?>
			<div class="ebc-location-bio">
				<?php if ( $location ) : ?>
					<div class="ebc-location"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html( $location ); ?></div>
				<?php endif; ?>
				<?php if ( $bio ) : ?>
					<div class="ebc-bio"><?php echo wp_kses_post( wpautop( $bio ) ); ?></div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<a class="ebc-primary-btn" href="<?php echo esc_url( $vcard_url ); ?>">
				<i class="fas fa-download"></i> <?php echo esc_html__( 'Save Contact', 'employee-business-cards' ); ?>
			</a>

			<div class="ebc-secondary-actions">
				<button class="ebc-btn-secondary ebc-share-btn" type="button" data-ebc-share-url="<?php echo esc_attr( $card_url ); ?>" data-ebc-share-prompt="<?php echo esc_attr__( 'Copy this URL:', 'employee-business-cards' ); ?>">
					<i class="fas fa-share"></i> <?php echo esc_html__( 'Share Card', 'employee-business-cards' ); ?>
				</button>
				<?php if ( $qr_url ) : ?>
					<button class="ebc-btn-tertiary ebc-qr-toggle" type="button" aria-expanded="false" aria-controls="ebc-qr-<?php echo esc_attr( (string) $post_id ); ?>">
						<i class="fas fa-qrcode"></i> <span data-show-label="<?php echo esc_attr__( 'Show QR', 'employee-business-cards' ); ?>" data-hide-label="<?php echo esc_attr__( 'Hide QR', 'employee-business-cards' ); ?>"><?php echo esc_html__( 'Show QR', 'employee-business-cards' ); ?></span>
					</button>
				<?php endif; ?>
			</div>

			<?php if ( $qr_url ) : ?>
				<div class="ebc-qr-container" id="ebc-qr-<?php echo esc_attr( (string) $post_id ); ?>" hidden>
					<p><?php echo esc_html__( 'Scan to open this card', 'employee-business-cards' ); ?></p>
					<img src="<?php echo esc_url( $qr_url ); ?>" alt="<?php echo esc_attr__( 'QR code for employee card', 'employee-business-cards' ); ?>" loading="lazy" />
				</div>
			<?php endif; ?>

			<?php if ( $has_socials ) : ?>
				<div class="ebc-socials">
					<?php if ( $instagram ) : ?>
						<a class="ebc-social-icon" href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
					<?php endif; ?>
					<?php if ( $twitter ) : ?>
						<a class="ebc-social-icon" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-x-twitter"></i></a>
					<?php endif; ?>
					<?php if ( $linkedin ) : ?>
						<a class="ebc-social-icon" href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

</div>
<?php
if ( $render_full_page ) {
	get_footer();
}

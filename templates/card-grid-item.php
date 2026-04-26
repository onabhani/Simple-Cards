<?php
/**
 * Grid item template.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$card_id = isset( $post_id ) ? absint( $post_id ) : get_the_ID();
if ( ! $card_id ) {
	return;
}

$name      = ebc_get_card_name( $card_id );
$job_title = (string) ebc_get_field_value( $card_id, 'job_title' );
$company   = (string) ebc_get_field_value( $card_id, 'company_name' );
$photo     = ebc_get_card_photo_url( $card_id, 'medium' );
$url       = ebc_get_card_url( $card_id );
?>
<article class="ebc-grid-item">
	<?php if ( $photo ) : ?>
		<div class="ebc-grid-photo"><img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" /></div>
	<?php endif; ?>
	<h3><?php echo esc_html( $name ); ?></h3>
	<?php if ( $job_title ) : ?><p class="ebc-grid-job"><?php echo esc_html( $job_title ); ?></p><?php endif; ?>
	<?php if ( $company ) : ?><p class="ebc-grid-company"><?php echo esc_html( $company ); ?></p><?php endif; ?>
	<a class="ebc-btn ebc-btn-primary" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html__( 'View Card', 'employee-business-cards' ); ?></a>
</article>

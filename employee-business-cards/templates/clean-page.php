<?php
/**
 * Minimal page wrapper used for employee card pages and any post that
 * embeds the [employee_business_card] shortcode. No theme header/footer.
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

EBC_Assets::enqueue_public_assets();

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
<?php wp_head(); ?>
</head>
<body <?php body_class( 'ebc-clean-page' ); ?>>
<main class="ebc-clean-main" id="ebc-content">
<?php
if ( is_singular( 'employee_card' ) ) {
	while ( have_posts() ) {
		the_post();
		$ebc_post_id = (int) get_the_ID();
		echo EBC_Shortcodes::instance()->render_card( $ebc_post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
} else {
	while ( have_posts() ) {
		the_post();
		the_content();
	}
}
?>
</main>
<?php wp_footer(); ?>
</body>
</html>

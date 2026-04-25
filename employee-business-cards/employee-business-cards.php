<?php
/**
 * Plugin Name: Employee Business Cards
 * Description: Create and share digital business cards for employees.
 * Version: 1.0.0
 * Author: Omar Alnabhane
 * Text Domain: employee-business-cards
 * Domain Path: /languages
 * Requires PHP: 8.0
 *
 * @package EmployeeBusinessCards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EBC_VERSION', '1.0.0' );
define( 'EBC_FILE', __FILE__ );
define( 'EBC_PATH', plugin_dir_path( __FILE__ ) );
define( 'EBC_URL', plugin_dir_url( __FILE__ ) );
define( 'EBC_TEXT_DOMAIN', 'employee-business-cards' );

require_once EBC_PATH . 'includes/helpers.php';
require_once EBC_PATH . 'includes/class-ebc-post-type.php';
require_once EBC_PATH . 'includes/class-ebc-meta-boxes.php';
require_once EBC_PATH . 'includes/class-ebc-settings.php';
require_once EBC_PATH . 'includes/class-ebc-shortcodes.php';
require_once EBC_PATH . 'includes/class-ebc-vcard.php';
require_once EBC_PATH . 'includes/class-ebc-assets.php';
require_once EBC_PATH . 'includes/class-ebc-plugin.php';

register_activation_hook( EBC_FILE, array( 'EBC_Plugin', 'activate' ) );
register_deactivation_hook( EBC_FILE, array( 'EBC_Plugin', 'deactivate' ) );

EBC_Plugin::instance();

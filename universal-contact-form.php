<?php
/**
 * Universal Contact Form.
 *
 * A highly customizable contact form.
 *
 * @package   Universal Contact Form
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 *
 * @wordpress-plugin
 * Plugin Name:       Universal Contact Form
 * Plugin URI:        https://github.com/ThemeAvenue/Universal-Contact-Form
 * Description:       A highly customizable contact form plugin.
 * Version:           1.0.0
 * Author:            ThemeAvenue
 * Author URI:        http://themeavenue.net
 * Text Domain:       ucf
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/ThemeAvenue/Universal-Contact-Form
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Define plugin path and URI
 *----------------------------------------------------------------------------*/
define( 'UCF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
require_once( plugin_dir_path( __FILE__ ) . 'public/class-ucf.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/includes/class-custom-post-type.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/includes/class-generator.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/includes/class-submit.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/includes/shortcodes.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/views/templates.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Universal_Contact_Form', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Universal_Contact_Form', 'deactivate' ) );

/*
 * @TODO:
 *
 * - replace Plugin_Name with the name of the class defined in
 *   `class-plugin-name.php`
 */
add_action( 'plugins_loaded', array( 'Universal_Contact_Form', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/**
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-ucf-admin.php' );
	add_action( 'plugins_loaded', array( 'Universal_Contact_Form_Admin', 'get_instance' ) );

}

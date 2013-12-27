<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Universal Contact Form
 * @author    ThemeAvenue <web@themeavenue.net>
 * @license   GPL-2.0+
 * @link      http://themeavenue.net
 * @copyright 2013 ThemeAvenue
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// @TODO: Delete the form post types
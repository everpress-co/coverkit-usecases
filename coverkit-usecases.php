<?php
/**
 * Plugin Name: CoverKit Use Cases
 * Plugin URI: https://coverkit.com
 * Description: Loads custom CoverKit use case plugins from the plugins/ directory in this package.
 * Version: 0.1.4
 * Requires at least: 7.0
 * Requires PHP: 8.0
 * Requires Plugins: coverkit
 * Author: EverPress
 * Author URI: https://coverkit.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: coverkit-usecases
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COVERKIT_USECASES_VERSION' ) ) {
	define( 'COVERKIT_USECASES_VERSION', '0.1.4' );
}

if ( ! defined( 'COVERKIT_USECASES_FILE' ) ) {
	define( 'COVERKIT_USECASES_FILE', __FILE__ );
}

if ( ! defined( 'COVERKIT_USECASES_DIR' ) ) {
	define( 'COVERKIT_USECASES_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Load every use case bootstrap under plugins/coverkit-usecase-{slug}/.
 *
 * @return void
 */
function coverkit_usecases_load_plugins(): void {
	$plugins_dir = COVERKIT_USECASES_DIR . 'plugins';

	if ( ! is_dir( $plugins_dir ) ) {
		return;
	}

	$sandbox_bootstrap = $plugins_dir . '/coverkit-sandbox/coverkit-sandbox.php';
	if ( is_readable( $sandbox_bootstrap ) ) {
		require_once $sandbox_bootstrap;
	}

	$plugin_dirs = glob( $plugins_dir . '/coverkit-usecase-*', GLOB_ONLYDIR );

	if ( false === $plugin_dirs ) {
		return;
	}

	sort( $plugin_dirs, SORT_STRING );

	foreach ( $plugin_dirs as $plugin_dir ) {
		$slug      = basename( $plugin_dir );
		$bootstrap = $plugin_dir . '/' . $slug . '.php';

		if ( ! is_readable( $bootstrap ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug-only when bootstrap is missing.
				error_log( sprintf( 'CoverKit Use Cases: missing bootstrap file %s', $bootstrap ) );
			}
			continue;
		}

		require_once $bootstrap;
	}
}

/**
 * Warn site owners when CoverKit is not available.
 *
 * @return void
 */
function coverkit_usecases_admin_notice_missing_coverkit(): void {
	if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	if ( function_exists( 'coverkit_register_use_case' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__(
			'CoverKit Use Cases requires the CoverKit plugin to be installed and active.',
			'coverkit-usecases'
		)
	);
}

add_action( 'plugins_loaded', 'coverkit_usecases_load_plugins', 15 );
add_action( 'admin_notices', 'coverkit_usecases_admin_notice_missing_coverkit' );

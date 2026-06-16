<?php
/**
 * Plugin Name: CoverKit Use Case: Dashboard widget
 * Plugin URI: https://coverkit.com
 * Description: Site-wide wp-admin dashboard widget with a CoverKit generated image as the background.
 * Version: 0.1.3
 * Requires at least: 7.0
 * Requires PHP: 8.0
 * Requires Plugins: coverkit
 * Author: EverPress
 * Author URI: https://coverkit.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: coverkit-usecase-dashboard-widget
 *
 * @package CoverKitUseCaseDashboardWidget
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

/**
 * Register the dashboard widget use case with CoverKit.
 *
 * @return void
 */
function coverkit_usecase_dashboard_widget_register(): void {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dashboard-widget-use-case.php';

	coverkit_register_use_case(
		'dashboard_widget',
		array(
			'class' => \CoverKitUseCaseDashboardWidget\Dashboard_Widget_Use_Case::class,
			'label' => __( 'Dashboard widget', 'coverkit-usecase-dashboard-widget' ),
		)
	);
}

add_action( 'coverkit_init', 'coverkit_usecase_dashboard_widget_register', 5 );

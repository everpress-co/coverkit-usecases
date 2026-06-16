<?php
/**
 * Plugin Name: CoverKit Use Case: Starter
 * Plugin URI: https://github.com/everpress-co/coverkit-usecases
 * Description: Minimal editor-only test use case for the CoverKit use cases monorepo.
 * Version: 0.1.0
 * Requires at least: 7.0
 * Requires PHP: 8.0
 * Requires Plugins: coverkit
 * Author: EverPress
 * Author URI: https://coverkit.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: coverkit-usecase-starter
 *
 * @package CoverKitUseCaseStarter
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

/**
 * Register the starter use case with CoverKit.
 *
 * @return void
 */
function coverkit_usecase_starter_register(): void {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-starter-use-case.php';

	coverkit_register_use_case(
		'starter',
		array(
			'class'       => \CoverKitUseCaseStarter\Starter_Use_Case::class,
			'label'       => __( 'Starter (test)', 'coverkit-usecase-starter' ),
			'description' => __(
				'Editor-only demo use case for testing templates and field mappings.',
				'coverkit-usecase-starter'
			),
		)
	);
}

add_action( 'coverkit_init', 'coverkit_usecase_starter_register', 5 );

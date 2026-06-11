<?php
/**
 * Plugin Name: CoverKit Use Case: Starter
 * Plugin URI: https://github.com/everpress-co/coverkit-usecases
 * Description: Minimal editor-only test use case for the CoverKit use cases monorepo.
 * Version: 0.1.1
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

define( 'COVERKIT_USECASE_STARTER_VERSION', '0.1.1' );
define( 'COVERKIT_USECASE_STARTER_FILE', __FILE__ );
define( 'COVERKIT_USECASE_STARTER_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Register the starter use case with CoverKit.
 *
 * @return void
 */
function coverkit_usecase_starter_register(): void {
	require_once COVERKIT_USECASE_STARTER_DIR . 'includes/class-starter-use-case.php';

	\CoverKit\coverkit_register_use_case(
		'starter',
		array(
			'class' => \CoverKitUseCaseStarter\Starter_Use_Case::class,
			'label' => __( 'Starter (test)', 'coverkit-usecase-starter' ),
		)
	);
}

add_action( 'coverkit_init', 'coverkit_usecase_starter_register', 5 );

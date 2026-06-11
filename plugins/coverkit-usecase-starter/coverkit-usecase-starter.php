<?php
/**
 * Plugin Name: CoverKit Use Case — Starter
 * Plugin URI: https://coverkit.com
 * Description: Minimal test use case for the CoverKit use cases monorepo. Editor preview only — no front-end output.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
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

define( 'COVERKIT_USECASE_STARTER_VERSION', '0.1.0' );
define( 'COVERKIT_USECASE_STARTER_FILE', __FILE__ );
define( 'COVERKIT_USECASE_STARTER_DIR', plugin_dir_path( __FILE__ ) );

require_once COVERKIT_USECASE_STARTER_DIR . 'includes/class-starter-use-case.php';

/**
 * Bootstrap the starter use case when CoverKit is available.
 *
 * @return void
 */
function coverkit_usecase_starter_bootstrap(): void {
	if ( ! function_exists( 'CoverKit\coverkit_register_use_case' ) ) {
		add_action(
			'admin_notices',
			static function (): void {
				if ( ! current_user_can( 'activate_plugins' ) ) {
					return;
				}

				printf(
					'<div class="notice notice-warning"><p>%s</p></div>',
					esc_html__(
						'CoverKit Use Case — Starter requires the CoverKit plugin to be installed and active.',
						'coverkit-usecase-starter'
					)
				);
			}
		);
		return;
	}

	add_action(
		'coverkit_init',
		static function (): void {
			\CoverKit\coverkit_register_use_case(
				'starter',
				array(
					'class' => \CoverKitUseCaseStarter\Starter_Use_Case::class,
					'label' => __( 'Starter (test)', 'coverkit-usecase-starter' ),
				)
			);
		},
		5
	);
}

add_action( 'plugins_loaded', 'coverkit_usecase_starter_bootstrap', 20 );

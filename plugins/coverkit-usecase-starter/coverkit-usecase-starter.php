<?php
/**
 * Starter use case bootstrap — loaded by CoverKit Use Cases (coverkit-usecases.php).
 *
 * @package CoverKitUseCaseStarter
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

define( 'COVERKIT_USECASE_STARTER_VERSION', '0.1.0' );
define( 'COVERKIT_USECASE_STARTER_FILE', __FILE__ );
define( 'COVERKIT_USECASE_STARTER_DIR', plugin_dir_path( __FILE__ ) );


require_once COVERKIT_USECASE_STARTER_DIR . 'includes/class-starter-use-case.php';

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

<?php
/**
 * Starter use case bootstrap — loaded by CoverKit Use Cases (coverkit-usecases.php).
 *
 * @package CoverKitUseCaseStarter
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'includes/class-starter-use-case.php';

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

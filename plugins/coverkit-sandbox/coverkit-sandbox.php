<?php
/**
 * CoverKit Sandbox use case — bundled with CoverKit Use Cases.
 *
 * @package CoverKitSandbox
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

/**
 * Register the Sandbox use case with CoverKit.
 *
 * @return void
 */
function coverkit_sandbox_register(): void {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sandbox-use-case.php';

	\CoverKit\coverkit_register_use_case(
		'sandbox',
		array(
			'label'       => __( 'Sandbox', 'coverkit-sandbox' ),
			'description' => __(
				'Test templates and mappings in the editor. Exposes every built-in setting control; no front-end output.',
				'coverkit-sandbox'
			),
			'class'       => \CoverKitSandbox\Sandbox_Use_Case::class,
		)
	);
}

add_action( 'coverkit_init', 'coverkit_sandbox_register', 5 );

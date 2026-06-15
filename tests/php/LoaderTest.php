<?php
/**
 * Tests the monorepo loader discovers use case bootstraps.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

namespace CoverKitUseCases\Tests;

/**
 * @covers ::coverkit_usecases_load_plugins
 */
class LoaderTest extends CoverKitUseCases_TestCase {

	public function test_load_plugins_finds_readable_bootstraps(): void {
		require_once COVERKIT_USECASES_FILE;

		$plugins_dir = COVERKIT_USECASES_DIR . 'plugins';
		$plugin_dirs = glob( $plugins_dir . '/coverkit-usecase-*', GLOB_ONLYDIR );

		$this->assertIsArray( $plugin_dirs );
		$this->assertNotEmpty( $plugin_dirs );

		foreach ( $plugin_dirs as $plugin_dir ) {
			$slug      = basename( $plugin_dir );
			$bootstrap = $plugin_dir . '/' . $slug . '.php';
			$this->assertFileIsReadable( $bootstrap, $bootstrap );
		}

		\coverkit_usecases_load_plugins();

		$this->assertTrue( \function_exists( 'coverkit_usecase_starter_register' ) );
		$this->assertTrue( \function_exists( 'coverkit_sandbox_register' ) );
	}
}

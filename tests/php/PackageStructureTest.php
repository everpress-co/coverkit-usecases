<?php
/**
 * Ensures release zips are WordPress-installable plugin packages.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

namespace CoverKitUseCases\Tests;

/**
 * @coversNothing
 */
class PackageStructureTest extends CoverKitUseCases_TestCase {

	public function test_package_release_verify_succeeds(): void {
		$repo_root = dirname( __DIR__, 2 );
		$command   = 'cd ' . escapeshellarg( $repo_root ) . ' && composer run package:release:verify 2>&1';
		$output    = array();
		$exit_code = 0;

		exec( $command, $output, $exit_code );

		$this->assertSame(
			0,
			$exit_code,
			"package:release:verify failed:\n" . implode( "\n", $output )
		);
	}

	/**
	 * @depends test_package_release_verify_succeeds
	 */
	public function test_release_zip_has_plugin_folder_root(): void {
		$repo_root = dirname( __DIR__, 2 );

		$plugin_dirs = glob( $repo_root . '/plugins/coverkit-usecase-*', GLOB_ONLYDIR ) ?: array();
		$this->assertNotEmpty( $plugin_dirs );

		foreach ( $plugin_dirs as $plugin_dir ) {
			$slug            = basename( $plugin_dir );
			$bootstrap_file  = $plugin_dir . '/' . $slug . '.php';
			$bootstrap       = (string) file_get_contents( $bootstrap_file );
			$this->assertMatchesRegularExpression( '/^\s*\*\s*Version:\s*(.+)$/m', $bootstrap, $bootstrap_file );
			preg_match( '/^\s*\*\s*Version:\s*(.+)$/m', $bootstrap, $matches );
			$zip_path = $repo_root . '/dist/' . $slug . '.zip';

			$this->assertFileExists( $zip_path, "Missing release zip for {$slug}" );

			$zip = new \ZipArchive();
			$this->assertTrue( $zip->open( $zip_path ) );

			$bootstrap = $slug . '/' . $slug . '.php';
			$found     = false;

			for ( $index = 0; $index < $zip->numFiles; $index++ ) {
				$name = $zip->getNameIndex( $index );
				if ( ! is_string( $name ) ) {
					continue;
				}

				if ( $bootstrap === $name ) {
					$found = true;
				}

				$this->assertStringStartsWith(
					$slug . '/',
					$name,
					"Zip entry must be inside plugin folder: {$name}"
				);
			}

			$zip->close();
			$this->assertTrue( $found, "Zip must contain {$bootstrap}" );
		}
	}

}

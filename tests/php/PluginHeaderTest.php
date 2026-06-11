<?php
/**
 * Validates WordPress plugin headers on use case bootstraps.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

namespace CoverKitUseCases\Tests;

/**
 * @coversNothing
 */
class PluginHeaderTest extends CoverKitUseCases_TestCase {

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function bootstrap_provider(): array {
		$plugins_dir = COVERKIT_USECASES_DIR . 'plugins';
		$plugin_dirs = glob( $plugins_dir . '/coverkit-usecase-*', GLOB_ONLYDIR );

		if ( false === $plugin_dirs || array() === $plugin_dirs ) {
			return array();
		}

		$cases = array();
		foreach ( $plugin_dirs as $plugin_dir ) {
			$slug                = basename( $plugin_dir );
			$cases[ $slug ] = array( $plugin_dir . '/' . $slug . '.php' );
		}

		return $cases;
	}

	/**
	 * @dataProvider bootstrap_provider
	 */
	public function test_bootstrap_has_required_plugin_header_fields( string $bootstrap_file ): void {
		$this->assertFileIsReadable( $bootstrap_file );

		$contents = (string) file_get_contents( $bootstrap_file );
		$headers  = $this->parse_plugin_headers( $contents );

		$this->assertArrayHasKey( 'Plugin Name', $headers );
		$this->assertNotSame( '', trim( $headers['Plugin Name'] ) );

		$this->assertArrayHasKey( 'Version', $headers );
		$this->assertMatchesRegularExpression( '/^\d+\.\d+\.\d+/', $headers['Version'] );

		$this->assertArrayHasKey( 'Requires Plugins', $headers );
		$this->assertStringContainsString( 'coverkit', strtolower( $headers['Requires Plugins'] ) );

		$this->assertArrayHasKey( 'Text Domain', $headers );
		$this->assertNotSame( '', trim( $headers['Text Domain'] ) );
	}

	/**
	 * @param string $contents Bootstrap file contents.
	 * @return array<string, string>
	 */
	private function parse_plugin_headers( string $contents ): array {
		if ( ! preg_match( '/\/\*\*(.*?)\*\//s', $contents, $matches ) ) {
			return array();
		}

		$block   = $matches[1];
		$headers = array();

		foreach ( preg_split( '/\R/', $block ) ?: array() as $line ) {
			if ( ! preg_match( '/^\s*\*\s*([^:]+):\s*(.+)$/', $line, $parts ) ) {
				continue;
			}

			$headers[ trim( $parts[1] ) ] = trim( $parts[2] );
		}

		return $headers;
	}
}

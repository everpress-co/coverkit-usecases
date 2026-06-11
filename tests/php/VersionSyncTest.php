<?php
/**
 * Ensures release version in package.json matches PHP headers and readme files.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

namespace CoverKitUseCases\Tests;

/**
 * @coversNothing
 */
class VersionSyncTest extends CoverKitUseCases_TestCase {

	/**
	 * @return string
	 */
	private function expected_version(): string {
		$package_json = dirname( __DIR__, 2 ) . '/package.json';
		$this->assertFileIsReadable( $package_json );

		$decoded = json_decode( (string) file_get_contents( $package_json ), true );
		$this->assertIsArray( $decoded );
		$this->assertArrayHasKey( 'version', $decoded );
		$this->assertIsString( $decoded['version'] );

		return $decoded['version'];
	}

	public function test_loader_version_matches_package_json(): void {
		$version  = $this->expected_version();
		$contents = (string) file_get_contents( COVERKIT_USECASES_FILE );
		$headers  = $this->parse_plugin_headers( $contents );

		$this->assertSame( $version, $headers['Version'] ?? '' );
		$this->assertTrue( defined( 'COVERKIT_USECASES_VERSION' ) );
		$this->assertSame( $version, COVERKIT_USECASES_VERSION );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function bootstrap_provider(): array {
		return PluginHeaderTest::bootstrap_provider();
	}

	/**
	 * @dataProvider bootstrap_provider
	 */
	public function test_bootstrap_version_is_valid_semver( string $bootstrap_file ): void {
		$contents = (string) file_get_contents( $bootstrap_file );
		$headers  = $this->parse_plugin_headers( $contents );
		$slug     = basename( dirname( $bootstrap_file ) );
		$prefix   = $this->slug_to_constant_prefix( $slug );
		$constant = $prefix . '_VERSION';
		$version  = $headers['Version'] ?? '';

		$this->assertMatchesRegularExpression( '/^\d+\.\d+\.\d+/', $version );

		if ( $this->plugin_uses_version_constant( $slug ) ) {
			$this->assertMatchesRegularExpression(
				"/define\(\s*'{$constant}',\s*'{$version}'\s*\);/",
				$contents
			);
		}
	}

	/**
	 * @dataProvider bootstrap_provider
	 */
	public function test_readme_stable_tag_matches_bootstrap_version( string $bootstrap_file ): void {
		$contents = (string) file_get_contents( $bootstrap_file );
		$headers  = $this->parse_plugin_headers( $contents );
		$version  = $headers['Version'] ?? '';
		$readme_file = dirname( $bootstrap_file ) . '/readme.txt';

		if ( ! is_readable( $readme_file ) ) {
			$this->markTestSkipped( 'readme.txt not present for ' . basename( dirname( $bootstrap_file ) ) );
		}

		$readme_contents = (string) file_get_contents( $readme_file );
		$this->assertMatchesRegularExpression( '/^Stable tag:\s*' . preg_quote( $version, '/' ) . '\s*$/m', $readme_contents );
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

	/**
	 * @param string $slug Plugin folder slug.
	 * @return string
	 */
	private function slug_to_constant_prefix( string $slug ): string {
		$without_prefix = preg_replace( '/^coverkit-usecase-/', '', $slug ) ?? $slug;

		return 'COVERKIT_USECASE_' . strtoupper( str_replace( '-', '_', $without_prefix ) );
	}

	/**
	 * Whether a use case bootstrap defines VERSION/FILE/DIR constants.
	 *
	 * The in-repo starter template stays minimal; release plugins use constants.
	 *
	 * @param string $slug Plugin folder slug.
	 * @return bool
	 */
	private function plugin_uses_version_constant( string $slug ): bool {
		return 'coverkit-usecase-starter' !== $slug;
	}
}

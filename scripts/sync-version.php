<?php
/**
 * Sync monorepo release version from package.json into PHP headers and readme files.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

$repo_root = dirname( __DIR__ );

$check_only = in_array( '--check', $argv, true );

$package_json = $repo_root . '/package.json';

if ( ! is_readable( $package_json ) ) {
	fwrite( STDERR, "package.json not found: {$package_json}\n" );
	exit( 1 );
}

$decoded = json_decode( (string) file_get_contents( $package_json ), true );

if ( ! is_array( $decoded ) || empty( $decoded['version'] ) || ! is_string( $decoded['version'] ) ) {
	fwrite( STDERR, "Could not read version from package.json\n" );
	exit( 1 );
}

$version = $decoded['version'];

if ( ! preg_match( '/^\d+\.\d+\.\d+/', $version ) ) {
	fwrite( STDERR, "Invalid semver in package.json: {$version}\n" );
	exit( 1 );
}

$changes = array();

$loader_file = $repo_root . '/coverkit-usecases.php';
if ( is_readable( $loader_file ) ) {
	$updated = sync_loader_file( $loader_file, $version );
	if ( null !== $updated ) {
		$changes[ $loader_file ] = $updated;
	}
}

$plugins_dir = $repo_root . '/plugins';
$plugin_dirs = glob( $plugins_dir . '/coverkit-usecase-*', GLOB_ONLYDIR ) ?: array();

sort( $plugin_dirs, SORT_STRING );

foreach ( $plugin_dirs as $plugin_dir ) {
	$slug            = basename( $plugin_dir );
	$bootstrap_file  = $plugin_dir . '/' . $slug . '.php';
	$readme_file     = $plugin_dir . '/readme.txt';
	$constant_prefix = slug_to_constant_prefix( $slug );

	if ( is_readable( $bootstrap_file ) ) {
		$updated = sync_bootstrap_file( $bootstrap_file, $version, $constant_prefix );
		if ( null !== $updated ) {
			$changes[ $bootstrap_file ] = $updated;
		}
	}

	if ( is_readable( $readme_file ) ) {
		$updated = sync_readme_stable_tag( $readme_file, $version );
		if ( null !== $updated ) {
			$changes[ $readme_file ] = $updated;
		}
	}
}

if ( array() === $changes ) {
	if ( $check_only ) {
		fwrite( STDOUT, "Version {$version} is already synced.\n" );
	}
	exit( 0 );
}

if ( $check_only ) {
	fwrite( STDERR, "Version drift detected for release {$version}:\n" );
	foreach ( array_keys( $changes ) as $file ) {
		fwrite( STDERR, "  - {$file}\n" );
	}
	fwrite( STDERR, "Run: composer run sync:version\n" );
	exit( 1 );
}

foreach ( $changes as $file => $contents ) {
	if ( false === file_put_contents( $file, $contents ) ) {
		fwrite( STDERR, "Failed to write {$file}\n" );
		exit( 1 );
	}
	fwrite( STDOUT, "Updated {$file}\n" );
}

fwrite( STDOUT, "Synced version {$version}.\n" );
exit( 0 );

/**
 * Convert plugin slug to VERSION constant prefix.
 *
 * @param string $slug Plugin folder slug.
 * @return string
 */
function slug_to_constant_prefix( string $slug ): string {
	$without_prefix = preg_replace( '/^coverkit-usecase-/', '', $slug ) ?? $slug;
	$normalized     = strtoupper( str_replace( '-', '_', $without_prefix ) );

	return 'COVERKIT_USECASE_' . $normalized;
}

/**
 * Sync loader plugin version fields.
 *
 * @param string $file    Loader bootstrap path.
 * @param string $version Target version.
 * @return string|null Updated file contents or null when unchanged.
 */
function sync_loader_file( string $file, string $version ): ?string {
	$contents = (string) file_get_contents( $file );
	$updated  = preg_replace( '/^(\s*\*\s*Version:\s*).+$/m', '${1}' . $version, $contents, 1, $header_count );
	$updated  = preg_replace(
		"/^define\(\s*'COVERKIT_USECASES_VERSION',\s*'[^']*'\s*\);/m",
		"define( 'COVERKIT_USECASES_VERSION', '{$version}' );",
		$updated ?? $contents,
		1,
		$constant_count
	);

	if ( 0 === $header_count || 0 === $constant_count || null === $updated ) {
		fwrite( STDERR, "Could not update version fields in {$file}\n" );
		exit( 1 );
	}

	return $updated === $contents ? null : $updated;
}

/**
 * Sync use case bootstrap version fields.
 *
 * @param string $file            Bootstrap path.
 * @param string $version         Target version.
 * @param string $constant_prefix Constant prefix without _VERSION suffix.
 * @return string|null Updated file contents or null when unchanged.
 */
function sync_bootstrap_file( string $file, string $version, string $constant_prefix ): ?string {
	$contents = (string) file_get_contents( $file );
	$updated  = preg_replace( '/^(\s*\*\s*Version:\s*).+$/m', '${1}' . $version, $contents, 1, $header_count );
	$updated  = preg_replace(
		"/^define\(\s*'{$constant_prefix}_VERSION',\s*'[^']*'\s*\);/m",
		"define( '{$constant_prefix}_VERSION', '{$version}' );",
		$updated ?? $contents,
		1,
		$constant_count
	);

	if ( 0 === $header_count || 0 === $constant_count || null === $updated ) {
		fwrite( STDERR, "Could not update version fields in {$file}\n" );
		exit( 1 );
	}

	return $updated === $contents ? null : $updated;
}

/**
 * Sync readme.txt Stable tag field.
 *
 * @param string $file    readme.txt path.
 * @param string $version Target version.
 * @return string|null Updated file contents or null when unchanged.
 */
function sync_readme_stable_tag( string $file, string $version ): ?string {
	$contents = (string) file_get_contents( $file );
	$updated  = preg_replace( '/^Stable tag:\s*.+$/m', 'Stable tag: ' . $version, $contents, 1, $count );

	if ( 0 === $count || null === $updated ) {
		fwrite( STDERR, "Could not update Stable tag in {$file}\n" );
		exit( 1 );
	}

	return $updated === $contents ? null : $updated;
}

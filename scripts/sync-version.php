<?php
/**
 * Sync monorepo release version from package.json into PHP headers and readme files.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

$repo_root = dirname( __DIR__ );

$check_only      = in_array( '--check', $argv, true );
$changed_since   = null;
$sync_all_plugins = true;

for ( $index = 1; $index < $argc; $index++ ) {
	$arg = $argv[ $index ];

	if ( '--changed-since' === $arg ) {
		$changed_since = $argv[ $index + 1 ] ?? '';
		if ( '' === $changed_since ) {
			fwrite( STDERR, "--changed-since requires a git ref (e.g. 0.1.0)\n" );
			exit( 1 );
		}
		$sync_all_plugins = false;
		++$index;
		continue;
	}

	if ( '--all-plugins' === $arg ) {
		$sync_all_plugins = true;
		$changed_since    = null;
	}
}

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
	$slug = basename( $plugin_dir );

	if ( ! $sync_all_plugins && null !== $changed_since && ! plugin_has_changes_since( $repo_root, $changed_since, $slug ) ) {
		continue;
	}

	$bootstrap_file  = $plugin_dir . '/' . $slug . '.php';
	$readme_file     = $plugin_dir . '/readme.txt';
	$constant_prefix = slug_to_constant_prefix( $slug );

	if ( is_readable( $bootstrap_file ) ) {
		$bootstrap_contents = (string) file_get_contents( $bootstrap_file );

		if ( plugin_uses_version_constant( $slug, $bootstrap_contents ) ) {
			$updated = sync_bootstrap_file( $bootstrap_file, $version, $constant_prefix );
		} else {
			$updated = sync_bootstrap_header_only( $bootstrap_file, $version );
		}

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

if ( $check_only ) {
	$errors = array();

	if ( isset( $changes[ $loader_file ] ) ) {
		$errors[] = "Loader version does not match package.json ({$loader_file})";
	}

	foreach ( $plugin_dirs as $plugin_dir ) {
		$slug = basename( $plugin_dir );
		$consistency_error = validate_plugin_version_consistency( $plugin_dir, $slug );
		if ( null !== $consistency_error ) {
			$errors[] = $consistency_error;
		}

		$should_match_package = false;
		if ( in_array( '--all-plugins', $argv, true ) ) {
			$should_match_package = true;
		} elseif ( null !== $changed_since ) {
			$should_match_package = plugin_has_changes_since( $repo_root, $changed_since, $slug );
		}

		if ( ! $should_match_package ) {
			continue;
		}

		$bootstrap_file = $plugin_dir . '/' . $slug . '.php';
		if ( isset( $changes[ $bootstrap_file ] ) ) {
			$errors[] = "Plugin {$slug} version should be {$version} (bootstrap)";
		}

		$readme_file = $plugin_dir . '/readme.txt';
		if ( isset( $changes[ $readme_file ] ) ) {
			$errors[] = "Plugin {$slug} Stable tag should be {$version} (readme.txt)";
		}
	}

	if ( array() === $errors ) {
		fwrite( STDOUT, "Version checks passed for {$version}.\n" );
		exit( 0 );
	}

	fwrite( STDERR, "Version drift detected for release {$version}:\n" );
	foreach ( $errors as $error ) {
		fwrite( STDERR, "  - {$error}\n" );
	}
	fwrite( STDERR, "Run: composer run sync:version\n" );
	exit( 1 );
}

if ( array() === $changes ) {
	exit( 0 );
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
 * Whether a plugin directory has changes since a git ref.
 *
 * @param string $repo_root Repository root.
 * @param string $ref       Git ref (tag or commit).
 * @param string $slug      Plugin folder slug.
 * @return bool
 */
function plugin_has_changes_since( string $repo_root, string $ref, string $slug ): bool {
	$relative = 'plugins/' . $slug;
	$command  = sprintf(
		'cd %s && git diff --quiet %s..HEAD -- %s',
		escapeshellarg( $repo_root ),
		escapeshellarg( $ref ),
		escapeshellarg( $relative )
	);
	$output   = array();
	$exit     = 0;
	exec( $command, $output, $exit );

	// git diff --quiet: 0 = no diff, 1 = diff, other = error.
	if ( 0 === $exit ) {
		return false;
	}

	if ( 1 === $exit ) {
		return true;
	}

	fwrite( STDERR, "git diff failed for {$relative} (ref {$ref}, exit {$exit})\n" );
	exit( 1 );
}

/**
 * Validate bootstrap Version, VERSION constant, and readme Stable tag agree.
 *
 * @param string $plugin_dir Plugin directory.
 * @param string $slug       Plugin folder slug.
 * @return string|null Error message or null when valid.
 */
function validate_plugin_version_consistency( string $plugin_dir, string $slug ): ?string {
	$bootstrap_file  = $plugin_dir . '/' . $slug . '.php';
	$readme_file     = $plugin_dir . '/readme.txt';
	$constant_prefix = slug_to_constant_prefix( $slug );

	if ( ! is_readable( $bootstrap_file ) ) {
		return "Missing bootstrap: {$bootstrap_file}";
	}

	$contents = (string) file_get_contents( $bootstrap_file );

	if ( ! preg_match( '/^\s*\*\s*Version:\s*(.+)$/m', $contents, $header_match ) ) {
		return "Missing Version header in {$bootstrap_file}";
	}

	$header_version = trim( $header_match[1] );

	if ( ! preg_match( '/^\d+\.\d+\.\d+/', $header_version ) ) {
		return "Invalid Version in {$bootstrap_file}: {$header_version}";
	}

	if ( plugin_uses_version_constant( $slug, $contents ) && ! preg_match(
		"/^define\(\s*'{$constant_prefix}_VERSION',\s*'{$header_version}'\s*\);/m",
		$contents
	) ) {
		return "VERSION constant does not match header in {$bootstrap_file}";
	}

	if ( ! is_readable( $readme_file ) ) {
		return null;
	}

	$readme = (string) file_get_contents( $readme_file );

	if ( ! preg_match( '/^Stable tag:\s*(.+)$/m', $readme, $readme_match ) ) {
		return "Missing Stable tag in {$readme_file}";
	}

	$stable_tag = trim( $readme_match[1] );

	if ( $stable_tag !== $header_version ) {
		return "Stable tag ({$stable_tag}) does not match bootstrap Version ({$header_version}) in {$slug}";
	}

	return null;
}

/**
 * Whether a use case bootstrap defines a VERSION constant.
 *
 * @param string $slug     Plugin folder slug.
 * @param string $contents Bootstrap file contents.
 * @return bool
 */
function plugin_uses_version_constant( string $slug, string $contents ): bool {
	$constant_prefix = slug_to_constant_prefix( $slug );

	return (bool) preg_match( "/define\(\s*'{$constant_prefix}_VERSION'/", $contents );
}

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
		"/^\s*define\(\s*'COVERKIT_USECASES_VERSION',\s*'[^']*'\s*\);/m",
		"\tdefine( 'COVERKIT_USECASES_VERSION', '{$version}' );",
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
 * Sync only the Version header in a use case bootstrap.
 *
 * @param string $file    Bootstrap path.
 * @param string $version Target version.
 * @return string|null Updated file contents or null when unchanged.
 */
function sync_bootstrap_header_only( string $file, string $version ): ?string {
	$contents = (string) file_get_contents( $file );
	$updated  = preg_replace( '/^(\s*\*\s*Version:\s*).+$/m', '${1}' . $version, $contents, 1, $header_count );

	if ( 0 === $header_count || null === $updated ) {
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

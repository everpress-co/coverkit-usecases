<?php
/**
 * Sync monorepo release version from package.json into PHP headers and readme files.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

$repo_root = dirname( __DIR__ );

$check_only           = in_array( '--check', $argv, true );
$changed_since        = null;
$sync_all_plugins     = true;
$loader_only          = false;
$update_wp_tested_up_to = in_array( '--update-wp-tested-up-to', $argv, true );

for ( $index = 1; $index < $argc; $index++ ) {
	$arg = $argv[ $index ];

	if ( '--loader-only' === $arg ) {
		$loader_only      = true;
		$sync_all_plugins = false;
		$changed_since    = null;
		continue;
	}

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

if ( $update_wp_tested_up_to && ! $check_only ) {
	$latest_wp = fetch_wordpress_latest_version();
	if ( null === $latest_wp ) {
		fwrite( STDERR, "Could not resolve latest WordPress version from api.wordpress.org\n" );
		exit( 1 );
	}

	if ( ! isset( $decoded['wordpress'] ) || ! is_array( $decoded['wordpress'] ) ) {
		$decoded['wordpress'] = array();
	}

	$decoded['wordpress']['requiresAtLeast'] = '7.0';
	$decoded['wordpress']['testedUpTo']      = $latest_wp;

	$encoded = json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n";

	if ( false === file_put_contents( $package_json, $encoded ) ) {
		fwrite( STDERR, "Failed to write {$package_json}\n" );
		exit( 1 );
	}

	fwrite( STDOUT, "Updated package.json wordpress.testedUpTo to {$latest_wp}.\n" );
}

$wp_requires_at_least = '7.0';
$wp_tested_up_to      = '7.0';

if ( isset( $decoded['wordpress'] ) && is_array( $decoded['wordpress'] ) ) {
	if ( ! empty( $decoded['wordpress']['requiresAtLeast'] ) && is_string( $decoded['wordpress']['requiresAtLeast'] ) ) {
		$wp_requires_at_least = $decoded['wordpress']['requiresAtLeast'];
	}

	if ( ! empty( $decoded['wordpress']['testedUpTo'] ) && is_string( $decoded['wordpress']['testedUpTo'] ) ) {
		$wp_tested_up_to = $decoded['wordpress']['testedUpTo'];
	}
}

if ( '7.0' !== $wp_requires_at_least ) {
	fwrite( STDERR, "package.json wordpress.requiresAtLeast must be 7.0 (found {$wp_requires_at_least})\n" );
	exit( 1 );
}

if ( ! preg_match( '/^\d+\.\d+(?:\.\d+)?$/', $wp_tested_up_to ) ) {
	fwrite( STDERR, "Invalid wordpress.testedUpTo in package.json: {$wp_tested_up_to}\n" );
	exit( 1 );
}

$changes = array();

$loader_file = $repo_root . '/coverkit-usecases.php';
if ( is_readable( $loader_file ) ) {
	$loader_contents = (string) file_get_contents( $loader_file );
	$loader_updated  = sync_loader_file_contents( $loader_contents, $version );

	if ( ! $loader_only ) {
		$loader_updated = sync_bootstrap_wp_requires_at_least_contents( $loader_updated ?? $loader_contents, $wp_requires_at_least );
	}

	if ( null !== $loader_updated && $loader_updated !== $loader_contents ) {
		$changes[ $loader_file ] = $loader_updated;
	}
}

$plugins_dir = $repo_root . '/plugins';
$plugin_dirs = glob( $plugins_dir . '/coverkit-usecase-*', GLOB_ONLYDIR ) ?: array();

sort( $plugin_dirs, SORT_STRING );

foreach ( $plugin_dirs as $plugin_dir ) {
	$slug = basename( $plugin_dir );

	if ( $loader_only ) {
		continue;
	}

	$sync_plugin_version = $sync_all_plugins
		|| null === $changed_since
		|| plugin_has_changes_since( $repo_root, (string) $changed_since, $slug );

	$bootstrap_file  = $plugin_dir . '/' . $slug . '.php';
	$readme_file     = $plugin_dir . '/readme.txt';
	$constant_prefix = slug_to_constant_prefix( $slug );

	if ( is_readable( $bootstrap_file ) ) {
		$bootstrap_contents = (string) file_get_contents( $bootstrap_file );
		$bootstrap_updated  = $bootstrap_contents;

		if ( $sync_plugin_version ) {
			if ( plugin_uses_version_constant( $slug, $bootstrap_contents ) ) {
				$bootstrap_updated = sync_bootstrap_file_contents( $bootstrap_updated, $version, $constant_prefix );
			} else {
				$bootstrap_updated = sync_bootstrap_header_only_contents( $bootstrap_updated, $version );
			}
		}

		$bootstrap_updated = sync_bootstrap_wp_requires_at_least_contents( $bootstrap_updated, $wp_requires_at_least );

		if ( null !== $bootstrap_updated && $bootstrap_updated !== $bootstrap_contents ) {
			$changes[ $bootstrap_file ] = $bootstrap_updated;
		}
	}

	if ( is_readable( $readme_file ) ) {
		$readme_contents = (string) file_get_contents( $readme_file );
		$readme_updated  = $readme_contents;

		if ( $sync_plugin_version ) {
			$readme_updated = sync_readme_stable_tag_contents( $readme_updated, $version );
		}

		$readme_updated = sync_readme_wordpress_compatibility_contents( $readme_updated, $wp_requires_at_least, $wp_tested_up_to );

		if ( null !== $readme_updated && $readme_updated !== $readme_contents ) {
			$changes[ $readme_file ] = $readme_updated;
		}
	}
}

if ( $check_only ) {
	$errors = array();

	if ( is_readable( $loader_file ) ) {
		$loader_contents = (string) file_get_contents( $loader_file );

		if ( ! preg_match( '/^\s*\*\s*Version:\s*(.+)$/m', $loader_contents, $loader_version_match )
			|| trim( $loader_version_match[1] ) !== $version ) {
			$errors[] = "Loader version does not match package.json ({$loader_file})";
		}

		$loader_error = validate_bootstrap_wp_requires_at_least( $loader_file, $wp_requires_at_least );
		if ( null !== $loader_error ) {
			$errors[] = $loader_error;
		}
	}

	foreach ( $plugin_dirs as $plugin_dir ) {
		$slug = basename( $plugin_dir );
		$consistency_error = validate_plugin_version_consistency( $plugin_dir, $slug );
		if ( null !== $consistency_error ) {
			$errors[] = $consistency_error;
		}

		$bootstrap_file = $plugin_dir . '/' . $slug . '.php';
		$wp_bootstrap_error = validate_bootstrap_wp_requires_at_least( $bootstrap_file, $wp_requires_at_least );
		if ( null !== $wp_bootstrap_error ) {
			$errors[] = $wp_bootstrap_error;
		}

		$readme_file = $plugin_dir . '/readme.txt';
		$wp_readme_error = validate_readme_wordpress_compatibility( $readme_file, $wp_requires_at_least, $wp_tested_up_to );
		if ( null !== $wp_readme_error ) {
			$errors[] = $wp_readme_error;
		}

		$should_match_package = false;
		if ( in_array( '--all-plugins', $argv, true ) ) {
			$should_match_package = true;
		} elseif ( null !== $changed_since ) {
			$should_match_package = plugin_has_changes_since( $repo_root, $changed_since, $slug );
		} elseif ( $sync_all_plugins ) {
			$should_match_package = true;
		}

		if ( ! $should_match_package ) {
			continue;
		}

		if ( is_readable( $bootstrap_file ) && preg_match( '/^\s*\*\s*Version:\s*(.+)$/m', (string) file_get_contents( $bootstrap_file ), $version_match ) ) {
			if ( trim( $version_match[1] ) !== $version ) {
				$errors[] = "Plugin {$slug} version should be {$version} (bootstrap)";
			}
		}

		if ( is_readable( $readme_file ) && preg_match( '/^Stable tag:\s*(.+)$/m', (string) file_get_contents( $readme_file ), $stable_match ) ) {
			if ( trim( $stable_match[1] ) !== $version ) {
				$errors[] = "Plugin {$slug} Stable tag should be {$version} (readme.txt)";
			}
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
 * Fetch the latest stable WordPress version from api.wordpress.org.
 *
 * @return string|null Semver string (e.g. 7.0) or null on failure.
 */
function fetch_wordpress_latest_version(): ?string {
	$response = @file_get_contents( 'https://api.wordpress.org/core/version-check/1.7/?version=0' );

	if ( false === $response ) {
		return null;
	}

	$decoded = json_decode( $response, true );

	if ( ! is_array( $decoded ) || empty( $decoded['offers'][0]['version'] ) || ! is_string( $decoded['offers'][0]['version'] ) ) {
		return null;
	}

	return $decoded['offers'][0]['version'];
}

/**
 * Validate bootstrap Requires at least header.
 *
 * @param string $file                 Bootstrap path.
 * @param string $requires_at_least    Expected WordPress minimum.
 * @return string|null Error message or null when valid.
 */
function validate_bootstrap_wp_requires_at_least( string $file, string $requires_at_least ): ?string {
	if ( ! is_readable( $file ) ) {
		return null;
	}

	$contents = (string) file_get_contents( $file );

	if ( ! preg_match( '/^\s*\*\s*Requires at least:\s*(.+)$/m', $contents, $match ) ) {
		return "Missing Requires at least header in {$file}";
	}

	$found = trim( $match[1] );

	if ( $found !== $requires_at_least ) {
		return "Requires at least ({$found}) should be {$requires_at_least} in {$file}";
	}

	return null;
}

/**
 * Validate readme.txt WordPress compatibility headers.
 *
 * @param string $file              readme.txt path.
 * @param string $requires_at_least Expected WordPress minimum.
 * @param string $tested_up_to      Expected tested-up-to version.
 * @return string|null Error message or null when valid.
 */
function validate_readme_wordpress_compatibility( string $file, string $requires_at_least, string $tested_up_to ): ?string {
	if ( ! is_readable( $file ) ) {
		return null;
	}

	$contents = (string) file_get_contents( $file );

	if ( ! preg_match( '/^Requires at least:\s*(.+)$/m', $contents, $requires_match ) ) {
		return "Missing Requires at least in {$file}";
	}

	if ( trim( $requires_match[1] ) !== $requires_at_least ) {
		return "Requires at least should be {$requires_at_least} in {$file}";
	}

	if ( ! preg_match( '/^Tested up to:\s*(.+)$/m', $contents, $tested_match ) ) {
		return "Missing Tested up to in {$file}";
	}

	if ( trim( $tested_match[1] ) !== $tested_up_to ) {
		return "Tested up to should be {$tested_up_to} in {$file}";
	}

	return null;
}

/**
 * Sync loader plugin version fields.
 *
 * @param string $contents File contents.
 * @param string $version  Target version.
 * @return string Updated file contents.
 */
function sync_loader_file_contents( string $contents, string $version ): string {
	$updated = preg_replace( '/^(\s*\*\s*Version:\s*).+$/m', '${1}' . $version, $contents, 1, $header_count );
	$updated = preg_replace(
		"/^\s*define\(\s*'COVERKIT_USECASES_VERSION',\s*'[^']*'\s*\);/m",
		"\tdefine( 'COVERKIT_USECASES_VERSION', '{$version}' );",
		$updated ?? $contents,
		1,
		$constant_count
	);

	if ( 0 === $header_count || 0 === $constant_count || null === $updated ) {
		fwrite( STDERR, "Could not update loader version fields\n" );
		exit( 1 );
	}

	return $updated;
}

/**
 * Sync only the Version header in a use case bootstrap.
 *
 * @param string $contents File contents.
 * @param string $version  Target version.
 * @return string Updated file contents.
 */
function sync_bootstrap_header_only_contents( string $contents, string $version ): string {
	$updated = preg_replace( '/^(\s*\*\s*Version:\s*).+$/m', '${1}' . $version, $contents, 1, $header_count );

	if ( 0 === $header_count || null === $updated ) {
		fwrite( STDERR, "Could not update bootstrap Version header\n" );
		exit( 1 );
	}

	return $updated;
}

/**
 * Sync use case bootstrap version fields.
 *
 * @param string $contents        File contents.
 * @param string $version         Target version.
 * @param string $constant_prefix Constant prefix without _VERSION suffix.
 * @return string Updated file contents.
 */
function sync_bootstrap_file_contents( string $contents, string $version, string $constant_prefix ): string {
	$updated = preg_replace( '/^(\s*\*\s*Version:\s*).+$/m', '${1}' . $version, $contents, 1, $header_count );
	$updated = preg_replace(
		"/^define\(\s*'{$constant_prefix}_VERSION',\s*'[^']*'\s*\);/m",
		"define( '{$constant_prefix}_VERSION', '{$version}' );",
		$updated ?? $contents,
		1,
		$constant_count
	);

	if ( 0 === $header_count || 0 === $constant_count || null === $updated ) {
		fwrite( STDERR, "Could not update bootstrap version fields\n" );
		exit( 1 );
	}

	return $updated;
}

/**
 * Sync bootstrap Requires at least header.
 *
 * @param string $contents          File contents.
 * @param string $requires_at_least Target WordPress minimum.
 * @return string Updated file contents.
 */
function sync_bootstrap_wp_requires_at_least_contents( string $contents, string $requires_at_least ): string {
	$updated = preg_replace(
		'/^(\s*\*\s*Requires at least:\s*).+$/m',
		'${1}' . $requires_at_least,
		$contents,
		1,
		$count
	);

	if ( 0 === $count || null === $updated ) {
		fwrite( STDERR, "Could not update Requires at least header\n" );
		exit( 1 );
	}

	return $updated;
}

/**
 * Sync readme.txt Stable tag field.
 *
 * @param string $contents File contents.
 * @param string $version  Target version.
 * @return string Updated file contents.
 */
function sync_readme_stable_tag_contents( string $contents, string $version ): string {
	$updated = preg_replace( '/^Stable tag:\s*.+$/m', 'Stable tag: ' . $version, $contents, 1, $count );

	if ( 0 === $count || null === $updated ) {
		fwrite( STDERR, "Could not update Stable tag\n" );
		exit( 1 );
	}

	return $updated;
}

/**
 * Sync readme.txt WordPress compatibility headers.
 *
 * @param string $contents          File contents.
 * @param string $requires_at_least Target WordPress minimum.
 * @param string $tested_up_to      Target tested-up-to version.
 * @return string Updated file contents.
 */
function sync_readme_wordpress_compatibility_contents( string $contents, string $requires_at_least, string $tested_up_to ): string {
	$updated = preg_replace( '/^(Requires at least:\s*).+$/m', '${1}' . $requires_at_least, $contents, 1, $requires_count );
	$updated = preg_replace( '/^(Tested up to:\s*).+$/m', '${1}' . $tested_up_to, $updated ?? $contents, 1, $tested_count );

	if ( 0 === $requires_count || 0 === $tested_count || null === $updated ) {
		fwrite( STDERR, "Could not update readme WordPress compatibility headers\n" );
		exit( 1 );
	}

	return $updated;
}

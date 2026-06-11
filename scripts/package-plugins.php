<?php
/**
 * Build install-ready WordPress plugin zips for each use case.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

$repo_root = dirname( __DIR__ );

$verify_only = in_array( '--verify', $argv, true );

$dist_dir    = $repo_root . '/dist';
$staging_dir = $repo_root . '/build/release';

if ( is_dir( $staging_dir ) ) {
	remove_directory( $staging_dir );
}

if ( is_dir( $dist_dir ) ) {
	remove_directory( $dist_dir );
}

mkdir( $dist_dir, 0775, true );
mkdir( $staging_dir, 0775, true );

$plugin_dirs = glob( $repo_root . '/plugins/coverkit-usecase-*', GLOB_ONLYDIR ) ?: array();
sort( $plugin_dirs, SORT_STRING );

if ( array() === $plugin_dirs ) {
	fwrite( STDERR, "No use case plugins found under plugins/\n" );
	exit( 1 );
}

$zip_paths = array();

foreach ( $plugin_dirs as $plugin_dir ) {
	$slug            = basename( $plugin_dir );
	$bootstrap_file  = $plugin_dir . '/' . $slug . '.php';
	$plugin_version  = read_plugin_version( $bootstrap_file );
	$zip_path        = package_plugin( $repo_root, $plugin_dir, $slug, $plugin_version, $staging_dir, $dist_dir );
	$zip_paths[ $slug ] = $zip_path;
	fwrite( STDOUT, "Created {$zip_path}\n" );
}

if ( $verify_only ) {
	foreach ( $zip_paths as $slug => $zip_path ) {
		verify_zip_structure( $zip_path, $slug );
	}
	fwrite( STDOUT, "Verified " . count( $zip_paths ) . " release zip(s).\n" );
}

exit( 0 );

/**
 * Read semver from a use case bootstrap Version header.
 *
 * @param string $bootstrap_file Path to {slug}.php.
 * @return string
 */
function read_plugin_version( string $bootstrap_file ): string {
	if ( ! is_readable( $bootstrap_file ) ) {
		fwrite( STDERR, "Bootstrap not found: {$bootstrap_file}\n" );
		exit( 1 );
	}

	$contents = (string) file_get_contents( $bootstrap_file );

	if ( ! preg_match( '/^\s*\*\s*Version:\s*(.+)$/m', $contents, $matches ) ) {
		fwrite( STDERR, "Could not read Version header in {$bootstrap_file}\n" );
		exit( 1 );
	}

	$version = trim( $matches[1] );

	if ( ! preg_match( '/^\d+\.\d+\.\d+/', $version ) ) {
		fwrite( STDERR, "Invalid semver in {$bootstrap_file}: {$version}\n" );
		exit( 1 );
	}

	return $version;
}

/**
 * Package one use case plugin into a WordPress-installable zip.
 *
 * @param string $repo_root   Repository root.
 * @param string $plugin_dir  Source plugin directory.
 * @param string $slug        Plugin slug / folder name.
 * @param string $version     Release version.
 * @param string $staging_dir Staging root.
 * @param string $dist_dir    Output directory for zips.
 * @return string Absolute path to created zip.
 */
function package_plugin(
	string $repo_root,
	string $plugin_dir,
	string $slug,
	string $version,
	string $staging_dir,
	string $dist_dir
): string {
	$plugin_package_json = $plugin_dir . '/package.json';
	if ( is_readable( $plugin_package_json ) ) {
		run_npm_build( $plugin_dir );
	}

	$plugin_composer_json = $plugin_dir . '/composer.json';
	if ( is_readable( $plugin_composer_json ) && plugin_has_runtime_composer_require( $plugin_composer_json ) ) {
		run_composer_prod_install( $plugin_dir );
	}

	$stage_plugin_dir = $staging_dir . '/' . $slug;
	mkdir( $stage_plugin_dir, 0775, true );

	$excludes = build_exclude_patterns( $plugin_dir );

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $plugin_dir, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ( $iterator as $item ) {
		/** @var SplFileInfo $item */
		$source_path = $item->getPathname();
		$relative    = substr( $source_path, strlen( $plugin_dir ) + 1 );

		if ( should_exclude_path( $relative, $excludes ) ) {
			continue;
		}

		$target_path = $stage_plugin_dir . '/' . $relative;

		if ( $item->isDir() ) {
			if ( ! is_dir( $target_path ) ) {
				mkdir( $target_path, 0775, true );
			}
			continue;
		}

		$target_parent = dirname( $target_path );
		if ( ! is_dir( $target_parent ) ) {
			mkdir( $target_parent, 0775, true );
		}

		if ( ! copy( $source_path, $target_path ) ) {
			fwrite( STDERR, "Failed to copy {$source_path}\n" );
			exit( 1 );
		}
	}

	$bootstrap = $stage_plugin_dir . '/' . $slug . '.php';
	if ( ! is_readable( $bootstrap ) ) {
		fwrite( STDERR, "Missing bootstrap in staged plugin: {$bootstrap}\n" );
		exit( 1 );
	}

	$zip_name = "{$slug}-{$version}.zip";
	$zip_path = $dist_dir . '/' . $zip_name;

	if ( is_file( $zip_path ) ) {
		unlink( $zip_path );
	}

	$escaped_staging = escapeshellarg( $staging_dir );
	$escaped_zip     = escapeshellarg( $zip_path );
	$escaped_slug    = escapeshellarg( $slug );

	$command = "cd {$escaped_staging} && zip -rq {$escaped_zip} {$escaped_slug}";
	$exit    = 0;
	$output  = array();
	exec( $command, $output, $exit );

	if ( 0 !== $exit || ! is_file( $zip_path ) ) {
		fwrite( STDERR, "Failed to create zip for {$slug}\n" );
		if ( array() !== $output ) {
			fwrite( STDERR, implode( "\n", $output ) . "\n" );
		}
		exit( 1 );
	}

	return $zip_path;
}

/**
 * Run npm build inside a plugin when package.json exists.
 *
 * @param string $plugin_dir Plugin directory.
 * @return void
 */
function run_npm_build( string $plugin_dir ): void {
	$commands = array(
		'npm ci --no-fund --silent',
		'npm run build --if-present',
	);

	foreach ( $commands as $npm_command ) {
		$escaped_dir = escapeshellarg( $plugin_dir );
		$command     = "cd {$escaped_dir} && {$npm_command}";
		$exit        = 0;
		$output      = array();
		exec( $command, $output, $exit );

		if ( 0 !== $exit ) {
			fwrite( STDERR, "Command failed in {$plugin_dir}: {$npm_command}\n" );
			if ( array() !== $output ) {
				fwrite( STDERR, implode( "\n", $output ) . "\n" );
			}
			exit( 1 );
		}
	}
}

/**
 * Whether plugin composer.json has runtime requirements.
 *
 * @param string $composer_json Path to composer.json.
 * @return bool
 */
function plugin_has_runtime_composer_require( string $composer_json ): bool {
	$decoded = json_decode( (string) file_get_contents( $composer_json ), true );

	if ( ! is_array( $decoded ) ) {
		return false;
	}

	$require = $decoded['require'] ?? array();

	if ( ! is_array( $require ) ) {
		return false;
	}

	foreach ( array_keys( $require ) as $package ) {
		if ( 'php' !== $package ) {
			return true;
		}
	}

	return false;
}

/**
 * Run composer install --no-dev for plugin runtime dependencies.
 *
 * @param string $plugin_dir Plugin directory.
 * @return void
 */
function run_composer_prod_install( string $plugin_dir ): void {
	$escaped_dir = escapeshellarg( $plugin_dir );
	$command     = "cd {$escaped_dir} && composer install --no-dev --quiet";
	$exit        = 0;
	$output      = array();
	exec( $command, $output, $exit );

	if ( 0 !== $exit ) {
		fwrite( STDERR, "composer install --no-dev failed in {$plugin_dir}\n" );
		if ( array() !== $output ) {
			fwrite( STDERR, implode( "\n", $output ) . "\n" );
		}
		exit( 1 );
	}
}

/**
 * Build exclude patterns for a plugin directory.
 *
 * @param string $plugin_dir Plugin directory.
 * @return array<int, string>
 */
function build_exclude_patterns( string $plugin_dir ): array {
	$defaults = array(
		'node_modules',
		'src',
		'tests',
		'.git',
		'.github',
		'package.json',
		'package-lock.json',
		'composer.json',
		'composer.lock',
		'phpunit.xml',
		'phpunit.xml.dist',
		'.phpcs.xml',
		'.env',
		'.env.*',
		'.DS_Store',
		'.phpunit.cache',
		'.distignore',
	);

	$distignore = $plugin_dir . '/.distignore';
	if ( is_readable( $distignore ) ) {
		foreach ( file( $distignore, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) ?: array() as $line ) {
			$line = trim( $line );
			if ( '' === $line || str_starts_with( $line, '#' ) ) {
				continue;
			}
			$defaults[] = $line;
		}
	}

	$markdown_excludes = array();
	foreach ( glob( $plugin_dir . '/*.md' ) ?: array() as $markdown_file ) {
		if ( 'readme.txt' !== basename( $markdown_file ) ) {
			$markdown_excludes[] = basename( $markdown_file );
		}
	}

	return array_values( array_unique( array_merge( $defaults, $markdown_excludes ) ) );
}

/**
 * Whether a relative path should be excluded from packaging.
 *
 * @param string               $relative Relative path inside plugin.
 * @param array<int, string>   $excludes Exclude patterns.
 * @return bool
 */
function should_exclude_path( string $relative, array $excludes ): bool {
	$relative = str_replace( '\\', '/', $relative );

	foreach ( $excludes as $pattern ) {
		$pattern = str_replace( '\\', '/', $pattern );

		if ( str_ends_with( $pattern, '/*' ) ) {
			$prefix = substr( $pattern, 0, -2 );
			if ( $relative === $prefix || str_starts_with( $relative, $prefix . '/' ) ) {
				return true;
			}
			continue;
		}

		if ( str_ends_with( $pattern, '*' ) ) {
			$prefix = substr( $pattern, 0, -1 );
			if ( str_starts_with( basename( $relative ), $prefix ) ) {
				return true;
			}
			continue;
		}

		if ( $relative === $pattern || str_starts_with( $relative, $pattern . '/' ) ) {
			return true;
		}

		if ( basename( $relative ) === $pattern ) {
			return true;
		}
	}

	return false;
}

/**
 * Verify zip has WordPress plugin folder structure.
 *
 * @param string $zip_path Zip file path.
 * @param string $slug     Expected plugin slug.
 * @return void
 */
function verify_zip_structure( string $zip_path, string $slug ): void {
	$zip = new ZipArchive();

	if ( true !== $zip->open( $zip_path ) ) {
		fwrite( STDERR, "Could not open zip: {$zip_path}\n" );
		exit( 1 );
	}

	$bootstrap = $slug . '/' . $slug . '.php';
	$found     = false;

	for ( $index = 0; $index < $zip->numFiles; $index++ ) {
		$name = $zip->getNameIndex( $index );
		if ( ! is_string( $name ) ) {
			continue;
		}

		if ( $name === $bootstrap ) {
			$found = true;
		}

		if ( ! str_starts_with( $name, $slug . '/' ) ) {
			fwrite( STDERR, "Zip {$zip_path} has entry outside plugin folder: {$name}\n" );
			$zip->close();
			exit( 1 );
		}

		foreach ( array( 'node_modules/', 'src/', 'tests/', '.git/' ) as $forbidden ) {
			if ( str_contains( $name, $forbidden ) ) {
				fwrite( STDERR, "Zip {$zip_path} contains excluded path: {$name}\n" );
				$zip->close();
				exit( 1 );
			}
		}
	}

	$zip->close();

	if ( ! $found ) {
		fwrite( STDERR, "Zip {$zip_path} is missing {$bootstrap}\n" );
		exit( 1 );
	}
}

/**
 * Recursively remove a directory.
 *
 * @param string $directory Directory path.
 * @return void
 */
function remove_directory( string $directory ): void {
	if ( ! is_dir( $directory ) ) {
		return;
	}

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $directory, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ( $iterator as $item ) {
		/** @var SplFileInfo $item */
		if ( $item->isDir() ) {
			rmdir( $item->getPathname() );
			continue;
		}

		unlink( $item->getPathname() );
	}

	rmdir( $directory );
}

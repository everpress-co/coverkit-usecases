<?php
/**
 * PHPUnit bootstrap for CoverKit Use Cases.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- WordPress API stubs for PHPUnit.

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', sys_get_temp_dir() . '/coverkit-usecases-phpunit-abspath/' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

if ( ! defined( 'COVERKIT_USECASES_PHPUNIT' ) ) {
	define( 'COVERKIT_USECASES_PHPUNIT', true );
}

$usecases_root = dirname( __DIR__, 2 );

require_once $usecases_root . '/vendor/autoload.php';

if ( ! function_exists( 'plugin_dir_path' ) ) {
	/**
	 * @param string $file Plugin bootstrap file.
	 */
	function plugin_dir_path( string $file ): string {
		return rtrim( dirname( $file ), '/\\' ) . '/';
	}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	/**
	 * @param string $file Plugin bootstrap file.
	 */
	function plugin_dir_url( string $file ): string {
		unset( $file );
		return 'http://example.org/wp-content/plugins/coverkit-usecases/';
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * @param string $text Text.
	 */
	function __( string $text, ?string $domain = null ): string {
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	/**
	 * @param string $text Text.
	 */
	function esc_html__( string $text, ?string $domain = null ): string {
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * @param string $text Text.
	 */
	function esc_html( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'add_action' ) ) {
	/**
	 * Minimal action registry for PHPUnit.
	 *
	 * @param mixed $hook_name     Hook name.
	 * @param mixed $callback      Callback.
	 * @param int   $priority      Priority.
	 * @param int   $accepted_args Accepted args.
	 */
	function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ): void {
		$hook_name = (string) $hook_name;
		$priority  = (int) $priority;

		if ( ! isset( $GLOBALS['coverkit_usecases_phpunit_actions'][ $hook_name ] ) ) {
			$GLOBALS['coverkit_usecases_phpunit_actions'][ $hook_name ] = array();
		}

		if ( ! isset( $GLOBALS['coverkit_usecases_phpunit_actions'][ $hook_name ][ $priority ] ) ) {
			$GLOBALS['coverkit_usecases_phpunit_actions'][ $hook_name ][ $priority ] = array();
		}

		$GLOBALS['coverkit_usecases_phpunit_actions'][ $hook_name ][ $priority ][] = $callback;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	/**
	 * @param string $hook_name Hook name.
	 */
	function do_action( string $hook_name, mixed ...$args ): void {
		$hooks = $GLOBALS['coverkit_usecases_phpunit_actions'][ $hook_name ] ?? array();
		if ( array() === $hooks ) {
			return;
		}

		\ksort( $hooks, SORT_NUMERIC );

		foreach ( $hooks as $callbacks ) {
			foreach ( $callbacks as $callback ) {
				\call_user_func_array( $callback, $args );
			}
		}
	}
}

if ( ! function_exists( 'coverkit_usecases_phpunit_reset_actions' ) ) {
	/**
	 * Clear registered actions between PHPUnit tests.
	 */
	function coverkit_usecases_phpunit_reset_actions(): void {
		$GLOBALS['coverkit_usecases_phpunit_actions'] = array();
	}
}

if ( ! isset( $GLOBALS['coverkit_usecases_phpunit_actions'] ) ) {
	$GLOBALS['coverkit_usecases_phpunit_actions'] = array();
}

$coverkit_dir = getenv( 'COVERKIT_PLUGIN_DIR' );
if ( ! is_string( $coverkit_dir ) || '' === $coverkit_dir ) {
	$coverkit_dir = $usecases_root . '/../coverkit';
}

$coverkit_dir = rtrim( str_replace( '\\', '/', $coverkit_dir ), '/' ) . '/';

if ( ! is_dir( $coverkit_dir ) ) {
	throw new RuntimeException(
		sprintf(
			'CoverKit plugin directory not found at %s. Set COVERKIT_PLUGIN_DIR for PHPUnit.',
			$coverkit_dir
		)
	);
}

if ( ! defined( 'COVERKIT_PLUGIN_DIR' ) ) {
	define( 'COVERKIT_PLUGIN_DIR', $coverkit_dir );
}

if ( ! defined( 'COVERKIT_PLUGIN_URL' ) ) {
	define( 'COVERKIT_PLUGIN_URL', 'http://example.org/wp-content/plugins/coverkit/' );
}

if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

// Load CoverKit helpers without Freemius (coverkit vendor autoload pulls in wordpress-sdk).
require_once COVERKIT_PLUGIN_DIR . 'includes/functions.php';

spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'CoverKit\\';
		$len    = strlen( $prefix );
		if ( strncmp( $prefix, $class_name, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class_name, $len );
		$base_dir       = COVERKIT_PLUGIN_DIR . 'includes/';

		if ( strpos( $relative_class, 'Use_Cases\\' ) === 0 ) {
			$use_case_class = substr( $relative_class, strlen( 'Use_Cases\\' ) );
			$file           = $base_dir . 'use-cases/class-coverkit-' . strtolower( str_replace( '_', '-', $use_case_class ) ) . '.php';
		} elseif ( strpos( $relative_class, 'Generator_' ) === 0 ) {
			$file = $base_dir . 'generator/class-coverkit-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';
		} else {
			$file = $base_dir . 'class-coverkit-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';
		}

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

if ( ! defined( 'COVERKIT_USECASES_VERSION' ) ) {
	$package_json = $usecases_root . '/package.json';
	$decoded      = json_decode( (string) file_get_contents( $package_json ), true );
	$version      = ( is_array( $decoded ) && isset( $decoded['version'] ) && is_string( $decoded['version'] ) )
		? $decoded['version']
		: '0.0.0';
	define( 'COVERKIT_USECASES_VERSION', $version );
}

if ( ! defined( 'COVERKIT_USECASES_FILE' ) ) {
	define( 'COVERKIT_USECASES_FILE', $usecases_root . '/coverkit-usecases.php' );
}

if ( ! defined( 'COVERKIT_USECASES_DIR' ) ) {
	define( 'COVERKIT_USECASES_DIR', $usecases_root . '/' );
}

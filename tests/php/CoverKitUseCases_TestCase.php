<?php
/**
 * Base TestCase with Brain Monkey lifecycle.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

namespace CoverKitUseCases\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

abstract class CoverKitUseCases_TestCase extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		if ( \function_exists( 'coverkit_usecases_phpunit_reset_actions' ) ) {
			\coverkit_usecases_phpunit_reset_actions();
		}

		Functions\when( 'apply_filters' )->alias(
			static function ( $hook, $value, ...$args ) {
				unset( $hook, $args );
				return $value;
			}
		);

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'get_option' )->justReturn( array() );
	}

	protected function tearDown(): void {
		if ( \class_exists( \CoverKit\Use_Case_Registry::class ) ) {
			\CoverKit\Use_Case_Registry::reset();
		}

		if ( \function_exists( 'coverkit_usecases_phpunit_reset_actions' ) ) {
			\coverkit_usecases_phpunit_reset_actions();
		}

		Monkey\tearDown();
		parent::tearDown();
	}
}

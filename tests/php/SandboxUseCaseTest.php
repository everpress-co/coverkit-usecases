<?php
/**
 * Tests for Sandbox_Use_Case.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

namespace CoverKitUseCases\Tests;

use CoverKit\Use_Case_Registry;
use CoverKitSandbox\Sandbox_Use_Case;
use function CoverKit\coverkit_register_use_case;

/**
 * @covers \CoverKitSandbox\Sandbox_Use_Case
 */
class SandboxUseCaseTest extends CoverKitUseCases_TestCase {

	protected function setUp(): void {
		parent::setUp();
		Use_Case_Registry::reset();

		\Brain\Monkey\Functions\when( 'sanitize_key' )->returnArg();
		\Brain\Monkey\Functions\when( 'sanitize_text_field' )->returnArg();
		\Brain\Monkey\Functions\when( 'get_post_types' )->justReturn( array() );
		\Brain\Monkey\Functions\when( 'apply_filters' )->alias(
			static function ( $hook_name, $value, ...$args ) {
				if ( 'coverkit_use_case_sandbox_settings_schema' === $hook_name ) {
					$instance = new Sandbox_Use_Case();

					return $instance->filter_public_post_type_schema_options( is_array( $value ) ? $value : array(), Sandbox_Use_Case::class );
				}

				unset( $hook_name, $args );
				return $value;
			}
		);

		require_once COVERKIT_USECASES_DIR . 'plugins/coverkit-sandbox/includes/class-sandbox-use-case.php';

		coverkit_register_use_case(
			'sandbox',
			array(
				'label' => 'Sandbox',
				'class' => Sandbox_Use_Case::class,
			)
		);
	}

	public function test_slug_and_single_flag(): void {
		$this->assertFalse( Sandbox_Use_Case::is_install_single() );
		$this->assertSame( 'sandbox', Sandbox_Use_Case::get_slug() );
	}

	public function test_recommended_settings(): void {
		$recommended = Sandbox_Use_Case::get_recommended_settings();

		$this->assertSame( 300, $recommended['dimensions']['width'] );
		$this->assertSame( 300, $recommended['dimensions']['height'] );
		$this->assertSame( array( 'jpg', 'webp' ), $recommended['formats'] );
		$this->assertTrue( $recommended['crop'] );
	}

	public function test_settings_schema(): void {
		$schema = Sandbox_Use_Case::get_settings_schema();

		$this->assertNotFalse( \has_filter( 'coverkit_use_case_sandbox_settings_schema' ) );

		$this->assertArrayHasKey( 'format', $schema );
		$this->assertSame( 'select', $schema['format']['control'] );
		$this->assertSame( 'jpg', $schema['format']['default'] );

		$this->assertSame( 'text', $schema['alt_text']['control'] );
		$this->assertTrue( $schema['alt_text']['override'] );
		$this->assertSame( 'textarea', $schema['caption']['control'] );
		$this->assertSame( 'toggle', $schema['show_border']['control'] );
		$this->assertSame( 'checkbox', $schema['include_metadata']['control'] );

		$this->assertArrayHasKey( 'post_type', $schema );
		$this->assertSame( array( 'post' ), $schema['post_type']['default'] );
	}

	public function test_mapping_sources(): void {
		$sources = Sandbox_Use_Case::get_mapping_sources();

		$this->assertTrue( ! empty( $sources['post_title']['required'] ) );
		$this->assertTrue( ! empty( $sources['featured_image']['recommended'] ) );
	}

	public function test_sanitize_settings_uses_schema(): void {
		$clean = Sandbox_Use_Case::sanitize_settings(
			array(
				'format'           => 'webp',
				'quality'          => 200,
				'show_border'      => 1,
				'include_metadata' => 0,
			)
		);

		$this->assertSame( 'webp', $clean['format'] );
		$this->assertSame( 100, $clean['quality'] );
		$this->assertTrue( $clean['show_border'] );
		$this->assertFalse( $clean['include_metadata'] );
	}

	public function test_format_field_value_prefixes_post_date(): void {
		$instance = new Sandbox_Use_Case();
		$instance->maybe_init();

		$this->assertSame(
			'— Jan 1, 2024',
			$instance->filter_format_field_value( 'Jan 1, 2024', 'post_date', 1, array(), 'text', Sandbox_Use_Case::class )
		);
	}
}

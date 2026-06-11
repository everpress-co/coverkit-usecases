<?php
/**
 * Tests for the starter use case class.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

namespace CoverKitUseCases\Tests;

use CoverKitUseCaseStarter\Starter_Use_Case;

/**
 * @covers \CoverKitUseCaseStarter\Starter_Use_Case
 */
class StarterUseCaseTest extends CoverKitUseCases_TestCase {

	protected function setUp(): void {
		parent::setUp();
		require_once COVERKIT_USECASES_DIR . 'plugins/coverkit-usecase-starter/includes/class-starter-use-case.php';
	}

	public function test_recommended_settings(): void {
		$settings = Starter_Use_Case::get_recommended_settings();

		$this->assertSame(
			array(
				'crop'       => true,
				'dimensions' => array(
					'width'  => 400,
					'height' => 400,
				),
				'formats'    => array( 'jpg', 'webp' ),
			),
			$settings
		);
	}

	public function test_use_case_mapping_sources(): void {
		$sources = Starter_Use_Case::get_mapping_sources();

		$this->assertTrue( $sources['post_title']['required'] ?? false );
		$this->assertTrue( $sources['post_excerpt']['recommended'] ?? false );
		$this->assertTrue( $sources['site_logo']['recommended'] ?? false );
	}

	public function test_use_case_settings_schema(): void {
		$schema = Starter_Use_Case::get_settings_schema();

		$this->assertArrayHasKey( 'show_badge', $schema );
		$this->assertSame( 'boolean', $schema['show_badge']['type'] ?? null );
		$this->assertTrue( $schema['show_badge']['default'] ?? false );
	}
}

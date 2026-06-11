<?php
/**
 * Tests starter bootstrap registration on coverkit_init.
 *
 * @package CoverKitUseCases
 */

declare(strict_types=1);

namespace CoverKitUseCases\Tests;

use CoverKit\Use_Case_Registry;
use CoverKitUseCaseStarter\Starter_Use_Case;

/**
 * @coversNothing
 */
class StarterRegistrationTest extends CoverKitUseCases_TestCase {

	public function test_registers_starter_on_coverkit_init(): void {
		require_once COVERKIT_USECASES_DIR . 'plugins/coverkit-usecase-starter/coverkit-usecase-starter.php';

		\coverkit_usecase_starter_register();

		$type = Use_Case_Registry::get_instance()->get( 'starter' );

		$this->assertIsArray( $type );
		$this->assertSame( Starter_Use_Case::class, $type['class'] ?? null );
		$this->assertSame( 'Starter (test)', $type['label'] ?? null );
	}
}

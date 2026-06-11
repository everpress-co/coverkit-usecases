<?php
/**
 * Starter test use case — editor preview only.
 *
 * @package CoverKitUseCaseStarter
 */

declare(strict_types=1);

namespace CoverKitUseCaseStarter;

use CoverKit\Use_Case;

defined( 'ABSPATH' ) || exit;

/**
 * Minimal custom use case for testing templates and field mappings in the editor.
 *
 * No front-end hooks; safe to enable on any template while experimenting.
 */
class Starter_Use_Case extends Use_Case {

	/**
	 * Square preview output for quick visual checks.
	 *
	 * @return array<string, mixed>
	 */
	protected static function recommended_settings(): array {
		return array(
			'dimensions' => array(
				'width'  => 400,
				'height' => 400,
			),
			'crop'       => true,
			'formats'    => array( 'jpg', 'webp' ),
		);
	}

	/**
	 * Optional settings shown in the template editor sidebar.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	protected static function use_case_settings_schema(): array {
		return array(
			'show_badge' => array(
				'type'    => 'boolean',
				'label'   => \__( 'Show starter badge', 'coverkit-usecase-starter' ),
				'help'    => \__(
					'Demo toggle for custom use case settings. Does not affect front-end output.',
					'coverkit-usecase-starter'
				),
				'control' => 'toggle',
				'default' => true,
			),
		);
	}

	/**
	 * Mapping sources available for this use case.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	protected static function use_case_mapping_sources(): array {
		return array(
			'post_title'   => array(
				'required' => true,
			),
			'post_excerpt' => array(
				'recommended' => true,
			),
			'site_logo'    => array(
				'recommended' => true,
			),
		);
	}

	/**
	 * No runtime hooks — editor and REST preview only.
	 */
	protected function init(): void {
	}
}

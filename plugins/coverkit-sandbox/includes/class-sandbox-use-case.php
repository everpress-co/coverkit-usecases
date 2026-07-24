<?php
/**
 * Sandbox use case — reference implementation for custom use case authors.
 *
 * Exposes every built-in setting control and common mapping sources for editor testing.
 * Not intended as a production output type (no front-end hooks).
 *
 * @package CoverKitSandbox
 */

declare(strict_types=1);

namespace CoverKitSandbox;

use CoverKit\Use_Case;

defined( 'ABSPATH' ) || exit;

/**
 * Safe place to test templates, mappings, and live editor previews without affecting the public site.
 */
class Sandbox_Use_Case extends Use_Case {

	/**
	 * Recommended dimensions, fixed square output (crop), and formats for sandbox preview output.
	 *
	 * @return array<string, mixed>
	 */
	protected static function recommended_settings(): array {
		return array(
			'dimensions' => array(
				'width'  => 300,
				'height' => 300,
			),
			'crop'       => true,
			'formats'    => array( 'jpg', 'webp' ),
		);
	}

	/**
	 * Settings schema fields for the sandbox use case (all built-in control types).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	protected static function use_case_settings_schema(): array {
		return array(
			'alt_text'           => array(
				'type'     => 'string',
				'help'     => \__( 'The alt text of the image.', 'coverkit-sandbox' ),
				'label'    => \__( 'Alt text', 'coverkit-sandbox' ),
				'control'  => 'text',
				'default'  => '',
				'override' => true,
			),
			'caption'            => array(
				'type'     => 'string',
				'label'    => \__( 'Caption', 'coverkit-sandbox' ),
				'control'  => 'textarea',
				'default'  => '',
				'override' => true,
			),
			'show_border'        => array(
				'type'     => 'boolean',
				'help'     => \__( 'Whether to show a border around the image.', 'coverkit-sandbox' ),
				'label'    => \__( 'Show border', 'coverkit-sandbox' ),
				'control'  => 'toggle',
				'default'  => false,
				'override' => true,
			),
			'include_metadata'   => array(
				'type'     => 'boolean',
				'help'     => \__( 'Whether to include metadata in the sandbox preview.', 'coverkit-sandbox' ),
				'label'    => \__( 'Include metadata', 'coverkit-sandbox' ),
				'control'  => 'checkbox',
				'default'  => true,
				'override' => true,
			),
			'apply_front_page'   => array(
				'type'    => 'boolean',
				'help'    => \__(
					'When enabled, this template may be used on the front page (including the posts index). This is separate from the post type list above.',
					'coverkit-sandbox'
				),
				'label'   => \__( 'Front page', 'coverkit-sandbox' ),
				'control' => 'toggle',
				'default' => false,
			),
			'apply_non_singular' => array(
				'type'    => 'boolean',
				'help'    => \__(
					'When enabled, this template may be used on archives and search results (not singular, not the front page).',
					'coverkit-sandbox'
				),
				'label'   => \__( 'Archives and search', 'coverkit-sandbox' ),
				'control' => 'toggle',
				'default' => false,
			),
		);
	}

	/**
	 * Overrides for the shared `post_type` settings field (checkbox list of public post types).
	 *
	 * @return array<string, mixed> Keys merged onto the default `post_type` schema (e.g. `help`, `default`).
	 */
	protected static function post_type_settings_schema_delta(): array {
		return array(
			'help'    => \__(
				'Select the post types to include in the sandbox preview.',
				'coverkit-sandbox'
			),
			'default' => array( 'post' ),
		);
	}

	/**
	 * Delta merged onto {@see Use_Case::default_mapping_sources()} (same id: keys in this array win).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	protected static function use_case_mapping_sources(): array {
		return array(
			'post_title'     => array(
				'required' => true,
			),
			'featured_image' => array(
				'recommended' => true,
			),
			'post_link'      => array(
				'recommended' => true,
			),
			'post_excerpt'   => array(
				'recommended' => true,
			),
			'author'         => array(
				'recommended' => true,
			),
			'site_logo'      => array(
				'recommended' => true,
			),
		);
	}

	/**
	 * Use-case-specific hooks. Shared registration runs in {@see Use_Case::maybe_init()}.
	 */
	protected function init(): void {
	}

	/**
	 * Reference implementation for per-use-case field formatting (sandbox demo).
	 *
	 * @param mixed                $formatted  Formatted value after built-in formatters.
	 * @param string               $source_key Mapping source key.
	 * @param int|null             $post_id    Post ID used for resolution.
	 * @param array<string, mixed> $source_def Mapping source definition.
	 * @param string               $formatter      Formatter id.
	 * @param string               $use_case_class Use case class name.
	 * @return mixed
	 */
	public function filter_format_field_value( $formatted, string $source_key, ?int $post_id, array $source_def, string $formatter, string $use_case_class ) {
		unset( $post_id, $source_def, $formatter, $use_case_class );

		if ( 'post_date' !== $source_key ) {
			return $formatted;
		}

		if ( ! is_string( $formatted ) || '' === $formatted ) {
			return $formatted;
		}

		return '— ' . $formatted;
	}
}

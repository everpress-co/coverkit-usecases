<?php
/**
 * Dashboard widget use case — wp-admin widget with generated image background.
 *
 * @package CoverKitUseCaseDashboardWidget
 */

declare(strict_types=1);

namespace CoverKitUseCaseDashboardWidget;

use CoverKit\Use_Case;
use CoverKit\Use_Case_Storage;

defined( 'ABSPATH' ) || exit;

/**
 * Site-wide dashboard branding via a CoverKit image background.
 */
class Dashboard_Widget_Use_Case extends Use_Case {

	/**
	 * Dashboard widget ID.
	 */
	private const WIDGET_ID = 'coverkit_dashboard_widget';

	/**
	 * Cached active template ID for the current request.
	 *
	 * @var int|null
	 */
	private static ?int $resolved_template_id = null;

	/**
	 * Whether template resolution has run this request.
	 *
	 * @var bool
	 */
	private static bool $resolution_seeded = false;

	/**
	 * Assignment cardinality for this use case.
	 *
	 * @return string
	 */
	public static function intrinsic_cardinality(): string {
		return 'install_single';
	}

	/**
	 * Dashboard branding is not scoped to post types.
	 *
	 * @return bool
	 */
	protected static function include_post_type_in_settings_schema(): bool {
		return false;
	}

	/**
	 * Wide banner dimensions for dashboard metabox backgrounds.
	 *
	 * @return array<string, mixed>
	 */
	protected static function recommended_settings(): array {
		return array(
			'dimensions' => array(
				'width'  => 1200,
				'height' => 400,
			),
			'crop'       => true,
			'formats'    => array( 'jpg', 'webp' ),
		);
	}

	/**
	 * Editor settings for the dashboard widget container.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	protected static function use_case_settings_schema(): array {
		return array(
			'widget_title' => array(
				'type'    => 'string',
				'label'   => \__( 'Widget title', 'coverkit-usecase-dashboard-widget' ),
				'help'    => \__(
					'Optional title shown inside the widget. Leave empty to use the site title.',
					'coverkit-usecase-dashboard-widget'
				),
				'control' => 'text',
				'default' => '',
			),
			'min_height'   => array(
				'type'    => 'integer',
				'label'   => \__( 'Minimum height', 'coverkit-usecase-dashboard-widget' ),
				'help'    => \__(
					'Minimum height of the background area in pixels.',
					'coverkit-usecase-dashboard-widget'
				),
				'control' => 'range',
				'default' => 200,
				'minimum' => 120,
				'maximum' => 600,
			),
		);
	}

	/**
	 * Site-level field mappings for preview and dashboard output.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	protected static function use_case_mapping_sources(): array {
		return array(
			'site_name'        => array(
				'required' => true,
			),
			'site_description' => array(
				'recommended' => true,
			),
			'site_logo'        => array(
				'recommended' => true,
			),
		);
	}

	/**
	 * Register the wp-admin dashboard widget.
	 */
	protected function init(): void {
		\add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_styles' ) );
	}

	/**
	 * Add the CoverKit dashboard widget when a template assignment is active.
	 */
	public function register_dashboard_widget(): void {
		$template_id = $this->resolve_active_template_id();
		if ( null === $template_id ) {
			return;
		}

		$assignment = Use_Case_Storage::get_assignment( $template_id, static::get_slug() );
		$settings   = isset( $assignment['settings'] ) && is_array( $assignment['settings'] )
			? $assignment['settings']
			: array();

		$widget_title = isset( $settings['widget_title'] ) && is_string( $settings['widget_title'] )
			? \trim( $settings['widget_title'] )
			: '';

		if ( '' === $widget_title ) {
			$widget_title = (string) \get_bloginfo( 'name' );
		}

		if ( '' === $widget_title ) {
			$widget_title = \__( 'CoverKit', 'coverkit-usecase-dashboard-widget' );
		}

		\wp_add_dashboard_widget(
			self::WIDGET_ID,
			$widget_title,
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Output the widget content with a generated image background.
	 */
	public function render_dashboard_widget(): void {
		$template_id = $this->resolve_active_template_id();
		if ( null === $template_id ) {
			return;
		}

		$assignment = Use_Case_Storage::get_assignment( $template_id, static::get_slug() );
		$settings   = isset( $assignment['settings'] ) && is_array( $assignment['settings'] )
			? $assignment['settings']
			: array();

		$format = isset( $settings['format'] ) && is_string( $settings['format'] ) && '' !== $settings['format']
			? $settings['format']
			: 'jpg';

		$min_height = isset( $settings['min_height'] ) ? (int) $settings['min_height'] : 200;
		$min_height = max( 120, min( 600, $min_height ) );

		$image_url = \CoverKit\coverkit_rest_use_case_image_url(
			static::get_slug(),
			$template_id,
			0,
			$format,
			null,
			true
		);

		if ( '' === $image_url ) {
			return;
		}

		$widget_title = isset( $settings['widget_title'] ) && is_string( $settings['widget_title'] )
			? \trim( $settings['widget_title'] )
			: '';

		$style = \sprintf(
			'min-height:%dpx;background-image:url(%s);',
			$min_height,
			\esc_url( $image_url )
		);

		echo '<div class="coverkit-dashboard-widget" style="' . \esc_attr( $style ) . '">';

		if ( '' !== $widget_title ) {
			echo '<span class="coverkit-dashboard-widget__title">' . \esc_html( $widget_title ) . '</span>';
		}

		echo '</div>';
	}

	/**
	 * Scoped dashboard styles for the widget background container.
	 *
	 * @param string $hook_suffix Current admin screen hook suffix.
	 */
	public function enqueue_dashboard_styles( string $hook_suffix ): void {
		if ( 'index.php' !== $hook_suffix ) {
			return;
		}

		if ( null === $this->resolve_active_template_id() ) {
			return;
		}

		$css = '
			#coverkit_dashboard_widget .inside {
				margin: 0;
				padding: 0;
			}
			#coverkit_dashboard_widget .coverkit-dashboard-widget {
				background-size: cover;
				background-position: center;
				background-repeat: no-repeat;
				border-radius: 4px;
				overflow: hidden;
				display: flex;
				align-items: flex-end;
				padding: 16px;
				box-sizing: border-box;
			}
			#coverkit_dashboard_widget .coverkit-dashboard-widget__title {
				color: #fff;
				font-size: 1.25em;
				font-weight: 600;
				line-height: 1.3;
				text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
			}
		';

		\wp_register_style( 'coverkit-dashboard-widget', false, array(), COVERKIT_USECASE_DASHBOARD_WIDGET_VERSION );
		\wp_enqueue_style( 'coverkit-dashboard-widget' );
		\wp_add_inline_style( 'coverkit-dashboard-widget', $css );
	}

	/**
	 * Find the first published template with an active dashboard_widget assignment.
	 */
	private function resolve_active_template_id(): ?int {
		if ( self::$resolution_seeded ) {
			return self::$resolved_template_id;
		}

		self::$resolution_seeded    = true;
		self::$resolved_template_id = $this->scan_first_active_template();

		return self::$resolved_template_id;
	}

	/**
	 * Scan published templates in ascending ID order.
	 */
	private function scan_first_active_template(): ?int {
		$template_ids = \get_posts(
			array(
				'post_type'      => 'coverkit',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		if ( ! is_array( $template_ids ) ) {
			return null;
		}

		foreach ( $template_ids as $template_id ) {
			$template_id = (int) $template_id;
			if ( $template_id <= 0 ) {
				continue;
			}

			if ( Use_Case_Storage::is_assignment_active( $template_id, static::get_slug() ) ) {
				return $template_id;
			}
		}

		return null;
	}
}

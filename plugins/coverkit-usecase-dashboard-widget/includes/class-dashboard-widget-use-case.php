<?php
/**
 * Dashboard widget use case — wp-admin widget with generated image background.
 *
 * @package CoverKitUseCaseDashboardWidget
 */

declare(strict_types=1);

namespace CoverKitUseCaseDashboardWidget;

use CoverKit\Use_Case;

defined( 'ABSPATH' ) || exit;

/**
 * Site-wide dashboard branding via a CoverKit image background.
 */
class Dashboard_Widget_Use_Case extends Use_Case {

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

			'crop'    => true,
			'formats' => array( 'jpg', 'webp' ),
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
	 * Add one CoverKit dashboard widget per active template assignment.
	 */
	public function register_dashboard_widget(): void {
		foreach ( static::find_active_template_ids() as $template_id ) {
			$widget_id    = self::widget_id_for_template( $template_id );
			$widget_title = $this->resolve_widget_title( $template_id );

			\wp_add_dashboard_widget(
				$widget_id,
				$widget_title,
				function () use ( $template_id ): void {
					$this->render_dashboard_widget( $template_id );
				}
			);
		}
	}

	/**
	 * Output the widget content with a generated image background.
	 *
	 * @param int $template_id CoverKit template post ID.
	 */
	public function render_dashboard_widget( int $template_id ): void {
		if ( $template_id <= 0 ) {
			return;
		}

		$min_height = (int) static::get_setting( $template_id, 'min_height' );

		$image_url = static::get_image_url( $template_id );

		if ( '' === $image_url ) {
			return;
		}

		$widget_title = \trim( (string) static::get_setting( $template_id, 'widget_title', '' ) );

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

		if ( array() === static::find_active_template_ids() ) {
			return;
		}

		$css = '
			.postbox[id^="coverkit_dashboard_widget_"] .inside {
				margin: 0;
				padding: 0;
			}
			.coverkit-dashboard-widget {
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
			.coverkit-dashboard-widget__title {
				color: #fff;
				font-size: 1.25em;
				font-weight: 600;
				line-height: 1.3;
				text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
			}
		';

		\wp_register_style( 'coverkit-dashboard-widget', false, array(), \md5( $css ) );
		\wp_enqueue_style( 'coverkit-dashboard-widget' );
		\wp_add_inline_style( 'coverkit-dashboard-widget', $css );
	}

	/**
	 * Unique dashboard widget ID for a template assignment.
	 *
	 * @param int $template_id CoverKit template post ID.
	 */
	private static function widget_id_for_template( int $template_id ): string {
		return 'coverkit_dashboard_widget_' . $template_id;
	}

	/**
	 * Widget metabox title for a template assignment.
	 *
	 * @param int $template_id CoverKit template post ID.
	 */
	private function resolve_widget_title( int $template_id ): string {
		$widget_title = \trim( (string) static::get_setting( $template_id, 'widget_title', '' ) );

		if ( '' === $widget_title ) {
			$widget_title = (string) \get_bloginfo( 'name' );
		}

		if ( '' === $widget_title ) {
			$widget_title = \__( 'CoverKit', 'coverkit-usecase-dashboard-widget' );
		}

		return $widget_title;
	}
}

<?php
/**
 * Include shortcodes.
 *
 * @package    WP Business Hours
 * @since      1.0.0
 * @author     CLEANCODED
 * @copyright  Copyright (c) 2017, CLEANCODED
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Business Hours Widget Shortcodes.
 */
class WP_BUSINESS_HOURS_Widget_Shortcodes {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'wp-business-hours', array( &$this, 'widget_shortcode' ) );
		add_shortcode( 'wp-business-hours-ajax', array( &$this, 'widget_shortcode_ajax' ) );
	}

	/**
	 * Add main shortcode.
	 */
	public function widget_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => false,
		), $atts, 'wp-business-hours' );

		$options   = get_option( 'widget_WP_BUSINESS_HOURS' );
		$widget_id = $atts['id'];
		$instance  = $options[ $widget_id ];

		$args = wp_parse_args( (array) $instance, wpbh_default_args() );

		return wpbh_ajax_output( $args );
	}

	/**
	 * Shortcode when ajax is enabled
	 *
	 * @param $atts
	 *
	 * @return string
	 * @since 1.6.0
	 */
	public function widget_shortcode_ajax( $atts ) {
		$atts = shortcode_atts( array(
			'id' => false,
		), $atts, 'wp-business-hours-ajax' );

		$options   = get_option( 'widget_WP_BUSINESS_HOURS' );
		$widget_id = $atts['id'];
		$instance  = $options[ $widget_id ];

		$args = wp_parse_args( (array) $instance, wpbh_default_args() );

		return wpbh_output( $args );
	}
}

new WP_BUSINESS_HOURS_Widget_Shortcodes();
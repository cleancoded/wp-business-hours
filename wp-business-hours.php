<?php
/**
 * Plugin Name: WP Business Hours
 * Plugin URI: https://cleancoded.com
 * Description: Easily display your business hours on your WordPress website.
 * Version: 1.6.1
 * Author: CLEANCODED
 * Text Domain: wp-business-hours
 * Domain Path: /languages
 * Author URI: https://cleancoded.com
 * License: GNU GENERAL PUBLIC LICENSE V3
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Business_Hours {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Set the constants.
		add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );
		// Load the necessary files.
		add_action( 'plugins_loaded', array( $this, 'includes' ), 4 );
		// Internationalize the text strings
		add_action( 'plugins_loaded', array( $this, 'languages' ), 2 );
		// Loads the admin styles and scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		// Load the front end styles and scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
		// Register the widget.
		add_action( 'widgets_init', array( $this, 'widget' ) );
		// Add documentation & widget links
		add_filter( 'plugin_action_links', array( $this, 'add_links' ), 10, 5 );

	}

	/**
	 * Set constants
	 *
	 * @since 1.6.0
	 */
	public function constants() {
		define( 'WP_BUSINESS_HOURS_VERSION', '1.6.1' );
		define( 'WP_BUSINESS_HOURS_DIR', plugin_dir_path( __FILE__ ) );
		define( 'WP_BUSINESS_HOURS_URL', plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Include necessary files
	 *
	 * @since 1.6.0
	 */
	public function includes() {
		require_once WP_BUSINESS_HOURS_DIR . 'includes/shortcodes.php';
		require_once WP_BUSINESS_HOURS_DIR . 'includes/functions.php';
		require_once WP_BUSINESS_HOURS_DIR . 'includes/widget.php';
	}

	/**
	 * Internationalize the text
	 *
	 * @since 1.6.0
	 */
	public function languages() {
		load_plugin_textdomain( 'wp-business-hours', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Load custom styles and js in the admin area
	 *
	 * @since 1.6.0
	 */
	public function admin_scripts() {
		wp_register_style( 'WP_BUSINESS_HOURS_admin_style', plugins_url( '/css/wpbh-admin.css', __FILE__ ) );
		wp_enqueue_style( 'WP_BUSINESS_HOURS_admin_style' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-business-hours-color-picker', plugins_url( '/js/color-picker.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
		wp_enqueue_media();
		wp_enqueue_script( 'wp-business-hours-media-upload', plugins_url( '/js/media-upload.js', __FILE__ ) );
		wp_enqueue_script( 'wp-business-hours-admin-js', plugins_url( '/js/wpbh-admin.js', __FILE__ ) );
	}

	/**
	 * Load the main style for frontend
	 *
	 * @since 1.6.0
	 */
	public function front_scripts() {
		wp_register_style( 'wp-business-hours-main-style', plugins_url( '/css/wpbh-style.css', __FILE__ ) );
		wp_register_style( 'wp-business-hours-custom-style', plugins_url( '/css/wpbh-custom-style.css', __FILE__ ) );
		wp_enqueue_style( 'wp-business-hours-main-style' );
		wp_enqueue_style( 'wp-business-hours-custom-style' );
		// ajax scripts
		wp_enqueue_script( 'wpbh-ajax-script', plugins_url( '/js/wpbh-ajax.js', __FILE__ ), array( 'jquery' ) );
		wp_localize_script( 'wpbh-ajax-script', 'wpbh_ajax', array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'wpbh-ajax-nonce' ),
		) );
	}

	/**
	 * Register the widget
	 *
	 * @since 1.6.0
	 */
	public function widget() {
		register_widget( 'WP_BUSINESS_HOURS_Widget' );
	}

	/**
	 *
	 * Link to documentation in the admin area
	 *
	 * @param $links
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function add_links( $actions, $plugin_file ) {
		static $plugin;

		if ( ! isset( $plugin ) ) {
			$plugin = plugin_basename( __FILE__ );
		}
		if ( $plugin == $plugin_file ) {

			$settings  = array( 'widgets' => '<a href="' . esc_url( get_admin_url( null, 'widgets.php' ) ) . '">Widgets</a>' );
			$site_link = array( 'support' => '<a href="https://support.bestwebsite.com/" target="_blank">Help Center</a>' );

			$actions = array_merge( $settings, $actions );
			$actions = array_merge( $site_link, $actions );

		}

		return $actions;
	}

}

new WP_Business_Hours();

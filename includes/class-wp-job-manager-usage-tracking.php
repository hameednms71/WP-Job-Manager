<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require dirname( __FILE__ ) . '/../lib/usage-tracking/class-usage-tracking-base.php';

/**
 * WPJM Usage Tracking subclass.
 */
class WP_Job_Manager_Usage_Tracking extends WP_Job_Manager_Usage_Tracking_Base {

	const WPJM_SETTING_NAME = 'job_manager_usage_tracking_enabled';

	const WPJM_TRACKING_INFO_URL = 'https://wpjobmanager.com/document/what-data-does-wpjm-track';

	/**
	 * WP_Job_Manager_Usage_Tracking constructor.
	 */
	protected function __construct() {
		parent::__construct();

		// Add filter for settings.
		add_filter( 'job_manager_settings', array( $this, 'add_setting_field' ) );

		// In the setup wizard, do not display the normal opt-in dialog.
		if ( isset( $_GET['page'] ) && 'job-manager-setup' === $_GET['page'] ) {
			remove_action( 'admin_notices', array( $this, 'maybe_display_tracking_opt_in' ) );
		}
	}

	/**
	 * Implementation for abstract functions.
	 */

	/**
	 * Return the instance of this class.
	 *
	 * @return object
	 */
	public static function get_instance() {
		return self::get_instance_for_subclass( get_class() );
	}

	/**
	 * Get prefix for the usage data setting.
	 *
	 * @return string
	 */
	protected function get_prefix() {
		return 'job_manager';
	}

	/**
	 * Get prefix for the event sent for usage tracking.
	 *
	 * @return string
	 */
	protected function get_event_prefix() {
		return 'wpjm';
	}

	/**
	 * Get the text domain used in the plugin.
	 *
	 * @return string
	 */
	protected function get_text_domain() {
		return 'wp-job-manager';
	}

	/**
	 * Get the status of usage tracking.
	 *
	 * @return bool
	 */
	public function get_tracking_enabled() {
		return get_option( self::WPJM_SETTING_NAME ) || false;
	}

	/**
	 * Set whether or not usage tracking is enabled.
	 *
	 * @param bool $enable
	 */
	public function set_tracking_enabled( $enable ) {
		update_option( self::WPJM_SETTING_NAME, $enable );
	}

	/**
	 * Check if the current user can manage usage tracking settings.
	 *
	 * @return bool
	 */
	protected function current_user_can_manage_tracking() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get the text to show in the opt-in dialog.
	 *
	 * @return string
	 */
	protected function opt_in_dialog_text() {
		return sprintf(
			__(
				"We'd love if you helped us make WP Job Manager better by allowing us to collect " .
				"<a href=\"%s\" target=\"_blank\">usage tracking data</a>. " .
				"No sensitive information is collected, and you can opt out at any time.",
				'wp-job-manager'
			), self::WPJM_TRACKING_INFO_URL
		);
	}

	/**
	 * Check if we should track the status of a plugin.
	 *
	 * @param string $plugin_slug
	 * @return bool
	 */
	protected function do_track_plugin( $plugin_slug ) {
		if ( 1 === preg_match( '/^wp-job-manager/', $plugin_slug ) ) {
			return true;
		}
		$third_party_plugins = array(
			'all-in-one-seo-pack',
			'polylang',
			'jetpack',
			'wordpress-seo', // Yoast.
			'sitepress-multilingual-cms', // WPML.
			'bibblio-related-posts', // Related Posts for WordPress.
		);
		if ( in_array( $plugin_slug, $third_party_plugins, true ) ) {
			return true;
		}
		return false;
	}


	/*
	 * Public functions.
	 */

	/**
	 * @inheritdoc
	 */
	public function hide_tracking_opt_in() {
		parent::hide_tracking_opt_in();
	}

	/**
	 * @inheritdoc
	 */
	public function opt_in_dialog_text_allowed_html() {
		return parent::opt_in_dialog_text_allowed_html();
	}

	/**
	 * Get the opt-in text.
	 *
	 * @return string
	 */
	public function opt_in_checkbox_text() {
		return sprintf(

			/*
			 * translators: the href tag contains the URL for the page
			 * telling users what data WPJM tracks.
			 */
			__(
				'Help us make WP Job Manager better by allowing us to collect
				<a href="%s" target="_blank">usage tracking data</a>.
				No sensitive information is collected.', 'wp-job-manager'
			), self::WPJM_TRACKING_INFO_URL
		);
	}


	/**
	 * Hooks.
	 */

	/**
	 * Add tracking setting field to general settings.
	 *
	 * @param array $fields
	 * @return array
	 */
	public function add_setting_field( $fields ) {
		$fields['general'][1][] = array(
			'name'     => self::WPJM_SETTING_NAME,
			'std'      => '0',
			'type'     => 'checkbox',
			'desc'     => '',
			'label'    => __( 'Enable Usage Tracking', 'wp-job-manager' ),
			'cb_label' => $this->opt_in_checkbox_text(),
		);

		return $fields;
	}


	/**
	 * Helpers.
	 */

	/**
	 * Clear options used for usage tracking.
	 */
	public function clear_options() {
		delete_option( self::WPJM_SETTING_NAME );
		delete_option( $this->hide_tracking_opt_in_option_name );
	}
}

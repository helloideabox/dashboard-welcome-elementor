<?php
/**
 * Plugin Name: Dashboard Welcome for Elementor
 * Plugin URI: https://powerpackelements.com/dashboard-welcome-elementor/
 * Description: Replaces the default WordPress dashboard welcome panel with a Elementor template.
 * Version: 1.0.10
 * Author: IdeaBox Creations
 * Author URI: https://ideaboxcreations.com
 * Copyright: (c) 2018 IdeaBox Creations
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dashboard-welcome-for-elementor
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DWEL_VER', '1.0.10' );
define( 'DWEL_DIR', plugin_dir_path( __FILE__ ) );
define( 'DWEL_URL', plugins_url( '/', __FILE__ ) );
define( 'DWEL_PATH', plugin_basename( __FILE__ ) );
define( 'DWEL_FILE', __FILE__ );

final class Dashboard_Welcome_Elementor_Plugin {
	/**
	 * Holds the current class object.
	 * 
	 * @since 1.0.0
	 * @var object
	 */
	public static $instance;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'loader' ) );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function loader() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'plugin_load_fail' ) );
			return;
		}

		require_once DWEL_DIR . 'classes/class-dwel-admin.php';

		$dwe_admin = Dashboard_Welcome_Elementor_Plugin\Admin::get_instance();
	}

	/**
	 * Check Elementor plugin and renders notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function plugin_load_fail() {
		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		$plugin = 'elementor/elementor.php';

		if ( function_exists( '_is_elementor_installed' ) && _is_elementor_installed() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			$activation_url = wp_nonce_url( 'plugins.php?action=activate&plugin=' . $plugin . '&plugin_status=all&paged=1&s', 'activate-plugin_' . $plugin );

			$message  = '<p>' . esc_html__( 'Dashboard Welcome is not working because you need to activate the Elementor plugin.', 'dashboard-welcome-for-elementor' ) . '</p>';
			$message .= sprintf(
				'<p><a href="%s" class="button-primary">%s</a></p>',
				esc_url( $activation_url ),
				esc_html__( 'Activate Elementor Now', 'dashboard-welcome-for-elementor' )
			);
		} else {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}

			$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );

			$message  = '<p>' . esc_html__( 'Dashboard Welcome is not working because you need to install the Elementor plugin.', 'dashboard-welcome-for-elementor' ) . '</p>';
			$message .= sprintf(
				'<p><a href="%s" class="button-primary">%s</a></p>',
				esc_url( $install_url ),
				esc_html__( 'Install Elementor Now', 'dashboard-welcome-for-elementor' )
			);
		}

		echo '<div class="notice notice-error">' . wp_kses_post( $message ) . '</div>';
	}

	/**
	 * Get the instance of the class.
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Dashboard_Welcome_Elementor_Plugin ) ) {
			self::$instance = new Dashboard_Welcome_Elementor_Plugin();
		}

		return self::$instance;
	}
}

// Initialize the class.
$dwel_plugin = Dashboard_Welcome_Elementor_Plugin::get_instance();

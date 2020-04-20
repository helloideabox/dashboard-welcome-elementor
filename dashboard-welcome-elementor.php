<?php
/**
 * Plugin Name: Dashboard Welcome for Elementor
 * Plugin URI: https://powerpackelements.com/dashboard-welcome-elementor/
 * Description: Replaces the default WordPress dashboard welcome panel with a Elementor template.
 * Version: 1.0.5
 * Author: IdeaBox Creations
 * Author URI: https://ideaboxcreations.com
 * Copyright: (c) 2018 IdeaBox Creations
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ibx-dwe
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IBX_DWE_VER', '1.0.5' );
define( 'IBX_DWE_DIR', plugin_dir_path( __FILE__ ) );
define( 'IBX_DWE_URL', plugins_url( '/', __FILE__ ) );
define( 'IBX_DWE_PATH', plugin_basename( __FILE__ ) );
define( 'IBX_DWE_FILE', __FILE__ );

final class DWE_Plugin {
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
	public function __construct()
	{
		add_action( 'plugins_loaded', array( $this, 'loader' ) );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function loader()
	{
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'plugin_load_fail' ) );
			return;
		}

		require_once IBX_DWE_DIR . 'classes/class-dwe-admin.php';

		$dwe_admin = DWE_Plugin\Admin::get_instance();
	}

	/**
	 * Check Elementor plugin and renders notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function plugin_load_fail()
	{
		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		$plugin = 'elementor/elementor.php';

		if ( function_exists( '_is_elementor_installed' ) && _is_elementor_installed() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );

			$message = '<p>' . __( 'Dashboard Welcome is not working because you need to activate the Elementor plugin.', 'ibx-dwe' ) . '</p>';
			$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate Elementor Now', 'ibx-dwe' ) ) . '</p>';
		} else {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}

			$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );

			$message = '<p>' . __( 'Dashboard Welcome is not working because you need to install the Elementor plugin', 'ibx-dwe' ) . '</p>';
			$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install Elementor Now', 'ibx-dwe' ) ) . '</p>';
		}

		echo '<div class="error"><p>' . $message . '</p></div>';
	}

	/**
	 * Get the instance of the class.
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DWE_Plugin ) ) {
			self::$instance = new DWE_Plugin();
		}

		return self::$instance;
	}
}

// Initialize the class.
$dwe_plugin = DWE_Plugin::get_instance();

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_dwe() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
		require_once IBX_DWE_DIR . 'includes/appsero/src/Client.php';
    }

    $client = new Appsero\Client( 'a1e91aa7-93ee-4e13-970b-e42cdebdb6ad', 'Dashboard Welcome for Elementor', __FILE__ );

    // Active insights
    $client->insights()->init();

}

appsero_init_tracker_dwe();
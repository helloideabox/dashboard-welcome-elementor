<?php
/**
 * Plugin Name: Dashboard Welcome for Elementor
 * Plugin URI: https://powerpackelements.com
 * Description: A set of custom, creative, unique modules for Beaver Builder to speed up your web design and development process.
 * Version: 1.0.0
 * Author: Team IdeaBox - PowerPack Elements
 * Author URI: https://ideaboxcreations.com
 * Copyright: (c) 2016 IdeaBox Creations
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ibx-dwe
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IBX_DWE_VER', '1.0.0' );
define( 'IBX_DWE_DIR', plugin_dir_path( __FILE__ ) );
define( 'IBX_DWE_URL', plugins_url( '/', __FILE__ ) );
define( 'IBX_DWE_PATH', plugin_basename( __FILE__ ) );
define( 'IBX_DWE_FILE', __FILE__ );

final class DWE_Plugin {
	public static $instance;

	public function __construct()
	{
		require_once IBX_DWE_DIR . 'classes/class-dwe-admin.php';
	}

	public static function get_instance()
	{
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DWE_Plugin ) ) {
			self::$instance = new DWE_Plugin();
		}

		return self::$instance;
	}
}

$dwe_plugin = DWE_Plugin::get_instance();
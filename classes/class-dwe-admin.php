<?php
namespace DWE_Plugin;

use Elementor;
use Elementor\Core\Files\CSS\Post as Post_CSS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DWE_Admin {
	public static $instance;

	public $settings_page;

	public $settings_title;

	public $settings;

	/**
     * Holds the CSS classes.
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
	public $classes;

	/**
     * Holds the user roles.
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    public $roles;

    /**
     * Holds the current user role.
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    public $current_role;

	/**
	 * Initializes the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct()
	{
		if ( ! is_admin() ) {
			return;
		}

		$this->settings_page = 'dwe-settings';
		$this->settings_title = __('Dashboard Welcome', 'ibx-dwe');
		$this->settings = $this->get_settings();

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'plugins_loaded', array( $this, 'init_hooks' ) );
	}

	public function init_hooks()
	{
		if ( ! is_admin() ) {
			return;
		}
		
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 1000 );
	}

	public function admin_init()
	{
		$this->update_settings();

		global $pagenow;

        if ( 'index.php' != $pagenow ) {
            return;
		}

		$settings 	= $this->settings;
		$role		= $this->current_role;

		if ( ! empty( $settings ) && isset( $settings[ $role ] ) ) {
			if ( isset( $settings[ $role ]['template'] ) && ! empty( $settings[ $role ]['template'] ) ) {
				remove_action( 'welcome_panel', 'wp_welcome_panel' );
				add_action( 'welcome_panel', array( $this, 'welcome_panel' ) );
				//add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}
		}
		
		// custom fallback for the users who don't have
		// enough capabilities to display welcome panel.
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			$this->classes = 'welcome-panel';
			add_action( 'admin_notices', array( $this, 'welcome_panel' ) );
		}
	}

	public function enqueue_scripts()
	{
		$screen = get_current_screen();
		if ( $screen->base == 'dashboard' ) {
			$settings 	= $this->settings;
			$role		= $this->current_role;

			if ( ! empty( $settings ) && isset( $settings[ $role ] ) ) {
				if ( isset( $settings[ $role ]['template'] ) ) {
					$template_id = $settings[ $role ]['template'];
					//wp_enqueue_style( 'dwe-panel', esc_url( site_url() . '/wp-content/uploads/elementor/css/post-' . $template_id . '.css', false, IBX_DWE_VER, 'all' ) );
					wp_enqueue_style( 'dwe-panel', IBX_DWE_URL . 'assets/css/dashboard.css', false, IBX_DWE_VER );
				}
			}
		}
	}

	public function welcome_panel()
	{
		include IBX_DWE_DIR . 'includes/welcome-panel.php';
	}

	public function admin_menu()
	{	
		global $wp_roles;

		$this->roles 		= $wp_roles->get_names();
		$this->current_role = $this->get_user_role();

		if ( current_user_can( 'manage_options' ) ) {

			$title = $this->settings_title;
			$cap   = 'manage_options';
			$slug  = $this->settings_page;
			$func  = array( $this, 'render_settings' );

			add_submenu_page( 'elementor', $title, $title, $cap, $slug, $func );
		}
	}

	public function render_settings()
	{
		$title 			= $this->settings_title;
		$form_action 	= $this->get_form_action();
		$roles			= $this->roles;
		$current_role	= $this->current_role;
		$templates		= $this->get_templates();
		$settings		= $this->get_settings();

		include IBX_DWE_DIR . 'includes/admin-settings.php';
	}

	public function render_template()
	{
		$settings 	= $this->settings;
		$role		= $this->current_role;

		if ( ! empty( $settings ) && isset( $settings[ $role ] ) ) {
			if ( isset( $settings[ $role ]['template'] ) && ! empty( $settings[ $role ]['template'] ) ) {
				$template_id = $settings[ $role ]['template'];
				$dismissible = isset( $settings[ $role ]['dismissible'] ) ? true : false;

				echo '<style>';
				$css = file_get_contents( IBX_DWE_DIR . 'assets/css/dashboard.css' );

				if ( ! $dismissible ) {
					$css .= '.welcome-panel .welcome-panel-close { display: none; }';
				}

				$css = str_replace( array("\r\n", "\n", "\r\t", "\t", "\r"), '', $css );
				$css = preg_replace('/\s+/', ' ', $css);

				echo $css;
				echo '</style>';

				$elementor = Elementor\Plugin::$instance;
				echo $elementor->frontend->get_builder_content( $template_id, true );
			}
		}
	}

	private function get_form_action()
	{
		return admin_url( '/admin.php?page=' . $this->settings_page );
	}

	private function get_templates()
	{
		$templates = get_posts( array(
            'post_type'         => 'elementor_library',
			'posts_per_page'    => '-1',
			'post_status'		=> 'publish'
		) );
		
		$options = array();

        if ( ! empty( $templates ) && ! is_wp_error( $templates ) ){
            foreach ( $templates as $post ) {
                $options[ $post->ID ] = $post->post_title;
            }
		}
		
        return $options;
	}

	private function get_user_role()
	{
		// Get current user role in multisite network using WP_User_Query.
        if ( is_multisite() ) {
			$user_query = new WP_User_Query( array( 'blog_id' => 1 , 'include' => array( get_current_user_id() ) ) );
			
            if ( ! empty( $user_query->results ) ) {
				$roles = $user_query->results[0]->roles;
				
                if ( is_array( $roles ) && count( $roles ) ) {
                    return $roles[0];
                }
            }
        }

        $user   = wp_get_current_user();
        $roles  = $user->roles;
        $roles  = array_shift( $roles );

        return $roles;
	}

	public function get_settings()
	{
		$settings = get_option( '_dwe_templates', array() );
		$this->settings = $settings;

		return $settings;
	}

	public function update_settings()
	{
		if ( ! isset( $_POST['dwe_settings_nonce'] ) || ! wp_verify_nonce( $_POST['dwe_settings_nonce'], 'dwe_settings' ) ) {
			return;
		}

		if ( ! isset( $_POST['dwe_templates'] ) ) {
			return;
		}

		update_option( '_dwe_templates', $_POST['dwe_templates'] );
	}

	public static function get_instance()
	{
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DWE_Admin ) ) {
			self::$instance = new DWE_Admin();
		}

		return self::$instance;
	}
}

$dwe_admin = DWE_Admin::get_instance();
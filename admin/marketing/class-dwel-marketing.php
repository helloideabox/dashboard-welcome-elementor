<?php
/**
 * DynamicKit marketing notice inside the Elementor form-fields repeater.
 *
 * @package Dashboard_Welcome_Elementor_Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'DWEL_Marketing_Controllers' ) ) {

	class DWEL_Marketing_Controllers {

		const PLUGIN_SLUG = 'dynamickit-elementor';
		const PLUGIN_FILE = 'dynamickit-elementor/dynamickit-elementor.php';
		const DISMISS_OPT = 'dwel_dynamickit_marketing_dismissed';

		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( self::is_dynamickit_active() ) {
				return;
			}

			$active_plugins = (array) get_option( 'active_plugins', array() );

			$form_hosts = array(
				'elementor-pro/elementor-pro.php',
				'pro-elements/pro-elements.php',
				'hello-plus/hello-plus.php',
			);

			if ( ! array_intersect( $form_hosts, $active_plugins ) ) {
				return;
			}

			add_action( 'elementor/init', array( $this, 'init_hooks' ) );

			if ( ! get_option( self::DISMISS_OPT, false ) ) {
				add_action( 'elementor/element/form/section_form_fields/before_section_end', array( $this, 'add_marketing_control' ), 100, 2 );
			}

			add_action( 'wp_ajax_dwel_install_dynamickit', array( $this, 'ajax_install_plugin' ) );
			add_action( 'wp_ajax_dwel_dismiss_dynamickit_notice', array( $this, 'ajax_dismiss_notice' ) );
		}

		public static function is_dynamickit_active() {
			if ( is_plugin_active( self::PLUGIN_FILE ) ) {
				return true;
			}

			$all_plugins = get_plugins();
			return isset( $all_plugins[ self::PLUGIN_FILE ] ) && is_plugin_active( self::PLUGIN_FILE );
		}

		public function init_hooks() {
			add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ), 0 );
			add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );
		}

		public function enqueue_editor_scripts() {
			wp_enqueue_script(
				'dwel-form-marketing',
				DWEL_URL . 'admin/marketing/js/dwel-form-marketing.js',
				array( 'jquery' ),
				DWEL_VER,
				true
			);

			wp_localize_script(
				'dwel-form-marketing',
				'dwelMarketing',
				array(
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
					'install_nonce' => wp_create_nonce( 'dwel_install_nonce' ),
					'dismiss_nonce' => wp_create_nonce( 'dwel_dismiss_nonce' ),
				)
			);
		}

		public function enqueue_editor_styles() {
			wp_enqueue_style(
				'dwel-form-marketing',
				DWEL_URL . 'admin/marketing/css/dwel-mkt.css',
				array(),
				DWEL_VER
			);
		}

		public function add_marketing_control( $widget ) {
			$elementor    = \Elementor\Plugin::instance();
			$control_data = $elementor->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );

			if ( is_wp_error( $control_data ) ) {
				return;
			}

			$widget->add_control(
				'dwel_dynamickit_marketing_box',
				array(
					'label' => '',
					'type'  => \Elementor\Controls_Manager::RAW_HTML,
					'raw'   => $this->get_notice_html(),
				)
			);
		}

		private function get_notice_html() {
			$install_nonce = wp_create_nonce( 'dwel_install_nonce' );
			$dismiss_nonce = wp_create_nonce( 'dwel_dismiss_nonce' );

			ob_start();
			?>
			<div class="elementor-control-raw-html dwel-dynamickit-wrp">
				<div class="elementor-control-notice elementor-control-notice-type-info">
					<div class="elementor-control-notice-icon">
						<i class="eicon-elementor-square" aria-hidden="true"></i>
					</div>
					<div class="elementor-control-notice-main">
						<div class="elementor-control-notice-main-content">
							<?php echo esc_html__( 'Extend Elementor with 30+ creative widgets and dynamic content extensions.', 'dashboard-welcome-for-elementor' ); ?>
						</div>
						<div class="elementor-control-notice-main-actions">
							<button type="button" class="elementor-button e-btn e-info e-btn-1 dwel-install-dynamickit" data-nonce="<?php echo esc_attr( $install_nonce ); ?>">
								<?php echo esc_html__( 'Install DynamicKit for Elementor', 'dashboard-welcome-for-elementor' ); ?>
							</button>
						</div>
					</div>
					<button type="button" class="elementor-control-notice-dismiss tooltip-target dwel-dismiss-dynamickit" data-nonce="<?php echo esc_attr( $dismiss_nonce ); ?>">
						<i class="eicon eicon-close" aria-hidden="true"></i>
					</button>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		public function ajax_dismiss_notice() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => 'Permission denied' ) );
			}

			$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'dwel_dismiss_nonce' ) ) {
				wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			}

			update_option( self::DISMISS_OPT, true );
			wp_send_json_success();
		}

		public function ajax_install_plugin() {
			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( array( 'errorMessage' => __( 'Sorry, you are not allowed to install plugins on this site.', 'dashboard-welcome-for-elementor' ) ) );
			}

			check_ajax_referer( 'dwel_install_nonce' );

			$status = array(
				'install' => 'plugin',
				'slug'    => self::PLUGIN_SLUG,
			);

			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => self::PLUGIN_SLUG,
					'fields' => array( 'sections' => false ),
				)
			);

			if ( is_wp_error( $api ) ) {
				$status['errorMessage'] = $api->get_error_message();
				wp_send_json_error( $status );
			}

			$status['pluginName'] = $api->name;

			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $api->download_link );

			if ( is_wp_error( $result ) ) {
				$status['errorCode']    = $result->get_error_code();
				$status['errorMessage'] = $result->get_error_message();
				wp_send_json_error( $status );
			}

			if ( is_wp_error( $skin->result ) ) {
				if ( 'Destination folder already exists.' === $skin->result->get_error_message() ) {
					$install_status = install_plugin_install_status( $api );
					if ( current_user_can( 'activate_plugin', $install_status['file'] ) ) {
						$activation_result = activate_plugin( $install_status['file'] );
						if ( is_wp_error( $activation_result ) ) {
							$status['errorCode']    = $activation_result->get_error_code();
							$status['errorMessage'] = $activation_result->get_error_message();
							wp_send_json_error( $status );
						}
						$status['activated'] = true;
						wp_send_json_success( $status );
					}
				} else {
					$status['errorCode']    = $skin->result->get_error_code();
					$status['errorMessage'] = $skin->result->get_error_message();
					wp_send_json_error( $status );
				}
			}

			if ( $skin->get_errors()->has_errors() ) {
				$status['errorMessage'] = $skin->get_error_messages();
				wp_send_json_error( $status );
			}

			if ( is_null( $result ) ) {
				global $wp_filesystem;
				$status['errorCode']    = 'unable_to_connect_to_filesystem';
				$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'dashboard-welcome-for-elementor' );
				if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
					$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
				}
				wp_send_json_error( $status );
			}

			$install_status = install_plugin_install_status( $api );

			if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
				$activation_result = activate_plugin( $install_status['file'] );
				if ( is_wp_error( $activation_result ) ) {
					$status['errorCode']    = $activation_result->get_error_code();
					$status['errorMessage'] = $activation_result->get_error_message();
					wp_send_json_error( $status );
				}
				$status['activated'] = true;
			}

			wp_send_json_success( $status );
		}
	}

	DWEL_Marketing_Controllers::get_instance();
}

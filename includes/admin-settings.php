<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dwel_render_admin_settings_page( $title, $form_action, $roles, $templates, $settings ) {
	?>
	<div class="dwel-settings-header">
		<h2><?php echo esc_html( $title ); ?></h2>
	</div>

	<div class="dwel-settings-wrap">
		<?php if ( is_multisite() && get_current_blog_id() !== 1 ) : ?>
			<div class="notice notice-warning dwel-subsite-notice">
				<p><?php esc_html_e( 'Please note, changing the template in subsite will override the main settings.', 'dashboard-welcome-for-elementor' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" id="dwel-settings-form" action="<?php echo esc_url( $form_action ); ?>">
			<table class="dwel-settings-table wp-list-table widefat">
				<tr valign="top">
					<th scope="row"><strong><?php esc_html_e( 'User Role', 'dashboard-welcome-for-elementor' ); ?></strong></th>
					<th scope="row"><strong><?php esc_html_e( 'Select Template', 'dashboard-welcome-for-elementor' ); ?></strong></th>
					<th scope="row"><strong><?php esc_html_e( 'Is Dismissible?', 'dashboard-welcome-for-elementor' ); ?></strong></th>
				</tr>

				<?php
				$dwe_count = 0;
				foreach ( $roles as $role => $dwe_role_title ) :
					$dwe_row_class = ( 0 === $dwe_count % 2 ) ? 'alternate' : '';
					?>
					<tr class="<?php echo esc_attr( $dwe_row_class ); ?>">
						<td><?php echo esc_html( $dwe_role_title ); ?></td>

						<td>
							<select name="dwe_templates[<?php echo esc_attr( $role ); ?>][template]" class="dwel-templates-list">
								<option value=""><?php esc_html_e( '-- Select --', 'dashboard-welcome-for-elementor' ); ?></option>

								<?php foreach ( $templates as $id => $dwe_template ) : ?>
									<option
										value="<?php echo esc_attr( $id ); ?>"
										data-site="<?php echo esc_attr( $dwe_template['site'] ?? '' ); ?>"
										<?php
										if ( ! empty( $settings[ $role ]['template'] ) ) {
											selected( $settings[ $role ]['template'], $id );
										}
										?>
									>
										<?php echo esc_html( $dwe_template['title'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>

							<?php if ( is_multisite() ) : ?>
								<input
									type="hidden"
									name="dwe_templates[<?php echo esc_attr( $role ); ?>][site]"
									value="<?php echo esc_attr( $settings[ $role ]['site'] ?? '' ); ?>"
								/>
							<?php endif; ?>
						</td>

						<td>
							<input
								type="checkbox"
								name="dwe_templates[<?php echo esc_attr( $role ); ?>][dismissible]"
								value="1"
								<?php checked( ! empty( $settings[ $role ]['dismissible'] ) ); ?>
							/>
						</td>
					</tr>
					<?php
					$dwe_count++;
				endforeach;
				?>
			</table>

			<?php if ( is_multisite() && get_current_blog_id() === 1 ) : ?>
				<p>
					<label>
						<input
							type="checkbox"
							name="dwe_hide_from_subsites"
							value="1"
							<?php checked( get_option( 'dwe_hide_from_subsites' ) ); ?>
						/>
						<?php esc_html_e( 'Hide settings from network subsites', 'dashboard-welcome-for-elementor' ); ?>
					</label>
				</p>
			<?php endif; ?>

			<?php
			wp_nonce_field( 'dwe_settings', 'dwe_settings_nonce' );
			submit_button();
			?>
		</form>
	</div>

	<style>
	.dwel-settings-wrap {
		max-width: 860px;
	}
	.dwel-subsite-notice {
		margin: 0;
		margin-bottom: 10px;
	}
	</style>

	<?php if ( is_multisite() ) { ?>
	<script>
	(function($) {
		$('.dwel-templates-list').on('change', function() {
			var id = $(this).val();
			var siteId = $(this).find('option[value="'+id+'"]').data('site'); console.log(siteId);

			if ( '' !== siteId && undefined !== siteId ) {
				$(this).parent().find('input[type="hidden"]').val(siteId);
			}
		});
	})(jQuery);
	</script>
	<?php } ?>
	<?php
}

dwel_render_admin_settings_page( $title, $form_action, $roles, $templates, $settings );
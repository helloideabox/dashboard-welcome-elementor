<div class="dwe-settings-header">
	<h2><?php echo $title; ?></h2>
</div>
<div class="dwe-settings-wrap">
	<form method="post" id="dwe-settings-form" action="<?php echo $form_action; ?>">
		<table class="dwe-settings-table wp-list-table widefat">
			<tr valign="top">
                <th scope="row" valign="top">
                    <strong><?php esc_html_e('User Role', 'ibx-dwe'); ?></strong>
                </th>
                <th scope="row" valign="top">
                    <strong><?php esc_html_e('Select Template', 'ibx-dwe'); ?></strong>
                </th>
                <th scope="row" valign="top">
                    <strong><?php esc_html_e('Is Dismissible?', 'ibx-dwe'); ?></strong>
                </th>
            </tr>
			<?php $count = 0; foreach ( $roles as $role => $role_title ) { ?>
			<tr class="<?php echo $count % 2 == 0 ? 'alternate' : ''; ?>">
				<td><?php echo $role_title; ?></td>
				<td>
					<select name="dwe_templates[<?php echo $role; ?>][template]">
						<option value=""><?php _e( '-- Select --', 'ibx-dwe' ); ?></option>
						<?php foreach ( $templates as $id => $template ) { ?>
							<?php if ( ! empty( $settings ) && isset( $settings[$role]['template'] ) && $id == $settings[$role]['template'] ) { ?>
								<option value="<?php echo $id; ?>" selected="selected"><?php echo $template; ?></option>
							<?php } else { ?>
								<option value="<?php echo $id; ?>"><?php echo $template; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
				</td>
				<td>
					<input type="checkbox" name="dwe_templates[<?php echo $role; ?>][dismissible]" value="1"<?php echo ( ! empty( $settings ) && isset( $settings[$role]['dismissible'] ) ) ? ' checked="checked"' : ''; ?> />
				</td>
			</tr>
			<?php $count++; } ?>
		</table>
		
		<?php wp_nonce_field( 'dwe_settings', 'dwe_settings_nonce' ); ?>
		<?php submit_button(); ?>

	</form>
</div>

<style>
.dwe-settings-wrap {
	max-width: 860px;
}
</style>
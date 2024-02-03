<div class="dwe-settings-header">
	<h2><?php echo $title; ?></h2>
</div>
<div class="dwe-settings-wrap">
	<?php if ( is_multisite() && get_current_blog_id() != 1 ) { ?>
		<div class="notice notice-warning dwe-subsite-notice">
			<p><?php esc_html_e('Please note, changing the template in subsite will override the main settings.', 'ibx-dwe'); ?></p>
		</div>
	<?php } ?>
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
					<select name="dwe_templates[<?php echo $role; ?>][template]" class="dwe-templates-list">
						<option value=""><?php _e( '-- Select --', 'ibx-dwe' ); ?></option>
						<?php foreach ( $templates as $id => $template ) { ?>
							<?php if ( ! empty( $settings ) && isset( $settings[$role]['template'] ) && $id == $settings[$role]['template'] ) { ?>
								<option value="<?php echo $id; ?>" selected="selected" data-site="<?php echo null != $template['site'] ? $template['site'] : '';?>"><?php echo $template['title']; ?></option>
							<?php } else { ?>
								<option value="<?php echo $id; ?>" data-site="<?php echo null != $template['site'] ? $template['site'] : '';?>"><?php echo $template['title']; ?></option>
							<?php } ?>
						<?php } ?>
					</select>
					<?php if ( is_multisite() ) { ?>
						<input type="hidden" name="dwe_templates[<?php echo $role; ?>][site]" value="<?php echo isset( $settings[$role]['site'] ) ? $settings[$role]['site'] : ''; ?>" />
					<?php } ?>
				</td>
				<td>
					<input type="checkbox" name="dwe_templates[<?php echo $role; ?>][dismissible]" value="1"<?php echo ( ! empty( $settings ) && isset( $settings[$role]['dismissible'] ) ) ? ' checked="checked"' : ''; ?> />
				</td>
			</tr>
			<?php $count++; } ?>
		</table>

		<?php if ( is_multisite() && get_current_blog_id() == 1 ) { ?>
            <p>
                <label>
                    <input type="checkbox" value="1" name="dwe_hide_from_subsites" <?php if ( get_option( 'dwe_hide_from_subsites' ) == true ) { echo 'checked="checked"'; } ?> />
                    <?php esc_html_e( 'Hide settings from network subsites', 'ibx-dwe' ); ?>
                </label>
            </p>
        <?php } ?>
		
		<?php wp_nonce_field( 'dwe_settings', 'dwe_settings_nonce' ); ?>
		<?php submit_button(); ?>

	</form>
</div>

<style>
.dwe-settings-wrap {
	max-width: 860px;
}
.dwe-subsite-notice {
	margin: 0;
	margin-bottom: 10px;
}
</style>

<?php if ( is_multisite() ) { ?>
<script>
(function($) {
	$('.dwe-templates-list').on('change', function() {
		var id = $(this).val();
		var siteId = $(this).find('option[value="'+id+'"]').data('site'); console.log(siteId);

		if ( '' !== siteId && undefined !== siteId ) {
			$(this).parent().find('input[type="hidden"]').val(siteId);
		}
	});
})(jQuery);
</script>
<?php } ?>
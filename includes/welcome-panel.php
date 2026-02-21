<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="dwe-dashboard-welcome" class="dwe-panel-content">
	<?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
        <a class="welcome-panel-close" href="<?php echo esc_url( admin_url('welcome=0') ); ?>"><?php esc_html_e( 'Dismiss', 'dashboard-welcome-for-elementor' ); ?></a>
	<?php } ?>
	
	<?php $this->render_template(); ?>
</div>

<?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
<script type="text/javascript">
    ;(function($) {
        $(document).ready(function() {
            $('<div id="welcome-panel" class="welcome-panel"></div>').insertBefore('#dashboard-widgets-wrap').append($('#dwe-dashboard-welcome'));
        });
    })(jQuery);
</script>
<?php } ?>
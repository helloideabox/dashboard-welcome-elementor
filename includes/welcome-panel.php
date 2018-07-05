<div class="welcome-panel-content dwe-panel-content">
	<?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
        <a class="welcome-panel-close" href="<?php echo admin_url('welcome=0'); ?>"><?php _e('Dismiss'); ?></a>
	<?php } ?>
	
	<?php $this->render_template(); ?>
</div>

<?php if ( ! current_user_can( 'edit_theme_options' ) ) { ?>
<script type="text/javascript">
    ;(function($) {
        $(document).ready(function() {
            $('<div id="welcome-panel" class="welcome-panel"></div>').insertBefore('#dashboard-widgets-wrap').append($('.dwe-panel-content'));
        });
    })(jQuery);
</script>
<?php } ?>
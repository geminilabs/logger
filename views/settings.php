<?php defined( 'WPINC' ) || die; ?>

<div class="wrap">
	<h1><?= $title; ?></h1>
	<p>
		<?= __( 'The benefit of using the filter over the function is that it will not break your site if the Logger plugin is inactive or not installed.', 'logger' ); ?>
		<br>
		<?= sprintf( __( 'The default log type for the %s function is %s, it is also chainable.', 'logger' ), '<code>gllog</code>', '<em>debug</em>' ); ?>
		<br>
		<?= __( 'Available log types are:', 'logger' ); ?> <code>emergency|alert|critical|error|warning|notice|info|debug</code>
	</p>
	<h2><?= __( 'Usage examples:', 'logger' ); ?></h2>
	<pre>gllog($data);
gllog($data)->error($data);
gllog()->error($data);
gllog()->info($data)->error($error);
apply_filters('logger', $data, $optional_log_type);</pre>
	<form method="post" action="options.php" enctype="multipart/form-data">
		<?php settings_fields( $id ); ?>
		<input type="hidden" id="enabled" name="<?= $id; ?>[enabled]" value="<?= wp_validate_boolean( $settings->enabled ) ? 0 : 1; ?>">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?= wp_validate_boolean( $settings->enabled ) ? __( 'Disable Logging', 'logger' ) : __( 'Enable Logging', 'logger' ); ?>">
	</form>
	<?php if( wp_validate_boolean( $settings->enabled )) : ?>
	<br>
	<form method="post">
		<textarea class="large-text code" rows="20" id="log-file" onclick="this.select()" readonly><?= $log; ?></textarea>
		<?php wp_nonce_field( 'clear-log' ); ?>
		<input type="hidden" name="<?= $id; ?>[action]" value="clear-log">
		<?php submit_button( __( 'Clear Log', 'logger' ), 'secondary', 'clear-log', false ); ?>
	</form>
	<?php endif; ?>
</div>

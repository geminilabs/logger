<?php
/**
 * ╔═╗╔═╗╔╦╗╦╔╗╔╦  ╦  ╔═╗╔╗ ╔═╗
 * ║ ╦║╣ ║║║║║║║║  ║  ╠═╣╠╩╗╚═╗
 * ╚═╝╚═╝╩ ╩╩╝╚╝╩  ╩═╝╩ ╩╚═╝╚═╝
 *
 * Plugin Name: Logger
 * Description: Provides a logger for WordPress development
 * Version:     1.0.3
 * Author:      Paul Ryley
 * Author URI:  https://profiles.wordpress.org/pryley#content-plugins
 * License:     GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: logger
 * Domain Path: languages
 */

defined( 'WPINC' ) || die;

if( !class_exists( 'GL_Plugin_Check' )) {
	require_once __DIR__.'/activate.php';
}
require_once __DIR__.'/autoload.php';
if( GL_Plugin_Check::shouldDeactivate( __FILE__ ))return;
GeminiLabs\Logger\Application::load()->init();

/**
 * @return GeminiLabs\Logger\Log
 */
function gllog() {
	$app = GeminiLabs\Logger\Application::load();
	return func_num_args() > 0
		? call_user_func_array( [$app, 'initLogger'], ['logger', func_get_arg(0)] )
		: $app->log->logger();
}

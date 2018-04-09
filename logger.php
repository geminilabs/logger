<?php
/**
 * ╔═╗╔═╗╔╦╗╦╔╗╔╦  ╦  ╔═╗╔╗ ╔═╗
 * ║ ╦║╣ ║║║║║║║║  ║  ╠═╣╠╩╗╚═╗
 * ╚═╝╚═╝╩ ╩╩╝╚╝╩  ╩═╝╩ ╩╚═╝╚═╝
 *
 * Plugin Name: Logger
 * Description: Provides a logger for WordPress development
 * Version:     1.0.1
 * Author:      Paul Ryley
 * Author URI:  https://profiles.wordpress.org/pryley#content-plugins
 * License:     GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: logger
 * Domain Path: languages
 */

defined( 'WPINC' ) || die;

if( !class_exists( 'GL_Activate' )) {
	require_once __DIR__.'/activate.php';
}
require_once __DIR__.'/autoload.php';
if( GL_Activate::shouldDeactivate( __FILE__ ))return;
GeminiLabs\Logger\Application::load()->init();

/**
 * Alternate to: `apply_filters( 'logger', $data, $optional_log_type );`
 * @param mixed $message
 * @return GeminiLabs\Logger\Log
 */
function gllog( $message = null ) {
	$logger = GeminiLabs\Logger\Application::load()->log->logger();
	return func_num_args() > 0
		? $logger->debug( func_get_arg(0) )
		: $logger;
}

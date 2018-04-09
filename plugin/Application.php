<?php

namespace GeminiLabs\Logger;

use GeminiLabs\Logger\Log;

final class Application
{
	const ID = 'logger';

	public $file;
	public $languages;
	public $log;
	public $name;
	public $version;

	protected static $instance;

	/**
	 * @return static
	 */
	public static function load()
	{
		if( empty( static::$instance )) {
			static::$instance = new static;
		}
		return static::$instance;
	}

	/**
	 * @return void
	 */
	public function __construct()
	{
		$this->file = trailingslashit( dirname( __DIR__ )).static::ID.'.php';
		$this->log = new Log( $this->path( 'development.log' ));
		$plugin = get_file_data( $this->file, [
			'languages' => 'Domain Path',
			'name' => 'Plugin Name',
			'version' => 'Version',
		], 'plugin' );
		array_walk( $plugin, function( $value, $key ) {
			$this->$key = $value;
		});
	}

	/**
	 * @return void
	 */
	public function init()
	{
		add_action( 'admin_init',     [$this, 'clearLog'] );
		add_action( 'plugins_loaded', [$this, 'registerLanguages'] );
		add_action( 'admin_menu',     [$this, 'registerMenu'] );
		add_action( 'admin_menu',     [$this, 'registerSettings'] );
		add_filter( 'all',            [$this, 'initLogger'] );
	}

	/**
	 * Usage: apply_filters( 'logger', $data, $optional_log_type );
	 * @return void
	 */
	public function initLogger()
	{
		if( func_get_arg(0) != static::ID )return;
		$type = func_get_arg(2)
			? func_get_arg(2)
			: 'debug';
		$this->log->log( $type, func_get_arg(1) );
	}

	/**
	 * @return void
	 */
	public function clearLog()
	{
		$request = filter_input( INPUT_POST, static::ID, FILTER_DEFAULT , FILTER_REQUIRE_ARRAY );
		if( empty( $request['action'] ) || $request['action'] != 'clear-log' )return;
		check_admin_referer( $request['action'] );
		$this->log->clear();
		add_action( 'admin_notices', [$this, 'renderNotice'] );
	}

	/**
	 * @param string $file
	 * @return string
	 */
	public function path( $file = '' )
	{
		return plugin_dir_path( $this->file ).ltrim( trim( $file ), '/' );
	}

	/**
	 * @return void
	 */
	public function registerLanguages()
	{
		load_plugin_textdomain( static::ID, '',
			trailingslashit( plugin_basename( $this->path() ).'/'.$this->languages )
		);
	}

	/**
	 * @return void
	 * @action admin_menu
	 */
	public function registerMenu()
	{
		add_submenu_page(
			'options-general.php',
			__( 'Logger', 'logger' ),
			__( 'Logger', 'logger' ),
			'manage_options',
			static::ID,
			[$this, 'renderSettingsPage']
		);
	}

	/**
	 * @return void
	 * @action admin_menu
	 */
	public function registerSettings()
	{
		register_setting( static::ID, static::ID );
	}

	/**
	 * @param string $view
	 * @return void|null
	 */
	public function render( $view, array $data = [] )
	{
		if( !file_exists( $file = $this->path( 'views/'.$view.'.php' )))return;
		extract( $data );
		include $file;
	}

	/**
	 * @return void
	 */
	public function renderNotice()
	{
		$this->render( 'notice', [
			'notice' => __( 'The log was cleared.', 'logger' ),
			'status' => 'success',
		]);
	}

	/**
	 * @return void
	 * @callback add_submenu_page
	 */
	public function renderSettingsPage()
	{
		$this->render( 'settings', [
			'id' => static::ID,
			'log' => $this->log,
			'settings' => $this->getSettings(),
			'title' => __( 'Logger', 'logger' ),
		]);
	}

	/**
	 * @return object
	 */
	protected function getSettings()
	{
		$settings = get_option( static::ID, [] );
		if( empty( $settings )) {
			update_option( static::ID, $settings = [
				'enabled' => 0,
			]);
		}
		return (object)$settings;
	}
}

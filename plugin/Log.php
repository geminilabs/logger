<?php

namespace GeminiLabs\Logger;

use DateTime;
use ReflectionClass;

class Log
{
	const EMERGENCY = 'emergency';
	const ALERT = 'alert';
	const CRITICAL  = 'critical';
	const ERROR = 'error';
	const WARNING = 'warning';
	const NOTICE = 'notice';
	const INFO = 'info';
	const DEBUG = 'debug';

	protected $file;
	protected $log;

	public function __construct( $filename )
	{
		$this->file = $filename;
		$this->log = file_exists( $filename )
			? file_get_contents( $filename )
			: '';
	}

	public function __toString()
	{
		return $this->log;
	}

	/**
	 * Action must be taken immediately.
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 * @param string $message
	 * @param array $context
	 * @return static
	 */
	public function alert( $message, array $context = [] )
	{
		return $this->log( static::ALERT, $message, $context );
	}

	/**
	 * @return void
	 */
	public function clear()
	{
		$this->log = '';
		file_put_contents( $this->file, $this->log );
	}

	/**
	 * Critical conditions.
	 * Example: Application component unavailable, unexpected exception.
	 * @param string $message
	 * @param array $context
	 * @return static
	 */
	public function critical( $message, array $context = [] )
	{
		return $this->log( static::CRITICAL, $message, $context );
	}

	/**
	 * Detailed debug information.
	 * @param string $message
	 * @param array $context
	 * @return static
	 */
	public function debug( $message, array $context = [] )
	{
		return $this->log( static::DEBUG, $message, $context );
	}

	/**
	 * System is unusable.
	 * @param string $message
	 * @param array $context
	 * @return static
	 */
	public function emergency( $message, array $context = [] )
	{
		return $this->log( static::EMERGENCY, $message, $context );
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 * @param string $message
	 * @param array $context
	 * @return static
	 */
	public function error( $message, array $context = [] )
	{
		return $this->log( static::ERROR, $message, $context );
	}

	/**
	 * Interesting events.
	 * Example: User logs in, SQL logs.
	 * @param string $message
	 * @param array $context
	 * @return static
	 */
	public function info( $message, array $context = [] )
	{
		return $this->log( static::INFO, $message, $context );
	}

	/**
	 * @param mixed $level
	 * @param string $message
	 * @return static
	 */
	public function log( $level, $message, array $context = [] )
	{
		$constants = (new ReflectionClass( __NAMESPACE__.'\Log' ))->getConstants();
		$constants = (array)apply_filters( Application::ID.'/log-levels', $constants );
		if( in_array( $level, $constants, true )) {
			$date = get_date_from_gmt( gmdate('Y-m-d H:i:s') );
			$level = strtoupper( $level );
			$message = $this->interpolate( $message, $context );
			$entry = "[$date] $level: $message" . PHP_EOL;
			file_put_contents( $this->file, $entry, FILE_APPEND|LOCK_EX );
		}
		return $this;
	}

	/**
	 * @return static
	 */
	public function logger()
	{
		return $this;
	}

	/**
	 * Normal but significant events.
	 * @param string $message
	 * @param array $context
	 * @return static
	 */
	public function notice( $message, array $context = [] )
	{
		return $this->log( static::NOTICE, $message, $context );
	}

	/**
	 * Exceptional occurrences that are not errors.
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 * @param string $message
	 * @param array $context
	 * @return static
	 */
	public function warning( $message, array $context = [] )
	{
		return $this->log( static::WARNING, $message, $context );
	}

	/**
	 * @param mixed $message
	 * @param array $context
	 * @return array|string
	 */
	protected function interpolate( $message, array $context = [] )
	{
		if( $this->isObjectOrArray( $message )) {
			return print_r( $message, true );
		}
		$replace = [];
		foreach( $context as $key => $value ) {
			$replace['{'.$key.'}'] = $this->normalizeValue( $value );
		}
		return strtr( $message, $replace );
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	protected function isObjectOrArray( $value )
	{
		return is_object( $value ) || is_array( $value );
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	protected function normalizeValue( $value )
	{
		if( $value instanceof DateTime ) {
			$value = $value->format( 'Y-m-d H:i:s' );
		}
		else if( $this->isObjectOrArray( $value )) {
			$value = json_encode( $value );
		}
		else if( is_resource( $value )) {
			$value = (string)$value;
		}
		return $value;
	}
}

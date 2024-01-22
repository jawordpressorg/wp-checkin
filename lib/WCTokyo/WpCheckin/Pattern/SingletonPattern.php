<?php

namespace WCTokyo\WpCheckin\Pattern;


/**
 * Singleton pattern.
 */
abstract class SingletonPattern {

	/**
	 * @var static[] Instance store.
	 */
	private static $instances = [];

	/**
	 * Constructor.
	 */
	final protected function __construct() {
		$this->init();
	}

	/**
	 * Executed in constructor.
	 *
	 * @return void
	 */
	protected function init() {
		// Do something here.
	}

	/**
	 * Return instance.
	 *
	 * @return static
	 */
	public static function get_instance() {
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name();
		}
		return self::$instances[ $class_name ];
	}
}

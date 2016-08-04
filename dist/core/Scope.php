<?php
/**
 * This file houses the Scope class.
 *
 * @package RonaPHP
 * @copyright Copyright (c) 2016 Ryan Whitman (http://www.ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version .5.4.1
 * @link https://github.com/RyanWhitman/ronaphp
 * @since .5.4.1
 */

class Scope {

	private static $instance;
	
	private function __construct() {}

	public static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}
}
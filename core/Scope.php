<?php

class Scope {

	private static $instance;
	
	private function __construct() {}

	public static function instance() {

		if (self::$instance == NULL)
			self::$instance = new self();

		return self::$instance;
	}
}
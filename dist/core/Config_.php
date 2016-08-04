<?php
/**
 * This file houses the Config_ class.
 *
 * @package RonaPHP
 * @copyright Copyright (c) 2016 Ryan Whitman (http://www.ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version .5.4.1
 * @link https://github.com/RyanWhitman/ronaphp
 * @since .5.4.1
 */

class Config_ {

	private
		$starting_path,
		$is_const;
	
	public function __construct($starting_path, $is_const) {
		$this->starting_path = $starting_path;
		$this->is_const = $is_const;
	}

	public function _($path, $val = RONA_UNDEFINED) {

		if ($val === RONA_UNDEFINED)
			return new self($this->starting_path . '.' . $path, $this->is_const);

		Config::m($this->starting_path . '.' . $path, $val, $this->is_const);
		
		return $this;
	}
}
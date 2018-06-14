<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.5.0
 */

namespace Rona;

class Param_Filter_Response {

	public $success;

	public $tag;

	public $data;

	public function __construct(bool $success, $tag = NULL, $data = NULL) {
		$this->success = $success;
		$this->tag = (string) $tag;
		$this->data = $data;
	}
}
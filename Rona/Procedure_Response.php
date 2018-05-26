<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.3.1
 */

namespace Rona;

class Procedure_Response {

	public $success;

	public $tag;

	public $data;

	public function __construct(bool $success, string $tag, $data = NULL) {
		$this->success = $success;
		$this->tag = $tag;
		$this->data = $data;
	}
}
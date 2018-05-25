<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.3.0
 */

namespace Rona;

class Param_Exam_Response {

	public $success;

	public $data;

	public function __construct(bool $success, array $data) {
		$this->success = $success;
		$this->data = $data;
	}
}
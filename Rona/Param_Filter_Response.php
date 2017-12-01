<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
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
<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
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
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

class Scope {

	/**
	 * The HTTP Request object.
	 * 
	 * @var \Rona\HTTP_Request
	 */
	public $http_request;

	public function __construct(HTTP_Request $http_request) {

		// Set the HTTP Request object.
		$this->http_request = $http_request;
	}

	public function get_request_input(string $item = NULL) {

		return is_null($item) ? $this->http_request->get_input() : (isset($this->http_request->get_input()[$item]) ? $this->http_request->get_input()[$item] : NULL);
	}
}
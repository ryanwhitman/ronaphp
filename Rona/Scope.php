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
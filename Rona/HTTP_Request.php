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

class HTTP_Request {

	protected $original_method;

	protected $method;

	protected $scheme;

	protected $host;

	protected $port;

	protected $path;

	protected $path_vars = [];

	protected $query_string;

	protected $query_string_arr;

	protected $content_type;

	protected $headers;

	protected $body;

	protected $uploaded_files = [];

	protected $original_input = [];

	public $input = [];

	protected $processed_input = [];

	public function __construct(\Rona\Rona $app) {

		// If the getallheaders function doesn't exist natively, create it.
		if (!function_exists('getallheaders')) {
			function getallheaders() {

				$headers = [];
				if (!empty($_SERVER) && is_array($_SERVER)) {
					foreach ($_SERVER as $name => $value) {
						if (substr($name, 0, 5) == 'HTTP_')
							$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
					}
				}
				return $headers;
			}
		}

		// Get the original HTTP method.
		$this->original_method = $_SERVER['REQUEST_METHOD'] ?? '';

		// The HTTP method can be overridden by posting _method.
		$this->method = strtoupper(!empty($_POST['_method']) ? $_POST['_method'] : $this->original_method);

		// Get scheme.
		$this->scheme = $_SERVER['REQUEST_SCHEME'] ?? '';

		// Get host.
		$this->host = $_SERVER['HTTP_HOST'] ?? '';

		// Get port.
		$this->port = $_SERVER['SERVER_PORT'] ?? '';

		// Get path.
		$this->path = str_replace($app->config('base_path'), '', $app->config('request_path'));
		if ($this->path == '/')
			$this->path = '';

		// Get query string.
		$this->query_string = $_SERVER['QUERY_STRING'] ?? '';

		// Get query string array.
		parse_str($this->query_string, $this->query_string_arr);

		// Get content type.
		$this->content_type = $_SERVER['CONTENT_TYPE'] ?? '';

		// Get headers.
		$this->headers = getallheaders();

		// Get body.
		if ($this->get_content_type() == 'application/json')
			$this->body = json_decode(file_get_contents('php://input'), true);
		else if ($this->get_original_method() == 'POST' && strstr($this->get_content_type(), 'multipart/form-data') !== false) {
			# We're using the original method here to allow our manual put/patch/etc _method override methods to upload files.
			$this->body = $_POST;
			$this->uploaded_files = $_FILES;
		} else
			parse_str(file_get_contents('php://input'), $this->body);

		// Get input.
		$this->original_input = $this->input = $this->get_method() == 'GET' ? $this->get_query_string_arr() : array_merge($this->get_body(), $this->get_uploaded_files());
	}

	public function set_path_vars(array $path_vars) {
		$this->path_vars = $path_vars;
		$this->input = array_merge($this->input, $this->path_vars);
	}

	public function get_original_method() {
		return $this->original_method;
	}

	public function get_method() {
		return $this->method;
	}

	public function get_scheme() {
		return $this->scheme;
	}

	public function get_host() {
		return $this->host;
	}

	public function get_port() {
		return $this->port;
	}

	public function get_path() {
		return $this->path;
	}

	public function get_path_vars() {
		return $this->path_vars;
	}

	public function get_query_string() {
		return $this->query_string;
	}

	public function get_query_string_arr() {
		return $this->query_string_arr;
	}

	public function get_content_type() {
		return $this->content_type;
	}

	public function get_headers() {
		return $this->headers;
	}

	public function get_uploaded_files() {
		return $this->uploaded_files;
	}

	public function get_body() {
		return $this->body;
	}

	public function get_original_input(): array {
		return $this->original_input;
	}

	public function get_input(): array {
		return $this->input;
	}

	public function set_processed_input(array $processed_input) {
		$this->processed_input = $processed_input;
	}

	public function get_processed_input(): array {
		return $this->processed_input;
	}
}
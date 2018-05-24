<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.2.0
 */

namespace Rona\Routing;

class Store {

	protected $http_methods;

	protected $routes = [];

	public function __construct(array $http_methods) {
		$this->http_methods = $http_methods;
	}

	public function get($paths, $controller = NULL) {
		return $this->map('GET', $paths, $controller);
	}

	public function post($paths, $controller = NULL) {
		return $this->map('POST', $paths, $controller);
	}

	public function put($paths, $controller = NULL) {
		return $this->map('PUT', $paths, $controller);
	}

	public function patch($paths, $controller = NULL) {
		return $this->map('PATCH', $paths, $controller);
	}

	public function delete($paths, $controller = NULL) {
		return $this->map('DELETE', $paths, $controller);
	}

	public function head($paths, $controller = NULL) {
		return $this->map('HEAD', $paths, $controller);
	}

	public function options($paths, $controller = NULL) {
		return $this->map('OPTIONS', $paths, $controller);
	}

	public function all($paths, $controller = NULL) {
		return $this->map($this->http_methods, $paths, $controller);
	}

	public function map($methods, $paths, $controller = NULL) {

		// Create a Route Queue object.
		$route_queue = new Queue();

		// If a controller was passed in, append it to the route queue.
		if (!is_null($controller))
			$route_queue->append_controller($controller);

		// Loop thru each path.
		foreach ((array) $paths as $path) {

			// If the path is just a forward slash, remove it.
			if ($path == '/')
				$path = '';

			// Add route for each http method.
			foreach ((array) $methods as $method)
				$this->routes[strtoupper($method)][$path] = $route_queue;
		}

		// Return the route queue object.
		return $route_queue;
	}

	public function get_routes() {
		return $this->routes;
	}
}
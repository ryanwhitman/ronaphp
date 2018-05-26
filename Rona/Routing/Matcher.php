<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.3.1
 */

namespace Rona\Routing;

class Matcher {

	protected $path_vars_matched;

	public function get_matches(array $routes, string $method, string $path) {

		// Create an array to hold the matched routes.
		$matched_routes = [];

		// Strip the base path from the path (needs to be implemented).
		$path = str_replace('', '', urldecode($path));

		// When the path is just a slash, strip it.
		if ($path == '/')
			$path = '';

		// Loop thru the routes.
		foreach (($routes[strtoupper($method)] ?? []) as $route_path => $route_queue) {

			// Reset the path_vars_matched property.
			$this->path_vars_matched = [];

			// Create a regular expression to match the path.
			$regex = preg_replace_callback('/{([\da-z_]*[\da-z]+[\da-z_]*)(\([\S ]+?\))?}/i', function($matches) {
				$this->path_vars_matched[] = $matches[1];
				return isset($matches[2]) ? $matches[2] : '([^\/]+)';
			}, $route_path);

			$regex = preg_replace('/(?<!\\\\)\//', '\\/', $regex);

			// Validate the path against the regular expression.
			$path_matched = preg_match('/^' . $regex . '$/i', $path, $matches);

			if ($path_matched === 1) {

				$matched_route = [];

				// Store the matching route's queue in a variable.
				$matched_route['route_queue'] = $route_queue;

				// Collect the route variables.
				$matched_route['path_vars'] = [];
				for ($i = 1; $i < count($matches); $i++)
					$matched_route['path_vars'][$this->path_vars_matched[$i - 1]] = $matches[$i];

				// Add this route to the matched_routes array.
				array_push($matched_routes, $matched_route);
			}
		}

		return $matched_routes;
	}
}
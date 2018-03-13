<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona\Config;

use Rona\Helper;

class Config {

	const UNDEFINED = 'rona__undefined__rona';

	protected $constants = [];

	protected $variables = [];

	public function define(string $path, $val = self::UNDEFINED) {

		return $this->m($path, $val, true);
	}

	public function set(string $path, $val = self::UNDEFINED) {

		return $this->m($path, $val, false);
	}

	public function m(string $path, $val = self::UNDEFINED, bool $is_const) {

		$path = trim($path, ' .');

		$path_buildup = '';
		foreach (explode('.', $path) as $part) {
			$path_buildup .= '.' . $part;
			$path_buildup = trim($path_buildup, ' .');
			$eval_arr = Helper::array_get($this->constants, $path_buildup, self::UNDEFINED);
			if ($eval_arr !== self::UNDEFINED && !is_array($eval_arr))
				return false;
		}

		if ($val === self::UNDEFINED)
			return new Builder($this, $path, $is_const);

		if ($is_const)
			Helper::array_set($this->constants, $path, $val);
		else
			Helper::array_set($this->variables, $path, $val);

		return true;
	}

	/**
	 * Locate the value for the given configuration path.
	 * 
	 * @param    string   $path   The configuration path.
	 * @return   mixed            The value of the configuration or undefined if the configuration has not been set.
	 */
	protected function locate(string $path) {

		// Trim the path
		$path = trim($path, ' .');

		// Get the constants and variables.
		$constants = Helper::array_get($this->constants, $path, self::UNDEFINED);
		$variables = Helper::array_get($this->variables, $path, self::UNDEFINED);

		// If the configuration exists in both the constants array and variables array, return the merged array with the constants taking precedence.
		if (is_array($constants) && is_array($variables))
			return array_replace_recursive($variables, $constants);

		// If the configuration exists in the constants array, return it.
		else if ($constants !== self::UNDEFINED)
			return $constants;

		// If the configuration exists in the variables array, return it.
		else if ($variables !== self::UNDEFINED)
			return $variables;

		// The provided configuration does not exist.
		return self::UNDEFINED;
	}

	/**
	 * Determine whether or not a particular configuration path has been set (both constants and variables).
	 * 
	 * @param    string   $path   The configuration path.
	 * @return   bool
	 */
	public function isset(string $path): bool {
		return $this->locate($path) != self::UNDEFINED;
	}

	/**
	 * Get a configuration value.
	 * 
	 * @param    string   $path   The configuration path.
	 * @return   mixed            The configuration value.
	 *
	 * @throws   Exception        Throws an exception when the configuration value does not exist.
	 */
	public function get(string $path) {
		$res = $this->locate($path);
		if ($res == self::UNDEFINED)
			throw new \Exception("The configuration '$path' does not exist.");
		return $res;
	}
}
<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona\Config;

use Exception;
use Rona\Helper;

define('RONA_UNDEFINED', 'RONA__UNDEFINED__RONA');

class Config {

	protected $constants = [];

	protected $variables = [];

	public function define(string $path, $val = RONA_UNDEFINED) {

		return $this->m($path, $val, true);
	}

	public function set(string $path, $val = RONA_UNDEFINED) {

		return $this->m($path, $val, false);
	}

	public function m(string $path, $val = RONA_UNDEFINED, bool $is_const) {

		$path = strtolower(trim($path, ' .'));

		$path_buildup = '';
		foreach (explode('.', $path) as $part) {
			$path_buildup .= '.' . $part;
			$path_buildup = trim($path_buildup, ' .');
			$eval_arr = Helper::array_get($this->constants, $path_buildup, RONA_UNDEFINED);
			if ($eval_arr !== RONA_UNDEFINED && !is_array($eval_arr))
				return false;
		}

		if ($val === RONA_UNDEFINED)
			return new Builder($this, $path, $is_const);

		if ($is_const)
			Helper::array_set($this->constants, $path, $val);
		else
			Helper::array_set($this->variables, $path, $val);

		return true;
	}

	public function get(string $path) {

		$path = strtolower(trim($path, ' .'));

		$variables = Helper::array_get($this->variables, $path, NULL);
		$constants = Helper::array_get($this->constants, $path, NULL);
		
		if (is_array($constants) && is_array($variables))
			return array_replace_recursive($variables, $constants);
		else if (!is_null($constants))
			return $constants;
		else if (!is_null($variables))
			return $variables;
		else
			throw new Exception("The configuration '$path' does not exist.");
	}
}
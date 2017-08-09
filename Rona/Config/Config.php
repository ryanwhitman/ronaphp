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

class Config {

	const RONA_UNDEFINED = 'rona__undefined__rona';

	public $constants = [];

	public $variables = [];

	public function define(string $path, $val = self::RONA_UNDEFINED) {

		return $this->m($path, $val, true);
	}

	public function set(string $path, $val = self::RONA_UNDEFINED) {

		return $this->m($path, $val, false);
	}

	public function m(string $path, $val = self::RONA_UNDEFINED, bool $is_const) {

		$path = trim($path, ' .');

		$path_buildup = '';
		foreach (explode('.', $path) as $part) {
			$path_buildup .= '.' . $part;
			$path_buildup = trim($path_buildup, ' .');
			$eval_arr = Helper::array_get($this->constants, $path_buildup, self::RONA_UNDEFINED);
			if ($eval_arr !== self::RONA_UNDEFINED && !is_array($eval_arr))
				return false;
		}

		if ($val === self::RONA_UNDEFINED)
			return new Builder($this, $path, $is_const);

		if ($is_const)
			Helper::array_set($this->constants, $path, $val);
		else
			Helper::array_set($this->variables, $path, $val);

		return true;
	}

	public function get(string $path) {

		$path = trim($path, ' .');

		$variables = Helper::array_get($this->variables, $path, self::RONA_UNDEFINED);
		$constants = Helper::array_get($this->constants, $path, self::RONA_UNDEFINED);
		
		if (is_array($constants) && is_array($variables))
			return array_replace_recursive($variables, $constants);
		else if ($constants !== self::RONA_UNDEFINED)
			return $constants;
		else if ($variables !== self::RONA_UNDEFINED)
			return $variables;
		else
			throw new Exception("The configuration '$path' does not exist.");
	}
}
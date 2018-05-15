<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.1.0
 */

namespace Rona;

/**
 * The Rona module.
 */
class Rona_Module extends Module {

	/**
	 * @see parent class
	 */
	public function register_param_filter_groups() {
		$this->register_param_filter_group('general', '\Rona\Rona_Param_Filters');
	}
}
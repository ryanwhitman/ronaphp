<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.2.0
 */

namespace Rona;

/**
 * The Rona module.
 */
class Rona_Module extends Module {

	/**
	 * @see parent class
	 */
	public function register_resources() {
		$this->register_resource('db', function() {
			return new Rona_Db($this->app_config('db.host'), $this->app_config('db.username'), $this->app_config('db.password'), $this->app_config('db.name'));
		});
	}

	/**
	 * @see parent class
	 */
	public function register_param_filter_groups() {
		$this->register_param_filter_group('general', '\Rona\Rona_Param_Filters');
	}
}
<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.3.0
 */

namespace Rona\Modules;

/**
 * The Rona module.
 */
class Rona extends \Rona\Module {

	/**
	 * @see parent class
	 */
	protected function register_config() {

		// Standard module configuration
		$this->config()->define('route_base', 'rona');
	}

	/**
	 * @see parent class
	 */
	public function register_resources() {
		$this->register_resource('db', function() {
			return new \Rona\Modules\Rona\Resources\Db($this->app_config('db.host'), $this->app_config('db.username'), $this->app_config('db.password'), $this->app_config('db.name'));
		});
	}

	/**
	 * @see parent class
	 */
	public function register_param_filter_groups() {
		$this->register_param_filter_group('general', '\Rona\Modules\Rona\Param_Filters\General');
	}

	/**
	 * @see parent class
	 */
	public function register_routes() {

		/**
		 * Run deployment hook.
		 */
		$this->register_route()->get('/' . $this->config('route_base') . '/deploy', function($http_request, $route, $scope, $http_response) {
			$this->get_app()->run_hook('rona_deploy');
		});
	}
}
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
 * The Rona Logger module.
 */
class Rona_Logger extends \Rona\Module {

	/**
	 * @see parent class
	 */
	protected function register_config() {

		// Standard module configuration
		$this->config()->define('db_table_prefix', 'rona_logger');
		$this->config()->define('route_base', 'rona_logger');
		$this->config()->define('display_name', 'RonaPHP Logger');

		// Entry
		$this->config()->define('tag', [
			'min_length'	=> 1,
			'max_length'	=> 20
		]);
		$this->config()->define('description', [
			'min_length'	=> 1,
			'max_length'	=> 2000
		]);

		// DB Updates
		$this->config()->define('db_updates', [
			1	=> [
				"
				CREATE TABLE {$this->config('db_table_prefix')} (db_version TINYINT(1) UNSIGNED NOT NULL) COLLATE = 'utf8_general_ci';
				",
				"
				CREATE TABLE {$this->config('db_table_prefix')}_entries (
					entry_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					tag VARCHAR({$this->config('tag.max_length')}) NOT NULL,
					description TEXT NOT NULL,
					data TEXT NULL,
					when_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (entry_id)
				)
				COLLATE = 'utf8_general_ci'
				ENGINE = InnoDB
				;
				"
			]
		]);

		// View Entry
		$this->config()->set('view_entry_url', function($route) {
			return $route;
		});

		// Email Report
		$this->config()->set('email_report', [
			'email_handler'		=> function($from, $subject, $body) {},
			'minute_interval'	=> 5
		]);
	}

	/**
	 * @see parent class
	 */
	public function register_param_filter_groups() {
		$this->register_param_filter_group('entry', '\\' . __CLASS__ . '\Param_Filters\Entry');
	}

	/**
	 * @see parent class
	 */
	public function register_procedure_groups() {
		$this->register_procedure_group('general', '\\' . __CLASS__ . '\Procedures\General');
		$this->register_procedure_group('entry', '\\' . __CLASS__ . '\Procedures\Entry');
	}

	/**
	 * @see parent class
	 */
	public function register_hooks() {

		// Deploy
		$this->register_hook('rona_deploy', function() {
			return $this->run_procedure('general.deploy');
		});
	}

	/**
	 * @see parent class
	 */
	public function register_routes() {

		/**
		 * Get an entry.
		 */
		$this->register_route()->get('/' . $this->config('route_base') . '/entry/{entry_id(\d+)}', function($http_request, $route, $scope, $http_response) {

			// Get the entry.
			$get_entry_res = $this->run_procedure('entry.get', ['entry_id' => $http_request->get_input()['entry_id']]);
			if (!$get_entry_res->success)
				$http_response->set_code('400');

			// Produce the view.
			ob_start();
				?>
				<html>
					<head>
						<title><?php echo $this->config('display_name') ?></title>
					</head>
					<body>
						<h1><?php echo $this->config('display_name') ?></h1>
						<p>
							<?php
							if (!$get_entry_res->success) {
								?>
								Entry ID <?php echo $http_request->get_input()['entry_id'] ?> was not found.
								<?php
							} else {
								?>
								Entry ID: <?php echo $get_entry_res->data['entry_id'] ?><br>
								Description: <?php echo $get_entry_res->data['description'] ?><br>
								When Created: <?php echo $get_entry_res->data['when_created'] ?><br>
								Data: <pre><?php print_r($get_entry_res->data['data']) ?></pre>
								<?php
							}
							?>
						</p>
					</body>
				</html>
				<?php
			$http_response->set_body(ob_get_clean());
		});
	}
}
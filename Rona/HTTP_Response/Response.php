<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona\HTTP_Response;

use Rona\Helper;

class Response {

	protected $app;

	protected $scope;

	protected $route_module;

	protected $current_controller_module;

	protected $is_json = false;

	protected $code;

	protected $api;

	protected $view;

	protected $body;

	public function __construct(\Rona\Rona $app, \Rona\Scope $scope) {
		$this->app = $app;
		$this->scope = $scope;
	}

	public function set_route_module(\Rona\Module $route_module) {
		$this->route_module = $route_module;
	}

	public function set_current_controller_module(\Rona\Module $current_controller_module) {
		$this->current_controller_module = $current_controller_module;
	}

	public function set_code(int $code) {
		$this->code = $code;
	}

	public function get_code() {
		return $this->code;
	}

	public function set_as_json(bool $is_json = true) {
		$this->is_json = $is_json;
	}

	public function set_body($body) {
		$this->body = $body;
	}

	public function get_body() {
		return $this->body;
	}

	public function api(): API {
		if (is_null($this->api))
			$this->api = new API;
		return $this->api;
	}

	public function view(): View {
		if (is_null($this->view))
			$this->view = new View;
		$this->view->set_current_controller_module($this->current_controller_module);
		return $this->view;
	}

	public function output() {

		// Set the HTTP Response code.
		$code = $this->get_code();
		if (is_int($code))
			http_response_code($code);

		// When the content type is to be JSON:
		if ($this->is_json || is_object($this->api)) {
			header('Content-Type: application/json;charset=utf-8');

			if (is_object($this->api))
				$this->set_body($this->api);

			$this->set_body(json_encode($this->get_body()));
		}

		// When the content type is to be text/HTML:
		else {
			header('Content-Type: text/html;charset=utf-8');

			if (is_object($this->view) && $this->view->template) {

				ob_start();
					(function() {
						$this->route_module->include_template_file($this->scope, $this->view->template['module']->config('view_assets.template', true)($this->view->template['module'], Helper::maybe_closure($this->view->template['template']), Helper::maybe_closure($this->view->template['data'])));
					})();
				$body = ob_get_clean();

				foreach ($this->view->components as $placeholder => $sections) {

					// Merge the first, middle, and last sections into a single array.
					$p = array_merge($sections['first'], $sections['middle'], $sections['last']);

					ob_start();

						foreach ($p as $components) {

							foreach ($components['items'] as $item) {

								switch ($components['type']) {

									// Stylesheet
									case 'stylesheet':
										?>
										<link href="<?php echo $components['module']->config('view_assets.stylesheet', true)($components['module'], Helper::maybe_closure($item), Helper::maybe_closure($components['data'])) ?>" rel="stylesheet">
										<?php
										break;

									// Javascript
									case 'javascript':
										?>
										<script src="<?php echo $components['module']->config('view_assets.javascript', true)($components['module'], Helper::maybe_closure($item), Helper::maybe_closure($components['data'])) ?>"></script>
										<?php
										break;

									// File
									case 'file':
										(function() use ($components, $item) {
											$this->route_module->include_template_file($this->scope, $components['module']->config('view_assets.file', true)($components['module'], Helper::maybe_closure($item), Helper::maybe_closure($components['data'])));
										})();
										break;

									// Content
									case 'content':
										echo Helper::maybe_closure($item);
										break;
								}
							}
						}
					$contents = ob_get_clean();

					// Escape $n backreferences
					$contents = preg_replace('/\$(\d)/', '\\\$$1', $contents);

					$body = str_replace(str_replace($this->app->config('template_placeholder_replace_text'), $placeholder, $this->app->config('template_placeholder')), $contents, $body);
				}
				
				// Remove any remaining placeholders.
				$body = preg_replace('/' . str_replace($this->app->config('template_placeholder_replace_text'), '.*', $this->app->config('template_placeholder')) . '/i', '', $body);
				
				// Set the body.				
				$this->set_body($body);
			}
		}

		// Output the body.
		echo $this->get_body();
	}
}
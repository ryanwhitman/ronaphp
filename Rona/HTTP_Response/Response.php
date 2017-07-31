<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona\HTTP_Response;

class Response {

	protected $app;

	protected $active_module;

	protected $is_json = false;

	protected $code = 200;

	protected $view;

	protected $body;

	public function __construct(\Rona\App $app) {
		$this->app = $app;
	}

	public function set_active_module(\Rona\Module $active_module = NULL) {
		$this->active_module = $active_module;
	}

	public function set_code(int $code) {
		$this->code = $code;
	}

	public function get_code(): int {
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

	public function view(): View {
		if (is_null($this->view))
			$this->view = new View;
		$this->view->set_active_module($this->active_module);
		return $this->view;
	}

	public function output() {
		http_response_code($this->code);
		if ($this->is_json) {
			header('Content-Type: application/json;charset=utf-8');
			echo json_encode($this->get_body());
		} else {
			header('Content-Type: text/html;charset=utf-8');

			if (!empty($this->view->template)) {
		
				$body = (function() {
					ob_start();
						include $this->view->template['module']->run_hook('view_template', false, $this->view->template['module'], $this->view->template['template']);
					return ob_get_clean();
				})();

				foreach ($this->view->components as $placeholder => $sections) {

					// Merge the first, middle, and last sections into a single array.
					$p = array_merge($sections['first'], $sections['middle'], $sections['last']);

					ob_start();

						foreach ($p as $components) {

							foreach ($components['items'] as $item) {

								// Stylesheet
								if ($components['type'] == 'stylesheet')
									echo $components['module']->run_hook('view_stylesheet', false, $components['module'], $item);

								// Javascript
								else if ($components['type'] == 'javascript')
									echo $components['module']->run_hook('view_javascript', false, $components['module'], $item);

								// File
								else if ($components['type'] == 'file') {
									echo (function($module, $item) {
										ob_start();
											$scope = $this->app->scope;
											include $module->run_hook('view_file', false, $module, $item);
										return ob_get_clean();
									})($components['module'], $item);
								}

								// Content
								else if ($components['type'] == 'content') {
									echo $item;
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

			echo $this->get_body();
		}
	}
}
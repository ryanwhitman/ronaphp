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

use Rona\Helper;

class Response {

	protected $app;

	public $route_module;

	protected $active_module;

	protected $is_json = false;

	protected $code = 200;

	protected $view;

	protected $body;

	public function __construct(\Rona\Rona $app) {
		$this->app = $app;
	}

	public function set_route_module(\Rona\Module $route_module) {
		$this->route_module = $route_module;
	}

	public function set_active_module(\Rona\Module $active_module) {
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

			if (is_object($this->view) && $this->view->template) {

				ob_start();
					(function() {
						$this->route_module->include($this->app->config('view_assets.template')($this->view->template['module'], Helper::func_or($this->view->template['template'])));
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
										echo $this->app->config('view_assets.stylesheet')($components['module'], Helper::func_or($item));
										break;

									// Javascript
									case 'javascript':
										echo $this->app->config('view_assets.javascript')($components['module'], Helper::func_or($item));
										break;

									// File
									case 'file':
										(function() use ($components, $item) {
											$this->route_module->include($this->app->config('view_assets.file')($components['module'], Helper::func_or($item)));
										})();
										break;

									// Content
									case 'content':
										echo Helper::func_or($item);
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

			echo $this->get_body();
		}
	}
}
<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.5.0
 */

namespace Rona\HTTP_Response;

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

	/**
	 * Output the HTTP Response.
	 */
	public function output() {

		// Set the HTTP Response code.
		$code = $this->get_code();
		if (is_int($code))
			http_response_code($code);

		// When the content type is to be JSON:
		if ($this->is_json || is_object($this->api)) {

			// Set the content type header.
			header('Content-Type: application/json;charset=utf-8');

			// If the API object is being utilized, set it as the response body.
			if (is_object($this->api))
				$this->set_body($this->api);

			// JSON encode the response body.
			$this->set_body(json_encode($this->get_body()));
		}

		// When the content type is to be text/HTML:
		else {

			// Set the content type header.
			header('Content-Type: text/html;charset=utf-8');

			// When the view object is being utilized and a template has been defined:
			if (is_object($this->view) && $this->view->template) {

				// Set the response body to contain the template file.
				ob_start();
					(function() {

						// If the full option is set, simply use the defined file.
						if (!empty($this->view->template['options']['full']))
							$file = $this->view->template['file'];

						// Otherwise, run the file through the config.
						else {

							// If the module option is set, use that module.
							if (isset($this->view->template['options']['module']))
								$this->view->template['module'] = $this->app->get_module($this->view->template['options']['module']);

							// Set the file.
							$file = $this->view->template['module']->config('file_locations.templates', true)($this->view->template['module'], $this->app->get_module_resource('rona', 'helper')->maybe_closure($this->view->template['file']));
						}

						// Include the file under the route module. ** Should this be changed to the established module above? **
						$this->route_module->include_file($this->scope, $file);
					})();
				$body = ob_get_clean();

				// Add the view components to the response body.
				foreach ($this->view->components as $placeholder => $sections) {

					// Merge the first, middle, and last sections into a single array.
					$merged_sections = array_merge($sections['first'], $sections['middle'], $sections['last']);

					// Run through the sections and process the various component types.
					ob_start();
						foreach ($merged_sections as $components) {
							foreach ($components['files_or_content'] as $file_or_content) {
								switch ($components['type']) {

									// Stylesheets
									case 'stylesheet':

										// If the full option is set, simply use the defined file.
										if (!empty($components['options']['full']))
											$file = $file_or_content;

										// Otherwise, run the file through the config.
										else {

											// If the module option is set, use that module.
											if (isset($components['options']['module']))
												$components['module'] = $this->app->get_module($components['options']['module']);

											// Set the file.
											$file = $components['module']->config('file_locations.stylesheets', true)($components['module'], $this->app->get_module_resource('rona', 'helper')->maybe_closure($file_or_content));
										}

										// Establish the element attributes by merging the component options and build the HTML element.
										$attrs = array_merge([
											'href'	=> $file,
											'rel'	=> 'stylesheet'
										], $components['options']['attrs'] ?? []);
										?>
										<link<?php foreach ($attrs as $attr_name => $attr_val) {echo ' ' . $attr_name . (is_null($attr_val) ? '' : '="' . $attr_val . '"');} ?>>
										<?php
										break;

									// Javascript
									case 'javascript':

										// If the full option is set, simply use the defined file.
										if (!empty($components['options']['full']))
											$file = $file_or_content;

										// Otherwise, run the file through the config.
										else {

											// If the module option is set, use that module.
											if (isset($components['options']['module']))
												$components['module'] = $this->app->get_module($components['options']['module']);

											// Set the file.
											$file = $components['module']->config('file_locations.javascript', true)($components['module'], $this->app->get_module_resource('rona', 'helper')->maybe_closure($file_or_content));
										}

										// Establish the element attributes by merging the component options and build the HTML element.
										$attrs = array_merge([
											'src'	=> $file
										], $components['options']['attrs'] ?? []);
										?>
										<script<?php foreach ($attrs as $attr_name => $attr_val) {echo ' ' . $attr_name . (is_null($attr_val) ? '' : '="' . $attr_val . '"');} ?>></script>
										<?php
										break;

									// Files
									case 'file':
										(function() use ($components, $file_or_content) {

											// If the full option is set, simply use the defined file.
											if (!empty($components['options']['full']))
												$file = $file_or_content;

											// Otherwise, run the file through the config.
											else {

												// If the module option is set, use that module.
												if (isset($components['options']['module']))
													$components['module'] = $this->app->get_module($components['options']['module']);

												// Set the file.
												$file = $components['module']->config('file_locations.files', true)($components['module'], $this->app->get_module_resource('rona', 'helper')->maybe_closure($file_or_content));
											}

											// Include the file under the route module. ** Should this be changed to the established module above? **
											$this->route_module->include_file($this->scope, $file);
										})();
										break;

									// Content
									case 'content':
										echo $this->app->get_module_resource('rona', 'helper')->maybe_closure($file_or_content);
										break;
								}
							}
						}
					$output = ob_get_clean();

					/**
					 * Found at https://stackoverflow.com/a/18993978/6589108. "Pre-parse the replacement text to escape the $ when followed by a number ($n has special meaning when using in the replacement text). See the comment on the php.net docs page." http://us1.php.net/manual/en/function.preg-replace.php#103985. "If there's a chance your replacement text contains any strings such as "$0.95", you'll need to escape those $n backreferences."
					 */
					$output = preg_replace('/\$(\d)/', '\\\$$1', $output);

					// Add the output to the response body at the designated placeholder.
					$body = str_replace(str_replace($this->app->config('template_placeholder_replace_text'), $placeholder, $this->app->config('template_placeholder')), $output, $body);
				}

				// Remove any remaining placeholders.
				$body = preg_replace('/' . str_replace($this->app->config('template_placeholder_replace_text'), '.*', $this->app->config('template_placeholder')) . '/i', '', $body);

				// Set the response body.
				$this->set_body($body);
			}
		}

		// Output the response body.
		echo $this->get_body();
	}
}
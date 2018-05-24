<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.2.0
 */

namespace Rona\Routing;

use Rona\Module;

class Route {

	public $route_found = false;

	public $is_no_route = false;

	protected $module;

	protected $current_controller_module;

	protected $controllers = [
		'first'		=> [],
		'middle'	=> [],
		'last'		=> []
	];

	protected $authentication;

	protected $input;

	protected $authorization;

	protected $procedure;

	protected $procedure_callback;

	protected $finalization;

	public $data = [];

	public function set_module(Module $module) {
		$this->module = $module;
	}

	public function get_module(): Module {
		return $this->module;
	}

	public function set_current_controller_module(Module $current_controller_module) {
		$this->current_controller_module = $current_controller_module;
	}

	protected function place_controller(string $placement, $controller): self {

		$c = [];

		if ($controller instanceof \Closure) {
			$c['module'] = $this->current_controller_module;
			$c['callback'] = $controller;
		} else if (is_string($controller) && method_exists($this->current_controller_module, $controller)) {
			$c['module'] = $this->current_controller_module;
			$c['callback'] = [$this->current_controller_module, $controller];
		} else if (is_array($controller) && count($controller) == 2) {
			if (isset($controller['module']) && $controller['module'] instanceof Module && isset($controller['callback']) && is_callable($controller['callback'])) {
				$c['module'] = $controller['module'];
				$c['callback'] = $controller['callback'];
			} else if (isset($controller[0]) && isset($controller[1]) && is_string($controller[1])) {
				if ($controller[0] instanceof Module && method_exists($controller[0], $controller[1])) {
					$c['module'] = $controller[0];
					$c['callback'] = $controller;
				} else if (is_string($controller[0])) {
					$module = $this->current_controller_module->get_module($controller[0]);
					if ($module && method_exists($module, $controller[1])) {
						$c['module'] = $module;
						$c['callback'] = [$module, $controller[1]];
					}
				}
			}
		}

		if (empty($c))
			throw new \Exception('The controller ' . json_encode($controller) . ' identified in the module "' . $this->current_controller_module->get_id() . '" is not valid.');

		switch ($placement) {

			case 'first':
				array_unshift($this->controllers['first'], $c);
				break;

			case 'prepend':
				array_unshift($this->controllers['middle'], $c);
				break;

			case 'set':
				$this->controllers['first'] = [];
				$this->controllers['middle'] = [$c];
				$this->controllers['last'] = [];
				break;

			case 'append':
				$this->controllers['middle'][] = $c;
				break;

			case 'last':
				$this->controllers['last'][] = $c;
				break;
		}

		return $this;
	}

	public function first_controller($controller): self {
		return $this->place_controller('first', $controller);
	}

	public function prepend_controller($controller): self {
		return $this->place_controller('prepend', $controller);
	}

	public function set_controller($controller): self {
		return $this->place_controller('set', $controller);
	}

	public function append_controller($controller): self {
		return $this->place_controller('append', $controller);
	}

	public function last_controller($controller): self {
		return $this->place_controller('last', $controller);
	}

	public function remove_controllers(): self {
		$this->controllers['first'] = [];
		$this->controllers['middle'] = [];
		$this->controllers['last'] = [];

		return $this;
	}

	public function get_controllers() {
		return array_merge($this->controllers['first'], $this->controllers['middle'], $this->controllers['last']);
	}

	public function set_authentication(\Closure $callback = NULL): self {
		$this->authentication = $callback;
		return $this;
	}

	public function get_authentication() {
		return $this->authentication;
	}

	public function set_input($input = NULL): self {
		$this->input = $input;
		return $this;
	}

	public function get_input() {
		return $this->input;
	}

	public function set_authorization(\Closure $callback = NULL): self {
		$this->authorization = $callback;
		return $this;
	}

	public function get_authorization() {
		return $this->authorization;
	}

	public function set_procedure($procedure): self {

		$p = [];

		if (is_string($procedure)) {
			$p['module'] = $this->current_controller_module;
			$p['full_procedure_name'] = $procedure;
		} else if (
			is_array($procedure) &&
			count($procedure == 2) &&
			isset($procedure[0]) &&
			isset($procedure[1]) &&
			is_string($procedure[1])
		) {
			if ($procedure[0] instanceof Module) {
				$p['module'] = $procedure[0];
				$p['full_procedure_name'] = $procedure[1];
			} else if (is_string($procedure[0])) {
				$module = $this->current_controller_module->get_module($procedure[0]);
				if ($module) {
					$p['module'] = $module;
					$p['full_procedure_name'] = $procedure[1];
				}
			}
		}

		if (empty($p))
			throw new \Exception('The procedure ' . json_encode($procedure) . ' identified in the module "' . $this->current_controller_module->get_id() . '" is not valid.');

		$this->procedure = $p;
		return $this;
	}

	public function get_procedure() {
		return $this->procedure;
	}

	public function set_procedure_callback($procedure_callback = NULL): self {
		$this->procedure_callback = $procedure_callback;
		return $this;
	}

	public function get_procedure_callback() {
		return $this->procedure_callback;
	}

	public function set_finalization(\Closure $callback = NULL): self {
		$this->finalization = $callback;
		return $this;
	}

	public function get_finalization() {
		return $this->finalization;
	}
}
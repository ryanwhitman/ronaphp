<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona\Routing;

use Rona\Module;

class Route {

	public $route_found = false;

	public $is_no_route = false;

	protected $active_module;

	protected $controllers = [
		'first'		=> [],
		'middle'	=> [],
		'last'		=> []
	];

	protected $authentication;
	
	protected $authorization;

	protected $procedure;

	public $data = [];

	public function set_active_module(Module $active_module) {
		$this->active_module = $active_module;
	}

	protected function ctrl(string $placement, $controller): self {

		$c = [];

		if ($controller instanceof \Closure) {
			$c['module'] = $this->active_module;
			$c['callback'] = $controller;
		} else if (is_string($controller) && method_exists($this->active_module, $controller)) {
			$c['module'] = $this->active_module;
			$c['callback'] = [$this->active_module, $controller];
		} else if (is_array($controller) && count($controller == 2)) {
			if (isset($controller['module']) && $controller['module'] instanceof Module && isset($controller['callback']) && is_callable($controller['callback'])) {
				$c['module'] = $controller['module'];
				$c['callback'] = $controller['callback'];
			} else if (isset($controller[0]) && isset($controller[1]) && is_string($controller[1])) {
				if ($controller[0] instanceof Module && method_exists($controller[0], $controller[1])) {
					$c['module'] = $controller[0];
					$c['callback'] = $controller;
				} else if (is_string($controller[0])) {
					$module = $this->active_module->get_module($controller[0]);
					if ($module && method_exists($module, $controller[1])) {
						$c['module'] = $module;
						$c['callback'] = [$module, $controller[1]];
					}
				}
			}
		}

		if (empty($c))
			throw new \Exception('The controller ' . json_encode($controller) . ' identified in the module "' . $this->active_module->get_id() . '" is not valid.');

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
		return $this->ctrl('first', $controller);
	}

	public function prepend_controller($controller): self {
		return $this->ctrl('prepend', $controller);
	}

	public function set_controller($controller): self {
		return $this->ctrl('set', $controller);
	}

	public function append_controller($controller): self {
		return $this->ctrl('append', $controller);
	}

	public function last_controller($controller): self {
		return $this->ctrl('last', $controller);
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

	public function authentication(\Closure $callback = NULL): self {
		$this->authentication = $callback;
		return $this;
	}

	public function get_authentication() {
		return $this->authentication;
	}

	public function authorization(\Closure $callback = NULL): self {
		$this->authorization = $callback;
		return $this;
	}

	public function get_authorization() {
		return $this->authorization;
	}

	public function procedure($procedure, $failed_input_handler = NULL, $procedure_handler = NULL): self {

		$p = [];

		if (is_string($procedure)) {
			$p['module'] = $this->active_module;
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
				$module = $this->active_module->get_module($procedure[0]);
				if ($module) {
					$p['module'] = $module;
					$p['full_procedure_name'] = $procedure[1];
				}
			}
		}

		if (empty($p))
			throw new \Exception('The procedure ' . json_encode($procedure) . ' identified in the module "' . $this->active_module->get_id() . '" is not valid.');

		$p['failed_input_handler'] = $failed_input_handler;
		$p['procedure_handler'] = $procedure_handler;

		$this->procedure = $p;
		return $this;
	}

	public function get_procedure() {
		return $this->procedure;
	}
}
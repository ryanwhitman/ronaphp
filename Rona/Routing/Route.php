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

use Exception;
use Closure;
use Rona\Module;

class Route {

	public $route_found = false;

	public $is_no_route = false;

	protected $app;

	protected $active_module;

	protected $controllers = [
		'first'		=> [],
		'middle'	=> [],
		'last'		=> []
	];

	public $data = [];

	public function __construct(\Rona\App $app) {
		$this->app = $app;
	}

	public function set_active_module(Module $active_module = NULL) {
		$this->active_module = $active_module;
	}

	protected function ctrl(string $placement, $controller): self {

		$c = [];

		if ($controller instanceof Closure) {
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
					$module = $this->app->module($controller[0]);
					if ($module && method_exists($module, $controller[1])) {
						$c['module'] = $module;
						$c['callback'] = [$module, $controller[1]];
					}
				}
			}
		}

		if (empty($c))
			throw new Exception('The controller ' . json_encode($controller) . ' identified in the module "' . $this->active_module->name() . '" is not valid.');

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

	public function controller($controller): self {
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
}
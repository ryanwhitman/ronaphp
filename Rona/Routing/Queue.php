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

class Queue {

	protected $data = [];

	protected $queue = [];

	protected $route;

	public function process(Route $route) {
		$this->route = $route;
		$this->route->data = array_merge($this->route->data, $this->data);
		foreach ($this->queue as $item) {
			call_user_func_array($item['func'], $item['args']);
		}
	}

	protected function queue(array $args, callable $func): self {
		$this->queue[] = ['args' => $args, 'func' => $func];
		return $this;
	}

	public function first_controller($controller): self {
		return $this->queue([$controller], function($controller) {
			$this->route->first_controller($controller);
		});
	}

	public function prepend_controller($controller): self {
		return $this->queue([$controller], function($controller) {
			$this->route->prepend_controller($controller);
		});
	}

	public function controller($controller): self {
		return $this->queue([$controller], function($controller) {
			$this->route->controller($controller);
		});
	}

	public function append_controller($controller): self {
		return $this->queue([$controller], function($controller) {
			$this->route->append_controller($controller);
		});
	}

	public function last_controller($controller): self {
		return $this->queue([$controller], function($controller) {
			$this->route->last_controller($controller);
		});
	}

	public function remove_controllers(): self {
		return $this->queue([], function() {
			$this->route->remove_controllers();
		});
	}

	public function data(array $data): self {
		$this->data = $data;
		return $this;
	}
}
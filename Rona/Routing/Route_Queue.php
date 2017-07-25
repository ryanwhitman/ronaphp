<?php

namespace Rona\Routing;

class Route_Queue {

	protected $queue = [];

	protected $route;

	public function process(Route $route) {
		$this->route = $route;
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
}
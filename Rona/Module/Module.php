<?php

namespace Rona\Module;

use Exception;

class Module {

	protected $name;

	protected $config;

	protected $app;

	public function __construct(\Rona\App\App $app, string $name = NULL) {

		// Set the Rona instance.
		$this->app = $app;

		// If a module name was passed in thru the construct, set it.
		if (!is_null($name))
			$this->name = $name;

		// Prepare the module name and ensure it exists.
		$this->name = strtolower(trim($this->name()));
		if (!$this->name())
			throw new Exception('A module must have a name.');

		// Create a config object for this module.
		$this->config = new \Rona\Config\Config;

		// Register this module's config.
		$this->register_config();

		// Create route store objects for this module.
		$this->route_store = [
			'abstract'			=> new \Rona\Routing\Route_Store($this->app()->config('http_methods')),
			'non_abstract'		=> new \Rona\Routing\Route_Store($this->app()->config('http_methods'))
		];
	}

	public function name() {
		return $this->name;
	}

	public function app(): \Rona\App\App {
		return $this->app;
	}

	public function config(string $item = NULL) {
		return is_null($item) ? $this->config : $this->config->get($item);
	}

	protected function register_config() {}

	protected function register_abstract_route() {
		return $this->route_store['abstract'];
	}

	protected function register_route() {
		return $this->route_store['non_abstract'];
	}

	public function register_routes() {}

	public function run_hook(string $name, bool $persist = true, ...$args) {

		$res = [];

		$hook_run = false;
		if (method_exists($this, $this->app()->config('hook_prefix') . $name)) {
			$res[$this->name()] = call_user_func_array([$this, $this->app()->config('hook_prefix') . $name], $args);
			$hook_run = true;
		}

		if (!$hook_run || $persist) {
			if (method_exists($this->app(), $this->app()->config('hook_prefix') . $name))
				$res['app'] = call_user_func_array([$this->app(), $this->app()->config('hook_prefix') . $name], $args);
		}

		return $persist ? $res : current($res);
	}

	public function response($success = NULL, $messages = [], $data = NULL) {
		return new \Rona\Response\Response($success, $messages, $data);
	}
}
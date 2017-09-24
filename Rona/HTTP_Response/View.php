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

class View {

	protected $active_module;

	public $template = false;

	public $components = [];

	public function set_active_module(\Rona\Module $active_module) {
		$this->active_module = $active_module;
	}

	public function template($template): self {

		// If false is passed in, set the template property back to false.
		if ($template === false)
			$this->template = false;

		// Otherwise, set the template.
		else
			$this->template = ['module' => $this->active_module, 'template' => $template];

		// Response
		return $this;
	}

	protected function component(string $placement, string $placeholder, $items, string $type): self {

		$arr = [
			'module'		=> $this->active_module,
			'type'			=> $type,
			'items'			=> (array) $items
		];

		if (!isset($this->components[$placeholder]['first']))
			$this->components[$placeholder]['first'] = [];

		if (!isset($this->components[$placeholder]['middle']))
			$this->components[$placeholder]['middle'] = [];

		if (!isset($this->components[$placeholder]['last']))
			$this->components[$placeholder]['last'] = [];

		switch ($placement) {

			case 'first':
				array_unshift($this->components[$placeholder]['first'], $arr);
				break;

			case 'prepend':
				array_unshift($this->components[$placeholder]['middle'], $arr);
				break;

			case 'set':
				$this->components[$placeholder]['first'] = [];
				$this->components[$placeholder]['middle'] = [$arr];
				$this->components[$placeholder]['last'] = [];
				break;

			case 'append':
				$this->components[$placeholder]['middle'][] = $arr;
				break;

			case 'last':
				$this->components[$placeholder]['last'][] = $arr;
				break;
		}

		return $this;
	}

	public function first_stylesheet(string $placeholder, $items): self {
		return $this->component('first', $placeholder, $items, 'stylesheet');
	}

	public function prepend_stylesheet(string $placeholder, $items): self {
		return $this->component('prepend', $placeholder, $items, 'stylesheet');
	}

	public function set_stylesheet(string $placeholder, $items): self {
		return $this->component('set', $placeholder, $items, 'stylesheet');
	}

	public function append_stylesheet(string $placeholder, $items): self {
		return $this->component('append', $placeholder, $items, 'stylesheet');
	}

	public function last_stylesheet(string $placeholder, $items): self {
		return $this->component('last', $placeholder, $items, 'stylesheet');
	}

	public function first_javascript(string $placeholder, $items): self {
		return $this->component('first', $placeholder, $items, 'javascript');
	}

	public function prepend_javascript(string $placeholder, $items): self {
		return $this->component('prepend', $placeholder, $items, 'javascript');
	}

	public function set_javascript(string $placeholder, $items): self {
		return $this->component('set', $placeholder, $items, 'javascript');
	}

	public function append_javascript(string $placeholder, $items): self {
		return $this->component('append', $placeholder, $items, 'javascript');
	}

	public function last_javascript(string $placeholder, $items): self {
		return $this->component('last', $placeholder, $items, 'javascript');
	}

	public function first_file(string $placeholder, $items): self {
		return $this->component('first', $placeholder, $items, 'file');
	}

	public function prepend_file(string $placeholder, $items): self {
		return $this->component('prepend', $placeholder, $items, 'file');
	}

	public function set_file(string $placeholder, $items): self {
		return $this->component('set', $placeholder, $items, 'file');
	}

	public function append_file(string $placeholder, $items): self {
		return $this->component('append', $placeholder, $items, 'file');
	}

	public function last_file(string $placeholder, $items): self {
		return $this->component('last', $placeholder, $items, 'file');
	}

	public function first_content(string $placeholder, $items): self {
		return $this->component('first', $placeholder, $items, 'content');
	}

	public function prepend_content(string $placeholder, $items): self {
		return $this->component('prepend', $placeholder, $items, 'content');
	}

	public function set_content(string $placeholder, $items): self {
		return $this->component('set', $placeholder, $items, 'content');
	}

	public function append_content(string $placeholder, $items): self {
		return $this->component('append', $placeholder, $items, 'content');
	}

	public function last_content(string $placeholder, $items): self {
		return $this->component('last', $placeholder, $items, 'content');
	}
}
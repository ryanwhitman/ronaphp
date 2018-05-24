<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.2.0
 */

namespace Rona\HTTP_Response;

class View {

	protected $current_controller_module;

	public $template = false;

	public $components = [];

	public function set_current_controller_module(\Rona\Module $current_controller_module) {
		$this->current_controller_module = $current_controller_module;
	}

	public function template($template, array $data = []): self {

		// If false is passed in, set the template property back to false.
		if ($template === false)
			$this->template = false;

		// Otherwise, set the template.
		else {
			$this->template = [
				'module'	=> $this->current_controller_module,
				'template'	=> $template,
				'data'		=> $data
			];
		}

		// Response
		return $this;
	}

	protected function component(string $type, string $placement, string $placeholder, $items, array $data = []): self {

		$arr = [
			'module'		=> $this->current_controller_module,
			'type'			=> $type,
			'items'			=> (array) $items,
			'data'			=> $data
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

	public function first_stylesheet(string $placeholder, $items, array $data = []): self {
		return $this->component('stylesheet', 'first', $placeholder, $items, $data);
	}

	public function prepend_stylesheet(string $placeholder, $items, array $data = []): self {
		return $this->component('stylesheet', 'prepend', $placeholder, $items, $data);
	}

	public function set_stylesheet(string $placeholder, $items, array $data = []): self {
		return $this->component('stylesheet', 'set', $placeholder, $items, $data);
	}

	public function append_stylesheet(string $placeholder, $items, array $data = []): self {
		return $this->component('stylesheet', 'append', $placeholder, $items, $data);
	}

	public function last_stylesheet(string $placeholder, $items, array $data = []): self {
		return $this->component('stylesheet', 'last', $placeholder, $items, $data);
	}

	public function first_javascript(string $placeholder, $items, array $data = []): self {
		return $this->component('javascript', 'first', $placeholder, $items, $data);
	}

	public function prepend_javascript(string $placeholder, $items, array $data = []): self {
		return $this->component('javascript', 'prepend', $placeholder, $items, $data);
	}

	public function set_javascript(string $placeholder, $items, array $data = []): self {
		return $this->component('javascript', 'set', $placeholder, $items, $data);
	}

	public function append_javascript(string $placeholder, $items, array $data = []): self {
		return $this->component('javascript', 'append', $placeholder, $items, $data);
	}

	public function last_javascript(string $placeholder, $items, array $data = []): self {
		return $this->component('javascript', 'last', $placeholder, $items, $data);
	}

	public function first_file(string $placeholder, $items, array $data = []): self {
		return $this->component('file', 'first', $placeholder, $items, $data);
	}

	public function prepend_file(string $placeholder, $items, array $data = []): self {
		return $this->component('file', 'prepend', $placeholder, $items, $data);
	}

	public function set_file(string $placeholder, $items, array $data = []): self {
		return $this->component('file', 'set', $placeholder, $items, $data);
	}

	public function append_file(string $placeholder, $items, array $data = []): self {
		return $this->component('file', 'append', $placeholder, $items, $data);
	}

	public function last_file(string $placeholder, $items, array $data = []): self {
		return $this->component('file', 'last', $placeholder, $items, $data);
	}

	public function first_content(string $placeholder, $items): self {
		return $this->component('content', 'first', $placeholder, $items);
	}

	public function prepend_content(string $placeholder, $items): self {
		return $this->component('content', 'prepend', $placeholder, $items);
	}

	public function set_content(string $placeholder, $items): self {
		return $this->component('content', 'set', $placeholder, $items);
	}

	public function append_content(string $placeholder, $items): self {
		return $this->component('content', 'append', $placeholder, $items);
	}

	public function last_content(string $placeholder, $items): self {
		return $this->component('content', 'last', $placeholder, $items);
	}
}
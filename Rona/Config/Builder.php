<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.0.0
 */

namespace Rona\Config;

class Builder {

	protected $config_inst;

	protected $starting_path;

	protected $is_const;

	public function __construct(Config $config_inst, string $starting_path, bool $is_const) {
		$this->config_inst = $config_inst;
		$this->starting_path = $starting_path;
		$this->is_const = $is_const;
	}

	public function _(string $path, $val = Config::UNDEFINED): self {

		if ($val === Config::UNDEFINED)
			return new static($this->config_inst, $this->starting_path . '.' . $path, $this->is_const);

		$this->config_inst->m($this->starting_path . '.' . $path, $val, $this->is_const);

		return $this;
	}
}
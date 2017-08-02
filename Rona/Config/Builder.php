<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
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

	public function _(string $path, $val = Config::RONA_UNDEFINED): self {

		if ($val === Config::RONA_UNDEFINED)
			return new static($this->config_inst, $this->starting_path . '.' . $path, $this->is_const);

		$this->config_inst->m($this->starting_path . '.' . $path, $val, $this->is_const);
		
		return $this;
	}
}
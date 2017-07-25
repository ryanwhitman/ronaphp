<?php

namespace Rona\Config;

class Config_Builder {

	protected $config_inst;

	protected $starting_path;

	protected $is_const;
	
	public function __construct(Config $config_inst, $starting_path, $is_const) {
		$this->config_inst = $config_inst;
		$this->starting_path = $starting_path;
		$this->is_const = $is_const;
	}

	public function _($path, $val = RONA_UNDEFINED) {

		if ($val === RONA_UNDEFINED)
			return new static($this->config_inst, $this->starting_path . '.' . $path, $this->is_const);

		$this->config_inst->m($this->starting_path . '.' . $path, $val, $this->is_const);
		
		return $this;
	}
}
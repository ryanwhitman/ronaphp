<?php

class Config_ {

	private
		$starting_path,
		$is_const;
	
	public function __construct($starting_path, $is_const) {
		$this->starting_path = $starting_path;
		$this->is_const = $is_const;
	}

	public function _($path, $val = NULL) {

		if (is_null($val))
			return new self($this->starting_path . '.' . $path, $this->is_const);

		Config::m($this->starting_path . '.' . $path, $val, $this->is_const);
		
		return $this;
	}
}

?>
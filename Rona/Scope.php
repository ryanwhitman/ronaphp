<?php
/**
 * @package RonaPHP
 * @copyright Copyright (c) 2017 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT   MIT
 * @version 1.0.0 - beta
 * @link https://github.com/RyanWhitman/ronaphp/tree/v1
 * @since 1.0.0 - beta
 */

namespace Rona;

class Scope {

	protected $rona_form_data = [];

	public function get_form_data(string $item = NULL) {

		return is_null($item) ? $this->rona_form_data : (isset($this->rona_form_data[$item]) ? $this->rona_form_data[$item] : NULL);
	}

	public function set_form_data(array $data) {
		$this->rona_form_data = array_replace_recursive($this->rona_form_data, $data);
	}
}
<?php

namespace Rona\Modules\Rona_Logger\Param_Filters;

/**
 * Param filters for a single entry.
 */
class Entry extends \Rona\Param_Filter_Group {

	/**
	 * @see parent class
	 */
	protected function register_filters() {

		/**
		 * Tag
		 */
		$this->copy(['rona', 'general.string'], 'tag', [
			'min_length'	=> $this->config('tag.min_length'),
			'max_length'	=> $this->config('tag.max_length')
		]);

		/**
		 * Description
		 */
		$this->copy(['rona', 'general.string'], 'description', [
			'min_length'	=> $this->config('description.min_length'),
			'max_length'	=> $this->config('description.max_length')
		]);
	}
}
<?php

namespace Rona\Modules\Rona_Logger\Procedures;

/**
 * Procedures for a single entry.
 */
class Entry extends \Rona\Procedure_Group {

	/**
	 * @see parent class
	 */
	protected function register_procedures() {

		/**
		 * Create an entry.
		 */
		$this->register('create',
			function($param_exam, $raw_input) {
				$param_exam->reqd_param('tag', 'entry.tag');
				$param_exam->reqd_param('description', 'entry.description');
				$param_exam->opt_param('data', ['rona', 'general.array']);
			},
			function($input) {

				// Set the data.
				$data = isset($input['data']) && !$this->get_module_resource('rona', 'helper')->is_empty_string($input['data']) ? json_encode($input['data']) : NULL;

				// Insert the entry into the DB.
				$mysqli = $this->get_module_resource('rona', 'db')->mysqli;
				$stmt = $mysqli->prepare("INSERT INTO {$this->config('db_table_prefix')}_entries SET tag = ?, description = ?, data = ?;");
				$stmt->bind_param('sss', $input['tag'], $input['description'], $data);
				$is_success = $stmt->execute();
				$stmt->close();
				if (!$is_success)
					return $this->failure('unknown_error');

				// Success
				return $this->success('entry_created');
			}
		);

		/**
		 * Get an entry.
		 */
		$this->register('get',
			function($param_exam, $raw_input) {
				$param_exam->reqd_param('entry_id', ['rona', 'general.integer']);
			},
			function($input) {

				// Get the entry.
				$mysqli = $this->get_module_resource('rona', 'db')->mysqli;
				$stmt = $mysqli->prepare("SELECT * FROM {$this->config('db_table_prefix')}_entries WHERE entry_id = ?;");
				$stmt->bind_param('i', $input['entry_id']);
				$is_success = $stmt->execute();
				$rs = $stmt->get_result();
				$stmt->close();
				if (!$is_success)
					return $this->failure('unknown_error');
				if ($rs->num_rows !== 1)
					return $this->failure('not_found');
				$entry = $rs->fetch_assoc();
				$entry['data'] = $this->get_module_resource('rona', 'helper')->is_null_or_empty_string($entry['data']) ? NULL : json_decode($entry['data']);

				// Success
				return $this->success('entry_retrieved', $entry);
			}
		);
	}
}
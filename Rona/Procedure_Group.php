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

class Procedure_Group extends Module_Extension {

	protected $name;

	protected $module;

	protected $procedures = [];

	public function __construct(string $name, \Rona\Module $module) {

		// Set the name.
		$this->name = $name;

		// Set the module.
		$this->module = $module;

		// Register the procedures.
		$this->register_procedures();
	}

	protected function register_procedures() {}

	public function register_procedure(string $procedure_name, \Closure $param_exam_callback, \Closure $execute_callback) {

		// Ensure procedure wasn't already registered.
		if (isset($this->procedures[$procedure_name]))
			throw new \Exception("The procedure '$procedure_name' has already been registered.");

		$this->procedures[$procedure_name] = [
			'param_exam_callback'	=> $param_exam_callback,
			'execute_callback'		=> $execute_callback
		];
	}

	public function run_procedure(string $procedure_name, array $raw_input = []): Response {

		// Ensure procedure has been registered.
		if (!isset($this->procedures[$procedure_name]))
			throw new \Exception("A procedure named '$procedure_name' has not been registered.");

		// Create a new Param Exam object.
		$param_exam = new \Rona\Param_Exam($this->module);

		// Run the procedure's Param Exam callback.
		$this->procedures[$procedure_name]['param_exam_callback']($param_exam, $raw_input);
		
		// Examine the params. If a success is not returned, do not proceed with executing procedure.
		$res = $param_exam->examine($raw_input);
		if (!$res->success)
			return $res;
		$processed_input = $res->data;

		// Execute the procedure.
		$res = $this->procedures[$procedure_name]['execute_callback']($processed_input);

		// Ensure the response is a \Rona\Response object.
		if (!is_a($res, '\Rona\Response'))
			throw new \Exception("The procedure '$procedure_name' did not return a valid response object.");

		// Response
		return $res;
	}
}
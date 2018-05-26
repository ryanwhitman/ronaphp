<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.3.1
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

	public function register(string $procedure_name, \Closure $param_exam_callback, \Closure $execute_callback) {

		// Ensure procedure wasn't already registered.
		if (isset($this->procedures[$procedure_name]))
			throw new \Exception("The procedure '$procedure_name' has already been registered.");

		$this->procedures[$procedure_name] = [
			'param_exam_callback'	=> $param_exam_callback,
			'execute_callback'		=> $execute_callback
		];
	}

	/**
	 * Get a procedure.
	 *
	 * @param  string         $procedure_name   The procedure name.
	 * @return array                            The procedure.
	 */
	public function get(string $procedure_name): array {

		// Ensure the procedure has been registered.
		if (!isset($this->procedures[$procedure_name]))
			throw new \Exception("A procedure named '$procedure_name' has not been registered.");

		// Return the procedure.
		return $this->procedures[$procedure_name];
	}

	/**
	 * Process a procedure's input with param exam.
	 *
	 * @param  string         $procedure_name   The procedure name.
	 * @param  array          $raw_input        The raw input that is to be processed.
	 * @return Response
	 */
	public function process_input(string $procedure_name, array $raw_input = []): Param_Exam_Response {

		// Get the procedure.
		$procedure = $this->get($procedure_name);

		// Create a new Param Exam object.
		$param_exam = new \Rona\Param_Exam($this->module);

		// Run the procedure's Param Exam callback.
		$procedure['param_exam_callback']($param_exam, $raw_input);

		// Examine the params and return the response.
		return $param_exam->examine($raw_input);
	}

	/**
	 * Execute a procedure's callback, with the processed input passed in instead of being run thru param exam.
	 *
	 * @param  string         $procedure_name   The procedure name.
	 * @param  array          $processed_input  Input that has already been run thru param exam.
	 * @return Response
	 */
	public function execute(string $procedure_name, array $processed_input = []): Procedure_Response {

		// Get the procedure.
		$procedure = $this->get($procedure_name);

		// Execute the procedure.
		$res = $procedure['execute_callback']($processed_input);

		// Ensure the response is the correct type.
		if (!is_a($res, '\Rona\Procedure_Response'))
			throw new \Exception("The procedure did not return an instance of \Rona\Procedure_Response.");

		// Response
		return $res;
	}

	/**
	 * Fully run a procedure: Process its input and execute it.
	 *
	 * @param  string         $procedure_name   The procedure name.
	 * @param  array          $raw_input        The raw input that is to be processed and injected into the procedure.
	 * @return mixed
	 */
	public function run(string $procedure_name, array $raw_input = []) {

		// Process the input.
		$res = $this->process_input($procedure_name, $raw_input);
		if (!$res->success)
			return $res;
		$processed_input = $res->data;

		// Execute the procedure and return the response.
		return $this->execute($procedure_name, $processed_input);
	}

	public function success(string $tag, $data = NULL): Procedure_Response {
		return new Procedure_Response(true, $tag, $data);
	}

	public function failure(string $tag, $data = NULL): Procedure_Response {
		return new Procedure_Response(false, $tag, $data);
	}
}
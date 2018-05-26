<?php
/**
 * @package RonaPHP
 * @author Ryan Whitman ryanawhitman@gmail.com
 * @copyright Copyright (c) 2018 Ryan Whitman (https://ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT
 * @link https://github.com/RyanWhitman/ronaphp
 * @version 1.3.1
 */

namespace Rona\Modules\Rona\Resources;

/**
 * A mysqli database class.
 */
class Db {

	/**
	 * The mysqli object.
	 *
	 * @var \mysqli
	 */
	public $mysqli;

	/**
	 * The constructor.
	 *
	 * @param    string    $host        The DB host.
	 * @param    string    $username    The DB username.
	 * @param    string    $password    The DB password.
	 * @param    string    $name        The DB name.
	 */
	public function __construct(string $host, string $username, string $password, string $name) {

		// Establish the DB connection and store the result in an object property.
		$this->mysqli = new \mysqli($host, $username, $password, $name);
	}
}
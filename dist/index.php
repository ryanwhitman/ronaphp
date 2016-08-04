<?php
/**
 * This is the main index.php file that loads and runs the RonaPHP framework.
 *
 * @package RonaPHP
 * @copyright Copyright (c) 2016 Ryan Whitman (http://www.ryanwhitman.com)
 * @license https://opensource.org/licenses/MIT MIT
 * @version .5.4.1
 * @link https://github.com/RyanWhitman/ronaphp
 * @since .5.4.1
 */

require_once(__DIR__ . '/core/Rona.php');
Rona::init();

/* Run additional code here */

Rona::run();
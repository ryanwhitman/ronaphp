<?php

/* Sample procedures */

Procedure::define('test')
	->execute(function($input, $input_raw) {

		return Response::true('', $input_raw);
	});

?>
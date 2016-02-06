<?php

/* Sample procedures */

Procedure::set('test')
	->execute(function($input, $input_raw) {

		return Response::set(true, '', $input_raw);
	});

?>
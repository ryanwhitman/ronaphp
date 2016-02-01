<?php

Procedure::procedure('test')
	->route()->get('test/{anything}')
	->execute(function($input, $raw) {
	
		return Response::true(['hi' => 'test', 'test'], $raw);
	});

?>
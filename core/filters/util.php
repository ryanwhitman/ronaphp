<?php

Filter::set('set_val', [
		'field'		=> '',
		'val'		=> ''
	], function($input, $options) {

		$data[$options['field']] = $options['val'];

		return Response::set(true, '', $data);
});

Filter::set('set_val_from_input', [
		'field'		=> '',
		'val'		=> []
	], function($input, $options) {

		$data[$options['field']] = Helper::array_get($input, $options['val']);

		return Response::set(true, '', $data);
});

?>
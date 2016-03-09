<?php

Filter::set('password', [
	'type'	=> 'standard'
	], function($input, $options) {

	// Get min. pw length
		$min_length = Config::get('user.min_pw_length');
	
	// Get password
		$password = Helper::array_get($input, 'password');
		$password = trim($password);

	// Validate password
		if (strlen($password) >= $min_length)
			return Response::set(true, '', ['password' => $password]);
		
		return Response::set(false, "A " . ($options['type'] == 'update' ? 'new ' : '') . "password of at least {$min_length} character" . ($min_length == 1 ? "" : "s") . " is required.");
});

Filter::set('name', [
	'reqd'		=>	true,
	'field'		=> 'name',
	'var1'		=> 'name'
	], function($input, $options) {

	// Get name
		$name = Helper::array_get($input, $options['field']);
		$name = Helper::trim_full($name);

	// Is it required?
		if (!$options['reqd'] && Helper::is_nullOrEmptyString($name))
			return Response::set(true);

	// Validate name
		if (Helper::is_persons_name($name))
			return Response::set(true, '', [$options['field'] => $name]);

	return Response::set(false, "A valid {$options['var1']} is required.");
});

?>
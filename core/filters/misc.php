<?php

Filter::set('email', [
	'reqd'		=> true,
	'field'		=> 'email'
	], function($input, $options) {

	// Get email
		$email = Helper::array_get($input, $options['field']);

	// Is it required?
		if (!$options['reqd'] && Helper::is_nullOrEmptyString($email))
			return Response::set(true);
		
	$email = Helper::get_email($email);
	if (Helper::is_email($email))
		return Response::set(true, '', [$options['field'] => $email]);
	
	return Response::set(false, 'A valid email address is required.');
});

Filter::set('anything', [
	'field'		=> ''
	], function($input, $options) {

	$val = Helper::array_get($input, $options['field']);
	$val = trim($val);

	return Response::set(true, '', [$options['field'] => $val]);
});

Filter::set('chars', [
	'field'				=> '',
	'var1'				=> '',
	'dependent_boolean'	=> ''
	], function($input, $options) {

	// Get field
		$val = Helper::array_get($input, $options['field']);
		$val = trim($val);

	// Is this field dependent on a boolean field being true?
		if (!Helper::is_emptyString($options['dependent_boolean']) && !Helper::array_get($input, $options['dependent_boolean']))
			return Response::set(true);
	
	if (!Helper::is_emptyString($val))
		return Response::set(true, '', [$options['field'] => $val]);
	
	return Response::set(false, "{$options['var1']} is required.");
});

Filter::set('boolean', [
	'reqd'				=>	false,
	'field'				=> '',
	'default'			=> '0',
	'return_tinyint'	=> true
	], function($input, $options) {

	// Get field
		$val = Helper::array_get($input, $options['field']);

	// Is it required? If not, set default
		if (!$options['reqd'] && Helper::is_nullOrEmptyString($val) && !Helper::is_nullOrEmptyString($options['default']))
			$val = $options['default'];

	// Convert similar inputs to a boolean
		if (
			$val === 'false' ||
			$val === 'off' ||
			$val === 'no' ||
			$val === 'n' ||
			$val === '0' ||
			$val === 0
		)
			$val = false;

		elseif (
			$val === 'true' ||
			$val === 'on' ||
			$val === 'yes' ||
			$val === 'y' ||
			$val === '1' ||
			$val === 1
		)
			$val = true;
		
	if (is_bool($val)) {

		if ($options['return_tinyint'])
			$val = $val == false ? '0' : '1';

		return Response::set(true, '', [$options['field'] => $val]);
	}
	
	return Response::set(false, "You must provide a true/false value for '{$options['field']}'.");
});

Filter::set('alphanumeric', [
		'reqd'		=> true,
		'field'		=> 'token'
	], function($input, $options) {

	// Get val
		$val = Helper::array_get($input, $options['field']);

	// Is it required?
		if (!$options['reqd'] && Helper::is_nullOrEmptyString($val))
			return Response::set(true);
		
	if (Helper::is_alphanumeric($val))
		return Response::set(true, '', [$options['field'] => $val]);
	
	return Response::set(false, "A {$options['field']} is required.");
});

?>
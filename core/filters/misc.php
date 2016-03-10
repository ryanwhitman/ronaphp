<?php

Filter::set('email', [
	'reqd'		=> true,
	'field'		=> 'email'
	], function($input, $options) {

	// Get email
		$email = Helper::array_get($input, $options['field']);
		$email = trim($email);

	// Is it required?
		if (!$options['reqd'] && Helper::is_nullOrEmptyString($email))
			return Response::set(true);
		
	$email = Helper::get_email($email);
	if (Helper::is_email($email))
		return Response::set(true, '', [$options['field'] => $email]);
	
	return Response::set(false, 'A valid email address is required.');
});

Filter::set('emails', [
	'reqd'			=> true,
	'field'			=> 'emails',
	'all_match'		=> true,
	'at_least_one'	=> true
	], function($input, $options) {
	
	// Get emails
		$emails = Helper::array_get($input, $options['field'], []);

	// Is it required?
		if (!$options['reqd'] && Helper::is_nullOrEmptyString($emails))
			return Response::set(true);
			
	// Get the email address count
		$initial_count = count($emails);
			
	// Reduce the email array to only contain legitimate email addresses
		$emails = Helper::get_emails($emails);
			
	// if 'at_least_one' is set to true, then ensure at least 1 legitimate email address was provided.
		if ($options['at_least_one'] && count($emails) == 0)
			return App::ret(false, 'You must provide at least 1 email address.');
		
	// If 'all_match' is set to true, then the initial count must be the same as the new count
		if ($options['all_match'] && count($emails) != $initial_count)
			return App::ret(false, 'At least 1 of the email addresses you provided was not a valid email address.');
			
	// Response
		return Response::set(true, '', [$options['field'] => $emails]);
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
	'reqd'				=> false,
	'field'				=> '',
	'default'			=> false,
	'return_tinyint'	=> true
	], function($input, $options) {

	// Get val
		$val = Helper::array_get($input, $options['field']);
		$val = trim($val);

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

Filter::set('numeric', [
		'reqd'		=> true,
		'field'		=> ''
	], function($input, $options) {

	// Get val
		$val = Helper::array_get($input, $options['field']);
		$val = trim($val);

	// Is it required?
		if (!$options['reqd'] && Helper::is_nullOrEmptyString($val))
			return Response::set(true);
		
	if (Helper::is_numeric($val))
		return Response::set(true, '', [$options['field'] => $val]);
	
	return Response::set(false, "A valid {$options['field']} is required.");
});

Filter::set('alphanumeric', [
		'reqd'		=> true,
		'field'		=> ''
	], function($input, $options) {

	// Get val
		$val = Helper::array_get($input, $options['field']);
		$val = trim($val);

	// Is it required?
		if (!$options['reqd'] && Helper::is_nullOrEmptyString($val))
			return Response::set(true);
		
	if (Helper::is_alphanumeric($val))
		return Response::set(true, '', [$options['field'] => $val]);
	
	return Response::set(false, "A valid {$options['field']} is required.");
});

Filter::set('persons_name', [
	'reqd'		=> true,
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

Filter::set('password', [
	'reqd'			=> true,
	'field'			=> 'password',
	'var1'			=> 'password',
	'min_length'	=> 8,
	'max_length'	=> 30
	], function($input, $options) {

	// Get password
		$password = Helper::array_get($input, $options['field']);
		$password = trim($password);

	// Is it required?
		if (!$options['reqd'] && Helper::is_nullOrEmptyString($password))
			return Response::set(true);

	// Validate password
		if (strlen($password) >= $options['min_length'] && strlen($password) <= $options['max_length'])
			return Response::set(true, '', [$options['field'] => $password]);
		
		return Response::set(false, "A {$options['var1']} of at least {$options['min_length']} character" . ($options['min_length'] == 1 ? "" : "s") . " is required.");
});

?>
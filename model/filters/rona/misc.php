<?php

Filter::set('persons_name', [
	'reqd'		=>	true,
	'field'		=> 'name',
	'msg_var1'	=> 'name'
	], function($input, $options) {

	// Get name
		$name = isset($input[$options['field']]) ? $input[$options['field']] : '';

	// Is an empty ok?
		if (empty($name) && !$options['reqd']) return App::ret(true);
	
	$name = Helper::trim_full($name);
	if (Helper::is_persons_name($name)) {
		return App::ret(true, '', [$options['field'] => $name]);
	}
	return App::ret(false, "A valid {$options['msg_var1']} is required.");
});

Filter::set('name', [
	'reqd'		=>	true,
	'field'		=> 'name',
	'msg_var1'	=> 'name'
	], function($input, $options) {

	// Get name
		$name = isset($input[$options['field']]) ? $input[$options['field']] : '';

	// Is an empty ok?
		if (empty($name) && !$options['reqd']) return App::ret(true);
	
	$name = Helper::trim_full($name);
	if (strlen($name) > 0) {
		return App::ret(true, '', [$options['field'] => $name]);
	}
	return App::ret(false, "A {$options['msg_var1']} is required.");
});

Filter::set('email', [
	'reqd'	=>	true
	], function($input, $options) {

	// Get email
		$email = isset($input['email']) ? $input['email'] : '';

	// Is an empty ok?
		if (empty($email) && !$options['reqd']) return App::ret(true);
		
	$email = Helper::get_email($email);
	if (Helper::is_email($email)) {
		return App::ret(true, '', ['email' => $email]);
	}
	return App::ret(false, 'A valid email address is required.');
});

Filter::set('emails', [
	'reqd'			=>	true,
	'field'			=> 'emails',
	'all_match'		=> true,
	'at_least_one'	=> true
	], function($input, $options) {
	
	// Get emails
		$emails = isset($input[$options['field']]) && is_array($input[$options['field']]) ? $input[$options['field']] : [];

	// Is an empty ok?
		if (empty($emails) && !$options['reqd']) return App::ret(true);
			
	// Get the email address count
		$initial_count = count($emails);
			
	// Reduce the email array to only contain legitimate email addresses
		$emails = Helper::get_emails($emails);
			
	// if 'at_least_one' is set to true, then ensure at least 1 legitimate email address was provided.
		if ($options['at_least_one'] && count($emails) == 0)
			return App::ret(false, 'You must provide at least 1 email address.');
		
	// If 'all_match' is set to true, then the initial count must be the same as the new count
		if ($options['all_match'] && count($emails) != $initial_count)
			return App::ret(false, 'At least 1 of the email addresses you provided was not an actual email address.');
			
	// Return
		return App::ret(true, '', [$options['field'] => $emails]);
});

Filter::set('chars', [
	'field'		=> 'abc_xyz',
	'msg_var1'	=> ''
	], function($input, $options) {

	// Get field
		$field = isset($input[$options['field']]) ? $input[$options['field']] : '';
	
	$field = trim($field);
	if (strlen($field) > 0) {
		return App::ret(true, '', [$options['field'] => $field]);
	}
	return App::ret(false, "{$options['msg_var1']} is required.");
});

Filter::set('boolean', [
	'reqd'		=>	true,
	'field'		=> 'abc_xyz',
	'msg_var1'	=> ''
	], function($input, $options) {

	// Get field
		$field = isset($input[$options['field']]) ? $input[$options['field']] : '';

	// Is an empty ok?
		if (empty($field) && $field !== 0 && $field !== '0' && !$options['reqd']) return App::ret(true);
		
	if ($field == '0' || $field == '1') {
		return App::ret(true, '', [$options['field'] => $field]);
	}		
	return App::ret(false, "You must provide either a '0' or a '1' for '{$options['msg_var1']}.'");
});

Filter::set('date', [
	'reqd'			=>	true,
	'field'			=>	'date',
	'msg_var1'		=>	'date',
	'output_format'	=>	'Y-m-d'
	], function($input, $options) {
	
	// Get field
		$field = isset($input[$options['field']]) ? $input[$options['field']] : '';

	// Is an empty ok?
		if (empty($field) && !$options['reqd']) return App::ret(true);
		
	if (!empty($field)) {
		$field = date($options['output_format'], strtotime($field));
		return App::ret(true, '', [$options['field'] => $field]);
	}
	
	return App::ret(false, "You must provide a valid {$options['msg_var1']}.");
});

Filter::set('password', [
	'reqd'			=>	true,
	'field'			=>	'password',
	'msg_var1'		=>	'password',
	'min_length'	=>	USER_MIN_PW_LENGTH
	], function($input, $options) {
	
	// Get password
		$password = Helper::array_get($input, $options['field']);
	
	// Is an empty ok?
		if (empty($password) && !$options['reqd']) return App::ret(true);
		
	$password = trim($password);
	if (strlen($password) >= $options['min_length']) {
		return App::ret(true, '', [$options['field'] => $password]);
	}
	
	return App::ret(false, "A {$options['msg_var1']} of at least {$options['min_length']} character" . ($options['min_length'] == 1 ? "" : "s") . " is required.");
});

?>
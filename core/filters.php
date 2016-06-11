<?php

Filter::set('string', [
		'trim_full'		=> Config::get('rona.filters.options.string.trim_full'), // true or false
		'trim'			=> Config::get('rona.filters.options.string.trim') // false disables, mask will be used otherwise
	], function($val, $label, $options) {

	if (is_string($val)) {

		if ($options['trim_full'])
			$val = Helper::trim_full($val);

		if ($options['trim'] !== false)
			$val = trim($val, $options['trim']);

		return Response::set(true, Helper::func_or(Config::get('rona.filters.messages.string.success'), get_defined_vars()), $val);
	}

	return Response::set(false, Helper::func_or(Config::get('rona.filters.messages.string.failure'), get_defined_vars()));
});

Filter::set('email', [], function($val, $label, $options) {
		
	$val = Helper::get_email($val);
	if (Helper::is_email($val))
		return Response::set(true, Helper::func_or(Config::get('rona.filters.messages.email.success'), get_defined_vars()), $val);

	return Response::set(false, Helper::func_or(Config::get('rona.filters.messages.email.failure'), get_defined_vars()));
});

Filter::set('emails', [
		'all_match'		=> Config::get('rona.filters.options.emails.all_match')
	], function($val, $label, $options) {
	
	// Ensure $val is an array
	$val = (array) $val;
			
	// Get the initial email address count
	$initial_count = count($val);
			
	// Reduce the email address array to only contain legitimate email addresses
	$val = Helper::get_emails($val);

	// Get the refined email address count
	$refined_count = count($val);
			
	// if 'all_match' is set to false, then ensure at least 1 legitimate email address was provided.
	if (!$options['all_match']) {
		if ($refined_count == 0)
			return Response::set(false, Helper::func_or(Config::get('rona.filters.messages.emails.failure.at_least_1'), get_defined_vars()));
	}

	// If 'all_match' is set to true, then the initial count must be the same as the new count
	else {
		
		if ($refined_count != $initial_count) {
			$num_invalids = $initial_count - $refined_count;
			return Response::set(false, Helper::func_or(Config::get('rona.filters.messages.emails.failure.all_must_match'), get_defined_vars()));
		}
	}			
	
	return Response::set(true, Helper::func_or(Config::get('rona.filters.messages.emails.success'), get_defined_vars()), $val);
});

Filter::set('boolean', [
		'return_int'	=> Config::get('rona.filters.options.boolean.return_int')
	], function($val, $label, $options) {

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

	else if (
		$val === 'true' ||
		$val === 'on' ||
		$val === 'yes' ||
		$val === 'y' ||
		$val === '1' ||
		$val === 1
	)
		$val = true;
		
	if (is_bool($val)) {

		if ($options['return_int'])
			$val = $val == false ? 0 : 1;
		
		return Response::set(true, Helper::func_or(Config::get('rona.filters.messages.boolean.success'), get_defined_vars()), $val);
	}
	
	return Response::set(false, Helper::func_or(Config::get('rona.filters.messages.boolean.failure'), get_defined_vars()));
});

Filter::set('persons_name', [], function($val, $label, $options) {

	$val = Helper::trim_full($val);

	if (Helper::is_persons_name($val))
		return Response::set(true, Helper::func_or(Config::get('rona.filters.messages.persons_name.success'), get_defined_vars()), $val);
	
	return Response::set(false, Helper::func_or(Config::get('rona.filters.messages.persons_name.failure'), get_defined_vars()));
});

Filter::set('password', [
	'min_length'	=> Config::get('rona.filters.options.password.min_length'),
	'max_length'	=> Config::get('rona.filters.options.password.max_length')
	], function($val, $label, $options) {

	$val = trim($val);
	$pw_length = strlen($val);

	if ($pw_length >= $options['min_length'] && $pw_length <= $options['max_length'])
		return Response::set(true, Helper::func_or(Config::get('rona.filters.messages.password.success'), get_defined_vars()), $val);
	
	return Response::set(false, Helper::func_or(Config::get('rona.filters.messages.password.failure'), get_defined_vars()));
});

Filter::set('numeric', [], function($val, $label, $options) {

	$val = trim($val);

	if (Helper::is_numeric($val))
		return Response::set(true, Helper::func_or(Config::get('rona.filters.messages.numeric.success'), get_defined_vars()), $val);
	
	return Response::set(false, Helper::func_or(Config::get('rona.filters.messages.numeric.failure'), get_defined_vars()));
});

Filter::set('alphanumeric', [
		'case'		=> Config::get('rona.filters.options.alphanumeric.case')
	], function($val, $label, $options) {

	$case = $options['case'];
	$val = trim($val);

	if (Helper::is_alphanumeric($val, $case))
		return Response::set(true, Helper::func_or(Config::get('rona.filters.messages.alphanumeric.success'), get_defined_vars()), $val);
	
	return Response::set(false, Helper::func_or(Config::get('rona.filters.messages.alphanumeric.failure'), get_defined_vars()));
});

Filter::set('date', [
		'output_format'	=> Config::get('rona.filters.options.date.output_format')
	], function($val, $label, $options) {

	#** This function needs to be modified as it basically validates anything

	$date = date($options['output_format'], strtotime($val));

	$dt = DateTime::createFromFormat($options['output_format'], $date);
	if ($dt !== false && !array_sum($dt->getLastErrors()))
		return Response::set(true, Helper::func_or(Config::get('rona.filters.messages.date.success'), get_defined_vars()), $date);
	
	return Response::set(false, Helper::func_or(Config::get('rona.filters.messages.date.failure'), get_defined_vars()));
});
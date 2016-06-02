<?php

Filter::set('string', [
		'trim_full'		=> false, // true or false
		'trim'			=> ' ' // false disables, mask will be used otherwise
	], function($val, $label, $options) {

	if (is_string($val)) {

		if ($options['trim_full'])
			$val = Helper::trim_full($val);

		if ($options['trim'] !== false)
			$val = trim($val, $options['trim']);

		return Response::set(true, '', $val);	
	}

	return Response::set(false);
});

Filter::set('email', [], function($val, $label, $options) {
		
	$val = Helper::get_email($val);
	if (Helper::is_email($val))
		return Response::set(true, '', $val);
	
	return Response::set(false);
});

Filter::set('emails', [
		'all_match'			=> true
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
			return Response::set(false, "You must provide a valid $label.");
	}

	// If 'all_match' is set to true, then the initial count must be the same as the new count
	else {
		
		if ($refined_count != $initial_count) {
			$num_invalids = $initial_count - $refined_count;
			$label = $num_invalids == 1 ? $options['label_singular'] : $options['label_plural'];
			return Response::set(false, "You provided $num_invalids invalid " . Helper::pluralize($label) . ".");
		}
	}
			
	return Response::set(true, '', $val);
});

Filter::set('boolean', [
		'return_int'		=> false
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

		return Response::set(true, '', $val);
	}
	
	return Response::set(false);
});

Filter::set('persons_name', [], function($val, $label, $options) {

	$val = Helper::trim_full($val);

	if (Helper::is_persons_name($val))
		return Response::set(true, '', $val);

	return Response::set(false);
});

Filter::set('password', [
	'min_length'	=> 8,
	'max_length'	=> 30
	], function($val, $label, $options) {

	$val = trim($val);

	if (strlen($val) >= $options['min_length'] && strlen($val) <= $options['max_length'])
		return Response::set(true, '', $val);
		
	return Response::set(false, "The $label you provided is invalid. It must be between {$options['min_length']} and {$options['max_length']} characters in length.");
});

Filter::set('numeric', [], function($val, $label, $options) {

	$val = trim($val);

	if (Helper::is_numeric($val))
		return Response::set(true, '', $val);
	
	return Response::set(false);
});

Filter::set('alphanumeric', [
		'case'		=> 'ci'
	], function($val, $label, $options) {

	$case = $options['case'];
	$val = trim($val);

	if (Helper::is_alphanumeric($val, $case))
		return Response::set(true, '', $val);
	
	return Response::set(false);
});

Filter::set('date', [
		'output_format'	=> 'Y-m-d'
	], function($val, $label, $options) {

	#** This function needs to be modified as it basically validates anything

	$date = date($options['output_format'], strtotime($val));

	$dt = DateTime::createFromFormat($options['output_format'], $date);
	if ($dt !== false && !array_sum($dt->getLastErrors()))
		return Response::set(true, '', $date);

	return Response::set(false);
});
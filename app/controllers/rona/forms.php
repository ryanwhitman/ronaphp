<?php

Controller::define('get', function($procedure) {

	if (!Helper::get($procedure['success']))
		Helper::location(Helper::get(Request::route_options()['fail_url'], next_url()));

	foreach ($procedure['data'] as $field => $val)
		$_POST[$field] = Helper::get($val);
});

Controller::define('submit', function($procedure) {

	if (!Helper::get($procedure['success'])) {
		user_alert('form_error', $procedure['message']);
		return;
	}

	user_alert('light', $procedure['message']);
	Helper::location(Helper::get(Request::route_options()['success_url'], next_url()));
});

?>
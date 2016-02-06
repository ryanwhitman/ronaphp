<?php

Controller::set('get', function($scope) {

	if (!Helper::get($scope->procedure_res->success))
		Helper::location(Helper::get(Request::route_options()['fail_url'], next_url()));

	foreach ($scope->procedure_res->data as $field => $val)
		$_POST[$field] = Helper::get($val);
});

Controller::set('submit', function($scope) {

	if (!Helper::get($scope->procedure_res->success)) {
		user_alert('form_error', $scope->procedure_res->message);
		return;
	}

	user_alert('light', $scope->procedure_res->message);
	Helper::location(Helper::get(Request::route_options()['success_url'], next_url()));
});

?>
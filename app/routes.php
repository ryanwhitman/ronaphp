<?php

Route::no_route([
	'views'	=>	['sample_templates/standard', 'sample/no_route']
]);

Route::get('*', [
	'views'	=> ['view 1', 'view 2']
]);

Route::get('*', [
	'views'	=> ['view 1', 'view 2']
]);

Route::get('/test', [
	'views'	=> ['view 1', 'view 2']
]);

?>
<?php

/* Build App routes here */

Route::no_route([
	'views'	=>	['sample_templates/standard', 'sample/no_route']
]);

App::get('*', [
	'views'	=> '"tiger lion"'
]);

App::get('/test', [
	'view'	=> '"bear"'
]);

?>
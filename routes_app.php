<?php

/* Build App routes here */

Route::no_route([
	'views'	=>	['sample_templates/standard', 'sample/no_route']
]);

Route::get('', [
	'view'	=> '"Welcome to Rona."'
]);

?>
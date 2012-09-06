<?php defined('SYSPATH') or die('No direct script access.');

Route::set('nosql_example_mongo_all', 'example_mongo(/<action>(/<collection>(/<item_name>)))')
	->defaults(array(
		'controller'	=>	'rest_example'
	));
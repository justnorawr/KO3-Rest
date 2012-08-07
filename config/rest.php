<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @package		Kohana
 * @subpackage	Rest
 * @author		Nicholas Curtis	<nich.curtis@gmail.com>
 */

return array
(
	'sign_request'	=>	TRUE,

	'signature'	=>	array
	(
		// data index name for timestamp field
		'timestamp'		=>	'timstamp',

		// data index name for public api key
		'public_key'		=>	'public_key',

		// data index name for client signature api key
		'signature'		=>	'signature',
		
		// time difference to allow for variations in times on client/server
		// should be as low as possible to prevent replay attacks
		'replaytimeout'		=>	3000,

		// salt to use with php crypt function
		'salt'			=>	'$2a$10$',

		'debug'			=>	TRUE,
		'profile'			=>	TRUE,
	)
);

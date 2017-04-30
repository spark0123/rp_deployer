<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Default Remote Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify the default connection that will be used for SSH
	| operations. This name should correspond to a connection name below
	| in the server list. Each connection will be manually accessible.
	|
	*/

	'default' => 'stage',

	/*
	|--------------------------------------------------------------------------
	| Remote Server Connections
	|--------------------------------------------------------------------------
	|
	| These are the servers that will be accessible via the SSH task runner
	| facilities of Laravel. This feature radically simplifies executing
	| tasks on your servers, such as deploying out these applications.
	|
	*/

	'connections' => array(

		'stage' => array(
			'host'      => env('STAGE_FTP_HOST', ''),
			'username'  => env('STAGE_FTP_USERNAME', ''),
			'password'  => env('STAGE_FTP_PW', ''),
			'key'       => env('STAGE_FTP_KEY', ''),
			'keyphrase' => env('STAGE_FTP_KEYPHRASE', ''),
			'root'      => env('STAGE_FTP_ROOT', '/'),
		),
		'production' => array(
			'host'      => env('FTP_HOST', ''),
			'username'  => env('FTP_USERNAME', ''),
			'password'  => env('FTP_PW', ''),
			'key'       => env('FTP_KEY', ''),
			'keyphrase' => env('FTP_KEYPHRASE', ''),
			'root'      => env('FTP_ROOT', '/'),
		),

	),

	/*
	|--------------------------------------------------------------------------
	| Remote Server Groups
	|--------------------------------------------------------------------------
	|
	| Here you may list connections under a single group name, which allows
	| you to easily access all of the servers at once using a short name
	| that is extremely easy to remember, such as "web" or "database".
	|
	*/

	'groups' => array(

		'web' => array('production')

	),

);
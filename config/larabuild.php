<?php 

return [

	'app_name' => 'laravel',

	/**
	 * Set to NULL if you don't want to split your project folders.
	 */
	'new_public_folder' => 'public_html',

	// NOT IMPLEMENTED
	'compile_assets' => true,

	'artisan' => [
		'config:clear',
		'route:clear',
		'view:clear',
	],

	'files' => [
		'app',
		'bootstrap',
		'config',
		'public',
		'resources',
		'routes',
		'storage',
		'artisan',
		'package.json',
	],

	'files_to_edit' => [
		'public_html/index.php' => [
			"__DIR__.'/../bootstrap" => "__DIR__.'/../laravel/bootstrap",
		],
	],

	/**
	 * Flysystem's permissions config
	 */
	'file_permissions' => [
	    'file' => [
	        'public' => 0644,
	        'private' => 0644,
	    ],
	    'dir' => [
	        'public' => 0755,
	        'private' => 0755,
	    ],
    ],

    /**
     * NOT IMPLEMENTED
     * Parameters passed to each hook: BuildCommand $command, MountManager $manager, array $config
     */
    'hooks' => [
    	'before' => [
    		//Some::class,
    	],
    	'after' => [
    		//Some::class,
    	],
    ],
];

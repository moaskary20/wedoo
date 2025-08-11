<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;

class Filters extends BaseConfig
{
	/**
	 * Configures aliases for Filter classes to
	 * make reading things nicer and simpler.
	 *
	 * @var array
	 */
	public $aliases = [
		'csrf'     => CSRF::class,
		'toolbar'  => DebugToolbar::class,
		'honeypot' => Honeypot::class,
		'admin_sanitizer' => \App\Filters\AdminPanelSanitizer::class,
		'ImageFallback' => \App\Filters\ImageFallback::class,

	];

	/**
	 * List of filter aliases that are always
	 * applied before and after every request.
	 *
	 * @var array
	 */
	public $globals = [
		'before' => [
			
			// 'csrf' =>[
			// 	'except' =>[
			// 		"/api/[a-z0-9_-]+/[a-z0-9_-]+",
			// 		"/partner/api/[a-z0-9_-]+/[a-z0-9_-]+",
			// 	]
			// ],
			'admin_sanitizer' => ['except' => ['login/*', 'logout/*']], // Apply to all routes except login and logout
		],
		'after'  => [
			'ImageFallback',
			// 'toolbar' => [
			// 	'except'=>[
			// 		"/api/[a-z0-9_-]+/[a-z0-9_-]+",
			// 		"/partner/api/[a-z0-9_-]+/[a-z0-9_-]+",
			// 	]
			// ],
	
		],
	];

	/**
	 * List of filter aliases that works on a
	 * particular HTTP method (GET, POST, etc.).
	 *
	 * Example:
	 * 'post' => ['csrf', 'throttle']
	 *
	 * @var array
	 */
	public $methods = [
		// 'post' => ['throttle'],
		// 'get' => ['throttle'],

	];

	/**
	 * List of filter aliases that should run on any
	 * before or after URI patterns.
	 *
	 * Example:
	 * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
	 *
	 * @var array
	 */
	public $filters = [];
}

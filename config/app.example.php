<?php

return [
	'CacheConfig' => [
		'force' => false, // Debug mode => off
		'check' => null, // Auto
		'engine' => null, // File
		'when' => null, // Defaults to GET only
		'cacheTime' => null, // Only for non-engine (file only) cache
		'prefix' => null, // Only for non-engine (file only) cache
		'keyGenerator' => null, // a callable which must return a key for the given url
	],
];

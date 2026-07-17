<?php

declare(strict_types=1);

return [
	'routes' => [
		['name' => 'admin#index', 'url' => '/admin', 'verb' => 'GET'],
		['name' => 'admin#saveRoute', 'url' => '/admin/route', 'verb' => 'POST'],
		['name' => 'admin#deleteRoute', 'url' => '/admin/route/delete', 'verb' => 'POST'],
	],
];

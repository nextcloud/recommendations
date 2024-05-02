<?php

declare(strict_types=1);


/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
	'routes' => [
		['name' => 'settings#getSettings', 'url' => '/settings', 'verb' => 'GET'],
		['name' => 'settings#setSetting', 'url' => '/settings/{key}', 'verb' => 'PUT'],
		['name' => 'recommendation#index', 'url' => '/api/recommendations', 'verb' => 'GET'],
		['name' => 'recommendation#always', 'url' => '/api/recommendations/always', 'verb' => 'GET'],
	],
];

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
	],
	'ocs' => [
		['name' => 'recommendation#always', 'url' => '/api/v1/recommendations/always', 'verb' => 'GET'],
		['name' => 'recommendation#index', 'url' => '/api/v1/recommendations', 'verb' => 'GET'],
	],
];

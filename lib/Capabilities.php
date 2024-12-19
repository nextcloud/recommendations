<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations;

use OCA\Recommendations\AppInfo\Application;
use OCP\Capabilities\ICapability;
use OCP\IConfig;
use OCP\IUserSession;

class Capabilities implements ICapability {
	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct(
		private IUserSession $userSession,
		private IConfig $config,
	) {
	}

	/**
	 * @return array{
	 *     recommendations?: array{
	 *         enabled: bool,
	 *     },
	 * }
	 */
	public function getCapabilities(): array {
		$user = $this->userSession->getUser();
		if ($user === null) {
			return [];
		}

		$enabled = $this->config->getUserValue($user->getUID(), Application::APP_ID, 'enabled', 'true') === 'true';
		return [
			'recommendations' => [
				'enabled' => $enabled,
			],
		];
	}
}

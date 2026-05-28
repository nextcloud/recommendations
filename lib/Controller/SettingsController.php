<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Controller;

use Exception;
use OCA\Recommendations\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Config\IUserConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;

class SettingsController extends Controller {
	public function __construct(
		$appName,
		IRequest $request,
		private readonly IUserConfig $config,
		private readonly IUserSession $userSession,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @throws Exception
	 */
	#[NoAdminRequired]
	public function getSettings(): JSONResponse {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			throw new Exception('Not logged in');
		}
		return new JSONResponse([
			'enabled' => $this->config->getValueBool($user->getUID(), Application::APP_ID, 'enabled', true),
		]);
	}

	/**
	 * @throws Exception
	 */
	#[NoAdminRequired]
	public function setSetting(string $key, string $value): JSONResponse {
		$user = $this->userSession->getUser();
		if (!$user instanceof IUser) {
			throw new Exception('Not logged in');
		}
		$availableSettings = ['enabled'];
		if (!in_array($key, $availableSettings)) {
			return new JSONResponse([
				'message' => 'parameter does not exist',
			], Http::STATUS_UNPROCESSABLE_ENTITY);
		}
		if ($key === 'enabled') {
			$this->config->setValueBool($user->getUID(), Application::APP_ID, $key, $value === 'true' ? true : false);
		} else {
			$this->config->setValueString($user->getUID(), Application::APP_ID, $key, $value);
		}
		return new JSONResponse([
			'key' => $key,
			'value' => $value,
		]);
	}
}

<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Controller;

use Exception;
use OCP\AppFramework\OCSController;
use OCA\Recommendations\AppInfo\Application;
use OCA\Recommendations\Service\RecommendationService;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class RecommendationController extends OCSController {
	private IUserSession $userSession;
	private RecommendationService $recommendationService;
	private IConfig $config;

	public function __construct(IRequest $request,
		IUserSession $userSession,
		RecommendationService $recommendationService,
		IConfig $config) {
		parent::__construct(Application::APP_ID, $request);
		$this->userSession = $userSession;
		$this->recommendationService = $recommendationService;
		$this->config = $config;
	}

	/**
	 * @NoAdminRequired
	 * @return DataResponse
	 */
	public function index(): DataResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new Exception("Not logged in");
		}
		$response = [];
		$response['enabled'] = $this->config->getUserValue($user->getUID(), Application::APP_ID, 'enabled', 'true') === 'true';
		if ($response['enabled']) {
			$response['recommendations'] = $this->recommendationService->getRecommendations($user);
		}
		return new DataResponse(
			$response
		);
	}

	/**
	 * @NoAdminRequired
	 * @return DataResponse
	 */
	public function always(): DataResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new Exception("Not logged in");
		}
		$response = [
			'enabled' => $this->config->getUserValue($user->getUID(), Application::APP_ID, 'enabled', 'true') === 'true',
			'recommendations' => $this->recommendationService->getRecommendations($user),
		];
		return new DataResponse(
			$response
		);
	}
}

<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Controller;

use Exception;
use OCA\Recommendations\AppInfo\Application;
use OCA\Recommendations\Service\RecommendationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class RecommendationController extends Controller {
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
	 * @return JSONResponse
	 */
	public function index(): JSONResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new Exception("Not logged in");
		}
		$response = [];
		$response['enabled'] = $this->config->getUserValue($user->getUID(), Application::APP_ID, 'enabled', 'true') === 'true';
		if ($response['enabled']) {
			$response['recommendations'] = $this->recommendationService->getRecommendations($user);
		}
		return new JSONResponse(
			$response
		);
	}

	/**
	 * @NoAdminRequired
	 * @return JSONResponse
	 */
	public function always(): JSONResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new Exception("Not logged in");
		}
		$response = [
			'enabled' => $this->config->getUserValue($user->getUID(), Application::APP_ID, 'enabled', 'true') === 'true',
			'recommendations' => $this->recommendationService->getRecommendations($user),
		];
		return new JSONResponse(
			$response
		);
	}
}

<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Controller;

use Exception;
use OCA\Recommendations\AppInfo\Application;
use OCA\Recommendations\Service\IRecommendation;
use OCA\Recommendations\Service\RecommendationService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\Config\IUserConfig;
use OCP\IRequest;
use OCP\IUserSession;
use ResponseDefinitions;

/**
 * @psalm-import-type RecommendationsRecommendedFile from ResponseDefinitions
 */
class RecommendationController extends OCSController {
	public function __construct(
		IRequest $request,
		private readonly IUserSession $userSession,
		private readonly RecommendationService $recommendationService,
		private readonly IUserConfig $config,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * Get recommendations, but only if enabled
	 *
	 * @return DataResponse<Http::STATUS_OK, array{enabled: bool, recommendations?: list<RecommendationsRecommendedFile>}, array{}>
	 *
	 * 200: Recommendations returned
	 */
	#[NoAdminRequired]
	public function index(): DataResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new Exception('Not logged in');
		}
		$response = [];
		$response['enabled'] = $this->config->getValueBool($user->getUID(), Application::APP_ID, 'enabled', true);
		if ($response['enabled']) {
			$response['recommendations'] = array_map(static fn (IRecommendation $recommendation) => $recommendation->jsonSerialize(), $this->recommendationService->getRecommendations($user));
		}
		return new DataResponse(
			$response
		);
	}

	/**
	 * Get recommendations
	 *
	 * @return DataResponse<Http::STATUS_OK, array{enabled: bool, recommendations: list<RecommendationsRecommendedFile>}, array{}>
	 *
	 * 200: Recommendations returned
	 */
	#[NoAdminRequired]
	public function always(): DataResponse {
		$user = $this->userSession->getUser();
		if (is_null($user)) {
			throw new Exception('Not logged in');
		}
		$response = [
			'enabled' => $this->config->getValueBool($user->getUID(), Application::APP_ID, 'enabled', true),
			'recommendations' => array_map(static fn (IRecommendation $recommendation) => $recommendation->jsonSerialize(), $this->recommendationService->getRecommendations($user)),
		];
		return new DataResponse(
			$response
		);
	}
}

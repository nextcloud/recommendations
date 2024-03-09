<?php

declare(strict_types=1);

/**
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Recommendations\Controller;

use OCA\Recommendations\AppInfo\Application;
use OCA\Recommendations\Service\RecommendationService;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserManager;

class RecommendationApiController extends OCSController {

	/** @var IConfig */
	private $config;

	/** @var IUserManager */
	private $userManager;

	/** @var RecommendationService */
	private $service;

	public function __construct($appName, IRequest $request, IConfig $config,
								IUserManager $userManager,
								RecommendationService $service, $userId) {
		parent::__construct($appName, $request, 'GET');
		$this->config = $config;
		$this->service = $service;
		$this->userManager = $userManager;
		$this->userId = $userId;
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function index(): DataResponse {
		$user = $this->userManager->get($this->userId);

		$response = [];
		$response['enabled'] = $this->config->getUserValue($user->getUID(), Application::APP_ID, 'enabled', 'true') === 'true';

		if ($response['enabled']) {
			$response['recommendations'] = $this->service->getRecommendations($user);
		}

		return new DataResponse(
			$response
		);
	}

	/**
	 * @CORS
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
	public function always(): DataResponse {
		$user = $this->userManager->get($this->userId);

		$response = [
			'enabled' => $this->config->getUserValue($user->getUID(), Application::APP_ID, 'enabled', 'true') === 'true',
			'recommendations' => $this->service->getRecommendations($user),
		];

		return new DataResponse(
			$response
		);
	}
}

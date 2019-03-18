<?php

declare(strict_types=1);

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\Recommendations\AppInfo;

use OC;
use OCA\Recommendations\Service\RecommendationService;
use OCP\AppFramework\App;
use OCP\IInitialStateService;
use OCP\IUserSession;
use OCP\Util;

class Application extends App {

	public const APP_ID = 'recommendations';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);

		$this->getContainer()->getServer()->getEventDispatcher()->addListener(
			'OCA\Files::loadAdditionalScripts',
			function () {
				/** @var IInitialStateService $initialState */
				$initialState = $this->getContainer()->query(IInitialStateService::class);
				/** @var RecommendationService $recommendationsService */
				$recommendationsService = $this->getContainer()->query(RecommendationService::class);
				/** @var IUserSession $userSession */
				$userSession = $this->getContainer()->query(IUserSession::class);
				$user = $userSession->getUser();

				if ($user !== null) {
					$initialState->provideInitialState(
						self::APP_ID,
						'recommendations',
						$recommendationsService->getRecommendations($user)
					);
					Util::addScript(self::APP_ID, 'main');
				}
			}
		);
	}

}

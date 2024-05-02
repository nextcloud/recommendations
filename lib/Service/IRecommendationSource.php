<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCP\IUser;

interface IRecommendationSource {

	/**
	 * @param IUser $user
	 * @param int $max maximum number of recommendations
	 *
	 * @return IRecommendation[]
	 */
	public function getMostRecentRecommendation(IUser $user, int $max): array ;
}

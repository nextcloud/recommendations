<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\StorageNotAvailableException;
use OCP\ITags;
use OCP\IUser;

class FavoriteFilesSource implements IRecommendationSource {
	public const REASON = 'favorite';

	public function __construct(
		private readonly IRootFolder $rootFolder,
	) {
	}

	/**
	 * The user's favorites, the most recently changed first
	 *
	 * @return RecommendedFile[]
	 */
	#[\Override]
	public function getMostRecentRecommendation(IUser $user, int $max): array {
		$userFolder = $this->rootFolder->getUserFolder($user->getUID());

		$favorites = $userFolder->searchByTag(ITags::TAG_FAVORITE, $user->getUID());
		usort($favorites, fn (Node $a, Node $b): int => $b->getMTime() <=> $a->getMTime());

		$results = [];
		foreach ($favorites as $node) {
			try {
				$results[] = new RecommendedFile(
					$userFolder->getRelativePath($node->getParent()->getPath()) ?? '/',
					$node,
					$node->getMTime(),
					self::REASON,
				);
			} catch (StorageNotAvailableException) {
				// skip unavailable files
			}
			if (count($results) >= $max) {
				break;
			}
		}

		return $results;
	}
}

<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\StorageNotAvailableException;
use OCP\IUser;
use OCP\Share\IManager;
use OCP\Share\IShare;
use function array_slice;
use function usort;

class RecentlySharedFilesSource implements IRecommendationSource {

	public const REASON = 'recently-shared';

	public function __construct(
		private IManager $shareManager,
		private IRootFolder $rootFolder,
	) {
	}

	/**
	 * @return IShare[]
	 */
	private function getSharesPage(IUser $user, int $shareType, int $offset, int $pageSize): array {
		return $this->shareManager->getSharedWith(
			$user->getUID(),
			$shareType,
			null,
			$pageSize,
			$offset
		);
	}

	/**
	 * @param list<IShare> $shares
	 * @return list<IShare>
	 */
	private function sortSharesByMostRecent(array $shares): array {
		usort($shares, static function (IShare $a, IShare $b): int {
			return $b->getShareTime()->getTimestamp() <=> $a->getShareTime()->getTimestamp();
		});

		return $shares;
	}

	/**
	 * Merge a new batch of shares into the current top-N list.
	 *
	 * We intentionally do not assume that getSharedWith() is ordered by
	 * share time, so every fetched page must still be considered.
	 *
	 * @param list<IShare> $topShares
	 * @param list<IShare> $page
	 * @return list<IShare>
	 */
	private function mergeTopShares(array $topShares, array $page, int $max): array {
		if ($page === []) {
			return $topShares;
		}

		$merged = [...$topShares, ...$page];
		$merged = $this->sortSharesByMostRecent($merged);

		if (count($merged) > $max) {
			$merged = array_slice($merged, 0, $max);
		}

		return $merged;
	}

	/**
	 * Return the globally most recent shares across the supported share types.
	 *
	 * This preserves the existing behavior of considering all shares before
	 * choosing the top results, while avoiding materializing the complete
	 * merged share list in memory.
	 *
	 * @return list<IShare>
	 */
	private function getMostRecentShares(IUser $user, int $max): array {
		if ($max <= 0) {
			return [];
		}

		$pageSize = 50;
		$topShares = [];

		// @todo load other share types as well
		foreach ([IShare::TYPE_USER, IShare::TYPE_GROUP] as $shareType) {
			$offset = 0;

			while (true) {
				$page = $this->getSharesPage($user, $shareType, $offset, $pageSize);
				if ($page === []) {
					break;
				}

				$topShares = $this->mergeTopShares($topShares, $page, $max);

				// Short page means pagination for this share type is exhausted.
				// This is only an end-of-results check; it does not assume any
				// recency ordering from getSharedWith().
				if (count($page) < $pageSize) {
					break;
				}

				$offset += $pageSize;
			}
		}

		return $topShares;
	}

	/**
	 * @return list<IRecommendation>
	 */
	#[\Override]
	public function getMostRecentRecommendation(IUser $user, int $max): array {
		if ($max <= 0) {
			return [];
		}

		$userFolder = $this->rootFolder->getUserFolder($user->getUID());
		$recommendations = [];

		foreach ($this->getMostRecentShares($user, $max) as $share) {
			try {
				$recommendations[] = new RecommendedFile(
					$userFolder->getRelativePath($userFolder->get($share->getTarget())->getParent()->getPath()),
					$share->getNode(),
					$share->getShareTime()->getTimestamp(),
					self::REASON,
				);
			} catch (NotFoundException $e) {
				continue;
			} catch (StorageNotAvailableException $e) {
				continue;
			}
		}

		return $recommendations;
	}
}

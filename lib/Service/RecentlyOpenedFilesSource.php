<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCA\Recommendations\Db\OpenedFileMapper;
use OCP\Files\IRootFolder;
use OCP\Files\StorageNotAvailableException;
use OCP\IUser;

class RecentlyOpenedFilesSource implements IRecommendationSource {
	public const REASON = 'recently-opened';

	public function __construct(
		private readonly OpenedFileMapper $mapper,
		private readonly IRootFolder $rootFolder,
	) {
	}

	/**
	 * @return RecommendedFile[]
	 */
	#[\Override]
	public function getMostRecentRecommendation(IUser $user, int $max): array {
		$userFolder = $this->rootFolder->getUserFolder($user->getUID());

		$results = [];
		$offset = 0;

		do {
			$batch = $this->mapper->findRecent($user->getUID(), $max, $offset);

			foreach ($batch as ['fileId' => $fileId, 'openedAt' => $openedAt]) {
				// Deleted since, or no longer shared with the user
				$node = $userFolder->getFirstNodeById($fileId);
				if ($node === null) {
					continue;
				}
				try {
					$results[] = new RecommendedFile(
						$userFolder->getRelativePath($node->getParent()->getPath()) ?? '/',
						$node,
						$openedAt,
						self::REASON,
					);
				} catch (StorageNotAvailableException) {
					// skip unavailable files
				}
				if (count($results) >= $max) {
					break 2;
				}
			}

			$offset += $max;
		} while (count($batch) === $max);

		return $results;
	}
}

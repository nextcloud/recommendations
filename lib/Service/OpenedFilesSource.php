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
use OCP\Snowflake\ISnowflakeDecoder;

/**
 * Files the user opened, in the order the subclass lists them
 */
abstract class OpenedFilesSource implements IRecommendationSource {
	public function __construct(
		protected readonly OpenedFileMapper $mapper,
		private readonly IRootFolder $rootFolder,
		private readonly ISnowflakeDecoder $snowflakeDecoder,
	) {
	}

	abstract protected function getReason(): string;

	/**
	 * @return list<array{fileId: int, lastId: string}> with the id of the last time each was opened
	 */
	abstract protected function findOpened(string $userId, int $limit, int $offset): array;

	/**
	 * @return RecommendedFile[]
	 */
	#[\Override]
	public function getMostRecentRecommendation(IUser $user, int $max): array {
		$userFolder = $this->rootFolder->getUserFolder($user->getUID());

		$results = [];
		$offset = 0;

		do {
			$batch = $this->findOpened($user->getUID(), $max, $offset);

			foreach ($batch as ['fileId' => $fileId, 'lastId' => $lastId]) {
				// Deleted since, or no longer shared with the user
				$node = $userFolder->getFirstNodeById($fileId);
				if ($node === null) {
					continue;
				}
				try {
					$results[] = new RecommendedFile(
						$userFolder->getRelativePath($node->getParent()->getPath()) ?? '/',
						$node,
						$this->snowflakeDecoder->decode($lastId)->getCreatedAt()->getTimestamp(),
						$this->getReason(),
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

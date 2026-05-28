<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCP\Config\IUserConfig;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\StorageNotAvailableException;
use OCP\IUser;
use Psr\Container\ContainerInterface;

class RecentlyEditedFilesSource implements IRecommendationSource {

	public const REASON = 'recently-edited';

	public function __construct(
		private readonly ContainerInterface $serverContainer,
		private readonly IUserConfig $config,
	) {
	}

	/**
	 * Returns true if the node's path contains a hidden component (a path
	 * segment starting with '.'), meaning the file itself or one of its
	 * ancestor directories is hidden.
	 */
	private function isNodeHidden(Node $node): bool {
		foreach (explode('/', $node->getPath()) as $part) {
			if ($part !== '' && str_starts_with($part, '.')) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return RecommendedFile[]
	 */
	#[\Override]
	public function getMostRecentRecommendation(IUser $user, int $max): array {
		/** @var IRootFolder $rootFolder */
		$rootFolder = $this->serverContainer->get(IRootFolder::class);
		$userFolder = $rootFolder->getUserFolder($user->getUID());

		$showHidden = $this->config->getValueBool($user->getUID(), 'files', 'show_hidden');

		$results = [];
		$offset = 0;

		do {
			$batch = $userFolder->getRecent($max, $offset);
			if (empty($batch)) {
				break;
			}

			foreach ($batch as $node) {
				if (!$showHidden && $this->isNodeHidden($node)) {
					continue;
				}
				try {
					$parentPath = dirname($node->getPath());
					if ($parentPath === '' || $parentPath === '.' || $parentPath === '/') {
						$parentPath = $node->getParent()->getPath();
					}
					$results[] = new RecommendedFile(
						$userFolder->getRelativePath($parentPath),
						$node,
						$node->getMTime(),
						self::REASON,
					);
				} catch (StorageNotAvailableException $e) {
					// skip unavailable files
				}
				if (count($results) >= $max) {
					break;
				}
			}

			// If the batch was smaller than requested, there are no more items to fetch
			if (count($batch) < $max) {
				break;
			}

			$offset += $max;
		} while (count($results) < $max);

		return $results;
	}
}

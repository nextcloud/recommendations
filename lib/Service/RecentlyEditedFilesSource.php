<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\StorageNotAvailableException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IServerContainer;
use OCP\IUser;

class RecentlyEditedFilesSource implements IRecommendationSource {

	public const REASON = 'recently-edited';

	/**
	 * Multiplier for the candidate pool when filtering hidden files.
	 * Fetching more candidates than requested ensures we can fill the result
	 * set even if a significant portion of recent files are hidden.
	 */
	private const HIDDEN_FILTER_CANDIDATE_MULTIPLIER = 10;

	private IServerContainer $serverContainer;
	private IL10N $l10n;
	private IConfig $config;

	public function __construct(IServerContainer $serverContainer,
		IL10N $l10n,
		IConfig $config) {
		$this->serverContainer = $serverContainer;
		$this->l10n = $l10n;
		$this->config = $config;
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

		$showHidden = $this->config->getUserValue($user->getUID(), 'files', 'show_hidden', '0') === '1';

		$candidates = $showHidden
			? $userFolder->getRecent($max)
			: $userFolder->getRecent(self::HIDDEN_FILTER_CANDIDATE_MULTIPLIER * $max);

		$results = [];
		foreach ($candidates as $node) {
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

		return $results;
	}
}

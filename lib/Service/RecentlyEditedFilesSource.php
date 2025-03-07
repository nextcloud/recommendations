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
use OCP\IL10N;
use OCP\IServerContainer;
use OCP\IUser;

class RecentlyEditedFilesSource implements IRecommendationSource {

	public const REASON = 'recently-edited';

	private IServerContainer $serverContainer;
	private IL10N $l10n;

	public function __construct(IServerContainer $serverContainer,
		IL10N $l10n) {
		$this->serverContainer = $serverContainer;
		$this->l10n = $l10n;
	}

	/**
	 * @return RecommendedFile[]
	 */
	public function getMostRecentRecommendation(IUser $user, int $max): array {
		/** @var IRootFolder $rootFolder */
		$rootFolder = $this->serverContainer->get(IRootFolder::class);
		$userFolder = $rootFolder->getUserFolder($user->getUID());

		return array_filter(array_map(function (Node $node) use ($userFolder): ?RecommendedFile {
			try {
				$parentPath = dirname($node->getPath());
				if ($parentPath === '' || $parentPath === '.' || $parentPath === '/') {
					$parentPath = $node->getParent()->getPath();
				}
				return new RecommendedFile(
					$userFolder->getRelativePath($parentPath),
					$node,
					$node->getMTime(),
					self::REASON,
				);
			} catch (StorageNotAvailableException $e) {
				return null;
			}
		}, $userFolder->getRecent($max)), function (?RecommendedFile $entry): bool {
			return $entry !== null;
		});
	}
}

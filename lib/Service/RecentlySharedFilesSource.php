<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use Generator;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\StorageNotAvailableException;
use OCP\IL10N;
use OCP\IUser;
use OCP\Share\IManager;
use OCP\Share\IShare;
use function array_filter;
use function array_map;
use function array_merge;
use function array_slice;
use function iterator_to_array;
use function usort;

class RecentlySharedFilesSource implements IRecommendationSource {

	public const REASON = 'recently-shared';

	private IManager $shareManager;
	private IRootFolder $rootFolder;
	private IL10N $l10n;

	public function __construct(IManager $shareManager,
		IRootFolder $rootFolder,
		IL10N $l10n) {
		$this->shareManager = $shareManager;
		$this->rootFolder = $rootFolder;
		$this->l10n = $l10n;
	}

	/**
	 * @return Generator<IShare>
	 */
	private function getAllShares(IUser $user, int $shareType): Generator {
		$offset = 0;
		$pageSize = 50;

		while (count($page = $this->shareManager->getSharedWith(
			$user->getUID(),
			$shareType,
			null,
			$pageSize,
			$offset
		))) {
			foreach ($page as $share) {
				yield $share;
			}

			$offset += $pageSize;
		}
	}

	/**
	 * @param IShare[] $shares
	 *
	 * @return IShare[]
	 */
	private function sortShares(array $shares): array {
		usort($shares, function (IShare $a, IShare $b) {
			return $b->getShareTime()->getTimestamp() - $a->getShareTime()->getTimestamp();
		});
		return $shares;
	}

	/**
	 * @param IUser $user
	 *
	 * @todo load other share types as well
	 *
	 * @return IShare[]
	 */
	private function getMostRecentShares(IUser $user, int $max): array {
		$shares = $this->sortShares(array_merge(
			iterator_to_array($this->getAllShares($user, IShare::TYPE_USER)),
			iterator_to_array($this->getAllShares($user, IShare::TYPE_GROUP))
		));

		return array_slice($shares, 0, $max);
	}

	/**
	 * @return IRecommendation[]
	 */
	public function getMostRecentRecommendation(IUser $user, int $max): array {
		$shares = $this->getMostRecentShares($user, $max);
		$userFolder = $this->rootFolder->getUserFolder($user->getUID());

		return array_filter(array_map(function (IShare $share) use ($userFolder): ?RecommendedFile {
			try {
				return new RecommendedFile(
					$userFolder->getRelativePath($userFolder->get($share->getTarget())->getParent()->getPath()),
					$share->getNode(),
					$share->getShareTime()->getTimestamp(),
					self::REASON,
				);
			} catch (NotFoundException $ex) {
				return null;
			} catch (StorageNotAvailableException $e) {
				return null;
			}
		}, $shares));
	}
}

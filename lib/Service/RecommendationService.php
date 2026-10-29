<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCP\IPreview;
use OCP\IUser;
use function array_push;
use function array_slice;

class RecommendationService {
	private const MAX_RECOMMENDATIONS = 7;

	/** @var IRecommendationSource[] */
	private array $sources;

	public function __construct(
		RecentlyCommentedFilesSource $recentlyCommented,
		RecentlyEditedFilesSource $recentlyEdited,
		RecentlySharedFilesSource $recentlyShared,
		RecentlyOpenedFilesSource $recentlyOpened,
		FrequentlyOpenedFilesSource $frequentlyOpened,
		FavoriteFilesSource $favorites,
		private IPreview $previewManager,
		private RecommendationScorer $scorer,
	) {
		$this->sources = [
			$recentlyCommented,
			$recentlyEdited,
			$recentlyShared,
			$recentlyOpened,
			$frequentlyOpened,
			$favorites,
		];
	}

	/**
	 * @param list<IRecommendation> $recommendations
	 *
	 * @return list<IRecommendation>
	 */
	private function addPreviews(array $recommendations): array {
		foreach ($recommendations as $recommendation) {
			if ($this->previewManager->isAvailable($recommendation->getNode())) {
				$recommendation->setHasPreview(true);
			}
		}
		return $recommendations;
	}

	/**
	 * @param IUser $user
	 *
	 * @return list<IRecommendation>
	 */
	public function getRecommendations(IUser $user, int $max = self::MAX_RECOMMENDATIONS): array {
		if ($max <= 0) {
			return [];
		}

		$all = [];

		foreach ($this->sources as $source) {
			array_push($all, ...$source->getMostRecentRecommendation($user, self::MAX_RECOMMENDATIONS));
		}

		$ranked = $this->scorer->rank($user, $all);

		return $this->addPreviews(array_slice($ranked, 0, $max));
	}
}

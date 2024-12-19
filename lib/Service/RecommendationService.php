<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCP\IPreview;
use OCP\IUser;
use function array_merge;
use function array_reduce;
use function array_slice;
use function usort;

class RecommendationService {
	private const MAX_RECOMMENDATIONS = 7;

	/** @var IRecommendationSource[] */
	private array $sources;
	private IPreview $previewManager;

	public function __construct(RecentlyCommentedFilesSource $recentlyCommented,
		RecentlyEditedFilesSource $recentlyEdited,
		RecentlySharedFilesSource $recentlyShared,
		IPreview $previewManager) {
		$this->sources = [
			$recentlyCommented,
			$recentlyEdited,
			$recentlyShared,
		];
		$this->previewManager = $previewManager;
	}

	/**
	 * @param list<IRecommendation> $recommendations
	 *
	 * @return list<IRecommendation>
	 */
	private function sortRecommendations(array $recommendations): array {
		usort($recommendations, function (IRecommendation $a, IRecommendation $b) {
			return $b->getTimestamp() - $a->getTimestamp();
		});

		return $recommendations;
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
		$all = array_reduce($this->sources, function (array $carry, IRecommendationSource $source) use ($user) {
			return array_merge($carry, $source->getMostRecentRecommendation($user, self::MAX_RECOMMENDATIONS));
		}, []);

		$sorted = $this->sortRecommendations($all);
		$topX = $this->getDeduplicatedSlice($sorted, $max);

		return $this->addPreviews($topX);
	}

	/**
	 * Deduplicate the sorted recommendations and return the top $max picks
	 *
	 * The first (most recent) recommendation wins, hence eventually show its
	 * recommendation reason
	 *
	 * @param list<IRecommendation> $recommendations
	 * @param int $max
	 * @return list<IRecommendation>
	 */
	private function getDeduplicatedSlice(array $recommendations, int $max): array {
		$picks = [];

		foreach ($recommendations as $recommendation) {
			if (empty(array_filter($picks, function (IRecommendation $rec) use ($recommendation) {
				return $recommendation->getNode()->getId() === $rec->getNode()->getId();
			}))) {
				$picks[] = $recommendation;
			}
		}

		return array_slice($picks, 0, $max);
	}
}

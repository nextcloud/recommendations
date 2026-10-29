<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCA\Recommendations\AppInfo\Application;
use OCA\Recommendations\Db\OpenedFileMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IAppConfig;
use OCP\IUser;

/**
 * Ranks the files the sources suggest: the more reasons, and the more
 * recent they are, the higher a file goes.
 *
 * Each reason adds its weight, halved for every week it is old. How often
 * the user opened a file adds the frequently opened weight, scaled from 0
 * to 1 against the most opened file. Weights can be tuned with the app
 * config keys `weight_<reason>`, e.g. `weight_recently_opened`.
 */
class RecommendationScorer {
	public const HALF_LIFE = 7 * 24 * 60 * 60; // 1 week

	public const DEFAULT_WEIGHTS = [
		RecentlyOpenedFilesSource::REASON => 0.3,
		FrequentlyOpenedFilesSource::REASON => 0.2,
		FavoriteFilesSource::REASON => 0.2,
		RecentlyEditedFilesSource::REASON => 0.1,
		RecentlyEditedFilesSource::REASON_UPLOADED => 0.1,
		RecentlySharedFilesSource::REASON => 0.1,
		RecentlyCommentedFilesSource::REASON => 0.1,
	];

	public function __construct(
		private readonly OpenedFileMapper $mapper,
		private readonly IAppConfig $appConfig,
		private readonly ITimeFactory $timeFactory,
	) {
	}

	/**
	 * One recommendation per file, the best first. Each file keeps the
	 * reason that weighs the most for it.
	 *
	 * @param list<IRecommendation> $recommendations
	 * @return list<IRecommendation>
	 */
	public function rank(IUser $user, array $recommendations): array {
		$now = $this->timeFactory->getTime();

		/** @var array<int, float> $scores */
		$scores = [];
		/** @var array<int, array{IRecommendation, float}> $shown */
		$shown = [];
		/** @var array<int, IRecommendation> $frequent */
		$frequent = [];

		foreach ($recommendations as $recommendation) {
			$id = $recommendation->getNode()->getId();
			$scores[$id] ??= 0.0;

			// Weighed by how often it was opened, below
			if ($recommendation->getReason() === FrequentlyOpenedFilesSource::REASON) {
				$frequent[$id] = $recommendation;
				continue;
			}

			$score = $this->weight($recommendation->getReason()) * $this->decay($now - $recommendation->getTimestamp());
			$scores[$id] += $score;
			if (!isset($shown[$id]) || $score > $shown[$id][1]) {
				$shown[$id] = [$recommendation, $score];
			}
		}

		$opens = $this->mapper->countOpens($user->getUID(), array_keys($scores));
		$mostOpens = $opens === [] ? 0 : max($opens);
		foreach ($opens as $id => $count) {
			$score = $this->weight(FrequentlyOpenedFilesSource::REASON) * log(1 + $count) / log(1 + $mostOpens);
			$scores[$id] += $score;
			if (isset($frequent[$id]) && (!isset($shown[$id]) || $score > $shown[$id][1])) {
				$shown[$id] = [$frequent[$id], $score];
			}
		}

		// Frequently opened files the count did not cover, the table cleaned up meanwhile
		foreach ($frequent as $id => $recommendation) {
			$shown[$id] ??= [$recommendation, 0.0];
		}

		arsort($scores);
		return array_map(fn (int $id): IRecommendation => $shown[$id][0], array_keys($scores));
	}

	private function weight(string $reason): float {
		$key = 'weight_' . str_replace('-', '_', $reason);
		return $this->appConfig->getValueFloat(Application::APP_ID, $key, self::DEFAULT_WEIGHTS[$reason] ?? 0.0);
	}

	private function decay(int $age): float {
		return 0.5 ** ((float)max(0, $age) / (float)self::HALF_LIFE);
	}
}

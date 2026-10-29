<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Service;

use OCA\Recommendations\Db\OpenedFileMapper;
use OCA\Recommendations\Service\FavoriteFilesSource;
use OCA\Recommendations\Service\FrequentlyOpenedFilesSource;
use OCA\Recommendations\Service\IRecommendation;
use OCA\Recommendations\Service\RecentlyEditedFilesSource;
use OCA\Recommendations\Service\RecentlyOpenedFilesSource;
use OCA\Recommendations\Service\RecommendationScorer;
use OCA\Recommendations\Service\RecommendedFile;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\File;
use OCP\IAppConfig;
use OCP\IUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecommendationScorerTest extends TestCase {
	private const NOW = 10_000_000;
	private const WEEK = 7 * 24 * 60 * 60;

	private OpenedFileMapper&MockObject $mapper;
	/** @var array<string, float> */
	private array $config = [];
	private IUser&MockObject $user;
	private RecommendationScorer $scorer;

	protected function setUp(): void {
		parent::setUp();

		$this->mapper = $this->createMock(OpenedFileMapper::class);

		$appConfig = $this->createMock(IAppConfig::class);
		$appConfig->method('getValueFloat')->willReturnCallback(
			fn (string $app, string $key, float $default): float => $this->config[$key] ?? $default,
		);
		$timeFactory = $this->createMock(ITimeFactory::class);
		$timeFactory->method('getTime')->willReturn(self::NOW);
		$this->user = $this->createMock(IUser::class);
		$this->user->method('getUID')->willReturn('alice');

		$this->scorer = new RecommendationScorer($this->mapper, $appConfig, $timeFactory);
	}

	private function recommendation(int $fileId, string $reason, int $age): RecommendedFile {
		$file = $this->createMock(File::class);
		$file->method('getId')->willReturn($fileId);
		return new RecommendedFile('/', $file, self::NOW - $age, $reason);
	}

	/**
	 * @param list<IRecommendation> $ranked
	 * @return list<string> "<file id> <reason>"
	 */
	private function summary(array $ranked): array {
		return array_map(fn (IRecommendation $r): string => $r->getNode()->getId() . ' ' . $r->getReason(), $ranked);
	}

	public function testNewerRanksHigherForTheSameReason(): void {
		$ranked = $this->scorer->rank($this->user, [
			$this->recommendation(1, RecentlyEditedFilesSource::REASON, 2 * self::WEEK),
			$this->recommendation(2, RecentlyEditedFilesSource::REASON, self::WEEK),
		]);

		$this->assertSame(['2 recently-edited', '1 recently-edited'], $this->summary($ranked));
	}

	// Edited and favorite: 0.1 + 0.2, above opened a day ago: 0.3 * 0.9
	public function testReasonsAddUp(): void {
		$ranked = $this->scorer->rank($this->user, [
			$this->recommendation(1, RecentlyEditedFilesSource::REASON, 0),
			$this->recommendation(1, FavoriteFilesSource::REASON, 0),
			$this->recommendation(2, RecentlyOpenedFilesSource::REASON, self::WEEK / 7),
		]);

		$this->assertSame(['1 favorite', '2 recently-opened'], $this->summary($ranked));
	}

	public function testKeepsTheReasonThatWeighsTheMost(): void {
		$ranked = $this->scorer->rank($this->user, [
			$this->recommendation(1, RecentlyEditedFilesSource::REASON, 0),
			$this->recommendation(1, RecentlyOpenedFilesSource::REASON, self::WEEK),
			$this->recommendation(1, RecentlyOpenedFilesSource::REASON, 0),
		]);

		$this->assertSame(['1 recently-opened'], $this->summary($ranked));
		$this->assertSame(self::NOW, $ranked[0]->getTimestamp());
	}

	public function testOftenOpenedRanksHigher(): void {
		$this->mapper->method('countOpens')->with('alice', [1, 2])->willReturn([1 => 1, 2 => 20]);

		$ranked = $this->scorer->rank($this->user, [
			$this->recommendation(1, RecentlyOpenedFilesSource::REASON, 0),
			$this->recommendation(2, RecentlyOpenedFilesSource::REASON, 0),
			$this->recommendation(2, FrequentlyOpenedFilesSource::REASON, 0),
		]);

		$this->assertSame(['2 recently-opened', '1 recently-opened'], $this->summary($ranked));
	}

	// Opened often weeks ago: its frequency is what brings it up
	public function testShowsFrequentlyOpenedWhenThatWeighsTheMost(): void {
		$this->mapper->method('countOpens')->willReturn([1 => 20]);

		$ranked = $this->scorer->rank($this->user, [
			$this->recommendation(1, RecentlyOpenedFilesSource::REASON, 4 * self::WEEK),
			$this->recommendation(1, FrequentlyOpenedFilesSource::REASON, 4 * self::WEEK),
		]);

		$this->assertSame(['1 frequently-opened'], $this->summary($ranked));
	}

	public function testWeightsCanBeTuned(): void {
		$this->config['weight_favorite'] = 0.0;

		$ranked = $this->scorer->rank($this->user, [
			$this->recommendation(1, FavoriteFilesSource::REASON, 0),
			$this->recommendation(2, RecentlyEditedFilesSource::REASON, self::WEEK),
		]);

		$this->assertSame(['2 recently-edited', '1 favorite'], $this->summary($ranked));
	}
}

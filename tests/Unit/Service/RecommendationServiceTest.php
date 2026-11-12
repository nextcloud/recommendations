<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Service;

use OCA\Recommendations\Service\FavoriteFilesSource;
use OCA\Recommendations\Service\FrequentlyOpenedFilesSource;
use OCA\Recommendations\Service\RecentlyCommentedFilesSource;
use OCA\Recommendations\Service\RecentlyEditedFilesSource;
use OCA\Recommendations\Service\RecentlyOpenedFilesSource;
use OCA\Recommendations\Service\RecentlySharedFilesSource;
use OCA\Recommendations\Service\RecommendationScorer;
use OCA\Recommendations\Service\RecommendationService;
use OCA\Recommendations\Service\RecommendedFile;
use OCP\Files\File;
use OCP\IPreview;
use OCP\IUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecommendationServiceTest extends TestCase {
	private RecentlyEditedFilesSource&MockObject $edited;
	private RecommendationScorer&MockObject $scorer;
	private IUser&MockObject $user;
	private RecommendationService $service;

	protected function setUp(): void {
		parent::setUp();

		$this->edited = $this->createMock(RecentlyEditedFilesSource::class);
		$this->scorer = $this->createMock(RecommendationScorer::class);
		$this->user = $this->createMock(IUser::class);

		$this->service = new RecommendationService(
			$this->createMock(RecentlyCommentedFilesSource::class),
			$this->edited,
			$this->createMock(RecentlySharedFilesSource::class),
			$this->createMock(RecentlyOpenedFilesSource::class),
			$this->createMock(FrequentlyOpenedFilesSource::class),
			$this->createMock(FavoriteFilesSource::class),
			$this->createMock(IPreview::class),
			$this->scorer,
		);
	}

	/**
	 * @return list<RecommendedFile>
	 */
	private function recommendations(int $count): array {
		return array_map(
			fn (int $i): RecommendedFile => new RecommendedFile('/', $this->createMock(File::class), $i, RecentlyEditedFilesSource::REASON),
			range(1, $count),
		);
	}

	public function testReturnsTheBestRankedUpToMax(): void {
		$ranked = $this->recommendations(5);
		// Every source is asked for the default number, whatever the max
		$this->edited->expects($this->once())->method('getMostRecentRecommendation')->with($this->user, 7)->willReturn($ranked);
		$this->scorer->method('rank')->with($this->user, $ranked)->willReturn($ranked);

		$this->assertSame(array_slice($ranked, 0, 3), $this->service->getRecommendations($this->user, 3));
	}

	public function testAsksNothingForNoRecommendations(): void {
		$this->edited->expects($this->never())->method('getMostRecentRecommendation');
		$this->scorer->expects($this->never())->method('rank');

		$this->assertSame([], $this->service->getRecommendations($this->user, 0));
	}
}

<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Db;

use OCA\Recommendations\Db\OpenedFileMapper;
use OCP\Server;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('DB')]
class OpenedFileMapperTest extends TestCase {
	private const USER = 'opened-file-mapper-test';
	private const OTHER_USER = 'opened-file-mapper-test-other';

	private OpenedFileMapper $mapper;

	protected function setUp(): void {
		parent::setUp();

		$this->mapper = Server::get(OpenedFileMapper::class);
		$this->mapper->deleteByUser(self::USER);
		$this->mapper->deleteByUser(self::OTHER_USER);
	}

	protected function tearDown(): void {
		$this->mapper->deleteByUser(self::USER);
		$this->mapper->deleteByUser(self::OTHER_USER);

		parent::tearDown();
	}

	/**
	 * @return list<int>
	 */
	private function recentFileIds(string $userId, int $limit = 10, int $offset = 0): array {
		return array_column($this->mapper->findRecent($userId, $limit, $offset), 'fileId');
	}

	public function testFindRecentListsEachFileOnceByLastOpen(): void {
		$this->mapper->add(self::USER, 1);
		$this->mapper->add(self::USER, 2);
		$this->mapper->add(self::USER, 1);
		$this->mapper->add(self::OTHER_USER, 3);

		$this->assertSame([1, 2], $this->recentFileIds(self::USER));
	}

	public function testFindRecentPages(): void {
		$this->mapper->add(self::USER, 1);
		$this->mapper->add(self::USER, 2);
		$this->mapper->add(self::USER, 3);

		$this->assertSame([2], $this->recentFileIds(self::USER, 1, 1));
	}

	/**
	 * @return list<int>
	 */
	private function frequentFileIds(string $userId, int $limit = 10, int $offset = 0): array {
		return array_column($this->mapper->findFrequent($userId, $limit, $offset), 'fileId');
	}

	/**
	 * @param list<int> $fileIds
	 */
	private function open(string $userId, array $fileIds): void {
		foreach ($fileIds as $fileId) {
			$this->mapper->add($userId, $fileId);
		}
	}

	public function testFindFrequentListsTheMostOpenedFirst(): void {
		$this->open(self::USER, [1, 1, 2, 2, 2, 3, 3, 3, 3]);

		$this->assertSame([3, 2, 1], $this->frequentFileIds(self::USER));
	}

	// Opened once is not opened often
	public function testFindFrequentSkipsFilesOpenedOnce(): void {
		$this->open(self::USER, [1, 2, 2]);

		$this->assertSame([2], $this->frequentFileIds(self::USER));
	}

	public function testFindFrequentBreaksTiesWithTheLastOpen(): void {
		$this->open(self::USER, [1, 2, 2, 1]);

		$this->assertSame([1, 2], $this->frequentFileIds(self::USER));
	}

	public function testFindFrequentPages(): void {
		$this->open(self::USER, [1, 1, 2, 2, 2, 3, 3, 3, 3]);

		$this->assertSame([2], $this->frequentFileIds(self::USER, 1, 1));
	}

	public function testFindFrequentOnlyCountsTheUsersOpens(): void {
		$this->open(self::USER, [1, 1]);
		$this->open(self::OTHER_USER, [1, 2, 2, 2]);

		$this->assertSame([1], $this->frequentFileIds(self::USER));
		$this->assertSame([2], $this->frequentFileIds(self::OTHER_USER));
	}

	public function testCountOpens(): void {
		$this->mapper->add(self::USER, 1);
		$this->mapper->add(self::USER, 1);
		$this->mapper->add(self::USER, 2);
		$this->mapper->add(self::OTHER_USER, 1);

		$lastIds = array_column($this->mapper->findRecent(self::USER, 10), 'lastId', 'fileId');

		$counts = $this->mapper->countOpens(self::USER, [1, 2, 3]);
		ksort($counts);
		$this->assertSame([
			1 => ['opens' => 2, 'lastId' => $lastIds[1]],
			2 => ['opens' => 1, 'lastId' => $lastIds[2]],
		], $counts);
	}

	public function testDeleteBefore(): void {
		$this->mapper->add(self::USER, 1);
		$this->mapper->add(self::USER, 2);
		$second = $this->mapper->findRecent(self::USER, 1)[0]['lastId'];

		$this->mapper->deleteBefore($second);

		$this->assertSame([2], $this->recentFileIds(self::USER));
	}

	public function testDeleteByUserKeepsOthers(): void {
		$this->mapper->add(self::USER, 1);
		$this->mapper->add(self::OTHER_USER, 2);

		$this->mapper->deleteByUser(self::USER);

		$this->assertSame([], $this->recentFileIds(self::USER));
		$this->assertSame([2], $this->recentFileIds(self::OTHER_USER));
	}
}

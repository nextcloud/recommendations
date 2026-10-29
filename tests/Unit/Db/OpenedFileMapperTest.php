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

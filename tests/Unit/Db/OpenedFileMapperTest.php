<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Db;

use OCA\Recommendations\Db\OpenedFileMapper;
use OCP\IDBConnection;
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

		$this->mapper = new OpenedFileMapper(Server::get(IDBConnection::class));
		$this->mapper->deleteByUser(self::USER);
		$this->mapper->deleteByUser(self::OTHER_USER);
	}

	protected function tearDown(): void {
		$this->mapper->deleteByUser(self::USER);
		$this->mapper->deleteByUser(self::OTHER_USER);

		parent::tearDown();
	}

	public function testFindRecentListsEachFileOnceByLastOpen(): void {
		$this->mapper->insert(self::USER, 1, 100);
		$this->mapper->insert(self::USER, 2, 200);
		$this->mapper->insert(self::USER, 1, 300);
		$this->mapper->insert(self::OTHER_USER, 3, 400);

		$this->assertSame([
			['fileId' => 1, 'openedAt' => 300],
			['fileId' => 2, 'openedAt' => 200],
		], $this->mapper->findRecent(self::USER, 10));
	}

	public function testFindRecentPages(): void {
		$this->mapper->insert(self::USER, 1, 100);
		$this->mapper->insert(self::USER, 2, 200);
		$this->mapper->insert(self::USER, 3, 300);

		$this->assertSame([['fileId' => 2, 'openedAt' => 200]], $this->mapper->findRecent(self::USER, 1, 1));
	}

	public function testDeleteOlderThan(): void {
		$this->mapper->insert(self::USER, 1, 100);
		$this->mapper->insert(self::USER, 2, 200);

		$this->mapper->deleteOlderThan(150);

		$this->assertSame([['fileId' => 2, 'openedAt' => 200]], $this->mapper->findRecent(self::USER, 10));
	}

	public function testDeleteByUserKeepsOthers(): void {
		$this->mapper->insert(self::USER, 1, 100);
		$this->mapper->insert(self::OTHER_USER, 2, 200);

		$this->mapper->deleteByUser(self::USER);

		$this->assertSame([], $this->mapper->findRecent(self::USER, 10));
		$this->assertSame([['fileId' => 2, 'openedAt' => 200]], $this->mapper->findRecent(self::OTHER_USER, 10));
	}
}

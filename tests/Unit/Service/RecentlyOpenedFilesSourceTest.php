<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Service;

use OCA\Recommendations\Db\OpenedFileMapper;
use OCA\Recommendations\Service\RecentlyOpenedFilesSource;
use OCA\Recommendations\Service\RecommendedFile;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\IUserFolder;
use OCP\IUser;
use OCP\Snowflake\ISnowflakeDecoder;
use OCP\Snowflake\Snowflake;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecentlyOpenedFilesSourceTest extends TestCase {
	private OpenedFileMapper&MockObject $mapper;
	private IUserFolder&MockObject $userFolder;
	private IUser&MockObject $user;
	private RecentlyOpenedFilesSource $source;

	protected function setUp(): void {
		parent::setUp();

		$this->mapper = $this->createMock(OpenedFileMapper::class);
		$this->userFolder = $this->createMock(IUserFolder::class);
		$this->userFolder->method('getRelativePath')->willReturnCallback(
			fn (string $path): string => substr($path, strlen('/alice/files')) ?: '/',
		);
		$this->user = $this->createMock(IUser::class);
		$this->user->method('getUID')->willReturn('alice');

		$rootFolder = $this->createMock(IRootFolder::class);
		$rootFolder->method('getUserFolder')->with('alice')->willReturn($this->userFolder);

		// The test ids are the timestamps they were opened at
		$decoder = $this->createMock(ISnowflakeDecoder::class);
		$decoder->method('decode')->willReturnCallback(
			fn (string $id): Snowflake => new Snowflake(0, 0, false, 0, 0, new \DateTimeImmutable('@' . $id)),
		);

		$this->source = new RecentlyOpenedFilesSource($this->mapper, $rootFolder, $decoder);
	}

	private function file(string $parentPath): File&MockObject {
		$parent = $this->createMock(Folder::class);
		$parent->method('getPath')->willReturn($parentPath);
		$file = $this->createMock(File::class);
		$file->method('getParent')->willReturn($parent);
		return $file;
	}

	public function testRecommendsOpenedFiles(): void {
		$file = $this->file('/alice/files/Documents');
		$this->mapper->method('findRecent')->with('alice', 2, 0)->willReturn([['fileId' => 42, 'lastId' => '1000']]);
		$this->userFolder->method('getFirstNodeById')->with(42)->willReturn($file);

		$this->assertEquals(
			[new RecommendedFile('/Documents', $file, 1000, RecentlyOpenedFilesSource::REASON)],
			$this->source->getMostRecentRecommendation($this->user, 2),
		);
	}

	// Deleted or unshared since it was opened: the next page fills the gap
	public function testSkipsFilesNoLongerThere(): void {
		$file = $this->file('/alice/files');
		$this->mapper->method('findRecent')->willReturnMap([
			['alice', 2, 0, [['fileId' => 1, 'lastId' => '300'], ['fileId' => 2, 'lastId' => '200']]],
			['alice', 2, 2, [['fileId' => 3, 'lastId' => '100']]],
		]);
		$this->userFolder->method('getFirstNodeById')->willReturnMap([
			[1, null],
			[2, $file],
			[3, $file],
		]);

		$timestamps = array_map(
			fn (RecommendedFile $recommendation): int => $recommendation->getTimestamp(),
			$this->source->getMostRecentRecommendation($this->user, 2),
		);
		$this->assertSame([200, 100], $timestamps);
	}
}

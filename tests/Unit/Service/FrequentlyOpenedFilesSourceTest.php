<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Service;

use OCA\Recommendations\Db\OpenedFileMapper;
use OCA\Recommendations\Service\FrequentlyOpenedFilesSource;
use OCA\Recommendations\Service\RecommendedFile;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\IUserFolder;
use OCP\IUser;
use OCP\Snowflake\ISnowflakeDecoder;
use OCP\Snowflake\Snowflake;
use PHPUnit\Framework\TestCase;

class FrequentlyOpenedFilesSourceTest extends TestCase {
	public function testRecommendsTheMostOpenedFilesAtTheirLastOpen(): void {
		$parent = $this->createMock(Folder::class);
		$parent->method('getPath')->willReturn('/alice/files/Documents');
		$file = $this->createMock(File::class);
		$file->method('getParent')->willReturn($parent);

		$mapper = $this->createMock(OpenedFileMapper::class);
		$mapper->expects($this->never())->method('findRecent');
		$mapper->method('findFrequent')->with('alice', 2, 0)->willReturn([['fileId' => 42, 'lastId' => '1000']]);

		$userFolder = $this->createMock(IUserFolder::class);
		$userFolder->method('getFirstNodeById')->with(42)->willReturn($file);
		$userFolder->method('getRelativePath')->with('/alice/files/Documents')->willReturn('/Documents');
		$rootFolder = $this->createMock(IRootFolder::class);
		$rootFolder->method('getUserFolder')->with('alice')->willReturn($userFolder);
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('alice');

		// The test id is the timestamp it was opened at
		$decoder = $this->createMock(ISnowflakeDecoder::class);
		$decoder->method('decode')->willReturnCallback(
			fn (string $id): Snowflake => new Snowflake(0, 0, false, 0, 0, new \DateTimeImmutable('@' . $id)),
		);

		$source = new FrequentlyOpenedFilesSource($mapper, $rootFolder, $decoder);

		$this->assertEquals(
			[new RecommendedFile('/Documents', $file, 1000, FrequentlyOpenedFilesSource::REASON)],
			$source->getMostRecentRecommendation($user, 2),
		);
	}
}

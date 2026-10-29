<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Service;

use OCA\Recommendations\Service\FavoriteFilesSource;
use OCA\Recommendations\Service\RecommendedFile;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\IUserFolder;
use OCP\ITags;
use OCP\IUser;
use PHPUnit\Framework\TestCase;

class FavoriteFilesSourceTest extends TestCase {
	public function testRecommendsTheLatestChangedFavorites(): void {
		$parent = $this->createMock(Folder::class);
		$parent->method('getPath')->willReturn('/alice/files/Documents');
		$favorites = array_map(function (int $mtime) use ($parent): File {
			$file = $this->createMock(File::class);
			$file->method('getMTime')->willReturn($mtime);
			$file->method('getParent')->willReturn($parent);
			return $file;
		}, [100, 300, 200]);

		$userFolder = $this->createMock(IUserFolder::class);
		$userFolder->method('searchByTag')->with(ITags::TAG_FAVORITE, 'alice')->willReturn($favorites);
		$userFolder->method('getRelativePath')->with('/alice/files/Documents')->willReturn('/Documents');
		$rootFolder = $this->createMock(IRootFolder::class);
		$rootFolder->method('getUserFolder')->with('alice')->willReturn($userFolder);
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('alice');

		$recommendations = (new FavoriteFilesSource($rootFolder))->getMostRecentRecommendation($user, 2);

		$this->assertSame([300, 200], array_map(fn (RecommendedFile $r): int => $r->getTimestamp(), $recommendations));
		$this->assertSame(['/Documents', '/Documents'], array_map(fn (RecommendedFile $r): string => $r->getDirectory(), $recommendations));
	}
}

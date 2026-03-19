<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Service;

use OCA\Recommendations\Service\RecentlyEditedFilesSource;
use OCA\Recommendations\Service\RecommendedFile;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\StorageNotAvailableException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IServerContainer;
use OCP\IUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test double that bypasses StorageNotAvailableException's constructor, which
 * requires Nextcloud server internals (OC::$server) not available in unit tests.
 */
class TestStorageNotAvailableException extends StorageNotAvailableException {
	public function __construct() {
		\Exception::__construct('storage not available');
	}
}

class RecentlyEditedFilesSourceTest extends TestCase {
	private IServerContainer&MockObject $serverContainer;
	private IL10N&MockObject $l10n;
	private IConfig&MockObject $config;
	private Folder&MockObject $userFolder;
	private IUser&MockObject $user;
	private RecentlyEditedFilesSource $source;

	protected function setUp(): void {
		parent::setUp();

		$this->serverContainer = $this->createMock(IServerContainer::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->config = $this->createMock(IConfig::class);
		$this->userFolder = $this->createMock(Folder::class);
		$this->user = $this->createMock(IUser::class);

		$this->user->method('getUID')->willReturn('testuser');

		// Use an anonymous proxy instead of createMock(IRootFolder::class) so
		// that PHP never loads IRootFolder at runtime.  IRootFolder extends
		// OC\Hooks\Emitter (a server-internal interface), which would otherwise
		// require OC\Hooks stubs to be defined.
		$userFolder = $this->userFolder;
		$rootFolderProxy = new class($userFolder) {
			public function __construct(
				private Folder $folder,
			) {
			}
			public function getUserFolder(string $uid): Folder {
				return $this->folder;
			}
		};

		$this->serverContainer->method('get')
			->with(IRootFolder::class)
			->willReturn($rootFolderProxy);

		$this->source = new RecentlyEditedFilesSource(
			$this->serverContainer,
			$this->l10n,
			$this->config,
		);
	}

	/**
	 * Helper to create a mock Node with a specific path and mtime.
	 */
	private function createNodeMock(string $path, int $mtime = 1000): Node&MockObject {
		$node = $this->createMock(Node::class);
		$node->method('getPath')->willReturn($path);
		$node->method('getMTime')->willReturn($mtime);

		// dirname of e.g. "/testuser/files/doc.pdf" → "/testuser/files"
		$parent = $this->createMock(Folder::class);
		$parent->method('getPath')->willReturn(dirname($path));
		$node->method('getParent')->willReturn($parent);

		return $node;
	}

	/**
	 * Enable or disable show_hidden preference for the test user.
	 */
	private function setShowHidden(bool $show): void {
		$this->config->method('getUserValue')
			->with('testuser', 'files', 'show_hidden', '0')
			->willReturn($show ? '1' : '0');
	}

	public function testNormalFilesAreIncluded(): void {
		$this->setShowHidden(false);

		$node = $this->createNodeMock('/testuser/files/document.pdf', 1000);
		$this->userFolder->method('getRecent')
			->willReturnCallback(function (int $limit, int $offset) use ($node): array {
				return $offset === 0 ? [$node] : [];
			});
		$this->userFolder->method('getRelativePath')
			->willReturnCallback(fn (string $path) => str_replace('/testuser/files', '', $path));

		$results = $this->source->getMostRecentRecommendation($this->user, 5);

		$this->assertCount(1, $results);
		$this->assertInstanceOf(RecommendedFile::class, $results[0]);
		$this->assertSame(RecentlyEditedFilesSource::REASON, $results[0]->getReason());
	}

	public function testHiddenFilesAreExcludedWhenShowHiddenIsFalse(): void {
		$this->setShowHidden(false);

		$hiddenNode = $this->createNodeMock('/testuser/files/.hidden/secret.txt', 1000);
		$this->userFolder->method('getRecent')
			->willReturnCallback(function (int $limit, int $offset) use ($hiddenNode): array {
				return $offset === 0 ? [$hiddenNode] : [];
			});

		$results = $this->source->getMostRecentRecommendation($this->user, 5);

		$this->assertCount(0, $results);
	}

	public function testHiddenFileItselfIsExcludedWhenShowHiddenIsFalse(): void {
		$this->setShowHidden(false);

		$hiddenNode = $this->createNodeMock('/testuser/files/.hidden', 1000);
		$this->userFolder->method('getRecent')
			->willReturnCallback(function (int $limit, int $offset) use ($hiddenNode): array {
				return $offset === 0 ? [$hiddenNode] : [];
			});

		$results = $this->source->getMostRecentRecommendation($this->user, 5);

		$this->assertCount(0, $results);
	}

	public function testHiddenFilesAreIncludedWhenShowHiddenIsTrue(): void {
		$this->setShowHidden(true);

		$hiddenNode = $this->createNodeMock('/testuser/files/.hidden/secret.txt', 1000);
		$this->userFolder->method('getRecent')
			->willReturnCallback(function (int $limit, int $offset) use ($hiddenNode): array {
				return $offset === 0 ? [$hiddenNode] : [];
			});
		$this->userFolder->method('getRelativePath')
			->willReturnCallback(fn (string $path) => str_replace('/testuser/files', '', $path));

		$results = $this->source->getMostRecentRecommendation($this->user, 5);

		$this->assertCount(1, $results);
	}

	public function testOffsetLoopFetchesMoreWhenFirstBatchIsAllHidden(): void {
		$this->setShowHidden(false);

		$hiddenNode = $this->createNodeMock('/testuser/files/.hidden/file.txt', 1000);
		$visibleNode = $this->createNodeMock('/testuser/files/document.pdf', 2000);

		$this->userFolder->method('getRecent')
			->willReturnCallback(function (int $limit, int $offset) use ($hiddenNode, $visibleNode): array {
				if ($offset === 0) {
					return [$hiddenNode];
				}
				if ($offset === $limit) {
					return [$visibleNode];
				}
				return [];
			});
		$this->userFolder->method('getRelativePath')
			->willReturnCallback(fn (string $path) => str_replace('/testuser/files', '', $path));

		$results = $this->source->getMostRecentRecommendation($this->user, 1);

		$this->assertCount(1, $results);
		$this->assertSame(2000, $results[0]->getTimestamp());
	}

	public function testEmptyBatchStopsLoop(): void {
		$this->setShowHidden(false);

		// getRecent always returns empty
		$this->userFolder->method('getRecent')->willReturn([]);

		$results = $this->source->getMostRecentRecommendation($this->user, 5);

		$this->assertCount(0, $results);
	}

	public function testMaxLimitIsRespected(): void {
		$this->setShowHidden(false);

		$nodes = [];
		for ($i = 0; $i < 10; $i++) {
			$nodes[] = $this->createNodeMock('/testuser/files/file' . $i . '.pdf', 1000 + $i);
		}

		$this->userFolder->method('getRecent')
			->willReturnCallback(function (int $limit, int $offset) use ($nodes): array {
				return array_slice($nodes, $offset, $limit);
			});
		$this->userFolder->method('getRelativePath')
			->willReturnCallback(fn (string $path) => str_replace('/testuser/files', '', $path));

		$results = $this->source->getMostRecentRecommendation($this->user, 3);

		$this->assertCount(3, $results);
	}

	public function testStorageNotAvailableExceptionIsSkipped(): void {
		$this->setShowHidden(false);

		$unavailableNode = $this->createMock(Node::class);
		$unavailableNode->method('getPath')->willReturn('/testuser/files/unavailable.pdf');
		$unavailableNode->method('getMTime')->willThrowException(new TestStorageNotAvailableException());

		$visibleNode = $this->createNodeMock('/testuser/files/document.pdf', 2000);

		$this->userFolder->method('getRecent')
			->willReturnCallback(function (int $limit, int $offset) use ($unavailableNode, $visibleNode): array {
				if ($offset === 0) {
					return [$unavailableNode, $visibleNode];
				}
				return [];
			});
		$this->userFolder->method('getRelativePath')
			->willReturnCallback(fn (string $path) => str_replace('/testuser/files', '', $path));

		$results = $this->source->getMostRecentRecommendation($this->user, 5);

		$this->assertCount(1, $results);
		$this->assertSame(2000, $results[0]->getTimestamp());
	}

	public function testOffsetIncrementsCorrectly(): void {
		$this->setShowHidden(false);

		$observedOffsets = [];

		$this->userFolder->method('getRecent')
			->willReturnCallback(function (int $limit, int $offset) use (&$observedOffsets): array {
				$observedOffsets[] = $offset;
				if ($offset === 0) {
					// Return a full batch of hidden nodes (equal to $limit) so the loop
					// advances the offset and fetches the next page.
					return array_map(
						fn (int $i) => $this->createNodeMock('/testuser/files/.hidden/file' . $i . '.txt', 1000 + $i),
						range(0, $limit - 1),
					);
				}
				return [];
			});

		$this->source->getMostRecentRecommendation($this->user, 5);

		$this->assertSame([0, 5], $observedOffsets);
	}

	public function testLoopStopsWhenFewerItemsThanMaxAreAvailable(): void {
		$this->setShowHidden(false);

		// Only 2 visible files exist, but max is 5.
		// The batch is smaller than max, so the loop must not keep fetching.
		$node1 = $this->createNodeMock('/testuser/files/file1.pdf', 1000);
		$node2 = $this->createNodeMock('/testuser/files/file2.pdf', 2000);

		$callCount = 0;
		$this->userFolder->method('getRecent')
			->willReturnCallback(function (int $limit, int $offset) use ($node1, $node2, &$callCount): array {
				$callCount++;
				// Only the first call (offset=0) returns items; a second call would
				// indicate an infinite loop — the batch size < max should have stopped it.
				return $offset === 0 ? [$node1, $node2] : [];
			});
		$this->userFolder->method('getRelativePath')
			->willReturnCallback(fn (string $path) => str_replace('/testuser/files', '', $path));

		$results = $this->source->getMostRecentRecommendation($this->user, 5);

		$this->assertCount(2, $results);
		$this->assertSame(1, $callCount, 'getRecent should only be called once when the batch is smaller than max');
	}

	/**
	 * @dataProvider hiddenPathProvider
	 */
	public function testIsNodeHiddenDetectsHiddenComponents(string $path, bool $expectedHidden): void {
		$this->setShowHidden(false);

		$node = $this->createNodeMock($path, 1000);
		$this->userFolder->method('getRecent')
			->willReturnCallback(function (int $limit, int $offset) use ($node): array {
				return $offset === 0 ? [$node] : [];
			});
		$this->userFolder->method('getRelativePath')
			->willReturnCallback(fn (string $path) => $path);

		$results = $this->source->getMostRecentRecommendation($this->user, 5);

		if ($expectedHidden) {
			$this->assertCount(0, $results, "Expected '$path' to be hidden");
		} else {
			$this->assertCount(1, $results, "Expected '$path' to be visible");
		}
	}

	public static function hiddenPathProvider(): array {
		return [
			'visible file' => ['/testuser/files/document.pdf', false],
			'hidden file (dot prefix)' => ['/testuser/files/.hidden', true],
			'file in hidden directory' => ['/testuser/files/.git/config', true],
			'file in nested hidden directory' => ['/testuser/files/subdir/.cache/tmp', true],
			'file with dot in name but not at start' => ['/testuser/files/my.document.pdf', false],
			'file at root level' => ['/file.txt', false],
		];
	}
}

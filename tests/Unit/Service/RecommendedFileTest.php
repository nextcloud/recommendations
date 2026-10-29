<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Service;

use OCA\Recommendations\Service\RecommendedFile;
use OCP\Files\File;
use OCP\Files\Storage\ISharedStorage;
use OCP\Files\Storage\IStorage;
use OCP\IUser;
use OCP\Share\IAttributes;
use OCP\Share\IShare;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecommendedFileTest extends TestCase {
	private File&MockObject $node;

	protected function setUp(): void {
		parent::setUp();

		$owner = $this->createMock(IUser::class);
		$owner->method('getUID')->willReturn('bob');
		$owner->method('getDisplayName')->willReturn('Bob');

		$this->node = $this->createMock(File::class);
		$this->node->method('getId')->willReturn(42);
		$this->node->method('getName')->willReturn('beach.jpg');
		$this->node->method('getExtension')->willReturn('jpg');
		$this->node->method('getMimetype')->willReturn('image/jpeg');
		$this->node->method('getEtag')->willReturn('abc123');
		$this->node->method('getMTime')->willReturn(1760000000);
		$this->node->method('getSize')->willReturn(2048);
		$this->node->method('getPermissions')->willReturn(27);
		$this->node->method('getOwner')->willReturn($owner);
	}

	// What WebDAV says of the file, for the viewer to build the same node
	public function testDescribesTheFileAsWebDavDoes(): void {
		$this->node->method('getStorage')->willReturn($this->createMock(IStorage::class));

		$file = (new RecommendedFile('/Photos', $this->node, 1760000100, 'recently-edited'))->jsonSerialize();

		$this->assertSame('abc123', $file['etag']);
		$this->assertSame(1760000000, $file['mtime']);
		$this->assertSame(2048, $file['size']);
		$this->assertSame(27, $file['permissions']);
		$this->assertSame('bob', $file['ownerId']);
		$this->assertSame('Bob', $file['ownerDisplayName']);
		$this->assertSame([], $file['shareAttributes']);
	}

	// A share that forbids downloading says so, as `share-attributes` does
	public function testGivesWhatTheShareItCameThroughAllows(): void {
		$forbidden = [['scope' => 'permissions', 'key' => 'download', 'value' => false]];
		$attributes = $this->createMock(IAttributes::class);
		$attributes->method('toArray')->willReturn($forbidden);
		$share = $this->createMock(IShare::class);
		$share->method('getAttributes')->willReturn($attributes);
		$storage = $this->createMock(ISharedStorage::class);
		$storage->method('instanceOfStorage')->with(ISharedStorage::class)->willReturn(true);
		$storage->method('getShare')->willReturn($share);
		$this->node->method('getStorage')->willReturn($storage);

		$file = (new RecommendedFile('/Photos', $this->node, 1760000100, 'recently-shared'))->jsonSerialize();

		$this->assertSame($forbidden, $file['shareAttributes']);
	}
}

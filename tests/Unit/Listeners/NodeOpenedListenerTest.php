<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Listeners;

use OCA\Recommendations\Db\OpenedFileMapper;
use OCA\Recommendations\Listeners\NodeOpenedListener;
use OCP\Files\Events\Node\NodeOpenedEvent;
use OCP\Files\File;
use OCP\IUser;
use PHPUnit\Framework\TestCase;

class NodeOpenedListenerTest extends TestCase {
	public function testStoresTheOpen(): void {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('alice');
		$file = $this->createMock(File::class);
		$file->method('getId')->willReturn(42);

		$mapper = $this->createMock(OpenedFileMapper::class);
		$mapper->expects($this->once())->method('add')->with('alice', 42);

		(new NodeOpenedListener($mapper))->handle(new NodeOpenedEvent($file, $user));
	}
}

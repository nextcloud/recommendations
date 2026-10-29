<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Tests\Unit\Listeners;

use OCA\Recommendations\Db\OpenedFileMapper;
use OCA\Recommendations\Listeners\NodeOpenedListener;
use OCP\AppFramework\Utility\ITimeFactory;
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
		$timeFactory = $this->createMock(ITimeFactory::class);
		$timeFactory->method('getTime')->willReturn(1000);

		$mapper = $this->createMock(OpenedFileMapper::class);
		$mapper->expects($this->once())->method('insert')->with('alice', 42, 1000);

		(new NodeOpenedListener($mapper, $timeFactory))->handle(new NodeOpenedEvent($file, $user));
	}
}

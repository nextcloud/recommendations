<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Listeners;

use OCA\Recommendations\Db\OpenedFileMapper;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\NodeOpenedEvent;

/** @template-implements IEventListener<NodeOpenedEvent> */
class NodeOpenedListener implements IEventListener {
	public function __construct(
		private readonly OpenedFileMapper $mapper,
	) {
	}

	#[\Override]
	public function handle(Event $event): void {
		if (!$event instanceof NodeOpenedEvent) {
			return;
		}

		$this->mapper->add($event->getUser()->getUID(), $event->getNode()->getId());
	}
}

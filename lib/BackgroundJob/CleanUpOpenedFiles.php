<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\BackgroundJob;

use OCA\Recommendations\Db\OpenedFileMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

/**
 * Forget what was opened long ago: it says little about what the user
 * works with now, and the table would only grow
 */
class CleanUpOpenedFiles extends TimedJob {
	public const RETENTION = 90 * 24 * 60 * 60; // 90 days

	public function __construct(
		ITimeFactory $time,
		private readonly OpenedFileMapper $mapper,
	) {
		parent::__construct($time);

		$this->setInterval(24 * 60 * 60); // 1 day
		$this->setTimeSensitivity(self::TIME_INSENSITIVE);
	}

	#[\Override]
	protected function run($argument): void {
		$this->mapper->deleteOlderThan($this->time->getTime() - self::RETENTION);
	}
}

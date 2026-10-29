<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

class FrequentlyOpenedFilesSource extends OpenedFilesSource {
	public const REASON = 'frequently-opened';

	#[\Override]
	protected function getReason(): string {
		return self::REASON;
	}

	#[\Override]
	protected function findOpened(string $userId, int $limit, int $offset): array {
		return $this->mapper->findFrequent($userId, $limit, $offset);
	}
}

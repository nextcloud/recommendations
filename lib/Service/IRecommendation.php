<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use JsonSerializable;
use OCP\Files\Node;

interface IRecommendation extends JsonSerializable {
	public function getId(): string;

	public function getTimestamp(): int;

	public function getNode(): Node;

	public function hasPreview(): bool;

	public function setHasPreview(bool $state);

	public function getReason(): string;
}

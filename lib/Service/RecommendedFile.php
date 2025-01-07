<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCP\Files\Node;
use ResponseDefinitions;

/**
 * @psalm-import-type RecommendationsRecommendedFile from ResponseDefinitions
 */
class RecommendedFile implements IRecommendation {
	private string $directory;
	private Node $node;
	private int $timestamp;
	private string $reason;
	private bool $hasPreview;

	public function __construct(string $directory,
		Node $node,
		int $timestamp,
		string $reason) {
		$this->directory = $directory;
		$this->node = $node;
		$this->reason = $reason;
		$this->timestamp = $timestamp;
		$this->hasPreview = false;
	}

	public function getId(): string {
		return (string)$this->node->getId();
	}

	public function getDirectory(): string {
		return $this->directory;
	}

	public function getTimestamp(): int {
		return $this->timestamp;
	}

	public function getNode(): Node {
		return $this->node;
	}

	public function getReason(): string {
		return $this->reason;
	}

	public function hasPreview(): bool {
		return $this->hasPreview;
	}

	public function setHasPreview(bool $state) {
		$this->hasPreview = $state;
	}

	/**
	 * @return RecommendationsRecommendedFile
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'timestamp' => $this->getTimestamp(),
			'name' => $this->node->getName(),
			'directory' => $this->getDirectory(),
			'extension' => $this->node->getExtension(),
			'mimeType' => $this->node->getMimetype(),
			'hasPreview' => $this->hasPreview(),
			'reason' => $this->getReason(),
		];
	}
}

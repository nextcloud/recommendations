<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\Files\Storage\ISharedStorage;
use OCP\Share\IAttributes;
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

	#[\Override]
	public function getId(): string {
		return (string)$this->node->getId();
	}

	public function getDirectory(): string {
		return $this->directory;
	}

	#[\Override]
	public function getTimestamp(): int {
		return $this->timestamp;
	}

	#[\Override]
	public function getNode(): Node {
		return $this->node;
	}

	#[\Override]
	public function getReason(): string {
		return $this->reason;
	}

	#[\Override]
	public function hasPreview(): bool {
		return $this->hasPreview;
	}

	#[\Override]
	public function setHasPreview(bool $state) {
		$this->hasPreview = $state;
	}

	/**
	 * @return RecommendationsRecommendedFile
	 */
	#[\ReturnTypeWillChange]
	#[\Override]
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
			'etag' => $this->node->getEtag(),
			'mtime' => $this->node->getMTime(),
			'size' => $this->node->getSize(),
			'permissions' => $this->node->getPermissions(),
			'ownerId' => $this->node->getOwner()?->getUID(),
			'ownerDisplayName' => $this->node->getOwner()?->getDisplayName(),
			'shareAttributes' => $this->getShareAttributes(),
		];
	}

	/**
	 * What the share the file was received through allows, as WebDAV gives it
	 * in `share-attributes`: a share can forbid downloading it, for one.
	 *
	 * @return list<array{scope: string, key: string, value: mixed}>
	 */
	private function getShareAttributes(): array {
		try {
			$storage = $this->node->getStorage();
		} catch (NotFoundException) {
			return [];
		}
		if (!$storage->instanceOfStorage(ISharedStorage::class)) {
			return [];
		}
		/** @var ISharedStorage $storage */
		$attributes = $storage->getShare()->getAttributes();
		if (!$attributes instanceof IAttributes) {
			return [];
		}
		return array_values($attributes->toArray());
	}
}

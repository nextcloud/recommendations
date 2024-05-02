<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Service;

use OCP\Comments\IComment;
use OCP\Files\Node;

class FileWithComments {
	private string $directory;
	private Node $node;
	private IComment $comment;

	public function __construct(string $directory, Node $node, IComment $comment) {
		$this->directory = $directory;
		$this->node = $node;
		$this->comment = $comment;
	}

	public function getDirectory(): string {
		return $this->directory;
	}

	public function getNode(): Node {
		return $this->node;
	}

	public function getComment(): IComment {
		return $this->comment;
	}
}

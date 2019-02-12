<?php

declare(strict_types=1);

/**
 * @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Recommendations\Service;

use OCP\Comments\IComment;
use OCP\Files\Node;

class FileWithComments {

	/** @var string */
	private $directory;

	/** @var Node */
	private $node;

	/** @var IComment */
	private $comment;

	public function __construct(string $directory, Node $node, IComment $comment) {
		$this->directory = $directory;
		$this->node = $node;
		$this->comment = $comment;
	}

	/**
	 * @return string
	 */
	public function getDirectory(): string {
		return $this->directory;
	}

	/**
	 * @return Node
	 */
	public function getNode(): Node {
		return $this->node;
	}

	/**
	 * @return IComment
	 */
	public function getComment(): IComment {
		return $this->comment;
	}

}

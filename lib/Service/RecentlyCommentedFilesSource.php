<?php

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Recommendations\Service;

use OCP\Comments\IComment;
use OCP\Comments\ICommentsManager;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\IL10N;
use OCP\IUser;

class RecentlyCommentedFilesSource implements IRecommendationSource {

	public const REASON = 'recently-commented';

	public function __construct(
		private readonly ICommentsManager $commentsManager,
		private readonly IRootFolder $rootFolder,
	) {
	}

	/**
	 * @return IComment[]
	 */
	private function getCommentsPage(int $offset, int $pageSize): array {
		// Comment search returns newest comments first, so the first resolvable
		// unique file IDs we encounter are the most recent recommendations.
		return $this->commentsManager->search(
			'',
			'files',
			'',
			'',
			$offset,
			$pageSize
		);
	}

	private function getCommentedFile(IComment $comment, Folder $userFolder): ?FileWithComments {
		$node = $userFolder->getFirstNodeById((int)$comment->getObjectId());
		if ($node === null) {
			return null;
		}

		return new FileWithComments(
			$userFolder->getRelativePath($node->getParent()->getPath()),
			$node,
			$comment
		);
	}

	/**
	 * @return IRecommendation[]
	 */
	#[\Override]
	public function getMostRecentRecommendation(IUser $user, int $max): array {
		if ($max <= 0) {
			return [];
		}

		$offset = 0;
		$pageSize = 100;
		$results = [];
		$seenFileIds = [];

		$userFolder = $this->rootFolder->getUserFolder($user->getUID());

		while (count($results) < $max) {
			$page = $this->getCommentsPage($offset, $pageSize);
			if ($page === []) {
				break;
			}

			foreach ($page as $comment) {
				$fileId = (int)$comment->getObjectId();
				if (isset($seenFileIds[$fileId])) {
					continue;
				}

				$commentedFile = $this->getCommentedFile($comment, $userFolder);
				if ($commentedFile === null) {
					continue;
				}

				$seenFileIds[$fileId] = true;
				$results[] = new RecommendedFile(
					$commentedFile->getDirectory(),
					$commentedFile->getNode(),
					$comment->getCreationDateTime()->getTimestamp(),
					self::REASON,
				);

				if (count($results) >= $max) {
					break 2;
				}
			}

			if (count($page) < $pageSize) {
				break;
			}

			$offset += $pageSize;
		}

		return $results;
	}
}

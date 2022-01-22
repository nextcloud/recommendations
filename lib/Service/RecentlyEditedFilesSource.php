<?php

declare(strict_types=1);

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\Files\StorageNotAvailableException;
use OCP\IL10N;
use OCP\IServerContainer;
use OCP\IUser;

class RecentlyEditedFilesSource implements IRecommendationSource {

	/** @var IServerContainer */
	private $serverContainer;

	/** @var IL10N */
	private $l10n;

	public function __construct(IServerContainer $serverContainer,
								IL10N $l10n) {
		$this->serverContainer = $serverContainer;
		$this->l10n = $l10n;
	}

	/**
	 * @return bool
	 */
	private function isNodeExcluded(Node $node, bool $showHidden): bool {
		$next = $node;

		while (!$showHidden) {
			if ($next->getName()[0] == ".")
				return true;

			try {
				$next = $next->getParent();
			} catch (NotFoundException $e) {
				break;
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getMostRecentRecommendation(IUser $user, int $max): array {
		$showHidden = (bool) $this->serverContainer->getConfig()->getUserValue($user->getUID(), 'files', 'show_hidden', false);
		$userFolder = $this->serverContainer->getUserFolder($user->getUID());

		$count = 0;
		$limit = 2 * $max;
		$offset = 0;
		$results = [];

		do {
			$recentFiles = $userFolder->getRecent($limit, $offset);

			foreach ($recentFiles as $node) {
				if (!$this->isNodeExcluded($node, $showHidden)) {
					try {
						$results[] = new RecommendedFile(
							$userFolder->getRelativePath($node->getParent()->getPath()),
							$node,
							$node->getMTime(),
							$this->l10n->t("Recently edited")
						);
					} catch (StorageNotAvailableException $e) {
						//pass
					}
				}
			}

			$offset += $limit;
			$count++;
		} while ((count($results) < $max) && ($count < 5));

		return array_slice($results, 0, $max);
	}
}

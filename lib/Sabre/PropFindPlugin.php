<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Sabre;

use OCP\Files\Folder;
use Sabre\DAV\INode;
use Sabre\DAV\PropFind;
use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;

class PropFindPlugin extends ServerPlugin {
	private ?Folder $userFolder = null;

	public const RECOMMENDATION_REASON = '{http://nextcloud.org/ns}recommendation-reason';
	public const RECOMMENDATION_REASON_LABEL = '{http://nextcloud.org/ns}recommendation-reason-label';

	public function getPluginName(): string {
		return 'recommendationsPropFindPlugin';
	}

	public function initialize(Server $server): void {
		$server->on('propFind', $this->propFind(...));
	}

	public function propFind(PropFind $propFind, INode $node): void {
		if ($node instanceof RecommendationDirectory || $node instanceof RecommendationFile) {
			$propFind->handle(
				self::RECOMMENDATION_REASON,
				/** @psalm-suppress PossiblyNullReference Null already checked above */
				fn () => $node->getRecommendationReason(),
			);
			$propFind->handle(
				self::RECOMMENDATION_REASON_LABEL,
				/** @psalm-suppress PossiblyNullReference Null already checked above */
				fn () => $node->getRecommendationReasonLabel(),
			);
		}
	}
}

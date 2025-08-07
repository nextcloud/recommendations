<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Sabre;

use OC\Files\View;
use OCA\DAV\Connector\Sabre\CachingTree;
use OCA\DAV\Connector\Sabre\Directory;
use OCP\Files\FileInfo;
use OCP\Share\IManager;

class RecommendationDirectory extends Directory implements IRecommendationNode {
	public function __construct(
		private string $recommendationReason,
		private string $recommendationReasonLabel,
		View $view,
		FileInfo $info,
		?CachingTree $tree = null,
		?IManager $shareManager = null,
	) {
		parent::__construct($view, $info, $tree, $shareManager);
	}


	public function getRecommendationReason(): string {
		return $this->recommendationReason;
	}

	public function getRecommendationReasonLabel(): string {
		return $this->recommendationReasonLabel;
	}
}

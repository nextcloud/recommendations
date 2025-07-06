<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Sabre;

use OC\Files\View;
use OCA\DAV\Connector\Sabre\File;
use OCP\Files\FileInfo;
use OCP\IL10N;
use OCP\IRequest;
use OCP\Share\IManager;

class RecommendationFile extends File implements IRecommendationNode {
	public function __construct(
		private string $recommendationReason,
		private string $recommendationReasonLabel,
		View $view,
		FileInfo $info,
		?IManager $shareManager = null,
		?IRequest $request = null,
		?IL10N $l10n = null,
	) {
		parent::__construct($view, $info, $shareManager, $request, $l10n);
	}

	public function getRecommendationReason(): string {
		return $this->recommendationReason;
	}

	public function getRecommendationReasonLabel(): string {
		return $this->recommendationReasonLabel;
	}
}

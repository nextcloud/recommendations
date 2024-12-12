<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\Dashboard;

use OCA\Recommendations\AppInfo\Application;
use OCA\Recommendations\Service\IRecommendation;
use OCA\Recommendations\Service\RecommendationService;
use OCP\Dashboard\IAPIWidget;
use OCP\Dashboard\IIconWidget;
use OCP\Dashboard\IWidget;
use OCP\Dashboard\Model\WidgetItem;
use OCP\Files\IMimeTypeDetector;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Util;

class RecommendationWidget implements IWidget, IIconWidget, IAPIWidget {
	private IUserSession $userSession;
	private IL10N $l10n;
	private IURLGenerator $urlGenerator;
	private IMimeTypeDetector $mimeTypeDetector;
	private RecommendationService $recommendationService;
	private IUserManager $userManager;

	public function __construct(
		IUserSession $userSession,
		IL10N $l10n,
		IURLGenerator $urlGenerator,
		IMimeTypeDetector $mimeTypeDetector,
		RecommendationService $recommendationService,
		IUserManager $userManager
	) {
		$this->userSession = $userSession;
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
		$this->mimeTypeDetector = $mimeTypeDetector;
		$this->recommendationService = $recommendationService;
		$this->userManager = $userManager;
	}

	public function getId(): string {
		return 'recommendations';
	}

	public function getTitle(): string {
		return $this->l10n->t('Recommended files');
	}

	public function getOrder(): int {
		return 0;
	}

	public function getIconClass(): string {
		return 'icon-files-dark';
	}

	public function getIconUrl(): string {
		return $this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath('files', 'app-dark.svg'));
	}

	public function getUrl(): ?string {
		return null;
	}

	public function load(): void {
		$user = $this->userSession->getUser();
		if ($user === null) {
			return;
		}
		Util::addScript(Application::APP_ID, 'recommendations-dashboard');
	}

	public function getItems(string $userId, ?string $since = null, int $limit = 7): array {
		$user = $this->userManager->get($userId);
		if (!$user) {
			return [];
		}
		$recommendations = $this->recommendationService->getRecommendations($user, $limit);

		return array_map(function (IRecommendation $recommendation) {
			$url = $this->urlGenerator->linkToRouteAbsolute(
				'files.viewcontroller.showFile', ['fileid' => $recommendation->getNode()->getId()]
			);

			if ($recommendation->hasPreview()) {
				$icon = $this->urlGenerator->linkToRouteAbsolute('core.Preview.getPreviewByFileId', [
					'x' => 256,
					'y' => 256,
					'fileId' => $recommendation->getNode()->getId(),
					'c' => $recommendation->getNode()->getEtag(),
				]);
			} else {
				$icon = $this->urlGenerator->getAbsoluteURL(
					$this->mimeTypeDetector->mimeTypeIcon($recommendation->getNode()->getMimetype())
				);
			}

			return new WidgetItem(
				$recommendation->getNode()->getName(),
				'',
				$url,
				$icon,
				(string)$recommendation->getTimestamp()
			);
		}, $recommendations);
	}
}

<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\Recommendations\Sabre;

use OC\Files\Filesystem;
use OC\Files\View;
use OCA\Recommendations\AppInfo\Application;
use OCA\Recommendations\Service\IRecommendation;
use OCA\Recommendations\Service\RecommendationService;
use OCP\Files\FileInfo;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUser;
use OCP\Share\IManager;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\ICollection;

class RecommendationsHome implements ICollection {

	/** @var ?list<IRecommendation> */
	private ?array $cachedRecommendations = null;

	private View $fileView;

	public function __construct(
		private array $principalInfo,
		private IUser $user,
		private IConfig $config,
		private RecommendationService $recommendationService,
		private IManager $shareManager,
		private IRequest $request,
		private IL10N $l10n,
	) {
		$this->fileView = Filesystem::getView();
	}

	public function delete() {
		throw new Forbidden();
	}

	public function getName(): string {
		[, $name] = \Sabre\Uri\split($this->principalInfo['uri']);
		return $name;
	}

	public function setName($name) {
		throw new Forbidden();
	}

	public function createFile($name, $data = null) {
		throw new Forbidden();
	}

	public function createDirectory($name) {
		throw new Forbidden();
	}

	public function getChild($name) {
		if (!$this->isEnabled()) {
			throw new NotFound('Recommendations are disabled');
		}

		$recommendations = $this->getChildren();
		foreach ($recommendations as $child) {
			if ($child->getName() === $name) {
				return $child;
			}
		}

		throw new NotFound("Child '$name' not found in recommendations");
	}

	public function getChildren(): array {
		if (!$this->isEnabled()) {
			return [];
		}

		if ($this->cachedRecommendations === null) {
			$this->cachedRecommendations = $this->recommendationService->getRecommendations($this->user);
		}

		return array_map(
			function (IRecommendation $recommendation) {
				$fileInfo = $recommendation->getNode()->getFileInfo();
				if ($recommendation->getNode()->getType() === FileInfo::TYPE_FOLDER) {
					return new RecommendationDirectory(
						$recommendation->getReason(),
						$recommendation->getReasonLabel(),
						$this->fileView,
						$fileInfo,
						null,
						$this->shareManager,
					);
				}
				return new RecommendationFile(
					$recommendation->getReason(),
					$recommendation->getReasonLabel(),
					$this->fileView,
					$fileInfo,
					$this->shareManager,
					$this->request,
					$this->l10n
				);
			},
			$this->cachedRecommendations
		);
	}

	public function childExists($name): bool {
		if (!$this->isEnabled()) {
			return false;
		}
		// TODO: map the recommendations to a Sabre node type
		return true;
	}

	public function getLastModified(): int {
		return 0;
	}

	private function isEnabled(): bool {
		return $this->config->getUserValue($this->user->getUID(), Application::APP_ID, 'enabled', 'true') === 'true';
	}
}

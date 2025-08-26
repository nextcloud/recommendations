<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\Recommendations\Sabre;

use OCA\Recommendations\AppInfo\Application;
use OCA\Recommendations\Service\RecommendationService;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Share\IManager;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\INode;
use Sabre\DAVACL\AbstractPrincipalCollection;
use Sabre\DAVACL\PrincipalBackend;

class RootCollection extends AbstractPrincipalCollection {
	public function __construct(
		PrincipalBackend\BackendInterface $principalBackend,
		private IConfig $config,
		private RecommendationService $recommendationService,
		private IUserSession $userSession,
		private IManager $shareManager,
		private IRequest $request,
		private IL10N $l10n,
	) {
		parent::__construct($principalBackend, 'principals/users');
		$this->disableListing = !$config->getSystemValue('debug', false);
	}

	/**
	 * This method returns a node for a principal.
	 *
	 * The passed array contains principal information, and is guaranteed to
	 * at least contain a uri item. Other properties may or may not be
	 * supplied by the authentication backend.
	 *
	 * @param array $principalInfo
	 * @return INode
	 */
	public function getChildForPrincipal(array $principalInfo): RecommendationsHome {
		[, $name] = \Sabre\Uri\split($principalInfo['uri']);
		$user = $this->userSession->getUser();
		if (is_null($user) || $name !== $user->getUID()) {
			throw new Forbidden();
		}
		return new RecommendationsHome(
			$principalInfo,
			$user,
			$this->config,
			$this->recommendationService,
			$this->shareManager,
			$this->request,
			$this->l10n,
		);
	}

	public function getName(): string {
		return Application::APP_ID;
	}
}

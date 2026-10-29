<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Recommendations\AppInfo;

use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\Recommendations\Capabilities;
use OCA\Recommendations\Dashboard\RecommendationWidget;
use OCA\Recommendations\Listeners\FilesLoadAdditionalScriptsListener;
use OCA\Recommendations\Listeners\NodeOpenedListener;
use OCA\Recommendations\Listeners\UserDeletedListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Files\Events\Node\NodeOpenedEvent;
use OCP\User\Events\UserDeletedEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'recommendations';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	#[\Override]
	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(LoadAdditionalScriptsEvent::class, FilesLoadAdditionalScriptsListener::class);
		$context->registerEventListener(NodeOpenedEvent::class, NodeOpenedListener::class);
		$context->registerEventListener(UserDeletedEvent::class, UserDeletedListener::class);
		$context->registerDashboardWidget(RecommendationWidget::class);
		$context->registerCapability(Capabilities::class);
	}

	#[\Override]
	public function boot(IBootContext $context): void {
	}
}

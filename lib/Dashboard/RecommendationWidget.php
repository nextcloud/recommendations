<?php
/**
 * @copyright Copyright (c) 2020 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Recommendations\Dashboard;

use OCA\Recommendations\AppInfo\Application;
use OCP\Dashboard\IWidget;
use OCP\IL10N;
use OCP\IUserSession;
use OCP\Util;

class RecommendationWidget implements IWidget {
	private IUserSession $userSession;
	private IL10N $l10n;

	public function __construct(
		IUserSession $userSession,
		IL10N $l10n
	) {
		$this->userSession = $userSession;
		$this->l10n = $l10n;
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

	public function getUrl(): ?string {
		return null;
	}

	public function load(): void {
		$user = $this->userSession->getUser();
		if ($user === null) {
			return;
		}
		Util::addScript(Application::APP_ID, 'files_recommendation-dashboard');
	}
}

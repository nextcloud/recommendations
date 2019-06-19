<?php
/**
 * @copyright Copyright (c) 2019 Gary Kim <gary@garykim.dev>
 *
 * @author Gary Kim <gary@garykim.dev>
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
 *
 */

namespace OCA\Recommendations\Settings;

use OCA\Recommendations\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\Settings\ISettings;

class Personal implements ISettings {

	/** @var IConfig */
	private $config;

	/** @var IUserSession */
	private $user;

	/**
	 * Personal constructor
	 * 
	 * @param IConfig $config
	 * @param IUserSession $user
	 */
	public function __construct(IConfig $config, IUserSession $user) {
		$this->config = $config;
		$this->user = $user;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm() {
		$userId = $this->user->getUser()->getUID();
		$parameters = [
			'enabled' => $this->config->getUserValue($userId, Application::APP_ID, 'enabled', 'true') === 'true',
		];
		return new TemplateResponse(Application::APP_ID, 'personal', $parameters);
	}

	/**
	 * @return string section ID. 'recommendations' in this case
	 */
	public function getSection() {
		return 'recommendations';
	}

	/**
	 * @return int priority of settings
	 */
	public function getPriority() {
		return 50;
	}
}

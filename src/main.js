/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @copyright 2019-2020 Gary Kim <gary@garykim.dev>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license AGPL-3.0-or-later
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

import Vue from 'vue'
import { Header, registerFileListHeaders } from '@nextcloud/files'

import Recommendations from './components/Recommendations.vue'
import Settings from './components/Settings.vue'
import store from './store/store.js'

const View = Vue.extend(Recommendations)

const header = new Header({
	id: 'recommendations',
	order: 90,

	enabled(folder, view) {
		return view.id === 'files' && folder.path === '/'
	},

	render(el, folder, view) {
		// Load recommendations
		store.dispatch('fetchRecommendations')

		new View({
			name: 'RecommendationsHeader',
			store,
		}).$mount(el)

		// Create settings
		const SettingsView = Vue.extend(Settings)
		const settingsElement = new SettingsView({
			store,
		}).$mount().$el

		// Register Files App Settings
		if (OCA.Files && OCA.Files.Settings) {
			OCA.Files.Settings.register(new OCA.Files.Settings.Setting('recommendations', {
				el: () => { return settingsElement },
			}))
		}
	},
	updated(folder, view) {},
})

registerFileListHeaders(header)

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import Vue from 'vue'
import { Header, registerFileListHeaders } from '@nextcloud/files'

import FilesRecommendations from './components/FilesRecommendations.vue'
import Settings from './components/Settings.vue'
import store from './store/store.js'

const View = Vue.extend(FilesRecommendations)

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

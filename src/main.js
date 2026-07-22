/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import { createApp } from 'vue'
import { registerFileListHeader } from '@nextcloud/files'

import FilesRecommendations from './components/FilesRecommendations.vue'
import Settings from './components/Settings.vue'
import { useRecommendationsStore } from './store/store.js'

const header = {
	id: 'recommendations',
	order: 90,

	enabled(folder, view) {
		return view.id === 'files' && folder.path === '/'
	},

	render(el, folder, view) {
		// Load recommendations
		useRecommendationsStore().fetchRecommendations()

		createApp(FilesRecommendations).mount(el)

		// Create settings
		const settingsElement = document.createElement('div')
		createApp(Settings).mount(settingsElement)

		// Register Files App Settings
		if (OCA.Files && OCA.Files.Settings) {
			OCA.Files.Settings.register(new OCA.Files.Settings.Setting('recommendations', {
				el: () => { return settingsElement },
			}))
		}
	},
	updated(folder, view) {},
}

registerFileListHeader(header)

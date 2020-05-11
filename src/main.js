/*
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @copyright 2019-2020 Gary Kim <gary@garykim.dev>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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
 */

import Vue from 'vue'

import Nextcloud from './mixins/Nextcloud'
import Recommendations from './components/Recommendations'
import Settings from './components/Settings'
import store from './store/store'

Vue.mixin(Nextcloud)
OC.Plugins.register('OCA.Files.FileList', {

	el: null,

	attach: function(fileList) {
		if (fileList.id !== 'files') {
			return
		}

		this.el = document.createElement('div')
		this.el.id = 'files-recommendation-wrapper'
		fileList.registerHeader({
			id: 'recommendations',
			el: this.el,
			render: this.render.bind(this),
			order: 90,
		})
	},

	render: function(fileList) {

		// Load recommendations
		store.dispatch('fetchRecommendations')

		const View = Vue.extend(Recommendations)
		const vm = new View({
			propsData: {},
			store,
		}).$mount(this.el)

		// register Files App Setting
		const SettingsView = Vue.extend(Settings)
		const settingsElement = new SettingsView({
			store,
		}).$mount().$el
		if (OCA.Files && OCA.Files.Settings) {
			OCA.Files.Settings.register(new OCA.Files.Settings.Setting('recommendations', {
				el: () => { return settingsElement },
			}))
		}

		fileList.$el.on('changeDirectory', data => {
			if (data.dir.toString() === '/') {
				vm.show()
			} else {
				vm.hide()
			}
		})

		if (fileList.getCurrentDirectory() === '/') {
			vm.show()
		}

		return this.el
	},

})

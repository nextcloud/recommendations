/**
 * @copyright 2019-2020 Gary Kim <gary@garykim.dev>
 *
 * @author Gary Kim <gary@garykim.dev>
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

import { generateUrl } from '@nextcloud/router'
import Vuex, { Store } from 'vuex'
import axios from '@nextcloud/axios'
import Vue from 'vue'
import { fetchRecommendedFiles } from '../service/RecommendationService.js'

Vue.use(Vuex)

export default new Store({
	state: {
		enabled: true,
		loadedRecommendations: false,
		loading: false,
		recommendedFiles: [],
	},
	mutations: {
		enabled(state, val) {
			state.enabled = val
		},
		loadedRecommendations(state, val) {
			state.loadedRecommendations = val
		},
		loading(state, val) {
			state.loading = val
		},
		recommendedFiles(state, val) {
			state.recommendedFiles = val
		},
	},
	actions: {
		/**
		 * Toggle the recommendations and fetch recommended files if required
		 *
		 * @async
		 * @param {object} context the store context
		 * @param {boolean} enabled recommendations status
		 */
		async enabled(context, enabled) {
			context.commit('enabled', enabled)
			await axios.put(generateUrl('apps/recommendations/settings/enabled'), {
				value: enabled.toString(),
			})
			if (enabled) {
				context.dispatch('fetchRecommendations')
			}
		},
		/**
		 * Fetch recommendations and current enabled setting
		 *
		 * @async
		 * @param {object} context the store context
		 * @param {boolean} [always] set to true to always get recommendations regardless of enabled setting
		 */
		async fetchRecommendations(context, always) {
			if (context.state.loadedRecommendations || context.state.loading) {
				return
			}
			this.commit('loading', true)
			const fetched = await fetchRecommendedFiles(always)

			context.commit('enabled', fetched.enabled)
			if (fetched.recommendations) {
				context.commit('recommendedFiles', fetched.recommendations)
				this.commit('loadedRecommendations', true)
			}
			this.commit('loading', false)
		},
	},
})

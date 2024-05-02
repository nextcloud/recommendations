/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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

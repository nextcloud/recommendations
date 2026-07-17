/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import { generateUrl } from '@nextcloud/router'
import { createPinia, defineStore, setActivePinia } from 'pinia'
import axios from '@nextcloud/axios'
import { fetchRecommendedFiles } from '../service/RecommendationService.js'

setActivePinia(createPinia())

export const useRecommendationsStore = defineStore('recommendations', {
	state: () => ({
		enabled: true,
		loadedRecommendations: false,
		loading: false,
		recommendedFiles: [],
	}),
	actions: {
		/**
		 * Toggle the recommendations and fetch recommended files if required
		 *
		 * @async
		 * @param {boolean} enabled recommendations status
		 */
		async setEnabled(enabled) {
			this.enabled = enabled
			await axios.put(generateUrl('apps/recommendations/settings/enabled'), {
				value: enabled.toString(),
			})
			if (enabled) {
				this.fetchRecommendations()
			}
		},
		/**
		 * Fetch recommendations and current enabled setting
		 *
		 * @async
		 * @param {boolean} [always] set to true to always get recommendations regardless of enabled setting
		 */
		async fetchRecommendations(always) {
			if (this.loadedRecommendations || this.loading) {
				return
			}
			this.loading = true
			const fetched = await fetchRecommendedFiles(always)

			this.enabled = fetched.enabled
			if (fetched.recommendations) {
				this.recommendedFiles = fetched.recommendations
				this.loadedRecommendations = true
			}
			this.loading = false
		},
	},
})

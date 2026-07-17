/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
import Vue from 'vue'

import DashboardWidget from './components/DashboardWidget.vue'
import { useRecommendationsStore } from './store/store.js'

// Load recommendations
useRecommendationsStore().fetchRecommendations(true)

document.addEventListener('DOMContentLoaded', function() {

	OCA.Dashboard.register('recommendations', (el) => {
		const View = Vue.extend(DashboardWidget)
		// eslint-disable-next-line no-unused-vars
		const vm = new View({
			propsData: {},
		}).$mount(el)
	})

})

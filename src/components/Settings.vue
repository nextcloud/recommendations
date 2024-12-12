<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div id="recommendations-setting-enabled">
		<NcCheckboxRadioSwitch id="recommendationsEnabledToggle" :checked.sync="enabled">
			{{ t('recommendations', 'Show recommendations') }}
		</NcCheckboxRadioSwitch>
	</div>
</template>

<script>
import { NcCheckboxRadioSwitch } from '@nextcloud/vue'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'Settings',
	components: {
		NcCheckboxRadioSwitch,
	},

	computed: {
		enabled: {
			get() {
				return this.$store.state.enabled
			},
			set(val) {
				this.$store.dispatch('enabled', val)
			},
		},
	},

	methods: {
		t,
	},
}
</script>

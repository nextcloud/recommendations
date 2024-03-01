<template>
	<div id="recommendations-setting-enabled">
		<input id="recommendationsEnabledToggle"
			v-model="enabled"
			class="checkbox"
			checked="checked"
			type="checkbox"
			name="enabled">
		<label for="recommendationsEnabledToggle">{{ t('recommendations', 'Show recommendations') }}</label>
	</div>
</template>

<script>
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'Settings',
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

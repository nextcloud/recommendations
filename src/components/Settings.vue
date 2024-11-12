<template>
	<div id="recommendations-setting-enabled">
		<NcCheckboxRadioSwitch id="recommendationsEnabledToggle" :checked.sync="enabled">
			{{ t('recommendations', 'Show recommendations') }}
		</NcCheckboxRadioSwitch>
	</div>
</template>

<script>
import { NcCheckboxRadioSwitch } from '@nextcloud/vue'

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
}
</script>

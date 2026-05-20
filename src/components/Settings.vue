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

<script setup>
import { computed } from 'vue'
import { NcCheckboxRadioSwitch } from '@nextcloud/vue'
import { translate as t } from '@nextcloud/l10n'
import store from '../store/store.js'

const enabled = computed({
	get: () => store.state.enabled,
	set: (val) => store.dispatch('enabled', val),
})
</script>

<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div id="recommendations-setting-enabled">
		<NcCheckboxRadioSwitch id="recommendationsEnabledToggle" v-model="enabled">
			{{ t('recommendations', 'Show recommendations') }}
		</NcCheckboxRadioSwitch>
	</div>
</template>

<script setup>
import { computed } from 'vue'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import { translate as t } from '@nextcloud/l10n'
import { useRecommendationsStore } from '../store/store.js'

const store = useRecommendationsStore()
const enabled = computed({
	get: () => store.enabled,
	set: (val) => store.setEnabled(val),
})
</script>

<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div v-if="!loading && enabled">
		<ul
			v-if="recommendedFiles.length > 0"
			id="recommendations"
			class="group">
			<li
				v-for="file in recommendedFiles"
				:key="file.id"
				class="recommendation-item">
				<RecommendedFile
					:id="file.id"
					:extension="file.extension"
					:mimeType="file.mimeType"
					:name="file.name"
					:directory="file.directory"
					:reason="file.reason"
					:hasPreview="file.hasPreview"
					:timestamp="file.timestamp" />
			</li>
		</ul>
	</div>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import RecommendedFile from './RecommendedFile.vue'
import { useRecommendationsStore } from '../store/store.js'

const { enabled, loading, recommendedFiles } = storeToRefs(useRecommendationsStore())
</script>

<style scoped>
#recommendations {
	/* Center the thumbnail on the file list icon, so the text aligns with the file names */
	--recommendations-icon-offset: calc((32px - var(--icon-preview-size, 24px)) / 2);
	padding-block: 0;
	margin-top: var(--default-grid-baseline, 4px);
	padding-inline: calc(var(--row-height, 44px) - var(--recommendations-icon-offset) - 5px) var(--row-height, 44px);
	display: flex;
	max-height: 58px;
	overflow: hidden;
	flex-wrap: wrap;
	min-width: 0;
}

.recommendation-item {
	display: flex;
	align-items: center;
	flex-grow: 1;
	min-width: 250px;
}

/* show 2 per line for screen sizes smaller that 1200px */
@media only screen and (max-width: 1200px) {
	#recommendations {
		height: initial;
		max-height: 161px;
	}
	.recommendation-item {
		flex-basis: 50%;
		max-width: calc(50% - 15px);
	}
}

/*  GO FULL WIDTH BELOW 480 PIXELS */
@media only screen and (max-width: 480px) {
	.recommendation-item {
		flex-basis: 100%;
		min-width: 100%;
	}
}
</style>

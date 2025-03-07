<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div v-if="!loading && enabled">
		<ul v-if="recommendedFiles.length > 0"
			id="recommendations"
			class="group">
			<li v-for="file in recommendedFiles"
				:key="file.id"
				class="recommendation-item">
				<RecommendedFile :id="file.id"
					:extension="file.extension"
					:mime-type="file.mimeType"
					:name="file.name"
					:directory="file.directory"
					:reason="file.reason"
					:has-preview="file.hasPreview"
					:timestamp="file.timestamp" />
			</li>
		</ul>
	</div>
</template>

<script>
import { translate as t } from '@nextcloud/l10n'
import RecommendedFile from './RecommendedFile.vue'

export default {
	name: 'FilesRecommendations',
	components: { RecommendedFile },
	computed: {
		enabled() {
			return this.$store.state.enabled
		},
		loading() {
			return this.$store.state.loading
		},
		recommendedFiles() {
			return this.$store.state.recommendedFiles
		},
	},
	methods: {
		t,
	},
}
</script>

<style scoped>
#recommendations {
	padding: 28px 30px 0 50px;
	margin-bottom: 20px;
	display: flex;
	height: 86px;
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
		max-height: 189px;
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

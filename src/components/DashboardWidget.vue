<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcDashboardWidget id="recommendations" :items="recommendedFiles">
		<template #default="{ item }">
			<RecommendedFile :id="item.id"
				:key="item.id"
				:extension="item.extension"
				:mime-type="item.mimeType"
				:name="item.name"
				:directory="item.directory"
				:reason="item.reason"
				:has-preview="item.hasPreview"
				:timestamp="item.timestamp" />
		</template>
		<template #empty-content>
			<NcEmptyContent id="recommendations--empty-content"
				icon="icon-files-dark">
				<template #description>
					{{ t('recommendations', 'No recommendations yet') }}
				</template>
			</NcEmptyContent>
		</template>
	</NcDashboardWidget>
</template>

<script>
import { t } from '@nextcloud/l10n'
import NcDashboardWidget from '@nextcloud/vue/dist/Components/NcDashboardWidget.js'
import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import RecommendedFile from './RecommendedFile.vue'

export default {
	name: 'DashboardWidget',

	components: {
		RecommendedFile,
		NcDashboardWidget,
		NcEmptyContent,
	},
	computed: {
		enabled() {
			return this.$store.state.enabled
		},
		loading() {
			return this.$store.state.loading
		},
		recommendedFiles() {
			return this.$store.state.recommendedFiles.slice(0, 7)
		},
	},
	methods: {
		t,
	},
}
</script>

<style lang="scss" scoped>
	#recommendations {
		::v-deep .recommendation {
			max-width: 100%;
			padding: 8px;
			margin-right: 0;
			border-radius: var(--border-radius-large);

			.thumbnail {
				width: 44px;
				height: 44px;
			}

			.details {
				.file-name .extension {
					vertical-align: top;
				}

				.reason {
					display: none;
				}
			}
		}
	}
	#recommendations--empty-content {
		text-align: center;
		margin-top: 5vh;
	}

</style>

<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<DashboardWidget id="recommendations" :items="recommendedFiles">
		<template #default="{ item }">
			<RecommendedFile :id="item.id"
				:key="item.id"
				:extension="item.extension"
				:mime-type="item.mimeType"
				:name="item.name"
				:directory="item.directory"
				:reason="item.reason"
				:has-preview="item.hasPreview" />
		</template>
		<template #empty-content>
			<EmptyContent id="recommendations--empty-content"
				icon="icon-files-dark">
				<template #description>
					{{ t('recommendations', 'No recommendations yet') }}
				</template>
			</EmptyContent>
		</template>
	</DashboardWidget>
</template>

<script>
import { translate as t } from '@nextcloud/l10n'
import { DashboardWidget } from '@nextcloud/vue-dashboard'
import EmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import RecommendedFile from './RecommendedFile.vue'

export default {
	name: 'Dashboard',
	components: { RecommendedFile, DashboardWidget, EmptyContent },
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
			padding: 8px 0;
			margin-right: 0;
			border-radius: var(--border-radius-large);

			.thumbnail {
				margin-left: 8px;
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

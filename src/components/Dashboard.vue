<!--
  - @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
	<DashboardWidget id="recommendations" :items="recommendedFiles">
		<template v-slot:default="{ item }">
			<RecommendedFile
				:id="item.id"
				:key="item.id"
				:extension="item.extension"
				:mime-type="item.mimeType"
				:name="item.name"
				:directory="item.directory"
				:reason="item.reason"
				:has-preview="item.hasPreview" />
		</template>
		<template #empty-content>
			<EmptyContent
				id="recommendations--empty-content"
				icon="icon-files-dark">
				<template #desc>
					{{ t('recommendations', 'No recommendations yet') }}
				</template>
			</EmptyContent>
		</template>
	</DashboardWidget>
</template>

<script>
import { DashboardWidget } from '@nextcloud/vue-dashboard'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'
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

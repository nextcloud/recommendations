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
	<div v-if="!loading">
		<div v-if="recommendedFiles.length > 0"
			id="recommendations"
			class="group">
			<RecommendedFile v-for="file in recommendedFiles"
				:id="file.id"
				:key="file.id"
				:extension="file.extension"
				:mime-type="file.mimeType"
				:name="file.name"
				:directory="file.directory"
				:reason="file.reason"
				:has-preview="file.hasPreview"
			/>
		</div>
	</div>
	<div v-else>
		<div class="placeholder-line" v-for="index in 5" :key="index">
			<div class="placeholder-icon"></div>
			<div class="placeholder-text"></div>
		</div>
	</div>
</template>

<script>
import RecommendedFile from './RecommendedFile'

export default {
	name: 'Dashboard',
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
}
</script>

<style lang="scss" scoped>
	#recommendations {
		display: flex;
		flex-direction: row;
		overflow: hidden;
		flex-wrap: wrap;
		min-width: 0;

		::v-deep .recommendation .details {
			.file-name .extension {
				vertical-align: top;
			}

			.reason {
				display: none;
			}
		}
	}

	$placeholder-height: 40px;
	.placeholder-line {
		display: flex;
		align-items: stretch;

		.placeholder-icon {
			margin: 5px;
			width: $placeholder-height;
			height: $placeholder-height;
			display: block;
			background-color: var(--color-background-dark);
			border-radius: var(--border-radius);
		}
		.placeholder-text {
			height: $placeholder-height;
			margin: 5px;
			flex-grow: 1;
			display: block;
			background-color: var(--color-background-dark);
			border-radius: var(--border-radius);
		}
	}
</style>

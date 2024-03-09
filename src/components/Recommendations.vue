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
					:has-preview="file.hasPreview" />
			</li>
		</ul>
	</div>
</template>

<script>
import { translate as t } from '@nextcloud/l10n'
import RecommendedFile from './RecommendedFile.vue'

export default {
	name: 'Recommendations',
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

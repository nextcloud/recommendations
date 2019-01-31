<!--
  - @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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
	<div v-if="hidden"></div>
	<div v-else-if="loading"></div>
	<div v-else-if="recommendedFiles.length === 0"
		 class="apps-header">
		<span id="recommendation-headline"
			  class="extension">
			{{ t('recommendation', 'Recommendations') }}
			</span>
		<div id="recommendation-content"
			 class="section group">
			<div class="col empty_recommendation_content">
				{{ t('recommendation', 'no recommendations available') }}
			</div>
		</div>
	</div>
	<div v-else
		 class="apps-header">
		<span id="recommendation-headline"
			  class="extension">
			{{ t('recommendation', 'Recommendations') }}
		</span>
		<div id="recommendation-content"
			 class="section group">
			<RecommendedFile v-for="file in recommendedFiles"
							 :id="file.id"
							 :extension="file.extension"
							 :mime-type="file.mimeType"
							 :name="file.name"
							 :reason="file.reason"
							 :url="''"
							 :key="file.id" />
		</div>
	</div>
</template>

<script>
	import {fetchRecommendedFiles} from "../service/RecommendationService";
	import RecommendedFile from "./RecommendedFile";

	export default {
		name: "Recommendations",
		components: {RecommendedFile},
		data () {
			return {
				hidden: true,
				loading: false,
				recommendedFiles: [],
			}
		},
		methods: {
			show () {
				this.hidden = false;

				this.load();
			},
			hide () {
				this.hidden = true;
			},
			load () {
				this.loading = true;

				fetchRecommendedFiles()
					.then(files => {
						this.loading = false;
						this.recommendedFiles = files;
					})
					.catch(console.error.bind(this));
			},
		}
	}
</script>

<style scoped>
	.empty_recommendation_content {
		margin-left: 41px;
	}

	#recommendation-content.section {
		clear: both;
		margin-left: 32px;
		margin-top: 16px;
		display: flex;
		flex-wrap: nowrap;
		flex-flow: row wrap;
		flex-grow: 1;
		flex-shrink: 1;
		min-width: 0;
		padding-top: 0px;
	}

	#recommendation-headline.extension {
		clear: both;
		margin-left: 103px;
	}

	.apps-header {
		margin-top: 13px;
	}

	.col {
		display: block;
		float: left;
		flex-grow: 1;
		flex-shrink: 1;
		flex-basis: 20%;
	}

	/* show 2 per line for screen sizes smaller that 1200px */
	@media only screen and (max-width: 1200px) {
		.col {
			flex-basis: 50%;
			max-width: calc(50% - 15px);
		}

		#recommendation-content .thumbnail-wrapper {
			margin-bottom: 15px;
		}
	}

	/*  GO FULL WIDTH BELOW 480 PIXELS */
	@media only screen and (max-width: 480px) {
		.col {
			flex-basis: 100%;
			min-width: 100%;
		}

		#recommendation-content .thumbnail-wrapper {
			margin-bottom: 15px;
		}
	}
</style>

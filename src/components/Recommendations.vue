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
	<div v-if="hidden"></div>
	<div v-else-if="loading"></div>
	<div v-else>
		<div id="recommendations"
			 class="group">
			<RecommendedFile v-for="file in recommendedFiles"
							 :id="file.id"
							 :extension="file.extension"
							 :mime-type="file.mimeType"
							 :name="file.name"
							 :directory="file.directory"
							 :reason="file.reason"
							 :key="file.id"/>
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
	#recommendations {
		padding: 30px 30px 0 59px;
		margin-bottom: 30px;
		display: flex;
		height: 72px;
		overflow: hidden;
		flex-wrap: wrap;
		min-width: 0;
	}

	/* show 2 per line for screen sizes smaller that 1200px */
	@media only screen and (max-width: 1200px) {
		#recommendations {
			height: initial;
		}
	}
</style>

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
			 class="section group">
			<RecommendedFile v-for="file in recommendedFiles"
							 :id="file.id"
							 :extension="file.extension"
							 :mime-type="file.mimeType"
							 :name="file.name"
							 :reason="file.reason"
							 :url="''"
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
		margin-left: 32px;
		display: flex;
		flex-wrap: nowrap;
		flex-flow: row wrap;
		flex-grow: 1;
		flex-shrink: 1;
		min-width: 0;
	}
</style>
